<?php

namespace App\Jobs;

use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignDetail;
use App\Models\Tenant\WhatsappTemplate;
use App\Traits\WhatsApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendCampaignMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, WhatsApp;

    /**
     * Number of times the job may be attempted
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job
     */
    public array $backoff = [180, 300, 600];

    /**
     * The maximum number of seconds the job should be allowed to run
     */
    public int $timeout;

    protected $tenant;

    public $tenant_subdomain;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected CampaignDetail $detail,
        $tenant
    ) {
        $settings = get_batch_settings([
            'whatsapp.queue',
            'whatsapp.tries',
            'whatsapp.backoff',
        ]);
        $this->onQueue(json_decode($settings['whatsapp.queue'], true)['name']);
        $this->timeout = json_decode($settings['whatsapp.queue'], true)['timeout'] ?? 60;

        $this->tenant = $tenant;
        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant->id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if campaign is paused - if so, release the job back to queue
        $settings = get_batch_settings([
            'whatsapp.queue',
            'whatsapp.tries',
            'whatsapp.backoff',
        ]);
        if ($this->detail->campaign->pause_campaign) {
            $this->release(json_decode($settings['whatsapp.queue'], true)['retry_after'] ?? 180);

            return;
        }

        try {
            // Get the associated campaign
            $campaign = Campaign::findOrFail($this->detail->campaign_id);

            $template = WhatsappTemplate::where(['template_id' => $campaign->template_id, 'tenant_id' => $this->tenant->id])->firstOrFail()->toArray();

            // Format parameters for template
            $template['header_message'] = $template['header_data_text'] ?? null;
            $template['body_message'] = $template['body_data'] ?? null;
            $template['footer_message'] = $template['footer_data'] ?? null;

            // Prepare data for sending
            $contact = \App\Models\Tenant\Contact::fromTenant($this->tenant_subdomain)->where('id', $this->detail->rel_id)->first();
            if (! $contact) {
                throw new \Exception('Contact not found: '.$this->detail->rel_id);
            }

            // Build message parameters
            $rel_data = array_merge(
                [
                    'rel_type' => $this->detail->rel_type,
                    'rel_id' => $contact->id,
                ],
                $template,
                [
                    'campaign_id' => $campaign->id,
                    'header_data_format' => $template['header_data_format'] ?? null,
                    'filename' => $campaign->filename ?? null,
                    'header_params' => $campaign->header_params,
                    'body_params' => $campaign->body_params,
                    'footer_params' => $campaign->footer_params,
                ]
            );

            $this->setWaTenantId($rel_data['tenant_id']);
            // Use the WhatsApp trait to send the template
            $response = $this->sendTemplate($contact->phone, $rel_data);

            // Update the detail record with the response
            if (! empty($response['status'])) {
                // Update campaign detail
                $this->detail->update([
                    'status' => 2,
                    'message_status' => 'sent',
                    'whatsapp_id' => $response['data']->messages[0]->id ?? null,
                    'response_message' => null,
                ]);
            } else {
                // Update detail with error
                $this->detail->update([
                    'status' => 0,
                    'message_status' => 'failed',
                    'response_message' => $response['message'] ?? 'Unknown error occurred',
                ]);

                if (json_decode($settings['whatsapp.queue'], true)['retry_after'] ?? 180) {
                    $this->release(json_decode($settings['whatsapp.queue'], true)['retry_after'] ?? 180);

                    return;
                }
            }
        } catch (Throwable $e) {
            $this->handleFailure($e);

            // Check if we should retry
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1]);
            } else {
                $this->fail($e);
            }
        }
    }

    /**
     * Handle job failure
     */
    protected function handleFailure(Throwable $e): void
    {
        $this->detail->update([
            'status' => 0,
            'message_status' => 'failed',
            'response_message' => $e->getMessage(),
        ]);

        if (json_decode(get_tenant_setting_by_tenant_id('whatsapp', 'logging', null, $this->tenant->id), true)) {
            whatsapp_log(
                'Campaign message failed',
                'error',
                [
                    'campaign_id' => $this->detail->campaign_id,
                    'detail_id' => $this->detail->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
                $e
            );
        }
    }
}
