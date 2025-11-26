<?php

namespace App\Console\Commands;

use App\Services\Cache\AdminCacheManager;
use App\Services\Cache\AdminCacheTagRegistry;
use Illuminate\Console\Command;

/**
 * Admin Cache Management Command
 *
 * Provides command-line interface for managing admin cache operations.
 */
class AdminCacheCommand extends Command
{
    protected $signature = 'admin-cache:manage
                           {action : The cache action to perform (clear, warm, stats, tags, refresh)}
                           {--tag=* : Specific tags to target}
                           {--key= : Specific cache key to target}
                           {--all : Apply to all admin cache}
                           {--force : Force the operation without confirmation}';

    protected $description = 'Manage admin cache operations (clear, warm, stats, etc.)';

    protected AdminCacheManager $cacheManager;

    protected AdminCacheTagRegistry $tagRegistry;

    public function __construct(AdminCacheManager $cacheManager, AdminCacheTagRegistry $tagRegistry)
    {
        parent::__construct();
        $this->cacheManager = $cacheManager;
        $this->tagRegistry = $tagRegistry;
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        try {
            switch ($action) {
                case 'clear':
                    return $this->handleClear();

                case 'warm':
                    return $this->handleWarm();

                case 'stats':
                    return $this->handleStats();

                case 'tags':
                    return $this->handleTags();

                case 'refresh':
                    return $this->handleRefresh();

                default:
                    $this->error("Unknown action: {$action}");
                    $this->showUsage();

                    return 1;
            }
        } catch (\Exception $e) {
            $this->error("Cache operation failed: {$e->getMessage()}");

            return 1;
        }
    }

    protected function handleClear(): int
    {
        $tags = $this->option('tag');
        $all = $this->option('all');
        $force = $this->option('force');

        if ($all) {
            if (! $force && ! $this->confirm('Are you sure you want to clear ALL admin cache?')) {
                $this->info('Operation cancelled.');

                return 0;
            }

            $this->info('Clearing all admin cache...');
            $this->cacheManager->flush();
            $this->info('✅ All admin cache cleared successfully.');

            return 0;
        }

        if (empty($tags)) {
            $this->error('Please specify tags with --tag or use --all for complete flush.');

            return 1;
        }

        $this->info('Clearing cache for tags: '.implode(', ', $tags));
        $this->cacheManager->invalidateTags($tags);
        $this->info('✅ Cache cleared successfully for specified tags.');

        return 0;
    }

    protected function handleWarm(): int
    {
        $tags = $this->option('tag');

        if (empty($tags)) {
            $this->info('Warming up critical admin caches...');
            $this->cacheManager->warm('critical');
        } else {
            $this->info('Warming up cache for tags: '.implode(', ', $tags));
            foreach ($tags as $tag) {
                $this->cacheManager->warm($tag);
            }
        }

        $this->info('✅ Cache warming completed.');

        return 0;
    }

    protected function handleStats(): int
    {
        $this->info('Admin Cache Statistics:');
        $this->newLine();

        $stats = $this->cacheManager->getStatistics();

        $headers = ['Metric', 'Value'];
        $rows = [];

        foreach ($stats as $key => $value) {
            $displayValue = is_bool($value) ? ($value ? 'Yes' : 'No') : $value;
            $rows[] = [ucwords(str_replace('_', ' ', $key)), $displayValue];
        }

        $this->table($headers, $rows);

        // Additional tag information
        $this->newLine();
        $this->info('Available Cache Tags:');
        $allTags = $this->tagRegistry->getAllAdminTags();
        $criticalTags = array_keys($this->tagRegistry->getCriticalTags());

        $tagHeaders = ['Tag', 'Critical', 'Category'];
        $tagRows = [];

        foreach ($allTags as $tag) {
            $definition = $this->tagRegistry->getTagDefinition($tag);
            $tagRows[] = [
                $tag,
                in_array($tag, $criticalTags) ? '✅' : '❌',
                $definition['category'] ?? 'N/A',
            ];
        }

        $this->table($tagHeaders, $tagRows);

        return 0;
    }

    protected function handleTags(): int
    {
        $this->info('Admin Cache Tags:');
        $this->newLine();

        $categories = [];
        $allTags = $this->tagRegistry->getAllAdminTags();

        foreach ($allTags as $tag) {
            $definition = $this->tagRegistry->getTagDefinition($tag);
            $category = $definition['category'] ?? 'Other';

            if (! isset($categories[$category])) {
                $categories[$category] = [];
            }

            $categories[$category][] = [
                'tag' => $tag,
                'critical' => $definition['critical'] ?? false,
                'description' => $definition['description'] ?? 'No description',
            ];
        }

        foreach ($categories as $category => $tags) {
            $this->info("📁 {$category}:");
            foreach ($tags as $tagInfo) {
                $critical = $tagInfo['critical'] ? ' (Critical)' : '';
                $this->line("  • {$tagInfo['tag']}{$critical}");
                $this->line("    {$tagInfo['description']}", 'comment');
            }
            $this->newLine();
        }

        return 0;
    }

    protected function handleRefresh(): int
    {
        $key = $this->option('key');
        $tags = $this->option('tag');

        if (! $key) {
            $this->error('Please specify a cache key with --key option.');

            return 1;
        }

        $this->info("Refreshing cache key: {$key}");
        $this->cacheManager->refresh($key, null, $tags);
        $this->info('✅ Cache key refreshed successfully.');

        return 0;
    }

    protected function showUsage(): void
    {
        $this->newLine();
        $this->info('Usage examples:');
        $this->line('  php artisan admin:cache clear --all');
        $this->line('  php artisan admin:cache clear --tag=admin.dashboard --tag=admin.users');
        $this->line('  php artisan admin:cache warm');
        $this->line('  php artisan admin:cache warm --tag=admin.navigation');
        $this->line('  php artisan admin:cache stats');
        $this->line('  php artisan admin:cache tags');
        $this->line('  php artisan admin:cache refresh --key=dashboard.stats');
        $this->newLine();
    }
}
