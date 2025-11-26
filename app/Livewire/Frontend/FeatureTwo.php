<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class FeatureTwo extends Component
{
    public function render()
    {
        $featureSettings = get_batch_settings([
            'theme.feature_image_two',
            'theme.feature_two_enabled',
            'theme.feature_title_two',
            'theme.feature_subtitle_two',
            'theme.feature_list_two',
            'theme.feature_description_two',
        ]);

        return view('livewire.frontend.feature-two', ['featureSettings' => $featureSettings]);
    }
}
