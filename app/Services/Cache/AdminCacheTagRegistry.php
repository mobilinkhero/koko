<?php

namespace App\Services\Cache;

/**
 * Admin Cache Tag Registry
 *
 * Manages all cache tags, their relationships, TTL settings,
 * and model-to-tag mappings for the admin cache system.
 */
class AdminCacheTagRegistry
{
    protected array $tagDefinitions = [];

    protected array $modelTagMappings = [];

    protected array $warmupStrategies = [];

    protected array $ttlSettings = [];

    public function __construct()
    {
        $this->initializeTagDefinitions();
        $this->initializeModelMappings();
        $this->initializeWarmupStrategies();
        $this->initializeTtlSettings();
    }

    /**
     * Get all admin-related cache tags
     */
    public function getAllAdminTags(): array
    {
        return array_keys($this->tagDefinitions);
    }

    /**
     * Get tags associated with a specific model and event
     */
    public function getTagsForModel(string $modelClass, string $event, $model = null): array
    {
        $baseModelName = class_basename($modelClass);
        $tags = [];

        // Get base tags for the model
        if (isset($this->modelTagMappings[$baseModelName])) {
            $mapping = $this->modelTagMappings[$baseModelName];

            // Event-specific tags
            if (isset($mapping['events'][$event])) {
                $tags = array_merge($tags, $mapping['events'][$event]);
            }

            // Always clear tags
            if (isset($mapping['always'])) {
                $tags = array_merge($tags, $mapping['always']);
            }

            // Model-specific tags (with ID)
            if ($model && isset($model->id) && isset($mapping['specific'])) {
                foreach ($mapping['specific'] as $tagTemplate) {
                    $tags[] = str_replace('{id}', $model->id, $tagTemplate);
                }
            }
        }

        return array_unique($tags);
    }

    /**
     * Get TTL for specific tags
     */
    public function getTtlForTags(array $tags): ?int
    {
        $maxTtl = null;

        foreach ($tags as $tag) {
            if (isset($this->ttlSettings[$tag])) {
                $ttl = $this->ttlSettings[$tag];
                $maxTtl = $maxTtl === null ? $ttl : max($maxTtl, $ttl);
            }
        }

        return $maxTtl;
    }

    /**
     * Get warmup strategy for a tag
     */
    public function getWarmupStrategy(string $tag): ?string
    {
        return $this->warmupStrategies[$tag] ?? null;
    }

    /**
     * Get tag definition
     */
    public function getTagDefinition(string $tag): ?array
    {
        return $this->tagDefinitions[$tag] ?? null;
    }

    /**
     * Check if tag exists
     */
    public function hasTag(string $tag): bool
    {
        return isset($this->tagDefinitions[$tag]);
    }

    /**
     * Get tags by category
     */
    public function getTagsByCategory(string $category): array
    {
        return array_filter($this->tagDefinitions, function ($definition) use ($category) {
            return ($definition['category'] ?? null) === $category;
        });
    }

    /**
     * Get critical tags that should be warmed up
     */
    public function getCriticalTags(): array
    {
        return array_filter($this->tagDefinitions, function ($definition) {
            return $definition['critical'] ?? false;
        });
    }

    // Protected initialization methods

