<?php

namespace App\Livewire\Admin\Settings\System;

use App\Rules\PurifiedInput;
use Livewire\Component;

class MiscellaneousSettings extends Component
{
    public $tables_pagination_limit = 10;

    public $max_queue_jobs = 100;

    public ?bool $is_enable_landing_page = true;

    protected function rules()
    {
        return [
            'tables_pagination_limit' => ['nullable', 'integer', 'min:1', 'max:500', new PurifiedInput(t('sql_injection_error'))],
            'max_queue_jobs' => ['required', 'integer', 'min:100', 'max:1000', new PurifiedInput(t('sql_injection_error'))],
            'is_enable_landing_page' => 'nullable|bool',

        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $system_settings = get_settings_by_group('system');

        $this->tables_pagination_limit = $system_settings->tables_pagination_limit ?? 10;
        $this->max_queue_jobs = $system_settings->max_queue_jobs ?? 100;
        $this->is_enable_landing_page = $system_settings->is_enable_landing_page ?? '';

    }

    public function save()
    {
        if (checkPermission('admin.system_settings.edit')) {
            $this->validate();

            $originalSettings = get_settings_by_group('system');

            $newSettings = [
                'tables_pagination_limit' => empty($this->tables_pagination_limit) ? 10 : $this->tables_pagination_limit,
                'max_queue_jobs' => empty($this->max_queue_jobs) ? 100 : $this->max_queue_jobs,
                'is_enable_landing_page' => $this->is_enable_landing_page,

            ];

            // Compare and filter only modified settings
            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($originalSettings) {
                return $value !== $originalSettings->$key;
            }, ARRAY_FILTER_USE_BOTH);

            // Save only if there are modifications
            if (! empty($modifiedSettings)) {
                set_settings_batch('system', $modifiedSettings);
                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.system.miscellaneous-settings');
    }
}
