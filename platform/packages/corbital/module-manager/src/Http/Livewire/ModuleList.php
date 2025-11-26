<?php

namespace Corbital\ModuleManager\Http\Livewire;

use Corbital\ModuleManager\Facades\ModuleManager;
use Corbital\ModuleManager\Models\Module;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ModuleList extends Component
{
    use WithPagination;

    public $search = '';

    public $sortField = 'name';

    public $sortDirection = 'asc';

    public $perPage = 10;

    public $type = ''; // Filter by module type (core, addon, or all)

    public $status = ''; // Filter by status (active, inactive, or all)

    public $page = 1; // Current page for pagination

    public $overrideStates = []; // Store overridden module states

    // Envato validation modal properties
    public $showEnvatoModal = false;

    public $envatoUsername = '';

    public $envatoPurchaseCode = '';

    public $moduleToActivate = '';

    public $envatoValidationErrors = [];

    public $envatoResponse = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
        'type' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    /**
     * Handle checking for module updates
     *
     * @param  string  $moduleName
     * @return void
     */
    public function checkUpdate($itemId)
    {
        $this->redirect(route('admin.modules.check.update', ['itemId' => $itemId]));
    }

    // Add listeners for filter changes
    protected $listeners = [
        'refresh' => '$refresh',
        'typeChanged' => 'updateType',
        'statusChanged' => 'updateStatus',
        'perPageChanged' => 'updatePerPage',
    ];

    public function mount() {}

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Add handler for when search is actually updated
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updateType($value)
    {
        $this->type = $value;
        $this->resetPage();
    }

    public function updateStatus($value)
    {
        $this->status = $value;
        $this->resetPage();
    }

    public function updatePerPage($value)
    {
        $this->perPage = $value;
        $this->resetPage();
    }

    // For direct wire:model.live updates
    public function updatedType()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function activateModule($name)
    {
        // Check if module requires Envato validation
        $hooksService = app('module.hooks');
        if ($hooksService->requiresEnvatoValidation($name)) {
            // Show the Envato validation modal
            $this->moduleToActivate = $name;
            $this->showEnvatoModal = true;
            $this->envatoResponse = '';
            $this->envatoUsername = '';
            $this->envatoPurchaseCode = '';

            return;
        }

        // Direct activation for core modules or modules that don't require validation
        $this->performModuleActivation($name);
    }

    public function performModuleActivation($name, $validationData = [])
    {
        $response = ModuleManager::activate($name, [], $validationData);

        return $response;
    }

    protected function rules()
    {
        return [
            'envatoUsername' => ['required', 'string', 'max:255'],
            'envatoPurchaseCode' => ['required', 'string', 'regex:/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i'],
        ];
    }

    protected function messages()
    {
        return [
            'envatoUsername.required' => 'Envato username is required.',
            'envatoPurchaseCode.required' => 'Purchase code is required.',
            'envatoPurchaseCode.regex' => 'Invalid purchase code format. It should be in the format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        ];
    }

    public function validateAndActivateModule()
    {
        $validatedData = $this->validate();
        // Prepare validation data
        $validationData = [
            'envato_username' => $this->envatoUsername,
            'envato_purchase_code' => $this->envatoPurchaseCode,
        ];

        // Attempt to activate the module with validation
        $result = $this->performModuleActivation($this->moduleToActivate, $validationData);

        $this->envatoResponse = $result;
        if ($result['success']) {
            $this->notify([
                'type' => 'success',
                'message' => $result['message'],
            ]);
            $this->closeEnvatoModal();
        }
    }

    public function closeEnvatoModal()
    {
        $this->showEnvatoModal = false;
        $this->envatoUsername = '';
        $this->envatoPurchaseCode = '';
        $this->moduleToActivate = '';
        $this->envatoValidationErrors = [];
        $this->envatoResponse = '';
    }

    public function deactivateModule($name)
    {
        $response = ModuleManager::deactivate($name);
        $this->notify([
            'type' => ($response['success']) ? 'success' : 'danger',
            'message' => $response['message'],
        ]);
    }

    public function removeModule($name)
    {
        $response = ModuleManager::remove($name);
        $this->notify([
            'type' => ($response['success']) ? 'success' : 'danger',
            'message' => $response['message'],
        ]);
    }

    /**
     * Go to the next page.
     *
     * @return void
     */
    public function nextPage()
    {
        $total = Collection::make(ModuleManager::all())->count();
        $lastPage = max(ceil($total / $this->perPage), 1);

        if ($this->page < $lastPage) {
            $this->page = $this->page + 1;
        }
    }

    /**
     * Go to the previous page.
     *
     * @return void
     */
    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page = $this->page - 1;
        }
    }

    /**
     * Reset page to 1.
     *
     * @return void
     */
    public function resetPage()
    {
        $this->page = 1;
    }

    protected function getSortValue($module, $field)
    {
        switch ($field) {
            case 'name':
                return $module['name'] ?? '';
            case 'version':
                return $module['info']['version'] ?? '0.0.0';
            case 'author':
                return strtolower($module['info']['author'] ?? '');
            case 'type':
                return $module['info']['type'] ?? 'addon';
            case 'status':
                return $module['active'] ? 'a' : 'z'; // 'a' for active to sort first, 'z' for inactive
            default:
                return $module['name'] ?? '';
        }
    }

    public function upgradeVersion($item_id, $version)
    {
        $module = get_module($item_id);
        Module::where('item_id', $item_id)->update(['version' => $version]);
        ModuleManager::runModuleMigrations($module['name']);
        ModuleManager::runModuleSeeders($module['name']);

        $this->notify([
            'type' => 'success',
            'message' => t('database_upgraded_successfully'),
        ]);
    }

    public function render()
    {
        // Get all modules
        $allModules = ModuleManager::all();
        // Convert to collection for easier filtering and sorting
        $modules = Collection::make($allModules);

        // Apply search filter
        if (! empty($this->search)) {
            $modules = $modules->filter(function ($module, $name) {
                $searchLower = strtolower($this->search);
                $nameMatches = stripos($name, $this->search) !== false;
                $descriptionMatches = isset($module['info']['description']) ?
                    stripos($module['info']['description'], $this->search) !== false : false;
                $authorMatches = isset($module['info']['author']) ?
                    stripos($module['info']['author'], $this->search) !== false : false;

                return $nameMatches || $descriptionMatches || $authorMatches;
            });
        }

        // Apply type filter
        if (! empty($this->type)) {
            $modules = $modules->filter(function ($module, $name) {
                if ($this->type === 'core') {
                    return isset($module['info']['type']) && $module['info']['type'] === 'core';
                } elseif ($this->type === 'addon') {
                    return ! isset($module['info']['type']) || $module['info']['type'] === 'addon';
                } elseif ($this->type === 'custom') {
                    return isset($module['info']['type']) && $module['info']['type'] === 'custom';
                }

                return true;
            });
        }

        // Apply status filter
        if (! empty($this->status)) {
            $modules = $modules->filter(function ($module) {
                if ($this->status === 'active') {
                    return $module['active'];
                } elseif ($this->status === 'inactive') {
                    return ! $module['active'];
                }

                return true;
            });
        }

        // Apply sorting
        $modules = $modules->sort(function ($a, $b) {
            $valueA = $this->getSortValue($a, $this->sortField);
            $valueB = $this->getSortValue($b, $this->sortField);

            if ($valueA == $valueB) {
                return 0;
            }

            $comparison = $valueA <=> $valueB;

            return $this->sortDirection === 'asc' ? $comparison : -$comparison;
        });

        // Create proper paginator for modules
        $page = $this->page ?? 1;
        $total = $modules->count();
        $perPage = intval($this->perPage); // Ensure perPage is an integer
        $lastPage = max(ceil($total / $perPage), 1);

        // Clamp the current page
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        // Slice the collection for pagination
        $paginatedModules = $modules->slice(($page - 1) * $perPage, $perPage)->values();

        // Use the paginator data for view
        return view('modules::livewire.module-list', [
            'modules' => $paginatedModules,
            'totalModules' => $total,
            'lastPage' => $lastPage,
            'currentPage' => $page,
            'firstItem' => $total ? (($page - 1) * $perPage) + 1 : 0,
            'lastItem' => min($total, $page * $perPage),
        ]);
    }
}
