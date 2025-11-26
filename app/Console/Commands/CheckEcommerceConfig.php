<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\EcommerceConfiguration;

class CheckEcommerceConfig extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ecommerce:check-config {--tenant=1}';

    /**
     * The console command description.
     */
    protected $description = 'Check ecommerce configuration for debugging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = (int) $this->option('tenant');

        try {
            $this->info("🔍 Checking E-commerce Configuration");
            $this->info("Tenant ID: {$tenantId}");
            $this->info("----------------------------------------");

            // Check if config exists
            $config = EcommerceConfiguration::where('tenant_id', $tenantId)->first();
            
            if (!$config) {
                $this->error("❌ No e-commerce configuration found for tenant {$tenantId}");
                return 1;
            }

            $this->info("✅ E-commerce configuration found");
            $this->info("----------------------------------------");

            // Check basic settings
            $this->line("📋 Basic Settings:");
            $this->line("  - Is Configured: " . ($config->is_configured ? 'Yes' : 'No'));
            $this->line("  - Store Name: " . ($config->store_name ?: 'Not set'));
            $this->line("  - Currency: " . ($config->currency ?: 'Not set'));
            $this->line("  - Google Sheets URL: " . ($config->google_sheets_url ? 'Set' : 'Not set'));

            $this->info("----------------------------------------");

            // Check AI settings
            $this->line("🤖 AI Settings:");
            $this->line("  - AI Powered Mode: " . ($config->ai_powered_mode ? 'ENABLED' : 'DISABLED'));
            $this->line("  - OpenAI API Key: " . (!empty($config->openai_api_key) ? 'Set' : 'Not set'));
            $this->line("  - OpenAI Model: " . ($config->openai_model ?: 'Not set'));
            $this->line("  - AI Temperature: " . ($config->ai_temperature ?: 'Not set'));
            $this->line("  - AI Max Tokens: " . ($config->ai_max_tokens ?: 'Not set'));
            $this->line("  - Direct Sheets Integration: " . ($config->direct_sheets_integration ? 'Yes' : 'No'));
            $this->line("  - Bypass Local Database: " . ($config->bypass_local_database ? 'Yes' : 'No'));

            $this->info("----------------------------------------");

            // Check if fully configured for AI
            $isAiReady = $config->ai_powered_mode 
                && !empty($config->openai_api_key) 
                && !empty($config->google_sheets_url);

            if ($isAiReady) {
                $this->info("✅ AI is properly configured and ready");
            } else {
                $this->error("❌ AI is NOT ready:");
                if (!$config->ai_powered_mode) {
                    $this->line("  - AI mode is disabled");
                }
                if (empty($config->openai_api_key)) {
                    $this->line("  - OpenAI API key is missing");
                }
                if (empty($config->google_sheets_url)) {
                    $this->line("  - Google Sheets URL is missing");
                }
            }

            $this->info("----------------------------------------");

            // Check traditional ecommerce readiness
            $isTraditionalReady = $config->is_configured && !empty($config->google_sheets_url);
            
            if ($isTraditionalReady) {
                $this->info("✅ Traditional e-commerce is configured");
            } else {
                $this->error("❌ Traditional e-commerce is NOT configured:");
                if (!$config->is_configured) {
                    $this->line("  - is_configured = false");
                }
                if (empty($config->google_sheets_url)) {
                    $this->line("  - Google Sheets URL is missing");
                }
            }

            $this->info("----------------------------------------");
            $this->info("🎉 Configuration check completed!");

            // Show raw config for debugging
            if ($this->option('verbose')) {
                $this->info("📋 Raw Configuration:");
                $this->line(json_encode($config->toArray(), JSON_PRETTY_PRINT));
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
