<?php

use App\Models\Tenant\TenantSetting;
use Corbital\LaravelEmails\Services\MergeFieldsService;

if (! function_exists('parseCsvText')) {
    /**
     * Parse CSV-like text and replace placeholders with corresponding data.
     *
     * This function processes text by replacing placeholders in the format `{key}` with values from
     * the provided `$relData` array. It supports multiple parameters through JSON or plain text.
     *
     * @param  string  $type  The type prefix used to extract parameters and count from `$data`.
     * @param  array  $data  The main data array containing parameter values and counts.
     * @param  array  $relData  An associative array of placeholder keys and their corresponding values.
     * @return mixed An array of parsed and formatted text entries.
     */
    function parseCsvText(string $type, array $data, array $relData): mixed
    {
        // Create merge fields by mapping {key} => value
        $mergeFields = collect($relData)->mapWithKeys(fn ($value, $key) => ["{{$key}}" => $value])->toArray();
        $parseData = [];

        for ($i = 0; $i < $data["{$type}_params_count"]; $i++) {
            if (isJson($data["{$type}_params"] ?? '[]')) {
                $parsedText = json_decode($data["{$type}_params"], true) ?? [];
                $parsedText = array_map(function ($body) use ($mergeFields) {
                    // Convert "@{key}" syntax to "{$key}" syntax
                    $body = preg_replace('/@{(.*?)}/', '{$1}', $body);
                    foreach ($mergeFields as $key => $val) {
                        $body = str_contains($body, $key)
                            ? str_replace($key, ! empty($val) ? $val : ' ', $body)
                            : str_replace($key, '', $body);
                    }

                    return preg_replace('/\s+/', ' ', trim($body));
                }, $parsedText);
            } else {
                $parsedText[1] = preg_replace('/\s+/', ' ', trim($data["{$type}_params"]));
            }

            $parseData[] = ! empty($parsedText[$i]) ? $parsedText[$i] : ' ';
        }

        return $parseData;
    }
}

if (! function_exists('isJson')) {
    /**
     * Check if a given string is valid JSON.
     *
     * This function verifies whether the provided string is a valid JSON format.
     * It returns true if the string can be decoded as JSON, including the 'null' value.
     *
     * @param  mixed  $string  The input to be checked.
     * @return bool True if the input is a valid JSON string, otherwise false.
     */
    function isJson($string): bool
    {
        return is_string($string) && (json_decode($string) !== null || $string === 'null')
            ? true
            : false;
    }
}

if (! function_exists('get_meta_allowed_extension')) {
    /**
     * Get the allowed file extensions and their maximum sizes for various media types.
     *
     * @return array<string, array{extension: string, size: float}> An associative array containing:
     *                                                              - 'image': Allowed image extensions and maximum size (in MB).
     *                                                              - 'video': Allowed video extensions and maximum size (in MB).
     *                                                              - 'audio': Allowed audio extensions and maximum size (in MB).
     *                                                              - 'document': Allowed document extensions and maximum size (in MB).
     *                                                              - 'sticker': Allowed sticker extensions and maximum size (in MB).
     */
    function get_meta_allowed_extension()
    {
        return [
            'image' => [
                'extension' => '.jpeg, .png',
                'size' => 5,
            ],
            'video' => [
                'extension' => '.mp4, .3gp',
                'size' => 16,
            ],
            'audio' => [
                'extension' => '.aac, .amr, .mp3, .m4a, .ogg',
                'size' => 16,
            ],
            'document' => [
                'extension' => '.pdf, .doc, .docx, .txt, .xls, .xlsx, .ppt, .pptx',
                'size' => 100,
            ],
            'sticker' => [
                'extension' => '.webp',
                'size' => 0.1,
            ],
        ];
    }
}

