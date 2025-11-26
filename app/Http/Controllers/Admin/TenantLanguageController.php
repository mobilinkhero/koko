<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Support\Facades\File;

class TenantLanguageController extends Controller
{
    /**
     * Download tenant language file.
     */
    public function download(int $tenantId, string $code)
    {
        try {
            // Verify the language exists for the specified tenant
            $language = Language::query()
                ->where('code', $code)
                ->where('tenant_id', $tenantId)
                ->firstOrFail();

            // Only allow download for non-English tenant languages
            if ($code === 'en') {
                return response()->json([
                    'error' => 'Download not available for English language',
                ], 400);
            }

            // Build file path based on existing structure
            $filePath = resource_path("lang/translations/tenant/{$tenantId}/tenant_{$code}.json");

            if (! File::exists($filePath)) {
                return response()->json([
                    'error' => 'Language file not found',
                ], 404);
            }

            $fileContent = File::get($filePath);
            $fileName = "tenant_{$code}.json";

            return response()->streamDownload(
                function () use ($fileContent) {
                    echo $fileContent;
                },
                $fileName,
                [
                    'Content-Type' => 'application/json',
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                ]
            );

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Download failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
