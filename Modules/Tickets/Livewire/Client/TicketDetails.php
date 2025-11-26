<?php

namespace Modules\Tickets\Livewire\Client;

use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Tickets\Events\TicketReplyCreated;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;

class TicketDetails extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public string $content = '';

    public array $attachments = [];

    public bool $showReplyForm = true;

    protected function rules()
    {
        return [
            'content' => ['required', 'string', 'min:5', 'max:2000', new PurifiedInput(t('sql_injection_error'))],
            'attachments.*' => 'file|max:10240',
        ];
    }

    protected array $messages = [
        'content.required' => 'Please enter your reply.',
        'content.min' => 'Reply must be at least 5 characters.',
        'content.max' => 'Reply cannot exceed 2000 characters.',
        'attachments.*.file' => 'Invalid file uploaded.',
        'attachments.*.max' => 'File size cannot exceed 10MB.',
    ];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket->load(['department', 'replies.user']);
        if (! $this->ticket->tenant_viewed) {
            $this->ticket->tenant_viewed = 1;
            $this->ticket->save();
        }

        $this->showReplyForm = ! in_array($ticket->status, ['closed']);
    }

    public function removeReplyAttachment(int $index): void
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
        // Limit attachments to maximum 5 files
        if (count($this->attachments) > 5) {
            $this->addError('attachments', t('upload_maximum_5_files'));

            return;
        }
    }

    private function processAttachments(): array
    {
        $attachmentData = [];
        // Take only the first 5 attachments
        $filestoProcess = array_slice($this->attachments, 0, 5);

        foreach ($filestoProcess as $file) {
            if ($file && is_object($file) && method_exists($file, 'getClientOriginalName')) {
                try {
                    // Create a unique filename
                    $originalName = $file->getClientOriginalName();
                    $filename = time().'_'.str_replace(' ', '_', $originalName);

                    // Store in standard tickets/{tenant_id}/replies format
                    $path = $file->storeAs(
                        "tickets/{$this->ticket->tenant_id}/replies",
                        $filename,
                        'public'
                    );

                    $attachmentData[] = [
                        'filename' => $filename,
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ];
                } catch (\Exception $e) {
                    throw new \Exception(t('failed_to_upload ').$originalName);
                }
            }
        }

        return $attachmentData;
    }

    public function submitReply(): void
    {

        $this->validate([
            'content' => ['required', 'string', 'min:5', 'max:2000', new PurifiedInput(t('sql_injection_error'))],
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        // Process attachments first to catch any upload errors
        $attachmentData = [];

        if (! empty($this->attachments)) {
            try {
                $attachmentData = $this->processAttachments();
            } catch (\Exception $e) {
                $this->addError('attachments', $e->getMessage());

                return;
            }
        }

        $reply = TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'user_type' => $this->ticket->tenant_staff_id ? 'tenant_staff' : 'tenant',
            'content' => $this->content,
            'attachments' => ! empty($attachmentData) ? $attachmentData : null,
        ]);

        event(new TicketReplyCreated($reply, true));

        // Update the timestamp only
        // $this->ticket->touch();

        // Refresh the ticket data
        $this->ticket->refresh();
        $this->ticket->load(['department', 'replies.user']);

        // Reset the reply form
        $this->reset(['content', 'attachments']);
        $this->resetErrorBag();

        session()->flash('success', t('reply_added_successfully'));
    }

    public function getStatusColorProperty(): string
    {
        $colors = [
            'open' => 'success',
            'in_progress' => 'warning',
            'resolved' => 'primary',
            'closed' => 'secondary',
        ];

        return $colors[$this->ticket->status] ?? 'secondary';
    }

    public function getPriorityColorProperty(): string
    {
        $colors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'urgent' => 'dark',
        ];

        return $colors[$this->ticket->priority] ?? 'secondary';
    }

    public function getCanReplyProperty(): bool
    {
        return $this->showReplyForm && ! in_array($this->ticket->status, ['closed']);
    }

    public function getCanCloseProperty(): bool
    {
        return ! in_array($this->ticket->status, ['closed']);
    }

    public function getCanReopenProperty(): bool
    {
        return $this->ticket->status === 'closed';
    }

    public function render()
    {
        return view('Tickets::livewire.client.ticket-details', [
            'replies' => $this->ticket->replies()->with('user')->orderBy('created_at')->get(),
        ]);
    }
}
