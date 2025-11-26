<?php

namespace App\Livewire\Tenant\Partials;

use Illuminate\Support\Collection;
use Livewire\Component;

class TenantSidebarNavigation extends Component
{
    public Collection $menuItems;

    public Collection $setupMenuItems;

    public bool $collapsed = false;

    public function mount(bool $collapsed = false): void
    {
        $this->collapsed = $collapsed;
        $this->loadMenuItems();
        $this->getTenantFeature();
    }

    private function loadMenuItems(): void
    {
        $mainMenuConfig = config('tenant-sidebar.main_menu', []);
        $setupMenuConfig = config('tenant-sidebar.setup_menu', []);

        $mainMenuConfig = apply_filters('tenant_sidebar.main_menu', $mainMenuConfig);
        $setupMenuConfig = apply_filters('tenant_sidebar.setup_menus', $setupMenuConfig);

        $this->menuItems = $this->buildMenuFromConfig($mainMenuConfig);
        $this->setupMenuItems = $this->buildMenuFromConfig($setupMenuConfig);
    }

    private function buildMenuFromConfig(array $menuConfig): Collection
    {

        $user = auth()->user();

        return collect($menuConfig)
            ->filter(function ($item) use ($user) {
                return $this->isMenuItemVisible($item, $user);
            })
            ->sortBy('order')
            ->map(function ($item, $key) use ($user) {
                $menuItem = (object) [
                    'key' => $key,
                    'type' => $item['type'],
                    'label' => $item['label'],
                    'icon' => $item['icon'] ?? null,
                    'route' => $item['route'] ?? null,
                    'permission' => $item['permission'] ?? null,
                    'order' => $item['order'] ?? 0,
                    'active_routes' => $item['active_routes'] ?? [],
                    'badge' => $this->getBadgeValue($item),
                    'external' => $item['external'] ?? false,
                    'collapsed' => $item['collapsed'] ?? false,
                    'default_expanded' => $item['default_expanded'] ?? true,
                    'section_id' => $item['section_id'] ?? \Illuminate\Support\Str::slug($item['label'] ?? $key),
                    'feature_required' => $item['feature_required'] ?? null,
                    'children' => collect(),
                ];

                // Handle children for sections
                if ($item['type'] === 'section' && isset($item['children'])) {
                    $menuItem->children = collect($item['children'])

                        ->filter(function ($child) use ($user) {
                            return $this->isMenuItemVisible($child, $user);
                        })
                        ->sortBy('order')
                        ->map(function ($child, $childKey) {
                            return (object) [
                                'key' => $childKey,
                                'type' => $child['type'],
                                'label' => $child['label'],
                                'icon' => $child['icon'] ?? null,
                                'route' => $child['route'] ?? null,
                                'permission' => $child['permission'] ?? null,
                                'order' => $child['order'] ?? 0,
                                'active_routes' => $child['active_routes'] ?? [],
                                'badge' => $this->getBadgeValue($child),
                                'external' => $child['external'] ?? false,
                                'feature_required' => $child['feature_required'] ?? null,
                            ];
                        })
                        ->values();
                }

                return $menuItem;
            })
            ->values();
    }

    private function isMenuItemVisible(array $item, $user): bool
    {
        // Check custom visibility function
        if (isset($item['visible_when']) && is_callable($item['visible_when'])) {
            if (! $item['visible_when']($user)) {
                return false;
            }
        }

        // Check permissions
        if (isset($item['permission'])) {
            $permissions = $item['permission'];
            $permissionType = $item['permission_type'] ?? 'any';

            if (! $this->hasPermission($permissions, $permissionType)) {
                return false;
            }
        }

        return true;
    }

    private function hasPermission($permissions, string $permissionType = 'any'): bool
    {
        if (! $permissions) {
            return true;
        }

        if (! auth()->check()) {
            return false;
        }

        if (is_string($permissions)) {
            return $this->checkSinglePermission($permissions);
        }

        if (is_array($permissions)) {
            if ($permissionType === 'any') {
                return collect($permissions)->some(fn ($permission) => $this->checkSinglePermission($permission));
            } else {
                return collect($permissions)->every(fn ($permission) => $this->checkSinglePermission($permission));
            }
        }

        return true;
    }

    private function checkSinglePermission(string $permission): bool
    {
        // Use custom checkPermission function if available
        if (function_exists('checkPermission')) {
            return checkPermission($permission);
        }

        // Fallback to Laravel Gate system
        return auth()->user()->can($permission);
    }

    private function getBadgeValue(array $item): ?string
    {
        if (! isset($item['badge_callback'])) {
            return $item['badge'] ?? null;
        }

        $callback = $item['badge_callback'];

        if (function_exists($callback)) {
            $value = $callback();

            return $value > 0 ? (string) $value : null;
        }

        return null;
    }

    public function isActiveRoute(array $routes): bool
    {
        if (empty($routes)) {
            return false;
        }

        return collect($routes)->some(function ($route) {
            return request()->routeIs($route);
        });
    }

    public function getTenantFeature()
    {
        $user = auth()->user();
        $data = []; // default empty array

        if ($user) {
            $subscription = \App\Models\Subscription::where('tenant_id', $user->tenant_id)
                ->whereIn('status', ['active', 'trial'])
                ->latest()
                ->first();

            if ($subscription) {
                $data = \App\Models\PlanFeature::where('plan_id', $subscription->plan_id)
                    ->pluck('slug')
                    ->toArray();
            }

            return $data;
        }
    }

    public function shouldShowSetupMenu(): bool
    {
        $setupRoutes = [
            'tenant.staff.*',
            'tenant.roles.*',
            'tenant.status',
            'tenant.source',
            'tenant.ai-prompt',
            'tenant.canned-reply',
            'tenant.activity-log.*',
            'tenant.languages',
            'tenant.emails',
            'tenant.emails.save',
            'tenant.logs.index',
            'tenant.groups.list',
            'tenant.custom-fields.list',
        ];

        return request()->routeIs($setupRoutes);
    }

    public function render()
    {
        return view('livewire.tenant.partials.tenant-sidebar-navigation', [
            'user' => auth()->user(),
            'systemSettings' => function_exists('tenant_settings_by_group') ? tenant_settings_by_group('system') : [],
            'themeSettings' => function_exists('get_batch_settings') ? get_batch_settings(['theme.site_logo', 'theme.favicon', 'theme.dark_logo']) : [],
            'features' => $this->getTenantFeature(),
        ]);
    }
}
