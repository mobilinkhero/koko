<?php

namespace App\Livewire\Admin\Profile;

use App\Models\User;
use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class AdminProfileManager extends Component
{
    use WithFileUploads;

    public User $user;

    // Personal Info Properties
    public $firstname;

    public $lastname;

    public $email;

    public $phone;

    public $address;

    public $default_language;

    // Password Properties
    public $current_password;

    public $password;

    public $password_confirmation;

    public $avatar;

    public $avatar_upload;

    public $remove_existing_image = false;

    public function mount()
    {
        $this->user = Auth::user();

        // Initialize personal info
        $this->firstname = $this->user->firstname;
        $this->lastname = $this->user->lastname;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone;
        $this->address = $this->user->address;
        $this->default_language = $this->user->default_language;
        $this->avatar = $this->user->avatar;
        $this->avatar_upload = null;
    }

    public function updatePersonalInfo()
    {

        $this->validate([
            'firstname' => ['required', 'string', new PurifiedInput(t('sql_injection_error'))],
            'lastname' => ['required', 'string', new PurifiedInput(t('sql_injection_error'))],
            'email' => ['required', 'email', 'unique:users,email,'.Auth::id()],
            'phone' => ['required', 'unique:users,phone,'.Auth::id()],
            'address' => ['nullable', 'string'],
            'avatar_upload' => ['nullable', 'image', 'max:1024'],
        ]);

        if ($this->remove_existing_image && $this->user->avatar) {
            Storage::disk('public')->delete($this->user->avatar);
            $this->user->avatar = null;
            $this->remove_existing_image = false;
        }

        // Handle new image upload
        if ($this->avatar_upload) {
            $path = $this->avatar_upload->store('admin/profile-images', 'public');
            $this->user->avatar = $path;
        }

        // Update only changed fields
        $this->user->fill([
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone ?? $this->user->phone,
            'default_language' => $this->default_language,
        ]);

        if ($this->user->isDirty()) {
            $this->user->save();
            $this->notify(['type' => 'success', 'message' => t('profile_update_successfully')]);
        }

    }

    public function removeProfileImage()
    {
        if ($this->user->avatar) {
            Storage::disk('public')->delete($this->user->avatar);
            $this->user->avatar = null;
            $this->user->save();

            $this->notify([
                'type' => 'success',
                'message' => t('profile_image_removed_successfully'),
            ]);
        }
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required_with:password|current_password:web',
            'password' => [
                'nullable',
                'min:8',
                'confirmed',
            ],
        ]);

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($this->password),
        ]);

        // Destroy current session and logout
        session()->invalidate(); // Regenerate the session ID
        Auth::logout(); // Logout the user

        session()->flash('success', t('password_updated'));

        // Reset password fields
        $this->current_password = null;
        $this->password = null;
        $this->password_confirmation = null;

        $this->notify([
            'type' => 'success',
            'message' => t('password_update_successfully'),
        ]);

        // Redirect to login or home page
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.admin.profile.admin-profile-manager');
    }
}
