<?php

namespace App\Http\Controllers\Tenant;

use App\Enum\Tenant\Languages;
use App\Http\Controllers\Controller;
use App\Models\Tenant\AiPrompt;
use App\Models\Tenant\CannedReply;
use App\Models\Tenant\Chat;
use App\Models\Tenant\ChatMessage;
use App\Models\Tenant\Contact;
use App\Models\Tenant\Group;
use App\Models\Tenant\Source;
use App\Models\Tenant\Status;
use App\Models\Tenant\WhatsappTemplate;
use App\Models\User;
use App\Rules\PurifiedInput;
use App\Traits\Ai;
use App\Traits\WhatsApp;
use Corbital\LaravelEmails\Services\MergeFieldsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ManageChat extends Controller
{
    use Ai;
    use WhatsApp;

    public $tenant_id;

    public $tenant_subdomain;

    protected $pageSize = 20; // Number of records per page

    public function __construct()
    {
        $this->tenant_id = tenant_id();
        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
    }

    public function index()
    {

        if (! checkPermission(['tenant.chat.view', 'tenant.chat.read_only'])) {
            session()->flash('notification', ['type' => 'danger', 'message' => t('access_denied_note')]);

            return redirect()->route('tenant.dashboard');
        }
        // Update assignees and agents from contacts
        $this->syncAgentsWithContacts();

        // Get all chats with unread message count
        $chats = $this->getChatsForIndex();

        // Load all necessary data for the view
        $data = [
            'chats' => $chats,
            'ai_prompt' => AiPrompt::select(['id', 'name', 'action'])->get(),
            'canned_reply' => CannedReply::select(['id', 'added_from', 'description', 'title', 'is_public'])->get(),
            'users' => User::select(['id', 'firstname', 'lastname', 'is_admin'])->get(),
            'sources' => Source::all(),
            'languages' => Languages::all(),
            'selectedAgent' => [],
            'readOnlyPermission' => (! (Auth::user()->is_admin) && checkPermission('tenant.chat.read_only')) ? 0 : 1,
            'user_is_admin' => Auth::user()->is_admin,
            'enable_supportagent' => get_tenant_setting_from_db('whats-mark', 'Only agents can chat'),
            'login_user' => Auth::id(),
            'templates' => WhatsappTemplate::where('tenant_id', $this->tenant_id)->select(['id', 'template_id', 'template_name', 'header_data_text', 'footer_data', 'buttons_data', 'body_data', 'header_params_count', 'body_params_count', 'footer_params_count', 'language', 'header_data_format'])->get(),
            'statuses' => Status::forTenant($this->tenant_id)->get(),
            'groups' => Group::forTenant($this->tenant_id)->select(['id', 'name'])->get(),
            'subdomain' => $this->tenant_subdomain,
        ];

        return view('tenant.chat.manage-chat', $data);
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

    public function loadMergeFields()
    {
        $mergeFieldsService = app(MergeFieldsService::class);

        $field = array_merge(
            $mergeFieldsService->getFieldsForTemplate('tenant-other-group'),
            $mergeFieldsService->getFieldsForTemplate('tenant-contact-group')
        );

        //  $this->reset('mergeFields');

        $mergeFields = json_encode(array_map(fn ($value) => [
            'key' => ucfirst($value['name']),
            'value' => $value['key'],
        ], $field));

        return $mergeFields;
    }

    protected function handleFileUpload($format, $file, $filename)
    {

        if ($filename) {
            create_storage_link();
            Storage::disk('public')->delete($filename);
        }

        $directory = match ($format) {
            'IMAGE' => 'intiate_chat/images',
            'DOCUMENT' => 'intiate_chat/documents',
            'VIDEO' => 'intiate_chat/videos',
            'AUDIO' => 'intiate_chat/audio',
            default => 'intiate_chat',
        };

        $path = 'tenant/'.tenant_id().'/'.$directory;

        // Call storeAs() on the UploadedFile object
        $filename = $file->storeAs(
            $path,
            $this->generateFileName($file),
            'public'
        );

        return $filename; // Optionally return the saved filename
    }

    public function save(Request $request, $subdomain, $chatId)
    {

        try {
            $request->validate($this->rules());

            $templateId = $request->input('template_id');
            $headerInputs = json_decode($request->input('header_inputs'), true);
            $bodyInputs = json_decode($request->input('body_inputs'), true);
            $footerInputs = json_decode($request->input('footer_inputs'), true);
            $template = WhatsappTemplate::where('template_id', $templateId)->firstOrFail();
            $headerFormat = $template->header_data_format;
            $chat = Chat::fromTenant($this->tenant_subdomain)->where('id', $chatId)->firstOrFail();
            $contact = Contact::fromTenant($this->tenant_subdomain)->where('id', $chat->type_id)->firstOrFail();
            $file = $request->input('file');
            $filename = null;

            // Handle file upload
            if ($file) {
                $filename = $this->handleFileUpload($headerFormat, $file, $filename);
            }
            $rel_data = array_merge(
                [
                    'rel_type' => $chat->type,
                    'rel_id' => $contact->id,
                ],
                $template->toArray(),
                [
                    'campaign_id' => 0,
                    'header_data_format' => $headerFormat,
                    'filename' => $filename,
                    'header_params' => json_encode(array_values(array_filter($headerInputs))),
                    'body_params' => json_encode(array_values(array_filter($bodyInputs))),
                    'footer_params' => json_encode(array_values(array_filter($footerInputs ?? []))),
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
                            $buttonHtml .= "<button class='bg-gray-100 text-success-500 px-3 py-2 rounded-lg flex items-center justify-center text-xs space-x-2 w-full
                        dark:bg-gray-800 dark:text-success-400'>".e($button->text).'</button>';
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
                    ['wa_no', '=', get_tenant_setting_from_db('whatsapp', 'wm_default_phone_number')],
                    ['wa_no_id', '=', get_tenant_setting_from_db('whatsapp', 'wm_default_phone_number_id')],
                ])->value('id');

                if (empty($chat_id)) {
                    $chat_id = Chat::fromTenant($this->tenant_subdomain)->insertGetId([
                        'receiver_id' => $phone,
                        'wa_no' => get_tenant_setting_from_db('whatsapp', 'wm_default_phone_number'),
                        'wa_no_id' => get_tenant_setting_from_db('whatsapp', 'wm_default_phone_number_id'),
                        'name' => $contact->firstname.' '.$contact->lastname,
                        'last_message' => $body ?? '',
                        'time_sent' => now(),
                        'type' => $contact->type ?? 'guest',
                        'type_id' => $contact->id ?? '',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'tenant_id' => $this->tenant_id,
                    ]);
                }

                $chatMessage = ChatMessage::fromTenant($this->tenant_subdomain)->create([
                    'interaction_id' => $chat_id,
                    'sender_id' => get_tenant_setting_from_db('whatsapp', 'wm_default_phone_number'),
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
                    'tenant_id' => $this->tenant_id,
                ]);

                $chatMessageId = $chatMessage->id;
                Chat::fromTenant($this->tenant_subdomain)->where('id', $chat_id)->update([
                    'last_message' => $body ?? '',
                    'time_sent' => now(),
                ]);
                $pusher_settings = tenant_settings_by_group('pusher', $this->tenant_id);
                if (
                    ! empty($pusher_settings['app_key']) && ! empty($pusher_settings['app_secret']) && ! empty($pusher_settings['app_id']) && ! empty($pusher_settings['cluster'])
                ) {
                    // Use centralized notification method with enhanced metadata
                    \App\Http\Controllers\Whatsapp\WhatsAppWebhookController::triggerChatNotificationStatic($chat_id, $chatMessageId, $this->tenant_id, false);
                }
            }

            return json_encode($response);
        } catch (\Exception $e) {
            whatsapp_log(t('error_during_template_sending ').$e->getMessage(), 'error', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            return response()->json([
                'status' => false,
                'message' => t('something_went_wrong'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get messages for a specific chat
     */
    public function messagesGet($subdomain, $chatId, $lastMessageId = 0)
    {
        $query = ChatMessage::fromTenant($this->tenant_subdomain)->where('interaction_id', $chatId);

        // If lastMessageId is provided, get messages older than this ID
        if (! empty($lastMessageId)) {
            $query->where('id', '<', $lastMessageId);
        }

        $messages = $query->orderBy('id', 'desc')
            ->take(20)
            ->get()
            ->map(function ($message) {
                if (! empty($message->url)) {
                    $message->url = asset('storage/whatsapp-attachments/'.ltrim($message->url, '/'));
                }

                return $message;
            })
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, $chatId)
    {
        $chat = Chat::fromTenant($this->tenant_subdomain)->findOrFail($chatId);
        $chat->messages()->where('is_read', 0)->update(['is_read' => 1]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove a message
     */
    public function removeMessage($subdomain, $messageId)
    {
        $chatMessage = DB::table($this->tenant_subdomain.'_chat_messages as chat_messages')
            ->join($this->tenant_subdomain.'_chats as chats', 'chat_messages.interaction_id', '=', 'chats.id')
            ->where('chat_messages.id', $messageId)

            ->select('chat_messages.id')
            ->first();

        if ($chatMessage) {
            DB::table($this->tenant_subdomain.'_chat_messages')->where('id', $messageId)->delete();

            return response()->json([
                'success' => true,
                'message' => t('message_deleted_successfully'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => t('message_not_found'),
        ], 404);
    }

    /**
     * Delete a chat
     */
    public function removeChat($subdomain, $chatId)
    {

        if (! checkPermission('chat.delete')) {
            session()->flash('notification', ['type' => 'danger', 'message' => t('access_denied_note')]);

            return redirect()->route('admin.dashboard');
        }

        $chat = Chat::fromTenant($this->tenant_subdomain)->findOrFail($chatId);

        $chat->delete();

        return response()->json([
            'success' => true,
            'message' => t('chat_delete_successfully'),
        ]);
    }

    /**
     * Assign support agent to chat
     */
    public function assignSupportAgent(Request $request, $subdomain, $chatId)
    {

        $agentsId = $request->input('agent_ids');
        try {

            $chat = Chat::fromTenant($this->tenant_subdomain)->findOrFail($chatId);

            $agents = is_array($agentsId) ? implode(',', $agentsId) : $agentsId;

            if ($chat->type == 'lead' || $chat->type == 'customer') {
                $assign_id = Contact::fromTenant($this->tenant_subdomain)->where('id', $chat->type_id)->value('assigned_id');
            }

            $chat->update([
                'agent' => json_encode([
                    'assign_id' => $assign_id ?? 0,
                    'agents_id' => $agents ?? '',
                ]),
            ]);

            $agent_layout = $this->getSupportAgentView($subdomain, $chatId, true);

            return response()->json([
                'success' => true,
                'message' => t('support_agent_assigned_successfully'),
                'agent_layout' => $agent_layout['agent_layout'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => t('failed_to_assign_agent').$e->getMessage(),
            ]);
        }
    }

    public function userInformation(Request $request)
    {
        $type = $request->input('type');
        $contact_id = $request->input('type_Id');
        if (! empty($contact_id) && $type != 'guest') {
            $contact = Contact::fromTenant($this->tenant_subdomain)
                ->with(['source:id,name', 'status:id,name,color'])
                ->where(['type' => $type, 'id' => $contact_id])
                ->first();

            if ($contact) {
                $contactData = $contact->toArray();
                $contactData['groups'] = $contact->groups()->toArray(); // Convert groups collection to array

                return [$contactData];
            }
        }

        return [];
    }

    /**
     * Get support agent view
     */
    public function getSupportAgentView($subdomain, $chatId, $isReturn = false)
    {

        $chat = Chat::fromTenant($this->tenant_subdomain)->find($chatId);

        ChatMessage::fromTenant($this->tenant_subdomain)->where('interaction_id', $chatId)->update(['is_read' => 1]);

        if (! $chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $agentData = json_decode($chat->agent, true) ?? [];

        // Ensure 'agents_id' is an array
        $agentsIds = isset($agentData['agents_id']) && is_array($agentData['agents_id'])
            ? $agentData['agents_id']
            : explode(',', $agentData['agents_id'] ?? '');

        // Collect unique user IDs (assign_id + agents_id)
        $userIds = collect(array_merge([$agentData['assign_id'] ?? null], $agentsIds))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Fetch users with profile images and names
        $users = User::whereIn('id', $userIds)
            ->get(['id', 'firstname', 'lastname', 'avatar'])
            ->keyBy('id');

        // Generate agent layout
        $layout = '<div id="agent-container" x-data="{ openDropdown: false }" class="relative" wire:ignore>
                        <div class="flex items-center">';

        if ($users->count() === 1) {
            $user = $users->first();
            $profileImage = $this->getProfileImage($user->avatar);
            $fullName = e(trim($user->firstname.' '.$user->lastname));

            $layout .= "<img src='{$profileImage}' class='rounded-full h-7 w-7 object-cover ring-1 bg-gray-200 dark:bg-gray-700 cursor-pointer'
                        x-on:click.prevent='openDropdown = !openDropdown' data-tippy-content='{$fullName}'>";
        } else {
            $isMobile = request()->header('User-Agent') && preg_match('/(Mobile|Android|iPhone|iPad)/i', request()->header('User-Agent'));
            $maxToShow = $isMobile ? 0 : 3;
            $i = 0;

            foreach ($users as $user) {
                if ($i >= $maxToShow) {
                    break;
                }
                $profileImage = $this->getProfileImage($user->avatar);
                $fullName = e(trim($user->firstname.' '.$user->lastname));

                $layout .= "<img src='{$profileImage}' class='rounded-full h-7 w-7 object-cover ring-1 -ml-2 first:ml-0'
                            x-on:click.prevent='openDropdown = !openDropdown' data-tippy-content='{$fullName}'>";
                $i++;
            }

            if ($users->count() > $maxToShow) {
                $layout .= " <svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'
                            class='rounded-full bg-[#f4f4f4] dark:bg-[#050b14] dark:text-slate-400 w-7 h-7 object-cover cursor-pointer ring-1  -ml-2 first:ml-0'
                            x-on:click.prevent='openDropdown = !openDropdown' data-tippy-content='More'>
                    <path stroke-linecap='round' stroke-linejoin='round' d='M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z' />
                </svg>";
            }
        }

        $layout .= '</div>
                    <ul x-show="openDropdown" x-on:click.away="openDropdown = false"
                        class="absolute flex right-0 mt-2 bg-white dark:bg-gray-800 rounded-md shadow-lg p-2 z-20">
                        <li>
                            <div class="m-2 flex space-x-2 w-56 overflow-x-auto">';

        foreach ($users as $user) {
            $profileImage = $this->getProfileImage($user->avatar);
            $fullName = e(trim($user->firstname.' '.$user->lastname));

            $layout .= "<div class='flex items-center space-x-2 shrink-0'>
                            <img src='{$profileImage}' class='rounded-full h-8 w-8 object-cover ring-1 text-xs my-2' data-tippy-content='{$fullName}'>
                        </div>";
        }

        $layout .= '</div>
                        </li>
                    </ul>
                </div>';

        if ($isReturn ?? false) {
            return [
                'chat_id' => $chatId,
                'agent_layout' => $layout,
            ];
        }

        return response()->json([
            'chat_id' => $chatId,
            'agent_layout' => $layout,
        ]);
    }

    /**
     * Process AI response
     */
    public function processAiResponse(Request $request)
    {
        try {
            $data = [
                'menu' => $request->input('menu'),
                'submenu' => $request->input('submenu'),
                'input_msg' => $request->input('input_msg'),
            ];

            $response = $this->aiResponse($data);

            if ($response['status']) {
                return response()->json([
                    'success' => true,
                    'message' => $response['message'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response['message'],
                ]);
            }
        } catch (\Throwable $e) {
            whatsapp_log('Exception in AI response processing: '.$e->getMessage(), 'error', [
                'menu' => $request->input('menu'),
                'submenu' => $request->input('submenu'),
                'input_msg' => $request->input('input_msg'),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get new chat message for pusher
     */
    public static function newChatMessage($chat_id, $message_id, $tenant_id)
    {
        $subdomain = tenant_subdomain_by_tenant_id($tenant_id);
        $chatTableName = $subdomain.'_chats';
        $messageTableName = $subdomain.'_chat_messages';

        // Get chat with unread count
        $chat = Chat::fromTenant($subdomain)
            ->select("{$chatTableName}.*")
            ->selectRaw("(
                SELECT COUNT(*)
                FROM {$messageTableName}
                WHERE interaction_id = {$chatTableName}.id
                AND is_read = 0
                AND tenant_id = ?
            ) as unreadmessagecount", [$tenant_id])
            ->where("{$chatTableName}.tenant_id", $tenant_id)
            ->findOrFail($chat_id);

        // Get message directly
        $message = ChatMessage::fromTenant($subdomain)
            ->select('*')
            ->from($messageTableName)
            ->where([
                'id' => $message_id,
                'tenant_id' => $tenant_id,
                'interaction_id' => $chat_id,
            ])
            ->first();

        // Transform message URL if exists
        if ($message && ! empty($message->url)) {
            $message->url = asset('storage/whatsapp-attachments/'.ltrim($message->url, '/'));
        }

        // Add messages to chat object
        $chat->messages = collect([$message]);

        return $chat;
    }

    /**
     * Get formatted profile image URL or default image.
     */
    private function getProfileImage($profileUrl)
    {
        return $profileUrl
            ? asset('storage/'.$profileUrl)
            : asset('img/avatar-agent.svg');
    }

    /**
     * Check if user has permission to access chat
     */
    private function hasPermission()
    {
        return checkPermission(['tenant.chat.view', 'tenant.chat.read_only']);
    }

    /**
     * Sync agents with contacts
     */
    private function syncAgentsWithContacts()
    {
        $chatTable = "{$this->tenant_subdomain}_chats";
        $contactTable = "{$this->tenant_subdomain}_contacts";

        DB::table($chatTable.' as chat')
            ->join($contactTable.' as contact', 'contact.id', '=', 'chat.type_id')
            ->whereIn('chat.type', ['lead', 'customer'])
            ->update([
                'chat.agent' => DB::raw("
            JSON_SET(
                COALESCE(NULLIF(chat.agent, ''), '{}'),
                '$.assign_id', contact.assigned_id,
                '$.agents_id', IF(
                    JSON_CONTAINS_PATH(COALESCE(NULLIF(chat.agent, ''), '{}'), 'one', '$.agents_id'),
                    JSON_UNQUOTE(JSON_EXTRACT(COALESCE(NULLIF(chat.agent, ''), '{}'), '$.agents_id')),
                    0
                )
            )
        "),
                'chat.updated_at' => now(),
            ]);
    }

    protected function generateFileName($file)
    {
        $original = str_replace(' ', '_', $file->getClientOriginalName());

        return pathinfo($original, PATHINFO_FILENAME).'_'.time().'.'.$file->extension();
    }

    /**
     * Get all chats for the index page (initial load)
     */
    private function getChatsForIndex()
    {
        $subdomain = $this->tenant_subdomain;
        $chatTable = $subdomain.'_chats';
        $chatMessageTable = $subdomain.'_chat_messages';
        $contactTable = $subdomain.'_contacts';

        $query = Chat::fromTenant($subdomain)
            ->select("$chatTable.*")
            ->selectSub(function ($subquery) use ($chatMessageTable, $chatTable) {
                $subquery->from("$chatMessageTable as sub")
                    ->selectRaw('count(*)')
                    ->whereRaw('sub.is_read = 0')
                    ->whereColumn('sub.interaction_id', "$chatTable.id");
            }, 'unreadmessagecount')
            ->leftJoin(
                DB::raw("(
                    SELECT interaction_id, MAX(time_sent) as latest_msg_time
                    FROM {$chatMessageTable}
                    WHERE tenant_id = {$this->tenant_id}
                    GROUP BY interaction_id
                ) as latest_msgs"),
                "$chatTable.id",
                '=',
                'latest_msgs.interaction_id'
            )
            ->where("$chatTable.tenant_id", $this->tenant_id);

        // Always left join with contacts to include contact data
        $query->leftJoin("$contactTable as contact", function ($join) use ($chatTable) {
            $join->on('contact.id', '=', "$chatTable.type_id")
                ->whereIn("$chatTable.type", ['lead', 'customer']);
        })
            ->addSelect([
                'contact.source_id as contact_source_id',
                'contact.status_id as contact_status_id',
                'contact.group_id as contact_groups',
                'contact.assigned_id as contact_assigned_id',
            ]);

        // Order by the latest message time from either the messages table or the chat's time_sent
        $query->orderByRaw('COALESCE(latest_msgs.latest_msg_time, time_sent) DESC');

        $onlyAgentsCanChat = get_tenant_setting_from_db('whats-mark', 'only_agents_can_chat', false);

        if ($onlyAgentsCanChat && ! Auth::user()->is_admin) {
            $userId = Auth::id();

            $query->where(function ($q) use ($userId) {
                $q->whereRaw("JSON_EXTRACT(agent, '$.assign_id') = ?", [$userId])
                    ->orWhereRaw("JSON_CONTAINS(JSON_EXTRACT(agent, '$.agents_id'), ?)", ['"'.$userId.'"']);
            });
        }

        return $query->take($this->pageSize)->get()->toArray();
    }

    /**
     * Get filtered chat data via POST request
     */
    public function getChats(Request $request, $subdomain = null, $lastchatid = 0)
    {
        // Handle both route parameter and request body for lastChatId
        $lastChatId = $request->input('lastChatId', $lastchatid);
        $relationType = $request->input('relationType', '');
        $sourceId = $request->input('sourceId', '');
        $statusId = $request->input('statusId', '');
        $groupId = $request->input('groupId', '');
        $agentId = $request->input('agentId', '');
        $readStatus = $request->input('readStatus', ''); // New read/unread filter

        $subdomain = $this->tenant_subdomain;
        $chatTable = $subdomain.'_chats';
        $chatMessageTable = $subdomain.'_chat_messages';
        $contactTable = $subdomain.'_contacts';

        $query = Chat::fromTenant($subdomain)
            ->select("$chatTable.*")
            ->selectSub(function ($subquery) use ($chatMessageTable, $chatTable) {
                $subquery->from("$chatMessageTable as sub")
                    ->selectRaw('count(*)')
                    ->whereRaw('sub.is_read = 0')
                    ->whereColumn('sub.interaction_id', "$chatTable.id");
            }, 'unreadmessagecount')
            ->leftJoin(
                DB::raw("(
                    SELECT interaction_id, MAX(time_sent) as latest_msg_time
                    FROM {$chatMessageTable}
                    WHERE tenant_id = {$this->tenant_id}
                    GROUP BY interaction_id
                ) as latest_msgs"),
                "$chatTable.id",
                '=',
                'latest_msgs.interaction_id'
            )
            ->where("$chatTable.tenant_id", $this->tenant_id);

        // Always left join with contacts for filtering purposes and to include contact data
        $query->leftJoin("$contactTable as contact", function ($join) use ($chatTable) {
            $join->on('contact.id', '=', "$chatTable.type_id")
                ->whereIn("$chatTable.type", ['lead', 'customer']);
        })
            ->addSelect([
                'contact.source_id as contact_source_id',
                'contact.status_id as contact_status_id',
                'contact.group_id as contact_groups',
                'contact.assigned_id as contact_assigned_id',
            ]);

        // Apply filtering logic
        if (! empty($relationType)) {
            $query->where("$chatTable.type", $relationType);
        }

        // Apply source filter (only applies to lead/customer chats)
        if (! empty($sourceId)) {
            $query->where('contact.source_id', $sourceId)
                ->whereIn("$chatTable.type", ['lead', 'customer']);
        }

        // Apply status filter (only applies to lead/customer chats)
        if (! empty($statusId)) {
            $query->where('contact.status_id', $statusId)
                ->whereIn("$chatTable.type", ['lead', 'customer']);
        }

        // Apply group filter - groups are stored as JSON array in contact.group_id (only applies to lead/customer chats)
        if (! empty($groupId)) {
            $query->whereRaw('JSON_CONTAINS(contact.group_id, ?)', [json_encode((int) $groupId)])
                ->whereIn("$chatTable.type", ['lead', 'customer']);
        }

        // Apply agent filter - agents are stored as JSON in chat.agent
        if (! empty($agentId)) {
            $query->where(function ($q) use ($agentId) {
                $q->whereRaw("JSON_EXTRACT(agent, '$.assign_id') = ?", [(int) $agentId])
                    ->orWhereRaw("JSON_CONTAINS(JSON_EXTRACT(agent, '$.agents_id'), ?)", ['"'.(int) $agentId.'"']);
            });
        }

        // Apply read/unread filter - based on whether chat has unread messages
        if (! empty($readStatus)) {
            if ($readStatus === 'unread') {
                // Show only chats with unread messages (unreadmessagecount > 0)
                $query->havingRaw('unreadmessagecount > 0');
            } elseif ($readStatus === 'read') {
                // Show only chats with no unread messages (unreadmessagecount = 0)
                $query->havingRaw('unreadmessagecount = 0');
            }
        }

        // Handle pagination with lastChatId
        if (! empty($lastChatId)) {
            // For pagination, get chats with messages older than the last chat's last message time
            $lastChat = Chat::fromTenant($subdomain)
                ->leftJoin(
                    DB::raw("(
                        SELECT interaction_id, MAX(time_sent) as latest_msg_time
                        FROM {$chatMessageTable}
                        WHERE tenant_id = {$this->tenant_id}
                        GROUP BY interaction_id
                    ) as latest_msgs"),
                    "$chatTable.id",
                    '=',
                    'latest_msgs.interaction_id'
                )
                ->where("$chatTable.id", $lastChatId)
                ->select(DB::raw('COALESCE(latest_msgs.latest_msg_time, time_sent) as last_activity'))
                ->first();

            if ($lastChat) {
                $query->whereRaw('COALESCE(latest_msgs.latest_msg_time, time_sent) < ?', [$lastChat->last_activity]);
            }
        }

        // Order by the latest message time from either the messages table or the chat's time_sent
        $query->orderByRaw('COALESCE(latest_msgs.latest_msg_time, time_sent) DESC');

        // Apply agent permissions
        $onlyAgentsCanChat = get_tenant_setting_from_db('whats-mark', 'only_agents_can_chat', false);

        if ($onlyAgentsCanChat && ! Auth::user()->is_admin) {
            $userId = Auth::id();

            $query->where(function ($q) use ($userId) {
                $q->whereRaw("JSON_EXTRACT(agent, '$.assign_id') = ?", [$userId])
                    ->orWhereRaw("JSON_CONTAINS(JSON_EXTRACT(agent, '$.agents_id'), ?)", ['"'.$userId.'"']);
            });
        }

        $chats = $query->take($this->pageSize)->get()->toArray();

        // If this is a pagination request, return JSON response
        if (! empty($lastChatId) || $request->isMethod('post')) {
            // Include metadata for filtering if this is the initial request (lastChatId = 0)
            $responseData = [
                'chats' => $chats,
            ];

            // Add metadata only for initial load or when specifically requested
            if (empty($lastChatId)) {
                $responseData['metadata'] = [
                    'sources' => Source::forTenant($this->tenant_id)->select(['id', 'name'])->get(),
                    'statuses' => Status::forTenant($this->tenant_id)->select(['id', 'name', 'color'])->get(),
                    'groups' => Group::forTenant($this->tenant_id)->select(['id', 'name'])->get(),
                    'users' => User::select(['id', 'firstname', 'lastname', 'is_admin'])->get(),
                ];
            }

            return response()->json($responseData);
        }

        return $chats;
    }

    /**
     * Update contact status
     */
    public function updateContactStatus(Request $request)
    {

        $request->validate([
            'contact_id' => 'required|integer',
            'status_id' => 'required|integer|exists:statuses,id,tenant_id,'.$this->tenant_id,
        ]);

        try {

            $contact = Contact::fromTenant($this->tenant_subdomain)->findOrFail($request->contact_id);
            $contact->update([
                'status_id' => $request->status_id,
                'last_status_change' => now(),
            ]);

            $status = Status::forTenant($this->tenant_id)->findOrFail($request->status_id);

            return response()->json([
                'success' => true,
                'message' => 'Contact status updated successfully',
                'status' => [
                    'id' => $status->id,
                    'name' => $status->name,
                    'color' => $status->color,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update contact status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update contact groups
     */
    public function updateContactGroups(Request $request)
    {
        try {
            $request->validate([
                'contact_id' => 'required|integer',
                'group_ids' => 'nullable|array',
                'group_ids.*' => 'integer|exists:groups,id,tenant_id,'.$this->tenant_id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: '.collect($e->errors())->flatten()->implode(', '),
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $contact = Contact::fromTenant($this->tenant_subdomain)->findOrFail($request->contact_id);
            $groupIds = $request->group_ids ?? [];

            $contact->setGroups($groupIds);

            // Get updated groups information
            $groups = Group::forTenant($this->tenant_id)
                ->whereIn('id', $groupIds)
                ->select('id', 'name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Contact groups updated successfully',
                'groups' => $groups,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update contact groups',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
            ], 500);
        }
    }

    /**
     * Update contact source
     */
    public function updateContactSource(Request $request)
    {
        try {
            $request->validate([
                'contact_id' => 'required|integer',
                'source_id' => 'required|integer|exists:sources,id,tenant_id,'.$this->tenant_id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: '.collect($e->errors())->flatten()->implode(', '),
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $contact = Contact::fromTenant($this->tenant_subdomain)->findOrFail($request->contact_id);
            $contact->update([
                'source_id' => $request->source_id,
            ]);

            $source = Source::forTenant($this->tenant_id)->findOrFail($request->source_id);

            return response()->json([
                'success' => true,
                'message' => 'Contact source updated successfully',
                'source' => [
                    'id' => $source->id,
                    'name' => $source->name,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update contact source',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
            ], 500);
        }
    }
}
