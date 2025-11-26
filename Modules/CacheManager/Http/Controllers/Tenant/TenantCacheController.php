<?php

namespace Modules\CacheManager\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\CacheManager\Services\TenantCacheService;

class TenantCacheController extends Controller
{
    protected TenantCacheService $cacheService;

    public function __construct(TenantCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display tenant cache management dashboard
     */
    public function index()
    {
        $tenantId = tenant_id();
        $cacheStats = $this->cacheService->getTenantCacheStatistics($tenantId);

        return view('CacheManager::tenant.index', compact('cacheStats'));
    }

    /**
     * Clear tenant-specific cache
     */
    public function clearTenantCache(): JsonResponse
    {
        try {
            $tenantId = tenant_id();
            $result = $this->cacheService->clearTenantCache($tenantId);

            return response()->json([
                'success' => true,
                'message' => 'Tenant cache cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear tenant cache: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear tenant views cache
     */
    public function clearTenantViews(): JsonResponse
    {
        try {
            $tenantId = tenant_id();
            $result = $this->cacheService->clearTenantViews($tenantId);

            return response()->json([
                'success' => true,
                'message' => 'Tenant views cache cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear tenant views: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear tenant logs
     */
    public function clearTenantLogs(): JsonResponse
    {
        try {
            $tenantId = tenant_id();
            $result = $this->cacheService->clearTenantLogs($tenantId);

            return response()->json([
                'success' => true,
                'message' => 'Tenant logs cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear tenant logs: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear tenant session data
     */
    public function clearTenantSessions(): JsonResponse
    {
        try {
            $tenantId = tenant_id();
            $result = $this->cacheService->clearTenantSessions($tenantId);

            return response()->json([
                'success' => true,
                'message' => 'Tenant sessions cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear tenant sessions: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all tenant-specific data
     */
    public function clearAllTenantData(): JsonResponse
    {
        try {
            $tenantId = tenant_id();
            $result = $this->cacheService->clearAllTenantData($tenantId);

            return response()->json([
                'success' => true,
                'message' => 'All tenant data cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear all tenant data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optimize tenant data
     */
    public function optimizeTenant(): JsonResponse
    {
        try {
            $tenantId = tenant_id();
            $result = $this->cacheService->optimizeTenant($tenantId);

            return response()->json([
                'success' => true,
                'message' => 'Tenant optimized successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize tenant: '.$e->getMessage(),
            ], 500);
        }
    }
}
