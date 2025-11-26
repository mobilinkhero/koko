<?php

namespace App\Livewire\Tenant\Bot;

use App\Models\Tenant\TemplateBot;
use App\Models\Tenant\WhatsappTemplate;
use App\Rules\PurifiedInput;
use App\Services\FeatureService;
use Corbital\LaravelEmails\Services\MergeFieldsService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class TemplateBotCreator extends Component
{
    use WithFileUploads;

    // Model properties
    public $id;

    public $template_bot;

    public $template_name;

    public $rel_type;

    public $template_id;

    public $reply_type;

    public $is_bot_active = 1;

    public $trigger = [];

    public $headerInputs = [];

    public $bodyInputs = [];

    public $footerInputs = [];

    public $mergeFields;

    public $file;

    public $filename;

    protected $featureLimitChecker;

    public $isUploading = false;

    protected $listeners = [
        'refreshComponent' => '$refresh',
        'templates-updated' => 'handleTemplatesUpdated',
    ];

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    public function mount()
    {
        if (! checkPermission(['tenant.template_bot.edit', 'tenant.template_bot.create'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $templatebotId = request()->route('templatebotId');
        if ($templatebotId) {
            $this->loadExistingBot($templatebotId);
        } else {
            $this->template_bot = new TemplateBot;
        }

        // Initialize merge fields for variable substitution
        $this->loadMergeFields();
    }

    public function loadMergeFields()
    {
        $mergeFieldsService = app(MergeFieldsService::class);

        $field = array_merge(
            $mergeFieldsService->getFieldsForTemplate('tenant-contact-group'),
            $mergeFieldsService->getFieldsForTemplate('tenant-other-group'),
        );

        $this->mergeFields = json_encode(array_map(fn ($value) => [
            'key' => ucfirst($value['name']),
            'value' => $value['key'],
        ], $field));
    }

    /**
     * Load an existing template bot for editing
     */
    protected function loadExistingBot($templatebotId)
    {
        $this->template_bot = TemplateBot::findOrFail($templatebotId);
        $this->template_name = $this->template_bot->name;
        $this->reply_type = $this->template_bot->reply_type;
        $this->rel_type = $this->template_bot->rel_type;
        $this->template_id = $this->template_bot->template_id;
        $this->filename = $this->template_bot->filename;
        $this->trigger = $this->template_bot->trigger ? array_filter(explode(',', $this->template_bot->trigger)) : [];
        $this->headerInputs = json_decode($this->template_bot->header_params ?? '[]', true);
        $this->bodyInputs = json_decode($this->template_bot->body_params ?? '[]', true);
        $this->footerInputs = json_decode($this->template_bot->footer_params ?? '[]', true);
        // Get file information if exists
        if ($this->template_bot->filename) {
            $this->filename = $this->template_bot->filename;
        }

        // Initialize merge fields for variable substitution
        $this->loadMergeFields();
    }

    /**
     * Get available templates
     */
    public function getTemplatesProperty()
    {
        return WhatsappTemplate::where('tenant_id', tenant_id())->get();
    }

    /**
     * Handle template updates from frontend
     */
    public function handleTemplatesUpdated($templateId)
    {
        $this->template_id = $templateId;
    }

    /**
     * Validation rules
     */
    protected function rules()
    {
        $rules = [
            'template_name' => ['required', 'string', 'min:3', 'max:100', new PurifiedInput(t('sql_injection_error'))],
            'rel_type' => 'required',
            'template_id' => ['required', 'integer'],
            'reply_type' => ['required', 'integer'],
            'footerInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
            'headerInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
            'bodyInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
        ];

        // If reply type requires triggers, validate them
        if (in_array($this->reply_type, [1, 2])) {
            $rules['trigger'] = 'required|array|min:1';
            $rules['trigger.*'] = 'required|string|min:2';
        }

        // Custom validation for file uploads based on template type
        if ($this->requiresFileUpload()) {
            $rules['file'] = 'required_if:filename,null';
        }

        return $rules;
    }

    /**
     * Custom validation messages
     */
    protected function messages()
    {
        return [
            'template_name.required' => t('bot_name_required'),
            'rel_type.required' => t('relation_type_required'),
            'template_id.required' => t('template_required'),
            'reply_type.required' => t('reply_type_required'),
            'trigger.required' => t('trigger_required'),
            'trigger.*.required' => t('trigger_keyword_required'),
            'file.required_if' => t('file_required'),
        ];
    }

    /**
     * Check if the selected template requires a file upload
     */
    protected function requiresFileUpload()
    {
        if (! $this->template_id) {
            return false;
        }

        $template = WhatsappTemplate::find($this->template_id);
        if (! $template) {
            return false;
        }

        // Check if the template has a header format that requires a file
        $headerFormat = $template->header_format;

        return in_array($headerFormat, ['IMAGE', 'VIDEO', 'DOCUMENT']);
    }

    /**
     * Save the template bot
     */
    public function save()
    {
        if (checkPermission(['tenant.template_bot.edit', 'tenant.template_bot.create'])) {
            $this->validate();

            try {
                // Determine if we're creating or updating
                $isNewRecord = empty($this->template_bot->id);

                // For new records, check if creating one more would exceed the limit
                if ($isNewRecord) {
                    $limit = $this->featureLimitChecker->getLimit('template_bots');

                    // Skip limit check if unlimited (-1) or no limit set (null)
                    if ($limit !== null && $limit !== -1) {
                        $currentCount = TemplateBot::where('tenant_id', tenant_id())->count();

                        if ($currentCount >= $limit) {
                            $this->notify([
                                'type' => 'warning',
                                'message' => t('template_bot_limit_reached_upgrade_plan'),
                            ]);

                            return;
                        }
                    }
                }

                // Handle file upload if present
                if ($this->file) {
                    // Delete old file if exists
                    if (! empty($this->filename) && Storage::disk('public')->exists($this->filename)) {
                        Storage::disk('public')->delete($this->filename);
                    }

                    // Store new file and get path
                    $path = $this->file->store('tenant/'.tenant_id().'/template-bot', 'public');
                    $this->template_bot->filename = $path;
                    $this->filename = $path;
                }

                // Set model attributes
                $this->template_bot->tenant_id = tenant_id();
                $this->template_bot->name = $this->template_name;
                $this->template_bot->rel_type = $this->rel_type;
                $this->template_bot->template_id = $this->template_id;
                $this->template_bot->reply_type = $this->reply_type;
                $this->template_bot->trigger = ($this->reply_type == 1 || $this->reply_type == 2) ? implode(',', $this->trigger) : null;
                $this->template_bot->header_params = json_encode(array_values(array_filter($this->headerInputs)));
                $this->template_bot->body_params = json_encode(array_values(array_filter($this->bodyInputs)));
                $this->template_bot->footer_params = json_encode(array_values(array_filter($this->footerInputs)));

                // Check if any attributes have changed
                $hasChanged = $this->template_bot->isDirty([
                    'name',
                    'rel_type',
                    'template_id',
                    'reply_type',
                    'trigger',
                    'header_params',
                    'body_params',
                    'footer_params',
                    'filename',
                ]);

                // Only save and show notification if there are changes or it's a new record
                if ($isNewRecord || $hasChanged) {
                    // Save to database
                    $this->template_bot->save();

                    if ($isNewRecord) {
                        $this->featureLimitChecker->trackUsage('template_bots');
                    }

                    // Success message
                    if ($isNewRecord) {
                        $this->notify([
                            'type' => 'success',
                            'message' => t('template_bot_created_successfully'),
                        ], true);
                    } elseif ($hasChanged) {
                        $this->notify([
                            'type' => 'success',
                            'message' => t('template_bot_updated_successfully'),
                        ], true);
                    }

                    return redirect()->to(tenant_route('tenant.templatebot.list'));
                }

                // If no changes, redirect without notification
                return redirect()->to(tenant_route('tenant.templatebot.list'));
            } catch (\Exception $e) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('error_saving_template_bot'.$e->getMessage()),
                ]);
            }
        }
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('template_bots', TemplateBot::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('template_bots', TemplateBot::class);
    }

    public function render()
    {
        return view('livewire.tenant.bot.template-bot-creator');
    }
}
