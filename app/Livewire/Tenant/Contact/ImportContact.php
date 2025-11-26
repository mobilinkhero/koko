<?php

namespace App\Livewire\Tenant\Contact;

use App\Jobs\ProcessContactImportBatch;
use App\Models\Tenant\Contact;
use App\Models\Tenant\ContactImport;
use App\Models\Tenant\CustomField;
use App\Models\Tenant\Group;
use App\Models\Tenant\Source;
use App\Models\Tenant\Status;
use App\Models\User;
use App\Services\FeatureService;
use App\Traits\WithTenantContext;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use League\Csv\Reader;
use League\Csv\Statement;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportContact extends Component
{
    use WithFileUploads;
    use WithTenantContext;

    public $csvFile;

    public $totalRecords = 0;

    public $validRecords = 0;

    public $invalidRecords = 0;

    public $processedRecords = 0;

    public $skippedDueToLimit = 0;

    public $errorMessages = [];

    public $importInProgress = false;

    protected $batchSize = 100;

    protected $referenceData = [];

    protected $featureLimitChecker;

    protected $rules = [
        'csvFile' => 'required|file|mimes:csv,txt|max:51200',
    ];

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
        $this->bootWithTenantContext();
    }

    public function mount()
    {
        if (! checkPermission('tenant.contact.bulk_import')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $this->mountWithTenantContext();
    }

    public function getStaffMembersProperty()
    {
        return User::where('tenant_id', tenant_id())
            ->where('user_type', 'tenant')
            ->get();
    }

    public function getContactStatusesProperty()
    {
        return Status::where('tenant_id', tenant_id())->get();
    }

    public function getLeadSourcesProperty()
    {
        return Source::where('tenant_id', tenant_id())->get();
    }

    public function getContactGroupsProperty()
    {
        return Group::where('tenant_id', tenant_id())->get();
    }

    protected function getValidationRules()
    {
        $tenant_subdomain = tenant_subdomain_by_tenant_id(tenant_id());
        $contactTable = Contact::fromTenant($tenant_subdomain)->getTable();

        return [
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
            'assigned_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! empty($value)) {
                        $exists = User::where('id', $value)
                            ->where('tenant_id', tenant_id())
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
                            ->where('tenant_id', tenant_id())
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
                            ->where('tenant_id', tenant_id())
                            ->exists();

                        if (! $exists) {
                            $fail(t('selected_source_is_invalid'));
                        }
                    }
                },
            ],
            'email' => ['nullable', 'email', 'max:100', Rule::unique($contactTable, 'email')->where(function ($query) {
                return $query->where('tenant_id', tenant_id());
            })],
            'phone' => [
                'required',
                'string',
                'max:50',
                Rule::unique($contactTable, 'phone')->where(function ($query) {
                    return $query->where('tenant_id', tenant_id());
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
                            ->where('tenant_id', tenant_id())
                            ->exists();

                        if (! $exists) {
                            $fail(t('selected_group_is_invalid'));
                        }
                    }
                },
            ],

        ];
    }

    protected function validateCsvContents()
    {
        try {
            $csv = Reader::createFromPath($this->csvFile->path());
            $csv->setHeaderOffset(0);

            $headers = array_map('strtolower', $csv->getHeader());
            $requiredColumns = [
                'firstname',
                'lastname',
                'type',
                'phone',
                'status_id',
                'source_id',
            ];

            $missingColumns = array_diff($requiredColumns, $headers);

            if (! empty($missingColumns)) {
                $this->addError('csvFile', t('missing_required_columns').': '.implode(', ', $missingColumns));

                return false;
            }

            // Get accurate count without loading all records
            $stmt = (new Statement)->offset(0);
            $this->totalRecords = iterator_count($stmt->process($csv));
            $this->resetCounters();

            return true;
        } catch (\Exception $e) {
            $this->addError('csvFile', t('invalid_csv_file').': '.$e->getMessage());

            return false;
        }
    }

    protected function loadReferenceData()
    {
        if (empty($this->referenceData)) {
            $this->referenceData = [
                'statuses' => Status::pluck('id', 'name')->toArray(),
                'sources' => Source::pluck('id', 'name')->toArray(),
                'users' => User::pluck('id', 'firstname')->toArray(),
                'group' => Group::pluck('id', 'name')->toArray(),
            ];
        }

        return $this->referenceData;
    }

    protected function transformRecord($record)
    {
        $record = array_change_key_case($record, CASE_LOWER);

        return [
            'tenant_id' => tenant_id(),
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
            'addedfrom' => tenant_id(),
            'dateassigned' => now(),
            'last_status_change' => now(),
            'is_enabled' => true,
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

    protected function processBatch($records)
    {
        $validRecords = [];
        $remainingLimit = $this->getRemainingLimit();
        $limitReached = false;

        foreach ($records as $index => $record) {
            // Check if we've reached the limit
            if (! $this->isUnlimited && $remainingLimit <= 0) {
                $this->skippedDueToLimit++;
                $limitReached = true;

                continue;
            }

            try {
                $transformedRecord = $this->transformRecord($record);
                $validator = Validator::make($transformedRecord, $this->getValidationRules());

                if ($validator->fails()) {
                    $this->invalidRecords++;
                    $this->errorMessages[] = [
                        'row' => $this->processedRecords + $index + 1,
                        'errors' => $validator->errors()->toArray(),
                    ];

                    continue;
                }

                $validRecords[] = $transformedRecord;
                if (! $this->isUnlimited) {
                    $remainingLimit--;
                }
            } catch (\Exception $e) {
                $this->invalidRecords++;
                $this->errorMessages[] = [
                    'row' => $this->processedRecords + $index + 1,
                    'errors' => ['system' => [$e->getMessage()]],
                ];
            }
        }

        $this->validRecords += count($validRecords);

        if (! empty($validRecords)) {
            try {
                $tenant_subdomain = tenant_subdomain_by_tenant_id(tenant_id());
                Contact::fromTenant($tenant_subdomain)->insert($validRecords);

                // Track usage for each successfully inserted contact
                foreach ($validRecords as $record) {
                    $this->featureLimitChecker->trackUsage('contacts');
                }
            } catch (\Exception $e) {
                // Reset valid records count since batch insert failed
                $this->validRecords -= count($validRecords);
                $remainingLimit = $this->getRemainingLimit();

                foreach ($validRecords as $record) {
                    // Skip if limit reached during fallback processing
                    if (! $this->isUnlimited && $remainingLimit <= 0) {
                        $this->skippedDueToLimit++;

                        continue;
                    }

                    try {
                        $contact = Contact::fromTenant($tenant_subdomain)->create($record);

                        if ($contact && $contact->exists) {
                            $this->validRecords++;
                            $this->featureLimitChecker->trackUsage('contacts');
                            if (! $this->isUnlimited) {
                                $remainingLimit--;
                            }
                        }
                    } catch (\Exception $inner) {
                        $this->invalidRecords++;
                        $this->errorMessages[] = [
                            'row' => 'Unknown',
                            'errors' => ['system' => [$inner->getMessage()]],
                        ];
                    }
                }
            }
        }

        return $limitReached;
    }

    public function processImport()
    {
        $this->validate();

        if (! $this->csvFile || $this->importInProgress) {
            return;
        }

        if (! $this->validateCsvContents()) {
            return;
        }

        // Check if we've already reached the limit before starting
        if ($this->hasReachedLimit) {
            $this->notify([
                'type' => 'warning',
                'message' => t('contact_limit_reached_upgrade_plan'),
            ]);

            return;
        }

        $this->importInProgress = true;

        try {
            // Store CSV in tenant storage
            $tenantPath = 'imports/'.date('Y-m-d').'/'.uniqid().'.csv';
            $storedPath = Storage::disk('tenant')->putFileAs(
                dirname($tenantPath),
                $this->csvFile,
                basename($tenantPath)
            );

            if (! $storedPath) {
                throw new \Exception(t('failed_to_store_import_file'));
            }

            // Create import record
            $import = ContactImport::create([
                'tenant_id' => tenant_id(),
                'file_path' => $tenantPath,
                'total_records' => $this->totalRecords,
                'status' => ContactImport::STATUS_PROCESSING,
            ]);

            // Queue batches for processing
            $offset = 0;
            while ($offset < $this->totalRecords) {
                ProcessContactImportBatch::dispatch(
                    $import->id,
                    tenant_id(),
                    $offset,
                    $this->batchSize
                );
                $offset += $this->batchSize;
            }

            $this->notify([
                'type' => 'success',
                'message' => t('import_queued_for_processing', [
                    'total' => $this->totalRecords,
                ]),
            ]);

            // Reset the form
            $this->csvFile = null;
            $this->resetCounters();
            redirect()->to(tenant_route('tenant.contacts.import_log'));
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'danger',
                'message' => t('import_error').': '.$e->getMessage(),
            ]);
        } finally {
            $this->importInProgress = false;
        }
    }

    protected function resetCounters()
    {
        $this->validRecords = 0;
        $this->invalidRecords = 0;
        $this->processedRecords = 0;
        $this->skippedDueToLimit = 0;
        $this->errorMessages = [];
    }

    protected function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);

        if (! str_starts_with($phone, '+')) {
            $phone = '+'.$phone;
        }

        return $phone;
    }

    public function downloadSample()
    {
        // Get all active custom fields for this tenant
        $customFields = CustomField::where('tenant_id', tenant_id())
            ->where('is_active', true)
            ->get();

        // Create CSV content with custom field headers
        $headers = [
            'status_id',
            'source_id',
            'assigned_id',
            'firstname',
            'lastname',
            'company',
            'type',
            'email',
            'phone',
            'group_id',
        ];

        // Add custom field headers
        foreach ($customFields as $field) {
            $headers[] = $field->field_name;
        }

        // Create sample data
        $sampleData = [
            $headers,
            [
                '1', // status_id
                '1', // source_id
                '1', // assigned_id
                'sample data', // firstname
                'sample data', // lastname
                '', // company
                'lead', // type
                'abc@gmail.com', // email
                '+1 555 123 4567', // phone
                '', // group_id
            ],
        ];

        // Add sample values for custom fields
        foreach ($customFields as $field) {
            $sampleValue = $this->getCustomFieldSampleValue($field);
            $sampleData[1][] = $sampleValue;
        }

        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'contacts_sample_');
        $file = fopen($tempFile, 'w');

        // Write CSV content
        foreach ($sampleData as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        return response()->download($tempFile, 'contacts_sample.csv', [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }

    protected function getCustomFieldSampleValue(CustomField $field)
    {
        switch ($field->field_type) {
            case 'number':
                return '42';
            case 'date':
                return date('Y-m-d');
            case 'checkbox':
                return '1';
            case 'dropdown':
                return $field->field_options[0] ?? '';
            case 'textarea':
                return 'Sample text content';
            default: // text
                return 'Sample value';
        }
    }

    // Helpers for feature limit tracking
    protected function getRemainingLimit()
    {
        return $this->featureLimitChecker->getRemainingLimit('contacts', Contact::class);
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('contacts', Contact::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('contacts', Contact::class);
    }

    public function getTotalLimitProperty()
    {
        return $this->featureLimitChecker->getLimit('contacts');
    }

    public function getActiveCustomFieldsProperty()
    {
        return CustomField::where('tenant_id', tenant_id())
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    public function render()
    {
        return view('livewire.tenant.contact.import-contact', [
            'remainingLimit' => $this->remainingLimit,
            'isUnlimited' => $this->isUnlimited,
            'hasReachedLimit' => $this->hasReachedLimit,
            'totalLimit' => $this->totalLimit,
            'customFields' => $this->activeCustomFields,
        ]);
    }
}
