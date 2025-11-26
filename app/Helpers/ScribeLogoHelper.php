<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class ScribeLogoHelper
{
    /**
     * Get the logo URL for Scribe documentation
     */
    public static function getLogo(): string
    {
        // Get theme settings from the database
        $adminThemeSettings = get_batch_settings(['theme.site_logo', 'theme.favicon', 'theme.dark_logo']);

        // Return the site logo if available, otherwise use the default logo
        return ! empty($adminThemeSettings['theme.site_logo'])
            ? Storage::url($adminThemeSettings['theme.site_logo'])
            : './img/light_logo.png';
    }
}
