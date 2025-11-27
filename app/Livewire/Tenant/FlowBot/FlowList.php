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
                'unique:sources,name,'.($this->botFlow->id ?? 'NULL'),
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

    public function createBotFlow()
    {
        $this->resetForm();
        $this->showFlowModal = true;
    }

    public function save()
    {
        $this->validate();

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
