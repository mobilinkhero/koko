<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class Feature extends Component
{
    public function render()
    {
        $featureSettings = get_batch_settings([
            'theme.feature_image',
            'theme.feature_title',
            'theme.feature_subtitle',
            'theme.feature_list',
            'theme.feature_description',
        ]);

        return view('livewire.frontend.feature', ['featureSettings' => $featureSettings]);
    }
}
