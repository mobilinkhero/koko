<?php

namespace App\Console\Commands;

use App\Facades\Tenant;
use App\Models\Tenant\Chat;
use App\Models\Tenant\ChatMessage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class ClearChatHistory extends Command
{
    use TenantAware;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:clear-chat-history {--tenant=*}';

    protected $tenant;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old chat history based on configured settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->tenant = Tenant::current();

        // Check if auto clear feature is enabled
        if (! get_tenant_setting_by_tenant_id('whats-mark', 'enable_auto_clear_chat', null, $this->tenant->id) || ! get_tenant_setting_by_tenant_id('whats-mark', 'auto_clear_history_time', null, $this->tenant->id)) {
            $this->info('Auto clear chat history is disabled or not configured properly.');

            return;
        }

        $this->info('Starting chat history cleanup process...');

        $daysToKeep = (int) get_tenant_setting_by_tenant_id('whats-mark', 'auto_clear_history_time', null, $this->tenant->id);
        $cutoffDate = Carbon::now()->subDays($daysToKeep);

        try {
            DB::beginTransaction();

            // Find messages older than the cutoff date
            $oldMessages = ChatMessage::fromTenant($this->tenant->subdomain)->where('time_sent', '<', $cutoffDate->format('Y-m-d H:i:s'))->get();

            $messageCount = $oldMessages->count();
            $this->info("Found {$messageCount} messages older than {$daysToKeep} days.");

            if ($messageCount > 0) {
                // Get interaction IDs to potentially delete empty chats later
                $affectedInteractionIds = $oldMessages->pluck('interaction_id')->unique()->toArray();

                // Delete old messages
                ChatMessage::fromTenant($this->tenant->subdomain)->where('time_sent', '<', $cutoffDate->format('Y-m-d H:i:s'))->delete();
                $this->info("Deleted {$messageCount} old messages.");

                // Clean up empty chats (those with no messages left)
                $emptyInteractionsCount = 0;
                foreach ($affectedInteractionIds as $interactionId) {
                    $remainingMessages = ChatMessage::fromTenant($this->tenant->subdomain)->where('interaction_id', $interactionId)->count();

                    if ($remainingMessages === 0) {
                        Chat::fromTenant($this->tenant->subdomain)->where('id', $interactionId)->delete();
                        $emptyInteractionsCount++;
                    }
                }

                $this->info("Deleted {$emptyInteractionsCount} empty chat conversations.");
                $this->info('Chat history cleanup completed successfully.');
            } else {
                $this->info('No old messages to delete.');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during chat history cleanup: '.$e->getMessage());
        }
    }
}
