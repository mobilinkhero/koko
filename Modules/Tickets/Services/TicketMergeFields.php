<?php

namespace Modules\Tickets\Services;

use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;

class TicketMergeFields
{
    /**
     * Get the name of this merge field group
     */
    public function name(): string
    {
        return 'ticket-group';
    }

    /**
     * Get the templates this merge field group applies to
     */
    public function templates(): array
    {
        return [
            'ticket-created',
            'ticket-reply-tenant',
            'ticket-reply-admin',
            'ticket-status-changed',
            'ticket-assigned',
            'ticket-closed',
            'ticket-reopened',
        ];
    }

    /**
     * Build the merge fields array with admin-focused fields
     */
    public function build(): array
    {
        return [
            // Basic Ticket Information
            ['name' => 'Ticket ID',              'key' => '{{ticket_id}}'],
            ['name' => 'Ticket Number',          'key' => '{{ticket_number}}'],
            ['name' => 'Ticket Subject',         'key' => '{{ticket_subject}}'],
            ['name' => 'Ticket Body',            'key' => '{{ticket_body}}'],
            ['name' => 'Ticket Priority',        'key' => '{{ticket_priority}}'],
            ['name' => 'Ticket Status',          'key' => '{{ticket_status}}'],
            ['name' => 'Ticket Department',      'key' => '{{ticket_department}}'],

            // Ticket Dates
            ['name' => 'Created Date',           'key' => '{{ticket_created_at}}'],
            ['name' => 'Last Updated',           'key' => '{{ticket_updated_at}}'],
            ['name' => 'Closed Date',            'key' => '{{ticket_closed_at}}'],
            ['name' => 'Last Reply Date',        'key' => '{{ticket_last_reply_at}}'],

            // Ticket Metrics
            ['name' => 'Total Replies',          'key' => '{{total_replies}}'],
            ['name' => 'Days Open',              'key' => '{{days_open}}'],
            ['name' => 'Resolution Time',        'key' => '{{resolution_time}}'],

            // Tenant Information
            ['name' => 'Tenant Name',            'key' => '{{tenant_name}}'],
            ['name' => 'Tenant Email',           'key' => '{{tenant_email}}'],
            ['name' => 'Tenant Company',         'key' => '{{tenant_company}}'],
            ['name' => 'Tenant Staff Name',      'key' => '{{tenant_staff_name}}'],
            ['name' => 'Tenant Staff Email',     'key' => '{{tenant_staff_email}}'],

            // Assignment Information
            ['name' => 'Assigned Agent Name',    'key' => '{{assigned_user_name}}'],
            ['name' => 'Assigned Agent Email',   'key' => '{{assigned_user_email}}'],

            // URLs
            ['name' => 'Admin URL',              'key' => '{{admin_url}}'],
            ['name' => 'Client URL',             'key' => '{{client_url}}'],

            // Status Change Information
            [
                'name' => 'Previous Status',
                'key' => '{{old_status}}',
                'absent' => ['ticket-created', 'ticket-reply-tenant', 'ticket-reply-admin'],
            ],
            [
                'name' => 'New Status',
                'key' => '{{new_status}}',
                'absent' => ['ticket-created', 'ticket-reply-tenant', 'ticket-reply-admin'],
            ],
            [
                'name' => 'Status Changed By',
                'key' => '{{status_changed_by}}',
                'absent' => ['ticket-created', 'ticket-reply-tenant', 'ticket-reply-admin'],
            ],

            // Reply Information
            [
                'name' => 'Reply Content',
                'key' => '{{reply_content}}',
                'absent' => ['ticket-created'],
            ],
            [
                'name' => 'Reply Author',
                'key' => '{{reply_author}}',
                'absent' => ['ticket-created'],
            ],
            [
                'name' => 'Reply Created At',
                'key' => '{{reply_created_at}}',
                'absent' => ['ticket-created'],
            ],
        ];
    }

