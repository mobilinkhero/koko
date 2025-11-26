<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SanitizeInputs;
use Illuminate\Support\Facades\Route;

Route::middleware([AdminMiddleware::class, SanitizeInputs::class])->group(function () {
    // Define settings routes dynamically
    $settings = [
        'themes' => App\Livewire\Admin\Settings\Website\ThemeSettings::class,
        'section-title' => App\Livewire\Admin\Settings\Website\SectionTitleSubtitleSettings::class,
        'custom-css' => App\Livewire\Admin\Settings\Website\CustomCssSettings::class,
        'custom-js' => App\Livewire\Admin\Settings\Website\CustomJsSettings::class,
        'feature' => App\Livewire\Admin\Settings\Website\FeatureSettings::class,
        'feature-two' => App\Livewire\Admin\Settings\Website\FeatureTwoSettings::class,
        'feature-three' => App\Livewire\Admin\Settings\Website\FeatureThreeSettings::class,
        'hero-section' => App\Livewire\Admin\Settings\Website\HeroSectionSettings::class,
        'partner-logo' => App\Livewire\Admin\Settings\Website\PartnerLogoSettings::class,
        'testimonials' => App\Livewire\Admin\Settings\Website\TestimonialSettings::class,
        'unique-feature' => App\Livewire\Admin\Settings\Website\UniqueFeatureSettings::class,
        'website-seo' => App\Livewire\Admin\Settings\Website\WebsiteSeoSettings::class,
    ];

    foreach ($settings as $prefix => $component) {
        Route::get("/settings/{$prefix}", $component)->name("{$prefix}.settings.view");
    }
});
