<?php

namespace App\Http\Controllers;

class PrivacyPolicyController extends Controller
{
    public function show()
    {

        $settings = get_settings_by_group('privacy-policy');

        return view('privacy-policy', [
            'title' => $settings->title ?? 'Privacy Policy',
            'content' => $settings->content ?? '',
            'updated_at' => $settings->updated_at ? format_date_time($settings->updated_at) : null,
        ]);
    }
}
