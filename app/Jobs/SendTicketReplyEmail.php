<?php

namespace App\Jobs;

use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTicketReplyEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reply;

    public $ticket;

    public function __construct($reply)
    {
        $this->reply = $reply->load(['ticket', 'ticket.tenantStaff']);
        $this->ticket = $this->reply->ticket;
    }

    public function handle(): void
    {
        try {
            // Send notification based on who replied
            if ($this->reply->user_type === 'admin') {
                // Admin replied, notify tenant and/or tenant staff
                $this->sendTenantNotification();
            } else {
                // Client or tenant staff replied, notify admin
                $this->sendAdminNotification();
            }
        } catch (\Exception $e) {
            app_log('Error sending ticket reply notification', 'error', $e, [
                'reply_id' => $this->reply->id,
                'ticket_id' => $this->ticket->id,
            ]);
        }
    }

    /**
     * Send notification to tenant and/or tenant staff
     */
    private function sendTenantNotification(): void
    {
        $emailsSent = [];

        $context = [
            'ticketId' => $this->ticket->id,
            'replyId' => $this->reply->id,
            'tenantId' => $this->ticket->tenant_id,
        ];

        // Check if ticket was created by tenant staff
        if ($this->ticket->tenant_staff_id) {
            // Send notification to the specific tenant staff member
            $tenantStaff = $this->ticket->tenantStaff; // Assuming relationship is loaded

            if ($tenantStaff && $tenantStaff->email) {
                $this->sendEmailToTenantStaff($context, $tenantStaff);
                $emailsSent[] = $tenantStaff->email;
            } else {
                app_log('No tenant staff found or email missing for ticket notification', 'warning', null, [
                    'tenant_staff_id' => $this->ticket->tenant_staff_id,
                    'ticket_id' => $this->ticket->id,
                ]);
            }
        } else {
            // Ticket was created by tenant, send to tenant
            $tenantUser = getUserByTenantId($this->ticket->tenant_id);

            if ($tenantUser && $tenantUser->email) {
                $this->sendEmailToTenant($context, $tenantUser);
                $emailsSent[] = $tenantUser->email;
            } else {
                app_log('No tenant user found or email missing for ticket notification', 'warning', null, [
                    'tenant_id' => $this->ticket->tenant_id,
                    'ticket_id' => $this->ticket->id,
                ]);
            }
        }
    }

    /**
     * Send email notification to tenant
     */
    private function sendEmailToTenant($context, $tenantUser)
    {
        try {
            $content = render_email_template('ticket-reply-tenant', $context);
            $subject = get_email_subject('ticket-reply-tenant', $context);

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
     * Send email notification to tenant staff
     */
    private function sendEmailToTenantStaff($context, $tenantStaff)
    {
        try {
            $context['staffName'] = $tenantStaff->firstname;
            $content = render_email_template('ticket-reply-tenant', $context);
            $subject = get_email_subject('ticket-reply-tenant', $context);
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
    private function sendAdminNotification()
    {
        $emailsSent = [];

        // First, send to admins (primary admin group)
        $adminUsers = \App\Helpers\TicketHelper::getAdminUsersForNotification();

        try {
            if (! empty($adminUsers) && is_smtp_valid()) {
                // Use the email template functions for consistency
                $content = render_email_template('ticket-reply-admin', ['tenantId' => $this->ticket->tenant_id, 'ticketId' => $this->ticket->id, 'replyId' => $this->reply->id]);
                $subject = get_email_subject('ticket-reply-admin', ['tenantId' => $this->ticket->tenant_id, 'ticketId' => $this->ticket->id, 'replyId' => $this->reply->id]);

                Email::to($adminUsers)
                    ->subject($subject)
                    ->content($content)
                    ->send();

                $emailsSent = array_merge($emailsSent, $adminUsers);
            }

            // Then, send to department assignees if the ticket is assigned to a department
            if ($this->ticket->department_id) {
                $this->sendDepartmentAssigneesNotification($emailsSent);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send notification to all department assignees
     */
    private function sendDepartmentAssigneesNotification(array $alreadyNotifiedEmails = [])
    {
        // Get only the specifically selected assignees for the department
        $assignees = \App\Helpers\TicketHelper::getAllAssignedUsersForDepartment($this->ticket->department_id);

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
            // Use the email template functions for consistency
            $content = render_email_template('ticket-reply-admin', [
                'tenantId' => $this->ticket->tenant_id,
                'ticketId' => $this->ticket->id,
                'replyId' => $this->reply->id,
            ]);

            $subject = get_email_subject('ticket-reply-admin', [
                'tenantId' => $this->ticket->tenant_id,
                'ticketId' => $this->ticket->id,
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
