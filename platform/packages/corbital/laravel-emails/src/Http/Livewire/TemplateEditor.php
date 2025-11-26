<?php

namespace Corbital\LaravelEmails\Http\Livewire;

use Corbital\LaravelEmails\Models\EmailTemplate;
use Corbital\LaravelEmails\Services\TemplateRenderer;
use Illuminate\Support\Str;
use Livewire\Component;

class TemplateEditor extends Component
{
    /**
     * The template model instance.
     *
     * @var EmailTemplate
     */
    public $template;

    /**
     * The form data.
     *
     * @var array
     */
    public $form = [
        'name' => '',
        'slug' => '',
        'description' => '',
        'subject' => '',
        'content' => '',
        'variables' => '',
        'is_active' => true,
        'category' => '',
    ];

    /**
     * The detected template variables.
     *
     * @var array
     */
    public $detectedVariables = [];

    /**
     * Preview data.
     *
     * @var array
     */
    public $previewData = [];

    /**
     * Preview active state.
     *
     * @var bool
     */
    public $previewActive = false;

    /**
     * Preview HTML content.
     *
     * @var string
     */
    public $previewHtml = '';

    /**
     * Preview subject.
     *
     * @var string
     */
    public $previewSubject = '';

    /**
     * Validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $rules = [
            'form.name' => 'required|string|max:255',
            'form.subject' => 'required|string|max:255',
            'form.content' => 'required|string',
            'form.is_active' => 'boolean',
            'form.description' => 'nullable|string',
            'form.category' => 'nullable|string|max:255',
        ];

        // For edit mode, we need to ignore the current template's slug
        if ($this->template && $this->template->exists) {
            $rules['form.slug'] = 'nullable|string|max:255|unique:email_templates,slug,'.$this->template->id;
        } else {
            $rules['form.slug'] = 'nullable|string|max:255|unique:email_templates,slug';
        }

        return $rules;
    }

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount(?EmailTemplate $template = null)
    {
        $this->template = $template ?? new EmailTemplate;

        if ($this->template->exists) {
            $this->form = [
                'name' => $this->template->name,
                'slug' => $this->template->slug,
                'description' => $this->template->description ?? '',
                'subject' => $this->template->subject,
                'content' => $this->template->content,
                'is_active' => $this->template->is_active,
                'category' => $this->template->category ?? '',
            ];

            // Convert JSON variables to string
            if (! empty($this->template->variables) && is_array($this->template->variables)) {
                $this->form['variables'] = implode("\n", $this->template->variables);
            }
        }

        $this->detectVariables();
        $this->initializePreviewData();
    }

    /**
     * Auto-generate slug from name.
     *
     * @return void
     */
    public function updatedFormName()
    {
        if (empty($this->form['slug'])) {
            $this->form['slug'] = Str::slug($this->form['name']);
        }
    }

    /**
     * Detect variables in the template content and subject.
     *
     * @return void
     */
    public function detectVariables()
    {
        if (empty($this->form['content']) && empty($this->form['subject'])) {
            $this->detectedVariables = [];

            return;
        }

        $renderer = app(TemplateRenderer::class);

        // Extract variables from content
        $contentVariables = $renderer->extractVariables($this->form['content'] ?? '');

        // Extract variables from subject
        $subjectVariables = $renderer->extractVariables($this->form['subject'] ?? '');

        // Merge and make unique
        $this->detectedVariables = array_unique(array_merge($contentVariables, $subjectVariables));
    }

    /**
     * Initialize preview data with default values.
     *
     * @return void
     */
    public function initializePreviewData()
    {
        $this->previewData = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        // Add custom variables with placeholder values
        foreach ($this->detectedVariables as $variable) {
            if (! isset($this->previewData[$variable])) {
                $this->previewData[$variable] = 'Example '.ucfirst($variable);
            }
        }
    }

    /**
     * Generate preview of the template.
     *
     * @return void
     */
    public function generatePreview()
    {
        $renderer = app(TemplateRenderer::class);

        try {
            $this->previewHtml = $renderer->render($this->form['content'], $this->previewData);
            $this->previewSubject = $renderer->render($this->form['subject'], $this->previewData);
            $this->previewActive = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating preview: '.$e->getMessage());
        }
    }

    /**
     * Save the template.
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        // Prepare variables array
        $variables = [];
        if (! empty($this->form['variables'])) {
            $variableLines = explode("\n", $this->form['variables']);
            foreach ($variableLines as $line) {
                $line = trim($line);
                if (! empty($line)) {
                    $variables[] = $line;
                }
            }
        }

        // Update or create the template
        $this->template->fill([
            'name' => $this->form['name'],
            'slug' => $this->form['slug'] ?: Str::slug($this->form['name']),
            'description' => $this->form['description'],
            'subject' => $this->form['subject'],
            'content' => $this->form['content'],
            'variables' => $variables,
            'is_active' => $this->form['is_active'],
            'category' => $this->form['category'],
        ]);

        // Set auth user ID if available
        if (auth()->check()) {
            if ($this->template->exists) {
                $this->template->updated_by = auth()->id();
            } else {
                $this->template->created_by = auth()->id();
            }
        }

        $this->template->save();

        // Dispatch the appropriate event
        if ($this->template->wasRecentlyCreated) {
            event(new \Corbital\LaravelEmails\Events\EmailTemplateCreated($this->template));
            session()->flash('success', 'Email template created successfully.');
        } else {
            event(new \Corbital\LaravelEmails\Events\EmailTemplateUpdated($this->template));
            session()->flash('success', 'Email template updated successfully.');
        }

        // Redirect to the templates listing
        $this->redirect(route('laravel-emails.templates.index'));
    }

    /**
     * Update content for live preview.
     *
     * @return void
     */
    public function updatedFormContent()
    {
        $this->detectVariables();
        if ($this->previewActive) {
            $this->generatePreview();
        }
    }

    /**
     * Update subject for live preview.
     *
     * @return void
     */
    public function updatedFormSubject()
    {
        $this->detectVariables();
        if ($this->previewActive) {
            $this->generatePreview();
        }
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('laravel-emails::livewire.template-editor');
    }
}
