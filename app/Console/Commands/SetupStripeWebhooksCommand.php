<?php

namespace App\Console\Commands;

use App\Services\StripeWebhookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class SetupStripeWebhooksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:webhooks
                            {--url= : Custom webhook URL (default: application webhook route)}
                            {--events=* : Specific events to register (default: recommended set)}
                            {--list : List all current webhooks}
                            {--delete=* : Delete webhook by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Stripe webhooks configuration';

    /**
     * The webhook service instance.
     *
     * @var \App\Services\StripeWebhookService
     */
    protected $webhookService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(StripeWebhookService $webhookService)
    {
        parent::__construct();
        $this->webhookService = $webhookService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // List webhooks if requested
        if ($this->option('list')) {
            return $this->listWebhooks();
        }

        // Delete webhooks if IDs provided
        if ($webhookIds = $this->option('delete')) {
            return $this->deleteWebhooks($webhookIds);
        }

        // Ensure application URL is set for webhook endpoint generation
        if (! config('app.url')) {
            $this->error('Application URL is not set. Please set APP_URL in your .env file.');

            return 1;
        }

        // Set URL to HTTPS if not in local environment
        if (app()->environment() !== 'local') {
            URL::forceScheme('https');
        }

        // Get URL from option or generate from route
        $url = $this->option('url') ?: route('webhook.stripe');

        // Get events or use defaults
        $events = $this->option('events') ?: null;

        $this->info('Setting up Stripe webhooks...');
        $this->line("URL: {$url}");

        if ($events) {
            $this->line('Events: '.implode(', ', $events));
        } else {
            $this->line('Events: Using default set of recommended events');
        }

        // Configure webhooks
        $result = $this->webhookService->ensureWebhooksAreConfigured($url, $events);

        if ($result['success']) {
            $this->info($result['message']);

            if (isset($result['webhook']) && $result['webhook']) {
                $this->table(
                    ['ID', 'Provider', 'Endpoint URL', 'Events', 'Created At'],
                    [
                        [
                            $result['webhook']->webhook_id,
                            $result['webhook']->provider,
                            $result['webhook']->endpoint_url,
                            implode(', ', $result['webhook']->getEventsArray()),
                            $result['webhook']->created_at,
                        ],
                    ]
                );
            }

            return 0;
        } else {
            $this->error($result['message']);

            return 1;
        }
    }

    /**
     * List all webhooks.
     *
     * @return int
     */
    protected function listWebhooks()
    {
        $this->info('Listing Stripe webhooks...');

        $result = $this->webhookService->listWebhooks();

        if ($result['success']) {
            if (empty($result['webhooks'])) {
                $this->line('No webhooks found.');

                return 0;
            }

            $webhooks = collect($result['webhooks'])->map(function ($webhook) {
                return [
                    'id' => $webhook['id'],
                    'url' => $webhook['url'],
                    'status' => $webhook['status'],
                    'events' => implode(', ', array_slice($webhook['enabled_events'], 0, 3)).
                        (count($webhook['enabled_events']) > 3 ? '...' : ''),
                    'created' => date('Y-m-d H:i:s', $webhook['created']),
                ];
            })->toArray();

            $this->table(['ID', 'URL', 'Status', 'Events', 'Created'], $webhooks);

            return 0;
        } else {
            $this->error($result['message']);

            return 1;
        }
    }

    /**
     * Delete specific webhooks.
     *
     * @return int
     */
    protected function deleteWebhooks(array $webhookIds)
    {
        $success = true;

        foreach ($webhookIds as $webhookId) {
            $this->info("Deleting webhook: {$webhookId}");

            $result = $this->webhookService->deleteWebhook($webhookId);

            if ($result['success']) {
                $this->line("Webhook {$webhookId} deleted successfully.");
            } else {
                $this->error($result['message']);
                $success = false;
            }
        }

        return $success ? 0 : 1;
    }
}
