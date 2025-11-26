<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ThemeSettings extends Settings
{
    // Theme Logo, Favicon, coverpage image
    public ?string $site_logo;

    public ?string $favicon;

    public ?string $dark_logo;

    public ?string $cover_page_image;

    // Section Title Subtitle
    public ?string $pricing_section_title;

    public ?string $pricing_section_subtitle;

    public ?string $faq_section_title;

    public ?string $faq_section_subtitle;

    // Website SEO & OG
    public ?string $author_name;

    public ?string $seo_meta_title;

    public ?string $seo_meta_keywords;

    public ?string $seo_meta_description;

    public ?string $og_title;

    public ?string $og_description;

    // Custome css and js
    public ?string $customCss;

    public ?string $custom_js_header;

    public ?string $custom_js_footer;

    public ?string $partner_logos;

    // Uni Feature
    public ?string $uni_feature_title;

    public ?string $uni_feature_sub_title;

    public ?string $uni_feature_description;

    public ?string $uni_feature_list;

    public ?string $uni_feature_image;

    // Feature 1
    public ?string $feature_title;

    public ?string $feature_subtitle;

    public ?string $feature_description;

    public ?string $feature_list;

    public ?string $feature_image;

    // Feature 2
    public ?bool $feature_two_enabled;

    public ?string $feature_title_two;

    public ?string $feature_subtitle_two;

    public ?string $feature_description_two;

    public ?array $feature_list_two;

    public ?string $feature_image_two;

    // Feature 3
    public ?bool $feature_three_enabled;

    public ?string $feature_title_three;

    public ?string $feature_subtitle_three;

    public ?string $feature_description_three;

    public ?array $feature_list_three;

    public ?string $feature_image_three;

    // HeroSection Settings
    public ?string $title;

    public ?string $hero_heading;

    public ?string $description;

    public ?string $primary_button_text;

    public ?string $primary_button_url;

    public ?string $primary_button_type;

    public ?string $secondary_button_text;

    public ?string $secondary_button_url;

    public ?string $secondary_button_type;

    public ?string $image_path;

    public ?string $image_alt_text;

    // Testimonial
    public ?string $testimonials;

    // Dynamic Theme Settings
    public ?string $theme_style;

    public ?string $theme_style_modified_at;

    public static function group(): string
    {
        return 'theme';
    }
}
