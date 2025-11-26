<?php

namespace Corbital\LaravelEmails\Http\Controllers;

use App\Http\Controllers\Controller;
use Corbital\LaravelEmails\Facades\Email;
use Corbital\LaravelEmails\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTestController extends Controller
{
    /**
     * Display the test email form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $templates = EmailTemplate::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('laravel-emails::test.index', compact('templates'));
    }

    /**
     * Send a test email.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'to_email' => 'required|email',
            'to_name' => 'nullable|string|max:255',
            'test_data' => 'nullable|array',
        ]);

        // Get template
        $template = EmailTemplate::findOrFail($validated['template_id']);

        // Prepare test data
        $data = $validated['test_data'] ?? [];

        // Add recipient name if provided
        if (! empty($validated['to_name'])) {
            $data['name'] = $validated['to_name'];
        }

        // Add default system variables
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');

        // Add default values for variables
        if ($template->variables !== null) {
            foreach ($template->variables as $variable) {
                if (! isset($data[$variable])) {
                    $data[$variable] = "Test {$variable}";
                }
            }
        }

        // Send test email
        $result = Email::to($validated['to_email'])
            ->template($template->slug, $data)
            ->test(true)
            ->send();

        if ($result === true) {
            return redirect()->back()
                ->with('success', 'Test email sent successfully');
        } else {
            return redirect()->back()
                ->with('error', 'Failed to send test email: '.$result)
                ->withInput();
        }
    }
}
