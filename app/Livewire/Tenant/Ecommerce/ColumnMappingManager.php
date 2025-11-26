<?php

namespace App\Livewire\Tenant\Ecommerce;

use Livewire\Component;
use App\Services\GoogleSheetsService;
use App\Services\DynamicSheetMapperService;
use App\Models\Tenant\TenantSheetConfiguration;

/**
 * Column Mapping Manager
 * 
 * Allows tenants to view and manage their Google Sheets column mappings
 */
class ColumnMappingManager extends Component
{
    public $configuration;
    public $isConfigured = false;
    public $detectedColumns = [];
    public $columnMapping = [];
    public $customFields = [];
    public $hasRequiredFields = false;
    
    public $showMappingModal = false;
    public $editingColumn = '';
    public $editingField = '';

    protected $listeners = ['refreshMapping' => 'loadConfiguration'];

    public function mount()
    {
        $this->loadConfiguration();
    }

    public function loadConfiguration()
    {
        $mapper = new DynamicSheetMapperService(tenant_id(), 'products');
        $summary = $mapper->getConfigurationSummary();
        
        $this->isConfigured = $summary['is_configured'];
        $this->detectedColumns = $summary['detected_columns'] ?? [];
        $this->columnMapping = $summary['column_mapping'] ?? [];
        $this->customFields = $summary['custom_fields'] ?? [];
        $this->hasRequiredFields = $summary['has_required_fields'];
        
        $this->configuration = $mapper->getConfiguration();
    }

    public function resetDetection()
    {
        try {
            $mapper = new DynamicSheetMapperService(tenant_id(), 'products');
            $mapper->resetConfiguration();
            
            $this->notify([
                'type' => 'success',
                'message' => 'Column detection reset successfully. Sync products again to re-detect columns.'
            ]);
            
            $this->loadConfiguration();
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'error',
                'message' => 'Failed to reset detection: ' . $e->getMessage()
            ]);
        }
    }

    public function syncProducts()
    {
        try {
            $sheetsService = new GoogleSheetsService();
            $result = $sheetsService->syncProductsFromSheets();
            
            if ($result['success']) {
                $this->notify([
                    'type' => 'success',
                    'message' => $result['message']
                ]);
                
                $this->loadConfiguration();
            } else {
                $this->notify([
                    'type' => 'error',
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'error',
                'message' => 'Sync failed: ' . $e->getMessage()
            ]);
        }
    }

    public function openMappingModal($column)
    {
        $this->editingColumn = $column;
        $this->editingField = $this->columnMapping[$column] ?? '';
        $this->showMappingModal = true;
    }

    public function updateMapping()
    {
        try {
            $newMapping = $this->columnMapping;
            $newMapping[$this->editingColumn] = $this->editingField;
            
            $mapper = new DynamicSheetMapperService(tenant_id(), 'products');
            $success = $mapper->updateMapping($newMapping);
            
            if ($success) {
                $this->notify([
                    'type' => 'success',
                    'message' => 'Column mapping updated successfully'
                ]);
                
                $this->showMappingModal = false;
                $this->loadConfiguration();
            } else {
                $this->notify([
                    'type' => 'error',
                    'message' => 'Failed to update mapping'
                ]);
            }
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'error',
                'message' => 'Update failed: ' . $e->getMessage()
            ]);
        }
    }

    public function notify($data)
    {
        $this->dispatch('notify', $data);
    }

    public function render()
    {
        return view('livewire.tenant.ecommerce.column-mapping-manager');
    }
}
