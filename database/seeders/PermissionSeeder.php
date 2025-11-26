<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Admin permissions (prefixed with 'admin.')
        $adminPermissions = [
            'admin.tenants.view',
            'admin.tenants.create',
            'admin.tenants.edit',
            'admin.tenants.delete',
            'admin.tenants.login',

            'admin.subscription.view',

            'admin.invoices.view',

            'admin.transactions.view',
            'admin.transactions.actions',

            'admin.plans.view',
            'admin.plans.create',
            'admin.plans.edit',
            'admin.plans.delete',

            'admin.website_settings.view',
            'admin.website_settings.edit',

            'admin.system_settings.view',
            'admin.system_settings.edit',

            'admin.payment_settings.view',
            'admin.payment_settings.edit',

            'admin.users.view',
            'admin.users.create',
            'admin.users.edit',
            'admin.users.delete',

            'admin.roles.view',
            'admin.roles.create',
            'admin.roles.edit',
            'admin.roles.delete',

            'admin.department.view',
            'admin.department.create',
            'admin.department.edit',
            'admin.department.delete',

            'admin.currency.view',
            'admin.currency.create',
            'admin.currency.edit',
            'admin.currency.delete',

            'admin.coupon.view',
            'admin.coupon.create',
            'admin.coupon.edit',
            'admin.coupon.delete',

            'admin.faq.view',
            'admin.faq.create',
            'admin.faq.edit',
            'admin.faq.delete',

            'admin.pages.view',
            'admin.pages.create',
            'admin.pages.edit',
            'admin.pages.delete',

            'admin.email_template.view',
            'admin.email_template.edit',

        ];

        // Tenant permissions (prefixed with 'tenant.')
        $tenantPermissions = [
            'tenant.connect_account.view',
            'tenant.connect_account.connect',
            'tenant.connect_account.disconnect',

            'tenant.contact.view',
            'tenant.contact.view_own',
            'tenant.contact.create',
            'tenant.contact.edit',
            'tenant.contact.delete',
            'tenant.contact.bulk_import',

            'tenant.subscription.view',

            'tenant.invoices.view',

            'tenant.template.view',
            'tenant.template.load_template',
            'tenant.template.create',
            'tenant.template.edit',
            'tenant.template.delete',

            'tenant.campaigns.view',
            'tenant.campaigns.create',
            'tenant.campaigns.edit',
            'tenant.campaigns.delete',
            'tenant.campaigns.show_campaign',

            'tenant.bulk_campaigns.send',

            'tenant.template_bot.view',
            'tenant.template_bot.create',
            'tenant.template_bot.edit',
            'tenant.template_bot.delete',
            'tenant.template_bot.clone',

            'tenant.message_bot.view',
            'tenant.message_bot.create',
            'tenant.message_bot.edit',
            'tenant.message_bot.delete',
            'tenant.message_bot.clone',

            'tenant.source.view',
            'tenant.source.create',
            'tenant.source.edit',
            'tenant.source.delete',

            'tenant.status.view',
            'tenant.status.create',
            'tenant.status.edit',
            'tenant.status.delete',

            'tenant.group.view',
            'tenant.group.create',
            'tenant.group.edit',
            'tenant.group.delete',

            'tenant.custom_fields.view',
            'tenant.custom_fields.create',
            'tenant.custom_fields.edit',
            'tenant.custom_fields.delete',

            'tenant.ai_prompt.view',
            'tenant.ai_prompt.create',
            'tenant.ai_prompt.edit',
            'tenant.ai_prompt.delete',

            'tenant.canned_reply.view',
            'tenant.canned_reply.create',
            'tenant.canned_reply.edit',
            'tenant.canned_reply.delete',

            'tenant.chat.view',
            'tenant.chat.read_only',
            'tenant.chat.delete',

            'tenant.activity_log.view',
            'tenant.activity_log.delete',

            'tenant.whatsmark_settings.view',
            'tenant.whatsmark_settings.edit',

            'tenant.system_settings.view',
            'tenant.system_settings.edit',

            'tenant.staff.view',
            'tenant.staff.create',
            'tenant.staff.edit',
            'tenant.staff.delete',

            'tenant.role.view',
            'tenant.role.create',
            'tenant.role.edit',
            'tenant.role.delete',

            'tenant.email_template.view',
            'tenant.email_template.edit',

            'tenant.bot_flow.view',
            'tenant.bot_flow.create',
            'tenant.bot_flow.edit',
            'tenant.bot_flow.delete',
        ];

        // Create admin permissions
        foreach ($adminPermissions as $permission) {
            Permission::UpdateOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
                'scope' => 'admin',
            ]);
        }

        // Create tenant permissions
        foreach ($tenantPermissions as $permission) {
            Permission::UpdateOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
                'scope' => 'tenant',
            ]);
        }
    }
}
