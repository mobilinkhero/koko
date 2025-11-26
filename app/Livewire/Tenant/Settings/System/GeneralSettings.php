<?php

namespace App\Livewire\Tenant\Settings\System;

use App\Facades\TenantCache;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class GeneralSettings extends Component
{
    use WithFileUploads;

    public ?string $timezone = '';

    public ?string $date_format = '';

    public ?string $time_format = '';

    public ?string $active_language = '';

    public ?array $timezone_list = [];

    public array $date_formats = [
        'Y-m-d' => 'Y-m-d',
        'd/m/Y' => 'd/m/Y',
        'm/d/Y' => 'm/d/Y',
        'd.m.Y' => 'd.m.Y',
        'd-m-Y' => 'd-m-Y',
        'm-d-Y' => 'm-d-Y',
        'm.d.Y' => 'm.d.Y',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    protected function rules()
    {
        return [
            'timezone' => 'nullable|string|timezone',
            'date_format' => 'nullable|string',
            'active_language' => 'nullable|string',
            'time_format' => 'nullable|string',
        ];
    }

    public function mount()
    {
        if (! checkPermission('tenant.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        $this->timezone_list = timezone_identifiers_list();

        $system_settings = tenant_settings_by_group('system');

        $this->timezone = $system_settings['timezone'] ?? '';
        $this->date_format = $system_settings['date_format'] ?? '';
        $this->time_format = $system_settings['time_format'] ?? '';
        $this->active_language = $system_settings['active_language'] ?? '';
    }

    public function save()
    {
        if (checkPermission('tenant.system_settings.edit')) {
            $this->validate();

            $tenant_id = tenant_id();
            $originalSettings = tenant_settings_by_group('system');
            $language = $originalSettings['active_language'] ?? 'en';
            TenantCache::forget("translations.{$tenant_id}_tenant_{$language}");

            $newSettings = [
                'timezone' => $this->timezone,
                'date_format' => $this->date_format,
                'time_format' => $this->time_format,
                'active_language' => $this->active_language,
            ];

            Session::put('locale', $this->active_language);
            App::setLocale($this->active_language);

            // Compare and filter only modified or missing settings
            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return ! array_key_exists($key, $originalSettings) || $originalSettings[$key] !== $value;
            }, ARRAY_FILTER_USE_BOTH);

            if (! empty($modifiedSettings)) {
                foreach ($modifiedSettings as $key => $value) {
                    save_tenant_setting('system', $key, $value);
                }
            }

            // Always notify and dispatch after any change (image or text)
            if (! empty($modifiedSettings)) {
                $this->notify([
                    'type' => 'success',
                    'message' => t('setting_save_successfully'),
                ], true);

                return redirect()->to(tenant_route('tenant.settings.general'));
            }
        }
    }

    public function render()
    {
        return view('livewire.tenant.settings.system.general-settings');
    }
}
