<?php

namespace App\Livewire\Tenant\EmailTemplate;

use App\Models\Tenant\TenantEmailTemplate;
use Corbital\LaravelEmails\Services\MergeFieldsService;
use Livewire\Attributes\Rule;
use Livewire\Component;

class EmailTemplateSave extends Component
{
    public TenantEmailTemplate $emailTemplate;

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

    public function mount($id = null)
    {
        if (! checkPermission('tenant.email_template.edit')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        if (! $id) {
            $this->notify(['type' => 'danger', 'message' => t('email_template_not_exist')], true);

            return $this->redirect(tenant_route('tenant.emails'));
        }

        $template = TenantEmailTemplate::find($id);
        $this->layout = $template->layout_id;

        if (! $template) {
            $this->notify(['type' => 'danger', 'message' => t('email_template_not_exist')], true);

            return $this->redirect(tenant_route('tenant.emails'));
        }

        $this->emailTemplate = $template;

        if ($this->emailTemplate->exists) {
            $this->templateId = $id;
            $this->loadTemplateData();
        }

        $this->loadMergeFields();
    }

    public function loadTemplateData()
    {
        $template = TenantEmailTemplate::find($this->templateId);

        if ($template) {
            $this->name = $template->name;
            $this->subject = $template->subject;
            $this->content = $template->content;
            $this->selectedGroups = $template->merge_fields_groups ?? [];
        }
    }

    public function loadMergeFields()
    {
        $mergeFieldsService = app(MergeFieldsService::class);

        $slug = $this->emailTemplate->slug ?? null;

        if ($slug) {
            $this->groupedFields = $mergeFieldsService->getGroupedFieldsByTemplateSlug($slug, 'tenant_email_templates');
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

    public function save()
    {
        if (checkPermission('tenant.email_template.edit')) {
            $this->validate();

            try {

                $template = TenantEmailTemplate::find($this->templateId);
                $template->subject = $this->subject;
                $template->content = $this->content;

                if ($template->isDirty()) {
                    $template->save();
                    $this->notify(['type' => 'success', 'message' => t('email_template_updated_successfully')], true);
                }

                return $this->redirect(tenant_route('tenant.emails'));
            } catch (\Exception $e) {
                app_log('Failed to update email template: '.$e->getMessage(), 'error', $e, [
                    'template_id' => $this->templateId,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('email_template_update_failed')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.tenant.email-template.email-template-save');
    }
}
