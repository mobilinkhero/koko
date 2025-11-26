<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ThemeStyleController extends Controller
{
    /**
     * Output the theme style custom CSS
     */
    public function css(): Response
    {
        $cacheKey = 'theme_css';
        $modifiedTimestamp = (string) (get_setting('theme.theme_style_modified_at') ?? '1682202438');
        $modifiedAt = DateTime::createFromFormat('U', $modifiedTimestamp);
        $content = Cache::remember($cacheKey, now()->addHours(24), function () {
            return $this->parseCss(get_setting('theme.theme_style'));
        });

        return response(
            $content = $this->parseCss(get_setting('theme.theme_style')),
            empty($content) ? 204 : 200,
            [
                'Content-Type' => 'text/css',
                'Cache-Control' => 'no-store, max-age=0, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        )->setLastModified($modifiedAt);
    }

    /**
     * Save theme style settings
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'theme_style' => 'required|string',
            'theme_style_modified_at' => 'required|numeric',
        ]);

        set_setting('theme.theme_style', $validated['theme_style']);
        set_setting('theme.theme_style_modified_at', $validated['theme_style_modified_at']);

        // Clear CSS cache
        clearstatcache();
        Cache::forget('theme_css');

        return response()->json(['success' => true]);
    }

    /**
     * Parse the theme style CSS
     */
    protected function parseCss(?string $style): string
    {
        if (! $style || ! Str::isJson($style)) {
            return '';
        }

        $style = json_decode($style, true);
        $css = ':root {';

        foreach ($style as $color => $options) {
            foreach ($options['swatches'] as $swatch) {
                [$r, $g, $b] = sscanf($swatch['hex'], '#%02x%02x%02x');
                $css .= "--color-$color-{$swatch['stop']}: $r, $g, $b !important;";
            }
        }

        $css .= '} /* Custom theme colors applied */';

        return $css;
    }

    /**
     * Show theme settings page
     */
    public function index()
    {
        return view('admin.settings.theme-style.index', [
            'currentTheme' => get_setting('theme.theme_style'),
            'lastModified' => get_setting('theme.theme_style_modified_at'),
        ]);
    }
}
