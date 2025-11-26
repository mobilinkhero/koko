<?php

namespace Modules\Tickets\Listeners;

use App\Models\User;
use Corbital\LaravelEmails\Facades\Email;
use Modules\Tickets\Events\TicketAssigned;

class SendTicketAssignedNotification
{
    /**
     * Handle the event.
     */
    public function handle(TicketAssigned $event)
    {
        try {
            $ticket = $event->ticket;
            $assigneeIds = $ticket->assignee_id ?? [];
            $assigneeIds = array_unique(array_merge(array_map('intval', $assigneeIds), json_decode($ticket->department->assignee_id, true) ?? []));

            if (empty($assigneeIds)) {
                app_log('No assignees found for ticket', 'warning', null, [
                    'ticket_id' => $ticket->id,
                ]);

                return;
            }

            // Get all assigned users
            $assignedUsers = User::withoutGlobalScopes()->whereIn('id', $assigneeIds)
                ->where('user_type', 'admin')
                ->where('active', true)
                ->get();

            if ($assignedUsers->isEmpty()) {
                app_log('No valid assigned users found for ticket', 'warning', null, [
                    'ticket_id' => $ticket->id,
                    'assignee_ids' => $assigneeIds,
                ]);

                return;
            }

            // Send notification to each assigned user
            foreach ($assignedUsers as $user) {
                if (! $user->email) {
                    continue;
                }
                $context = [
                    'ticketId' => $ticket->id,
                    'tenantId' => $ticket->tenant_id,
                ];

                $content = render_email_template('ticket-assigned', $context);
                $subject = get_email_subject('ticket-assigned', $context);
                if (is_smtp_valid()) {
                    // Send email to all admin users
                    Email::to($user->email)
                        ->subject($subject)
                        ->content($content)
                        ->send();
                }
            }
        } catch (\Exception $e) {
            app_log('Error sending ticket assigned notification', 'error', $e, [
                'ticket_id' => $ticket->id ?? null,
            ]);

            return false;
        }
    }
}
