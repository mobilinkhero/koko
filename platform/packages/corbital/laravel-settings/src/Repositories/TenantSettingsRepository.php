<?php

namespace Corbital\Settings\Repositories;

use Corbital\Settings\Models\TenantSetting;
use Illuminate\Support\Facades\DB;

class TenantSettingsRepository
{
    public function get(string $tenantId, string $group, string $key)
    {
        return TenantSetting::where('tenant_id', $tenantId)
            ->where('group', $group)
            ->where('key', $key)
            ->value('value');
    }

    public function set(string $tenantId, string $group, string $key, $value): bool
    {
        return TenantSetting::updateOrCreate(
            ['tenant_id' => $tenantId, 'group' => $group, 'key' => $key],
            ['value' => $value]
        )->exists;
    }

    /**
     * Set multiple settings in bulk for performance.
     */
    public function setBulk(string $tenantId, array $settings): bool
    {
        if (empty($settings)) {
            return true;
        }

        $data = [];
        $now = now();

        foreach ($settings as $key => $value) {
            if (str_contains($key, '.')) {
                [$group, $setting] = explode('.', $key, 2);
            } else {
                $group = 'default';
                $setting = $key;
            }

            $data[] = [
                'tenant_id' => $tenantId,
                'group' => $group,
                'key' => $setting,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        try {
            DB::beginTransaction();

            // Use upsert for better performance
            TenantSetting::upsert(
                $data,
                ['tenant_id', 'group', 'key'],
                ['value', 'updated_at']
            );

            DB::commit();

            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return false;
        }
    }

    /**
     * Get multiple settings in bulk for performance.
     */
    public function getBulk(string $tenantId, array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $conditions = [];
        foreach ($keys as $key) {
            if (str_contains($key, '.')) {
                [$group, $setting] = explode('.', $key, 2);
            } else {
                $group = 'default';
                $setting = $key;
            }
            $conditions[] = compact('group', 'setting');
        }

        $query = TenantSetting::where('tenant_id', $tenantId);

        if (count($conditions) === 1) {
            $query->where('group', $conditions[0]['group'])
                ->where('key', $conditions[0]['setting']);
        } else {
            $query->where(function ($q) use ($conditions) {
                foreach ($conditions as $condition) {
                    $q->orWhere(function ($subQ) use ($condition) {
                        $subQ->where('group', $condition['group'])
                            ->where('key', $condition['setting']);
                    });
                }
            });
        }

        $results = $query->get(['group', 'key', 'value']);

        $formatted = [];
        foreach ($results as $result) {
            $fullKey = $result->group.'.'.$result->key;
            $formatted[$fullKey] = $result->value;
        }

        // Fill in missing keys with null
        foreach ($keys as $key) {
            if (! isset($formatted[$key])) {
                $formatted[$key] = null;
            }
        }

        return $formatted;
    }

    /**
     * Delete multiple settings in bulk for performance.
     */
    public function deleteBulk(string $tenantId, array $keys): bool
    {
        if (empty($keys)) {
            return true;
        }

        $conditions = [];
        foreach ($keys as $key) {
            if (str_contains($key, '.')) {
                [$group, $setting] = explode('.', $key, 2);
            } else {
                $group = 'default';
                $setting = $key;
            }
            $conditions[] = compact('group', 'setting');
        }

        try {
            $query = TenantSetting::where('tenant_id', $tenantId);

            $query->where(function ($q) use ($conditions) {
                foreach ($conditions as $condition) {
                    $q->orWhere(function ($subQ) use ($condition) {
                        $subQ->where('group', $condition['group'])
                            ->where('key', $condition['setting']);
                    });
                }
            });

            return $query->delete() >= 0;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    public function getGroup(string $tenantId, string $group): array
    {
        return TenantSetting::where('tenant_id', $tenantId)
            ->where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public function getAll(string $tenantId): array
    {
        return TenantSetting::where('tenant_id', $tenantId)
            ->get()
            ->groupBy('group')
            ->map(function ($group) {
                return $group->pluck('value', 'key')->toArray();
            })
            ->toArray();
    }

    /**
     * Flush all settings for a tenant.
     */
    public function flush(string $tenantId): bool
    {
        try {
            return TenantSetting::where('tenant_id', $tenantId)->delete() >= 0;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
