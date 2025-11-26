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
        return [
            'botFlow.name' => [
                'required',
                'unique:bot_flows,name,'.($this->botFlow->id ?? 'NULL').',id,tenant_id,'.tenant_id(),
                new PurifiedInput(t('sql_injection_error')),
                'max:150',
            ],
            'botFlow.description' => [
                'nullable',
                new PurifiedInput(t('sql_injection_error')),
                'max:150',
            ],
        ];
    }
    
    /**
     * Livewire hook: Called when botFlow.name is updated
     */
    public function updatedBotFlowName($value)
    {
        \Log::info('🟡 BOT FLOW NAME UPDATED', [
            'new_value' => $value,
            'length' => strlen($value ?? ''),
            'is_empty' => empty($value),
            'is_null' => is_null($value),
            'botFlow_exists' => $this->botFlow->exists,
        ]);
        
        // Clear validation error for name field when user types
        $this->resetValidation('botFlow.name');
    }

    public function createBotFlow()
    {
        \Log::info('🟢 CREATE BOT FLOW - Modal Opening', [
            'tenant_id' => tenant_id(),
            'botFlow_exists' => $this->botFlow->exists ?? false,
            'botFlow_name_before_reset' => $this->botFlow->name ?? 'null',
        ]);
        
        $this->resetForm();
        $this->resetValidation();
        
        \Log::info('🟢 CREATE BOT FLOW - After Reset', [
            'botFlow_name' => $this->botFlow->name ?? 'null',
            'botFlow_exists' => $this->botFlow->exists,
        ]);
        
        $this->showFlowModal = true;
    }

    public function save()
    {
        \Log::info('🔵 SAVE BOT FLOW - Validation Starting', [
            'tenant_id' => tenant_id(),
            'botFlow_name' => $this->botFlow->name ?? 'null',
            'botFlow_description' => $this->botFlow->description ?? 'null',
            'botFlow_exists' => $this->botFlow->exists,
            'botFlow_id' => $this->botFlow->id ?? 'null',
        ]);
        
        try {
            $this->validate();
            \Log::info('🟢 SAVE BOT FLOW - Validation Passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('🔴 SAVE BOT FLOW - Validation Failed', [
                'errors' => $e->errors(),
                'botFlow_name_value' => $this->botFlow->name ?? 'null',
                'botFlow_object' => $this->botFlow->toArray(),
            ]);
            throw $e;
        }

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
        $this->reset();
        $this->resetValidation();
        $this->botFlow = new BotFlow;
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