if (! function_exists('parseText')) {
    /**
     * Parse text with merge fields
     *
     * @param  string  $rel_type
     * @param  string  $type
     * @param  array  $data
     * @param  string  $return_type
     * @return string|array
     */
    function parseText($rel_type, $type, $data, $return_type = 'text')
    {
        // Ensure we have a MergeFields service instance
        $mergeFieldsService = app(MergeFieldsService::class);

        // Prepare context for merge field parsing
        $context = [
            'contactId' => $data['rel_id'] ?? null,
            'relType' => $rel_type,
            'tenantId' => $data['tenant_id'],
        ];

        // Default to empty array if params are not set
        $data["{$type}_params"] = $data["{$type}_params"] ?? '[]';

        // Replace @{} with {} for consistent merge field syntax
        $data["{$type}_params"] = preg_replace('/@{(.*?)}/', '{$1}', $data["{$type}_params"]);

        // Parse the parameters using merge fields
        $data["{$type}_params"] = $mergeFieldsService->parseTemplates(['tenant-other-group', 'tenant-contact-group'], $data["{$type}_params"], $context);

        // Get merge fields from both groups
        $merge_fields = array_merge(
            $mergeFieldsService->getFieldsForTemplate('tenant-contact-group'),
            $mergeFieldsService->getFieldsForTemplate('tenant-other-group'),
        );

        // Prepare to parse parameters
        $parsedData = [];
        $paramsCount = $data["{$type}_params_count"] ?? 0;
        $params = json_decode($data["{$type}_params"], true) ?? [];
        $index = ($return_type == 'text') ? 1 : 0;
        $last = ($return_type == 'text') ? $paramsCount : $paramsCount - 1;
        // Process each parameter
        for ($i = $index; $i <= $last; $i++) {
            // use ($merge_fields)
            $parsedText = is_array($params) ? array_map(function ($body) use ($merge_fields) {
                // Replace merge fields
                $body = preg_replace('/@{(.*?)}/', '{$1}', $body);
                foreach ($merge_fields as $field) {
                    $key = $field['key'] ?? '';
                    $body = str_contains($body, "{{$key}}")
                    ? str_replace("{{$key}}", '', $body)
                    : $body;
                }

                return preg_replace('/\s+/', ' ', trim($body));
            }, $params) : [1 => trim($data["{$type}_params"] ?? '')];

            // Handle message template
            if ($return_type == 'text' && ! empty($data["{$type}_message"])) {
                $data["{$type}_message"] = str_replace("{{{$i}}}", ! empty($parsedText[$i - 1]) ? $parsedText[$i - 1] : ' ', $data["{$type}_message"]);
            }

            $parsedData[] = ! empty($parsedText[$i]) ? $parsedText[$i] : ' ';
        }

        return ($return_type == 'text') ? $data["{$type}_message"] : $parsedData;
    }
}

if (! function_exists('parseMessageText')) {
    /**
     * Parse message text with merge fields
     *
     * @param  array  $data
     * @return array
     */
    function parseMessageText($data)
    {
        $data['reply_text'] = preg_replace('/@{(.*?)}/', '{$1}', $data['reply_text'] ?? '');

        $mergeFieldsService = app(MergeFieldsService::class);
        if ($data['rel_type'] == 'lead' || $data['rel_type'] == 'customer') {
            $data['reply_text'] = $mergeFieldsService->parseTemplates(['tenant-other-group', 'tenant-contact-group'], $data['reply_text'], ['contactId' => $data['rel_id'], 'tenantId' => $data['tenant_id']]);
        }
        $data['reply_text'] = $mergeFieldsService->parseTemplates(['tenant-other-group'], $data['reply_text'], []);

        return $data;
    }
}

if (! function_exists('getTenantIdFromWhatsappDetails')) {
    /**
     * get tenant id by business account id & phone number id
     *
     * @param  string  $waba_id
     * @param  string  $phone_number_id
     * @return int|null
     */
    function getTenantIdFromWhatsappDetails($waba_id, $phone_number_id)
    {
        // Ensure we have valid IDs
        if (! $waba_id && ! $phone_number_id) {
            return null;
        }

        // Get tenant settings where these IDs match
        $waba = TenantSetting::where('key', 'wm_business_account_id')
            ->where('value', 'LIKE', "%$waba_id%")
            ->first();

        $phone = TenantSetting::where('key', 'wm_default_phone_number_id')
            ->where('value', 'LIKE', "%$phone_number_id%")
            ->first();

        // Both settings must exist and belong to the same tenant
        if ($waba && $phone && $waba->tenant_id == $phone->tenant_id) {
            return $waba->tenant_id;
        }

        // As a fallback, check if one of them exists
        if ($waba) {
            return $waba->tenant_id;
        }

        if ($phone) {
            return $phone->tenant_id;
        }

        // Log this occurrence
        whatsapp_log(
            'Could not find tenant for WhatsApp IDs',
            'warning',
            [
                'waba_id' => $waba_id,
                'phone_number_id' => $phone_number_id,
            ]
        );

        return null;
    }
}

/**
 * Decode WhatsApp signs to HTML tags
 *
 * @param  string  $text
 * @return string
 */
if (! function_exists('decodeWhatsAppSigns')) {
    function decodeWhatsAppSigns($text)
    {
        $patterns = [
            '/\*(.*?)\*/',       // Bold
            '/_(.*?)_/',         // Italic
            '/~(.*?)~/',         // Strikethrough
            '/```(.*?)```/',      // Monospace
        ];
        $replacements = [
            '<strong>$1</strong>',
            '<em>$1</em>',
            '<del>$1</del>',
            '<code>$1</code>',
        ];

        return preg_replace($patterns, $replacements, $text);
    }
}
