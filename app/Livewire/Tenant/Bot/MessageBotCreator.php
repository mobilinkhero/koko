<?php

namespace App\Livewire\Tenant\Bot;

use App\Models\Tenant\MessageBot;
use App\Rules\PurifiedInput;
use App\Services\FeatureService;
use Corbital\LaravelEmails\Services\MergeFieldsService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class MessageBotCreator extends Component
{
    use WithFileUploads;

    // Basic bot properties
    public $name;

    public $rel_type;

    public $reply_text;

    public $reply_type = ' ';

    public $trigger = [];

    public $bot_header;

    public $bot_footer;

    // Button options
    public $button1;

    public $button1_id;

    public $button2;

    public $button2_id;

    public $button3;

    public $button3_id;

    public $button_name;

    public $button_url;

    // File upload properties
    public $file;

    public $file_type;

    public $filename;

    protected $featureLimitChecker;

    // Component state management
    public $message_bot;

    public $isUploading = false;

    public $mergeFields;

    public $selectedAssistantId;

    protected $listeners = [
        'upload-started' => 'setUploading',
        'upload-finished' => 'setUploadingComplete',
    ];

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100', new PurifiedInput(t('sql_injection_error'))],
            'rel_type' => ['required'],
            'reply_text' => ['required', 'string', 'max:1024', new PurifiedInput(t('sql_injection_error'))],
            'reply_type' => ['required'],
            'trigger' => ($this->reply_type == 1 || $this->reply_type == 2) ? 'required' : 'nullable',
            'button1' => ['nullable', 'max:20', new PurifiedInput(t('sql_injection_error'))],
            'button1_id' => ['nullable', 'max:256', new PurifiedInput(t('sql_injection_error'))],
            'button2' => ['nullable', 'max:20', new PurifiedInput(t('sql_injection_error'))],
            'button2_id' => ['nullable', 'max:256', new PurifiedInput(t('sql_injection_error'))],
            'button3' => ['nullable', 'max:20', new PurifiedInput(t('sql_injection_error'))],
            'button3_id' => ['nullable', 'max:256', new PurifiedInput(t('sql_injection_error'))],
            'button_name' => ['nullable', 'max:20', new PurifiedInput(t('sql_injection_error'))],
            'button_url' => ['nullable', 'url', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'bot_header' => ['nullable', 'max:60', new PurifiedInput(t('sql_injection_error'))],
            'bot_footer' => ['nullable', 'max:60', new PurifiedInput(t('sql_injection_error'))],
            'file' => ['nullable', 'file'], // 50MB max file size
        ];
    }

    public function mount()
    {
        if (! checkPermission(['tenant.message_bot.edit', 'tenant.message_bot.create'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $messagebotId = request()->route('messagebotId');

        if ($messagebotId) {
            $this->loadExistingBot($messagebotId);
            ! $this->file_type ? $this->file_type = 'image' : '';
        } else {
            $this->message_bot = new MessageBot;
            $this->file_type = 'image';
            $this->rel_type = array_key_first(\App\Enum\Tenant\WhatsAppTemplateRelationType::getRelationType());
            $this->reply_type = array_key_first(\App\Enum\Tenant\WhatsAppTemplateRelationType::getReplyType());
        }
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
     * Set uploading state to true
     */
    public function setUploading()
    {
        $this->isUploading = true;
    }

    /**
     * Set uploading state to false
     */
    public function setUploadingComplete()
    {
        $this->isUploading = false;
    }

    /**
     * Load an existing message bot for editing
     */
    protected function loadExistingBot($messagebotId)
    {
        $this->message_bot = MessageBot::findOrFail($messagebotId);

        // Load basic properties
        $this->name = $this->message_bot->name;
        $this->rel_type = $this->message_bot->rel_type;
        $this->reply_text = $this->message_bot->reply_text;
        $this->reply_type = $this->message_bot->reply_type;
        $this->trigger = $this->message_bot->trigger ? explode(',', $this->message_bot->trigger) : [];
        $this->bot_header = $this->message_bot->bot_header;
        $this->bot_footer = $this->message_bot->bot_footer;
        $this->selectedAssistantId = $this->message_bot->assistant_id ?? 0;

        // Load button properties
        $this->button1 = $this->message_bot->button1;
        $this->button1_id = $this->message_bot->button1_id;
        $this->button2 = $this->message_bot->button2;
        $this->button2_id = $this->message_bot->button2_id;
        $this->button3 = $this->message_bot->button3;
        $this->button3_id = $this->message_bot->button3_id;
        $this->button_name = $this->message_bot->button_name;
        $this->button_url = $this->message_bot->button_url;

        // Load file information if exists
        if (! empty($this->message_bot->filename)) {
            $this->filename = $this->message_bot->filename;
        }
    }

    /**
     * Save the message bot
     */
    public function save()
    {
        if (checkPermission(['tenant.message_bot.edit', 'tenant.message_bot.create'])) {
            $this->validate();
            try {
                // Determine if we're creating or updating
                $isNewRecord = empty($this->message_bot->id);

                // For new records, check if creating one more would exceed the limit
                if ($isNewRecord) {
                    $limit = $this->featureLimitChecker->getLimit('message_bots');

                    // Skip limit check if unlimited (-1) or no limit set (null)
                    if ($limit !== null && $limit !== -1) {
                        $currentCount = MessageBot::where('tenant_id', tenant_id())->count();

                        if ($currentCount >= $limit) {
                            $this->notify([
                                'type' => 'warning',
                                'message' => t('message_bot_limit_reached_upgrade_plan'),
                            ]);

                            return;
                        }
                    }
                }

                $this->message_bot->tenant_id = tenant_id();
                $this->message_bot->name = $this->name;
                $this->message_bot->rel_type = $this->rel_type;
                $this->message_bot->reply_text = $this->reply_text;
                $this->message_bot->reply_type = $this->reply_type;
                $this->message_bot->trigger = ($this->reply_type == 1 || $this->reply_type == 2) ? implode(',', $this->trigger) : null;
                $this->message_bot->bot_header = $this->bot_header;
                $this->message_bot->bot_footer = $this->bot_footer;
                $this->message_bot->button1 = $this->button1;
                $this->message_bot->button1_id = $this->button1_id;
                $this->message_bot->button2 = $this->button2;
                $this->message_bot->button2_id = $this->button2_id;
                $this->message_bot->button3 = $this->button3;
                $this->message_bot->button3_id = $this->button3_id;
                $this->message_bot->button_name = $this->button_name;
                $this->message_bot->button_url = $this->button_url;
                $this->message_bot->is_bot_active = 1;
                $this->message_bot->addedfrom = tenant_id();

                // Handle file upload
                if ($this->file) {
                    // If there was a previous file, remove it
                    if (! empty($this->message_bot->filename)) {
                        // Delete the file from storage
                        Storage::disk('public')->delete($this->message_bot->filename);
                    }

                    $originalName = str_replace(' ', '_', $this->file->getClientOriginalName());
                    $uniqueName = time().'_'.$originalName;
                    $path = $this->file->storeAs('tenant/'.tenant_id().'/message-bot', $uniqueName, 'public');
                    $this->filename = $path;
                    $this->message_bot->filename = $this->filename;
                } elseif ($this->file === false) { // Only remove file if explicitly cleared in UI
                    $this->removeFile();
                }

                $result = apply_filters('message_bot.before_save', ['message_bot' => $this->message_bot, 'livewire' => $this]);

                // Allow filters to return either array or just message_bot
                $this->message_bot = is_array($result) && isset($result['message_bot']) ? $result['message_bot'] : $this->message_bot;

                // Check if there are any changes (including file changes)
                $hasChanges = $this->message_bot->isDirty() ||
                    $this->file !== null || // New file uploaded
                    $this->file === false; // File was cleared

                if ($hasChanges || $isNewRecord) {
                    $this->message_bot->save();

                    if ($isNewRecord) {
                        $this->featureLimitChecker->trackUsage('message_bots');
                    }

                    // Success message
                    if ($isNewRecord) {
                        $this->notify([
                            'type' => 'success',
                            'message' => t('message_bot_saved_successfully'),
                        ], true);
                    } else {
                        $this->notify([
                            'type' => 'success',
                            'message' => t('message_bot_updated_successfully'),
                        ], true);
                    }

                    return redirect()->to(tenant_route('tenant.messagebot.list'));
                }

                return redirect()->to(tenant_route('tenant.messagebot.list'));
            } catch (\Exception $e) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('something_went_wrong').': '.$e->getMessage(),
                ]);
            }
        }
    }

    public function removeFile()
    {

        // If there's a file in the database, delete it from storage
        if (! empty($this->message_bot->filename)) {
            Storage::disk('public')->delete($this->message_bot->filename);
            $this->message_bot->filename = null;
        }

        // Clear the file properties
        $this->file = null;
        $this->filename = null;

        // Save the changes to remove file reference from database
        if ($this->message_bot->exists) {
            $this->message_bot->save();
        }
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('message_bots', MessageBot::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('message_bots', MessageBot::class);
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.tenant.bot.message-bot-creator');
    }
}
