<?php

namespace App\MergeFields\Admin;

use App\Models\Plan;

class PlanMergeFields
{
    public function name(): string
    {
        return 'plan-group';
    }

    public function templates(): array
    {
        return [];
    }

    public function build(): array
    {
        return [
            ['name' => 'Plan Name',           'key' => '{plan_name}'],
            ['name' => 'Plan Slug',           'key' => '{plan_slug}'],
            ['name' => 'Plan Description',    'key' => '{plan_description}'],
            ['name' => 'Plan Price',          'key' => '{plan_price}'],
            ['name' => 'Plan Yearly Price',   'key' => '{plan_yearly_price}'],
            ['name' => 'Yearly Discount (%)', 'key' => '{plan_yearly_discount}'],
            ['name' => 'Billing Period',      'key' => '{plan_billing_period}'],
            ['name' => 'Trial Days',          'key' => '{plan_trial_days}'],
            ['name' => 'Interval',            'key' => '{plan_interval}'],
            ['name' => 'Is Active',           'key' => '{plan_is_active}'],
            ['name' => 'Is Free',             'key' => '{plan_is_free}'],
            ['name' => 'Is Featured',         'key' => '{plan_featured}'],
            ['name' => 'Plan Color',          'key' => '{plan_color}'],
            ['name' => 'Currency ID',         'key' => '{plan_currency_id}'],
            ['name' => 'Stripe Product ID',   'key' => '{plan_stripe_product_id}'],
            ['name' => 'Plan Features',       'key' => '{plan_features}'],
        ];
    }

    public function format(array $context): array
    {
        if (empty($context['planId'])) {
            return [];
        }

        $plan = Plan::with('features')->findOrFail($context['planId']);

        $features = $plan->features->map(function ($feature) {
            return $feature->name.' ('.$feature->value.')';
        })->implode(', ');

        return [
            '{plan_name}' => $plan->name ?? '',
            '{plan_slug}' => $plan->slug ?? '',
            '{plan_description}' => $plan->description ?? '',
            '{plan_price}' => number_format($plan->price, 2),
            '{plan_yearly_price}' => number_format($plan->yearly_price, 2),
            '{plan_yearly_discount}' => $plan->yearly_discount.'%',
            '{plan_billing_period}' => $plan->billing_period ?? '',
            '{plan_trial_days}' => $plan->trial_days ?? 0,
            '{plan_interval}' => $plan->interval ?? 1,
            '{plan_is_active}' => $plan->is_active ? 'Yes' : 'No',
            '{plan_is_free}' => $plan->is_free ? 'Yes' : 'No',
            '{plan_featured}' => $plan->featured ? 'Yes' : 'No',
            '{plan_color}' => $plan->color ?? '',
            '{plan_currency_id}' => $plan->currency_id ?? '',
            '{plan_stripe_product_id}' => $plan->stripe_product_id ?? '',
            '{plan_features}' => $features,
        ];
    }
}
