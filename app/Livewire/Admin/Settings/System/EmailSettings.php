<?php

namespace App\Livewire\Admin\Settings\System;

use App\Facades\AdminCache;
use App\Rules\PurifiedInput;
use App\Traits\SendMailTrait;
use Corbital\LaravelEmails\Facades\Email;
use Livewire\Component;

class EmailSettings extends Component
{
    use SendMailTrait;

    public ?string $mailer = '';

    public $smtp_port = 0;

    public ?string $smtp_username = '';

    public ?string $smtp_password = '';

    public ?string $smtp_encryption = '';

    public ?string $sender_name = '';

    public ?string $sender_email = '';

    public ?string $sender_mail_path = '';

    public ?string $smtp_host = '';

    public ?string $test_mail;

    protected function rules()
    {
        return [
            'mailer' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'smtp_port' => ['nullable', 'numeric'],
            'smtp_username' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'smtp_password' => ['nullable', 'string'],
            'smtp_encryption' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'sender_name' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'sender_email' => 'nullable|email|max:255',
            'smtp_host' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_settings_by_group('email');

        $this->mailer = $settings->mailer ?? false;
        $this->smtp_port = $settings->smtp_port;
        $this->smtp_host = $settings->smtp_host;
        $this->smtp_username = $settings->smtp_username;
        $this->smtp_password = $settings->smtp_password;
        $this->smtp_encryption = $settings->smtp_encryption;
        $this->sender_name = $settings->sender_name;
        $this->sender_email = $settings->sender_email;
    }

    public function sendTestEmail($email)
    {
        if (empty($email)) {
            return session()->flash('error', t('no_recipient_provided'));
        }

        if (! is_smtp_valid()) {
            return $this->notify(['type' => 'danger', 'message' => t('email_config_is_required')]);
        }

        try {

            $content = render_email_template('test-email', ['userId' => auth()->id()]);
            $subject = get_email_subject('test-email', ['userId' => auth()->id()]);

            if (is_smtp_valid()) {
                $result = Email::to($email)
                    ->subject($subject)
                    ->content($content)
                    ->send();

                $this->notify(['type' => 'success', 'message' => t('email_sent_successfully')]);
            } else {
                $this->notify(['type' => 'danger', 'message' => t('email_config_is_required')]);
            }

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function save()
    {
        if (checkPermission('admin.system_settings.edit')) {
            $this->validate();

            $originalSettings = get_settings_by_group('email');

            $newSettings = [
                'mailer' => $this->mailer,
                'smtp_port' => $this->smtp_port,
                'smtp_username' => $this->smtp_username,
                'smtp_password' => $this->smtp_password,
                'smtp_encryption' => $this->smtp_encryption,
                'sender_name' => $this->sender_name,
                'sender_email' => $this->sender_email,
                'smtp_host' => $this->smtp_host,
            ];

            // Compare and filter only modified settings
            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return $value !== $originalSettings->$key;
            }, ARRAY_FILTER_USE_BOTH);

            // Save only if there are modifications
            if (! empty($modifiedSettings)) {
                set_settings_batch('email', $modifiedSettings);
                AdminCache::invalidateTags(['admin.settings', 'admin.mail']);
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.system.email-settings');
    }
}
