<?php

namespace App\Livewire\Tenant\Campaign;

use App\Models\Tenant\WhatsappTemplate;
use App\Rules\PurifiedInput;
use App\Traits\WhatsApp;
use Illuminate\Support\Str;
use League\Csv\Reader;
use Livewire\Component;
use Livewire\WithFileUploads;

class CsvCampaign extends Component
{
    use WhatsApp;
    use WithFileUploads;

    // File-related properties
    public $csvFile;

    public $file;

    public $filename;

    public $filelink;

    public $json_file_path;

    // Campaign details
    public $id;

    public $template_name;

    public $templates;

    public $csv_campaign_name;

    public $choose_csv_file;

    // Template inputs
    public $headerInputs = [];

    public $bodyInputs = [];

    public $footerInputs = [];

    public $fields = [];

    public $mergeFields;

    // Status tracking
    public $totalRecords = 0;

    public $validRecords = 0;

    public $invalidRecords = 0;

    public $processedRecords = 0;

    public $importInProgress = false;

    public $isDisconnected = false;

    public $isUploading = false;

    protected $listeners = [
        'save' => 'save',
        'upload-started' => 'setUploading',
        'upload-finished' => 'setUploadingComplete',
    ];

    public function mount()
    {
        if (! checkPermission('tenant.bulk_campaigns.send')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $this->templates = WhatsappTemplate::where('tenant_id', tenant_id())->get();
    }

    protected function rules()
    {
        return [
            'csv_campaign_name' => ['required', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'csvFile' => 'required|file|mimes:csv,txt',
        ];
    }

    public function sampledownload()
    {
        $filePath = public_path('csv_sample/campaigns_sample.csv');

        return response()->download($filePath);
    }

    /**
     * Validate file upload
     */
    protected function validateFileUpload($format)
    {
        if ($format !== 'TEXT') {
            $this->validate([
                'file' => array_merge([$this->filename ? 'nullable' : 'required', 'file'], $this->getFileValidationRules($format)),
            ]);
        }
    }

    /**
     * Get file validation rules based on format
     */
    protected function getFileValidationRules($format)
    {
        return match ($format) {
            'IMAGE' => ['mimes:jpeg,png', 'max:8192'],
            'DOCUMENT' => ['mimes:pdf,doc,docx,txt,ppt,pptx,xlsx,xls', 'max:102400'],
            'VIDEO' => ['mimes:mp4,3gp', 'max:16384'],
            default => ['file', 'max:5120'],
        };
    }

    /**
     * Prepare template parameters
     */
    protected function prepareTemplateParams($template)
    {
        if (($template['header_params_count'] ?? 0) == 0) {
            $this->headerInputs = [];
        }

        if (($template['body_params_count'] ?? 0) == 0) {
            $this->bodyInputs = [];
        }

        if (($template['footer_params_count'] ?? 0) == 0) {
            $this->footerInputs = [];
        }
    }

    /**
     * Prepare campaign data with template
     */
    protected function prepareCampaignData($data, $template)
    {
        $data['header_params'] = json_encode(array_filter($this->headerInputs));
        $data['body_params'] = json_encode(array_filter($this->bodyInputs));
        $data['footer_params'] = json_encode(array_filter($this->footerInputs));

        $data = array_merge($data, $template);
        $data['filename'] = $this->filename;
        $data['filelink'] = $this->filelink;

        return $data;
    }

    /**
     * Process campaign and send messages
     */
    protected function processCampaign($data)
    {
        if (empty($data['json_file_path'])) {
            throw new \Exception(t('no_campaign_data_found'));
        }

        $jsonData = file_get_contents($data['json_file_path']);
        $campaignData = json_decode($jsonData, true);
        $response = [];
        $batchSize = 100;
        $batches = array_chunk($campaignData, $batchSize);

        foreach ($batches as $batch) {
            foreach ($batch as $campaign) {
                $to = $campaign['phone'];
                $result = $this->sendBulkCampaign($to, $data, $campaign);
                array_push($response, $result);
            }
        }

        return $response;
    }

    /**
     * Count valid API responses
     */
    protected function countValidResponses($response)
    {
        return count(array_filter($response, fn ($item) => isset($item['responseCode']) && $item['responseCode'] === 200));
    }

    /**
     * Handle file upload
     */
    protected function handleFileUpload($format)
    {
        try {
            $directory = match ($format) {
                'IMAGE' => 'tenant/'.tenant_id().'/csv/images',
                'DOCUMENT' => 'tenant/'.tenant_id().'/csv/documents',
                'VIDEO' => 'tenant/'.tenant_id().'/csv/videos',
                default => 'tenant/'.tenant_id().'/csv',
            };

            $this->filelink = $this->file->storeAs(
                $directory,
                $this->generateFileName(),
                'public'
            );
            $this->filename = $this->generateFileName();
        } catch (\Exception $e) {
            whatsapp_log('File upload error: '.$e->getMessage(), 'error');
            throw new \Exception(t('file_upload_failed').': '.$e->getMessage());
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateFileName()
    {
        $original = str_replace(' ', '_', $this->file->getClientOriginalName());

        return pathinfo($original, PATHINFO_FILENAME).'_'.time().'.'.$this->file->extension();
    }

    /**
     * Reset counters
     */
    protected function resetCounters()
    {
        $this->validRecords = 0;
        $this->invalidRecords = 0;
        $this->processedRecords = 0;
    }

    /**
     * Validate CSV file structure
     */
    protected function validateCsvContents($csv)
    {
        try {
            $csv->setHeaderOffset(0); // Skips the first row (header)
            $headers = array_map('strtolower', $csv->getHeader());

            $requiredColumns = [
                'firstname',
                'lastname',
                'phone',
            ];

            $missingColumns = array_diff($requiredColumns, $headers);

            if (! empty($missingColumns)) {
                whatsapp_log('Missing required columns', 'warning', ['missing_columns' => $missingColumns]);

                return [
                    'type' => 'danger',
                    'message' => t('missing_required_columns').': '.implode(', ', $missingColumns),
                ];
            }

            return null;
        } catch (\Exception $e) {
            whatsapp_log('CSV Validation Error', 'error', ['exception' => $e->getMessage()], $e);

            return [
                'type' => 'danger',
                'message' => t('invalid_csv_file').': '.$e->getMessage(),
            ];
        }
    }

    /**
     * Save JSON file for campaign data
     */
    protected function saveJsonFile($fileName, $data)
    {
        $directory = storage_path('app/public/tenant/'.tenant_id().'/csv');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filePath = $directory.'/'.$fileName;
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);

        if (file_put_contents($filePath, $jsonContent) === false) {
            $exception = new \Exception(t('failed_to_save_json_file'));
            whatsapp_log('Failed to save JSON file.', 'error', ['fileName' => $fileName], $exception);
            throw $exception;
        }

        return $filePath;
    }

    /**
     * Update merge fields when template name changes
     */
    public function updatedTemplateName()
    {
        $prepareMergeFields = [];
        if ($this->fields != '') {
            try {
                foreach ($this->fields as $key => $value) {
                    $prepareMergeFields[] = [
                        'key' => ucfirst($value),
                        'value' => '{'.$value.'}',
                    ];
                }
            } catch (\Throwable $e) {
                whatsapp_log('Error updating merge fields', 'error', ['fields' => $this->fields], $e);
                throw $e;
            }
        }

        return $this->mergeFields = json_encode($prepareMergeFields);
    }

    /**
     * Get template data
     */
    protected function getTemplateData($templateName)
    {
        $template = WhatsappTemplate::where(['template_id' => $templateName, 'tenant_id' => tenant_id()])->first();
        if (! $template) {
            throw new \Exception(t('template_not_found'));
        }

        return $template->toArray();
    }

    /**
     * Save campaign and send messages
     */
    public function save()
    {

        try {
            $data = $this->all();
            $template = $this->getTemplateData($data['template_name']);

            // Handle file upload if needed
            if ($this->file) {
                $this->validateFileUpload($template['header_data_format'] ?? 'TEXT');
                $this->handleFileUpload($template['header_data_format'] ?? 'TEXT');
            }

            // Prepare template params
            $this->prepareTemplateParams($template);

            // Prepare campaign data
            $data = $this->prepareCampaignData($data, $template);

            // Store the JSON file path for deletion later
            $jsonFilePath = $data['json_file_path'];

            // Process campaign data and send messages
            $response = $this->processCampaign($data);

            // Handle results
            $valid = $this->countValidResponses($response);
            $this->dispatch('close-loading-modal');

            $message = '';
            if ($valid != 0) {
                $message = t('total_send_campaign_list').' : '.$valid;
                $type = 'success';
            } else {
                $firstErrorMessage = $response[0]['message'] ?? t('please_add_valid_number_in_csv_file');
                $message = $firstErrorMessage;
                $type = 'danger';
            }

            $this->notify([
                'type' => $type,
                'message' => $message,
            ], true);

            // Delete the JSON file after campaign is complete
            if (file_exists($jsonFilePath)) {
                unlink($jsonFilePath);
            }
        } catch (\Exception $e) {
            whatsapp_log('Campaign Error: '.$e->getMessage(), 'error', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->notify([
                'type' => 'danger',
                'message' => t('campaign_error').': '.$e->getMessage(),
            ], true);
        }

        return redirect()->to(tenant_route('tenant.csvcampaign'));
    }

    /**
     * Process CSV import and convert to JSON
     */
    public function processImportCsv()
    {
        $this->validate();

        if (! $this->csvFile || $this->importInProgress) {
            $this->notify([
                'type' => 'danger',
                'message' => t('please_select_csv_file'),
            ]);

            $this->importInProgress = false;

            return;
        }

        $this->importInProgress = true;

        try {
            $csv = Reader::createFromPath($this->csvFile->path());
            $csv->setHeaderOffset(0);

            $records = iterator_to_array($csv->getRecords());
            $filteredRecords = [];

            foreach ($records as $record) {
                $normalizedPhone = $this->normalizePhoneNumber(preg_replace('/\s+/', '', $record['phone']));

                if (empty($normalizedPhone)) {
                    continue;
                }

                $record['phone'] = $normalizedPhone;
                $filteredRecords[] = array_filter($record, fn ($value) => ! empty($value));
            }

            if (! empty($filteredRecords)) {
                $fileName = 'csv_'.Str::uuid().'.json';
                $filePath = storage_path('app/public/tenant/'.tenant_id().'/csv/'.$fileName);

                $directory = storage_path('app/public/tenant/'.tenant_id().'/csv/');
                if (! is_dir($directory)) {
                    mkdir($directory, 0775, true);
                }

                $jsonContent = json_encode($filteredRecords, JSON_PRETTY_PRINT);
                file_put_contents($filePath, $jsonContent);

                $this->validRecords = count($filteredRecords);
                $this->invalidRecords = count($records) - count($filteredRecords);
                $this->totalRecords = count($records);
                $this->json_file_path = $filePath;
                $this->fields = $csv->getHeader();

                $this->notify([
                    'type' => 'success',
                    'message' => t('csv_uploaded_successfully'),
                ]);
            } else {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('please_upload_valid_csv_file'),
                ]);
            }
        } catch (\Exception $e) {
            whatsapp_log('CSV Import Error', 'error', [
                'csvFile' => $this->csvFile->getClientOriginalName(),
                'exception' => $e->getMessage(),
            ], $e);

            $this->notify([
                'type' => 'danger',
                'message' => t('import_failed').': '.$e->getMessage(),
            ]);
        } finally {
            $this->importInProgress = false;
        }
    }

    /**
     * Normalize phone number
     */
    protected function normalizePhoneNumber($phoneNumber)
    {
        try {
            // Sanitize input first
            $sanitized = filter_var($phoneNumber, FILTER_SANITIZE_NUMBER_INT);
            $normalizedNumber = preg_replace('/\D/', '', $sanitized);

            // Validate phone number format (minimal validation)
            if (is_numeric($normalizedNumber) && strlen($normalizedNumber) >= 10) {
                return $normalizedNumber;
            }

            whatsapp_log('Invalid phone number format', 'warning', ['phone_number' => $phoneNumber]);

            return null;
        } catch (\Exception $e) {
            whatsapp_log('Error normalizing phone number', 'error', ['phone_number' => $phoneNumber], $e);

            return null;
        }
    }

    public function render()
    {

        return view('livewire.tenant.campaign.csv-campaign');
    }
}
