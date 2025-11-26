<?php

namespace App\Livewire\Admin\Settings\System;

use App\Rules\PurifiedInput;
use Livewire\Component;

class SystemSettings extends Component
{
    public ?string $site_name = '';

    public ?string $site_description = '';

    public ?string $timezone = '';

    public ?string $date_format = '';

    public ?string $time_format = '';

    public ?string $active_language = '';

    public ?string $company_name = '';

    public ?string $company_country_id = '';

    public ?string $company_email = '';

    public ?string $company_city = '';

    public ?string $company_state = '';

    public ?string $company_zip_code = '';

    public ?string $company_address = '';

    public ?array $timezone_list = [];

    public ?array $default_country_code = [];

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
            'site_name' => ['nullable', 'string', 'max:50', new PurifiedInput(t('sql_injection_error'))],
            'site_description' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'timezone' => 'nullable|string|timezone',
            'date_format' => 'nullable|string',
            'active_language' => 'nullable|string',
            'time_format' => 'nullable|string',
            'company_name' => ['nullable', 'string', 'max:30', new PurifiedInput(t('sql_injection_error'))],
            'company_country_id' => ['nullable', 'integer'],
            'company_email' => ['nullable', 'email', new PurifiedInput(t('sql_injection_error'))],
            'company_city' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
            'company_state' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
            'company_zip_code' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
            'company_address' => ['nullable', 'string', new PurifiedInput(t('sql_injection_error'))],
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $this->timezone_list = timezone_identifiers_list();

        $system_settings = get_settings_by_group('system');

        $this->site_name = $system_settings->site_name ?? '';
        $this->site_description = $system_settings->site_description ?? '';
        $this->timezone = $system_settings->timezone ?? '';
        $this->date_format = $system_settings->date_format ?? '';
        $this->time_format = $system_settings->time_format ?? '';
        $this->active_language = $system_settings->active_language ?? '';
        $this->company_name = $system_settings->company_name ?? '';
        $this->company_country_id = $system_settings->company_country_id ?? '';
        $this->company_city = $system_settings->company_city ?? '';
        $this->company_state = $system_settings->company_state ?? '';
        $this->company_zip_code = $system_settings->company_zip_code ?? '';
        $this->company_address = $system_settings->company_address ?? '';
        $this->company_email = $system_settings->company_email ?? '';
        $this->default_country_code = $system_settings->default_country_code ?? [
            'name' => 'India',
            'iso2' => 'in',
        ];
    }

    public function save()
    {
        if (checkPermission('admin.system_settings.edit')) {
            $this->validate();
            $settings = get_settings_by_group('system');
            $country = [
                'name' => $this->default_country_code['name'] ?? '',
                'iso2' => $this->default_country_code['iso2'] ?? '',
            ];
            $newSettings = [
                'site_name' => $this->site_name,
                'site_description' => $this->site_description,
                'timezone' => $this->timezone,
                'date_format' => $this->date_format,
                'time_format' => $this->time_format,
                'active_language' => $this->active_language,
                'company_name' => $this->company_name,
                'company_country_id' => $this->company_country_id,
                'company_city' => $this->company_city,
                'company_state' => $this->company_state,
                'company_zip_code' => $this->company_zip_code,
                'company_address' => $this->company_address,
                'company_email' => $this->company_email,
                'default_country_code' => $country,
            ];
            //   dd($newSettings);
            // Filter the settings that have been modified
            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($settings) {
                return $value !== $settings->$key;
            }, ARRAY_FILTER_USE_BOTH);

            // Save only if there are modifications
            if (! empty($modifiedSettings)) {
                set_settings_batch('system', $modifiedSettings);
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function getCountriesProperty()
    {
        return get_country_list();
    }

    public function render()
    {
        return view('livewire.admin.settings.system.system-settings');
    }
}
