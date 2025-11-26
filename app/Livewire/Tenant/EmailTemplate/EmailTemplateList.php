<?php

namespace App\Livewire\Tenant\EmailTemplate;

use App\Models\Tenant\TenantEmailTemplate;
use Livewire\Component;

class EmailTemplateList extends Component
{
    public $templates;

    public function mount()
    {
        if (! checkPermission('tenant.email_template.view')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $this->templates = TenantEmailTemplate::get();
    }

    public function toggleActive($templateId, $activateTemplate)
    {
        $template = TenantEmailTemplate::find($templateId);

        if ($template) {
            $template->update([
                'is_active' => $activateTemplate,
            ]);
        }

        $this->notify(['type' => 'success', 'message' => $activateTemplate ? t('template_activate_successfully') : t('template_deactivate_successfully')]);

        $this->templates = TenantEmailTemplate::get();
    }

    public function render()
    {
        return view('livewire.tenant.email-template.email-template-list');
    }
}
