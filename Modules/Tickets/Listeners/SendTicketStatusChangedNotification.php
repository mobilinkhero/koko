<?php

namespace Modules\Tickets\Listeners;

use Corbital\LaravelEmails\Facades\Email;
use Modules\Tickets\Events\TicketStatusChanged;

class SendTicketStatusChangedNotification
{
    public function handle(TicketStatusChanged $event): void
    {
        $ticket = $event->ticket;
        $oldStatus = $event->oldStatus;
        $newStatus = $ticket->status;

        // Check if notifications should be sent
        if (! $event->sendNotification) {

            return;
        }

        // Skip if status hasn't actually changed
        if ($oldStatus === $newStatus) {
            return;
        }

        try {
            // Send notification to tenant/client
            $this->sendTenantNotification($ticket, $oldStatus, $newStatus);

            // Only send admin notification if ticket is being closed by tenant
            if ($newStatus === 'closed' || $newStatus === 'open') {
                $this->sendAdminNotification($ticket, $oldStatus, $newStatus);
            }
        } catch (\Exception $e) {
            app_log('Error sending ticket status change notification', 'error', $e, [
                'ticket_id' => $ticket->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }
    }

    /**
     * Send status change notification to tenant
     */
    private function sendTenantNotification($ticket, $oldStatus, $newStatus): void
    {
        $emailsSent = [];

        // Check if ticket was created by tenant staff
        if ($ticket->tenant_staff_id && $ticket->tenantStaff) {
            // Send to specific tenant staff member
            $tenantStaff = $ticket->tenantStaff;
            if ($tenantStaff->email) {
                $this->sendEmailToTenantStaff($ticket, $oldStatus, $newStatus, $tenantStaff);
                $emailsSent[] = $tenantStaff->email;
            }
        } else {
            // Send to tenant (main contact)
            $tenantUser = getUserByTenantId($ticket->tenant_id);
            if ($tenantUser && $tenantUser->email) {
                $this->sendEmailToTenant($ticket, $oldStatus, $newStatus, $tenantUser);
                $emailsSent[] = $tenantUser->email;
            }
        }
    }

    /**
     * Send email to tenant
     */
    private function sendEmailToTenant($ticket, $oldStatus, $newStatus, $tenantUser)
    {
        try {
            // Use your existing email template system
            $content = render_email_template('ticket-status-changed', [
                'tenantId' => $ticket->tenant_id,
                'ticketId' => $ticket->id,
                'oldStatus' => ucfirst($oldStatus),
                'newStatus' => ucfirst($newStatus),
            ]);

            $subject = get_email_subject('ticket-status-changed', [
                'tenantId' => $ticket->tenant_id,
                'ticketId' => $ticket->id,
                'oldStatus' => ucfirst($oldStatus),
                'newStatus' => ucfirst($newStatus),
            ]);
            if (is_smtp_valid()) {
                Email::to($tenantUser->email)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send email to tenant staff
     */
    private function sendEmailToTenantStaff($ticket, $oldStatus, $newStatus, $tenantStaff)
    {
        try {
            $content = render_email_template('ticket-status-changed', [
                'tenantId' => $ticket->tenant_id,
                'ticketId' => $ticket->id,
                'oldStatus' => ucfirst($oldStatus),
                'newStatus' => ucfirst($newStatus),
            ]);

            $subject = get_email_subject('ticket-status-changed', [
                'tenantId' => $ticket->tenant_id,
                'ticketId' => $ticket->id,
                'oldStatus' => ucfirst($oldStatus),
                'newStatus' => ucfirst($newStatus),
            ]);

            if (is_smtp_valid()) {
                Email::to($tenantStaff->email)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send notification to admin
     */
    private function sendAdminNotification($ticket, $oldStatus, $newStatus)
    {
        $emailsSent = [];

        // First, send to all admin users
        $adminUsers = \App\Helpers\TicketHelper::getAdminUsersForNotification();

        try {
            if (! empty($adminUsers)) {
                $content = render_email_template('ticket-status-changed-admin', [
                    'tenantId' => $ticket->tenant_id,
                    'ticketId' => $ticket->id,
                    'oldStatus' => ucfirst($oldStatus),
                    'newStatus' => ucfirst($newStatus),
                ]);

                $subject = get_email_subject('ticket-status-changed-admin', [
                    'tenantId' => $ticket->tenant_id,
                    'ticketId' => $ticket->id,
                    'oldStatus' => ucfirst($oldStatus),
                    'newStatus' => ucfirst($newStatus),
                ]);

                if (is_smtp_valid()) {
                    Email::to($adminUsers)
                        ->subject($subject)
                        ->content($content)
                        ->send();

                    $emailsSent = array_merge($emailsSent, $adminUsers);
                }
            }

            // Then, notify all department assignees
            if ($ticket->department_id) {
                $this->sendDepartmentAssigneesNotification($ticket, $oldStatus, $newStatus, $emailsSent);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send notification to all department assignees
     */
    private function sendDepartmentAssigneesNotification($ticket, $oldStatus, $newStatus, array $alreadyNotifiedEmails = [])
    {
        // Get only the specifically selected assignees for the department
        $assignees = \App\Helpers\TicketHelper::getAllAssignedUsersForDepartment($ticket->department_id);

        if (empty($assignees)) {
            return;
        }

        // Create a list of emails to notify
        $assigneeEmails = [];
        foreach ($assignees as $assignee) {
            if (empty($assignee['email']) || in_array($assignee['email'], $alreadyNotifiedEmails)) {
                continue;
            }
            $assigneeEmails[] = $assignee['email'];
        }

        if (empty($assigneeEmails)) {
            return;
        }

        try {
            $content = render_email_template('ticket-status-changed-admin', [
                'tenantId' => $ticket->tenant_id,
                'ticketId' => $ticket->id,
                'oldStatus' => ucfirst($oldStatus),
                'newStatus' => ucfirst($newStatus),
            ]);

            $subject = get_email_subject('ticket-status-changed-admin', [
                'tenantId' => $ticket->tenant_id,
                'ticketId' => $ticket->id,
                'oldStatus' => ucfirst($oldStatus),
                'newStatus' => ucfirst($newStatus),
            ]);

            if (is_smtp_valid()) {
                Email::to($assigneeEmails)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }
        } catch (\Exception $e) {
            return false;
        }

    }
}
