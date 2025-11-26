<?php

namespace App\Livewire\Tenant\Template;

use App\Models\Tenant\WhatsappTemplate;
use App\Traits\WhatsApp;
use Livewire\Attributes\On;
use Livewire\Component;

class TemplateList extends Component
{
    use WhatsApp;

    public $showDeleteConfirmation = false;

    public $templateToDelete = null;

    public $deleteInProgress = false;

    public function mount()
    {
        if (! checkPermission('tenant.template.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function loadTemplate()
    {
        if (checkPermission('tenant.template.load_template')) {
            try {
                $response = $this->loadTemplatesFromWhatsApp();
                $this->notify([
                    'message' => $response['message'],
                    'type' => $response['status'] ? 'success' : 'danger',
                ]);

                $this->dispatch('pg:eventRefresh-whatspp-template-table-sgz2iu-table', [], 'window');
            } catch (\Exception $e) {
                whatsapp_log('Error loading WhatsApp templates: '.$e->getMessage(), 'error', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], $e);

                $this->notify([
                    'message' => t('template_load_failed').': '.$e->getMessage(),
                    'type' => 'danger',
                ]);
            }
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-whatspp-template-table-sgz2iu-table');
    }

    /**
     * Show delete confirmation modal
     */
    #[On('showDeleteConfirmation')]
    public function showDeleteConfirmation($templateId, $templateName, $templateMetaId)
    {
        if (! checkPermission('tenant.template.delete')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')]);

            return;
        }

        $this->templateToDelete = [
            'id' => $templateId,
            'name' => $templateName,
            'meta_id' => $templateMetaId,
        ];
        $this->showDeleteConfirmation = true;
    }

    #[On('showEditPage')]
    public function showEditPage($templateId)
    {
        if (! checkPermission('tenant.template.edit')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')]);

            return;
        }

        return redirect()->to(tenant_route('tenant.dynamic-template.show', [
            'id' => $templateId,
        ]));
    }

    /**
     * Cancel deletion
     */
    public function cancelDelete()
    {
        $this->showDeleteConfirmation = false;
        $this->templateToDelete = null;
        $this->deleteInProgress = false;
    }

    /**
     * Confirm and execute template deletion
     */
    public function confirmDelete()
    {
        if (! $this->templateToDelete || ! checkPermission('tenant.template.delete')) {
            $this->cancelDelete();

            return;
        }

        $this->deleteInProgress = true;

        try {
            // Find the template
            $template = WhatsappTemplate::where('id', $this->templateToDelete['id'])
                ->where('tenant_id', tenant_id())
                ->first();

            if (! $template) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('template_not_found'),
                ]);
                $this->cancelDelete();

                return;
            }

            // Delete from Meta and database
            $result = $this->deleteTemplate($template->template_name, $template->template_id);

            if ($result['status']) {
                $this->notify([
                    'type' => 'success',
                    'message' => $result['message'],
                ]);

                // Refresh the table
                $this->refreshTable();
            } else {
                $this->notify([
                    'type' => 'danger',
                    'message' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            whatsapp_log('Template deletion error', 'error', [
                'template_id' => $this->templateToDelete['id'],
                'template_name' => $this->templateToDelete['name'],
                'error' => $e->getMessage(),
                'tenant_id' => tenant_id(),
            ], $e);

            $this->notify([
                'type' => 'danger',
                'message' => t('template_deletion_failed').': '.$e->getMessage(),
            ]);
        } finally {
            $this->cancelDelete();
        }
    }

    public function render()
    {
        return view('livewire.tenant.template.template-list');
    }
}
