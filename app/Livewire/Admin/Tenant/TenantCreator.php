<?php

namespace App\Livewire\Admin\Tenant;

use App\Events\Tenant\TenantCreated;
use App\Models\Language;
use App\Models\Tenant;
use App\Models\User;
use App\Rules\PurifiedInput;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class TenantCreator extends Component
{
    public Tenant $tenant;

    public User $user;

    public $tenantId;

    public $password_confirmation;

    public $password;

    public $subdomain;

    public $newTenantsCount = 0;

    // Define what "new" means (in hours)
    protected $newTenantThreshold = 24;

    public Language $language;

    // Listen for tenant creation events
    protected $listeners = ['tenantCreated' => 'updateCount'];

    protected function rules()
    {
        return [
            'user.firstname' => ['required', 'string', new PurifiedInput(t('sql_injection_error'))],
            'user.lastname' => ['required', 'string', new PurifiedInput(t('sql_injection_error'))],
            'tenant.company_name' => ['required', 'string', new PurifiedInput(t('sql_injection_error'))],
            'tenant.subdomain' => ['required', 'string', 'unique:tenants,subdomain,'.$this->tenant->id, new PurifiedInput(t('sql_injection_error'))],
            'user.email' => ['required', 'email', 'unique:users,email,'.$this->user->id],
            'password' => $this->tenant->id ? ['nullable', Password::defaults()] : ['required', 'confirmed', Password::defaults(), 'min:8'],
            'tenant.country_id' => ['nullable', 'integer'],
            'user.phone' => ['required'],
            'tenant.address' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
            'tenant.features_config' => ['nullable', 'json'],
            'tenant.custom_colors' => ['nullable', 'json'],
            'tenant.timezone' => 'nullable',
            'user.default_language' => 'nullable',
        ];
    }

    public function mount()
    {
        if (! checkPermission(['admin.tenants.create', 'admin.tenants.edit'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $this->language = new Language;
        $this->tenantId = request()->route('tenantId');
        $this->tenant = $this->tenantId ? Tenant::findOrFail($this->tenantId) : new Tenant;
        $this->user = $this->tenant->exists
            ? User::where('tenant_id', $this->tenant->id)->first()
            : new User;
        // Calculate new tenants only if not on the tenant list page
        if (! request()->routeIs('admin.tenants.list')) {
            $this->updateCount();
        } else {
            // If on tenant list page, mark all as viewed by updating the session timestamp
            Session::put('last_viewed_tenants', Carbon::now()->toDateTimeString());
            $this->newTenantsCount = 0;
        }
    }

    public function updateCount()
    {
        // Get the last viewed timestamp from session
        $lastViewedTimestamp = Session::get('last_viewed_tenants');

        // If we have a last viewed timestamp, only count tenants created after that
        // Otherwise fall back to the 24-hour threshold
        $thresholdTime = $lastViewedTimestamp
            ? Carbon::parse($lastViewedTimestamp)
            : Carbon::now()->subHours($this->newTenantThreshold);

        $this->newTenantsCount = Tenant::where('created_at', '>=', $thresholdTime)->count();
    }

    public function save()
    {
        if (checkPermission(['admin.tenants.create', 'admin.tenants.edit'])) {
            $this->validate();
            $tenantSettings = get_batch_settings(['tenant.set_default_tenant_language']);
            $isNewTenant = ! $this->tenant->exists;
            $isNewUser = ! $this->user->exists;
            $this->subdomain = $this->tenant->subdomain;
            $this->tenant->save();
            $this->user->tenant_id = $this->tenant->id;
            $this->user->user_type = 'tenant';
            $this->user->is_admin = true;
            $this->user->email_verified_at = get_super_admin_current_time();
            $this->user->default_language = $tenantSettings['tenant.set_default_tenant_language'] ?? 'en';
            $this->user->password = Hash::make($this->password);
            $this->user->save();

            // If this is a new tenant, dispatch the event to update counters
            if ($isNewTenant) {
                event(new TenantCreated($this->tenant));
            }
            // Assign language to tenant : Over

            $this->notify([
                'type' => 'success',
                'message' => $isNewTenant || $isNewUser
                    ? t('tenant_created_successfully')
                    : t('tenant_update_successfully'),
            ]);

            return $this->redirect(route('admin.tenants.list'));
        }
    }

    public function getCountriesProperty()
    {
        return get_country_list();
    }

    public function render()
    {
        $subdomains = Tenant::pluck('subdomain')->toArray();

        return view('livewire.admin.tenant.tenant-creator', ['subdomains' => $subdomains]);
    }
}
