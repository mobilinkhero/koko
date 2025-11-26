<?php

namespace App\MergeFields\Tenant;

use Illuminate\Support\Facades\Storage;

class OtherMergeFields
{
    public function name(): string
    {
        return 'tenant-other-group';
    }

    public function templates(): array
    {
        return [
            'tenant-email-confirmation',
            'tenants-welcome-mail',
            'tenant-password-reset',
            'tenant-new-contact-assigned',
        ];
    }

    public function build(): array
    {
        return [
            [
                'name' => 'Company Name',
                'key' => '{company_name}',
            ],
            [
                'name' => 'Company Email',
                'key' => '{company_email}',
            ],
            [
                'name' => 'Dark Logo',
                'key' => '{dark_logo}',
                'absent' => [
                    'password-reset',
                ],
            ],
            [
                'name' => 'Light Logo',
                'key' => '{light_logo}',
                'absent' => [
                    'password-reset',
                ],
            ],
            [
                'name' => 'Base Url',
                'key' => '{base_url}',
            ],
        ];
    }

    public function format(): array
    {
        $settings = get_batch_settings([
            'system.site_name',
            'email.sender_email',
            'theme.dark_logo',
            'theme.site_logo',
        ]);

        return [
            '{company_name}' => $settings['system.site_name'] ?? config('app.name'),
            '{dark_logo}' => $settings['theme.dark_logo'] && Storage::disk('public')->exists($settings['theme.dark_logo'])
                ? asset('storage/'.$settings['theme.dark_logo'])
                : asset('/img/dark_logo.png'),
            '{light_logo}' => $settings['theme.site_logo'] && Storage::disk('public')->exists($settings['theme.site_logo'])
                ? asset('storage/'.$settings['theme.site_logo'])
                : asset('/img/light_logo.png'),
            '{base_url}' => url('/'),
        ];
    }
}
