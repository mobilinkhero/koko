<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiAssistantEcommerceBotFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Adds AI Assistant and Ecommerce Bot features to all existing plans.
     */
    public function run(): void
    {
        // Get the features
        $aiAssistantFeature = DB::table('features')->where('slug', 'ai_assistant')->first();
        $ecommerceBotFeature = DB::table('features')->where('slug', 'ecommerce_bot')->first();

        if (! $aiAssistantFeature && ! $ecommerceBotFeature) {
            // Features don't exist yet, they should be created by FeatureSeeder first
            return;
        }

        // Get all plans
        $plans = DB::table('plans')->get(['id']);

        $timestamp = now();

        // Process AI Assistant feature
        if ($aiAssistantFeature) {
            $existingAiAssistantFeatures = DB::table('plan_features')
                ->where('feature_id', $aiAssistantFeature->id)
                ->pluck('plan_id')
                ->toArray();

            $plansNeedingAiAssistant = $plans->whereNotIn('id', $existingAiAssistantFeatures);

            if ($plansNeedingAiAssistant->isNotEmpty()) {
                $insertData = $plansNeedingAiAssistant->map(function ($plan) use ($aiAssistantFeature, $timestamp) {
                    return [
                        'plan_id' => $plan->id,
                        'feature_id' => $aiAssistantFeature->id,
                        'name' => $aiAssistantFeature->name,
                        'slug' => $aiAssistantFeature->slug,
                        'description' => $aiAssistantFeature->description,
                        'value' => '0', // Disabled by default
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                })->toArray();

                DB::table('plan_features')->insert($insertData);
            }
        }

        // Process Ecommerce Bot feature
        if ($ecommerceBotFeature) {
            $existingEcommerceBotFeatures = DB::table('plan_features')
                ->where('feature_id', $ecommerceBotFeature->id)
                ->pluck('plan_id')
                ->toArray();

            $plansNeedingEcommerceBot = $plans->whereNotIn('id', $existingEcommerceBotFeatures);

            if ($plansNeedingEcommerceBot->isNotEmpty()) {
                $insertData = $plansNeedingEcommerceBot->map(function ($plan) use ($ecommerceBotFeature, $timestamp) {
                    return [
                        'plan_id' => $plan->id,
                        'feature_id' => $ecommerceBotFeature->id,
                        'name' => $ecommerceBotFeature->name,
                        'slug' => $ecommerceBotFeature->slug,
                        'description' => $ecommerceBotFeature->description,
                        'value' => '0', // Disabled by default
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                })->toArray();

                DB::table('plan_features')->insert($insertData);
            }
        }
    }
}

