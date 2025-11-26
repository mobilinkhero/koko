<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $faqs = [
            [
                'question' => 'How do I upgrade my subscription plan?',
                'answer' => 'You can upgrade your plan from your billing dashboard. Navigate to Subscriptions > Upgrade and select your desired higher-tier plan. The upgrade will be prorated, meaning you\'ll only pay the difference for the remaining billing period. Payment is required immediately to activate the upgrade.',
                'is_visible' => 1,
                'sort_order' => 1,
            ],
            [
                'question' => 'How do I downgrade my subscription plan?',
                'answer' => 'To downgrade, go to Subscriptions > Downgrade and choose a lower-tier plan. Downgrades are processed immediately, and any credit from the price difference will be applied to your account for future billing cycles.',
                'is_visible' => 1,
                'sort_order' => 2,
            ],
            [
                'question' => 'How does the billing cycle work?',
                'answer' => 'Your billing cycle depends on your plan\'s billing period (monthly or yearly). The cycle starts from your subscription activation date and renews automatically unless you\'ve disabled auto-renewal. You can view your next billing date in your subscription dashboard.',
                'is_visible' => 1,
                'sort_order' => 3,
            ],
            [
                'question' => 'When will I be charged for renewals?',
                'answer' => 'Auto-billing occurs 1 day after your current period ends. For example, if your subscription expires on January 15th, auto-billing will attempt on January 16th. This grace period ensures uninterrupted service.',
                'is_visible' => 1,
                'sort_order' => 4,
            ],
            [
                'question' => 'What happens if my payment fails?',
                'answer' => 'If auto-billing fails, you\'ll receive notifications and have a grace period to update your payment method. Failed payments are logged in your transaction history, and you can manually retry payment from your billing dashboard.',
                'is_visible' => 1,
                'sort_order' => 5,
            ],
        ];

        foreach ($faqs as $faq) {
            $faq['created_at'] = $now;
            $faq['updated_at'] = $now;
            DB::table('faqs')->updateOrInsert(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
