<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class UniqueFeature extends Component
{
    public function render()
    {
        $uniqueSettings = get_batch_settings([
            'theme.uni_feature_title',
            'theme.uni_feature_sub_title',
            'theme.uni_feature_list',
            'theme.uni_feature_description',
            'theme.uni_feature_image',
        ]);

        return view('livewire.frontend.unique-feature', ['uniqueSettings' => $uniqueSettings]);
    }
}
