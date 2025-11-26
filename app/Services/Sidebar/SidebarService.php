<?php

namespace App\Services\Sidebar;

use Illuminate\Support\Facades\Auth;

class SidebarService
{
    /**
     * Evaluate visibility condition for sidebar items
     */
    public static function evaluateVisibility($condition, $user = null): bool
    {
        if (is_string($condition)) {
            // Handle string-based method calls
            if (strpos($condition, '::') !== false) {
                return call_user_func($condition, $user);
            }
        }

        if (is_callable($condition)) {
            return $condition($user ?? Auth::user());
        }

        return true;
    }

    /**
     * Process sidebar configuration and evaluate visibility
     */
    public static function processSidebarConfig(array $config, $user = null): array
    {
        $user = $user ?? Auth::user();

        foreach ($config as $key => &$item) {
            if (isset($item['visible_when'])) {
                $isVisible = self::evaluateVisibility($item['visible_when'], $user);
                if (! $isVisible) {
                    unset($config[$key]);

                    continue;
                }
            }

            // Process children recursively
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = self::processSidebarConfig($item['children'], $user);

                // Remove parent if no children are visible
                if (empty($item['children'])) {
                    unset($config[$key]);
                }
            }
        }

        return $config;
    }
}
