<?php

namespace Corbital\LaravelEmails\Http\Controllers;

use Corbital\LaravelEmails\Models\EmailTemplate;
use Corbital\LaravelEmails\TemplatePreview;
use Illuminate\Http\Request;

class TemplatePreviewController
{
    /**
     * Preview an email template
     *
     * @param  string  $slug  The template slug
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request, $slug)
    {
        // Get sample data from request or use defaults
        $data = $request->input('data', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'company' => 'ACME Inc',
            'url' => 'https://example.com/verify',
            'link' => 'https://example.com/action',
            'product' => 'Laravel Emails',
            'year' => date('Y'),
        ]);

        try {
            $html = TemplatePreview::render($slug, $data);

            return response($html);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Send a test email
     *
     * @param  string  $slug  The template slug
     * @return \Illuminate\Http\Response
     */
    public function sendTest(Request $request, $slug)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $data = $request->input('data', [
            'name' => 'John Doe',
            'email' => $email,
            'company' => 'ACME Inc',
            'url' => 'https://example.com/verify',
            'link' => 'https://example.com/action',
            'product' => 'Laravel Emails',
            'year' => date('Y'),
        ]);

        $success = TemplatePreview::sendTest($slug, $email, $data);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Test email sent successfully' : 'Failed to send test email',
        ], $success ? 200 : 500);
    }

    /**
     * List available templates
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $templates = EmailTemplate::select(['id', 'name', 'slug', 'is_active'])
            ->orderBy('name')
            ->get();

        return response()->json($templates);
    }
}
