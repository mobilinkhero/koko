<?php

namespace App\Livewire\Admin\Tax;

use App\Models\Tax;
use App\Rules\PurifiedInput;
use Livewire\Component;

class TaxList extends Component
{
    public Tax $taxes;

    public $showTaxesModal = false;

    public $confirmingDeletion = false;

    public $tax_id = null;

    public $name;

    public $rate;

    public $description;

    protected $listeners = [
        'editTax' => 'editTax',
        'confirmDelete' => 'confirmDelete',
    ];

    public function mount()
    {
        if (! auth()->user()->is_admin && auth()->user()->user_type === 'admin') {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $this->taxes = new Tax;
    }

    public function createTaxes()
    {
        $this->resetForm();
        $this->showTaxesModal = true;
    }

    private function resetForm()
    {
        $this->reset();
        $this->resetValidation();
        $this->taxes = new Tax;
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'unique:taxes,name,'.($this->tax_id ?? 'NULL'), new PurifiedInput(t('sql_injection_error'))],
            'rate' => ['required', 'numeric', 'min:0', 'max:100', new PurifiedInput(t('sql_injection_error'))],
            'description' => ['nullable', 'max:255', new PurifiedInput(t('sql_injection_error'))],
        ];
    }

    public function save()
    {
        if (auth()->user()->is_admin) {
            $isUpdate = ! empty($this->tax_id);

            $this->validate();

            try {
                if ($isUpdate) {
                    $this->taxes = Tax::findOrFail($this->tax_id);
                } else {
                    $this->taxes = new Tax;
                }

                $this->taxes->name = $this->name;
                $this->taxes->rate = $this->rate;
                $this->taxes->description = $this->description;
                $this->taxes->save();

                $this->showTaxesModal = false;
                $this->dispatch('pg:eventRefresh-tax-table-n9y47e-table');

                $this->notify([
                    'type' => 'success',
                    'message' => $isUpdate ? t('tax_update_successfully') : t('tax_added_successfully'),
                ]);
            } catch (\Exception $e) {
                app_log('Tax save failed: '.$e->getMessage(), 'error', $e, [
                    'tax_id' => $this->taxes->id ?? null,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('tax_save_failed')]);
            }
        }
    }

    public function editTax($id)
    {
        $tax = Tax::query()
            ->where('id', $id)->firstOrFail();

        $this->tax_id = $tax->id;
        $this->name = $tax->name;
        $this->rate = $tax->rate;
        $this->description = $tax->description;

        $this->resetValidation();
        $this->showTaxesModal = true;
    }

    public function confirmDelete($id)
    {
        $this->tax_id = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (auth()->user()->is_admin) {
            try {
                $tax = Tax::findOrFail($this->tax_id);
                $tax->delete();
                $this->notify(['type' => 'success', 'message' => t('tax_deleted_successfully')]);

                $this->confirmingDeletion = false;
                $this->dispatch('pg:eventRefresh-tax-table-n9y47e-table');
            } catch (\Exception $e) {
                app_log('Tax deletion failed: '.$e->getMessage(), 'error', $e, [
                    'tax_id' => $this->taxes->id ?? null,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('tax_delete_failed')]);
            }
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-tax-table-n9y47e-table');
    }

    public function render()
    {
        return view('livewire.admin.taxes.taxes');
    }
}
