<?php

namespace App\Console\Commands;

use App\Facades\AdminCache;
use Illuminate\Console\Command;

class AdminCacheStatsCommand extends Command
{
    protected $signature = 'admin-cache:stats {--export= : Export stats to file}';

    protected $description = 'Display admin cache statistics';

    public function handle()
    {
        $this->info('📊 Admin Cache Statistics');
        $this->newLine();

        try {
            $stats = AdminCache::getCacheStatistics();

            $this->line("Driver: <info>{$stats['driver']}</info>");
            $this->line("Total Keys: <info>{$stats['total_keys']}</info>");
            $this->line("Total Size: <info>{$stats['total_size']}</info>");
            $this->line("Hit Rate: <info>{$stats['hit_rate']}%</info>");
            $this->line("Last Cleared: <info>{$stats['last_cleared']}</info>");

            if (isset($stats['tags']) && ! empty($stats['tags'])) {
                $this->newLine();
                $this->line('<comment>Tag Breakdown:</comment>');

                $headers = ['Tag', 'Keys', 'Size'];
                $rows = [];

                foreach ($stats['tags'] as $tag => $data) {
                    $rows[] = [
                        $tag,
                        $data['keys'] ?? 'N/A',
                        $data['size'] ?? 'N/A',
                    ];
                }

                $this->table($headers, $rows);
            }

            // Export option
            if ($file = $this->option('export')) {
                file_put_contents($file, json_encode($stats, JSON_PRETTY_PRINT));
                $this->info("Stats exported to: {$file}");
            }

        } catch (\Exception $e) {
            $this->error("Failed to get cache statistics: {$e->getMessage()}");

            return 1;
        }

        return 0;
    }
}
