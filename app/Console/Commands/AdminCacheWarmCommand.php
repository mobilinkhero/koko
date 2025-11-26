<?php

namespace App\Console\Commands;

use App\Facades\AdminCache;
use Illuminate\Console\Command;

class AdminCacheWarmCommand extends Command
{
    protected $signature = 'admin-cache:warm {--tags=* : Warm specific tags}';

    protected $description = 'Warm admin cache';

    public function handle()
    {
        $this->info('🔥 Warming Admin Cache');

        try {
            $tags = $this->option('tags');

            if (! empty($tags)) {
                foreach ($tags as $tag) {
                    AdminCache::warm($tag);
                    $this->line("✅ Warmed tag: <info>{$tag}</info>");
                }
            } else {
                // Warm critical caches
                $criticalTags = ['languages', 'currencies', 'settings'];
                foreach ($criticalTags as $tag) {
                    AdminCache::warm($tag);
                    $this->line("✅ Warmed critical cache: <info>{$tag}</info>");
                }
            }

            $this->info('🎉 Cache warming completed');

        } catch (\Exception $e) {
            $this->error("Failed to warm cache: {$e->getMessage()}");

            return 1;
        }

        return 0;
    }
}
