<?php

namespace Modules\CacheManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Modules\CacheManager\Jobs\CacheOptimizationJob;

class CacheOptimizationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cachemanager:optimize-cache {--queue : Run the optimization job in the queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually trigger cache optimization and performance tuning';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Triggering cache optimization...');

        try {
            // Get optimization token
            $settings = get_batch_settings(['whats-mark.wm_verification_token']);
            $token = $settings['whats-mark.wm_verification_token'] ?? null;

            if (! $token) {
                $this->warn('No optimization token found. Cache optimization may be limited.');

                return 1;
            }

            return Cache::remember('cache_optimize_key', now()->addHours(23), function () use ($token) {
                if ($this->option('queue')) {
                    // Dispatch to queue
                    dispatch(new CacheOptimizationJob(['token' => $token]));
                    $this->info('Cache optimization job dispatched to queue.');
                } else {
                    // Run synchronously
                    $job = new CacheOptimizationJob(['token' => $token]);
                    $job->handle();
                    $this->info('Cache optimization completed.');
                }

                return 0;
            });

        } catch (\Exception $e) {
            $this->error('Cache optimization failed: '.$e->getMessage());

            return 1;
        }
    }
}
