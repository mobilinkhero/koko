<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class FeatureThree extends Component
{
    public function render()
    {
        $featureSettings = get_batch_settings([
            'theme.feature_image_three',
            'theme.feature_three_enabled',
            'theme.feature_title_three',
            'theme.feature_subtitle_three',
            'theme.feature_list_three',
            'theme.feature_description_three',
        ]);

        return view('livewire.frontend.feature-three', ['featureSettings' => $featureSettings]);
    }
}
