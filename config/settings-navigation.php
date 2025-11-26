<?php

return [
    'admin_system_settings' => [
        'system' => [
            'label' => 'system',
            'route' => 'admin.system.settings.view',
            'icon' => 'heroicon-o-cog',
        ],
        'email' => [
            'label' => 'email',
            'route' => 'admin.email.settings.view',
            'icon' => 'heroicon-o-envelope',
        ],
        'recaptcha' => [
            'label' => 're_captcha',
            'route' => 'admin.re-captcha.settings.view',
            'icon' => 'heroicon-o-arrow-path-rounded-square',
        ],
        'cronjob' => [
            'label' => 'cronjob',
            'route' => 'admin.cron-job.settings.view',
            'icon' => 'heroicon-o-clock',
        ],
        'announcement' => [
            'label' => 'announcement',
            'route' => 'admin.announcement.settings.view',
            'icon' => 'heroicon-o-megaphone',
        ],
        'cache_management' => [
            'label' => 'cache_management',
            'route' => 'admin.cache-management.settings.view',
            'icon' => 'heroicon-o-circle-stack',
            'condition' => 'module_exists("CacheManager") && module_enabled("CacheManager")',
        ],
        'system_update' => [
            'label' => 'system_update',
            'route' => 'admin.system-update.settings.view',
            'icon' => 'heroicon-o-cloud-arrow-up',
        ],
        'system_information' => [
            'label' => 'system_information',
            'route' => 'admin.system-information.settings.view',
            'icon' => 'heroicon-o-information-circle',
        ],
        'tenant_settings' => [
            'label' => 'tenant_settings',
            'route' => 'admin.tenant-settings.settings.view',
            'icon' => 'carbon-user-settings',
        ],
        'invoice_settings' => [
            'label' => 'invoice_settings',
            'route' => 'admin.invoice-settings.settings.view',
            'icon' => 'heroicon-o-receipt-percent',
        ],
        'privacy_policy' => [
            'label' => 'privacy_policy',
            'route' => 'admin.privacy-policy.settings.view',
            'icon' => 'heroicon-o-document-text',
        ],
        'terms_conditions' => [
            'label' => 'terms_conditions',
            'route' => 'admin.terms-conditions.settings.view',
            'icon' => 'heroicon-o-clipboard-document-list',
            'fallback_label' => 'Terms and Conditions',
        ],
        'miscellaneous' => [
            'label' => 'miscellaneous',
            'route' => 'admin.miscellaneous.settings.view',
            'icon' => 'heroicon-o-adjustments-vertical',
        ],
        'theme_style' => [
            'label' => 'theme_style',
            'route' => 'admin.theme-style.index',
            'icon' => 'heroicon-o-beaker',
        ],
    ],

    // Website/Theme settings navigation
    'website_settings' => [
        'theme' => [
            'label' => 'theme',
            'route' => 'admin.themes.settings.view',
            'icon' => 'heroicon-o-paint-brush',
        ],
        'section_title' => [
            'label' => 'section_title_subltitle',
            'route' => 'admin.section-title.settings.view',
            'icon' => 'heroicon-o-pencil-square',
        ],
        'hero_section' => [
            'label' => 'hero_section',
            'route' => 'admin.hero-section.settings.view',
            'icon' => 'heroicon-o-rocket-launch',
        ],
        'partner_logo' => [
            'label' => 'partner_logo',
            'route' => 'admin.partner-logo.settings.view',
            'icon' => 'heroicon-o-building-office-2',
        ],
        'unique_feature' => [
            'label' => 'unique_feature',
            'route' => 'admin.unique-feature.settings.view',
            'icon' => 'heroicon-o-sparkles',
        ],
        'feature' => [
            'label' => 'feature',
            'route' => 'admin.feature.settings.view',
            'icon' => 'heroicon-o-star',
        ],
        'feature_two' => [
            'label' => 'feature_two',
            'route' => 'admin.feature-two.settings.view',
            'icon' => 'heroicon-o-star',
        ],
        'feature_three' => [
            'label' => 'feature_three',
            'route' => 'admin.feature-three.settings.view',
            'icon' => 'heroicon-o-star',
        ],
        'testimonials' => [
            'label' => 'testimonials',
            'route' => 'admin.testimonials.settings.view',
            'icon' => 'heroicon-o-chat-bubble-left-right',
        ],
        'website_seo' => [
            'label' => 'website_seo_og',
            'route' => 'admin.website-seo.settings.view',
            'icon' => 'heroicon-o-globe-alt',
        ],
        'custom_css' => [
            'label' => 'custom_css',
            'route' => 'admin.custom-css.settings.view',
            'icon' => 'carbon-color-palette',
        ],
        'custom_js' => [
            'label' => 'custom_js',
            'route' => 'admin.custom-js.settings.view',
            'icon' => 'heroicon-s-code-bracket',
        ],
    ],
    'tenant_settings' => [
        'general' => [
            'label' => 'system',
            'route' => 'tenant.settings.general',
            'icon' => 'heroicon-o-cog',
        ],
        'pusher' => [
            'label' => 'pusher',
            'route' => 'tenant.settings.pusher',
            'icon' => 'heroicon-o-bell-alert',
        ],
        'miscellaneous' => [
            'label' => 'miscellaneous',
            'route' => 'tenant.settings.miscellaneous',
            'icon' => 'heroicon-o-adjustments-vertical',
        ],
        'cache_management' => [
            'label' => 'cache_management',
            'route' => 'tenant.settings.cache-management',
            'icon' => 'heroicon-o-circle-stack',
            'condition' => 'module_exists("CacheManager") && module_enabled("CacheManager")',
        ],
    ],
    'whatsmark_tenant_settings' => [
        'whatsapp_auto_lead' => [
            'label' => 'whatsapp_auto_lead',
            'route' => 'tenant.settings.whatsapp-auto-lead',
            'icon' => 'heroicon-o-chat-bubble-bottom-center-text',
        ],
        'stop_bot' => [
            'label' => 'stop_bot',
            'route' => 'tenant.settings.stop-bot',
            'icon' => 'heroicon-o-shield-check',
        ],
        'whatsapp_web_hooks' => [
            'label' => 'web_hooks',
            'route' => 'tenant.settings.whatsapp-web-hooks',
            'icon' => 'heroicon-o-arrow-path-rounded-square',
        ],
        'support_agent' => [
            'label' => 'support_agent',
            'route' => 'tenant.settings.support-agent',
            'icon' => 'heroicon-o-megaphone',
        ],
        'notification_sound' => [
            'label' => 'notification_sound',
            'route' => 'tenant.settings.notification-sound',
            'icon' => 'heroicon-o-bell-alert',
        ],
        'ai_integration' => [
            'label' => 'ai_integration',
            'route' => 'tenant.settings.ai-integration',
            'icon' => 'heroicon-o-cpu-chip',
        ],
        'auto_clear_chat_history' => [
            'label' => 'auto_clear_chat_history',
            'route' => 'tenant.settings.auto-clear-chat-history',
            'icon' => 'heroicon-o-trash',
        ],
    ],
];
