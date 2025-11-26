<?php

namespace App\Traits;

use App\Services\FeatureService;
use Illuminate\Support\Facades\App;

trait TracksFeatureUsage
{
    public static function bootTracksFeatureUsage()
    {
        // After a model is deleted, update the feature usage count
        static::deleted(function ($model) {
            $featureSlug = $model->getFeatureSlug();

            if ($featureSlug) {
                App::make(FeatureService::class)->syncModelCount(
                    $featureSlug,
                    get_class($model)
                );
            }
        });
    }

    /**
     * Get the feature slug associated with this model.
     * Override this method in your model classes.
     */
    public function getFeatureSlug(): ?string
    {
        // Default implementation - override in models
        return null;
    }
}
