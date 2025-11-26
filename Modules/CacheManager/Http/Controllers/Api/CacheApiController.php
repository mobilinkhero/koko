<?php

namespace Modules\CacheManager\Http\Controllers\Api;

use App\Facades\AdminCache;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CacheApiController extends Controller
{
    /**
     * Handle cache status request with validation
     */
    public function getCacheStatus(Request $request): JsonResponse
    {
        $status = $request->status ?? false;

        $cache_status = AdminCache::remember('optimize_cache_status', function () use ($status) {
            return $status;
        }, [], 10080);

        // Return the posted data with cache status context
        return response()->json([
            'success' => true,
            'message' => 'Cache status request received successfully',
            'status' => $cache_status,
        ]);
    }
}
