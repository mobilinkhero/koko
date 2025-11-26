<?php

declare(strict_types=1);

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Tickets\Events\TicketStatusChanged;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;

class TicketsController extends Controller
{
    /**
     * Display the admin tickets dashboard
     */
    public function index(): View
    {
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::query()->open()->count(),
            'answered' => Ticket::query()->answered()->count(),
            'closed' => Ticket::query()->closed()->count(),
            'high_priority' => Ticket::query()->highPriority()->count(),
        ];

        // Fix: Use lowercase module name for view reference
        return view('Tickets::admin.tickets.index', compact('stats'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create(): View
    {
        // Get all active tenants (clients)
        $tenants = Tenant::where('status', 'active')
            ->select('id', 'company_name', 'subdomain')
            ->orderBy('company_name')
            ->get();

        $departments = Department::active()->get();

        return view('Tickets::admin.tickets.create', compact('tenants', 'departments'));
    }

    /**
     * Show the form for editing an existing ticket
     */
    public function updateAssignees(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'assignee_id' => 'array',
            'assignee_id.*' => 'integer|exists:users,id',
        ]);

        $requestAssignees = $validated['assignee_id'] ?? []; // from request
        $existingAssignees = $ticket->assignee_id;

        // Convert to integers to normalize types
        $requestAssignees = array_map('intval', $requestAssignees);
        $existingAssignees = array_map('intval', $existingAssignees);

        // Get new ones only
        $newAssignees = array_diff($requestAssignees, $existingAssignees);

        if (! empty($newAssignees)) {
            $assignedUsers = User::withoutGlobalScopes()->whereIn('id', $newAssignees)
                ->where('user_type', 'admin')
                ->where('active', true)
                ->get();

            foreach ($assignedUsers as $user) {
                if (! $user->email) {
                    continue;
                }
                $context = [
                    'ticketId' => $ticket->id,
                    'tenantId' => $ticket->tenant_id,
                ];

                $this->send_ticket_assigned_mail($user, $context);
            }
        }

        $ticket->update([
            'assignee_id' => json_encode($validated['assignee_id'] ?? []),
        ]);
        session()->flash('notification', [
            'type' => 'success',
            'message' => t('assigned_updated'),
        ]);

        return redirect()->back()->with('success', 'Assignees updated successfully.');
    }

    public function send_ticket_assigned_mail($user, $context)
    {
        try {
            $content = render_email_template('ticket-assigned', $context);
            $subject = get_email_subject('ticket-assigned', $context);
            if (is_smtp_valid()) {
                // Send email to all admin users
                Email::to($user->email)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Display the specified ticket with replies
     */
    public function show(Ticket $ticket): View
    {
        $autoAssignedUsers = [];

        $assignee_id = $ticket->assignee_id;

        $autoAssignedUsers = User::whereIn('id', $assignee_id)
            ->where('user_type', 'admin')
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->firstname.' '.$user->lastname,
                    'email' => $user->email,
                ];
            })
            ->toArray();

        // Get assigned user IDs for exclusion
        $assignedUserIds = collect($autoAssignedUsers)->pluck('user_id')->toArray();

        // Get admin users excluding those already assigned
        $adminUser = User::where('user_type', 'admin')
            ->where('is_admin', false)
            ->whereNotIn('id', $assignedUserIds) // Exclude already assigned users
            ->select('id', 'firstname', 'lastname', 'email')
            ->orderBy('firstname')
            ->get();

        // Load all necessary relationships
        $ticket->load([
            'tenant',          // Load the tenant
            'tenantStaff',     // Load the staff member who created the ticket
            'department',
            'replies.user',
        ]);

        // Mark as viewed by admin
        if (! $ticket->admin_viewed) {
            $ticket->update(['admin_viewed' => true]);
        }
        $departments = Department::active()->get();

        return view('Tickets::admin.tickets.show', compact('ticket', 'departments', 'autoAssignedUsers', 'adminUser'));
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request)
    {
        // The actual store logic is handled by the Livewire TicketForm component
        // This method is here for route completeness but may redirect to create
        return redirect()->route('admin.tickets.create')
            ->with('info', 'Please use the form below to create a ticket.');
    }

