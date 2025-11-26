<?php

namespace App\Http\Controllers\Auth;

use App\Events\NewRegistered;
use App\Events\Tenant\TenantCreated;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Rules\PurifiedInput;
use App\Rules\ValidSubdomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request)
    {
        // Check if registration is enabled
        $settings = get_settings_by_group('tenant');

        if (! $settings->isRegistrationEnabled) {
            return view('layouts.custom.registration-closed');
        }

        $subdomains = Tenant::pluck('subdomain')->toArray();
        if (! $request->filled('plan_id')) {

            $plans = Plan::where('is_active', true)
                ->orderBy('price', 'asc')
                ->get();

            return view('auth.register', [
                'plan' => null,
                'plans' => $plans,
                'subdomains' => $subdomains,
            ]);
        }

        $plan = Plan::findOrFail($request->plan_id);

        // If user is already logged in
        if (auth()->check()) {
            return redirect()->tenant_route('tenant.dashboard');
        }

        session()->flash('notification', [
            'type' => 'info',
            'message' => t('you_have_selected_the_plan').' '.$plan->name,
        ]);

        return view('auth.register', ['plan' => $plan, 'subdomains' => $subdomains]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $settings = get_settings_by_group('tenant');
        if (! $settings->isRegistrationEnabled) {
            return redirect()->view('components.registration-closed');
        }

        $reCaptchSettings = get_batch_settings([
            're-captcha.isReCaptchaEnable',
            're-captcha.secret_key',
        ]);

        if ($reCaptchSettings['re-captcha.isReCaptchaEnable']) {

            $recaptchaResponse = $request->input('g-recaptcha-response');
            $secretKey = $reCaptchSettings['re-captcha.secret_key'];

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip(),
            ]);

            $recaptchaResult = $response->json();

            if (! $recaptchaResult['success'] || $recaptchaResult['score'] < 0.5) {
                session()->flash('notification', ['type' => 'danger', 'message' => t('email_recaptcha_failed')]);
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => [t('email_recaptcha_failed')],
                ]);

            }
        }

        $plan = Plan::find($request->plan_id);

        $validation_data = [
            [
                'firstname' => ['required', 'string', 'max:50', new PurifiedInput(t('sql_injection_error'))],
                'lastname' => ['required', 'string', 'max:50', new PurifiedInput(t('sql_injection_error'))],
                'email' => ['required', 'string', new PurifiedInput(t('sql_injection_error')), 'lowercase', 'email', 'max:50', 'unique:'.User::class],
                'phone' => ['required', 'unique:'.User::class],
                'address' => ['nullable', 'string', 'max:400', new PurifiedInput(t('sql_injection_error'))],
                'country_id' => ['nullable'],
                'company_name' => ['required', 'string', 'max:50', new PurifiedInput(t('sql_injection_error'))],
                'subdomain' => ['required', 'string', 'max:15', 'unique:tenants,subdomain,'.Tenant::class, new PurifiedInput(t('sql_injection_error')), new ValidSubdomain],
                'plan_id' => ['exists:plans,id'],
                'password' => ['required', 'min:8'],
                'password_confirmation' => ['required', 'same:password'],
            ],
            [
                'subdomain.required' => t('tenant_name_is_required'),
                'subdomain.unique' => t('tenant_name_already_taken'),
            ],
        ];

        $added_data = apply_filters('tenant_validation_data', $validation_data);

        $validated = $request->validate(
            $added_data[0],
            $added_data[1]
        );

        $data = [
            'tenant_data' => [
                'company_name' => $validated['company_name'],
                'subdomain' => $validated['subdomain'],
                'status' => 'active',
                'address' => $validated['address'] ?? null,
                'country_id' => $validated['country_id'] ?? null,
            ],
            'validated' => $request,
        ];

        // Apply filter with single argument
        $new_data = apply_filters('tenant_registration_data', $data);

        // Create tenant
        $tenant = Tenant::create($new_data['tenant_data']);

        do_action('tenant_registered', $tenant, $validated);

        $tenantSettings = get_batch_settings(['tenant.set_default_tenant_language']);
        // Create User
        $user = User::create([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'tenant_id' => $tenant->id,
            'user_type' => 'tenant',
            'is_admin' => true,
            'default_language' => $tenantSettings['tenant.set_default_tenant_language'] ?? 'en',
            'password' => Hash::make($validated['password']),
        ]);

        event(new TenantCreated($tenant));
        event(new NewRegistered($user));
        session()->flash('notification', [
            'type' => 'success',
            'message' => t('registration_complete'),
        ]);

        return redirect()->route('login', ['plan_id' => $request->plan_id]);
    }
}
