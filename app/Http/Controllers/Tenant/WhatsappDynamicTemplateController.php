<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\WhatsappTemplate;
use App\Traits\WhatsApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WhatsappDynamicTemplateController extends Controller
{
    use WhatsApp;

    public $tenant_id;

    public $tenant_subdomain;

    public $template_id;

    const CATEGORIES = [
        'MARKETING' => 'Marketing',
        'UTILITY' => 'Utility',
    ];

    const LANGUAGES = [
        'af' => 'Afrikaans',
        'sq' => 'Albanian',
        'ar' => 'Arabic',
        'az' => 'Azerbaijani',
        'bn' => 'Bengali',
        'bg' => 'Bulgarian',
        'ca' => 'Catalan',
        'zh_CN' => 'Chinese (Simplified)',
        'zh_HK' => 'Chinese (Traditional - Hong Kong)',
        'zh_TW' => 'Chinese (Traditional - Taiwan)',
        'hr' => 'Croatian',
        'cs' => 'Czech',
        'da' => 'Danish',
        'nl' => 'Dutch',
        'en_GB' => 'English (UK)',
        'en_US' => 'English (US)',
        'et' => 'Estonian',
        'fil' => 'Filipino',
        'fi' => 'Finnish',
        'fr' => 'French',
        'ka' => 'Georgian',
        'de' => 'German',
        'el' => 'Greek',
        'gu' => 'Gujarati',
        'he' => 'Hebrew',
        'hi' => 'Hindi',
        'hu' => 'Hungarian',
        'id' => 'Indonesian',
        'ga' => 'Irish',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'kn' => 'Kannada',
        'kk' => 'Kazakh',
        'ko' => 'Korean',
        'ky' => 'Kyrgyz',
        'lo' => 'Lao',
        'lv' => 'Latvian',
        'lt' => 'Lithuanian',
        'mk' => 'Macedonian',
        'ms' => 'Malay',
        'ml' => 'Malayalam',
        'mr' => 'Marathi',
        'nb' => 'Norwegian',
        'fa' => 'Persian',
        'pl' => 'Polish',
        'pt_BR' => 'Portuguese (Brazil)',
        'pt_PT' => 'Portuguese (Portugal)',
        'pa' => 'Punjabi',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sr' => 'Serbian',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'es' => 'Spanish',
        'es_MX' => 'Spanish (Mexico)',
        'sw' => 'Swahili',
        'sv' => 'Swedish',
        'ta' => 'Tamil',
        'te' => 'Telugu',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        'vi' => 'Vietnamese',
        'zu' => 'Zulu',
    ];

    public function __construct()
    {
        $this->tenant_id = tenant_id();
        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
    }

    public function create()
    {
        // Check permissions
        if (! checkPermission(['tenant.template.create'])) {
            session()->flash('notification', ['type' => 'danger', 'message' => t('access_denied_note')]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $categories = self::CATEGORIES;
        $languages = self::LANGUAGES;
        $templates = WhatsappTemplate::latest()->paginate(15);
        $subdomain = $this->tenant_subdomain;

        return view('tenant.dynamic-template.index', compact('templates', 'categories', 'languages', 'subdomain'));
    }

    /**
     * Handle file upload for template media
     */
    public function uploadMedia($subdomain, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|max:104857600', // 100MB max
                'type' => 'required|in:image,video,document',
                'old_media_url' => 'nullable|string', // URL of old media to replace
            ]);

            $file = $request->file('file');
            $type = $request->input('type');
            $oldMediaUrl = $request->input('old_media_url');

            // Validate file type based on media type
            $this->validateFileType($file, $type);

            // Delete old file if it exists and is from our storage
            if ($oldMediaUrl && $this->isOurStorageUrl($oldMediaUrl)) {
                $this->deleteOldMediaFile($oldMediaUrl);
            }

            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = time().'_'.uniqid().'_'.Str::slug(pathinfo($originalName, PATHINFO_FILENAME)).'.'.$extension;

            // FIXED: Correct storage path
            $storagePath = "tenant/{$this->tenant_id}/whatsapp-templates/{$type}s";

            // Store file
            $filePath = $file->storeAs($storagePath, $filename, 'public');

            // Generate public URL
            $fileUrl = asset('storage/'.$filePath);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file_url' => $fileUrl,
                'file_path' => $filePath,
                'original_name' => $originalName,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'old_file_removed' => $oldMediaUrl ? true : false,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File upload failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate file type based on media type
     */
    private function validateFileType($file, $type)
    {
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        $validations = [
            'image' => [
                'mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'max_size' => 5 * 1024 * 1024, // 5MB
                'description' => 'Images (JPEG, PNG, GIF, WebP)',
            ],
            'video' => [
                'mimes' => ['video/mp4', 'video/quicktime', 'video/x-msvideo'],
                'max_size' => 16 * 1024 * 1024, // 16MB
                'description' => 'Videos (MP4, MOV, AVI)',
            ],
            'document' => [
                'mimes' => [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'text/plain',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                ],
                'max_size' => 100 * 1024 * 1024, // 100MB
                'description' => 'Documents (PDF, DOC, DOCX, TXT, XLS, XLSX, PPT, PPTX)',
            ],
        ];

        if (! isset($validations[$type])) {
            throw new \Exception('Invalid file type specified');
        }

        $validation = $validations[$type];

        // Check MIME type
        if (! in_array($mimeType, $validation['mimes'])) {
            throw new \Exception("Invalid file format. Expected: {$validation['description']}");
        }

        // Check file size
        if ($fileSize > $validation['max_size']) {
            $maxSizeMB = round($validation['max_size'] / (1024 * 1024), 1);
            throw new \Exception("File size exceeds {$maxSizeMB}MB limit");
        }
    }

    /**
     * Check if the URL belongs to our storage system
     */
    private function isOurStorageUrl($url)
    {
        if (empty($url)) {
            return false;
        }

        // Check if URL contains our storage path pattern
        $storagePattern = "/storage/tenant/{$this->tenant_id}/whatsapp-templates/";

        return strpos($url, $storagePattern) !== false;
    }

    /**
     * Delete old media file from storage
     */
    private function deleteOldMediaFile($url)
    {
        try {
            // Extract the storage path from the URL
            $storagePattern = '/storage/';
            $storageIndex = strpos($url, $storagePattern);

            if ($storageIndex === false) {
                return false;
            }

            // Get the relative path after 'storage/'
            $relativePath = substr($url, $storageIndex + strlen($storagePattern));

            // Delete the file if it exists
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function store($subdomain, Request $request): JsonResponse
    {

        $validated = $request->validate([
            'template_name' => ['required', 'string', 'max:512', 'regex:/^[a-z0-9_]+$/'],
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'language' => ['required', Rule::in(array_keys(self::LANGUAGES))],
            'data' => 'required|array',
            'header_variable_value' => 'nullable|array',
            'body_variable_value' => 'nullable|array',
        ]);

        // Extract and process template data
        $data = $validated['data'];

        $template_creation = $this->createTemplate($validated);

        if (! $template_creation['status']) {
            return response()->json([
                'success' => false,
                'message' => $template_creation['message'] ?? '',
            ], 500);
        }

        // Process header data
        $headerDataFormat = null;
        $headerDataText = null;
        $headerParamsCount = 0;
        $mediaUrl = null;
        if (isset($data['header']) && $data['header']) {
            $headerDataFormat = $data['header']['type'] ?? 'TEXT';

            if ($headerDataFormat === 'TEXT' && isset($data['header']['text'])) {
                $headerDataText = $data['header']['text'];
                // Count variables in header text
                preg_match_all('/\{\{(\d+)\}\}/', $headerDataText, $matches);
                $headerParamsCount = count(array_unique($matches[1]));
            } elseif (in_array($headerDataFormat, ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                // $headerDataText = $data['header']['media_url'] ?? null;
                $mediaUrl = $data['header']['media_url'] ?? null;
            }
        }

        // Process body data
        $bodyData = $data['body'];
        preg_match_all('/\{\{(\d+)\}\}/', $bodyData, $bodyMatches);
        $bodyParamsCount = count(array_unique($bodyMatches[1]));

        // Process footer data
        $footerData = $data['footer'] ?? null;
        $footerParamsCount = 0;
        if ($footerData) {
            preg_match_all('/\{\{(\d+)\}\}/', $footerData, $footerMatches);
            $footerParamsCount = count(array_unique($footerMatches[1]));
        }

        // Process buttons data
        $buttonsData = null;
        if (isset($data['buttons']) && ! empty($data['buttons'])) {
            $buttonsData = json_encode($data['buttons']);
        }

        $template = WhatsappTemplate::create([
            'tenant_id' => $this->tenant_id,
            'template_id' => $template_creation['template_id'] ?? null,
            'template_name' => $validated['template_name'],
            'category' => $validated['category'],
            'language' => $validated['language'],
            'status' => $template_creation['template_status'] ?? 'DRAFT',
            'header_data_format' => $headerDataFormat,
            'header_data_text' => $headerDataText,
            'header_params_count' => $headerParamsCount,
            'body_data' => $bodyData,
            'body_params_count' => $bodyParamsCount,
            'footer_data' => $footerData,
            'footer_params_count' => $footerParamsCount,
            'buttons_data' => $buttonsData,
            'header_file_url' => $mediaUrl,
            'header_variable_value' => isset($validated['header_variable_value']) ? json_encode($validated['header_variable_value']) : null,
            'body_variable_value' => isset($validated['body_variable_value']) ? json_encode($validated['body_variable_value']) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template created successfully',
            'data' => $this->formatTemplateResponse($template),
            'redirect_url' => tenant_route('tenant.template.list'),
        ], 201);
    }

    public function show($subdomain, $id)
    {
        if (! checkPermission(['tenant.template.edit'])) {
            session()->flash('notification', ['type' => 'danger', 'message' => t('access_denied_note')]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $categories = self::CATEGORIES;
        $languages = self::LANGUAGES;
        $subdomain = $this->tenant_subdomain;

        // Get the template to edit by ID
        $templateToEdit = WhatsappTemplate::findOrFail($id);
        $template_id = $templateToEdit->id;

        // Format the template data properly for Vue component
        $templates = $this->formatTemplateResponse($templateToEdit);

        return view('tenant.dynamic-template.index', compact(
            'templates',
            'categories',
            'languages',
            'subdomain',
            'templateToEdit',
            'template_id'
        ));
    }

    public function update($subdomain, Request $request, $id): JsonResponse
    {
        $template = WhatsappTemplate::findOrFail($id);

        $validated = $request->validate([
            'template_name' => 'required|string|max:512',
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'language' => ['required', Rule::in(array_keys(self::LANGUAGES))],
            'data' => 'required|array',
            'header_variable_value' => 'nullable|array',
            'body_variable_value' => 'nullable|array',
        ]);

        // Extract and process template data
        $data = $validated['data'];

        $updated = $this->updateTemplate($template['template_id'], $validated);

        if (! $updated['status']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template: '.$updated['message'],
            ], 500);
        }

        // Process header data
        $headerDataFormat = null;
        $headerDataText = null;
        $headerParamsCount = 0;
        $mediaUrl = null;

        if (isset($data['header']) && $data['header']) {
            $headerDataFormat = $data['header']['type'] ?? 'TEXT';

            if ($headerDataFormat === 'TEXT' && isset($data['header']['text'])) {
                $headerDataText = $data['header']['text'];
                // Count variables in header text
                preg_match_all('/\{\{(\d+)\}\}/', $headerDataText, $matches);
                $headerParamsCount = count(array_unique($matches[1]));
            } elseif (in_array($headerDataFormat, ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                $mediaUrl = $data['header']['media_url'] ?? null;
            }
        }

        // Process body data
        $bodyData = $data['body'];
        preg_match_all('/\{\{(\d+)\}\}/', $bodyData, $bodyMatches);
        $bodyParamsCount = count(array_unique($bodyMatches[1]));

        // Process footer data
        $footerData = $data['footer'] ?? null;
        $footerParamsCount = 0;
        if ($footerData) {
            preg_match_all('/\{\{(\d+)\}\}/', $footerData, $footerMatches);
            $footerParamsCount = count(array_unique($footerMatches[1]));
        }

        // Process buttons data
        $buttonsData = null;
        if (isset($data['buttons']) && ! empty($data['buttons'])) {
            $buttonsData = json_encode($data['buttons']);
        }

        $template->update([
            'tenant_id' => $this->tenant_id,
            'template_name' => $validated['template_name'],
            'category' => $validated['category'],
            'language' => $validated['language'],
            'status' => $updated['template_status'] ?? $template->status ?? 'PENDING',
            'header_data_format' => $headerDataFormat,
            'header_data_text' => $headerDataText,
            'header_params_count' => $headerParamsCount,
            'body_data' => $bodyData,
            'body_params_count' => $bodyParamsCount,
            'footer_data' => $footerData,
            'footer_params_count' => $footerParamsCount,
            'buttons_data' => $buttonsData,
            'template_id' => $updated['data']['id'] ?? null,
            'header_file_url' => $mediaUrl,
            'header_variable_value' => isset($validated['header_variable_value']) ? json_encode($validated['header_variable_value']) : null,
            'body_variable_value' => isset($validated['body_variable_value']) ? json_encode($validated['body_variable_value']) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully',
            'data' => $this->formatTemplateResponse($template->fresh()),
            'redirect_url' => tenant_route('tenant.template.list'),
        ]);
    }

    /**
     * Format template response to match frontend expectations
     * FIXED: Properly structure the data for Vue component
     */
    private function formatTemplateResponse(WhatsappTemplate $template): array
    {
        // Reconstruct header data
        $header = null;
        if ($template->header_data_format) {
            $header = [
                'type' => $template->header_data_format,
            ];

            if ($template->header_data_format === 'TEXT') {
                $header['text'] = $template->header_data_text ?? '';
                $header['media_url'] = ''; // Ensure media_url exists even for TEXT
            } else {
                $header['media_url'] = $template->header_file_url ?? '';
                $header['text'] = ''; // Ensure text exists even for media types
            }
        }

        // Reconstruct buttons data
        $buttons = [];
        if ($template->buttons_data) {
            $decodedButtons = json_decode($template->buttons_data, true);
            $buttons = is_array($decodedButtons) ? $decodedButtons : [];
        }

        // Return the structure that Vue component expects
        return [
            'id' => $template->id,
            '_id' => $template->id, // For frontend compatibility
            'template_name' => $template->template_name,
            'category' => $template->category,
            'language' => $template->language,
            'status' => $template->status,
            'header_variable_value' => $template->header_variable_value ? json_decode($template->header_variable_value, true) : [],
            'body_variable_value' => $template->body_variable_value ? json_decode($template->body_variable_value, true) : [],
            // IMPORTANT: The Vue component expects the template content in __data property
            '__data' => [
                'header' => $header,
                'body' => $template->body_data ?? '',
                'footer' => $template->footer_data ?? '',
                'buttons' => $buttons,
            ],
            'created_at' => $template->created_at,
            'updated_at' => $template->updated_at,
        ];
    }
}
