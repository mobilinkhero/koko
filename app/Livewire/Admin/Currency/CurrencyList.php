<?php

namespace App\Livewire\Admin\Currency;

use App\Models\Currency;
use App\Rules\PurifiedInput;
use App\Services\CurrencyCache;
use Livewire\Component;

class CurrencyList extends Component
{
    public Currency $currencies;

    public $showCurrenciesModal = false;

    public $confirmingDeletion = false;

    public $currency_id = null;

    public $name;

    public $symbol;

    public $code;

    public $format;

    public $exchange_rate;

    protected $listeners = [
        'editCurrency' => 'editCurrency',
        'confirmDelete' => 'confirmDelete',
    ];

    public function mount()
    {
        if (! checkPermission('admin.currency.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $this->currencies = new Currency;
        // Set default format to "before_amount"
        $this->format = 'before_amount';
    }

    public function createCurrencies()
    {
        $this->resetForm();
        $this->showCurrenciesModal = true;
    }

    public function resetForm()
    {
        $this->reset(['currency_id', 'name', 'symbol', 'code', 'exchange_rate', 'showCurrenciesModal', 'confirmingDeletion']);
        $this->resetValidation();
        $this->currencies = new Currency;
        $this->format = 'before_amount'; // Set default format
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'unique:currencies,name,'.($this->currency_id ?? 'NULL'), new PurifiedInput(t('sql_injection_error'))],
            'symbol' => ['required', 'unique:currencies,symbol,'.($this->currency_id ?? 'NULL'), new PurifiedInput(t('sql_injection_error'))],
            'format' => ['required'],
            'code' => ['required', 'unique:currencies,code,'.($this->currency_id ?? 'NULL'), new PurifiedInput(t('sql_injection_error'))],
        ];
    }

    public function save()
    {
        if (checkPermission(['admin.currency.create', 'admin.currency.edit'])) {
            $isUpdate = ! empty($this->currency_id);

            $this->validate();

            try {
                if ($isUpdate) {
                    $this->currencies = Currency::findOrFail($this->currency_id);
                } else {
                    $this->currencies = new Currency;
                }

                $this->currencies->name = strtoupper($this->name);
                $this->currencies->symbol = $this->symbol;
                $this->currencies->code = $this->code;
                $this->currencies->format = $this->format;
                $this->currencies->exchange_rate = 1.000000; // Set default exchange rate
                $this->currencies->save();

                CurrencyCache::clearCache();
                $this->showCurrenciesModal = false;
                $this->dispatch('pg:eventRefresh-currency-table-n9y47e-table');

                $this->notify([
                    'type' => 'success',
                    'message' => $isUpdate ? t('currency_update_successfully') : t('currency_added_successfully'),
                ]);
            } catch (\Exception $e) {

                app_log('Currency save failed: '.$e->getMessage(), 'error', $e, [
                    'currency_id' => $this->currencies->id ?? null,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('currency_save_failed')]);
            }
        }
    }

    public function editCurrency($id)
    {
        $currency = Currency::query()
            ->where('id', $id)->firstOrFail();

        $this->currency_id = $currency->id;
        $this->name = $currency->name;
        $this->code = $currency->code;
        $this->symbol = $currency->symbol;
        $this->format = $currency->format;

        $this->resetValidation();
        $this->showCurrenciesModal = true;
    }

    public function confirmDelete($id)
    {
        $this->currency_id = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('admin.currency.delete')) {
            try {
                $currency = Currency::findOrFail($this->currency_id);

                if ($currency['is_default'] == 1) {
                    $this->notify(['type' => 'danger', 'message' => t('not_delete_base_currency')]);
                } else {
                    $currency->delete();
                    $this->notify(['type' => 'success', 'message' => t('currency_deleted_successfully')]);
                }

                CurrencyCache::clearCache();
                $this->confirmingDeletion = false;
                $this->dispatch('pg:eventRefresh-currency-table-n9y47e-table');
            } catch (\Exception $e) {
                app_log('Currency deletion failed: '.$e->getMessage(), 'error', $e, [
                    'currency_id' => $this->currencies->id ?? null,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('currency_delete_failed')]);
            }
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-currency-table-n9y47e-table');
    }

    public function render()
    {
        return view('livewire.admin.currencies.currencies');
    }
}
