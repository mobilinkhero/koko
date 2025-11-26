<?php

namespace App\Livewire\Admin\User;

use App\Models\User;
use App\Rules\PurifiedInput;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserCreator extends Component
{
    use WithFileUploads;

    public User $user;

    public $id;

    public $password;

    public $password_confirmation;

    public $is_admin = false;

    public $user_type;

    public $roles;

    public $lockedPermissions = [];

    public $permissions;

    public $selectedPermissions = [];

    public $role_id;

    public ?int $userId = null;

    public $userAdditionalPermissions = [];

    public $roleAdditionalPermissions = [];

    public $rolePermissions = [];

    public $sendWelcomeMail = false;

    public $isVerified = false;

    protected $listeners = [
        'editUser' => 'editUser',
    ];

    protected function rules()
    {
        return [
            'user.firstname' => [
                'required',
                'string',
                new PurifiedInput(t('sql_injection_error')),
                'max:255',
            ],
            'user.lastname' => [
                'required',
                'string',
                new PurifiedInput(t('sql_injection_error')),
                'max:255',
            ],
            'user.email' => [
                'required',
                'email',
                'unique:users,email,'.$this->user->id,
                new PurifiedInput(t('sql_injection_error')),
                'max:255',
            ],
            'user.is_admin' => 'nullable|boolean',
            'user.phone' => ['required',  Rule::unique('users', 'phone')
                ->ignore($this->user->id)
                ->where(function ($query) {
                    return $query->where('tenant_id', null);
                }), new PurifiedInput(t('sql_injection_error'))],
            'user.default_language' => 'nullable',
            'user.avatar' => is_object($this->user->avatar) ? ['nullable'] : 'nullable',
            'password' => ($this->user->id) ? ['nullable', Password::defaults(), 'min:8'] : ['required', 'confirmed', Password::defaults(), 'min:8'],
            'role_id' => [$this->is_admin ? 'nullable' : 'required', 'integer', 'exists:roles,id'],

            'user.country_id' => 'nullable|integer',
            'user.address' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error')), 'max:500'],
        ];
    }

    public function mount()
    {
        if (! checkPermission(['admin.users.create', 'admin.users.edit'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->route('admin.dashboard');
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->id = $this->getId();
        $this->userId = request()->route('userId');
        $this->user = $this->userId ? User::with(['roles', 'permissions'])->findOrFail($this->userId) : new User;
        $this->is_admin = $this->user->is_admin;
        $this->roles = Role::with('permissions')->whereNull('tenant_id')->get();
        $this->role_id = ($this->userId && ! empty($this->user->roles->first())) ? optional($this->user->roles->first())->id : null;
        $this->sendWelcomeMail = $this->user->send_welcome_mail ?? false;
        $this->isVerified = ! empty($this->user->email_verified_at) ? true : false;

        $allPermissions = Permission::where('scope', 'admin')
            ->orWhere('name', 'like', 'admin.%')
            ->orderBy('name')
            ->get();

        $this->permissions = $allPermissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);

            return count($parts) > 1 ? $parts[1] : 'general';
        })->toArray();

        // Initialize selected permissions with user's permissions
        if ($this->userId) {
            $this->selectedPermissions = $this->user->permissions->pluck('name')->toArray();
        }

        // Load role permissions if role is selected
        if ($this->role_id) {
            $this->loadPermissions($this->role_id);
        }
    }

    private function loadPermissions($roleId)
    {
        // Clear previous permissions
        $this->rolePermissions = [];
        $this->userAdditionalPermissions = [];

        if (! empty($roleId) && ! $this->is_admin) {
            $role = Role::findOrFail($roleId);

            // Get role permissions
            $this->rolePermissions = $role->permissions->pluck('name')->toArray();

            // Keep track of user's additional permissions that aren't part of the role
            if ($this->userId) {
                $userPermissions = $this->user->permissions->pluck('name')->toArray();
                $this->userAdditionalPermissions = array_diff($userPermissions, $this->rolePermissions);

                // Make sure these additional permissions are included in selectedPermissions
                $this->selectedPermissions = array_unique(array_merge(
                    $this->rolePermissions,
                    $this->userAdditionalPermissions
                ));
            } else {
                // For a new user, start with just the role permissions
                $this->selectedPermissions = $this->rolePermissions;
            }
        }
    }

    public function updatedRoleId($roleId)
    {
        if (empty($roleId)) {
            $this->selectedPermissions = $this->userAdditionalPermissions;
            $this->rolePermissions = [];

            return;
        }

        // Get the new role's permissions
        $role = Role::with('permissions')->findOrFail($roleId);
        $this->rolePermissions = $role->permissions->pluck('name')->toArray();

        // Combine with user's additional permissions
        $this->selectedPermissions = array_unique(array_merge(
            $this->rolePermissions,
            $this->userAdditionalPermissions
        ));
    }

    public function save()
    {
        if (checkPermission(['admin.users.edit', 'admin.users.create'])) {
            $this->validate();

            $this->handleProfileImageUpload();
            $this->user->is_admin = $this->is_admin ?? false;
            $this->user->user_type = $this->user_type ?? 'admin';

            if (! empty($this->password)) {
                $this->user->password = Hash::make($this->password);
            }

            $role = null;
            if ($this->is_admin) {
                // For admin users, get the admin role that has no tenant_id (admin context)
                $role = Role::where('name', 'Admin')
                    ->whereNull('tenant_id') // Ensure it's an admin role, not tenant admin role
                    ->first();
            } elseif ($this->role_id) {
                $role = Role::find($this->role_id);

                // SECURITY: Ensure this role belongs to admin context (not tenant)
                if ($role && $role->tenant_id !== null) {
                    throw new \Exception('Invalid role assignment: Cannot assign tenant role to admin user');
                }
            }
            $this->user->role_id = $role ? $role->id : null;
            // Fix email verification date storage
            if (! can_send_email('email-confirmation') || $this->isVerified) {
                $this->user->email_verified_at = now();
            } else {
                $this->user->email_verified_at = null;
            }

            $isChanged = $this->user->getOriginal('send_welcome_mail');
            $this->user->send_welcome_mail = $this->sendWelcomeMail;

            $this->user->save();

            $this->send_welcom_mail($isChanged);

            // Sync the role if selected
            if ($role) {
                $this->user->syncRoles([$role]); // Pass role object instead of name
            } else {
                $this->user->roles()->detach();
            }

            // Determine which permissions are additional (not from the role)
            $additionalPermissions = $this->is_admin ? [] : array_diff(
                $this->selectedPermissions,
                $this->rolePermissions
            );

            // Sync the additional permissions
            $this->user->syncPermissions($additionalPermissions);

            $this->notify([
                'type' => 'success',
                'message' => $this->user->wasRecentlyCreated
                    ? t('user_save_successfully')
                    : t('user_update_successfully'),
            ], true);

            $this->redirect(route('admin.users.list'));
        }
    }

    public function send_welcom_mail($isChanged)
    {

        try {
            if (is_smtp_valid() && can_send_email('staff-welcome-mail') && $this->user->send_welcome_mail && $isChanged !== $this->user->send_welcome_mail) {
                $content = render_email_template('staff-welcome-mail', ['userId' => $this->user->id]);
                $subject = get_email_subject('staff-welcome-mail', ['userId' => $this->user->id]);

                Email::to($this->user->email)
                    ->subject($subject)
                    ->content($content)
                    ->send();
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function handleProfileImageUpload()
    {
        try {
            if (isset($this->user->avatar) && is_object($this->user->avatar)) {
                create_storage_link();

                // Delete old profile image if it exists
                if ($this->user->getOriginal('avatar')) {
                    Storage::disk('public')->delete($this->user->getOriginal('avatar'));
                }

                $filename = 'admin_profile_'.time().'.'.$this->user->avatar->getClientOriginalExtension();
                $path = $this->user->avatar->storeAs('profile-images', $filename, 'public');

                $this->user->avatar = $path;
            }
        } catch (\Exception $e) {
            app_log('Profile image upload failed: '.$e->getMessage(), 'error', $e, [
                'user_id' => $this->user->id ?? null,
            ]);

            throw new \Exception('Failed to upload profile image: '.$e->getMessage());
        }
    }

    public function removeProfileImage()
    {
        if (checkPermission('admin.users.edit')) {
            try {
                if ($this->user->avatar) {
                    if (Storage::disk('public')->exists($this->user->avatar)) {
                        Storage::disk('public')->delete($this->user->avatar);
                    }

                    $this->user->avatar = null;
                    $this->user->save();

                    $this->notify(['type' => 'success', 'message' => t('profile_image_removed_successfully')]);
                }
            } catch (\Exception $e) {
                app_log('Profile image removal failed: '.$e->getMessage(), 'error', $e, [
                    'user_id' => $this->user->id ?? null,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('failed_to_remove_profile_image')]);
            }
        } else {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(route('admin.dashboard'));
        }
    }

    public function cancel()
    {
        $this->resetValidation();
        $this->redirect(route('admin.users.list'), navigate: true);
    }

    public function getCountriesProperty()
    {
        return get_country_list();
    }

    public function render()
    {
        return view('livewire.admin.user.user-creator');
    }
}
