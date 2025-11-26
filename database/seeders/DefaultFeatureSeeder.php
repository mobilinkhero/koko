<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the feature once
        $feature = DB::table('features')->where('slug', 'enable_api')->first();

        if (! $feature) {
            return;
        }

        // Get all plans and existing plan_features in single queries
        $plans = DB::table('plans')->get(['id']);
        $existingPlanFeatures = DB::table('plan_features')
            ->where('feature_id', $feature->id)
            ->pluck('plan_id')
            ->toArray();

        // Filter out plans that already have this feature
        $plansNeedingFeature = $plans->whereNotIn('id', $existingPlanFeatures);

        if ($plansNeedingFeature->isEmpty()) {
            return;
        }

        // Prepare bulk insert data
        $timestamp = now();
        $insertData = $plansNeedingFeature->map(function ($plan) use ($feature, $timestamp) {
            return [
                'plan_id' => $plan->id,
                'feature_id' => $feature->id,
                'name' => $feature->name,
                'slug' => $feature->slug,
                'description' => $feature->description,
                'value' => '-1',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        })->toArray();

        // Single bulk insert
        DB::table('plan_features')->insert($insertData);
    }
}
