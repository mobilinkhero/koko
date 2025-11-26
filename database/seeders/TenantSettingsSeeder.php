<?php

namespace Database\Seeders;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Helper method to check if foreign key constraint exists
        $constraintExists = function (string $tableName, string $constraintName): bool {
            $count = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND CONSTRAINT_NAME = ?
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$tableName, $constraintName]);

            return $count[0]->count > 0;
        };

        if (! $constraintExists('ai_prompts', 'ai_prompts_tenant_id_foreign')) {
            Schema::table('ai_prompts', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('campaigns', 'campaigns_tenant_id_foreign')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('campaign_details', 'campaign_details_campaign_id_foreign')) {
            Schema::table('campaign_details', function (Blueprint $table) {
                $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            });
        }

        if (! $constraintExists('campaign_details', 'campaign_details_tenant_id_foreign')) {
            Schema::table('campaign_details', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('canned_replies', 'canned_replies_tenant_id_foreign')) {
            Schema::table('canned_replies', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('credit_transactions', 'credit_transactions_currency_id_foreign')) {
            Schema::table('credit_transactions', function (Blueprint $table) {
                $table->foreign('currency_id')->references('id')->on('currencies');
            });
        }

        if (! $constraintExists('credit_transactions', 'credit_transactions_invoice_id_foreign')) {
            Schema::table('credit_transactions', function (Blueprint $table) {
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            });
        }

        if (! $constraintExists('credit_transactions', 'credit_transactions_tenant_id_foreign')) {
            Schema::table('credit_transactions', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('feature_limits', 'feature_limits_feature_id_foreign')) {
            Schema::table('feature_limits', function (Blueprint $table) {
                $table->foreign('feature_id')->references('id')->on('features')->onDelete('cascade');
            });
        }

        if (! $constraintExists('feature_limits', 'feature_limits_plan_id_foreign')) {
            Schema::table('feature_limits', function (Blueprint $table) {
                $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            });
        }

        if (! $constraintExists('feature_limits', 'feature_limits_tenant_id_foreign')) {
            Schema::table('feature_limits', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('feature_usages', 'feature_usages_subscription_id_foreign')) {
            Schema::table('feature_usages', function (Blueprint $table) {
                $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            });
        }

        if (! $constraintExists('feature_usages', 'feature_usages_tenant_id_foreign')) {
            Schema::table('feature_usages', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('invoices', 'invoices_currency_id_foreign')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreign('currency_id')->references('id')->on('currencies');
            });
        }

        if (! $constraintExists('invoices', 'invoices_subscription_id_foreign')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('set null');
            });
        }

        if (! $constraintExists('invoices', 'invoices_tenant_id_foreign')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('invoice_items', 'invoice_items_invoice_id_foreign')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            });
        }

        if (! $constraintExists('invoice_taxes', 'invoice_taxes_invoice_id_foreign')) {
            Schema::table('invoice_taxes', function (Blueprint $table) {
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            });
        }

        if (! $constraintExists('invoice_taxes', 'invoice_taxes_tax_id_foreign')) {
            Schema::table('invoice_taxes', function (Blueprint $table) {
                $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('set null');
            });
        }

        if (! $constraintExists('message_bots', 'message_bots_tenant_id_foreign')) {
            Schema::table('message_bots', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('template_bots', 'template_bots_template_id_foreign')) {
            Schema::table('template_bots', function (Blueprint $table) {
                $table->foreign('template_id')->references('template_id')->on('whatsapp_templates')->onDelete('set null');
            });
        }

        if (! $constraintExists('template_bots', 'template_bots_tenant_id_foreign')) {
            Schema::table('template_bots', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('pages', 'pages_parent_id_foreign')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->foreign('parent_id')->references('id')->on('pages')->onDelete('set null');
            });
        }

        if (! $constraintExists('payment_methods', 'payment_methods_tenant_id_foreign')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('pusher_notifications', 'pusher_notifications_tenant_id_foreign')) {
            Schema::table('pusher_notifications', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('sources', 'sources_tenant_id_foreign')) {
            Schema::table('sources', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('statuses', 'statuses_tenant_id_foreign')) {
            Schema::table('statuses', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('subscriptions', 'subscriptions_plan_id_foreign')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->foreign('plan_id')->references('id')->on('plans');
            });
        }

        if (! $constraintExists('subscriptions', 'subscriptions_tenant_id_foreign')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('subscription_logs', 'subscription_logs_subscription_id_foreign')) {
            Schema::table('subscription_logs', function (Blueprint $table) {
                $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            });
        }

        if (! $constraintExists('subscription_logs', 'subscription_logs_transaction_id_foreign')) {
            Schema::table('subscription_logs', function (Blueprint $table) {
                $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            });
        }

        if (! $constraintExists('tenant_credit_balances', 'tenant_credit_balances_currency_id_foreign')) {
            Schema::table('tenant_credit_balances', function (Blueprint $table) {
                $table->foreign('currency_id')->references('id')->on('currencies');
            });
        }

        if (! $constraintExists('tenant_credit_balances', 'tenant_credit_balances_tenant_id_foreign')) {
            Schema::table('tenant_credit_balances', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('tenant_email_templates', 'tenant_email_templates_created_by_foreign')) {
            Schema::table('tenant_email_templates', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (! $constraintExists('tenant_email_templates', 'tenant_email_templates_tenant_id_foreign')) {
            Schema::table('tenant_email_templates', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('tenant_email_templates', 'tenant_email_templates_updated_by_foreign')) {
            Schema::table('tenant_email_templates', function (Blueprint $table) {
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (! $constraintExists('tickets', 'tickets_department_id_foreign')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            });
        }

        if (! $constraintExists('tickets', 'tickets_tenant_id_foreign')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
            });
        }

        if (! $constraintExists('tickets', 'tickets_tenant_staff_id_foreign')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreign('tenant_staff_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (! $constraintExists('ticket_replies', 'ticket_replies_ticket_id_foreign')) {
            Schema::table('ticket_replies', function (Blueprint $table) {
                $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            });
        }

        if (! $constraintExists('ticket_replies', 'ticket_replies_user_id_foreign')) {
            Schema::table('ticket_replies', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (! $constraintExists('transactions', 'transactions_currency_id_foreign')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreign('currency_id')->references('id')->on('currencies');
            });
        }

        if (! $constraintExists('transactions', 'transactions_invoice_id_foreign')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            });
        }

        if (! $constraintExists('transactions', 'transactions_payment_method_id_foreign')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
            });
        }

        if (! $constraintExists('webhook_logs', 'webhook_logs_tenant_id_foreign')) {
            Schema::table('webhook_logs', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('whatsapp_connections', 'whatsapp_connections_tenant_id_foreign')) {
            Schema::table('whatsapp_connections', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('whatsapp_templates', 'whatsapp_templates_tenant_id_foreign')) {
            Schema::table('whatsapp_templates', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (! $constraintExists('wm_activity_logs', 'wm_activity_logs_tenant_id_foreign')) {
            Schema::table('wm_activity_logs', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }
}
