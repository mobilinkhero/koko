<?php

namespace App\Livewire\Admin\Settings\System;

use App\Rules\PurifiedInput;
use App\Services\TaxCache;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class InvoiceSettings extends Component
{
    public $prefix = '';

    public $bank_name = '';

    public $account_name = '';

    public $account_number = '';

    public $ifsc_code = '';

    public $footer_text = '';

    public $default_taxes = [];

    public $available_taxes = [];

    protected function rules()
    {
        return [
            'prefix' => ['nullable', 'string', 'max:10', new PurifiedInput(t('sql_injection_error'))],
            'bank_name' => ['nullable', 'string', 'max:125', new PurifiedInput(t('sql_injection_error'))],
            'account_name' => ['nullable', 'string', 'max:125', new PurifiedInput(t('sql_injection_error'))],
            'account_number' => ['nullable', 'string', 'max:50', new PurifiedInput(t('sql_injection_error'))],
            'ifsc_code' => ['nullable', 'string', 'max:20', new PurifiedInput(t('sql_injection_error'))],
            'footer_text' => ['nullable', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'default_taxes' => ['nullable', 'array'],
            'default_taxes.*' => ['exists:taxes,id'],
        ];
    }

    protected function messages()
    {
        return [
            'account_number.regex' => 'The account number must contain only digits.',
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $invoice_settings = get_settings_by_group('invoice');

        $this->prefix = $invoice_settings->prefix ?? '';
        $this->bank_name = $invoice_settings->bank_name ?? '';
        $this->account_name = $invoice_settings->account_name ?? '';
        $this->account_number = $invoice_settings->account_number ?? '';
        $this->ifsc_code = $invoice_settings->ifsc_code ?? '';
        $this->footer_text = $invoice_settings->footer_text ?? '';

        // Get the default taxes from settings
        try {
            $defaultTaxesJson = $invoice_settings->default_taxes ?? '[]';
            $decoded = json_decode($defaultTaxesJson, true);
            $this->default_taxes = is_array($decoded) ? $decoded : [];
        } catch (\Exception $e) {
            app_log('Error parsing default taxes: '.$e->getMessage(), 'error', $e);
            $this->default_taxes = [];
        }

        // Get all available taxes
        $this->available_taxes = TaxCache::getAllTaxes();
    }

    public function save()
    {
        if (checkPermission('admin.system_settings.edit')) {
            $this->validate();
            $settings = get_settings_by_group('invoice');

            // Ensure default_taxes is an array
            $defaultTaxes = is_array($this->default_taxes) ? $this->default_taxes : [];

            $newSettings = [
                'prefix' => $this->prefix,
                'bank_name' => $this->bank_name,
                'account_name' => $this->account_name,
                'account_number' => $this->account_number,
                'ifsc_code' => $this->ifsc_code,
                'footer_text' => $this->footer_text,
                'default_taxes' => json_encode($defaultTaxes),
            ];

            // Filter the settings that have been modified
            $modifiedSettings = array_filter($newSettings, function ($value, $key) use ($settings) {
                if ($key === 'default_taxes') {
                    $oldValue = $settings->default_taxes ?? '[]';

                    return $value !== $oldValue;
                }

                return $value !== ($settings->$key ?? null);
            }, ARRAY_FILTER_USE_BOTH);

            // Only update if there are actually changes
            if (! empty($modifiedSettings)) {
                // Update the settings
                foreach ($modifiedSettings as $key => $value) {
                    app('settings')->set('invoice.'.$key, $value);
                }

                // Clear the settings cache to ensure fresh values are used
                Cache::forget('settings_invoice');

                // Clear tax cache to ensure fresh tax settings are used for new invoices
                Cache::forget(TaxCache::getCacheKey());

                // Reset the TaxCache internal state to force reload
                app('tax.cache')->reset();

                $this->notify(['type' => 'success', 'message' => t('setting_save_successfully')]);
            }
        } else {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')]);
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.system.invoice-settings');
    }
}
