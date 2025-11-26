<?php

namespace Modules\Tickets\Listeners;

use App\Jobs\SendTicketReplyEmail;
use App\Models\Tenant;
use Modules\Tickets\Events\TicketReplyCreated;

class SendTicketReplyNotification
{
    public function handle(TicketReplyCreated $event): void
    {
        $reply = $event->reply;

        if (! $event->sendNotification) {
            return;
        }

        if ($reply->user_type === 'system') {
            return;
        }

        try {
            // Get tenant ID and set context before dispatching
            $tenantId = $reply->ticket->tenant_id;
            // Find and make current tenant
            $tenant = Tenant::find($tenantId);
            $tenant->makeCurrent();
            SendTicketReplyEmail::dispatch($reply);
        } catch (\Exception $e) {
            app_log('JOB DISPATCH FAILED: '.$e->getMessage(), 'error');
        }
    }
}
