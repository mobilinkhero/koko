<?php

namespace App\MergeFields\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class UserMergeFields
{
    public function name(): string
    {
        return 'user-group';
    }

    public function templates(): array
    {
        return [
            'new-tenant-reminder-email-to-admin',
            'transection-created-reminder-mail-to-admin',
            'payment-approved',
            'staff-welcome-mail',
        ];
    }

    public function build(): array
    {
        return [
            [
                'name' => 'First Name',
                'key' => '{first_name}',
            ],
            [
                'name' => 'Last Name',
                'key' => '{last_name}',
            ],
            [
                'name' => 'User Email',
                'key' => '{user_email}',
            ],
            [
                'name' => 'Password Reset Link',
                'key' => '{reset_url}',
            ],
            [
                'name' => 'Email Confirmation Link',
                'key' => '{verification_url}',
            ],
        ];
    }

    public function format(array $context): array
    {
        if (empty($context['userId']) || is_null($context['userId'])) {
            return [];
        }

        $user = User::withoutGlobalScopes()->findOrFail($context['userId']);

        return [
            '{first_name}' => $user->firstname,
            '{last_name}' => $user->lastname,
            '{user_email}' => $user->email,
            '{reset_url}' => $this->generatePasswordResetUrl($context['reset_url'] ?? '', $user->email),
            '{verification_url}' => $context['verification_url'] ?? '',
        ];
    }

    public function generatePasswordResetUrl($token = '', $email = '')
    {
        if ($token == '') {
            return '';
        }
        if (! can_send_email('password-reset')) {
            return redirect()->back();
        }

        return URL::temporarySignedRoute(
            'password.reset',
            Carbon::now()->addMinutes(Config::get('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60)),
            ['token' => $token, 'email' => $email]
        );
    }
}
