<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    protected array $settings = [
        'theme.site_logo' => '',
        'theme.favicon' => '',
        'theme.dark_logo' => '',
        'theme.cover_page_image' => '',

        'theme.pricing_section_title' => '',
        'theme.pricing_section_subtitle' => '',
        'theme.faq_section_title' => '',
        'theme.faq_section_subtitle' => '',

        'theme.author_name' => '',
        'theme.seo_meta_title' => '',
        'theme.seo_meta_keywords' => '',
        'theme.seo_meta_description' => '',
        'theme.og_title' => '',
        'theme.og_description' => '',

        'theme.customCss' => '',
        'theme.custom_js_header' => '',
        'theme.custom_js_footer' => '',

        'theme.partner_logos' => '',

        'theme.uni_feature_image' => '',
        'theme.uni_feature_title' => 'Innovative Features',
        'theme.uni_feature_sub_title' => 'Unlocking New Possibilities with Cutting-Edge Technology',
        'theme.uni_feature_description' => 'Deliver great service experiences fast - without the complexity of traditional ITSM solutions.',
        'theme.uni_feature_list' => [
            'Continuous integration and deployment',
            'Development workflow',
            'Knowledge management',
        ],

        'theme.feature_title' => 'We invest in the world’s potential',
        'theme.feature_subtitle' => 'Deliver great service experiences fast - without the complexity of traditional ITSM solutions. Accelerate critical development work, eliminate toil, and deploy changes with ease.',
        'theme.feature_description' => 'Deliver great service experiences fast - without the complexity of traditional ITSM solutions.',
        'theme.feature_list' => [
            'Dynamic reports and dashboards',
            'Templates for everyone',
            'Development workflow',
            'Limitless business automation',
            'Knowledge management',
        ],
        'theme.feature_image' => '',

        'theme.title' => 'Scale your business with WhatsApp marketing automation',
        'theme.description' => 'Transform customer engagement with our powerful WhatsApp marketing platform. Send bulk messages, create chatbots, and automate conversations to boost sales and customer satisfaction.',
        'theme.primary_button_text' => '',
        'theme.primary_button_url' => '',
        'theme.primary_button_type' => '',
        'theme.secondary_button_text' => '',
        'theme.secondary_button_url' => '',
        'theme.secondary_button_type' => '',
        'theme.image_path' => '',
        'theme.image_alt_text' => '',

        // testimonials
        'theme.testimonials' => '',
    ];

    public function up(): void
    {
        foreach ($this->settings as $key => $value) {
            // Encode arrays as JSON for storage
            if (is_array($value)) {
                $value = json_encode($value);
            }

            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->settings) as $key) {
            if ($this->migrator->exists($key)) {
                $this->migrator->delete($key);
            }
        }
    }
};
