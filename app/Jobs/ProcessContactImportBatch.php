<?php

namespace App\Jobs;

use App\Models\Tenant\Contact;
use App\Models\Tenant\ContactImport;
use App\Models\Tenant\CustomField;
use App\Models\Tenant\Group;
use App\Models\Tenant\Source;
use App\Models\Tenant\Status;
use App\Models\User;
use App\Services\FeatureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use League\Csv\Reader;
use League\Csv\Statement;

class ProcessContactImportBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importId;

    protected $tenantId;

    protected $offset;

    protected $batchSize;

    public function __construct($importId, $tenantId, $offset, $batchSize)
    {
        $this->importId = $importId;
        $this->tenantId = $tenantId;
        $this->offset = $offset;
        $this->batchSize = $batchSize;
    }

    protected function getValidationRules()
    {
        $tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenantId);
        $contactTable = Contact::fromTenant($tenant_subdomain)->getTable();

        // Base rules combined with the validation for custom fields
        $baseRules = [
            'firstname' => 'required|string|max:191',
            'lastname' => 'required|string|max:191',
            'company' => 'nullable|string|max:191',
            'type' => 'required|in:lead,customer',
            'description' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'zip' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:191',
            'custom_fields_data' => 'nullable|array',
            'assigned_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! empty($value)) {
                        $exists = User::where('id', $value)
                            ->where('tenant_id', $this->tenantId)
                            ->exists();

                        if (! $exists) {
                            $fail(t('the_selected_user_invalid'));
                        }
                    }
                },
            ],
            'status_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! empty($value)) {
                        $exists = Status::where('id', $value)
                            ->where('tenant_id', $this->tenantId)
                            ->exists();

                        if (! $exists) {
                            $fail(t('selected_status_is_invalid'));
                        }
                    }
                },
            ],
            'source_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! empty($value)) {
                        $exists = Source::where('id', $value)
                            ->where('tenant_id', $this->tenantId)
                            ->exists();

                        if (! $exists) {
                            $fail(t('selected_source_is_invalid'));
                        }
                    }
                },
            ],
            'email' => ['nullable', 'email', 'max:100', Rule::unique($contactTable, 'email')->where(function ($query) {
                return $query->where('tenant_id', $this->tenantId);
            })],
            'phone' => [
                'required',
                'string',
                'max:50',
                Rule::unique($contactTable, 'phone')->where(function ($query) {
                    return $query->where('tenant_id', $this->tenantId);
                }),
                function ($attribute, $value, $fail) {
                    if (! preg_match('/^\+[1-9]\d{5,14}$/', $value)) {
                        $fail(t('phone_validation'));
                    }
                },
            ],
            'group_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (! empty($value)) {
                        $exists = Group::where('id', $value)
                            ->where('tenant_id', $this->tenantId)
                            ->exists();

                        if (! $exists) {
                            $fail(t('selected_group_is_invalid'));
                        }
                    }
                },
            ],
        ];

        // Add custom field validation rules
        $customFields = CustomField::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->get();

        foreach ($customFields as $field) {
            $fieldRules = [];

            // Add required rule if field is required
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Add type-specific validation rules
            switch ($field->field_type) {
                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'dropdown':
                    if (! empty($field->field_options)) {
                        $fieldRules[] = 'in:'.implode(',', $field->field_options);
                    }
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
            }

            $baseRules['custom_fields_data.'.$field->field_name] = implode('|', $fieldRules);
        }

        return $baseRules;
    }

    public function handle(FeatureService $featureLimitChecker)
    {
        $import = ContactImport::findOrFail($this->importId);

        if ($import->status === ContactImport::STATUS_FAILED) {
            return;
        }

        try {
            // Get tenant-specific CSV path
            $csvPath = Storage::disk('tenant')->path($import->file_path);

            $csv = Reader::createFromPath($csvPath);
            $csv->setHeaderOffset(0);

            $stmt = (new Statement)
                ->offset($this->offset)
                ->limit($this->batchSize);

            $records = iterator_to_array($stmt->process($csv));

            if (empty($records)) {
                $this->checkIfCompleted($import);

                return;
            }

            $tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenantId);
            $validRecords = [];
            $errorMessages = [];
            $skippedCount = 0;

            foreach ($records as $index => $record) {
                $remainingLimit = $featureLimitChecker->getRemainingLimit('contacts', Contact::class);

                if ($remainingLimit !== null && $remainingLimit <= 0) {
                    $skippedCount++;

                    continue;
                }

                try {
                    $transformedRecord = $this->transformRecord($record);
                    $validator = Validator::make($transformedRecord, $this->getValidationRules());

                    if ($validator->fails()) {
                        $errorMessages[] = [
                            'row' => $this->offset + $index + 1,
                            'errors' => $validator->errors()->toArray(),
                        ];

                        continue;
                    }

                    $validRecords[] = $transformedRecord;
                } catch (\Exception $e) {
                    $errorMessages[] = [
                        'row' => $this->offset + $index + 1,
                        'errors' => ['system' => [$e->getMessage()]],
                    ];
                }
            }

            // Process valid records in a transaction
            if (! empty($validRecords)) {
                try {
                    Contact::fromTenant($tenant_subdomain)->insert($validRecords);

                    // Track usage for successfully inserted contacts
                    foreach ($validRecords as $record) {
                        $featureLimitChecker->trackUsage('contacts');
                    }
                } catch (\Exception $e) {
                    // Fallback to individual inserts if batch insert fails
                    foreach ($validRecords as $record) {
                        try {
                            $contact = Contact::fromTenant($tenant_subdomain)->create($record);
                            if ($contact && $contact->exists) {
                                $featureLimitChecker->trackUsage('contacts');
                            }
                        } catch (\Exception $inner) {
                            $errorMessages[] = [
                                'row' => 'Unknown',
                                'errors' => ['system' => [$inner->getMessage()]],
                            ];
                        }
                    }
                }
            }

            // Update import progress
            $import->processed_records += count($records);
            $import->valid_records += count($validRecords);
            $import->invalid_records += count($errorMessages);
            $import->skipped_records += $skippedCount;

            // Merge new error messages with existing ones
            $currentErrors = $import->error_messages ?? [];
            $import->error_messages = array_merge($currentErrors, $errorMessages);

            $import->save();

            $this->checkIfCompleted($import);
        } catch (\Exception $e) {
            $import->status = ContactImport::STATUS_FAILED;
            $import->error_messages = array_merge(
                $import->error_messages ?? [],
                [['system' => [$e->getMessage()]]]
            );
            $import->save();
        }
    }

    protected function checkIfCompleted(ContactImport $import)
    {
        if ($import->processed_records >= $import->total_records) {
            $import->status = ContactImport::STATUS_COMPLETED;
            $import->save();
        }
    }

    protected function transformRecord($record)
    {
        $record = array_change_key_case($record, CASE_LOWER);

        // Get custom fields for this tenant
        $customFields = CustomField::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->get();

        // Process custom fields data
        $customFieldsData = [];
        foreach ($customFields as $field) {
            $fieldName = strtolower($field->field_name);
            if (isset($record[$fieldName])) {
                $value = $record[$fieldName];

                // Transform value based on field type
                switch ($field->field_type) {
                    case 'number':
                        $value = is_numeric($value) ? (float) $value : null;
                        break;
                    case 'date':
                        try {
                            $value = $value ? date('Y-m-d', strtotime($value)) : null;
                        } catch (\Exception $e) {
                            $value = null;
                        }
                        break;
                    case 'checkbox':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'dropdown':
                        // Validate against options
                        if (! in_array($value, $field->field_options ?? [])) {
                            $value = null;
                        }
                        break;
                }

                if ($value !== null || $field->is_required) {
                    $customFieldsData[$field->field_name] = $value;
                }
            } elseif ($field->is_required) {
                $customFieldsData[$field->field_name] = $field->default_value;
            }
        }

        return [
            'tenant_id' => $this->tenantId,
            'firstname' => $record['firstname'],
            'lastname' => $record['lastname'],
            'company' => $record['company'] ?? null,
            'type' => strtolower($record['type']) ?? 'lead',
            'description' => $record['description'] ?? null,
            'assigned_id' => isset($record['assigned_id']) ? (int) $record['assigned_id'] : null,
            'status_id' => isset($record['status_id']) ? (int) $record['status_id'] : null,
            'source_id' => isset($record['source_id']) ? (int) $record['source_id'] : null,
            'email' => $record['email'] ?? null,
            'phone' => $this->formatPhoneNumber($record['phone']),
            'group_id' => isset($record['group_id']) ? $this->parseGroupIds($record['group_id']) : [],
            'addedfrom' => $this->tenantId,
            'dateassigned' => now(),
            'last_status_change' => now(),
            'is_enabled' => true,
            'custom_fields_data' => $customFieldsData,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    protected function parseGroupIds($value)
    {
        if (is_array($value)) {
            return array_map('intval', array_filter($value, 'is_numeric'));
        }

        return array_map('intval', array_filter(explode(',', $value), function ($id) {
            return is_numeric(trim($id));
        }));
    }

    protected function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);

        if (! str_starts_with($phone, '+')) {
            $phone = '+'.$phone;
        }

        return $phone;
    }
}
