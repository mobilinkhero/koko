<?php

namespace Corbital\LaravelEmails\Database\Seeders;

use Corbital\LaravelEmails\Models\EmailLayout;
use Corbital\LaravelEmails\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultLayout = EmailLayout::where('slug', 'default')->first();
        $minimalLayout = EmailLayout::where('slug', 'minimal')->first();

        // Welcome Email
        if (! EmailTemplate::where('slug', 'welcome')->exists()) {
            EmailTemplate::create([
                'name' => 'Welcome Email',
                'slug' => 'welcome',
                'subject' => 'Welcome to {{app_name}}',
                'content' => '<h1>Welcome {{name}}!</h1><p>Thank you for joining us.</p>',
                'is_active' => true,
                'is_system' => true,
                'category' => 'system',
                'layout_id' => $defaultLayout->id ?? null,
                'use_layout' => true,
            ]);
        }

        // Password Reset
        if (! EmailTemplate::where('slug', 'password-reset')->exists()) {
            EmailTemplate::create([
                'name' => 'Password Reset',
                'slug' => 'password-reset',
                'subject' => 'Reset Your {{app_name}} Password',
                'content' => '<h1>Hello {{name}},</h1><p>You recently requested to reset your password. Click the button below to proceed:</p><p><a href="{{reset_url}}" style="display: inline-block; padding: 10px 20px; background: #4a6cf7; color: white; text-decoration: none; border-radius: 4px;">Reset Password</a></p><p>If you didn\'t request this, please ignore this email.</p>',
                'is_active' => true,
                'is_system' => true,
                'category' => 'authentication',
                'layout_id' => $defaultLayout->id ?? null,
                'use_layout' => true,
            ]);
        }

        // Notification
        if (! EmailTemplate::where('slug', 'notification')->exists()) {
            EmailTemplate::create([
                'name' => 'Simple Notification',
                'slug' => 'notification',
                'subject' => 'Notification from {{app_name}}',
                'content' => '<h2>{{notification_title}}</h2><p>{{notification_message}}</p>',
                'is_active' => true,
                'is_system' => true,
                'category' => 'notifications',
                'layout_id' => $minimalLayout->id ?? null,
                'use_layout' => true,
            ]);
        }

        // Raw HTML
        if (! EmailTemplate::where('slug', 'raw-html')->exists()) {
            EmailTemplate::create([
                'name' => 'Raw HTML Email',
                'slug' => 'raw-html',
                'subject' => 'HTML Email from {{app_name}}',
                'content' => '<!DOCTYPE html><html><head><title>{{email_title}}</title></head><body><div style="max-width: 600px; margin: 0 auto;">{{email_content}}</div></body></html>',
                'is_active' => true,
                'is_system' => true,
                'category' => 'system',
                'use_layout' => false,
            ]);
        }
    }
}
