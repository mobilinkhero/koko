<?php

namespace App\Console\Commands;

use App\Facades\Tenant;
use App\Jobs\SendCampaignMessageJob;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignDetail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class ProcessScheduledCampaigns extends Command
{
    use TenantAware;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:process-scheduled {--tenant=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process campaigns with scheduled_send_time';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        // Get campaigns that need to be sent now (scheduled time has arrived or passed)
        $campaigns = Campaign::where('is_sent', false)
            ->where(function ($query) use ($now) {
                $query->where('scheduled_send_time', '<=', $now)
                    ->orWhere('send_now', true);
            })
            ->where('pause_campaign', false)
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No scheduled campaigns to process.');

            return self::SUCCESS;
        }

        $totalProcessed = 0;

        foreach ($campaigns as $campaign) {
            $this->info("Processing campaign: {$campaign->name}");

            try {
                // Get all details for this campaign that haven't been sent
                $details = CampaignDetail::where('campaign_id', $campaign->id)
                    ->where('status', 1)
                    ->get();

                if ($details->isEmpty()) {
                    $this->warn("No messages to send for campaign: {$campaign->name}");

                    continue;
                }

                $this->info("Found {$details->count()} messages to queue");

                collect($details)->chunk(500)->each(function ($chunk) use ($campaign, $now) {
                    foreach ($chunk as $detail) {
                        // Determine immediate dispatch or delay
                        $delay = (! $campaign->send_now && $now->lessThan($campaign->scheduled_send_time))
                            ? $now->diffInSeconds($campaign->scheduled_send_time)
                            : 0;

                        SendCampaignMessageJob::dispatch($detail, Tenant::current())->delay($delay);
                    }
                });

                // Mark campaign as sent
                $campaign->update(['is_sent' => true]);

                $this->info("Successfully queued campaign: {$campaign->name}");
            } catch (\Throwable $e) {
                $this->error("Error processing campaign {$campaign->name}: {$e->getMessage()}");
                app_log('Campaign scheduling error', 'error', $e, [
                    'campaign_id' => $campaign->id,
                ]);
            }
        }

        $this->info("Total messages queued: {$totalProcessed}");

        return self::SUCCESS;
    }
}
