<?php

if (! function_exists('feature')) {
    /**
     * Get the feature service instance or check feature access.
     *
     * @return \App\Services\FeatureService|bool
     */
    function feature(?string $featureSlug = null, bool $requireActive = true)
    {
        $featureService = app(\App\Services\FeatureService::class);

        if ($featureSlug === null) {
            return $featureService;
        }

        return $featureService->hasAccess($featureSlug, $requireActive);
    }
}
