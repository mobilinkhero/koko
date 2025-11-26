<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Step 1: Insert base features
        $features = [
            ['name' => 'Contacts ', 'slug' => 'contacts', 'description' => 'Number of contacts allowed', 'type' => 'limit', 'display_order' => 10, 'default' => true],
            ['name' => 'Template Bots ', 'slug' => 'template_bots', 'description' => 'Number of template bots allowed', 'type' => 'limit', 'display_order' => 20, 'default' => false],
            ['name' => 'Message Bots ', 'slug' => 'message_bots', 'description' => 'Number of message bots allowed', 'type' => 'limit', 'display_order' => 30, 'default' => false],
            ['name' => 'Campaigns ', 'slug' => 'campaigns', 'description' => 'Number of campaigns allowed', 'type' => 'limit', 'display_order' => 40, 'default' => false],
            ['name' => 'AI Prompts ', 'slug' => 'ai_prompts', 'description' => 'Number of ai prompts allowed', 'type' => 'limit', 'display_order' => 50, 'default' => false],
            ['name' => 'Canned Replies ', 'slug' => 'canned_replies', 'description' => 'Number of canned replies allowed', 'type' => 'limit', 'display_order' => 60, 'default' => false],
            ['name' => 'Staff ', 'slug' => 'staff', 'description' => 'Number of staffs allowed', 'type' => 'limit', 'display_order' => 70, 'default' => false],
            ['name' => 'Conversation ', 'slug' => 'conversations', 'description' => 'Number of conversation allowed', 'type' => 'limit', 'display_order' => 80, 'default' => false],
            ['name' => 'Bot Flow ', 'slug' => 'bot_flow', 'description' => 'Number of bot flows allowed', 'type' => 'limit', 'display_order' => 90, 'default' => false],
            ['name' => 'Enable Api ', 'slug' => 'enable_api', 'description' => 'Enable(-1) or Disable(0) Api', 'type' => 'limit', 'display_order' => 100, 'default' => false],
            ['name' => 'AI Assistant ', 'slug' => 'ai_assistant', 'description' => 'Enable(-1) or Disable(0) AI Assistant', 'type' => 'limit', 'display_order' => 110, 'default' => false],
            ['name' => 'Ecommerce Bot ', 'slug' => 'ecommerce_bot', 'description' => 'Enable(-1) or Disable(0) Ecommerce Bot', 'type' => 'limit', 'display_order' => 120, 'default' => false],
        ];

        foreach ($features as $feature) {
            // Use updateOrInsert to avoid duplicates
            $feature['created_at'] = $now;
            $feature['updated_at'] = $now;
            DB::table('features')
                ->updateOrInsert(
                    ['slug' => $feature['slug']], // The unique identifier
                    $feature // All fields to update or insert
                );
        }

        // Step 3: Create plan_features
        $this->generatePlanFeatures($now);
    }

    private function generatePlanFeatures($now): void
    {
        $features = DB::table('features')->get();
        $freePlan = DB::table('plans')->where('slug', 'free')->first();

        if (! $freePlan) {
            // Step 2: Insert base plans
            $plan = [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Basic plan for individuals getting started with WhatsApp',
                'price' => 0,
                'yearly_price' => 0,
                'yearly_discount' => 0,
                'trial_days' => 14,
                'is_active' => true,
                'is_free' => true,
                'featured' => false,
                'interval' => 1,
                'color' => '#EF4444',
                'currency_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $planId = DB::table('plans')->insertGetId($plan);
            foreach ($features as $feature) {
                $value = $this->getFeatureValue($plan['slug'], $feature->slug);

                $data = [
                    'plan_id' => $planId,
                    'feature_id' => $feature->id,
                    'name' => $feature->name,
                    'slug' => $feature->slug,
                    'description' => $feature->description,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                DB::table('plan_features')->updateOrInsert(
                    [
                        'plan_id' => $data['plan_id'],
                        'feature_id' => $data['feature_id'],
                    ],
                    $data
                );
            }
        }
    }

    private function getFeatureValue(string $planSlug, string $featureSlug): string
    {
        $map = [
            'free' => [
                'contacts' => '50',
                'campaigns' => '5',
                'conversations' => '50',
                'ai_prompts' => '0',
                'canned_replies' => '0',
                'staff' => '1',
                'template_bots' => '5',
                'message_bots' => '5',
                'bot_flow' => '1',
                'ai_assistant' => '0',
                'ecommerce_bot' => '0',
            ],
        ];

        return $map[$planSlug][$featureSlug] ?? '0';
    }
}
