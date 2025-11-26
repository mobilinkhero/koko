<?php

namespace Modules\Tickets\Livewire\Admin;

use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Tickets\Events\TicketReplyCreated;
use Modules\Tickets\Events\TicketStatusChanged;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;

class TicketDetails extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public string $replyContent = '';

    public $attachments = [];

    public string $newStatus = '';

    public string $newPriority = '';

    public int $newDepartmentId = 0;

    public bool $send_notification = true;

    protected function rules()
    {
        return [
            'replyContent' => ['required', 'string', 'min:3', 'max:10000', new PurifiedInput(t('sql_injection_error'))],
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
            'newStatus' => 'nullable|in:open,answered,closed,on_hold',
            'newPriority' => 'nullable|in:low,medium,high',
            'newDepartmentId' => 'nullable|exists:departments,id',
            'send_notification' => 'boolean',
        ];
    }

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket->load(['tenant', 'tenantStaff', 'department', 'replies.user']);
        $this->newStatus = $this->ticket->status;
        $this->newPriority = $this->ticket->priority;
        $this->newDepartmentId = $this->ticket->department_id;

        // Mark as viewed by admin
        if (! $this->ticket->admin_viewed) {
            $this->ticket->update(['admin_viewed' => true]);
        }
    }

    public function addReply()
    {

        $this->validate([
            'replyContent' => ['required', 'string', 'min:3', 'max:10000', new PurifiedInput(t('sql_injection_error'))],
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $attachmentData = [];
        // Handle file uploads
        if ($this->attachments) {
            $filesToProcess = array_slice($this->attachments, 0, 5);

            foreach ($filesToProcess as $attachment) {
                if ($attachment && is_object($attachment) && method_exists($attachment, 'getClientOriginalName')) {
                    try {
                        // Create a unique filename
                        $originalName = $attachment->getClientOriginalName();
                        $filename = time().'_'.str_replace(' ', '_', $originalName);

                        // Store in a structured path: tickets/replies/[ticket_id]/[filename]
                        $path = $attachment->storeAs(
                            "tickets/replies/{$this->ticket->id}",
                            $filename,
                            'public'
                        );

                        // Store metadata for each attachment
                        $attachmentData[] = [
                            'filename' => $filename,
                            'path' => $path,
                            'size' => $attachment->getSize(),
                        ];
                    } catch (\Exception $e) {
                        app_log(t('reply_attachment_upload_failed'), 'error', $e, [
                            'error' => $e->getMessage(),
                        ]);

                        $this->addError('attachments', t('failed_to_upload').$originalName);

                        continue;
                    }
                }
            }
        }

        // Create the reply
        $reply = TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'user_type' => 'admin',
            'content' => $this->replyContent,
            'attachments' => ! empty($attachmentData) ? $attachmentData : null,
        ]);

        // Dispatch event for notifications if enabled
        if ($this->send_notification) {
            event(new TicketReplyCreated($reply, true));
        }

        // Update ticket status and viewed flags
        $oldStatus = $this->ticket->status;
        $this->ticket->update([
            'status' => 'answered',
            'tenant_viewed' => false, // Client needs to see the new reply
        ]);

        // Reset form
        $this->replyContent = '';
        $this->attachments = [];

        // Refresh ticket data
        $this->ticket->refresh();
        $this->ticket->load(['replies.user']);

        session()->flash('success', 'reply_added_successfully');
        $this->dispatch('reply-added');
    }

    public function deleteReply(int $replyId)
    {
        $reply = TicketReply::where('ticket_id', $this->ticket->id)
            ->where('id', $replyId)
            ->firstOrFail();

        if (! $reply->canBeDeletedBy(Auth::user())) {
            $this->notify([
                'type' => 'error',
                'message' => t('you_cannot_delete_this_reply').TicketReply::DELETION_WINDOW.' minutes of creation.',
            ]);

            return;
        }

        // Delete attachments from storage
        if ($reply->attachments) {
            foreach ($reply->attachments as $attachment) {
                if (is_array($attachment)) {
                    $path = $attachment['path'] ?? "tickets/replies/{$this->ticket->id}/".$attachment['filename'];
                    Storage::disk('public')->delete($path);
                } else {
                    Storage::disk('public')->delete("tickets/replies/{$this->ticket->id}/".$attachment);
                }
            }
        }

        $reply->delete();

        $this->ticket->refresh();
        $this->ticket->load(['replies.user']);

        $this->notify([
            'type' => 'success',
            'message' => t('reply_deleted_successfully'),
        ]);
        $this->dispatch('reply-deleted');
    }

    public function closeTicket()
    {
        $this->ticket->update(['status' => 'closed']);

        // Log ticket closure
        TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'user_type' => 'system',
            'content' => t('ticket_closed_by_admin'),
        ]);

        $this->newStatus = 'closed';
        $this->ticket->refresh();
        $this->ticket->load(['replies.user']);

        session()->flash('success', t('ticket_closed_successfully'));
        $this->dispatch('ticket-closed');
    }

    public function reopenTicket()
    {
        $this->ticket->update(['status' => 'open']);

        // Log ticket reopening
        TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'user_type' => 'system',
            'content' => 'Ticket reopened by admin',
        ]);

        $this->newStatus = 'open';
        $this->ticket->refresh();
        $this->ticket->load(['replies.user']);

        session()->flash('success', t('ticket_reopened_successfully'));
        $this->dispatch('ticket-reopened');
    }

    /**
     * Add a reply and then close the ticket
     */
    public function addReplyAndClose()
    {
        $this->validate([
            'replyContent' => 'required|min:3|max:10000',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $attachmentData = [];

        // Handle file uploads
        if ($this->attachments) {
            // Validate maximum number of attachments
            if (count($this->attachments) > 5) {
                $this->addError('attachments', t('maximum_5_files_allowed_reply'));

                return;
            }

            foreach ($this->attachments as $attachment) {
                if ($attachment && is_object($attachment) && method_exists($attachment, 'getClientOriginalName')) {
                    try {
                        // Create a unique filename
                        $originalName = $attachment->getClientOriginalName();
                        $filename = time().'_'.str_replace(' ', '_', $originalName);

                        // Store in a structured path: tickets/replies/[ticket_id]/[filename]
                        $path = $attachment->storeAs(
                            "tickets/replies/{$this->ticket->id}",
                            $filename,
                            'public'
                        );

                        // Store metadata for each attachment
                        $attachmentData[] = [
                            'path' => $path,
                            'filename' => $filename,
                            'size' => $attachment->getSize(),

                        ];
                    } catch (\Exception $e) {
                        app_log(t('reply_attachment_upload_failed'), 'error', $e);

                        $this->addError('attachments', t('failed_to_upload ').$originalName);

                        continue;
                    }
                }
            }
        }

        // Create the reply
        $reply = TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'user_type' => 'admin',
            'content' => $this->replyContent,
            'attachments' => ! empty($attachmentPaths) ? $attachmentPaths : null,
        ]);

        // Dispatch event for notifications ONLY if enabled
        if ($this->send_notification) {
            event(new TicketReplyCreated($reply, true));
        }

        // Update ticket status and viewed flags
        $oldStatus = $this->ticket->status;
        $this->ticket->update([
            'status' => 'closed',
            'tenant_viewed' => false, // Client needs to see the new reply
        ]);

        $this->newStatus = 'closed';

        // Log ticket closure
        TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'user_type' => 'system',
            'content' => t('ticket_closed_by_admin_after_reply'),
        ]);

        // Send status change notification if enabled
        if ($this->send_notification && $oldStatus !== 'closed') {
            event(new TicketStatusChanged($this->ticket, $oldStatus, true));
        }

        // Reset form
        $this->replyContent = '';
        $this->attachments = [];

        // Refresh ticket data
        $this->ticket->refresh();
        $this->ticket->load(['replies.user']);

        session()->flash('success', 'reply_added_ticket_closed');
        $this->dispatch('ticket-closed');
    }

    public function getStatusBadgeProperty()
    {
        return match ($this->ticket->status) {
            'open' => 'bg-primary',
            'answered' => 'bg-info',
            'closed' => 'bg-success',
            'on_hold' => 'bg-secondary',
            default => 'bg-light',
        };
    }

    public function getPriorityBadgeProperty()
    {
        return match ($this->ticket->priority) {
            'high' => 'bg-danger',
            'medium' => 'bg-warning',
            'low' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    /**
     * Get HTML badge markup for ticket status
     */
    public function getStatusBadge(string $status): string
    {
        $badges = [
            'open' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">Open</span>',
            'answered' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200">Answered</span>',
            'closed' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Closed</span>',
            'on_hold' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">On Hold</span>',
        ];

        return $badges[$status] ?? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Unknown</span>';
    }

    /**
     * Get HTML badge markup for ticket priority
     */
    public function getPriorityBadge(string $priority): string
    {
        $badges = [
            'high' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200">High</span>',
            'medium' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200">Medium</span>',
            'low' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">Low</span>',
            'urgent' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900">Urgent</span>',
        ];

        return $badges[$priority] ?? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Unknown</span>';
    }

    /**
     * Get the appropriate icon class for an activity type
     */
    public function getActivityIcon(string $type): string
    {
        return match ($type) {
            'system' => 'bi-gear text-secondary',
            'reply' => 'bi-chat-dots text-primary',
            'status' => 'bi-arrow-repeat text-info',
            'priority' => 'bi-flag text-warning',
            'department' => 'bi-diagram-3 text-success',
            default => 'bi-activity text-muted',
        };
    }

    public function render()
    {
        $departments = Department::active()->get();

        // Generate activity log from ticket replies
        $activityLog = collect();

        if ($this->ticket->replies) {
            foreach ($this->ticket->replies as $reply) {
                $logEntry = [
                    'type' => $reply->user_type === 'system' ? 'system' : 'reply',
                    'description' => $reply->user_type === 'system' ? $reply->content : ($reply->user_type === 'admin' ? t('admin_replied_to_ticket') : 'Client replied to ticket'),
                    'created_at' => $reply->created_at,
                ];
                $activityLog->push($logEntry);
            }
        }

        return view('Tickets::livewire.admin.ticket-details', [
            'departments' => $departments,
            'activityLog' => $activityLog,
        ]);
    }
}
