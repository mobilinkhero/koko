<?php

namespace App\Livewire\Tenant\Contact;

use App\Models\Tenant\ContactImport;
use App\Traits\WithTenantContext;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ImportLogs extends Component
{
    use WithTenantContext;

    public $selectedImport = null;

    public $showDetailsModal = false;

    public $showErrorModal = false;

    public $confirmingDeletion = false;

    public $errorMessages = null;

    public $importIdToDelete = null;

    protected $listeners = [
        'refreshImportLogs' => '$refresh',
        'showImportDetails' => 'showImportDetails',
        'retryImport' => 'retryImport',
        'confirmDeleteImport' => 'confirmDeleteImport',
        'refreshImportLogsTable' => 'refreshTable',
        'downloadFile' => 'downloadFile',
    ];

    public function boot()
    {
        $this->bootWithTenantContext();
    }

    public function mount()
    {
        if (! checkPermission(['tenant.contact.view', 'tenant.contact.create'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function showImportDetails($importId)
    {
        $this->selectedImport = ContactImport::where('tenant_id', tenant_id())
            ->findOrFail($importId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedImport = null;
    }

    public function showErrorsModal()
    {
        if ($this->selectedImport) {
            $import = ContactImport::where('tenant_id', tenant_id())
                ->findOrFail($this->selectedImport->id);
            $this->errorMessages = $import->error_messages;
        }
        $this->showErrorModal = true;
    }

    public function closeErrorModal()
    {
        $this->showErrorModal = false;
        $this->errorMessages = null;
    }

    public function retryImport($importId)
    {
        $import = ContactImport::where('tenant_id', tenant_id())->findOrFail($importId);

        if ($import->status === ContactImport::STATUS_FAILED) {
            // Reset import status and dispatch new jobs
            $import->update([
                'status' => ContactImport::STATUS_PROCESSING,
                'processed_records' => 0,
                'valid_records' => 0,
                'invalid_records' => 0,
                'skipped_records' => 0,
                'error_messages' => null,
            ]);

            // Re-queue the import batches
            $batchSize = 100;
            $offset = 0;
            while ($offset < $import->total_records) {
                \App\Jobs\ProcessContactImportBatch::dispatch(
                    $import->id,
                    tenant_id(),
                    $offset,
                    $batchSize
                );
                $offset += $batchSize;
            }

            $this->notify([
                'type' => 'success',
                'message' => t('import_retry_success'),
            ]);

            $this->closeDetailsModal();
            $this->refreshTable();
        }
    }

    public function confirmDeleteImport($importId)
    {
        $this->importIdToDelete = $importId;
        $this->confirmingDeletion = true;
    }

    public function deleteImport()
    {
        if ($this->importIdToDelete) {
            $import = ContactImport::where('tenant_id', tenant_id())
                ->findOrFail($this->importIdToDelete);

            // Delete the stored CSV file if it exists
            if ($import->file_path && Storage::disk('tenant')->exists($import->file_path)) {
                Storage::disk('tenant')->delete($import->file_path);
            }

            $import->delete();

            $this->notify([
                'type' => 'success',
                'message' => t('import_deleted_success'),
            ]);

            if ($this->selectedImport && $this->selectedImport->id === $this->importIdToDelete) {
                $this->closeDetailsModal();
            }

            $this->confirmingDeletion = false;
            $this->importIdToDelete = null;
            $this->refreshTable();
        }
    }

    public function cancelDeletion()
    {
        $this->confirmingDeletion = false;
        $this->importIdToDelete = null;
    }

    public function refreshTable()
    {
        $this->dispatch('refreshImportLogsTable');
    }

    public function downloadFile($importId)
    {
        $import = ContactImport::where('tenant_id', tenant_id())->findOrFail($importId);

        if (! $import->file_path || ! Storage::disk('tenant')->exists($import->file_path)) {
            $this->notify([
                'type' => 'error',
                'message' => t('file_not_found'),
            ]);

            return;
        }

        return response()->download(
            Storage::disk('tenant')->path($import->file_path),
            basename($import->file_path)
        );
    }

    public function render()
    {
        return view('livewire.tenant.contact.import-logs');
    }
}
