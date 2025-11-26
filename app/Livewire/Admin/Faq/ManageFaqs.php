<?php

namespace App\Livewire\Admin\Faq;

use App\Models\Faq;
use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ManageFaqs extends Component
{
    public Faq $faq;

    public $is_visible = true;

    public $question;

    public $answer;

    public $sort_order = 0;

    public $showFaqModal = false;

    public $confirmingDeletion = false;

    public bool $isUpdate;

    public $search = '';

    public $faq_id = null;

    protected $listeners = [
        'editFaq' => 'editFaq',
        'confirmDelete' => 'confirmDelete',
    ];

    protected function rules()
    {
        return [
            'faq.question' => [
                'required',
                new PurifiedInput(t('sql_injection_error')),
            ],
            'faq.answer' => [
                'required',
                new PurifiedInput(t('sql_injection_error')),
            ],
            'faq.is_visible' => ['boolean', 'nullable'],
        ];
    }

    public function mount()
    {
        if (! checkPermission(['admin.faq.view'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $this->resetForm();
        $this->faq = new Faq;
    }

    public function createFaq()
    {
        $this->resetForm();
        $this->showFaqModal = true;
    }

    private function resetForm()
    {
        $this->resetExcept('group');
        $this->resetValidation();
        $this->faq = new Faq;
    }

    public function save()
    {
        if (checkPermission(['admin.faq.create', 'admin.faq.edit'])) {
            $this->validate();

            try {
                $this->faq->sort_order = $this->faq_id
                    ? $this->faq->sort_order
                    : (Faq::max('sort_order') + 1);

                $this->faq->save();
                $this->showFaqModal = false;
                $message = $this->faq_id ? t('faq_updated_successfully') : t('faq_saved_successfully');
                $this->notify(['type' => 'success', 'message' => $message]);
                Cache::forget('faqs_visible_sorted');
            } catch (\Exception $e) {
                app_log('Faq save failed: '.$e->getMessage(), 'error', $e, [
                    'faq_id' => $this->faq->id ?? null,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('faq_save_failed')]);
            }
        }
    }

    public function editFaq($id)
    {
        $this->resetValidation();
        $faq = Faq::findOrFail($id);
        $this->faq = $faq;
        $this->faq_id = $faq->id;
        $this->showFaqModal = true;
    }

    public function confirmDelete($id)
    {
        $this->faq_id = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('admin.faq.delete')) {
            try {
                Faq::find($this->faq_id)->delete();
                $this->confirmingDeletion = false;
                Cache::forget('faqs_visible_sorted');
                $this->notify(['type' => 'success', 'message' => t('faq_delete_message')]);
            } catch (\Exception $e) {
                app_log('Faq delete failed: '.$e->getMessage(), 'error', $e, [
                    'faq_id' => $this->faq_id,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('faq_delete_failed')]);
            }
        }
    }

    public function reorder($items)
    {
        if (checkPermission('admin.faq.edit')) {
            foreach ($items as $item) {
                Faq::find($item['value'])->update(['sort_order' => $item['order']]);
            }
            $this->notify([
                'type' => 'success',
                'message' => t('faq_order_updated'),
            ]);
        } else {
            $this->notify([
                'message' => t('no_permission_to_perform_action'),
                'type' => 'warning',
            ]);
        }
    }

    public function toggleVisibility($id)
    {
        if (checkPermission('admin.faq.edit')) {
            $faq = Faq::find($id);
            $status = ! $faq->is_visible;
            $faq->update(['is_visible' => $status]);
            Cache::forget('faqs_visible_sorted');
            $this->notify([
                'message' => t($status ? 'faq_visible_message' : 'faq_hidden_message'),
                'type' => $status ? 'success' : 'warning',
            ]);
        } else {
            $this->notify([
                'message' => t('no_permission_to_perform_action'),
                'type' => 'warning',
            ]);
        }
    }

    public function getFaqsProperty()
    {
        return Faq::query()
            ->when($this->search, function ($query) {})
            ->orderBy('sort_order')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.faq.manage-faqs');
    }
}
