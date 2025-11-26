<?php

namespace Modules\CacheManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\CacheManager\Services\AdminCacheService;

class AdminCacheController extends Controller
{
    protected AdminCacheService $cacheService;

    public function __construct(AdminCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display admin cache management dashboard
     */
    public function index()
    {
        $cacheStats = $this->cacheService->getCacheStatistics();

        return view('CacheManager::admin.index', compact('cacheStats'));
    }

    /**
     * Clear application cache (admin-wide)
     */
    public function clearAppCache(): JsonResponse
    {
        try {
            $result = $this->cacheService->clearApplicationCache();

            return response()->json([
                'success' => true,
                'message' => 'Application cache cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear application cache: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear configuration cache
     */
    public function clearConfigCache(): JsonResponse
    {
        try {
            $result = $this->cacheService->clearConfigCache();

            return response()->json([
                'success' => true,
                'message' => 'Configuration cache cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear config cache: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear route cache
     */
    public function clearRouteCache(): JsonResponse
    {
        try {
            $result = $this->cacheService->clearRouteCache();

            return response()->json([
                'success' => true,
                'message' => 'Route cache cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear route cache: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear view cache
     */
    public function clearViewCache(): JsonResponse
    {
        try {
            $result = $this->cacheService->clearViewCache();

            return response()->json([
                'success' => true,
                'message' => 'View cache cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear view cache: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear application logs
     */
    public function clearLogs(): JsonResponse
    {
        try {
            $result = $this->cacheService->clearApplicationLogs();

            return response()->json([
                'success' => true,
                'message' => 'Application logs cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear logs: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all admin caches
     */
    public function clearAllCache(): JsonResponse
    {
        try {
            $result = $this->cacheService->clearAllAdminCache();

            return response()->json([
                'success' => true,
                'message' => 'All admin cache cleared successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear all cache: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optimize application (combine clear + cache)
     */
    public function optimizeApplication(): JsonResponse
    {
        try {
            $result = $this->cacheService->optimizeApplication();

            return response()->json([
                'success' => true,
                'message' => 'Application optimized successfully',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize application: '.$e->getMessage(),
            ], 500);
        }
    }
}
