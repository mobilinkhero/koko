<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Repositories\SubscriptionRepository;
use App\Services\SubscriptionCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected SubscriptionRepository $subscriptionRepository,
    ) {}

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
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
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => [t('email_recaptcha_failed')],
                ]);
            }
        }

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && $user->active == 0) {
            session()->flash('error', t('user_deactivated_message_in_login'));

            return back();
        }

        $settings = get_settings_by_group('tenant');
        if ($user && ($user->user_type == 'tenant') && $user->is_admin == 1) {
            if (! $settings->isVerificationEnabled && is_null($user->email_verified_at)) {
                $user->email_verified_at = now();
                $user->save();
            }
        }

        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $remember = $request->has('remember');

        do_action('auth.before_attempt', $request->only('email', 'password'));

        if (Auth::attempt($request->only('email', 'password'), $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Automatically logout from other devices on successful login
            if ($user->user_type === 'tenant') {
                try {
                    Auth::logoutOtherDevices($request->password);
                } catch (\Exception $e) {
                    // Log the error but don't fail the login process
                    Log::warning('Failed to logout other devices on login', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            do_action('user.before_login', $user);

            $locale = Session::get('locale', config('app.locale'));

            $user->last_login_at = now();
            $user->save();

            do_action('user.after_login', $user);

            $defaultLanguage = $user->default_language;

            if ($user->user_type === 'admin') {
                return $this->handleAdminAuthentication($locale, $defaultLanguage);
            } elseif ($user->user_type === 'tenant') {
                $tenant = Tenant::findOrFail($user->tenant_id);

                Tenant::forgetCurrent();
                $tenant->makeCurrent();
                session([
                    'current_tenant_id' => $tenant->id,
                    'current_user' => $user,
                ]);

                // Clear intended URL if it belongs to current host
                if ($request->session()->has('url.intended')) {
                    $intended = $request->session()->get('url.intended');
                    if (str_contains($intended, $request->getHost())) {
                        $request->session()->forget('url.intended');
                    }
                }
                $this->handleLanguageSetup($tenant, $locale, $defaultLanguage);
                $plan_id = $request->input('plan_id');
                if ($plan_id) {
                    session(['plan_id' => $plan_id]);
                }

                // Check for expired trial subscriptions
                $this->checkTrialStatus($tenant->id);

                // Handle plan selection
                if ($plan_id) {
                    $plan = Plan::find($plan_id);
                    if ($plan) {
                        if ($plan->price == 0) {
                            // FREE PLAN SELECTED
                            // Check if tenant has already used trial
                            $previousTrial = $this->subscriptionRepository->previousTrialExists(tenant_id());
                            if ($previousTrial) {
                                session()->flash('notification', [
                                    'type' => 'warning',
                                    'message' => t('you_have_already_used_free_trial'),
                                ]);

                                return redirect()->to(tenant_route('tenant.subscription'));
                            }

                            // Create new trial
                            $subscription = $this->subscriptionRepository->createTrial(tenant_id(), $plan->id, $plan->trial_days);
                            session()->flash('notification', [
                                'type' => 'info',
                                'message' => t('you_have_started_a_trial').' '.$plan->trial_days.' '.t('days_trial'),
                            ]);

                            return redirect()->to(tenant_route('tenant.dashboard'));
                        } else {
                            // Paid plan selected
                            return redirect()->to(tenant_route('tenant.billing', [
                                'plan_id' => $plan_id,
                            ]));
                        }
                    }
                }

                // Check for active trial
                $trialSubscription = $this->subscriptionRepository->getTrialSubscription(tenant_id());
                if ($trialSubscription) {
                    if ($trialSubscription->trial_ends_at && Carbon::now()->gt($trialSubscription->trial_ends_at)) {
                        // Trial expired, update status
                        $trialSubscription->status = Subscription::STATUS_ENDED;
                        $trialSubscription->ended_at = Carbon::now();
                        $trialSubscription->save();

                        // Add subscription log
                        $trialSubscription->addLog('ended', [
                            'plan' => $trialSubscription->plan->name,
                            'end_date' => Carbon::now()->format('Y-m-d H:i:s'),
                            'reason' => 'Trial period expired',
                        ]);

                        SubscriptionCache::clearCache(tenant_id());

                        session()->flash('notification', [
                            'type' => 'warning',
                            'message' => t('subscribe_to_plan'),
                        ]);

                        return redirect()->to(tenant_route('tenant.subscription'));
                    }

                    // Trial still active
                    $planName = $trialSubscription->plan->name ?? 'Trial Plan';
                    $daysLeft = round(Carbon::now()->diffInDays($trialSubscription->trial_ends_at, false));

                    session()->flash('notification', [
                        'type' => 'info',
                        'message' => t('you_are_currently_on_a_trial_plan')." {$daysLeft} ".t('days_remaining'),
                    ]);

                    return redirect()->to(tenant_route('tenant.dashboard'));
                }

                $transactionPendingStatus = Transaction::whereHas('invoice', function ($query) {
                    $query->where('tenant_id', tenant_id());
                })
                    ->where('status', Transaction::STATUS_PENDING)
                    ->latest()
                    ->first();

                $transactionFailedStatus = Transaction::whereHas('invoice', function ($query) {
                    $query->where('tenant_id', tenant_id());
                })
                    ->where('status', Transaction::STATUS_FAILED)
                    ->latest()
                    ->first();

                if ($transactionPendingStatus) {
                    session()->flash('notification', [
                        'type' => 'warning',
                        'message' => t('wait_for_administrator_approval'),
                    ]);

                    return redirect()->to(tenant_route('tenant.subscription.pending'));
                }
                if ($transactionFailedStatus) {
                    // Check if there's a successful transaction after this failed one
                    $successfulTransactionAfterFailed = Transaction::whereHas('invoice', function ($query) {
                        $query->where('tenant_id', tenant_id());
                    })
                        ->where('status', Transaction::STATUS_SUCCESS) // or whatever your success status is
                        ->where('created_at', '>', $transactionFailedStatus->created_at)
                        ->exists();

                    // Only show failed message if no successful transaction came after
                    if (! $successfulTransactionAfterFailed) {
                        session()->flash('notification', [
                            'type' => 'danger',
                            'message' => t('payment_reject_message'),
                        ]);

                        return redirect()->to(tenant_route('tenant.subscriptions'));
                    }
                }
                // If plan is in request
                if ($plan_id) {
                    return redirect()->to(tenant_route('tenant.billing', [
                        'plan_id' => $plan_id,
                    ]));
                }

                // Check for active subscription
                $hasActiveSubscription = Subscription::where('tenant_id', $tenant->id)
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->exists();

                if (! $hasActiveSubscription) {
                    return redirect()->to(tenant_route('tenant.subscription'));
                }

                return redirect()->to(tenant_route('tenant.dashboard'));
            }
        }

        do_action('auth.login_failed', $request->only('email', 'password'));

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    /**
     * Check trial status and update if expired
     */
    protected function checkTrialStatus($tenantId)
    {
        $trialSubscription = Subscription::where('tenant_id', $tenantId)
            ->where('status', Subscription::STATUS_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->latest()
            ->first();

        if ($trialSubscription && $trialSubscription->trial_ends_at && Carbon::now()->gt($trialSubscription->trial_ends_at)) {
            // Update subscription status
            $trialSubscription->status = Subscription::STATUS_ENDED;
            $trialSubscription->ended_at = Carbon::now();
            $trialSubscription->save();

            SubscriptionCache::clearCache(tenant_id());

            // Add subscription log
            $trialSubscription->addLog('ended', [
                'plan' => $trialSubscription->plan->name,
                'end_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'reason' => 'Trial period expired on login',
            ]);

            return true;
        }

        return false;
    }

    public function login_as(Request $request, $id): RedirectResponse
    {
        $admin_id = Auth::id();

        // Store admin ID in session for back_to_admin functionality
        session(['admin_id' => $admin_id]);

        // Find the tenant user
        $tenantUser = \App\Models\User::findOrFail($id);

        // Get the tenant
        $tenant = Tenant::findOrFail($tenantUser->tenant_id);

        // Preserve admin_id before invalidating session
        $admin_id = session('admin_id');

        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        // Restore admin_id after session regeneration
        session(['admin_id' => $admin_id]);

        // Make the tenant current
        Tenant::forgetCurrent();
        $tenant->makeCurrent();

        // Store tenant ID in session
        session([
            'current_tenant_id' => $tenant->id,
            'current_user' => $tenantUser,
        ]);
        // Login as the tenant user
        Auth::loginUsingId($id, $remember = true);

        $locale = ! empty($tenantUser->default_language) ? $tenantUser->default_language : get_tenant_setting_from_db('system', 'active_language');
        Session::put('locale', $locale);
        App::setLocale($locale);

        session()->flash('notification', [
            'type' => 'success',
            'message' => t('login_as_tenant_successfully'),
        ]);

        // Now that tenant context is set up, we can use tenant_route with the proper subdomain
        return redirect()->to(tenant_route('tenant.dashboard', [
            'subdomain' => $tenant->subdomain,
        ]));
    }

    /**
     * Handle admin user authentication.
     */
    private function handleAdminAuthentication(string $locale, ?string $defaultLanguage): RedirectResponse
    {
        Cache::forget("translations.{$locale}");
        $settings = get_batch_settings(['system.active_language']);
        $locale = ! empty($defaultLanguage) ? $defaultLanguage : $settings['system.active_language'];
        Session::put('locale', $locale);
        App::setLocale($locale);

        return redirect()->route('admin.dashboard');
    }

    public function back_to_admin(Request $request): RedirectResponse
    {
        // Get and validate admin ID from session
        $admin_id = session()->pull('admin_id');
        if (! $admin_id) {
            return redirect()->route('login')->with('error', 'Admin session expired');
        }

        // Get current tenant before logout for cleanup
        $tenant = Tenant::current();

        // Log out current user
        Auth::guard('web')->logout();

        // Clear all tenant-related session data
        if ($tenant) {
            // Clear tenant context from database and cache
            Tenant::forgetCurrent();
            Cache::forget("tenant:{$tenant->subdomain}");

            // Clear tenant-specific session data
            session()->forget([
                'current_tenant_id',
                'tenant_settings',
            ]);

            // Clear any cached tenant data
            Cache::forget("tenant_{$tenant->id}");
            Cache::forget("tenant_subdomain_{$tenant->subdomain}");
        }

        // Store admin ID temporarily
        $temp_admin_id = $admin_id;

        // Complete session invalidation
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Restore only the admin ID
        session(['admin_id' => $temp_admin_id]);

        // Log back in as admin
        Auth::loginUsingId($admin_id, true);

        // Ensure admin user was found and logged in
        if (! Auth::check() || Auth::user()->user_type !== 'admin') {
            return redirect()->route('login')->with('error', 'Could not restore admin session');
        }

        // Set the language based on admin user's preference or system default
        $adminUser = Auth::user();
        $settings = get_batch_settings(['system.active_language']);
        $locale = ! empty($adminUser->default_language) ? $adminUser->default_language : $settings['system.active_language'];
        Session::put('locale', $locale);
        App::setLocale($locale);

        // Set success message
        session()->flash('notification', [
            'type' => 'success',
            'message' => t('successfully_returned_to_admin_panel'),
        ]);

        // Redirect to admin dashboard
        return redirect()->route('admin.tenants.list');
    }

    /**
     * Handle language setup for tenant.
     */
    private function handleLanguageSetup(Tenant $tenant, string $locale, ?string $defaultLanguage): void
    {
        Cache::forget("{$tenant->id}_tenant_languages");
        Cache::forget("translations.{$tenant->id}_tenant_{$locale}");

        $locale = ! empty($defaultLanguage) ? $defaultLanguage : get_tenant_setting_from_db('system', 'active_language');
        Session::put('locale', $locale);
        App::setLocale($locale);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            do_action('user.before_logout', $user);
        }

        Auth::guard('web')->logout();

        if ($user) {
            do_action('user.after_logout', $user);
        }

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
