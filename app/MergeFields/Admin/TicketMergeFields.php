<?php

namespace App\MergeFields\Admin;

use Modules\Tickets\Models\Ticket;

class TicketMergeFields
{
    public function name(): string
    {
        return 'ticket-group';
    }

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
            'ticket-escalated',
        ];
    }

    public function build(): array
    {
        return [
            // Basic Ticket Information
            ['name' => 'Ticket ID',            'key' => '{ticket_id}'],
            ['name' => 'Ticket Subject',       'key' => '{ticket_subject}'],
            ['name' => 'Ticket Body/Content',  'key' => '{ticket_body}'],
            ['name' => 'Ticket Priority',      'key' => '{ticket_priority}'],
            ['name' => 'Ticket Status',        'key' => '{ticket_status}'],
            ['name' => 'Ticket Department',    'key' => '{ticket_department}'],
            ['name' => 'Ticket Created At',    'key' => '{ticket_created_at}'],
            ['name' => 'Ticket Updated At',    'key' => '{ticket_updated_at}'],

            // Assignment Information
            ['name' => 'Assigned User Name',   'key' => '{assigned_user_name}'],
            ['name' => 'Assigned User Email',  'key' => '{assigned_user_email}'],

            // Reply Information (for reply notifications)
            ['name' => 'Reply Author',         'key' => '{reply_author}'],
            ['name' => 'Reply Content',        'key' => '{reply_content}'],
            ['name' => 'Reply User Type',      'key' => '{reply_user_type}'],

            // URLs
            ['name' => 'Ticket URL',           'key' => '{ticket_url}'],
            ['name' => 'Admin Ticket URL',     'key' => '{ticket_admin_url}'],
            ['name' => 'Admin URL',            'key' => '{admin_url}'],

            // Status Change Information
            ['name' => 'Previous Status',      'key' => '{previous_status}'],
            ['name' => 'New Status',           'key' => '{new_status}'],
        ];
    }

    public function format(array $context): array
    {
        $data = [];

        // Handle ticket information
        if (! empty($context['ticketId'])) {
            $ticket = Ticket::with(['department', 'assignedUser', 'tenant'])
                ->findOrFail($context['ticketId']);

            $data = array_merge($data, [
                '{ticket_id}' => $ticket->ticket_id ?? $ticket->id,
                '{ticket_subject}' => $ticket->subject ?? '',
                '{ticket_body}' => $ticket->body ?? '',
                '{ticket_priority}' => ucfirst($ticket->priority ?? 'normal'),
                '{ticket_status}' => ucfirst($ticket->status ?? 'open'),
                '{ticket_department}' => $ticket->department?->name ?? 'General',
                '{ticket_created_at}' => $ticket->created_at?->format('M d, Y \a\t g:i A') ?? '',
                '{ticket_updated_at}' => $ticket->updated_at?->format('M d, Y \a\t g:i A') ?? '',

                // Assignment information
                '{assigned_user_name}' => $ticket->assignedUsers->map(fn ($user) => $user->firstname.' '.$user->lastname)->implode(', ') ?: 'Unassigned',
                '{assigned_user_email}' => $ticket->assignedUsers->pluck('email')->implode(', ') ?: '',

                // URLs
                '{ticket_url}' => $this->getTicketUrl($ticket),
                '{ticket_admin_url}' => $this->getTicketAdminUrl($ticket),
                '{admin_url}' => $this->getTicketAdminUrl($ticket),
            ]);
        }

        // Handle status change information
        $data['{previous_status}'] = '';
        $data['{new_status}'] = '';
        if (! empty($context['oldStatus'])) {
            $data['{previous_status}'] = ucfirst($context['oldStatus']);
        }
        if (! empty($context['newStatus'])) {
            $data['{new_status}'] = ucfirst($context['newStatus']);
        }

        return array_filter($data, fn ($value) => $value !== null && $value !== '');
    }

    private function getTicketUrl($ticket): string
    {
        try {
            $subdomain = $ticket->tenant->subdomain;

            return route('tenant.tickets.show', ['subdomain' => $subdomain, 'ticket' => $ticket->id]);
        } catch (\Exception $e) {
            return url('/admin/tickets/'.$ticket->id);
        }
    }

    private function getTicketAdminUrl($ticket): string
    {
        try {
            return route('admin.tickets.show', ['ticket' => $ticket->id]);
        } catch (\Exception $e) {
            return url('/admin/tickets/'.$ticket->id);
        }
    }
}
