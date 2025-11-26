<?php

namespace App\Livewire\Frontend;

use App\Models\Faq;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class FaqList extends Component
{
    public $activeId = null;

    public $faqs;

    public function mount()
    {
        $this->faqs = Cache::remember('faqs_visible_sorted', 3600, function () {
            return Faq::where('is_visible', true)
                ->orderBy('sort_order')
                ->get();

        });

    }

    public function toggleFaq($id)
    {
        $this->activeId = $this->activeId === $id ? null : $id;
    }

    public function render()
    {
        $themeSettings = get_batch_settings([
            'theme.faq_section_title',
            'theme.faq_section_subtitle',

        ]);

        return view('livewire.frontend.faq-list', ['themeSettings' => $themeSettings]);
    }
}
