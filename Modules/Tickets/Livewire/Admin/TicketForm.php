<?php

namespace Modules\Tickets\Livewire\Admin;

use App\Models\Tenant;
use App\Models\User;
use App\Rules\PurifiedInput;
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

    public ?Ticket $ticket = null;

    public array $form = [
        'tenant_id' => 0,
        'subject' => '',
        'department_id' => 0,
        'priority' => 'medium',
        'status' => 'open',
        'body' => '',
        'assignee_id' => [],  // Changed from assigned_id to assignee_id, now an array
    ];

    // Add these properties for better UX
    public $selectedDepartment = null;

    public $autoAssignedUsers = [];  // Changed from autoAssignedUser to autoAssignedUsers

    public $showAssignmentNotice = false;

    public $attachments = []; // For new file uploads

    public array $uploadedFiles = [];

    public $existingAttachments = []; // For existing attachments from DB

    public bool $sendNotification = true;

    public bool $markAsRead = true;

    protected function rules()
    {
        return [
            'form.tenant_id' => 'required|exists:tenants,id',
            'form.subject' => ['required', 'string', 'min:5', 'max:255', new PurifiedInput('sql_injection_error')],
            'form.department_id' => 'required|exists:departments,id',
            'form.priority' => 'required|in:low,medium,high',
            'form.status' => 'required|in:open,answered,closed,on_hold',
            'form.body' => ['required', 'string', 'min:10', 'max:1000', new PurifiedInput('sql_injection_error')],
            'form.assignee_id' => 'nullable|array',  // Changed validation for assignee_id
            'form.assignee_id.*' => 'exists:users,id',  // Validate each assignee id
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
            'attachments' => 'max:5',             // Maximum 5 files allowed
        ];
    }

    protected function messages()
    {
        return [
            'form.tenant_id.required' => 'The tenant field is required.',
            'form.tenant_id.exists' => 'The tenant field is invalid.',
            'form.subject.required' => 'The subject field is required.',
            'form.department_id.required' => 'The department field is required.',
            'form.department_id.exists' => 'The department field is invalid.',
            'form.priority.required' => 'The priority field is required.',
            'form.status.required' => 'The status field is required.',
            'form.body.required' => 'The body field is required.',
            'attachments.*.max' => 'Each attachment must not exceed 10MB.',
            'form.assigned_id.required' => 'The assigned user field is required.',
            'attachments.max' => 'You can upload a maximum of 5 attachments.',
        ];
    }

    // Enhanced updatedFormDepartmentId method using TicketHelper
    public function updatedFormDepartmentId($value)
    {
        // Reset assignment notice
        $this->showAssignmentNotice = false;
        $this->autoAssignedUsers = [];  // Reset to empty array
        $this->selectedDepartment = null;

        $assignment = \App\Helpers\TicketHelper::getAssignedUserForDepartment($value);

        if ($assignment) {
            $this->selectedDepartment = Department::find($value);
            $this->form['assignee_id'] = isset($assignment['user_id']) ? [$assignment['user_id']] : [];  // Convert to array
            $this->autoAssignedUsers = isset($assignment['user_id']) ? [$assignment] : [];  // Convert to array
            $this->showAssignmentNotice = true;
        } else {
            $this->form['assignee_id'] = [];  // Reset to empty array
        }
    }

    // Enhanced mount method with proper attachment handling
    public function mount(?Ticket $ticket = null)
    {
        if (($ticket && $ticket->exists)) {
            $this->ticket = $ticket;
            $this->form = [
                'tenant_id' => $ticket->tenant_id ?? 0,
                'subject' => $ticket->subject ?? '',
                'department_id' => $ticket->department_id ?? 0,
                'priority' => $ticket->priority ?? 'medium',
                'status' => $ticket->status ?? 'open',
                'body' => $ticket->body ?? '',
                'assignee_id' => is_array($ticket->assignee_id) ? $ticket->assignee_id : [],  // Convert to array if needed
            ];

            // Handle existing attachments properly
            $this->existingAttachments = $this->processExistingAttachments($ticket->attachments);
            // Handle existing attachments
            if ($ticket->attachments) {
                $this->uploadedFiles = $ticket->attachments;
            }

            // Load department info for editing
            if ($this->form['department_id']) {
                $this->selectedDepartment = Department::find($this->form['department_id']);
                // Load assigned users info
                if (! empty($this->form['assignee_id'])) {
                    $this->autoAssignedUsers = User::whereIn('id', $this->form['assignee_id'])
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
        } else {
            $this->resetForm();
        }
    }

    /**
     * Process existing attachments to ensure consistent format for admin side
     */
    private function processExistingAttachments($attachments)
    {
        if (! $attachments) {
            return [];
        }

        // If it's a string, decode it first
        if (is_string($attachments)) {
            $attachments = json_decode($attachments, true) ?: [];
        }

        // If it's not an array, return empty
        if (! is_array($attachments)) {
            return [];
        }

        $processedAttachments = [];

        foreach ($attachments as $attachment) {
            if (is_array($attachment)) {
                // New format from tenant side: extract filename
                if (isset($attachment['filename'])) {
                    $processedAttachments[] = $attachment['filename'];
                }
            } else {
                // Legacy format: already a filename string
                $processedAttachments[] = $attachment;
            }
        }

        return array_values($processedAttachments);
    }

    // Get admin users with better filtering
    public function getAdminUsersProperty()
    {
        return User::where('user_type', 'admin')
            ->where('is_admin', false)
            ->select('id', 'firstname', 'lastname', 'email')
            ->orderBy('firstname')
            ->get();
    }

    // Get departments with assigned users
    public function getDepartmentsProperty()
    {
        $departments = Department::where('status', true)
            ->orderBy('name')
            ->get();

        // Map departments to include assignment info
        return $departments->map(function ($department) {
            $assigneeIds = $department->assignee_id;
            if (is_string($assigneeIds)) {
                $assigneeIds = json_decode($assigneeIds, true) ?? [];
            }
            if (empty($assigneeIds) || ! is_array($assigneeIds)) {
                $assigneeIds = [];
            }
            $assigneeIds = array_values(array_filter(array_map('intval', $assigneeIds)));

            $assignedUsers = empty($assigneeIds) ? collect() : User::whereIn('id', $assigneeIds)
                ->where('user_type', 'admin')
                ->get();

            return (object) [
                'id' => $department->id,
                'name' => $department->name,
                'assignee_ids' => $assignedUsers->pluck('id')->toArray(),
                'assignee_names' => $assignedUsers->pluck('firstname', 'id')
                    ->map(fn ($name, $id) => $name.' '.$assignedUsers->where('id', $id)->first()->lastname)
                    ->toArray(),
                'display_name' => $department->name,
            ];
        });
    }

    // Method to get department assignment info
    public function getDepartmentAssignmentInfo($departmentId)
    {
        if (! $departmentId) {
            return null;
        }

        $department = Department::find($departmentId);

        if ($department && ! empty($department->assignee_id)) {
            return [
                'department_name' => $department->name,
                'assigned_users' => $department->assignedUsers(),
            ];
        }

        return null;
    }

    public function removeExistingAttachment($filename)
    {
        $this->existingAttachments = array_filter($this->existingAttachments, function ($attachment) use ($filename) {
            return $attachment !== $filename;
        });
        $this->existingAttachments = array_values($this->existingAttachments);
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

        // Count total attachments (existing + new)
        $totalAttachments = count($this->uploadedFiles) + count($this->attachments);
        if ($totalAttachments > 5) {
            $this->addError('attachments', t('attachment_validation'));

            return;
        }
        $attachmentPaths = [];

        // Process new attachments if any
        if (! empty($this->attachments) && is_array($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                if ($attachment && is_object($attachment) && method_exists($attachment, 'getClientOriginalName')) {
                    try {
                        // Create a unique filename
                        $originalName = $attachment->getClientOriginalName();
                        $filename = time().'_'.str_replace(' ', '_', $originalName);

                        // Store the file
                        $path = $attachment->storeAs(
                            "tickets/{$this->form['tenant_id']}",
                            $filename,
                            'public'
                        );

                        $attachmentPaths[] = [
                            'filename' => $filename,
                            'path' => $path,
                            'size' => $attachment->getSize(),
                        ];
                    } catch (\Exception $e) {
                        $this->addError('attachments', t('failed_to_upload').$originalName);

                        continue;
                    }
                }
            }
        }
        $allAttachments = array_merge($this->uploadedFiles, $attachmentPaths);

        $ticketData = [
            'tenant_id' => $this->form['tenant_id'],
            'subject' => $this->form['subject'],
            'department_id' => $this->form['department_id'],
            'priority' => $this->form['priority'],
            'status' => $this->form['status'],
            'body' => $this->form['body'],
            'assignee_id' => json_encode($this->form['assignee_id'] ?: []),  // Store as JSON string
            'admin_viewed' => true,
            'tenant_viewed' => false,
        ];

        if (! empty($allAttachments)) {
            $ticketData['attachments'] = $allAttachments;
        }

        try {
            $ticketData['ticket_id'] = Ticket::generateTicketId();
            $this->ticket = Ticket::create($ticketData);

            // Dispatch ticket created event first
            event(new TicketCreated($this->ticket, $this->form['tenant_id']));

            // Dispatch assignment event for new tickets with assignment
            event(new TicketAssigned($this->ticket));

            $message = t('ticket_created_successfully');

            $this->notify([
                'type' => 'success',
                'message' => $message,
            ], true);

            $this->redirect(route('admin.tickets.index'));

            $this->resetForm();
        } catch (\Exception $e) {
            app_log(t('failed_to_save_ticket'), 'error', $e, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_save_ticket').$e->getMessage(),
            ]);
        }
    }

    /**
     * Construct file path for existing attachments
     */
    private function constructPathForExistingFile($filename)
    {
        if (! $this->ticket) {
            return null;
        }

        return "tickets/{$this->ticket->tenant_id}/{$filename}";
    }

    public function resetForm()
    {
        $this->form = [
            'tenant_id' => 0,
            'tenant_staff_id' => 0,
            'subject' => '',
            'department_id' => 0,
            'priority' => 'medium',
            'status' => 'open',
            'body' => '',
            'assignee_id' => [],  // Reset to empty array
        ];
        $this->attachments = [];
        $this->existingAttachments = [];
        $this->selectedDepartment = null;
        $this->autoAssignedUsers = [];  // Reset to empty array
        $this->showAssignmentNotice = false;
        $this->resetErrorBag();
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    public function updatedFormTenantId()
    {
        $this->form['tenant_staff_id'] = 0;
    }

    public function getTenantsProperty()
    {
        return Tenant::where('status', 'active')
            ->select('id', 'company_name', 'subdomain')
            ->orderBy('company_name')
            ->get();
    }

    public function getTenantStaffProperty()
    {
        if (! $this->form['tenant_id']) {
            return collect();
        }

        return User::where('tenant_id', $this->form['tenant_id'])
            ->where('role_id', '!=', 1)
            ->select('id', 'firstname', 'lastname', 'email')
            ->orderBy('firstname')
            ->get();
    }

    public function getPriorityOptionsProperty()
    {
        return config('Tickets.priorities', [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ]);
    }

    public function getStatusOptionsProperty()
    {
        return config('Tickets.statuses', [
            'open' => 'Open',
            'answered' => 'Answered',
            'closed' => 'Closed',
            'on_hold' => 'On Hold',
        ]);
    }

    public function render()
    {
        return view('Tickets::livewire.admin.ticket-form', [
            'adminUsers' => $this->adminUsers,
        ]);
    }
}
