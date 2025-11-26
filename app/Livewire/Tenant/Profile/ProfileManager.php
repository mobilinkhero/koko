<?php

namespace App\Livewire\Tenant\Profile;

use App\Models\Tenant;
use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileManager extends Component
{
    use WithFileUploads;

    // Add authenticated user property
    public $user;

    // Personal Info Properties
    public $firstname;

    public $lastname;

    public $email;

    public $phone;

    public $address;

    public $avatar;

    public $avatar_upload;

    public $default_language;

    public $remove_existing_image = false;

    // Password Properties
    public $current_password;

    public $password;

    public $password_confirmation;

    // billing details
    public $billing_name;

    public $billing_email;

    public $billing_address;

    public $billing_city;

    public $billing_state;

    public $billing_zip_code;

    public $billing_country;

    public $billing_phone;

    public function mount()
    {
        $this->user = Auth::user();

        $tenant = Tenant::find($this->user->tenant_id);

        // Initialize personal info
        $this->firstname = $this->user->firstname;
        $this->lastname = $this->user->lastname;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone;
        $this->address = $tenant->address;
        $this->default_language = $this->user->default_language;
        // Initialize appearance
        $this->avatar = $this->user->avatar;
        $this->avatar_upload = null;

        $this->billing_name = $tenant->billing_name;
        $this->billing_email = $tenant->billing_email;
        $this->billing_address = $tenant->billing_address;
        $this->billing_city = $tenant->billing_city;
        $this->billing_state = $tenant->billing_state;
        $this->billing_zip_code = $tenant->billing_zip_code;
        $this->billing_country = $tenant->billing_country;
        $this->billing_phone = $tenant->billing_phone;

        apply_filters('billing_component_data', $this);

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

        $user = Auth::user();
        $tenant = current_tenant();

        if ($this->remove_existing_image && $this->user->avatar) {
            Storage::disk('public')->delete($this->user->avatar);
            $this->user->avatar = null;
            $this->remove_existing_image = false;
        }

        // Handle new image upload
        if ($this->avatar_upload) {
            $path = $this->avatar_upload->store('tenant/'.tenant_id().'/profile-images', 'public');
            $this->user->avatar = $path;
            $this->user->save(); // Don't forget to save the changes
        }

        // Check if user data has changed
        $this->user->firstname = $this->firstname;
        $this->user->lastname = $this->lastname;
        $this->user->email = $this->email;
        $this->user->phone = $this->phone;
        $this->user->default_language = $this->default_language;

        $userChanged = $this->user->isDirty(['firstname', 'lastname', 'email', 'phone', 'default_language']);

        // Check if tenant data has changed
        $tenant->address = $this->address;
        $tenantChanged = $tenant->isDirty(['address']);

        // Only save and notify if there are changes
        if ($userChanged || $tenantChanged || $this->avatar_upload || $this->remove_existing_image) {
            if ($userChanged) {
                $this->user->save();
            }

            if ($tenantChanged) {
                $tenant->save();
            }

            $this->notify([
                'type' => 'success',
                'message' => t('personal_information_updated'),
            ]);
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
            ], ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->password),
        ]);

        // Destroy current session and logout
        session()->invalidate(); // Regenerate the session ID
        Auth::logout(); // Logout the user

        session()->flash('success', t('password_updated_successfully'));
        // Reset password fields
        $this->current_password = null;
        $this->password = null;
        $this->password_confirmation = null;

        $this->notify([
            'type' => 'success',
            'message' => t('password_updated_successfullyy'),
        ]);

        return redirect()->route('login');
    }

    /**
     * Update the tenant's billing information
     */
    public function updateBilling()
    {
        // Base validation rules
        $validation_data = [
            'billing_name' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
            'billing_email' => ['nullable', 'email', 'unique:users,email,'.Auth::id(), new PurifiedInput(t('sql_injection_error'))],
            'billing_address' => ['nullable', 'string', 'max:1000', new PurifiedInput(t('sql_injection_error'))],
            'billing_city' => ['nullable', 'string', 'max:1000', new PurifiedInput(t('sql_injection_error'))],
            'billing_state' => ['nullable', 'string', 'max:1000', new PurifiedInput(t('sql_injection_error'))],
            'billing_zip_code' => ['nullable', 'string', 'max:20', new PurifiedInput(t('sql_injection_error'))],
            'billing_country' => 'nullable',
            'billing_phone' => ['nullable', 'unique:users,phone,'.Auth::id(), new PurifiedInput(t('sql_injection_error'))],
        ];

        // Apply hooks for additional validation
        $validation_data = apply_filters('billing_validation_data', $validation_data);

        $this->validate($validation_data);

        $tenant = current_tenant();

        // Assign base billing fields
        $fields = [
            'billing_name',
            'billing_email',
            'billing_address',
            'billing_city',
            'billing_state',
            'billing_zip_code',
            'billing_country',
            'billing_phone',
        ];

        foreach ($fields as $field) {
            $tenant->{$field} = $this->{$field};
        }

        // Apply hooks to allow modules to modify/add extra fields dynamically
        $tenant = apply_filters('billing_registration_data', $tenant);

        // Collect all fields to check for changes
        $billing_fields_to_check = apply_filters('billing_tracked_fields', $fields);

        // Ensure extra fields are assigned to model so isDirty() works
        foreach ($billing_fields_to_check as $field) {
            if (property_exists($this, $field)) {
                $tenant->{$field} = $this->{$field};
            }
        }

        // Check for changes
        if ($tenant->isDirty($billing_fields_to_check)) {
            $tenant->save();

            $this->notify([
                'type' => 'success',
                'message' => t('billing_information_updated'),
            ]);
        }
    }

    public function getCountriesProperty()
    {
        return get_country_list();
    }

    public function render()
    {
        return view('livewire.tenant.profile.profile-manager');
    }
}
