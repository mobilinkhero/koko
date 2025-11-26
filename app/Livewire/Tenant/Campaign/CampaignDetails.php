<?php

namespace App\Livewire\Tenant\Campaign;

use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignDetail;
use App\Models\Tenant\Contact;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CampaignDetails extends Component
{
    public $campaign;

    public $template_name;

    public $totalDeliveredPercent;

    public $totalReadPercent;

    public $totalFailedPercent;

    public $totalContacts;

    public $totalCampaignsPercent;

    public $totalCount;

    public $status;

    public $campaignStatus;

    public $deliverCount;

    public $readCount;

    public $failedCount;

    public $sentCount;

    public $isInQueue;

    public $isRetryAble;

    public $tenant_id;

    public $tenant_subdomain;

    private function getTimezone(): string
    {
        return get_tenant_setting_from_db('system', 'timezone') ?: 'Asia/kolkata';
    }

    private function calculatePercentage($value, $total): float
    {
        return ! empty($total) ? round(($value / $total) * 100, 2) : 0;
    }

    public function mount()
    {
        if (! checkPermission('tenant.campaigns.show_campaign')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $this->tenant_id = tenant_id();
        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
        $campaignId = request()->route('campaignId');

        // Load campaign with template
        $this->campaign = Campaign::with('whatsappTemplate')
            ->where('tenant_id', $this->tenant_id)
            ->findOrFail($campaignId);

        $this->template_name = $this->campaign->whatsappTemplate?->template_name ?? t('template_not_found');

        // Get campaign details with a single query - using message_status for accurate webhook-updated statistics
        $campaignDetails = CampaignDetail::where('campaign_id', $campaignId)
            ->where('tenant_id', tenant_id())
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN message_status = "delivered" THEN 1 ELSE 0 END) as deliver_count,
                SUM(CASE WHEN message_status = "sent" THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN message_status = "read" THEN 1 ELSE 0 END) as read_count,
                SUM(CASE WHEN message_status = "failed" THEN 1 ELSE 0 END) as failed_count,
                MAX(CASE WHEN status = 1 THEN 1 ELSE 0 END) as is_in_queue
            ')
            ->first();

        $this->totalCount = $campaignDetails->total_count;
        $this->totalContacts = Contact::fromTenant($this->tenant_subdomain)
            ->where('type', $this->campaign->rel_type)
            ->count();

        $this->isRetryAble = false;

        if ($this->totalCount > 0) {
            $this->deliverCount = $campaignDetails->deliver_count;
            $this->readCount = $campaignDetails->read_count;
            $this->failedCount = $campaignDetails->failed_count;
            $this->sentCount = $campaignDetails->sent_count;
            $this->isInQueue = $campaignDetails->is_in_queue;

            $timezone = $this->getTimezone();
            $scheduledTime = $this->campaign->scheduled_send_time;
            $givenTime = Carbon::parse($scheduledTime, $timezone);
            $thresholdTime = $givenTime->copy()->addMinutes(5);
            $currentTime = Carbon::now($timezone);

            // Check if campaign is retryable
            if ($this->campaign->is_sent && ($this->failedCount > 0 ||
                (! ($this->totalCount == $this->deliverCount) && $currentTime->gt($thresholdTime)))) {
                $this->isRetryAble = true;
            }

            // Calculate percentages
            $this->totalCampaignsPercent = $this->calculatePercentage($this->totalCount, $this->totalContacts);
            $this->totalFailedPercent = $this->calculatePercentage($this->failedCount, $this->totalCount);
            $this->totalReadPercent = $this->calculatePercentage($this->readCount, $this->totalCount);
            $this->totalDeliveredPercent = $this->calculatePercentage($this->deliverCount, $this->totalCount);

            // Determine campaign status
            $this->campaignStatus = $this->determineCampaignStatus();
        } else {
            $this->resetCounters();
        }
    }

    private function determineCampaignStatus(): string
    {
        if ($this->totalCount === 0) {
            return 'Failed';
        }
        if ($this->failedCount == $this->totalCount) {
            return 'fail';
        }
        if ($this->deliverCount == $this->totalCount) {
            return 'sent';
        }
        if (! $this->isInQueue) {
            return 'executed';
        }

        return 'pending';
    }

    private function resetCounters(): void
    {
        $this->totalFailedPercent = 0;
        $this->totalReadPercent = 0;
        $this->totalDeliveredPercent = 0;
        $this->deliverCount = 0;
        $this->readCount = 0;
        $this->failedCount = 0;
        $this->sentCount = 0;
        $this->campaignStatus = 'Failed';
    }

    public function resumeCampaign()
    {
        if (! $this->isInQueue) {
            return $this->notify([
                'type' => 'warning',
                'message' => t('your_campaign_is_already_executed'),
            ]);
        }

        $newStatus = $this->campaign->pause_campaign ? 0 : 1;

        $this->campaign->update(['pause_campaign' => $newStatus]);

        $this->notify([
            'type' => 'success',
            'message' => $newStatus ?
                t('campaign_paused_successfully') :
                t('campaign_resumed_successfully'),
        ]);
    }

    public function retryCampaign()
    {
        if (! $this->isRetryAble) {
            return $this->notify([
                'type' => 'danger',
                'message' => t('you_cant_resend_this_campaign'),
            ]);
        }

        DB::transaction(function () {
            CampaignDetail::query()
                ->where('campaign_id', $this->campaign->id)
                ->where('tenant_id', tenant_id())
                ->where('message_status', 'failed')
                ->update([
                    'status' => 1,
                    'message_status' => 'sent',
                ]);

            $this->campaign->update([
                'is_sent' => 0,
                'scheduled_send_time' => now(),
            ]);
        });

        $this->notify([
            'type' => 'success',
            'message' => t('campaign_resend_process_initiated'),
        ]);
    }

    public function campaginList()
    {
        return redirect()->to(tenant_route('tenant.campaigns.list'));
    }

    public function createCampaign()
    {
        return redirect()->to(tenant_route('tenant.campaign'));
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-campaign-detail-table-luxo6s-table');
        $this->dispatch('pg:eventRefresh-campaign-executed-table-q6pjqg-table');
    }

    public function render()
    {
        return view('livewire.tenant.campaign.campaign-details');
    }
}
