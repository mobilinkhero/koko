<?php

namespace App\Livewire\Admin\EmailTemplate;

use App\Models\EmailTemplate;
use Corbital\LaravelEmails\Services\MergeFieldsService;
use Livewire\Attributes\Rule;
use Livewire\Component;

class EmailTemplateSave extends Component
{
    public EmailTemplate $emailTemplate;

    public $templateId;

    #[Rule('required|string|max:255')]
    public $name;

    #[Rule('required|string|max:255')]
    public $subject;

    #[Rule('required|string')]
    public $content;

    public $deferLoad = false;

    // Merge fields structure
    public $groupedFields = [];

    public $selectedGroups = [];

    public $layout;

    protected $listeners = ['contentUpdated' => 'handleContentUpdate'];

    // Initialize the component
    public function mount($id = null)
    {
        if (! checkPermission('admin.email_template.edit')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $this->emailTemplate = $id ? EmailTemplate::findOrFail($id) : new EmailTemplate;

        if ($this->emailTemplate->exists) {
            $this->templateId = $id;
            $this->loadTemplateData();
        }
        $template = EmailTemplate::find($id);
        $this->layout = $template->layout;
        $this->loadMergeFields();
    }

    public function loadMergeFields()
    {
        $mergeFieldsService = app(MergeFieldsService::class);

        $slug = $this->emailTemplate->slug ?? null;

        if ($slug) {
            $this->groupedFields = $mergeFieldsService->getGroupedFieldsByTemplateSlug($slug);
        }
    }

    public function insertMergeField($field)
    {
        $this->content .= ' '.$field;

        // Find which group contains this field
        foreach ($this->groupedFields as $group => $fields) {
            if (collect($fields)->contains('key', $field)) {
                if (! in_array($group, $this->selectedGroups)) {
                    $this->selectedGroups[] = $group;
                }
                break;
            }
        }
    }

    // Load template data from the database
    public function loadTemplateData()
    {
        $template = EmailTemplate::find($this->templateId);

        if ($template) {
            $this->name = $template->name;
            $this->subject = $template->subject;
            $this->content = $this->emailTemplate->content;
            $this->selectedGroups = $template->merge_fields_groups ?? [];
        }
    }

    // Handle content update from the Quill editor
    public function handleContentUpdate($content)
    {
        $this->deferLoad = true;
        $this->content = $content;
    }

    public function save()
    {
        if (checkPermission('admin.email_template.edit')) {
            $this->validate();

            try {
                // Find the template
                if ($this->templateId) {
                    $template = EmailTemplate::findOrFail($this->templateId);

                    // Update the template
                    $template->subject = $this->subject;
                    $template->content = $this->content;
                    $template->save();

                    // Success notification
                    $this->notify(['type' => 'success', 'message' => t('email_template_updated_successfully')], true);
                } else {
                    $this->notify(['type' => 'danger', 'message' => t('template_not_found')], true);
                }
            } catch (\Exception $e) {
                app_log('Failed to update email template: '.$e->getMessage(), 'error', $e, [
                    'template_id' => $this->templateId,
                ]);

                // Error notification
                $this->notify(['type' => 'danger', 'message' => t('email_template_update_failed')], true);
            }

            return redirect()->to(route('admin.email-template.list'));
        }
    }

    // Render the component
    public function render()
    {
        return view('livewire.admin.email-template.email-template-save');
    }
}