    protected function initializeTagDefinitions(): void
    {
        $this->tagDefinitions = [
            // Dashboard-related tags
            'admin.dashboard' => [
                'description' => 'Admin dashboard data and statistics',
                'category' => 'dashboard',
                'critical' => true,
            ],
            'admin.dashboard.stats' => [
                'description' => 'Dashboard statistics',
                'category' => 'dashboard',
                'critical' => true,
            ],
            'admin.dashboard.recent' => [
                'description' => 'Recent activities and data',
                'category' => 'dashboard',
                'critical' => false,
            ],

            // Navigation-related tags
            'admin.navigation' => [
                'description' => 'Admin sidebar and navigation menus',
                'category' => 'navigation',
                'critical' => true,
            ],
            'admin.navigation.sidebar' => [
                'description' => 'Sidebar menu structure',
                'category' => 'navigation',
                'critical' => true,
            ],

            // User management tags
            'admin.users' => [
                'description' => 'User management data',
                'category' => 'users',
                'critical' => false,
            ],
            'admin.users.list' => [
                'description' => 'User listing and pagination',
                'category' => 'users',
                'critical' => false,
            ],
            'admin.users.stats' => [
                'description' => 'User statistics',
                'category' => 'users',
                'critical' => false,
            ],

            // Plan management tags
            'admin.plans' => [
                'description' => 'Subscription plans data',
                'category' => 'plans',
                'critical' => true,
            ],
            'admin.plans.list' => [
                'description' => 'Plans listing',
                'category' => 'plans',
                'critical' => true,
            ],
            'admin.plans.stats' => [
                'description' => 'Plans statistics',
                'category' => 'plans',
                'critical' => false,
            ],

            // Tenant management tags
            'admin.tenants' => [
                'description' => 'Tenant management data',
                'category' => 'tenants',
                'critical' => false,
            ],
            'admin.tenants.list' => [
                'description' => 'Tenant listing',
                'category' => 'tenants',
                'critical' => false,
            ],
            'admin.tenants.stats' => [
                'description' => 'Tenant statistics',
                'category' => 'tenants',
                'critical' => false,
            ],

            // Settings tags
            'admin.settings' => [
                'description' => 'System settings and configuration',
                'category' => 'settings',
                'critical' => true,
            ],
            'admin.settings.system' => [
                'description' => 'System-level settings',
                'category' => 'settings',
                'critical' => true,
            ],

            // Permission and role tags
            'admin.permissions' => [
                'description' => 'Roles and permissions data',
                'category' => 'permissions',
                'critical' => true,
            ],
            'admin.roles' => [
                'description' => 'Role definitions and assignments',
                'category' => 'permissions',
                'critical' => true,
            ],

            // Statistics tags
            'admin.statistics' => [
                'description' => 'General admin statistics',
                'category' => 'statistics',
                'critical' => false,
            ],
            'admin.statistics.revenue' => [
                'description' => 'Revenue and financial statistics',
                'category' => 'statistics',
                'critical' => false,
            ],

            // Transaction management tags
            'admin.transactions' => [
                'description' => 'Transaction management data',
                'category' => 'transactions',
                'critical' => false,
            ],
            'admin.transactions.pending' => [
                'description' => 'Pending transaction lists',
                'category' => 'transactions',
                'critical' => false,
            ],

            // Currency management tags
            'admin.currencies' => [
                'description' => 'Currency management data',
                'category' => 'settings',
                'critical' => true,
            ],

            // Frontend tags
            'frontend.menu' => [
                'description' => 'Frontend menu structure',
                'category' => 'frontend',
                'critical' => true,
            ],
            'frontend.pricing' => [
                'description' => 'Frontend pricing display',
                'category' => 'frontend',
                'critical' => true,
            ],

            // Model-specific tags
            'model.user' => [
                'description' => 'Individual user model cache',
                'category' => 'models',
                'critical' => false,
            ],
            'model.plan' => [
                'description' => 'Individual plan model cache',
                'category' => 'models',
                'critical' => false,
            ],
            'model.tenant' => [
                'description' => 'Individual tenant model cache',
                'category' => 'models',
                'critical' => false,
            ],

            // Collection tags
            'collection.users' => [
                'description' => 'User collections and lists',
                'category' => 'collections',
                'critical' => false,
            ],
            'collection.plans' => [
                'description' => 'Plan collections and lists',
                'category' => 'collections',
                'critical' => false,
            ],
            'collection.tenants' => [
                'description' => 'Tenant collections and lists',
                'category' => 'collections',
                'critical' => false,
            ],
            'admin.mail' => [
                'description' => 'Mail configuration',
                'category' => 'settings',
                'critical' => true,
            ],
        ];
    }

