<?php

namespace App\Livewire\Tenant\Chat;

use App\Models\Tenant\AiPrompt;
use App\Rules\PurifiedInput;
use App\Services\FeatureService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageAiPrompt extends Component
{
    use WithPagination;

    public AiPrompt $prompt;

    public $showAiPromptModal = false;

    public $confirmingDeletion = false;

    public $prompt_id = null;

    protected $listeners = [
        'editAiPrompt' => 'editAiPrompt',
        'confirmDelete' => 'confirmDelete',
    ];

    protected $featureLimitChecker;

    public $tenant_id;

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    public function mount()
    {
        if (! checkPermission('tenant.ai_prompt.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $this->resetForm();
        $this->prompt = new AiPrompt;
        $this->tenant_id = tenant_id();

    }

    public function validatePromtAction()
    {
        $this->validate([
            'prompt.action' => ['required', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
        ]);
    }

    protected function rules()
    {
        return [
            'prompt.name' => [
                'required',
                'max:255',
                Rule::unique('ai_prompts', 'name')
                    ->where(function ($query) {
                        return $query->where('tenant_id', tenant_id());
                    })
                    ->ignore($this->prompt->id), // For update case
                new PurifiedInput(t('sql_injection_error')),
            ],
            'prompt.action' => ['required', new PurifiedInput(t('sql_injection_error'))],
        ];
    }

    public function createAiPrompt()
    {
        $this->resetForm();
        $this->showAiPromptModal = true;
    }

    public function save()
    {
        if (checkPermission(['tenant.ai_prompt.create', 'tenant.ai_prompt.edit'])) {
            $this->validate();
            // Check if this is a new prompt being created (not an update)
            $isNew = ! $this->prompt->exists;

            // For new records, check if creating one more would exceed the limit
            if ($isNew) {
                $limit = $this->featureLimitChecker->getLimit('ai_prompts');

                // Skip limit check if unlimited (-1) or no limit set (null)
                if ($limit !== null && $limit !== -1) {
                    $currentCount = AiPrompt::where('tenant_id', tenant_id())->count();

                    if ($currentCount >= $limit) {
                        $this->showAiPromptModal = false;
                        // Show upgrade notification
                        $this->notify([
                            'type' => 'warning',
                            'message' => t('aiprompt_limit_reached_upgrade_plan'),
                        ]);

                        return;
                    }
                }
            }
            if ($this->prompt->isDirty()) {
                $this->prompt->tenant_id = tenant_id();
                $this->prompt->save();

                if ($isNew) {
                    $this->featureLimitChecker->trackUsage('ai_prompts');
                }

                $this->showAiPromptModal = false;

                $message = $this->prompt->wasRecentlyCreated
                    ? t('ai_prompt_saved_successfully')
                    : t('ai_prompt_updated_successfully');

                $this->notify(['type' => 'success', 'message' => $message]);
                $this->dispatch('pg:eventRefresh-ai-prompt-table-9etnvs-table');
            } else {
                $this->showAiPromptModal = false;
            }
        }
    }

    public function editAiPrompt($promptId)
    {
        $prompt = AiPrompt::findOrFail($promptId);
        $this->prompt = $prompt;
        $this->resetValidation();
        $this->showAiPromptModal = true;
    }

    public function confirmDelete($promptId)
    {
        $this->prompt_id = $promptId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('tenant.ai_prompt.delete')) {
            $prompt = AiPrompt::find($this->prompt_id);
            if ($prompt) {
                $prompt->delete();
            }

            $this->confirmingDeletion = false;
            $this->reset();
            $this->prompt_id = null;
            $this->resetPage();

            $this->notify(['type' => 'success', 'message' => t('ai_prompt_delete_successfully')]);
            $this->dispatch('pg:eventRefresh-ai-prompt-table-9etnvs-table');
        }
    }

    private function resetForm()
    {
        $this->resetExcept('ai_prompts');
        $this->resetValidation();
        $this->prompt = new AiPrompt;
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('ai_prompts', AiPrompt::class);
    }

    public function getTotalLimitProperty()
    {
        return $this->featureLimitChecker->getLimit('ai_prompts', AiPrompt::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('ai_prompts', AiPrompt::class);
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-ai-prompt-table-9etnvs-table');
    }

    public function render()
    {
        return view('livewire.tenant.chat.manage-ai-prompt');
    }
}
