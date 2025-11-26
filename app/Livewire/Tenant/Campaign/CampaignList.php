<?php

namespace App\Livewire\Tenant\Campaign;

use App\Models\Tenant\Campaign;
use App\Services\FeatureService;
use Livewire\Component;

class CampaignList extends Component
{
    public $campaign_id = null;

    public $confirmingDeletion = false;

    protected $listeners = [
        'confirmDelete' => 'confirmDelete',
    ];

    protected $featureLimitChecker;

    public function mount()
    {
        if (! checkPermission('tenant.campaigns.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    public function confirmDelete($campaignId)
    {
        $this->campaign_id = $campaignId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('tenant.campaigns.delete')) {
            Campaign::findOrFail($this->campaign_id)->delete();
            $this->confirmingDeletion = false;
            $this->notify(['type' => 'success', 'message' => t('campaign_delete_successfully')]);
            $this->dispatch('pg:eventRefresh-campaign-table-zfu4eg-table');
        }
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('campaigns', Campaign::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('campaigns', Campaign::class);
    }

    public function getTotalLimitProperty()
    {
        return $this->featureLimitChecker->getLimit('campaigns');
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-campaign-table-zfu4eg-table');
    }

    public function render()
    {
        return view('livewire.tenant.campaign.campaign-list');
    }
}
