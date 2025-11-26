<?php

namespace Corbital\LaravelEmails\Http\Controllers;

use App\Http\Controllers\Controller;
use Corbital\LaravelEmails\Events\EmailTemplateCreated;
use Corbital\LaravelEmails\Events\EmailTemplateDeleted;
use Corbital\LaravelEmails\Events\EmailTemplateUpdated;
use Corbital\LaravelEmails\Facades\Email;
use Corbital\LaravelEmails\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of email templates.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $templates = EmailTemplate::orderBy('name')->get();

        return view('laravel-emails::templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new email template.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('laravel-emails::templates.create');
    }

    /**
     * Store a newly created email template.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:email_templates,slug',
            'description' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'variables' => 'nullable|string',
            'is_active' => 'boolean',
            'category' => 'nullable|string|max:255',
            'layout_id' => 'nullable|exists:email_layouts,id',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Convert variables string to JSON
        if (! empty($validated['variables'])) {
            $variableLines = explode("\n", $validated['variables']);
            $variables = [];

            foreach ($variableLines as $line) {
                $line = trim($line);
                if (! empty($line)) {
                    $variables[] = $line;
                }
            }

            $validated['variables'] = $variables;
        } else {
            $validated['variables'] = [];
        }

        // Set auth user ID if available
        if (auth()->check()) {
            $validated['created_by'] = auth()->id();
        }

        // Create template
        $template = EmailTemplate::create($validated);

        // Fire event
        event(new EmailTemplateCreated($template));

        return redirect()->route('laravel-emails.templates.index')
            ->with('success', 'Email template created successfully');
    }

    /**
     * Display the specified email template.
     *
     * @return \Illuminate\View\View
     */
    public function show(EmailTemplate $template)
    {
        return view('laravel-emails::templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified email template.
     *
     * @return \Illuminate\View\View
     */
    public function edit(EmailTemplate $template)
    {
        // Convert JSON variables to string for form display
        $variablesString = '';
        if (! empty($template->variables) && is_array($template->variables)) {
            $variablesString = implode("\n", $template->variables);
        }

        return view('laravel-emails::templates.edit', compact('template', 'variablesString'));
    }

    /**
     * Update the specified email template.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, EmailTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:email_templates,slug,'.$template->id,
            'description' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'variables' => 'nullable|string',
            'is_active' => 'boolean',
            'category' => 'nullable|string|max:255',
            'layout_id' => 'nullable|exists:email_layouts,id',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Convert variables string to JSON
        if (! empty($validated['variables'])) {
            $variableLines = explode("\n", $validated['variables']);
            $variables = [];

            foreach ($variableLines as $line) {
                $line = trim($line);
                if (! empty($line)) {
                    $variables[] = $line;
                }
            }

            $validated['variables'] = $variables;
        } else {
            $validated['variables'] = [];
        }

        // Set auth user ID if available
        if (auth()->check()) {
            $validated['updated_by'] = auth()->id();
        }

        // Update template
        $template->update($validated);

        // Fire event
        event(new EmailTemplateUpdated($template));

        return redirect()->route('laravel-emails.templates.index')
            ->with('success', 'Email template updated successfully');
    }

    /**
     * Remove the specified email template.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(EmailTemplate $template)
    {
        // Check if this is a system template
        if ($template->is_system) {
            return redirect()->route('laravel-emails.templates.index')
                ->with('error', 'System templates cannot be deleted');
        }

        // Fire event before deletion
        event(new EmailTemplateDeleted($template));

        // Delete template
        $template->delete();

        return redirect()->route('laravel-emails.templates.index')
            ->with('success', 'Email template deleted successfully');
    }

    /**
     * Preview the email template with test data.
     *
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request, EmailTemplate $template)
    {
        $data = $request->input('data', []);

        // Add default variables if needed
        $data = array_merge([
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'name' => 'John Doe',
            'email' => 'example@example.com',
            'company_name' => config('app.name'),
            'company_logo' => 'https://via.placeholder.com/200x50',
            'company_address' => '123 Company St, City, Country',
            'year' => date('Y'),
        ], $data);

        // Add template-specific variables
        if (! empty($template->variables) && is_array($template->variables)) {
            foreach ($template->variables as $variable) {
                if (! isset($data[$variable])) {
                    $data[$variable] = "Sample {$variable}";
                }
            }
        }

        // Add layout-specific variables if a layout is used
        if ($template->layout) {
            if (! empty($template->layout->variables) && is_array($template->layout->variables)) {
                foreach ($template->layout->variables as $variable) {
                    if (! isset($data[$variable])) {
                        $data[$variable] = "Sample {$variable}";
                    }
                }
            }
        }

        // Render template with data
        $content = $template->renderContent($data);
        $subject = $template->renderSubject($data);

        return response()->json([
            'subject' => $subject,
            'content' => $content,
        ]);
    }
}
