<?php

namespace Modules\Tickets\Listeners;

use App\Models\Tenant;
use Corbital\LaravelEmails\Facades\Email;
use Modules\Tickets\Events\TicketCreated;

class SendTicketCreatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(TicketCreated $event): void
    {
        // Set the tenant context for this job
        if (isset($event->tenantId)) {
            // Find and switch to the correct tenant
            $tenant = Tenant::find($event->tenantId);
            if ($tenant) {
                $tenant->makeCurrent();
            }
        }

        $ticket = $event->ticket;

        try {
            // Send notification to admin users
            $this->sendAdminNotification($ticket, $event->tenantId);

            // Send notification to department assignees
            $this->sendDepartmentAssigneesNotification($ticket, $event->tenantId);
        } catch (\Exception $e) {
            app_log('Error sending ticket created notification', 'error', $e, [
                'ticket_id' => $ticket->id,
                'tenant_id' => $event->tenantId ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification to admin
     */
    protected function sendAdminNotification($ticket, $tenantId = null)
    {
        // Find all admin users using our helper method
        $adminUsers = \App\Helpers\TicketHelper::getAdminUsersForNotification();

        if (empty($adminUsers)) {
            return;
        }

        // Prepare context with all necessary data for merge fields
        $context = [
            'ticketId' => $ticket->id,
            'tenantId' => $tenantId ?? $ticket->tenant_id,
        ];

        try {
            // Use the email template system to render content and subject
            $content = render_email_template('ticket-created', $context);
            $subject = get_email_subject('ticket-created', $context);
            if (is_smtp_valid()) {
                // Send email to all admin users
                Email::to($adminUsers)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }

        } catch (\Exception $e) {
            app_log('Error sending admin ticket created notification', 'error', $e, [
                'ticket_id' => $ticket->id,
                'tenant_id' => $tenantId ?? $ticket->tenant_id,
                'admin_emails' => $adminUsers,
            ]);

            return false;
        }
    }

    /**
     * Send notification to all department assignees
     */
    protected function sendDepartmentAssigneesNotification($ticket, $tenantId = null)
    {
        if (! $ticket->department_id) {
            return;
        }

        // Get only the specifically selected assignees for the department
        $assignees = \App\Helpers\TicketHelper::getAllAssignedUsersForDepartment($ticket->department_id);

        if (empty($assignees)) {
            return;
        }

        // Extract email addresses from assignees
        $assigneeEmails = array_map(function ($user) {
            return $user['email'];
        }, $assignees);

        // Remove duplicates and empty values
        $assigneeEmails = array_unique(array_filter($assigneeEmails));

        if (empty($assigneeEmails)) {
            return;
        }

        // Prepare context with all necessary data for merge fields
        $context = [
            'ticketId' => $ticket->id,
            'tenantId' => $tenantId ?? $ticket->tenant_id,
        ];

        try {
            // Use the email template system to render content and subject
            $content = render_email_template('ticket-created', $context);
            $subject = get_email_subject('ticket-created', $context);
            if (is_smtp_valid()) {
                // Send email only to the selected department assignees
                Email::to($assigneeEmails)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }

        } catch (\Exception $e) {
            app_log('Error sending assignee ticket created notification', 'error', $e, [
                'ticket_id' => $ticket->id,
                'tenant_id' => $tenantId ?? $ticket->tenant_id,
                'assignee_emails' => $assigneeEmails,
            ]);

            return false;
        }
    }
}
