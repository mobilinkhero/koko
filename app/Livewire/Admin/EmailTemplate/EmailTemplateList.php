<?php

namespace App\Livewire\Admin\EmailTemplate;

use App\Models\EmailTemplate;
use Livewire\Component;

class EmailTemplateList extends Component
{
    public function mount()
    {
        if (! checkPermission('admin.email_template.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
    }

    public function render()
    {
        $templates = EmailTemplate::where(function ($query) {
            $query->where('type', 'admin')
                ->orWhereNull('type');
        })->latest()->get();

        return view('livewire.admin.email-template.email-template-list', ['templates' => $templates]);
    }
}
