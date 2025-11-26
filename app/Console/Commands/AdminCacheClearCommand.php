<?php

namespace App\Console\Commands;

use App\Facades\AdminCache;
use Illuminate\Console\Command;

class AdminCacheClearCommand extends Command
{
    protected $signature = 'admin-cache:clear {--tags=* : Clear specific tags} {--all : Clear all admin cache}';

    protected $description = 'Clear admin cache';

    public function handle()
    {
        $this->info('🧹 Clearing Admin Cache');

        try {
            $tags = $this->option('tags');
            $all = $this->option('all');

            if ($all) {
                AdminCache::flush();
                $this->info('✅ All admin cache cleared');
            } elseif (! empty($tags)) {
                foreach ($tags as $tag) {
                    AdminCache::invalidateTag($tag);
                    $this->line("✅ Cleared tag: <info>{$tag}</info>");
                }
            } else {
                if ($this->confirm('Clear all admin cache?', false)) {
                    AdminCache::flush();
                    $this->info('✅ All admin cache cleared');
                } else {
                    $this->info('Operation cancelled');
                }
            }

        } catch (\Exception $e) {
            $this->error("Failed to clear cache: {$e->getMessage()}");

            return 1;
        }

        return 0;
    }
}
