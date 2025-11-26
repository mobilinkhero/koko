<?php

return [
    'tenant_status' => [
        [
            'name' => 'New',
            'color' => '#4CAF50',
            'isdefault' => 1,
        ],
        [
            'name' => 'In Progress',
            'color' => '#2196F3',
            'isdefault' => 0,
        ],
        [
            'name' => 'Contacted',
            'color' => '#FFC107',
            'isdefault' => 0,
        ],
        [
            'name' => 'Qualified',
            'color' => '#9C27B0',
            'isdefault' => 0,
        ],
        [
            'name' => 'Closed',
            'color' => '#F44336',
            'isdefault' => 0,
        ],
    ],
    'tenant_source' => [
        [
            'name' => 'facebook',
        ],
        [
            'name' => 'whatsapp',
        ],
    ],
    'tenant_default_settings' => [
        [
            'group' => 'system',
            'key' => 'timezone',
            'value' => 'UTC',
        ],
        [
            'group' => 'system',
            'key' => 'date_format',
            'value' => 'Y-m-d',
        ],
        [
            'group' => 'system',
            'key' => 'time_format',
            'value' => '12',
        ],
        [
            'group' => 'pusher',
            'key' => 'app_id',
            'value' => null,
        ],
        [
            'group' => 'pusher',
            'key' => 'app_key',
            'value' => null,
        ],
        [
            'group' => 'pusher',
            'key' => 'app_secret',
            'value' => null,
        ],
        [
            'group' => 'pusher',
            'key' => 'cluster',
            'value' => 'ap2',
        ],
        [
            'group' => 'pusher',
            'key' => 'real_time_notify',
            'value' => false,
        ],
        [
            'group' => 'pusher',
            'key' => 'desk_notify',
            'value' => false,
        ],
        [
            'group' => 'pusher',
            'key' => 'dismiss_desk_notification',
            'value' => 5,
        ],
        [
            'group' => 'dynamic_tables',
            'key' => 'tenant_table_names',
            'value' => '',
        ],
        [
            'group' => 'miscellaneous',
            'key' => 'tables_pagination_limit',
            'value' => 10,
        ],
        [
            'group' => 'whatsapp',
            'key' => 'logging',
            'value' => json_encode([
                'enabled' => false,
                'channel' => 'whatsapp',
                'level' => 'info',
            ]),
        ],
    ],
    'tenant_email_templates' => [
        [
            'name' => 'Email Confirmation',
            'subject' => 'Email Confirmation',
            'content' => '<p>Thank you for signing up with {company_name}, {first_name} {last_name}!</p><p>We\'re thrilled to have you on board. Before you get started, we need to verify your email address to ensure the security of your account.</p><p>Please click the button below to verify your email:</p><p><br></p><p> {verification_url}</p><p><br></p><p>Thank you.</p>',
            'slug' => 'tenant-email-confirmation',
            'merge_fields_groups' => json_encode(['tenant-other-group', 'tenant-user-group']),
            'is_active' => 1,
            'layout_id' => '1',
            'type' => 'tenant',
        ],
        [
            'name' => 'Welcome Email',
            'subject' => 'Welcome to {company_name}!',
            'content' => '<p>Dear {first_name} {last_name},</p><p>Welcome to {company_name}! We\'re excited to have you on board. 🚀</p><p>Get ready to explore our amazing features and make your life easier.</p><p>If you have any questions, our support team at <a href="mailto:{company_email}">{company_email}</a> is always here to help.</p><p>Start your journey here: <a href="{base_url}">{base_url}</a></p><p>Looking forward to seeing you thrive!</p>',
            'slug' => 'tenants-welcome-mail',
            'merge_fields_groups' => json_encode(['tenant-other-group', 'tenant-user-group']),
            'is_active' => 1,
            'layout_id' => '1',
            'type' => 'tenant',
        ],
        [
            'name' => 'Password Reset',
            'subject' => 'Password Reset Request',
            'content' => '<p>Hello {first_name} {last_name},</p><p>We received a request to reset your password for your {company_name} account.</p><p>If you made this request, click the button below to reset your password:</p><p><a href="{reset_url}" rel="noopener noreferrer" target="_blank">{reset_url}</a></p><p>If you did not request a password reset, please ignore this email or contact support at <a href="mailto:{company_email}" rel="noopener noreferrer" target="_blank">{company_email}</a>.</p>',
            'slug' => 'tenant-password-reset',
            'merge_fields_groups' => json_encode(['tenant-other-group', 'tenant-user-group']),
            'is_active' => 1,
            'layout_id' => '1',
            'type' => 'tenant',
        ],
        [
            'name' => 'New Contact Assigned',
            'subject' => '📌 New Contact Assigned to You',
            'content' => '<p>Hi {first_name} {last_name},</p><p>A new contact has been assigned to you. Here are the details:</p><ol><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span><strong>Contact Name:</strong> {contact_first_name} {contact_last_name}</li><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span><strong>Email:</strong> {contact_email}</li><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span><strong>Phone:</strong> {contact_phone_number}</li><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span><strong>Assigned By:</strong> {assigned_by}</li></ol><p>Please reach out to them promptly and ensure a smooth follow-up.</p><p>If you have any questions, feel free to get in touch.</p><p><strong>Best regards,</strong></p><p> {company_name}</p>',
            'slug' => 'tenant-new-contact-assigned',
            'merge_fields_groups' => json_encode(['tenant-other-group', 'tenant-user-group', 'tenant-contact-group']),
            'is_active' => 1,
            'layout_id' => '1',
            'type' => 'tenant',
        ],

    ],
];
