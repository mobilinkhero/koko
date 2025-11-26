<?php

namespace Corbital\LaravelEmails\Http\Controllers;

use App\Http\Controllers\Controller;
use Corbital\LaravelEmails\Models\EmailLayout;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailLayoutController extends Controller
{
    /**
     * Display a listing of email layouts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $layouts = EmailLayout::latest()->paginate(10);

        return view('laravel-emails::layouts.index', compact('layouts'));
    }

    /**
     * Show the form for creating a new email layout.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('laravel-emails::layouts.create');
    }

    /**
     * Store a newly created email layout.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:email_layouts',
            'header' => 'nullable|string',
            'footer' => 'nullable|string',
            'master_template' => 'required|string',
            'variables' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Process variables from string to array
        if (! empty($validated['variables'])) {
            $variables = array_map('trim', explode(',', $validated['variables']));
            $validated['variables'] = $variables;
        }

        EmailLayout::create($validated);

        return redirect()->route('laravel-emails.layouts.index')
            ->with('success', 'Email layout created successfully.');
    }

    /**
     * Display the specified email layout.
     *
     * @return \Illuminate\View\View
     */
    public function show(EmailLayout $layout)
    {
        return view('laravel-emails::layouts.show', compact('layout'));
    }

    /**
     * Show the form for editing the specified email layout.
     *
     * @return \Illuminate\View\View
     */
    public function edit(EmailLayout $layout)
    {
        return view('laravel-emails::layouts.edit', compact('layout'));
    }

    /**
     * Preview an email layout with sample content.
     *
     * @return \Illuminate\View\View
     */
    public function preview(EmailLayout $layout)
    {
        // Sample data for preview
        $data = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'company_name' => config('app.name'),
            'company_logo' => 'https://via.placeholder.com/200x50',
            'company_address' => '123 Company St, City, Country',
            'year' => date('Y'),
        ];

        // Additional variables from the layout
        if (! empty($layout->variables) && is_array($layout->variables)) {
            foreach ($layout->variables as $variable) {
                if (! isset($data[$variable])) {
                    $data[$variable] = "Sample {$variable}";
                }
            }
        }

        // Sample content for preview
        $sampleContent = '
        <h2>Sample Email Content</h2>
        <p>This is a sample email content to demonstrate how the layout will look.</p>
        <p>The layout includes a header and a footer that are managed centrally.</p>
        <ul>
            <li>You can add your content here</li>
            <li>The header and footer will be consistent across all emails</li>
            <li>Variables can be used throughout the template</li>
        </ul>
        <p>Thank you for using our email template system!</p>';

        // Render the layout with sample content
        $previewHtml = $layout->render($sampleContent, $data);

        return view('laravel-emails::layouts.preview', compact('layout', 'previewHtml'));
    }

    /**
     * Update the specified email layout.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, EmailLayout $layout)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('email_layouts')->ignore($layout)],
            'header' => 'nullable|string',
            'footer' => 'nullable|string',
            'master_template' => 'required|string',
            'variables' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Process variables from string to array
        if (! empty($validated['variables'])) {
            $variables = array_map('trim', explode(',', $validated['variables']));
            $validated['variables'] = $variables;
        }

        $layout->update($validated);

        return redirect()->route('laravel-emails.layouts.index')
            ->with('success', 'Email layout updated successfully.');
    }

    /**
     * Remove the specified email layout.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(EmailLayout $layout)
    {
        // Don't allow deletion of system layouts
        if ($layout->is_system) {
            return redirect()->route('laravel-emails.layouts.index')
                ->with('error', 'Cannot delete system layouts.');
        }

        // Check if layout is used by templates
        if ($layout->templates()->count() > 0) {
            return redirect()->route('laravel-emails.layouts.index')
                ->with('error', 'Cannot delete layout because it is used by one or more templates.');
        }

        $layout->delete();

        return redirect()->route('laravel-emails.layouts.index')
            ->with('success', 'Email layout deleted successfully.');
    }
}
