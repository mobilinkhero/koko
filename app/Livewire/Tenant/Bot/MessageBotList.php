<?php

namespace App\Livewire\Tenant\Bot;

use App\Models\Tenant\MessageBot;
use App\Services\FeatureService;
use Livewire\Component;

class MessageBotList extends Component
{
    public $confirmingDeletion = false;

    public $messagebotId = null;

    protected $featureLimitChecker;

    protected $listeners = [
        'confirmDelete' => 'confirmDelete',
    ];

    public function mount()
    {
        if (! checkPermission(['tenant.message_bot.view'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    public function confirmDelete($messagebotId)
    {
        $this->messagebotId = $messagebotId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission(['tenant.message_bot.delete'])) {
            $messageBot = MessageBot::findOrFail($this->messagebotId);
            $files = storage_path('/app/public/'.$messageBot->filename);
            if (is_file($files)) {
                unlink($files);
            }
            $messageBot->delete();
            $this->confirmingDeletion = false;
            $this->notify(['type' => 'success', 'message' => t('delete_message_bot_successfully')]);
            $this->dispatch('pg:eventRefresh-message-bot-table-hb8oye-table');
        }
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('message_bots', MessageBot::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('message_bots', MessageBot::class);
    }

    public function getTotalLimitProperty()
    {
        return $this->featureLimitChecker->getLimit('message_bots');
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-message-bot-table-hb8oye-table');
    }

    public function render()
    {
        return view('livewire.tenant.bot.message-bot-list');
    }
}
