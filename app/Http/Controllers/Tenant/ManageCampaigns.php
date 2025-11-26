<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignDetail;
use App\Models\Tenant\Contact;
use App\Models\Tenant\Group;
use App\Models\Tenant\Source;
use App\Models\Tenant\Status;
use App\Models\Tenant\WhatsappTemplate;
use App\Rules\PurifiedInput;
use App\Services\FeatureService;
use App\Traits\WhatsApp;
use Carbon\Carbon;
use Corbital\LaravelEmails\Services\MergeFieldsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ManageCampaigns extends Controller
{
    use WhatsApp;
    // =================================================================
    // MAIN CRUD METHODS (Standard RESTful Routes)
    // =================================================================

    public $tenant_id;

    public $tenant_subdomain;

    protected $featureLimitChecker;

    public function __construct()
    {
        $this->tenant_id = tenant_id();
        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
        $this->featureLimitChecker = app(FeatureService::class);
    }

    /**
     * Display campaigns listing page
     */
    public function index()
    {
        // Check permissions
        if (! checkPermission(['tenant.campaigns.view'])) {
            session()->flash('notification', ['type' => 'danger', 'message' => t('access_denied_note')]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $campaigns = Campaign::query()
            ->select('campaigns.*', 'whatsapp_templates.template_name as template_name')
            ->leftJoin('whatsapp_templates', 'campaigns.template_id', '=', 'whatsapp_templates.template_id')
            ->when(! Auth::user()->is_admin && checkPermission('tenant.contact.view_own'), function ($query) {
                return $query->where('campaigns.created_by', auth()->id());
            })
            ->orderBy('campaigns.created_at', 'desc')
            ->where('whatsapp_templates.tenant_id', $this->tenant_id);
        // ->paginate(20);

        $templates = WhatsappTemplate::all();

        // Fetch statuses
        $statuses = Status::select('id', 'name')->orderBy('name')->get();

        // Fetch sources
        $sources = Source::select('id', 'name')->orderBy('name')->get();

        $groups = Group::select('id', 'name')->orderBy('name')->get();

        $mergeFields = $this->getMergeFieldsData();

        $remainingLimit = $this->getRemainingLimitProperty();
        $hasReachedLimit = $this->getHasReachedLimitProperty();

        return view('tenant.campaign.manage-campaigns', compact(['campaigns', 'templates', 'statuses', 'sources', 'groups', 'mergeFields', 'remainingLimit', 'hasReachedLimit']));
    }

    /**
     * Store new campaign
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        // Check permissions
        if (! checkPermission(['tenant.campaigns.create'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => t('access_denied')], 403);
            }
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        if ($this->featureLimitChecker->hasReachedLimit('campaigns', Campaign::class)) {
            $this->featureLimitChecker->trackUsage('campaigns');

            return response()->json([
                'success' => false,
                'message' => t('campaign_limit_reached_upgrade_plan'),
                'redirect' => tenant_route('tenant.campaigns.list'),
            ]);
        }

        try {
            DB::beginTransaction();

            // Validate the request
            $validatedData = $this->validateCampaignData($request);

            // Handle file upload if present
            $filename = $this->handleCampaignFileUpload($request);

            // Create campaign
            $campaign = $this->createCampaign($validatedData, $filename);

            // Create campaign details for contacts
            $this->createCampaignDetails($campaign, $validatedData);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => t('campaign_created_successfully'),
                    'redirect' => tenant_route('tenant.campaigns.list'),
                ]);
            }

            return redirect()->tenant_route('tenant.campaigns.list')
                ->with('success', t('campaign_created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            app_log('Campaign creation failed', 'error', $e, [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->except(['file']),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => t('campaign_save_failed').': '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()
                ->with('error', t('campaign_save_failed').': '.$e->getMessage());
        }
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('campaigns', Campaign::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('campaigns', Campaign::class);
    }

    /**
     * Show campaign edit form
     */
    public function edit(string $subdomain, $id)
    {
        // Check permissions
        if (! checkPermission(['tenant.campaigns.edit'])) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        // Find campaign
        $campaign = Campaign::with(['campaign_details'])->findOrFail($id);

        $campaign->scheduled_send_time = format_date_time($campaign->scheduled_send_time);
        // Check ownership if not admin
        if (! $campaign) {
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.campaign'));
        }

        // Load form data with campaign
        $formData = $this->getFormInitialData($campaign);

        return view('tenant.campaign.manage-campaigns', array_merge($formData, ['campaign' => $campaign]));
    }

    /**
     * Update existing campaign
     */
    public function update(Request $request, $subdoamin, int $id): JsonResponse|RedirectResponse
    {
        // Check permissions
        if (! checkPermission(['tenant.campaigns.edit'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => t('access_denied')], 403);
            }
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        try {
            DB::beginTransaction();

            $campaign = Campaign::findOrFail($id);

            // Validate the request
            $validatedData = $this->validateCampaignData($request, $id);

            // Handle file upload if present
            $filename = $this->handleCampaignFileUpload($request, $campaign);

            // Update campaign
            $this->updateCampaign($campaign, $validatedData, $filename);

            // Recreate campaign details
            $campaign->campaign_details()->delete();
            $this->createCampaignDetails($campaign, $validatedData);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => t('campaign_updated_successfully'),
                    'redirect' => tenant_route('tenant.campaigns.list'),
                ]);
            }

            return redirect()->tenant_route('tenant.campaigns.list')
                ->with('success', t('campaign_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            app_log(t('campaign_update_failed'), 'error', $e, [
                'campaign_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => t('campaign_update_failed').': '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()
                ->with('error', t('campaign_update_failed').': '.$e->getMessage());
        }
    }

    /**
     * Delete campaign
     */
    public function destroy(int $id): JsonResponse|RedirectResponse
    {
        // Check permissions
        if (! checkPermission(['tenant.campaigns.delete'])) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => t('access_denied')], 403);
            }
            session()->flash('notification', [
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        try {
            $campaign = Campaign::findOrFail($id);

            // Delete associated file if exists
            if ($campaign->filename) {
                Storage::disk('public')->delete($campaign->filename);
            }

            // Delete campaign (cascade will handle details)
            $campaign->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => t('campaign_deleted_successfully'),
                ]);
            }
            session()->flash('notification', ['type' => 'success', 'message' => t('campaign_deleted_successfully')]);

            return redirect()->to(tenant_route('admin.campaigns.list'))
                ->with('success', t('campaign_deleted_successfully'));
        } catch (\Exception $e) {
            app_log('Campaign deletion failed', 'error', $e, [
                'campaign_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => t('campaign_delete_failed').': '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', t('campaign_delete_failed').': '.$e->getMessage());
        }
    }

    // =================================================================
    // AJAX HELPER METHODS (Dynamic Content Loading)
    // =================================================================

    /**
     * Get contacts based on filters (AJAX)
     */
    public function getContactsPaginated(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->input('page', 1);
            $perPage = 100;
            $offset = ($page - 1) * $perPage;

            $contacts = $this->loadFilteredContacts($request->all(), $offset, $perPage);
            $totalCount = $this->calculateContactCount($request->all());
            $hasMore = ($offset + $perPage) < $totalCount;

            return response()->json([
                'success' => true,
                'data' => $contacts,
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'has_more' => $hasMore,
            ]);
        } catch (\Exception $e) {
            app_log('Failed to load paginated contacts', 'error', $e, [
                'error' => $e->getMessage(),
                'filters' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => t('failed_to_load_contacts ').$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get template data (AJAX)
     */
    public function getTemplate(Request $request): JsonResponse
    {
        try {
            $templateId = $request->input('template_id');
            $template = WhatsappTemplate::where('template_id', $templateId)->firstOrFail();

            $templateData = $this->processTemplateData($template);

            return response()->json([
                'success' => true,
                'data' => $templateData,
            ]);
        } catch (\Exception $e) {
            app_log('Failed to load template', 'error', $e, [
                'template_id' => $request->input('template_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => t('failed_to_load_template').$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Count contacts based on filters (AJAX)
     */
    public function countContacts(Request $request): JsonResponse
    {
        try {
            $count = $this->calculateContactCount($request->all());

            return response()->json([
                'success' => true,
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => t('failed_to_count_contacts').$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle file upload (AJAX)
     */
    public function uploadFile(Request $request): JsonResponse
    {
        try {
            // Validate file
            $this->validateFileUpload($request);

            // Handle upload
            $filename = $this->processFileUpload($request->file('file'), $request->input('type'));

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'url' => Storage::disk('public')->url($filename),
                'message' => t('file_uploaded_successfully'),
            ]);
        } catch (\Exception $e) {
            app_log('File upload failed', 'error', $e, [
                'error' => $e->getMessage(),
                'file_name' => $request->file('file')?->getClientOriginalName(),
            ]);

            return response()->json([
                'success' => false,
                'message' => t('file_upload_failed').$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate form data (AJAX)
     */
    public function validateForm(Request $request): JsonResponse
    {
        try {
            $rules = $this->getValidationRules($request->input('step', 'all'));

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Validation passed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // =================================================================
    // PRIVATE HELPER METHODS (Business Logic)
    // =================================================================

    /**
     * Check if WhatsApp is disconnected
     */
    private function isWhatsAppDisconnected(): bool
    {
        $settings = get_batch_settings(['whatsapp.is_webhook_connected', 'whatsapp.is_whatsmark_connected', 'whatsapp.wm_default_phone_number']);

        return empty($settings['whatsapp.is_webhook_connected']) || empty($settings['whatsapp.is_whatsmark_connected']) || empty($settings['whatsapp.wm_default_phone_number']);
    }

    /**
     * Get initial form data
     */
    private function getFormInitialData(?Campaign $campaign = null): array
    {
        // Decode existing parameters if campaign exists
        $existingVariables = [
            'header' => [],
            'body' => [],
            'footer' => [],
        ];

        $existingFile = null;
        $selectedContacts = [];
        $totalSelectedCount = 0;
        if ($campaign) {
            $existingVariables['header'] = json_decode($campaign->header_params ?? '[]', true) ?: [];
            $existingVariables['body'] = json_decode($campaign->body_params ?? '[]', true) ?: [];
            $existingVariables['footer'] = json_decode($campaign->footer_params ?? '[]', true) ?: [];

            // Get existing file info
            if ($campaign->filename) {
                $existingFile = [
                    'filename' => $campaign->filename,
                    'url' => Storage::disk('public')->url($campaign->filename),
                ];
            }

            // Get selected contacts from campaign details
            $selectedContacts = $campaign->campaign_details()->pluck('rel_id')->toArray();
            $totalSelectedCount = $campaign->campaign_details()->count();
        }

        return [
            'templates' => WhatsappTemplate::where('status', 'APPROVED')->get(),
            'statuses' => Status::select('id', 'name')->orderBy('name')->get(),
            'sources' => Source::select('id', 'name')->orderBy('name')->get(),
            'groups' => Group::select('id', 'name')->orderBy('name')->get(),
            'mergeFields' => $this->getMergeFieldsData(),
            'relationTypes' => ['lead' => 'Lead', 'customer' => 'Customer'],
            'campaign' => $campaign,
            'existingVariables' => $existingVariables,
            'existingFile' => $existingFile,
            'selectedContacts' => $selectedContacts,
            'totalSelectedCount' => $totalSelectedCount, // Add this
            'isEditMode' => $campaign ? true : false, // Add this flag
        ];
    }

    /**
     * Get merge fields data
     */
    private function getMergeFieldsData(): string
    {
        $mergeFieldsService = app(MergeFieldsService::class);

        $fields = array_merge(
            $mergeFieldsService->getFieldsForTemplate('tenant-other-group'),
            $mergeFieldsService->getFieldsForTemplate('tenant-contact-group')
        );

        return json_encode(array_map(fn ($field) => [
            'key' => ucfirst($field['name']),
            'value' => $field['key'],
        ], $fields));
    }

    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Validate campaign data
     */
    private function validateCampaignData(Request $request, ?int $campaignId = null): array
    {
        if ($request->has('file') && is_string($request->input('file'))) {
            $request->request->remove('file');
        }

        $rules = [
            'campaign_name' => [
                'required',
                'min:3',
                'max:255',

                new PurifiedInput(t('sql_injection_error')),
            ],
            'rel_type' => 'required|in:lead,customer',
            'template_id' => 'required|exists:whatsapp_templates,template_id',
            'send_now' => 'required|in:0,1,true,false', // Accept both string and boolean
            'select_all' => 'required|in:0,1,true,false', // Accept both string and boolean
            'relation_type_dynamic' => 'array', // Remove conditional for now
            'status_name' => 'nullable|exists:statuses,id',
            'source_name' => 'nullable|exists:sources,id',
            'group_name' => 'nullable|exists:groups,id',
            'headerInputs' => 'array',
            'headerInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
            'bodyInputs' => 'array',
            'bodyInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
            'footerInputs' => 'array',
            'footerInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
        ];

        if ($request->hasFile('file')) {
            $rules['file'] = 'nullable|file';
        }

        // Add scheduled_send_time validation only if needed
        $sendNow = $request->input('send_now');
        if (! in_array($sendNow, ['1', 1, true, 'true'], true)) {
            $rules['scheduled_send_time'] = [
                'required',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        $fail(t('scheduled_send_time_required') ?? 'Scheduled send time is required.');

                        return;
                    }

                    try {
                        // Use the same parsing logic as processScheduledTime
                        $scheduledDate = $this->parseScheduledDateTime($value);

                        // Check if the date is in the future
                        if ($scheduledDate->isPast()) {
                            $fail(t('scheduled_time_must_be_future') ?? 'Scheduled time must be in the future.');
                        }
                    } catch (\Exception $e) {
                        $fail((t('invalid_date_format') ?? 'Invalid date format').': '.$e->getMessage());
                    }
                },
            ];
        }

        // Add contact validation only if not select all
        $selectAll = $request->input('select_all');
        if (! in_array($selectAll, ['1', 1, true, 'true'], true)) {
            $rules['relation_type_dynamic'] = 'required|array|min:1';
        }

        $validatedData = $request->validate($rules);

        // Normalize boolean values
        $validatedData['send_now'] = in_array($validatedData['send_now'], ['1', 1, true, 'true'], true);
        $validatedData['select_all'] = in_array($validatedData['select_all'], ['1', 1, true, 'true'], true);

        return $validatedData;
    }

    /**
     * Get validation rules for specific steps
     */
    private function getValidationRules(string $step): array
    {
        $allRules = [
            'campaign_name' => 'required|min:3|max:255',
            'rel_type' => 'required|in:lead,customer',
            'template_id' => 'required',
            // Add more rules as needed
        ];

        return match ($step) {
            'basic' => array_intersect_key($allRules, array_flip(['campaign_name', 'rel_type'])),
            'template' => array_intersect_key($allRules, array_flip(['template_id'])),
            'all' => $allRules,
            default => $allRules
        };
    }

    // =================================================================
    // BUSINESS LOGIC METHODS (Add these to your existing controller)
    // =================================================================

    /**
     * Load filtered contacts based on request parameters
     */
    private function loadFilteredContacts(array $filters, int $offset, int $limit): Collection
    {
        $relType = $filters['rel_type'] ?? null;
        $statusIds = $filters['status_ids'] ?? []; // Changed to array
        $sourceIds = $filters['source_ids'] ?? []; // Changed to array
        $groupIds = $filters['group_ids'] ?? [];   // Changed to array

        if (! $relType) {
            return collect([]);
        }

        $query = Contact::fromTenant($this->tenant_subdomain)->where('type', $relType)
            ->where('is_enabled', 1);

        // Apply permission-based filtering
        if (! Auth::user()->is_admin && checkPermission('tenant.contact.view_own')) {
            $query->where('assigned_id', auth()->id());
        }

        // Apply status filter
        if (! empty($statusIds)) {
            $query->whereIn('status_id', $statusIds);
        }

        // Apply source filter - now handles multiple values
        if (! empty($sourceIds)) {
            $query->whereIn('source_id', $sourceIds);
        }

        // Apply group filter - now handles multiple values
        if (! empty($groupIds)) {
            $query->where(function ($q) use ($groupIds) {
                foreach ($groupIds as $groupId) {
                    $q->orWhereRaw('JSON_CONTAINS(group_id, ?)', [json_encode([$groupId])]);
                }
            });
        }

        return $query->select('id', 'firstname', 'lastname', 'email', 'phone')
            ->orderBy('firstname')
            ->orderBy('id')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate contact count based on filters
     */
    private function calculateContactCount(array $filters): int
    {
        $relType = $filters['rel_type'] ?? null;
        $statusIds = $filters['status_ids'] ?? []; // Changed to array
        $sourceIds = $filters['source_ids'] ?? []; // Changed to array
        $groupIds = $filters['group_ids'] ?? [];   // Changed to array

        $selectAll = $filters['select_all'] ?? false;
        $selectedContacts = $filters['selected_contacts'] ?? [];

        if (! $relType) {
            return 0;
        }

        // If specific contacts are selected, return their count
        if (! $selectAll && ! empty($selectedContacts)) {
            return count($selectedContacts);
        }

        // If select all is enabled, count all matching contacts
        $query = Contact::fromTenant($this->tenant_subdomain)->where('type', $relType)
            ->where('is_enabled', 1);

        // Apply permission-based filtering
        if (! Auth::user()->is_admin && checkPermission('tenant.contact.view_own')) {
            $query->where('assigned_id', auth()->id());
        }

        // Apply filters - now handles multiple values
        if (! empty($statusIds)) {
            $query->whereIn('status_id', $statusIds);
        }

        if (! empty($sourceIds)) {
            $query->whereIn('source_id', $sourceIds);
        }

        if (! empty($groupIds)) {
            $query->where(function ($q) use ($groupIds) {
                foreach ($groupIds as $groupId) {
                    $q->orWhereRaw('JSON_CONTAINS(group_id, ?)', [json_encode([$groupId])]);
                }
            });
        }

        return $query->count();
    }

    /**
     * Process template data for frontend
     */
    private function processTemplateData(WhatsappTemplate $template): array
    {
        return [
            'id' => $template->template_id,
            'name' => $template->template_name,
            'language' => $template->language,
            'header' => [
                'format' => $template->header_data_format ?? 'TEXT',
                'text' => $template->header_data_text ?? '',
                'params_count' => $template->header_params_count ?? 0,
            ],
            'body' => [
                'text' => $template->body_data ?? '',
                'params_count' => $template->body_params_count ?? 0,
            ],
            'footer' => [
                'text' => $template->footer_data ?? '',
                'params_count' => $template->footer_params_count ?? 0,
            ],
            'buttons' => $this->parseTemplateButtons($template->buttons_data),
            'allowed_file_types' => $this->getAllowedFileTypes($template->header_data_format),
            'max_file_size' => $this->getMaxFileSize($template->header_data_format),
        ];
    }

    /**
     * Parse template buttons data
     */
    private function parseTemplateButtons(?string $buttonsData): array
    {
        if (empty($buttonsData)) {
            return [];
        }

        try {
            $buttons = json_decode($buttonsData, true);

            return is_array($buttons) ? $buttons : [];
        } catch (\Exception $e) {
            app_log('Failed to parse template buttons', 'warning', $e, [
                'buttons_data' => $buttonsData,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get allowed file types based on template format
     */
    private function getAllowedFileTypes(?string $format): array
    {
        $extensions = get_meta_allowed_extension();

        return match ($format) {
            'IMAGE' => [
                'extensions' => $extensions['image']['extension'] ?? '.jpeg,.png',
                'accept' => 'image/*',
            ],
            'VIDEO' => [
                'extensions' => $extensions['video']['extension'] ?? '.mp4,.3gp',
                'accept' => 'video/*',
            ],
            'DOCUMENT' => [
                'extensions' => $extensions['document']['extension'] ?? '.pdf,.doc,.docx',
                'accept' => '.pdf,.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx',
            ],
            'AUDIO' => [
                'extensions' => $extensions['audio']['extension'] ?? '.mp3,.aac',
                'accept' => 'audio/*',
            ],
            default => [
                'extensions' => '',
                'accept' => '',
            ]
        };
    }

    /**
     * Get max file size based on template format
     */
    private function getMaxFileSize(?string $format): int
    {
        $extensions = get_meta_allowed_extension();

        return match ($format) {
            'IMAGE' => ($extensions['image']['size'] ?? 5) * 1024 * 1024, // Convert MB to bytes
            'VIDEO' => ($extensions['video']['size'] ?? 16) * 1024 * 1024,
            'DOCUMENT' => ($extensions['document']['size'] ?? 100) * 1024 * 1024,
            'AUDIO' => ($extensions['audio']['size'] ?? 16) * 1024 * 1024,
            default => 5 * 1024 * 1024 // 5MB default
        };
    }

    // =================================================================
    // CAMPAIGN MANAGEMENT METHODS
    // =================================================================

    /**
     * Create new campaign
     */
    private function createCampaign(array $validatedData, ?string $filename): Campaign
    {
        $scheduledTime = $this->processScheduledTime($validatedData);
        $this->featureLimitChecker->trackUsage('campaigns');

        return Campaign::create([
            'name' => $validatedData['campaign_name'],
            'rel_type' => $validatedData['rel_type'],
            'template_id' => $validatedData['template_id'],
            'scheduled_send_time' => $scheduledTime,
            'send_now' => $validatedData['send_now'] ?? false,
            'select_all' => $validatedData['select_all'] ?? false,
            'sending_count' => $this->calculateSendingCount($validatedData),
            'header_params' => $this->encodeParams($validatedData['headerInputs'] ?? []),
            'body_params' => $this->encodeParams($validatedData['bodyInputs'] ?? []),
            'footer_params' => $this->encodeParams($validatedData['footerInputs'] ?? []),
            'filename' => $filename,
            'rel_data' => $this->encodeRelationData($validatedData),
        ]);
    }

    /**
     * Update existing campaign
     */
    private function updateCampaign(Campaign $campaign, array $validatedData, ?string $filename): void
    {
        $scheduledTime = $this->processScheduledTime($validatedData);
        $campaign->update([
            'name' => $validatedData['campaign_name'],
            'rel_type' => $validatedData['rel_type'],
            'template_id' => $validatedData['template_id'],
            'scheduled_send_time' => $scheduledTime,
            'send_now' => $validatedData['send_now'] ?? false,
            'select_all' => $validatedData['select_all'] ?? false,
            'sending_count' => $this->calculateSendingCount($validatedData),
            'header_params' => $this->encodeParams($validatedData['headerInputs'] ?? []),
            'body_params' => $this->encodeParams($validatedData['bodyInputs'] ?? []),
            'footer_params' => $this->encodeParams($validatedData['footerInputs'] ?? []),
            'filename' => $filename ?? $campaign->filename,
            'rel_data' => $this->encodeRelationData($validatedData),
            'updated_at' => now(),
        ]);
    }

    /**
     * Process scheduled time
     */
    private function processScheduledTime(array $validatedData): ?string
    {
        if ($validatedData['send_now'] ?? false) {
            return now()->utc()->toDateTimeString(); // return current UTC time
        }

        if (empty($validatedData['scheduled_send_time'])) {
            return null;
        }

        try {
            $scheduledDate = $this->parseScheduledDateTime($validatedData['scheduled_send_time']);

            // Convert to UTC for database storage
            return $scheduledDate->setTimezone('UTC')->toDateTimeString();
        } catch (\Exception $e) {
            // Log the error for debugging
            app_log('Date parsing failed in processScheduledTime', 'error', $e, [
                'input_date' => $validatedData['scheduled_send_time'],
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Invalid date/time format: '.$e->getMessage());
        }
    }

    /**
     * Parse scheduled date time from user input using configured formats
     */
    private function parseScheduledDateTime(string $dateTimeString): Carbon
    {
        // Get the configured date and time format from tenant settings
        $dateFormat = get_tenant_setting_from_db('system', 'date_format', 'd-m-Y');
        $timeFormat = get_tenant_setting_from_db('system', 'time_format') == '12' ? 'h:i A' : 'H:i';
        $userTimezone = get_tenant_setting_from_db('system', 'timezone', config('app.timezone'));

        // Primary format based on settings
        $primaryFormat = $dateFormat.' '.$timeFormat;

        // Fallback formats to handle various input scenarios
        $fallbackFormats = [
            $dateFormat.' '.($timeFormat === 'h:i A' ? 'H:i' : 'h:i A'),
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'd-m-Y H:i',
            'd.m.Y H:i',
            'm/d/Y h:i A',
            'm/d/Y H:i',
        ];

        $allFormats = array_merge([$primaryFormat], $fallbackFormats);

        foreach ($allFormats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateTimeString, $userTimezone);
                if ($date !== false) {
                    return $date;
                }
            } catch (\Exception $e) {
                // Continue to next format
                continue;
            }
        }

        // If all formats fail, try Carbon's built-in parsing as last resort
        try {
            return Carbon::parse($dateTimeString, $userTimezone);
        } catch (\Exception $e) {
            throw new \Exception("Unable to parse date '{$dateTimeString}'. Expected format: {$primaryFormat}");
        }
    }

    /**
     * Calculate sending count
     */
    private function calculateSendingCount(array $validatedData): int
    {
        if ($validatedData['select_all'] ?? false) {
            return $this->calculateContactCount($validatedData);
        }

        return count($validatedData['relation_type_dynamic'] ?? []);
    }

    /**
     * Search contacts (separate from pagination)
     */
    public function searchContacts(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search', '');
            $relType = $request->input('rel_type');
            $statusIds = $request->input('status_ids', []); // Changed to array
            $sourceIds = $request->input('source_ids', []); // Changed to array
            $groupIds = $request->input('group_ids', []);   // Changed to array

            if (empty($search) || empty($relType)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'total' => 0,
                ]);
            }

            $query = Contact::fromTenant($this->tenant_subdomain)->where('type', $relType)
                ->where('is_enabled', 1);

            // Apply permission-based filtering
            if (! Auth::user()->is_admin && checkPermission('tenant.contact.view_own')) {
                $query->where('assigned_id', auth()->id());
            }

            // Apply filters - now handles multiple values
            if (! empty($statusIds)) {
                $query->whereIn('status_id', $statusIds);
            }

            if (! empty($sourceIds)) {
                $query->whereIn('source_id', $sourceIds);
            }

            if (! empty($groupIds)) {
                $query->where(function ($q) use ($groupIds) {
                    foreach ($groupIds as $groupId) {
                        $q->orWhereRaw('JSON_CONTAINS(group_id, ?)', [json_encode([$groupId])]);
                    }
                });
            }

            // Apply search filter
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', '%'.$search.'%')
                    ->orWhere('lastname', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ['%'.$search.'%']);
            });

            $contacts = $query->select('id', 'firstname', 'lastname', 'email', 'phone')
                ->orderBy('firstname')
                ->limit(500) // Limit search results to reasonable number
                ->get();

            return response()->json([
                'success' => true,
                'data' => $contacts,
                'total' => $contacts->count(),
            ]);
        } catch (\Exception $e) {
            app_log('Failed to search contacts', 'error', $e, [
                'error' => $e->getMessage(),
                'search' => $request->input('search'),
            ]);

            return response()->json([
                'success' => false,
                'message' => t('failed_to_search_contacts').$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Encode parameters array to JSON
     */
    private function encodeParams(array $params): string
    {
        return json_encode(array_values(array_filter($params)));
    }

    /**
     * Encode relation data
     */
    private function encodeRelationData(array $validatedData): string
    {
        $normalize = function ($value) {
            if (is_array($value)) {
                return count($value) === 1 ? (string) $value[0] : $value;
            }

            return $value ?? null;
        };

        return json_encode([
            'status_id' => $normalize($validatedData['status_name'] ?? null),
            'source_id' => $normalize($validatedData['source_name'] ?? null),
            'group_id' => $normalize($validatedData['group_name'] ?? null),
        ]);
    }

    // =================================================================
    // CAMPAIGN DETAILS CREATION
    // =================================================================

    /**
     * Create campaign details for selected contacts
     */
    private function createCampaignDetails(Campaign $campaign, array $validatedData): void
    {
        $template = WhatsappTemplate::where('template_id', $campaign->template_id)->firstOrFail();
        $contacts = $this->getSelectedContacts($validatedData);

        if ($contacts->isEmpty()) {
            throw new \Exception(t('no_contacts_selected_campaign'));
        }

        $headerInputs = $validatedData['headerInputs'] ?? [];
        $bodyInputs = $validatedData['bodyInputs'] ?? [];
        $footerInputs = $validatedData['footerInputs'] ?? [];

        $campaignDetails = [];

        foreach ($contacts as $contact) {
            $campaignDetails[] = [
                'campaign_id' => $campaign->id,
                'tenant_id' => $this->tenant_id,
                'rel_id' => $contact->id,
                'rel_type' => $campaign->rel_type,
                'header_message' => $this->parseMessageVariables($template->header_data_text, $headerInputs, $contact),
                'body_message' => $this->parseMessageVariables($template->body_data, $bodyInputs, $contact),
                'footer_message' => $this->parseMessageVariables($template->footer_data, $footerInputs, $contact),
                'status' => 1,
                'message_status' => 'pending',
                'whatsapp_id' => null,
                'response_message' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $chunks = array_chunk($campaignDetails, 1000);

        foreach ($chunks as $chunk) {
            CampaignDetail::insert($chunk);
        }
    }

    /**
     * Get selected contacts for campaign
     */
    private function getSelectedContacts(array $validatedData): Collection
    {
        if ($validatedData['select_all'] ?? false) {
            return $this->getAllContactsForSelectAll($validatedData);
        }

        $contactIds = $validatedData['relation_type_dynamic'] ?? [];

        if (empty($contactIds)) {
            return collect([]);
        }

        return Contact::fromTenant($this->tenant_subdomain)->whereIn('id', $contactIds)
            ->where('type', $validatedData['rel_type'])
            ->where('is_enabled', 1)
            ->get();
    }

    /**
     * Get all contacts for select_all campaigns (without pagination)
     */
    private function getAllContactsForSelectAll(array $validatedData): Collection
    {
        $relType = $validatedData['rel_type'];
        $statusId = $validatedData['status_name'] ?? null;  // Form uses status_name
        $sourceId = $validatedData['source_name'] ?? null;  // Form uses source_name
        $groupId = $validatedData['group_name'] ?? null;    // Form uses group_name

        $query = Contact::fromTenant($this->tenant_subdomain)->where('type', $relType)
            ->where('is_enabled', 1);

        // Apply permission-based filtering
        if (! Auth::user()->is_admin && checkPermission('tenant.contact.view_own')) {
            $query->where('assigned_id', auth()->id());
        }

        // Apply status filter
        if ($statusId) {
            $query->where('status_id', $statusId);
        }

        // Apply source filter
        if ($sourceId) {
            $query->where('source_id', $sourceId);
        }

        // Apply group filter
        if ($groupId) {
            $query->inGroup($groupId);
        }

        // Return ALL matching contacts (no pagination) - optimized query
        return $query->select('id', 'firstname', 'lastname', 'email', 'phone', 'company')
            ->orderBy('id') // Use indexed column for better performance
            ->get();
    }

    /**
     * Parse message variables with contact data
     */
    private function parseMessageVariables(?string $message, array $params, $contact): ?string
    {
        if (empty($message)) {
            return null;
        }

        $parsedMessage = $message;

        // Replace numbered parameters {{1}}, {{2}}, etc.
        foreach ($params as $index => $param) {
            $placeholder = '{{'.($index + 1).'}}';
            $value = $param ?? '';
            $parsedMessage = str_replace($placeholder, $value, $parsedMessage);
        }

        // Replace contact merge fields
        $parsedMessage = $this->replaceMergeFields($parsedMessage, $contact);

        return $parsedMessage;
    }

    /**
     * Replace merge fields with contact data
     */
    private function replaceMergeFields(string $message, $contact): string
    {
        $settings = get_batch_settings([
            'system.site_name',
        ]);
        $mergeFields = [
            '{{contact_first_name}}' => $contact->firstname ?? '',
            '{{contact_last_name}}' => $contact->lastname ?? '',
            '{{contact_full_name}}' => trim(($contact->firstname ?? '').' '.($contact->lastname ?? '')),
            '{{contact_email}}' => $contact->email ?? '',
            '{{contact_phone}}' => $contact->phone ?? '',
            '{{contact_company}}' => $contact->company ?? '',
            '{{business_name}}' => $settings['system.site_name'] ?? 'Business',
            '{{current_date}}' => now()->format('Y-m-d'),
            '{{current_time}}' => now()->format('H:i:s'),
        ];

        return str_replace(array_keys($mergeFields), array_values($mergeFields), $message);
    }

    // =================================================================
    // FILE HANDLING METHODS
    // =================================================================

    /**
     * Handle campaign file upload
     */
    private function handleCampaignFileUpload(Request $request, ?Campaign $existingCampaign = null): ?string
    {
        $file = $request->file('file');
        $templateId = $request->input('template_id');

        // Get template to determine file type requirements
        $template = WhatsappTemplate::where('template_id', $templateId)->first();

        if (! $template) {
            throw new \Exception(t('template_not_found'));
        }

        if (! $request->hasFile('file')) {
            return ($template->header_data_format === 'TEXT' || $template->header_data_format === null) ? '' : $existingCampaign?->filename;
        }

        // Validate file
        $this->validateFileUpload($request, $template->header_data_format);

        // Delete existing file if updating
        if ($existingCampaign && $existingCampaign->filename) {
            Storage::disk('public')->delete($existingCampaign->filename);
        }

        // Process upload
        return $this->processFileUpload($file, $template->header_data_format);
    }

    /**
     * Validate file upload
     */
    private function validateFileUpload(Request $request, ?string $expectedFormat = null): void
    {
        $file = $request->file('file');

        if (! $file || ! $file->isValid()) {
            throw new \Exception(t('invalid_file_upload'));
        }

        // Get file extension and MIME type
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Get allowed file types and sizes
        $extensions = get_meta_allowed_extension();

        // Validate based on expected format
        if ($expectedFormat) {
            $formatKey = strtolower($expectedFormat);

            if (! isset($extensions[$formatKey])) {
                throw new \Exception('Unsupported file format: '.$expectedFormat);
            }

            $allowedExtensions = explode(',', $extensions[$formatKey]['extension']);
            $allowedExtensions = array_map('trim', $allowedExtensions);
            $maxSize = $extensions[$formatKey]['size'] * 1024 * 1024; // Convert MB to bytes

            // Check extension
            if (! in_array('.'.$extension, $allowedExtensions)) {
                throw new \Exception('Invalid file extension. Allowed: '.implode(', ', $allowedExtensions));
            }

            // Check file size
            if ($fileSize > $maxSize) {
                throw new \Exception('File size too large. Maximum: '.$extensions[$formatKey]['size'].'MB');
            }

            // Validate MIME type for security
            $this->validateMimeType($mimeType, $formatKey);
        }
    }

    /**
     * Validate MIME type for security
     */
    private function validateMimeType(string $mimeType, string $format): void
    {
        $allowedMimeTypes = [
            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'video' => ['video/mp4', 'video/3gpp', 'video/quicktime'],
            'document' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
            ],
            'audio' => ['audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/wav', 'audio/aac'],
        ];

        if (isset($allowedMimeTypes[$format]) && ! in_array($mimeType, $allowedMimeTypes[$format])) {
            throw new \Exception('Invalid file type. Detected: '.$mimeType);
        }
    }

    /**
     * Process file upload and return filename
     */
    private function processFileUpload(UploadedFile $file, string $format): string
    {
        // Generate secure filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug($originalName).'_'.time().'.'.$extension;

        // Determine storage directory
        $directory = match (strtolower($format)) {
            'image' => '/campaigns/images',
            'video' => '/campaigns/videos',
            'document' => '/campaigns/documents',
            'audio' => '/campaigns/audio',
            default => '/campaigns'
        };

        // Store file
        $path = $file->storeAs('tenant/'.tenant_id().$directory, $filename, 'public');

        if (! $path) {
            throw new \Exception('Failed to store file');
        }

        return $path;
    }

    // =================================================================
    // UTILITY METHODS
    // =================================================================

    /**
     * Format JSON response
     */
    private function formatResponse(bool $success, $data = null, string $message = '', array $errors = []): array
    {
        return [
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get campaign statistics
     */
    private function getCampaignStats(Campaign $campaign): array
    {
        $details = $campaign->campaign_details();

        return [
            'total_contacts' => $details->count(),
            'sent_count' => $details->where('message_status', 'sent')->count(),
            'pending_count' => $details->where('status', 1)->count(),
            'failed_count' => $details->where('message_status', 'failed')->count(),
            'delivered_count' => $details->where('message_status', 'delivered')->count(),
            'read_count' => $details->where('message_status', 'read')->count(),
        ];
    }
}
