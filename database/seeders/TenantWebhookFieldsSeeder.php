<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Tenant\TenantSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantWebhookFieldsSeeder extends Seeder
{
    /**
     * The webhook fields configuration
     */
    private const WEBHOOK_SELECTED_FIELDS = [
        'account_alerts',
        'account_review_update',
        'account_settings_update',
        'account_update',
        'automatic_events',
        'business_capability_update',
        'business_status_update',
        'calls',
        'flows',
        'group_lifecycle_update',
        'group_participants_update',
        'group_settings_update',
        'group_status_update',
        'history',
        'message_echoes',
        'message_template_components_update',
        'message_template_quality_update',
        'message_template_status_update',
        'messages',
        'messaging_handovers',
        'partner_solutions',
        'payment_configuration_update',
        'phone_number_name_update',
        'phone_number_quality_update',
        'security',
        'web_app_data_sync',
        'web_message_echoes',
        'template_category_update',
        'tracking_events',
        'user_preferences',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        try {
            DB::transaction(function () {
                $tenants = Tenant::all();
                $this->command->info("Found {$tenants->count()} tenants to process");

                $createdCount = 0;
                $skippedCount = 0;

                foreach ($tenants as $tenant) {
                    // Check if the setting already exists
                    $existingSetting = TenantSetting::where('tenant_id', $tenant->id)
                        ->where('group', 'whats-mark')
                        ->where('key', 'webhook_selected_fields')
                        ->first();

                    if ($existingSetting) {
                        $skippedCount++;

                        continue;
                    }

                    // Create the new setting
                    TenantSetting::create([
                        'tenant_id' => $tenant->id,
                        'group' => 'whats-mark',
                        'key' => 'webhook_selected_fields',
                        'value' => json_encode(self::WEBHOOK_SELECTED_FIELDS),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $createdCount++;

                }

                $this->command->info('Seeding completed successfully!');
                $this->command->table(
                    ['Result', 'Count'],
                    [
                        ['Settings Created', $createdCount],
                        ['Settings Skipped', $skippedCount],
                        ['Total Tenants', $tenants->count()],
                    ]
                );
            });
        } catch (\Exception $e) {
            $this->command->error('Seeding failed: '.$e->getMessage());
            throw $e;
        }
    }
}
