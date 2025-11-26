<?php

namespace App\Livewire\Admin\Department;

use App\Models\User;
use App\Rules\PurifiedInput;
use Livewire\Component;
use Modules\Tickets\Models\Department;

class DepartmentList extends Component
{
    public $department;

    public $showDepartmentModal = false;

    public $confirmingDeletion = false;

    public $department_id = null;

    public $name;

    public $description;

    public $status = true;

    // For backward compatibility, kept to handle old code
    public $assigned_id = null;

    // Array for multiple assignees
    public $assignee_ids = [];

    public $users = [];

    public $bulkAction = '';

    public $checkboxValues = [];

    protected $listeners = [
        'editDepartment' => 'editDepartment',
        'confirmDelete' => 'confirmDelete',
    ];

    public function mount()
    {
        if (! checkPermission('admin.department.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $this->loadActiveAdminUsers();
        $this->resetForm();
    }

    /**
     * Load active admin users for the dropdown
     */
    private function loadActiveAdminUsers()
    {
        // Load users for the dropdown - only active admin staff members
        $this->users = User::where('user_type', 'admin')
            ->where('is_admin', false)
            ->where('active', true)
            // Skip filtering by current user ID to avoid auth issues
            ->orderBy('firstname')
            ->select(['id', 'firstname', 'lastname', 'email'])
            ->get()
            ->map(function ($user) {
                // Create a display name property instead of modifying the model
                $displayName = $user->firstname.' '.$user->lastname;

                return [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'name' => $displayName, // Add this as a property rather than modifying the model
                ];
            });
    }

    public function createDepartment()
    {
        $this->resetForm();
        $this->loadActiveAdminUsers(); // Refresh users list to get latest active users
        $this->showDepartmentModal = true;
    }

    private function resetForm()
    {
        $this->reset(['name', 'description', 'status', 'department_id', 'assigned_id', 'assignee_ids', 'bulkAction', 'checkboxValues']);
        $this->status = true; // Default to active
        $this->resetValidation();
        $this->department = new Department;
    }

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'unique:departments,name,'.($this->department_id ?? 'NULL'),
                new PurifiedInput(t('sql_injection_error')),
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                new PurifiedInput(t('sql_injection_error')),
                'max:255',
            ],
            'status' => [
                'required',
                'boolean',
            ],
            'assignee_ids' => [
                'nullable',
                'array',
            ],
            'assignee_ids.*' => [
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $user = User::find($value);
                        if (! $user || $user->user_type !== 'admin' || ! $user->active) {
                            $fail(t('selected_user_must_be_active_admin'));
                        }
                    }
                },
            ],
            // For backward compatibility - not actually used in the update
            'assigned_id' => [
                'nullable',
            ],
        ];
    }

    public function save()
    {
        if (checkPermission(['admin.department.create', 'admin.department.edit'])) {
            $isUpdate = ! empty($this->department_id);

            $this->validate();

            // try {
            if ($isUpdate) {
                $this->department = Department::findOrFail($this->department_id);
            } else {
                $this->department = new Department;
            }

            $this->department->name = $this->name;
            $this->department->description = $this->description;
            $this->department->status = true;

            // Convert array to JSON string before saving
            $this->department->assignee_id = json_encode($this->assignee_ids ?: []);

            // For backward compatibility, if single assigned_id is set and assignee_ids is empty,
            // use it instead
            if (empty($this->assignee_ids) && $this->assigned_id) {
                $this->department->assignee_id = json_encode([$this->assigned_id]);
            }

            if ($this->department->isDirty()) {
                $this->department->save();

                $this->showDepartmentModal = false;
                $this->dispatch('pg:eventRefresh-department-table-l3bdsm-table');

                $this->notify([
                    'type' => 'success',
                    'message' => $isUpdate ? t('department_update_successfully') : t('department_added_successfully'),
                ]);

                $this->resetForm();
            } else {
                $this->showDepartmentModal = false;
            }
            // } catch (\Exception $e) {
            //     app_log('Department save failed: '.$e->getMessage(), 'error', $e, [
            //         'department_id' => $this->department->id ?? null,
            //     ]);

            //     $this->notify(['type' => 'danger', 'message' => t('department_save_failed')]);
            // }
        }
    }

    public function editDepartment($id)
    {
        $department = Department::findOrFail($id);

        $this->department_id = $department->id;
        $this->name = $department->name;
        $this->description = $department->description;
        $this->status = true;

        // Decode the JSON string from assignee_id to array
        $this->assignee_ids = json_decode($department->assignee_id, true) ?: [];

        // For backward compatibility, also set the first assignee as assigned_id
        $this->assigned_id = ! empty($this->assignee_ids) ? $this->assignee_ids[0] : null;

        $this->loadActiveAdminUsers(); // Refresh users list to get latest active users
        $this->resetValidation();
        $this->showDepartmentModal = true;
    }

    public function confirmDelete($id)
    {
        $this->department_id = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('admin.currency.delete')) {
            try {
                $department = Department::findOrFail($this->department_id);

                // Check if department has tickets assigned
                if ($department->tickets()->count() > 0) {
                    $this->notify([
                        'type' => 'danger',
                        'message' => t('department_has_tickets_error'),
                    ]);

                    return;
                }

                $department->delete();
                $this->notify(['type' => 'success', 'message' => t('department_deleted_successfully')]);

                $this->confirmingDeletion = false;
                $this->resetForm();
                $this->dispatch('pg:eventRefresh-department-table-l3bdsm-table');
            } catch (\Exception $e) {
                app_log('Department deletion failed: '.$e->getMessage(), 'error', $e, [
                    'currency_id' => $this->department->id ?? null,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('department_delete_failed')]);
            }
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-department-table-l3bdsm-table');
    }

    public function render()
    {
        return view('livewire.admin.department.department-list');
    }
}
