<?php

namespace App\Livewire\Admin\User;

use App\Models\User;
use Livewire\Component;

class UserDetails extends Component
{
    public $user;

    public function mount($userId)
    {
        if (! checkPermission('admin.users.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->route('admin.dashboard');
        }
        $this->user = User::findOrFail($userId);
    }

    public function render()
    {
        return view('livewire.admin.user.user-details');
    }
}
