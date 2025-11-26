<?php

namespace App\Livewire\Admin\User;

use App\Models\User;
use Livewire\Component;

class UserList extends Component
{
    public User $user;

    public $userId;

    public $confirmingDeletion = false;

    protected $listeners = [
        'editUser' => 'editUser',
        'confirmDelete' => 'confirmDelete',
        'viewUser' => 'viewUser',
    ];

    public function mount()
    {
        if (! checkPermission('admin.users.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $this->user = new User;
    }

    public function createuser()
    {
        $this->redirect(route('admin.users.save'));
    }

    public function editUser($userId)
    {
        $this->user = User::findOrFail($userId);
        $this->redirect(route('admin.users.save', ['userId' => $userId]));
    }

    public function viewUser($userId)
    {
        return to_route('admin.users.details', ['userId' => $userId]);
    }

    public function confirmDelete($userId)
    {
        $this->userId = $userId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('admin.users.delete')) {
            $user = User::findOrFail($this->userId);
            if ($user->id == auth()->id()) {
                $this->notify(['type' => 'warning', 'message' => t('cannot_delete_yourself')]);

                return;
            }
            $user->delete();
            $this->notify(['type' => 'success', 'message' => t('user_deleted_successfully')]);
            $this->confirmingDeletion = false;
            $this->dispatch('pg:eventRefresh-user-table');
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-user-table');
    }

    public function render()
    {
        return view('livewire.admin.user.user-list');
    }
}