    protected function initializeModelMappings(): void
    {
        $this->modelTagMappings = [
            'User' => [
                'always' => ['admin.users', 'collection.users', 'admin.dashboard.stats'],
                'events' => [
                    'created' => ['admin.dashboard', 'admin.statistics'],
                    'updated' => ['admin.users.list'],
                    'deleted' => ['admin.dashboard', 'admin.statistics'],
                ],
                'specific' => ['model.user.{id}'],
            ],

            'Plan' => [
                'always' => ['admin.plans', 'collection.plans', 'admin.navigation'],
                'events' => [
                    'created' => ['admin.dashboard', 'admin.navigation.sidebar'],
                    'updated' => ['admin.plans.list', 'admin.navigation'],
                    'deleted' => ['admin.dashboard', 'admin.navigation'],
                ],
                'specific' => ['model.plan.{id}'],
            ],

            'Tenant' => [
                'always' => ['admin.tenants', 'collection.tenants', 'admin.dashboard.stats'],
                'events' => [
                    'created' => ['admin.dashboard', 'admin.statistics'],
                    'updated' => ['admin.tenants.list'],
                    'deleted' => ['admin.dashboard', 'admin.statistics'],
                ],
                'specific' => ['model.tenant.{id}'],
            ],

            'Role' => [
                'always' => ['admin.roles', 'admin.permissions'],
                'events' => [
                    'created' => ['admin.navigation'],
                    'updated' => ['admin.navigation'],
                    'deleted' => ['admin.navigation'],
                ],
                'specific' => ['model.role.{id}'],
            ],

            'Permission' => [
                'always' => ['admin.permissions'],
                'events' => [
                    'created' => ['admin.navigation'],
                    'updated' => ['admin.navigation'],
                    'deleted' => ['admin.navigation'],
                ],
                'specific' => ['model.permission.{id}'],
            ],

            // Add more model mappings as needed
            'Setting' => [
                'always' => ['admin.settings'],
                'events' => [
                    'updated' => ['admin.settings.system', 'admin.navigation'],
                ],
                'specific' => ['model.setting.{id}'],
            ],

            'Currency' => [
                'always' => ['admin.currencies', 'admin.settings', 'model.currency'],
                'events' => [
                    'created' => ['frontend.pricing'],
                    'updated' => ['admin.settings', 'frontend.pricing'],
                    'deleted' => ['admin.settings', 'frontend.pricing'],
                ],
                'specific' => ['model.currency.{id}'],
            ],

            'Transaction' => [
                'always' => ['admin.transactions', 'admin.dashboard'],
                'events' => [
                    'created' => ['admin.statistics', 'admin.dashboard'],
                    'updated' => ['admin.transactions.pending'],
                    'deleted' => ['admin.statistics', 'admin.dashboard'],
                ],
                'specific' => ['model.transaction.{id}'],
            ],

            'Page' => [
                'always' => ['admin.navigation', 'frontend.menu', 'model.page'],
                'events' => [
                    'created' => ['admin.navigation'],
                    'updated' => ['frontend.menu'],
                    'deleted' => ['admin.navigation', 'frontend.menu'],
                ],
                'specific' => ['model.page.{id}'],
            ],

            'Language' => [
                'always' => ['model.language', 'admin.settings'],
                'events' => [
                    'created' => ['admin.navigation', 'frontend.menu'],
                    'updated' => ['admin.navigation', 'frontend.menu'],
                    'deleted' => ['admin.navigation', 'frontend.menu'],
                ],
                'specific' => ['model.language.{id}'],
            ],
        ];
    }

    protected function initializeWarmupStrategies(): void
    {
        $this->warmupStrategies = [
            'admin.dashboard' => 'dashboard',
            'admin.navigation' => 'navigation',
            'admin.plans' => 'plans',
            'admin.statistics' => 'statistics',
        ];
    }

    protected function initializeTtlSettings(): void
    {
        $this->ttlSettings = [
            // Critical data - shorter TTL for freshness
            'admin.dashboard' => 300, // 5 minutes
            'admin.navigation' => 1800, // 30 minutes
            'admin.plans' => 1800, // 30 minutes

            // Less critical data - longer TTL
            'admin.users' => 3600, // 1 hour
            'admin.tenants' => 3600, // 1 hour
            'admin.statistics' => 7200, // 2 hours
            'admin.transactions' => 1800, // 30 minutes
            'admin.currencies' => 86400, // 24 hours (rarely change)

            // Frontend cache - medium TTL
            'frontend.menu' => 3600, // 1 hour
            'frontend.pricing' => 1800, // 30 minutes

            // Settings - long TTL as they rarely change
            'admin.settings' => 86400, // 24 hours
            'admin.permissions' => 86400, // 24 hours

            // Model-specific - medium TTL
            'model.user' => 1800, // 30 minutes
            'model.plan' => 3600, // 1 hour
            'model.tenant' => 3600, // 1 hour

            // Collections - shorter TTL due to frequent changes
            'collection.users' => 900, // 15 minutes
            'collection.plans' => 1800, // 30 minutes
            'collection.tenants' => 1800, // 30 minutes
        ];
    }

    /**
     * Get all tags with their configuration
     */
    public function getAllTags(): array
    {
        return $this->tagDefinitions;
    }
}
