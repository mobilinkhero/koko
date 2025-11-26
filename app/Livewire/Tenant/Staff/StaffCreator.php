<?php

namespace App\Livewire\Tenant\Staff;

use App\Models\Tenant\Source;
use App\Models\Tenant\Status;
use App\Models\User;
use App\Rules\PurifiedInput;
use App\Services\FeatureService;
use App\Traits\WithTenantContext;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StaffCreator extends Component
{
    use WithFileUploads;
    use WithTenantContext;

    public User $staff;

    public $staffId;

    public $password;

    public $avatar;

    public $is_admin = false;

    public $roles;

    public $permissions;

    public $selectedPermissions = [];

    public $selectedPermissionNames = [];

    public $role_id;

    public $password_confirmation;

    public $sendWelcomeMail = false;

    public $isVerified = false;

    // New properties to track role permissions vs additional permissions
    public $rolePermissions = [];

    public $userAdditionalPermissions = [];

    public $roleAdditionalPermissions = [];

    protected $featureLimitChecker;

    public $default_language;

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
        $this->bootWithTenantContext();
    }

    protected function rules()
    {
        return [
            'staff.firstname' => ['required', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'staff.lastname' => ['required', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'staff.email' => ['required', 'email', 'max:255', new PurifiedInput(t('sql_injection_error')), 'unique:users,email,'.$this->staff->id],
            'staff.phone' => ['required',  Rule::unique('users', 'phone')
                ->ignore($this->staff->id)
                ->where(function ($query) {
                    return $query->where('tenant_id', tenant_id());
                }), new PurifiedInput(t('sql_injection_error'))],
            'staff.country_id' => 'nullable|integer',
            'staff.address' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error')), 'max:500'],
            'password' => ($this->staff->id) ? ['nullable', Password::defaults(), 'min:8'] : ['required', 'confirmed', Password::defaults(), 'min:8'],
            'role_id' => [$this->is_admin ? 'nullable' : 'required', 'integer', 'exists:roles,id'],
            'staff.avatar' => ['nullable'],
            'staff.default_language' => ['nullable'],
        ];
    }

    public function mount()
    {
        if (! checkPermission(['tenant.staff.edit', 'tenant.staff.create'])) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        // Force reload of permissions to ensure we have the latest
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->staffId = request()->route('staffId');
        $this->staff = $this->staffId ? User::findOrFail($this->staffId) : new User;
        $this->is_admin = $this->staff->is_admin;
        $this->sendWelcomeMail = $this->staff->send_welcome_mail ?? false;
        $this->isVerified = ! empty($this->staff->email_verified_at) ? true : false;
        $this->staff->tenant_id = tenant_id();

        // Set the current tenant context for Spatie team support
        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant_id());

        // Load roles for the current tenant
        $this->roles = Role::where('tenant_id', tenant_id())->get();

        // Load all tenant permissions and group them
        $allPermissions = Permission::where(function ($query) {
            $query->where('scope', 'tenant')
                ->orWhere('name', 'like', 'tenant.%');
        })
            ->orderBy('name')
            ->get();

        $this->permissions = $allPermissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);

            return count($parts) > 1 ? $parts[1] : 'general';
        })->toArray();

        // If editing an existing staff member
        if ($this->staff->exists) {
            $this->role_id = ($this->staffId && ! empty($this->staff->role_id))
                ? optional($this->staff->roles->first())->id
                : null;

            if ($this->role_id) {
                $this->loadPermissions($this->role_id);
            }
        }
    }

    private function loadPermissions($roleId)
    {
        // Clear previous permissions
        $this->rolePermissions = [];
        $this->userAdditionalPermissions = [];
        $this->selectedPermissions = [];

        if (! empty($roleId) && ! $this->is_admin) {
            $role = Role::findOrFail($roleId);

            // Get role permissions
            $this->rolePermissions = $role->permissions->pluck('name')->toArray();

            // Check if there are saved additional permissions for this role
            if (isset($this->roleAdditionalPermissions[$roleId])) {
                $this->userAdditionalPermissions = $this->roleAdditionalPermissions[$roleId];
            } else {
                // Calculate user-specific permissions not included in the role
                $userPermissions = $this->staff->permissions->pluck('name')->toArray();
                $this->userAdditionalPermissions = array_diff($userPermissions, $this->rolePermissions);
            }

            // Combine role permissions and additional permissions
            $this->selectedPermissions = array_merge(
                $this->rolePermissions,
                $this->userAdditionalPermissions
            );

            $this->selectedPermissionNames = $this->selectedPermissions;
        }
    }

    public function updatedRoleId($roleId)
    {
        // Save current additional permissions for the existing role
        if ($this->role_id) {
            if ($this->staff->role_id == $this->role_id) {
                $this->roleAdditionalPermissions[$this->role_id] = array_diff(
                    $this->selectedPermissions,
                    $this->rolePermissions
                );
            } else {
                $this->roleAdditionalPermissions = [];
            }
        }

        $this->loadPermissions($roleId);

        // Restore additional permissions if available for the selected role
        $this->userAdditionalPermissions = $this->roleAdditionalPermissions[$roleId] ?? [];

        $this->selectedPermissions = array_merge(
            $this->rolePermissions,
            $this->userAdditionalPermissions
        );

        $this->selectedPermissionNames = $this->selectedPermissions;
    }

    public function save()
    {
        if (checkPermission(['tenant.staff.create', 'tenant.staff.edit'])) {
            $this->validate();
            $this->handleProfileImageUpload();

            // Set the current tenant context for Spatie team support
            app(PermissionRegistrar::class)->setPermissionsTeamId(tenant_id());

            // Set basic staff properties
            $this->staff->is_admin = $this->is_admin ? 1 : 0;
            $this->staff->user_type = 'tenant';
            $this->staff->tenant_id = tenant_id();
            $this->staff->default_language = ! empty($this->default_language) ? $this->default_language : null;

            if ($this->is_admin) {
                $this->staff->role_id = null;
            } elseif ($this->role_id) {
                $this->staff->role_id = $this->role_id;
            }

            // Handle password
            if (! empty($this->password)) {
                $this->staff->password = Hash::make($this->password);
            }

            // Fix email verification date storage
            if (! can_send_email('tenant-email-confirmation', 'tenant_email_templates') || $this->isVerified) {
                $this->staff->email_verified_at = now();
            } else {
                $this->staff->email_verified_at = null;
            }

            $isChanged = $this->staff->getOriginal('send_welcome_mail');
            $this->staff->send_welcome_mail = $this->sendWelcomeMail;

            $isNewStaff = ! $this->staff->exists;

            // For new staff records, check if creating one more would exceed the limit
            if ($isNewStaff) {
                $limit = $this->featureLimitChecker->getLimit('staff');

                // Skip limit check if unlimited (-1) or no limit set (null)
                if ($limit !== null && $limit !== -1) {
                    $currentCount = User::where('tenant_id', tenant_id())->count();

                    if ($currentCount >= $limit) {
                        $this->notify([
                            'type' => 'warning',
                            'message' => t('staff_limit_reached_upgrade_plan'),
                        ]);

                        return;
                    }
                }
            }            $this->staff->save();

            // Track staff usage after successful creation for all staff (including admins)
            if ($isNewStaff) {
                $this->featureLimitChecker->trackUsage('staff');
            }

            $this->send_welcome_mail_to_tenant($isChanged);

            $tenantId = tenant_id();

            // Assign role using Spatie's team-aware methods
            if ($this->is_admin) {
                // For admin users, find the admin role specifically for this tenant
                $adminRole = Role::where('name', 'Admin')
                    ->where('tenant_id', $tenantId)
                    ->first();

                if ($adminRole) {
                    // Use the role model directly instead of just the name
                    $this->staff->syncRoles([$adminRole]);
                } else {
                    $this->staff->syncRoles([]);
                }
            } elseif ($this->role_id) {
                $role = Role::findOrFail($this->role_id);

                // Verify this role belongs to the current tenant
                if ($role->tenant_id == $tenantId) {
                    // Use the role model directly instead of just the name
                    $this->staff->syncRoles([$role]);
                } else {
                    // This is a security issue - role doesn't belong to current tenant
                    throw new \Exception('Invalid role assignment: Role does not belong to current tenant');
                }
            } else {
                $this->staff->syncRoles([]);
            }

            // Calculate additional permissions (those not from the role)
            $additionalPermissions = array_diff(
                $this->selectedPermissions,
                $this->rolePermissions
            );

            // Sync only the additional permissions
            $this->staff->syncPermissions($additionalPermissions);

            $this->notify([
                'type' => 'success',
                'message' => $this->staff->wasRecentlyCreated
                    ? t('staff_created_successfully')
                    : t('staff_update_successfully'),
            ], true);

            return $this->redirect(tenant_route('tenant.staff.list'));
        }
    }

    public function send_welcome_mail_to_tenant($isChanged)
    {
        try {
            if (is_smtp_valid() && can_send_email('tenants-welcome-mail', 'tenant_email_templates') && $this->staff->send_welcome_mail && $isChanged !== $this->staff->send_welcome_mail) {
                $content = render_email_template('tenants-welcome-mail', ['userId' => auth()->id(), 'tenantId' => tenant_id()], 'tenant_email_templates');
                $subject = get_email_subject('tenants-welcome-mail', ['userId' => auth()->id(), 'tenantId' => tenant_id()], 'tenant_email_templates');

                $result = Email::to($this->staff->email)
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
            if (isset($this->staff->avatar) && is_object($this->staff->avatar)) {
                create_storage_link();

                if ($this->staff->getOriginal('avatar')) {
                    Storage::disk('public')->delete($this->staff->getOriginal('avatar'));
                }

                $filename = 'profile_'.time().'.'.$this->staff->avatar->getClientOriginalExtension();
                $path = $this->staff->avatar->storeAs('tenant/'.tenant_id().'/profile-images', $filename, 'public');

                $this->staff->avatar = $path;
            }
        } catch (\Exception $e) {
            app_log('Profile image upload failed: '.$e->getMessage(), 'error', $e, [
                'user_id' => $this->staff->id ?? null,
            ]);

            throw new \Exception(t('failed_upload_profile_image ').$e->getMessage());
        }
    }

    public function removeProfileImage()
    {
        try {
            if ($this->staff->avatar) {
                if (Storage::disk('public')->exists($this->staff->avatar)) {
                    Storage::disk('public')->delete($this->staff->avatar);
                }

                $this->staff->avatar = null;
                $this->staff->save();

                $this->notify(['type' => 'success', 'message' => t('profile_image_removed_successfully')]);
            }
        } catch (\Exception $e) {
            app_log('Profile image removal failed: '.$e->getMessage(), 'error', $e, [
                'user_id' => $this->staff->id ?? null,
            ]);

            $this->notify(['type' => 'danger', 'message' => t('failed_to_remove_profile_image')]);
        }
    }

    public function cancel()
    {
        $this->resetValidation();

        return redirect()->to(tenant_route('tenant.staff.list'));
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('staff', User::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('staff', User::class);
    }

    public function getStatusesProperty()
    {
        return Status::all();
    }

    public function getSourcesProperty()
    {
        return Source::all();
    }

    public function getCountriesProperty()
    {
        return get_country_list();
    }

    public function render()
    {
        return view('livewire.tenant.staff.staff-creator');
    }
}
