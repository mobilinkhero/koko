<?php

namespace Modules\Tickets\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Tickets\Events\TicketStatusChanged;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;

class TicketsController extends Controller
{
    /**
     * Display the client tickets dashboard
     */
    public function index()
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($currentUser->id);

        // Get ticket statistics for the current user

        $stats = [
            'total' => Ticket::where('tenant_id', $user->tenant_id)->count(),
            'open' => Ticket::where('tenant_id', $user->tenant_id)->open()->count(),
            'answered' => Ticket::where('tenant_id', $user->tenant_id)->answered()->count(),
            'closed' => Ticket::where('tenant_id', $user->tenant_id)->closed()->count(),
            'high_priority' => Ticket::where('tenant_id', $user->tenant_id)->highPriority()->count(),
        ];
        // Get recent tickets
        $recentTickets = $user->tickets()
            ->with(['department', 'replies' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->latest()
            ->limit(3)
            ->get();

        return view('Tickets::client.tickets.index', compact('stats', 'recentTickets'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create(string $subdomain)
    {
        $departments = Department::active()->get();

        return view('Tickets::client.tickets.create', compact('departments'));
    }

    /**
     * Display the specified ticket
     */
    public function show(string $subdomain, Ticket $ticket)
    {

        // Ensure the ticket belongs to the authenticated user
        if ($ticket->tenant_id !== tenant_id()) {
            abort(403, 'Unauthorized access to ticket.');
        }

        $ticket->load(['department', 'replies.user']);

        return view('Tickets::client.tickets.show', compact('ticket'));
    }

    /**
     * Download attachment file
     */
    public function downloadAttachment(string $subdomain, Ticket $ticket)
    {
        $filename = request()->query('filename');

        // Find the attachment in ticket or replies
        $attachment = null;

        // Check ticket attachments
        if ($ticket->attachments) {
            foreach ($ticket->attachments as $att) {
                // Handle both new format (array with filename) and legacy format (string or array with name)
                $attachmentName = null;
                if (is_array($att)) {
                    $attachmentName = $att['filename'] ?? $att['name'] ?? null;
                } else {
                    $attachmentName = $att;
                }

                if ($attachmentName === $filename) {
                    $attachment = $att;
                    break;
                }
            }
        }

        // Check reply attachments if not found in ticket
        if (! $attachment) {
            foreach ($ticket->replies as $reply) {
                if ($reply->attachments) {
                    foreach ($reply->attachments as $att) {
                        // Handle both new format (array with filename) and legacy format (string or array with name)
                        $attachmentName = null;
                        if (is_array($att)) {
                            $attachmentName = $att['filename'] ?? $att['name'] ?? null;
                        } else {
                            $attachmentName = $att;
                        }

                        if ($attachmentName === $filename) {
                            $attachment = $att;
                            break 2;
                        }
                    }
                }
            }
        }

        if (! $attachment) {
            abort(404, 'Attachment not found.');
        }

        // Handle different attachment formats for download
        // For new format (with url), try direct download
        if (is_array($attachment) && isset($attachment['url'])) {
            $filePath = str_replace('/storage/', '', $attachment['url']);
            if (Storage::disk('public')->exists($filePath)) {
                $absolutePath = Storage::disk('public')->path($filePath);

                return response()->download($absolutePath, $attachment['filename']);
            }
        }

        // For legacy format (with path)
        if (is_array($attachment) && isset($attachment['path'])) {
            if (Storage::disk('public')->exists($attachment['path'])) {
                $absolutePath = Storage::disk('public')->path($attachment['path']);

                return response()->download($absolutePath, $attachment['name'] ?? $filename);
            }
        }

        // For simple string format, assume it's in tickets folder
        if (is_string($attachment)) {
            $ticketsPath = 'tickets/'.$attachment;
            if (Storage::disk('public')->exists($ticketsPath)) {
                $absolutePath = Storage::disk('public')->path($ticketsPath);

                return response()->download($absolutePath, $attachment);
            }
        }

        abort(404, 'Attachment file not found on server.');
    }

    public function closeTicket(string $subdomain, Ticket $ticket)
    {
        $oldStatus = $ticket->status;
        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        // Send status change notification
        event(new TicketStatusChanged($ticket->fresh(), $oldStatus, true));

        $this->logStatusChange($ticket, $oldStatus, 'closed');

        return redirect()->back()->with(session()->flash('notification', [
            'type' => 'success',
            'message' => 'Ticket closed successfully.',
        ]));
    }

    /**
     * Reopen a ticket
     */
    public function reopenTicket(string $subdomain, Ticket $ticket)
    {
        $oldStatus = $ticket->status;
        $ticket->update([
            'status' => 'open',
            'closed_at' => null,
        ]);

        // Send status change notification
        event(new TicketStatusChanged($ticket->fresh(), $oldStatus, true));

        $this->logStatusChange($ticket, $oldStatus, 'open');

        return redirect()->back()->with(session()->flash('notification', [
            'type' => 'success',
            'message' => 'Ticket re-opened successfully.',
        ]));
    }

    /**
     * Log status change for audit trail
     */
    private function logStatusChange(Ticket $ticket, string $oldStatus, string $newStatus): void
    {
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'user_type' => 'system',
            'content' => 'Status changed from '.t($oldStatus).' to '.t($newStatus).'.',
        ]);
    }
}