    /**
     * Update the specified ticket
     */
    public function update(Request $request, Ticket $ticket)
    {
        // The actual update logic is handled by the Livewire TicketForm component
        // This method is here for route completeness but may redirect to edit
        return redirect()->route('admin.tickets.edit', $ticket)
            ->with('info', 'Please use the form below to update the ticket.');
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(Ticket $ticket)
    {
        // Soft delete the ticket
        $ticket->delete();

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, Ticket $ticket): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:open,answered,closed,on_hold',
        ]);

        $oldStatus = $ticket->status;
        $status = $request->input('status');

        if ($status === 'open') {
            $ticket->open();
        } elseif ($status === 'closed') {
            $ticket->close();
        } else {
            $ticket->update(['status' => $status]);
        }

        // Send status change notification
        event(new TicketStatusChanged($ticket->fresh(), $oldStatus, true));

        $newStatus = (string) $request->status;
        $this->logStatusChange($ticket, $oldStatus, $newStatus);

        return response()->json([
            'success' => true,
            'message' => 'Ticket status updated successfully',
        ]);
    }

    /**
     * Update ticket priority
     */
    public function updatePriority(Request $request, Ticket $ticket): JsonResponse
    {
        $request->validate([
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket->update(['priority' => $request->priority]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket priority updated successfully',
        ]);
    }

    /**
     * Assign ticket to department
     */
    public function assignDepartment(Request $request, Ticket $ticket): JsonResponse
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
        ]);

        $ticket->update(['department_id' => $request->department_id]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket assigned to department successfully',
        ]);
    }

    /**
     * Bulk update tickets
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'tickets' => 'required|array',
            'tickets.*' => 'exists:tickets,id',
            'action' => 'required|in:status,priority,department,delete',
            'value' => 'required_unless:action,delete',
        ]);

        $tickets = Ticket::whereIn('id', $request->tickets);

        switch ($request->action) {
            case 'status':
                $tickets->update(['status' => $request->value]);
                break;
            case 'priority':
                $tickets->update(['priority' => $request->value]);
                break;
            case 'department':
                $tickets->update(['department_id' => $request->value]);
                break;
            case 'delete':
                $tickets->delete();
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk operation completed successfully',
        ]);
    }

    /**
     * Download ticket attachment
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|void
     */
    public function downloadAttachment(Ticket $ticket)
    {
        $file = request()->query('file');
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

                if ($attachmentName === $file) {
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

                        if ($attachmentName === $file) {
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
                return Storage::download('public/'.$filePath, $attachment['filename']);
            }
        }

        // For legacy format (with path)
        if (is_array($attachment) && isset($attachment['path'])) {
            if (Storage::disk('public')->exists($attachment['path'])) {
                $fullPath = Storage::disk('public')->path($attachment['path']);

                return response()->download($fullPath, $attachment['name'] ?? $file);
            }
        }

        // For simple string format, assume it's in tickets folder
        if (is_string($attachment)) {
            $ticketsPath = 'tickets/'.$attachment;
            if (Storage::disk('public')->exists($ticketsPath)) {
                $fullPath = Storage::disk('public')->path($ticketsPath);

                return response()->download($fullPath, $attachment);
            }
        }

        abort(404, 'Attachment file not found on server.');
    }

    /**
     * Get ticket statistics for dashboard
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'today' => [
                'created' => Ticket::whereDate('created_at', today())->count(),
                'closed' => Ticket::whereDate('updated_at', today())->where('status', 'closed')->count(),
            ],
            'this_week' => [
                'created' => Ticket::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'closed' => Ticket::whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])->where('status', 'closed')->count(),
            ],
            'this_month' => [
                'created' => Ticket::whereMonth('created_at', now()->month)->count(),
                'closed' => Ticket::whereMonth('updated_at', now()->month)->where('status', 'closed')->count(),
            ],
            'by_status' => Ticket::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status'),
            'by_priority' => Ticket::selectRaw('priority, COUNT(*) as count')->groupBy('priority')->pluck('count', 'priority'),
            'by_department' => Ticket::with('department')->get()->groupBy('department.name')->map->count(),
        ];

        return response()->json($stats);
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
            'content' => 'Status changed from '.t($oldStatus).' to '.t($newStatus).'',
        ]);
    }

    /**
     * Close a ticket
     */
    public function closeTicket(Ticket $ticket)
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
    public function reopenTicket(Ticket $ticket)
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
}
