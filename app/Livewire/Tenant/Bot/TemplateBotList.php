<?php

namespace App\Livewire\Tenant\Bot;

use App\Models\Tenant\TemplateBot;
use App\Services\FeatureService;
use Livewire\Component;

class TemplateBotList extends Component
{
    public $confirmingDeletion = false;

    public $templatebotId = null;

    protected $featureLimitChecker;

    protected $listeners = [
        'confirmDelete' => 'confirmDelete',
    ];

    public function mount()
    {
        if (! checkPermission(['tenant.template_bot.view'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    public function confirmDelete($templatebotId)
    {
        $this->templatebotId = $templatebotId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission(['tenant.template_bot.delete'])) {
            TemplateBot::findOrFail($this->templatebotId)->delete();
            $this->confirmingDeletion = false;
            $this->notify(['type' => 'success', 'message' => t('template_bot_delete_successfully')]);
            $this->dispatch('pg:eventRefresh-template-bot-table');
        }
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('template_bots', TemplateBot::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('template_bots', TemplateBot::class);
    }

    public function getTotalLimitProperty()
    {
        return $this->featureLimitChecker->getLimit('template_bots');
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-template-bot-table');
    }

    public function render()
    {
        return view('livewire.tenant.bot.template-bot-list');
    }
}
