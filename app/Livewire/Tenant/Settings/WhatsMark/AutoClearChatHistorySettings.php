<?php

namespace App\Livewire\Tenant\Settings\WhatsMark;

use App\Models\Tenant\Chat;
use App\Models\Tenant\ChatMessage;
use App\Rules\PurifiedInput;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AutoClearChatHistorySettings extends Component
{
    public ?bool $enable_auto_clear_chat = false;

    public $auto_clear_history_time = null;

    public $showCleanupResults = false;

    public $cleanupResults = null;

    public $isProcessing = false;

    public $tenant_id;

    public $tenant_subdomain;

    protected function rules()
    {
        return [
            'enable_auto_clear_chat' => 'nullable|boolean',
            'auto_clear_history_time' => [
                'nullable',
                'numeric',
                new PurifiedInput(t('sql_injection_error')),
                'required_if:enable_auto_clear_chat,true',
                'min:1',
                'max:365',
            ],
        ];
    }

    public function mount()
    {
        if (! checkPermission('tenant.whatsmark_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $settings = tenant_settings_by_group('whats-mark');
        $this->enable_auto_clear_chat = $settings['enable_auto_clear_chat'] ?? false;
        $this->auto_clear_history_time = $settings['auto_clear_history_time'] ?? null;
        $this->tenant_id = tenant_id();
        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
    }

    public function save()
    {
        if (checkPermission('tenant.whatsmark_settings.edit')) {
            $this->validate();

            $originalSettings = tenant_settings_by_group('whats-mark');

            $newSettings = [
                'enable_auto_clear_chat' => $this->enable_auto_clear_chat,
                'auto_clear_history_time' => $this->auto_clear_history_time,
            ];

            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return ! array_key_exists($key, $originalSettings) || $originalSettings[$key] !== $value;
            }, ARRAY_FILTER_USE_BOTH);

            if (! empty($modifiedSettings)) {
                foreach ($modifiedSettings as $key => $value) {
                    save_tenant_setting('whats-mark', $key, $value);
                }

                $this->notify([
                    'type' => 'success',
                    'message' => t('setting_save_successfully'),
                ]);
            }
        }
    }

    public function runCleanup()
    {
        if (! $this->auto_clear_history_time) {
            $this->notify(['type' => 'warning', 'message' => t('please_specify_days_to_keep')]);

            return;
        }

        $this->isProcessing = true;

        try {

            $daysToKeep = (int) ($this->auto_clear_history_time ?? 30);
            $cutoffDate = Carbon::now()->subDays($daysToKeep);

            DB::beginTransaction();
            // Find messages older than the cutoff date
            $oldMessages = ChatMessage::fromTenant($this->tenant_subdomain)->where('time_sent', '<', $cutoffDate->format('Y-m-d H:i:s'))->get();
            $messageCount = $oldMessages->count();

            if ($messageCount > 0) {
                // Get interaction IDs to potentially delete empty chats later
                $affectedInteractionIds = $oldMessages->pluck('interaction_id')->unique()->toArray();

                // Delete old messages
                ChatMessage::fromTenant($this->tenant_subdomain)->where('time_sent', '<', $cutoffDate->format('Y-m-d H:i:s'))->delete();

                // Clean up empty chats
                $emptyInteractionsCount = 0;
                foreach ($affectedInteractionIds as $interactionId) {
                    $remainingMessages = ChatMessage::fromTenant($this->tenant_subdomain)->where('interaction_id', $interactionId)->count();

                    if ($remainingMessages === 0) {
                        Chat::fromTenant($this->tenant_subdomain)->where('id', $interactionId)->delete();
                        $emptyInteractionsCount++;
                    }
                }

                $this->cleanupResults = [
                    'messagesFound' => $messageCount,
                    'messagesDeleted' => $messageCount,
                    'conversationsDeleted' => $emptyInteractionsCount,
                    'status' => 'success',
                ];
            } else {
                $this->cleanupResults = [
                    'messagesFound' => 0,
                    'messagesDeleted' => 0,
                    'conversationsDeleted' => 0,
                    'status' => 'success',
                ];
            }

            DB::commit();
            $this->showCleanupResults = true;
            $this->notify(['type' => 'success', 'message' => t('chat_cleanup_completed_successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            app_log(
                'Manual chat cleanup failed: '.$e->getMessage(),
                'error',
                $e
            );

            $this->cleanupResults = [
                'status' => 'error',
                'errorMessage' => $e->getMessage(),
            ];
            $this->showCleanupResults = true;
            $this->notify(['type' => 'danger', 'message' => t('chat_cleanup_failed').': '.$e->getMessage()]);
        } finally {
            $this->isProcessing = false;
        }
    }

    public function dismissResults()
    {
        $this->showCleanupResults = false;
        $this->cleanupResults = null;
    }

    public function render()
    {
        return view('livewire.tenant.settings.whats-mark.auto-clear-chat-history-settings');
    }
}
