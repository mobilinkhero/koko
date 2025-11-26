<?php

namespace App\Livewire\Admin;

use App\Models\EmailTemplate;
use Livewire\Component;

class EmailTemplateSave extends Component
{
    public $templateId;

    public $name;

    public $slug;

    public $description;

    public $subject;

    public $content;

    public $variables = [];

    public $merge_fields_groups = [];

    public $is_active = true;

    public $is_system = false;

    public $category;

    public $type;

    public $layout_id;

    public $use_layout = false;

    public function mount($id = null)
    {
        $this->templateId = $id;
        if ($id) {
            $template = EmailTemplate::findOrFail($id);
            $this->fill($template->toArray());
        }
    }

    public function save()
    {
        $this->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $data = [
            'subject' => $this->subject,
            'content' => $this->content,
            'description' => $this->description,
            'category' => $this->category,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'use_layout' => $this->use_layout,
            'layout_id' => $this->layout_id,
        ];

        if ($this->templateId) {
            $template = EmailTemplate::findOrFail($this->templateId);
            $template->update($data);
            session()->flash('success', t('template_updated_successfully'));
        } else {
            $data['name'] = $this->name;
            $data['slug'] = $this->slug;
            $data['is_system'] = $this->is_system;
            $template = EmailTemplate::create($data);
            $this->templateId = $template->id;
            session()->flash('success', t('template_created_successfully'));
        }

        return redirect()->route('admin.email-template.list');
    }

    public function render()
    {
        return view('livewire.admin.email-template.email-template-save');
    }
}