    /**
     * Format the merge fields with actual data
     */
    public function format(array $context): array
    {
        $replacements = [];

        $ticket = isset($context['ticketId'])
            ? Ticket::with(['tenant', 'department', 'assignedUser', 'tenantStaff', 'replies'])
                ->findOrFail($context['ticketId'])
            : null;

        if ($ticket) {
            // Basic ticket information
            $replacements['{{ticket_id}}'] = $ticket->ticket_id ?? '';
            $replacements['{{ticket_number}}'] = $ticket->ticket_number ?? '';
            $replacements['{{ticket_subject}}'] = $ticket->subject ?? '';
            $replacements['{{ticket_body}}'] = $ticket->body ?? '';
            $replacements['{{ticket_priority}}'] = ucfirst($ticket->priority ?? 'medium');
            $replacements['{{ticket_status}}'] = ucfirst(str_replace('_', ' ', $ticket->status ?? 'open'));
            $replacements['{{ticket_department}}'] = $ticket->department->name ?? 'Unassigned';

            // Dates
            $replacements['{{ticket_created_at}}'] = $ticket->created_at ? $ticket->created_at->format('M d, Y H:i') : '';
            $replacements['{{ticket_updated_at}}'] = $ticket->updated_at ? $ticket->updated_at->format('M d, Y H:i') : '';
            $replacements['{{ticket_closed_at}}'] = $ticket->closed_at ? $ticket->closed_at->format('M d, Y H:i') : '';
            $replacements['{{ticket_last_reply_at}}'] = $ticket->last_reply_at ? $ticket->last_reply_at->format('M d, Y H:i') : '';

            // Metrics
            $replacements['{{total_replies}}'] = $ticket->replies->count() ?? 0;
            $replacements['{{days_open}}'] = $this->calculateDaysOpen($ticket);
            $replacements['{{resolution_time}}'] = $this->calculateResolutionTime($ticket);

            // URLs
            $replacements['{{admin_url}}'] = route('admin.tickets.show', $ticket->id);
            $replacements['{{client_url}}'] = tenant_route('tenant.tickets.show', $ticket->id);

            // Tenant information
            if ($ticket->tenant) {
                $replacements['{{tenant_name}}'] = $ticket->tenant->name ?? '';
                $replacements['{{tenant_email}}'] = $ticket->tenant->email ?? '';
                $replacements['{{tenant_company}}'] = $ticket->tenant->company_name ?? '';
            }

            // Tenant staff information
            if ($ticket->tenantStaff) {
                $replacements['{{tenant_staff_name}}'] = $ticket->tenantStaff->name ?? '';
                $replacements['{{tenant_staff_email}}'] = $ticket->tenantStaff->email ?? '';
            }

            // Assignment information
            if ($ticket->assignedUser) {
                $replacements['{{assigned_user_name}}'] = $ticket->assignedUser->name ?? '';
                $replacements['{{assigned_user_email}}'] = $ticket->assignedUser->email ?? '';
            }
        }

        // Reply information if provided
        if (isset($context['replyId'])) {
            $reply = TicketReply::with('user')->find($context['replyId']);
            if ($reply) {
                $replacements['{{reply_content}}'] = $reply->content ?? '';
                $replacements['{{reply_author}}'] = $reply->user ? $reply->user->name : ($reply->user_type === 'admin' ? 'Support Staff' : 'Customer');
                $replacements['{{reply_created_at}}'] = $reply->created_at ? $reply->created_at->format('M d, Y H:i') : '';
            }
        }

        // Status change information
        if (isset($context['oldStatus'])) {
            $replacements['{{old_status}}'] = ucfirst($context['oldStatus']);
        }
        if (isset($context['newStatus'])) {
            $replacements['{{new_status}}'] = ucfirst($context['newStatus']);
        }
        if (isset($context['statusChangedBy'])) {
            $replacements['{{status_changed_by}}'] = $context['statusChangedBy'];
        }

        return array_filter($replacements, function ($value) {
            return $value !== '' && $value !== null;
        });
    }

    /**
     * Calculate the number of days a ticket has been open
     */
    protected function calculateDaysOpen(Ticket $ticket): string
    {
        $start = $ticket->created_at;
        $end = $ticket->closed_at ?? now();

        $days = $start->diffInDays($end);

        return $days.' '.($days == 1 ? 'day' : 'days');
    }

    /**
     * Calculate the resolution time for a ticket
     */
    protected function calculateResolutionTime(Ticket $ticket): string
    {
        if (! $ticket->closed_at) {
            return 'Not resolved';
        }

        $duration = $ticket->created_at->diff($ticket->closed_at);

        if ($duration->days > 0) {
            return $duration->days.' '.($duration->days == 1 ? 'day' : 'days');
        } elseif ($duration->h > 0) {
            return $duration->h.' '.($duration->h == 1 ? 'hour' : 'hours');
        } else {
            return $duration->i.' '.($duration->i == 1 ? 'minute' : 'minutes');
        }
    }
}
