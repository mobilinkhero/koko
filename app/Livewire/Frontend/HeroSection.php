<?php

namespace App\Livewire\Frontend;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class HeroSection extends Component
{
    public $hero;

    public function mount()
    {
        // $this->hero = Cache::remember('active_hero_section', 3600, function () {
        //     return HeroSection::where('is_active', true)
        //         ->orderBy('sort_order')
        //         ->first();
        // });
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="animate-pulse">
            <div class="h-32 bg-gray-200 rounded w-full"></div>
        </div>
        HTML;
    }

    public function render()
    {
        $themeSettings = get_batch_settings([
            'theme.title',
            'theme.hero_heading',
            'theme.description',
            'theme.primary_button_text',
            'theme.primary_button_type',
            'theme.primary_button_url',
            'theme.secondary_button_text',
            'theme.secondary_button_type',
            'theme.secondary_button_url',
            'theme.image_path',
            'theme.image_alt_text',
        ]);

        return view('livewire.frontend.hero-section', ['themeSettings' => $themeSettings]);
    }
}
