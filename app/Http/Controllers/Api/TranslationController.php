<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Translation API Controller
 * Handles translation requests for Vue.js components
 * Supports both tenant and admin contexts
 */
class TranslationController extends Controller
{
    /**
     * Get translations for the specified locale
     */
    public function index(Request $request, ?string $locale = null): JsonResponse
    {
        try {
            // Use existing language service to resolve locale
            $resolvedLocale = $locale ?: app('App\Services\LanguageService')->resolveLanguage();

            // Get translations using existing helper function
            $translations = getLanguageJson($resolvedLocale);

            return response()->json([
                'success' => true,
                'translations' => $translations,
                'locale' => $resolvedLocale,
                'is_tenant' => tenant_check(),
                'tenant_id' => tenant_id(),
            ]);
        } catch (\Exception $e) {
            app_log('Error loading translations API: '.$e->getMessage(), 'error', $e);

            // Fallback to default locale
            $fallbackLocale = 'en';
            $fallbackTranslations = getLanguageJson($fallbackLocale);

            return response()->json([
                'success' => false,
                'translations' => $fallbackTranslations,
                'locale' => $fallbackLocale,
                'is_tenant' => tenant_check(),
                'tenant_id' => tenant_id(),
                'error' => 'Translation file not found, using fallback',
            ], 404);
        }
    }
}
