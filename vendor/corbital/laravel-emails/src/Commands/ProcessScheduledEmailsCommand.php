<?php

namespace Corbital\LaravelEmails\Commands;

use Corbital\LaravelEmails\Facades\Email;
use Corbital\LaravelEmails\Models\EmailLog;
use Corbital\LaravelEmails\Models\EmailTemplate;
use Corbital\LaravelEmails\Settings\EmailSettings;
use Illuminate\Console\Command;
use Spatie\LaravelSettings\Exceptions\SettingsMissingException;

class ProcessScheduledEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:process-scheduled
                           {--limit=50 : Maximum number of emails to process at once}
                           {--force : Force processing even if disabled in settings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled emails that are ready to be sent';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Check if scheduling is enabled
        $schedulingEnabled = true;

        try {
            $settings = app(EmailSettings::class);
            if (! $settings->enable_scheduling && ! $this->option('force')) {
                $this->info('Email scheduling is disabled in settings. Use --force to override.');

                return 0;
            }
        } catch (SettingsMissingException $e) {
            // If settings are not available, check config
            if (! config('laravel-emails.enable_scheduling', true) && ! $this->option('force')) {
                $this->info('Email scheduling is disabled in config. Use --force to override.');

                return 0;
            }
        }

        $this->info('Processing scheduled emails...');

        // Get the limit
        $limit = (int) $this->option('limit');

        // Find all scheduled emails that are due to be sent
        $scheduledEmails = EmailLog::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->limit($limit)
            ->get();

        if ($scheduledEmails->isEmpty()) {
            $this->info('No scheduled emails to process.');

            return 0;
        }

        $this->info("Found {$scheduledEmails->count()} scheduled emails to process.");

        $processed = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($scheduledEmails->count());
        $bar->start();

        foreach ($scheduledEmails as $scheduledEmail) {
            $this->processEmail($scheduledEmail, $processed, $failed);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Processed {$processed} scheduled emails. Failed: {$failed}");

        return 0;
    }

    /**
     * Process a single scheduled email.
     *
     * @param  int  $processed  Reference to processed counter
     * @param  int  $failed  Reference to failed counter
     * @return void
     */
    protected function processEmail(EmailLog $scheduledEmail, &$processed, &$failed)
    {
        try {
            // Check if we have a template or direct content
            if ($scheduledEmail->email_template_id) {
                $template = EmailTemplate::find($scheduledEmail->email_template_id);

                if (! $template) {
                    throw new \Exception("Template not found: ID {$scheduledEmail->email_template_id}");
                }

                // Send using facade
                $result = Email::to($scheduledEmail->to)
                    ->subject($scheduledEmail->subject)
                    ->template($template->slug, $scheduledEmail->data ?? [])
                    ->send();
            } else {
                // Cannot process without template or content
                throw new \Exception('Cannot process scheduled email without template or content');
            }

            if ($result === true) {
                // Update the log status
                $scheduledEmail->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                $processed++;
            } else {
                // Update with error
                $scheduledEmail->update([
                    'status' => 'failed',
                    'error' => $result,
                ]);

                $failed++;
            }
        } catch (\Exception $e) {
            // Update with error
            $scheduledEmail->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            $failed++;
        }
    }
}
