<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Corbital\LaravelEmails\Facades\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function forgot(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->back()->withErrors(['email' => t('user_not_found')]);
        }

        if (! $user->email_verified_at) {
            return redirect()->back()->withErrors(['email' => t('user_not_verified')]);
        }

        $resetUrl = $this->generatePasswordResetUrl($user);

        if ($user->user_type == 'admin') {
            $content = render_email_template('password-reset', [
                'first_name' => $user->firstname,
                'last_name' => $user->lastname,
                'reset_url' => $resetUrl,
                'email' => $user->email,
                'app_name' => config('app.name'),
            ]);
        } else {
            $content = render_email_template('tenant-password-reset', [
                'first_name' => $user->firstname,
                'last_name' => $user->lastname,
                'reset_url' => $resetUrl,
                'email' => $user->email,
                'app_name' => config('app.name'),
            ]);
        }

        try {
            if (is_smtp_valid()) {
                Email::to($user->email)
                    ->subject('Reset Password Notification')
                    ->content($content)
                    ->send();

                $user->last_password_change = now();
                $user->save();

                return redirect()->back()->with('status', t('password_reset_link_sent'));
            }

            return redirect()->back()->with('status', t('email_config_is_required'));
        } catch (\Exception $e) {
            return false;
        }
    }

    public function verified()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->back()->with('error', t('user_not_found'));
        }

        if ($user->email_verified_at) {
            if ($user->user_type == 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        try {
            $verificationUrl = $this->generateEmailVerificationUrl($user);

            if ($user->user_type == 'admin') {
                $content = render_email_template('email-confirmation', ['userId' => $user->id, 'verification_url' => $verificationUrl]);
                $subject = get_email_subject('email-confirmation', ['userId' => $user->id, 'verification_url' => $verificationUrl]);
            } else {
                $content = render_email_template('tenant-email-confirmation', ['userId' => $user->id, 'verification_url' => $verificationUrl, 'tenantId' => $user->tenant_id], 'tenant_email_templates');
                $subject = get_email_subject('tenant-email-confirmation', ['userId' => $user->id, 'verification_url' => $verificationUrl, 'tenantId' => $user->tenant_id], 'tenant_email_templates');
            }

            if (is_smtp_valid()) {
                $status = Email::to($user->email)
                    ->subject($subject)
                    ->content($content)
                    ->send();

                return redirect()->back()->with('status', t('verification_link_sent'));

            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'File to send verification link');
        }

    }

    public function generatePasswordResetUrl($user)
    {
        if (! can_send_email('password-reset')) {
            return redirect()->back();
        }

        $token = Password::createToken($user);

        return URL::temporarySignedRoute(
            'password.reset',
            Carbon::now()->addMinutes(Config::get('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60)),
            ['token' => $token, 'email' => $user->email]
        );
    }

    public function generateEmailVerificationUrl($user)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
        );
    }
}
