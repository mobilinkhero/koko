<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class Testimonials extends Component
{
    public $testimonials = [];

    public $currentIndex = 0;

    public function mount(): void
    {
        $settings = get_batch_settings(['theme.testimonials']);
        $this->testimonials = json_decode($settings['theme.testimonials'], true) ?? [];
    }

    public function render()
    {
        return view('livewire.frontend.testimonials', [
            'currentTestimonial' => $this->testimonials[$this->currentIndex] ?? null,
        ]);
    }
}
