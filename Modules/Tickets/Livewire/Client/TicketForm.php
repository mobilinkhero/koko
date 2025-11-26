<?php

namespace Modules\Tickets\Livewire\Client;

use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Tickets\Events\TicketAssigned;
use Modules\Tickets\Events\TicketCreated;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;

class TicketForm extends Component
{
    use WithFileUploads;

    // Form properties
    public string $subject = ''; // This will be same as title for client tickets

    public string $body = ''; // Alias for description

    public ?int $department_id = null;

    public string $priority = 'medium';

    public array $attachments = [];

    public array $uploadedFiles = [];

    // Auto-assignment properties
    public $selectedDepartment = null;

    public $autoAssignedUsers = [];  // Changed from autoAssignedUser to autoAssignedUsers

    public $assignee_id = [];  // Changed from assigned_id to assignee_id, now an array

    // Component properties
    public ?Ticket $ticket = null;

    public array $departments = [];

    // Validation rules
    protected function rules()
    {
        return [
            'subject' => ['required', 'string', 'min:5', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'body' => ['required', 'string', 'min:10', 'max:1000', new PurifiedInput(t('sql_injection_error'))],
            'department_id' => 'required|exists:departments,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments' => 'max:5', // Maximum 5 files allowed
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt,zip',
            'assignee_id' => 'nullable|array',  // Added validation for assignee_id array
            'assignee_id.*' => 'exists:users,id',  // Validate each assignee id
        ];
    }

    protected array $messages = [
        'subject.required' => 'Please enter a ticket subject.',
        'subject.min' => 'Subject must be at least 5 characters.',
        'subject.max' => 'Subject cannot exceed 255 characters.',
        'body.required' => 'Please provide ticket content.',
        'body.min' => 'Content must be at least 10 characters.',
        'body.max' => 'Content cannot exceed 5000 characters.',
        'department_id.required' => 'Please select a department.',
        'department_id.exists' => 'Selected department is invalid.',
        'priority.required' => 'Please select a priority level.',
        'priority.in' => 'Invalid priority level selected.',
        'attachments.max' => 'You cannot upload more than 5 files.',
        'attachments.*.file' => 'Invalid file uploaded.',
        'attachments.*.max' => 'File size cannot exceed 10MB.',
        'attachments.*.mimes' => 'Invalid file type. Allowed: jpg, jpeg, png, pdf, doc, docx, txt, zip.',
    ];

    public function mount(?Ticket $ticket = null): void
    {
        // Load departments
        $this->departments = Department::where('status', true)
            ->select('id', 'name', 'description', 'assignee_id')
            ->get()
            ->toArray();

        if ($ticket && $ticket->exists) {
            $this->ticket = $ticket;

            // Set form fields from ticket data
            $this->subject = $ticket->subject;
            $this->body = $ticket->body;
            $this->department_id = $ticket->department_id;
            $this->priority = $ticket->priority;
            $this->assignee_id = is_array($ticket->assignee_id) ? $ticket->assignee_id : []; // Convert to array if needed

            // Handle existing attachments
            if ($ticket->attachments) {
                $this->uploadedFiles = $ticket->attachments;
            }

            // Load department info and assignee info
            if ($this->department_id) {
                $this->selectedDepartment = collect($this->departments)
                    ->firstWhere('id', $this->department_id);

                if ($this->selectedDepartment && ! empty($this->assignee_id)) {
                    $this->autoAssignedUsers = User::whereIn('id', $this->assignee_id)
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
                }
            }
        }
    }

    /**
     * Auto-assign when department is selected (similar to admin side)
     */
    public function updatedDepartmentId($value)
    {
        // Reset assignment
        $this->autoAssignedUsers = [];  // Changed to array
        $this->selectedDepartment = null;
        $this->assignee_id = [];  // Changed to array

        if ($value) {
            $assignment = \App\Helpers\TicketHelper::getAssignedUserForDepartment($value);

            if ($assignment) {
                $this->selectedDepartment = collect($this->departments)->firstWhere('id', $value);
                $this->assignee_id = isset($assignment['user_id']) ? [$assignment['user_id']] : [];  // Convert to array
                $this->autoAssignedUsers = isset($assignment['user_id']) ? [$assignment] : [];  // Convert to array
            }
        }
    }

    public function removeAttachment(int $index): void
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    public function removeUploadedFile(int $index): void
    {
        if (isset($this->uploadedFiles[$index])) {
            // Delete the file from storage
            $file = $this->uploadedFiles[$index];
            if (isset($file['path']) && Storage::disk('public')->exists($file['path'])) {
                Storage::disk('public')->delete($file['path']);
            }

            unset($this->uploadedFiles[$index]);
            $this->uploadedFiles = array_values($this->uploadedFiles);

            // Update the ticket's attachments
            if ($this->ticket) {
                $this->ticket->update(['attachments' => $this->uploadedFiles]);
            }
        }
    }

    public function save()
    {
        $this->validate();

        // Validate total number of attachments
        $totalAttachments = count($this->uploadedFiles) + count($this->attachments);
        if ($totalAttachments > 5) {
            $this->addError('attachments', t('attachment_validation'));

            return;
        }

        $attachmentPaths = [];

        // Process new attachments if any
        if (! empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                if ($attachment && is_object($attachment) && method_exists($attachment, 'getClientOriginalName')) {
                    try {
                        $originalName = $attachment->getClientOriginalName();
                        $filename = time().'_'.str_replace(' ', '_', $originalName);

                        // Store in standardized tickets/{tenant_id} path
                        $currentUser = Auth::user();
                        $path = $attachment->storeAs(
                            'tickets/'.tenant_id(),
                            $filename,
                            'public'
                        );

                        $attachmentPaths[] = [
                            'filename' => $filename,
                            'path' => $path,
                            'size' => $attachment->getSize(),
                        ];
                    } catch (\Exception $e) {
                        $this->addError('attachments', t('failed_to_upload ').$attachment->getClientOriginalName());

                        continue;
                    }
                }
            }
        }

        // Merge existing attachments with new ones
        $allAttachments = array_merge($this->uploadedFiles, $attachmentPaths);

        // Determine tenant_id and tenant_staff_id based on current user
        $currentUser = Auth::user();
        $ticketData = [
            'subject' => $this->subject ?: '',
            'body' => $this->body ?: '',
            'department_id' => $this->department_id,
            'priority' => $this->priority,
            'assignee_id' => json_encode($this->assignee_id ?: []),  // Store as JSON string
            'tenant_viewed' => true,               // Client created/updated it
            'admin_viewed' => false,              // Admin needs to see it
        ];

        // Set tenant_id and tenant_staff_id based on user type
        if ($currentUser->user_type === 'tenant') {
            // Always set tenant_id from user's tenant_id
            $ticketData['tenant_id'] = $currentUser->tenant_id;

            if ($currentUser->is_admin) {
                // Tenant admin creates ticket
                $ticketData['tenant_staff_id'] = null;
            } else {
                // Tenant staff creates ticket
                $ticketData['tenant_staff_id'] = $currentUser->id;
            }
        }

        if (! empty($allAttachments)) {
            $ticketData['attachments'] = $allAttachments;
        }

        try {
            // Add new ticket specific fields
            $ticketData['status'] = 'open';
            $ticketData['ticket_id'] = Ticket::generateTicketId();

            // Create new ticket
            $this->ticket = Ticket::create($ticketData);
            $message = t('ticket_created_successfully');
            $event = 'ticket-created';

            event(new TicketCreated($this->ticket, tenant_id()));

            // Dispatch assignment event for new tickets with assignment
            event(new TicketAssigned($this->ticket));
            $this->notify([
                'type' => 'success',
                'message' => $message,
            ], true);

            $this->redirect(tenant_route('tenant.tickets.index'));

            $this->resetForm();
        } catch (\Exception $e) {
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_save_ticket').$e->getMessage(),
            ]);
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'subject',
            'body',
            'department_id',
            'priority',
            'attachments',
            'uploadedFiles',
            'assignee_id',  // Changed from assigned_id
            'selectedDepartment',
            'autoAssignedUsers',  // Changed from autoAssignedUser
        ]);
        $this->priority = 'medium';
        $this->assignee_id = [];  // Initialize as empty array
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function getDepartmentProperty()
    {
        if ($this->department_id) {
            return collect($this->departments)->firstWhere('id', $this->department_id);
        }

        return null;
    }

    public function render()
    {
        return view('Tickets::livewire.client.ticket-form', [
            'priorityOptions' => [
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
            ],
        ]);
    }
}
