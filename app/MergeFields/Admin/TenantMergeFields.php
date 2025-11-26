<?php

namespace App\MergeFields\Admin;

use App\Models\Tenant;

class TenantMergeFields
{
    public function name(): string
    {
        return 'tenant-group';
    }

    public function templates(): array
    {
        return [
            'tenant-welcome-mail',
            'subscription-renewal-success',
            'subscription-renewal-failed',
            'subscription-created',
            'subscription-activated',
            'invoice-receipt',
            'subscription-renewal-reminder',
            'subscription-expiring-soon',
            'payment-rejected',
            'subscription-cancelled',
            'subscription-expired',
        ];
    }

    public function build(): array
    {
        return [
            ['name' => 'Company Name',        'key' => '{tenant_company_name}'],
            ['name' => 'Subdomain',           'key' => '{tenant_subdomain}'],
            ['name' => 'Status',              'key' => '{tenant_status}'],
            ['name' => 'Timezone',            'key' => '{tenant_timezone}'],
            ['name' => 'Has Custom Domain',   'key' => '{tenant_has_custom_domain}'],
            ['name' => 'Stripe Customer ID',  'key' => '{tenant_stripe_customer_id}'],
            ['name' => 'Address',             'key' => '{tenant_address}'],
            ['name' => 'Country ID',          'key' => '{tenant_country_id}'],
            ['name' => 'Payment Method',      'key' => '{tenant_payment_method}'],
            ['name' => 'Billing Name',        'key' => '{tenant_billing_name}'],
            ['name' => 'Billing Email',       'key' => '{tenant_billing_email}'],
            ['name' => 'Billing Address',     'key' => '{tenant_billing_address}'],
            ['name' => 'Billing City',        'key' => '{tenant_billing_city}'],
            ['name' => 'Billing State',       'key' => '{tenant_billing_state}'],
            ['name' => 'Billing Zip Code',    'key' => '{tenant_billing_zip_code}'],
            ['name' => 'Billing Country',     'key' => '{tenant_billing_country}'],
            ['name' => 'Billing Phone',       'key' => '{tenant_billing_phone}'],
            ['name' => 'Expires At',          'key' => '{tenant_expires_at}'],
        ];
    }

    public function format(array $context): array
    {
        if (empty($context['tenantId'])) {
            return [];
        }

        $tenant = Tenant::findOrFail($context['tenantId']);

        return [
            '{tenant_company_name}' => $tenant->company_name ?? '',
            '{tenant_subdomain}' => $tenant->subdomain ?? '',
            '{tenant_status}' => $tenant->status ?? '',
            '{tenant_timezone}' => $tenant->timezone ?? '',
            '{tenant_has_custom_domain}' => $tenant->has_custom_domain ? 'Yes' : 'No',
            '{tenant_stripe_customer_id}' => $tenant->stripe_customer_id ?? '',
            '{tenant_address}' => $tenant->address ?? '',
            '{tenant_country_id}' => $tenant->country_id ?? '',
            '{tenant_payment_method}' => $tenant->payment_method ?? '',
            '{tenant_billing_name}' => $tenant->billing_name ?? '',
            '{tenant_billing_email}' => $tenant->billing_email ?? '',
            '{tenant_billing_address}' => $tenant->billing_address ?? '',
            '{tenant_billing_city}' => $tenant->billing_city ?? '',
            '{tenant_billing_state}' => $tenant->billing_state ?? '',
            '{tenant_billing_zip_code}' => $tenant->billing_zip_code ?? '',
            '{tenant_billing_country}' => $tenant->billing_country ?? '',
            '{tenant_billing_phone}' => $tenant->billing_phone ?? '',
            '{tenant_expires_at}' => optional($tenant->expires_at)->toDateTimeString() ?? '',
        ];
    }
}
