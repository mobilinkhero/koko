<?php

namespace App\Livewire\Tenant\Contact;

use App\Models\Tenant\Chat;
use App\Models\Tenant\ChatMessage;
use App\Models\Tenant\Contact;
use App\Models\Tenant\ContactNote;
use App\Models\Tenant\WhatsappTemplate;
use App\Rules\PurifiedInput;
use App\Services\FeatureService;
use App\Traits\WhatsApp;
use App\Traits\WithTenantContext;
use Corbital\LaravelEmails\Services\MergeFieldsService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class ContactList extends Component
{
    use WhatsApp, WithFileUploads, WithTenantContext;

    public Contact $contact;

    public ?int $contactId = null;

    public $confirmingDeletion = false;

    public $viewContactModal = false;

    public $showInitiateChatModal = false;

    public $contact_id = null;

    public $template_id;

    public $headerInputs = [];

    public $bodyInputs = [];

    public $file;

    public $filename;

    public $footerInputs = [];

    public $contacts = [];

    public array $selectedStatus = [];

    public $notes = [];

    public $customFields;

    public bool $isBulckDelete = false;

    protected $featureLimitChecker;

    public $tenant_id;

    public $tenant_subdomain;

    public $mergeFields;

    public $pusher_settings;

    public $whatsapp_settings;

    protected $listeners = [
        'editContact' => 'editContact',
        'confirmDelete' => 'confirmDelete',
        'viewContact' => 'viewContact',
        'openViewModal' => 'viewContact',
        'initiateChat' => 'initiateChat',
        'bulkInitiateChatSending' => 'bulkInitiateChatSending',
        'refreshComponent' => '$refresh',
    ];

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;

        $this->bootWithTenantContext();
    }

    public function mount()
    {
        if (! checkPermission(['tenant.contact.view', 'tenant.contact.view_own'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $this->contact = new Contact;
        $this->tenant_id = tenant_id();
        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
    }

    public function createContact()
    {
        $this->redirect(tenant_route('tenant.contacts.save'));
    }

    public function viewContact($contactId)
    {
        // Initialize contact with correct table name
        if (! checkPermission('tenant.contact.view')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $this->contact = new Contact;
        $this->contact->setTable($this->tenant_subdomain.'_contacts');

        // Then load the contact
        $this->contact = Contact::fromTenant($this->tenant_subdomain)->findOrFail($contactId);
        $this->notes = ContactNote::fromTenant($this->tenant_subdomain)->where(['contact_id' => $contactId])->get();

        // Load custom fields with values
        $this->customFields = collect();
        $customFields = $this->contact->getAvailableCustomFields();
        $customFieldsData = $this->contact->custom_fields_data ?? [];

        $this->customFields = collect();
        foreach ($customFields as $field) {
            $value = $customFieldsData[$field->field_name] ?? $field->default_value;
            $this->customFields->push([
                'field' => $field,
                'value' => $value,
                'display_value' => $this->contact->getCustomFieldDisplayValue($field, $value),
            ]);
        }

        $this->viewContactModal = true;
    }

    public function importContact()
    {
        if (! checkPermission('tenant.contact.bulk_import')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        return redirect()->to(tenant_route('tenant.contacts.import_log'));
    }

    public function editContact($contactId)
    {
        $this->contact = Contact::fromTenant($this->tenant_subdomain)->findOrFail($contactId);
        $this->redirect(tenant_route('tenant.contacts.save', ['contactId' => $contactId]));
    }

    public function updatedConfirmingDeletion($value)
    {
        if (! $value) {
            $this->js('window.pgBulkActions.clearAll()');
        }
    }

    public function confirmDelete($contactId)
    {
        $this->contact_id = $contactId;

        $this->isBulckDelete = is_array($this->contact_id) && count($this->contact_id) !== 1 ? true : false;

        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('tenant.contact.delete')) {
            if (is_array($this->contact_id) && count($this->contact_id) !== 0) {
                $selectedIds = $this->contact_id;
                // dispatch(function () use ($selectedIds) {
                Contact::fromTenant($this->tenant_subdomain)->whereIn('id', $selectedIds)
                    ->chunk(100, function ($contacts) {
                        foreach ($contacts as $contact) {
                            $contact->delete();
                        }
                    });
                // })->afterResponse();
                $this->contact_id = null;
                $this->js('window.pgBulkActions.clearAll()');
                $this->notify([
                    'type' => 'success',
                    'message' => t('contacts_delete_successfully'),
                ]);
            } else {

                $contact = Contact::fromTenant($this->tenant_subdomain)->findOrFail($this->contact_id);
                $this->contact_id = null;
                $contact->delete();

                $this->notify([
                    'type' => 'success',
                    'message' => t('contact_delete_success'),
                ]);
            }

            $this->confirmingDeletion = false;
            $this->dispatch('pg:eventRefresh-contact-table');
        }
    }

    public function resetForm()
    {
        $this->dispatch('refreshComponent');
        $this->reset('template_id');
        $this->template_id = '';
    }

    public function initiateChat($id)
    {
        if (checkPermission('tenant.contact.create', 'tenant.contact.edit')) {
            $this->template_id = [];
            $this->resetForm();
            $this->contacts = collect([Contact::fromTenant($this->tenant_subdomain)->with('contact_notes')->findOrFail($id)]);
            $this->loadMergeFields();
            $this->showInitiateChatModal = true;
        } else {
            $this->notify([
                'message' => t('access_denied_note'),
                'type' => 'warning',
            ]);
        }
    }

    public function bulkInitiateChatSending($ids)
    {
        $this->resetForm();
        $this->contacts = Contact::fromTenant($this->tenant_subdomain)->with('contact_notes')->findOrFail($ids);
        $this->loadMergeFields();
        $this->showInitiateChatModal = true;
    }

    public function loadMergeFields()
    {
        $mergeFieldsService = app(MergeFieldsService::class);
        $field = array_merge(
            $mergeFieldsService->getFieldsForTemplate('tenant-other-group'),
            $mergeFieldsService->getFieldsForTemplate('tenant-contact-group')
        );

        $this->mergeFields = json_encode(array_map(fn ($value) => [
            'key' => ucfirst($value['name']),
            'value' => $value['key'],
        ], $field));

        return $this->mergeFields;
    }

    protected function rules()
    {
        return [
            'headerInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
            'bodyInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
            'footerInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
            'template_id' => 'required',
            'file' => 'nullable|file',
        ];
    }

    protected function getFileValidationRules($format)
    {
        return match ($format) {
            'IMAGE' => ['mimes:jpeg,png', 'max:8192'],
            'DOCUMENT' => ['mimes:pdf,doc,docx,txt,ppt,pptx,xlsx,xls', 'max:102400'],
            'VIDEO' => ['mimes:mp4,3gp', 'max:16384'],
            'AUDIO' => ['mimes:mp3,wav,aac,ogg', 'max:16384'],
            default => ['file', 'max:5120'],
        };
    }

    #[Computed]
    public function templates()
    {
        return WhatsappTemplate::where('tenant_id', tenant_id())->get();
    }

    public function updatedShowInitiateChatModal($value)
    {
        if (! $value) {
            $this->js('window.pgBulkActions.clearAll()');
            $this->reset('template_id', 'headerInputs', 'bodyInputs', 'footerInputs', 'file', 'filename');
            $this->dispatch('reset-campaign-select');
        }
    }

    protected function handleFileUpload($format)
    {
        // Ensure storage link exists
        create_storage_link();

        // Delete old file if exists
        if (! empty($this->filename) && Storage::disk('public')->exists($this->filename)) {
            Storage::disk('public')->delete($this->filename);
        }

        // Determine subdirectory by format
        $formatDirectory = match ($format) {
            'IMAGE' => 'images',
            'DOCUMENT' => 'documents',
            'VIDEO' => 'videos',
            'AUDIO' => 'audio',
            default => 'misc',
        };

        // Final path like: tenant/123/init_chat/images
        $directory = 'tenant/'.tenant_id().'/init_chat/'.$formatDirectory;

        // Store the file
        $path = $this->file->storeAs(
            $directory,
            $this->generateFileName(),
            'public'
        );

        $this->filename = $path;

        return $path;
    }

    protected function generateFileName()
    {
        $original = str_replace(' ', '_', $this->file->getClientOriginalName());

        return pathinfo($original, PATHINFO_FILENAME).'_'.time().'.'.$this->file->extension();
    }

    public function save()
    {

        $this->validate();
        try {

            $template = WhatsappTemplate::where('template_id', $this->template_id)->firstOrFail();
            $headerFormat = $template->header_data_format ?? 'TEXT';
            $filename = null;

            // Handle file upload
            if ($this->file) {
                $filename = $this->handleFileUpload($headerFormat);
            }

            foreach ($this->contacts as $contact) {
                $response = $this->processContactChat($contact, $filename);
            }

            $this->showInitiateChatModal = true;

            if ($response['status']) {
                $this->showInitiateChatModal = false;
                $this->notify([
                    'type' => 'success',
                    'message' => t('chat_initiated_successfully'),
                ], );
            } else {
                $this->showInitiateChatModal = false;
                $this->notify([
                    'type' => 'danger',
                    'message' => trim($response['log_data']['response_data'], '"'),
                ], );
            }

            return redirect()->to(tenant_route('tenant.contacts.list'));
        } catch (\Exception $e) {
            whatsapp_log('Error during template sending: '.$e->getMessage(), 'error', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->notify([
                'type' => 'danger',
                'message' => t('something_went_wrong').': '.$e->getMessage(),
            ], true);
        }
    }

    protected function processContactChat(Contact $contact, $filename = null)
    {
        $this->pusher_settings = tenant_settings_by_group('pusher', $this->tenant_id);
        $this->whatsapp_settings = tenant_settings_by_group('whatsapp', $this->tenant_id);

        $template = WhatsappTemplate::where('template_id', $this->template_id)->firstOrFail();
        $headerFormat = $template->header_data_format ?? 'TEXT';

        // File validation (only once, or move outside loop if needed)
        if ($headerFormat !== 'TEXT') {
            $this->validate([
                'file' => array_merge([$this->filename ? 'nullable' : 'required', 'file'], $this->getFileValidationRules($headerFormat)),
            ]);
        }

        $rel_data = array_merge(
            [
                'rel_type' => $contact->type,
                'rel_id' => $contact->id,
            ],
            $template->toArray(),
            [
                'campaign_id' => 0,
                'header_data_format' => $headerFormat,
                'filename' => $filename ?? null,
                'header_params' => json_encode(array_values(array_filter($this->headerInputs))) ?? null,
                'body_params' => json_encode(array_values(array_filter($this->bodyInputs))) ?? null,
                'footer_params' => json_encode(array_values(array_filter($this->footerInputs))) ?? null,
                'header_message' => $template['header_data_text'] ?? null,
                'body_message' => $template['body_data'] ?? null,
                'footer_message' => $template['footer_data'] ?? null,
            ]
        );
        $response = $this->sendTemplate($contact->phone, $rel_data, 'Initiate Chat');

        if (! empty($response['status'])) {
            $header = parseText($rel_data['rel_type'], 'header', $rel_data);
            $body = parseText($rel_data['rel_type'], 'body', $rel_data);
            $footer = parseText($rel_data['rel_type'], 'footer', $rel_data);

            $buttonHtml = '';
            if (! empty($rel_data['buttons_data']) && is_string($rel_data['buttons_data'])) {
                $buttons = json_decode($rel_data['buttons_data']);
                if (is_array($buttons) || is_object($buttons)) {
                    $buttonHtml = "<div class='flex flex-col mt-2 space-y-2'>";
                    foreach ($buttons as $button) {
                        $buttonHtml .= "<button class='bg-gray-100 text-green-500 px-3 py-2 rounded-lg flex items-center justify-center text-xs space-x-2 w-full
                        dark:bg-gray-800 dark:text-green-400'>".e($button->text).'</button>';
                    }
                    $buttonHtml .= '</div>';
                }
            }
            // Header media / text rendering
            $headerData = '';
            $fileExtensions = get_meta_allowed_extension();

            if (! empty($rel_data['filename'])) {
                $extension = strtolower(pathinfo($rel_data['filename'], PATHINFO_EXTENSION));
                $fileType = array_key_first(array_filter($fileExtensions, fn ($data) => in_array('.'.$extension, explode(', ', $data['extension']))));

                if ($rel_data['header_data_format'] == 'IMAGE' && $fileType == 'image') {
                    $headerData = "<a href='".asset('storage/'.$rel_data['filename'])."'>
                    <img src='".asset('storage/'.$rel_data['filename'])."' class='img-responsive rounded-lg object-cover'>
                    </a>";
                } elseif ($rel_data['header_data_format'] == 'VIDEO' && $fileType == 'video') {
                    $headerData = "<a href='".asset('storage/'.$rel_data['filename'])."'>
                    <video src='".asset('storage/'.$rel_data['filename'])."' class='rounded-lg object-cover' controls>
                    </a>";
                } elseif ($rel_data['header_data_format'] == 'DOCUMENT') {
                    $headerData = "<a href='".asset('storage/'.$rel_data['filename'])."' target='_blank' class='btn btn-secondary w-full'>".t('document').'</a>';
                }
            }

            if (empty($headerData) && ($rel_data['header_data_format'] == 'TEXT' || empty($rel_data['header_data_format'])) && ! empty($header)) {
                $headerData = "<span class='font-bold mb-3'>".nl2br(decodeWhatsAppSigns(e($header))).'</span>';
            }

            // Handle phone format
            $phone = ltrim($contact->phone, '+');

            // Get or create chat
            $chat_id = Chat::fromTenant($this->tenant_subdomain)->where([
                ['receiver_id', '=', $phone],
                ['wa_no', '=', $this->whatsapp_settings['wm_default_phone_number']],
                ['wa_no_id', '=', $this->whatsapp_settings['wm_default_phone_number_id']],
            ])->value('id');

            if (empty($chat_id)) {
                $chat_id = Chat::fromTenant($this->tenant_subdomain)->insertGetId([
                    'tenant_id' => $this->tenant_id,
                    'receiver_id' => $phone,
                    'wa_no' => $this->whatsapp_settings['wm_default_phone_number'],
                    'wa_no_id' => $this->whatsapp_settings['wm_default_phone_number_id'],
                    'name' => $contact->firstname.' '.$contact->lastname,
                    'last_message' => $body ?? '',
                    'time_sent' => now(),
                    'type' => $contact->type ?? 'guest',
                    'type_id' => $contact->id ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $chatMessage = ChatMessage::fromTenant($this->tenant_subdomain)->create([
                'tenant_id' => $this->tenant_id,
                'interaction_id' => $chat_id,
                'sender_id' => $this->whatsapp_settings['wm_default_phone_number'],
                'url' => null,
                'message' => "
                    $headerData
                    <p>".nl2br(decodeWhatsAppSigns(e($body ?? '')))."</p>
                    <span class='text-gray-500 text-sm'>".nl2br(decodeWhatsAppSigns(e($footer ?? '')))."</span>
                    $buttonHtml
                ",
                'status' => 'sent',
                'time_sent' => now()->toDateTimeString(),
                'message_id' => $response['data']->messages[0]->id ?? null,
                'staff_id' => 0,
                'type' => 'text',
            ]);

            $chatMessageId = $chatMessage->id;
            Chat::fromTenant($this->tenant_subdomain)->where('id', $chat_id)->update([
                'last_message' => $body ?? '',
                'last_msg_time' => now(),
            ]);
            if (! empty($this->pusher_settings['app_key']) && ! empty($this->pusher_settings['app_secret']) && ! empty($this->pusher_settings['app_id']) && ! empty($this->pusher_settings['cluster'])) {
                // Use centralized notification method with enhanced metadata
                \App\Http\Controllers\Whatsapp\WhatsAppWebhookController::triggerChatNotificationStatic($chat_id, $chatMessageId, $this->tenant_id, false);
            }
        }

        return $response;
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

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-contact-table');
    }

    public function render()
    {
        return view('livewire.tenant.contact.contact-list');
    }
}
