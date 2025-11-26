<?php

namespace App\Http\Controllers;

class TermsConditionsController extends Controller
{
    public function show()
    {
        $settings = get_settings_by_group('terms-conditions');

        return view('terms-conditions', [
            'title' => $settings->title ?? 'Terms and Conditions',
            'content' => $settings->content ?? '',
            'updated_at' => $settings->updated_at ? format_date_time($settings->updated_at) : null,
        ]);
    }
}
