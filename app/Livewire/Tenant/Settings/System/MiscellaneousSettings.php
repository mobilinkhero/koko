<?php

namespace App\Livewire\Tenant\Settings\System;

use App\Rules\PurifiedInput;
use Livewire\Component;

class MiscellaneousSettings extends Component
{
    public $tables_pagination_limit = 0;

    protected function rules()
    {
        return [
            'tables_pagination_limit' => ['nullable', 'integer', 'min:1', 'max:500', new PurifiedInput(t('sql_injection_error'))],
        ];
    }

    public function mount()
    {
        if (! checkPermission('tenant.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $settings = tenant_settings_by_group('miscellaneous');

        $this->tables_pagination_limit = $settings['tables_pagination_limit'] ?? 0;
    }

    public function save()
    {
        if (checkPermission('tenant.system_settings.edit')) {
            $this->validate();

            $originalSettings = tenant_settings_by_group('miscellaneous');

            $newSettings = [
                'tables_pagination_limit' => $this->tables_pagination_limit,
            ];

            // Filter only modified or undefined settings
            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return ! array_key_exists($key, $originalSettings) || $originalSettings[$key] !== $value;
            }, ARRAY_FILTER_USE_BOTH);

            if (! empty($modifiedSettings)) {
                foreach ($modifiedSettings as $key => $value) {
                    save_tenant_setting('miscellaneous', $key, $value);
                }

                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.tenant.settings.system.miscellaneous-settings');
    }
}
