<?php

namespace App\Livewire\Tenant\FlowBot;

use App\Models\Tenant\BotFlow;
use App\Rules\PurifiedInput;
use App\Services\FeatureService;
use Livewire\Component;
use Livewire\WithPagination;

class FlowList extends Component
{
    use WithPagination;

    public BotFlow $botFlow;

    public $showFlowModal = false;

    public $confirmingDeletion = false;

    protected $featureLimitChecker;

    public $botFlowId = null;

    protected $listeners = [
        'editFlow' => 'editFlow',
        'confirmDelete' => 'confirmDelete',
        'editRedirect' => 'editRedirect',
    ];

    public $tenant_id;

    public function mount()
    {
        if (! checkPermission(['tenant.bot_flow.view', 'tenant.bot_flow.create'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }
        $this->resetForm();
        $this->botFlow = new BotFlow;
        $this->tenant_id = tenant_id();
    }

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    protected function rules()
    {
        return []; // No automatic Livewire validation - we'll handle manually
    }
    
    /**
     * Livewire hook: Called when botFlow.name is updated
     */
    public function updatedBotFlowName($value)
    {
        // Clear validation error for name field when user types
        $this->resetErrorBag('botFlow.name');
    }
    
    /**
     * Livewire hook: Called when botFlow.description is updated
     */
    public function updatedBotFlowDescription($value)
    {
        // Clear validation error for description field when user types
        $this->resetErrorBag('botFlow.description');
    }

    public function createBotFlow()
    {
        $this->resetForm();
        $this->resetValidation();
        $this->showFlowModal = true;
    }

    public function save()
    {
        // Debug: Log what we're working with
        \Log::info('🔵 MANUAL VALIDATION START', [
            'botFlow_name' => $this->botFlow->name,
            'botFlow_name_type' => gettype($this->botFlow->name),
            'botFlow_name_is_null' => is_null($this->botFlow->name),
            'botFlow_name_is_empty' => empty($this->botFlow->name),
            'botFlow_name_trimmed' => trim($this->botFlow->name ?? ''),
            'botFlow_exists' => $this->botFlow->exists,
            'botFlow_id' => $this->botFlow->id,
        ]);
        
        // Manual validation to bypass Livewire's automatic validation
        $errors = [];
        
        // Check name field
        if (empty($this->botFlow->name) || trim($this->botFlow->name) === '') {
            $errors['botFlow.name'] = 'The name field is required.';
            \Log::info('🔴 NAME VALIDATION FAILED - Empty');
        } else {
            // Check unique constraint
            $query = \App\Models\Tenant\BotFlow::where('name', trim($this->botFlow->name))
                ->where('tenant_id', tenant_id());
                
            if ($this->botFlow->exists) {
                $query->where('id', '!=', $this->botFlow->id);
            }
            
            if ($query->exists()) {
                $errors['botFlow.name'] = 'The name has already been taken.';
            }
            
            // Check max length
            if (strlen($this->botFlow->name) > 150) {
                $errors['botFlow.name'] = 'The name may not be greater than 150 characters.';
            }
        }
        
        // Check description field
        if (!empty($this->botFlow->description) && strlen($this->botFlow->description) > 150) {
            $errors['botFlow.description'] = 'The description may not be greater than 150 characters.';
        }
        
        // If there are errors, throw validation exception
        if (!empty($errors)) {
            \Log::info('🔴 VALIDATION FAILED WITH ERRORS', $errors);
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
        
        \Log::info('🟢 VALIDATION PASSED - SAVING BOT FLOW');

        $isNew = ! $this->botFlow->exists;

        // For new records, check if creating one more would exceed the limit
        if ($isNew) {
            $limit = $this->featureLimitChecker->getLimit('bot_flow');

            // Skip limit check if unlimited (-1) or no limit set (null)
            if ($limit !== null && $limit !== -1) {
                $currentCount = BotFlow::where('tenant_id', tenant_id())->count();

                if ($currentCount >= $limit) {
                    $this->showFlowModal = false;
                    // Show upgrade notification
                    $this->notify([
                        'type' => 'warning',
                        'message' => t('bot_flow_limit_reached_message'),
                    ]);

                    return;
                }
            }
        }

        if ($this->botFlow->isDirty()) {
            $this->botFlow->tenant_id = tenant_id();
            if ($isNew) {
                // Only set flow_data to null for completely new flows
                // This ensures new flows start with empty flow data
                $this->botFlow->flow_data = null;
            }
            // For existing flows, preserve the existing flow_data
            $this->botFlow->save();

            if ($isNew) {
                $this->featureLimitChecker->trackUsage('bot_flow');
            }

            $this->showFlowModal = false;

            $message = $this->botFlow->wasRecentlyCreated
                ? t('bot_flow_saved_successfully')
                : t('bot_flow_update_successfully');

            $this->notify(['type' => 'success', 'message' => $message]);
            $this->dispatch('pg:eventRefresh-flow-bot-table-9nci5n-table');
        } else {
            $this->showFlowModal = false;
        }
    }

    public function confirmDelete($flowId)
    {
        $this->botFlowId = $flowId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        $botFlow = BotFlow::find($this->botFlowId);

        if ($botFlow) {
            $botFlow->delete();
        }

        $this->confirmingDeletion = false;
        $this->resetForm();
        $this->botFlowId = null;
        $this->resetPage();

        $this->notify(['type' => 'success', 'message' => t('flow_delete_successfully')]);
        $this->dispatch('pg:eventRefresh-flow-bot-table-9nci5n-table');
    }

    public function editRedirect($flowId)
    {
        return redirect()->to(tenant_route('tenant.bot-flows.edit', [
            'id' => $flowId,
        ]));
    }

    public function editFlow($flowId)
    {
        $source = BotFlow::findOrFail($flowId);
        $this->botFlow = $source;
        $this->resetValidation();
        $this->showFlowModal = true;
    }

    private function resetForm()
    {
        // Do NOT use $this->reset() as it can make botFlow null and cause JS errors
        $this->showFlowModal = false;
        $this->confirmingDeletion = false;
        $this->botFlowId = null;
        $this->resetValidation();
        
        // Always ensure botFlow is a valid object
        $this->botFlow = new BotFlow();
        $this->botFlow->tenant_id = tenant_id();
        
        // Initialize properties to empty strings to prevent null issues on frontend
        $this->botFlow->name = '';
        $this->botFlow->description = '';
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('bot_flow', BotFlow::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('bot_flow', BotFlow::class);
    }

    public function getTotalLimitProperty()
    {
        return $this->featureLimitChecker->getLimit('bot_flow');
    }

    public function render()
    {
        return view('livewire.tenant.flow-bot.flow-list');
    }
}
