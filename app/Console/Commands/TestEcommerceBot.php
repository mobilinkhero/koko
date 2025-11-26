<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EcommerceOrderService;
use App\Models\Tenant\Contact;
use App\Models\Tenant\EcommerceConfiguration;

class TestEcommerceBot extends Command
{
    protected $signature = 'ecommerce:test-bot {message=shop} {--tenant=}';
    protected $description = 'Test the e-commerce bot with a sample message';

    public function handle()
    {
        $message = $this->argument('message');
        $tenantId = $this->option('tenant');
        
        // If no tenant specified, try to find one
        if (!$tenantId) {
            $tenantId = function_exists('tenant_id') ? tenant_id() : null;
        }
        
        // Still no tenant? Find the first configured one
        if (!$tenantId) {
            $this->warn("No tenant ID specified. Looking for configured tenants...");
            $config = EcommerceConfiguration::where('is_configured', true)
                                           ->whereNotNull('google_sheets_url')
                                           ->first();
            
            if ($config) {
                $tenantId = $config->tenant_id;
                $this->info("✅ Found configured tenant: {$tenantId}");
            } else {
                $this->error("❌ No configured e-commerce tenants found.");
                $this->line("\nAvailable configurations:");
                $allConfigs = EcommerceConfiguration::all();
                if ($allConfigs->isEmpty()) {
                    $this->error("   No e-commerce configurations exist.");
                    $this->line("\nPlease complete e-commerce setup first.");
                } else {
                    foreach ($allConfigs as $cfg) {
                        $this->line("   - Tenant {$cfg->tenant_id}: " . 
                                   ($cfg->is_configured ? "Configured" : "Not configured"));
                    }
                    $this->line("\nRun: php artisan ecommerce:test-bot shop --tenant=<ID>");
                }
                return 1;
            }
        }

        $this->info("Testing E-commerce Bot");
        $this->info("Tenant ID: {$tenantId}");
        $this->info("Message: {$message}");
        $this->line(str_repeat('=', 50));

        // Check configuration
        $config = EcommerceConfiguration::where('tenant_id', $tenantId)->first();
        
        if (!$config) {
            $this->error("❌ No e-commerce configuration found for tenant {$tenantId}");
            return 1;
        }

        $this->info("✅ Configuration found");
        $this->info("   - Is Configured: " . ($config->is_configured ? 'Yes' : 'No'));
        $this->info("   - Google Sheets URL: " . ($config->google_sheets_url ? 'Set' : 'Not set'));
        $this->info("   - Fully Configured: " . ($config->isFullyConfigured() ? 'Yes' : 'No'));
        $this->line('');

        if (!$config->isFullyConfigured()) {
            $this->error("❌ E-commerce is not fully configured");
            return 1;
        }

        // Create a mock contact for testing
        $testContact = new Contact();
        $testContact->phone = '1234567890';
        $testContact->tenant_id = $tenantId;
        $testContact->firstname = 'Test';
        $testContact->lastname = 'Customer';
        $testContact->type = 'lead';
        $testContact->id = 999999; // Mock ID

        $this->info("✅ Using test contact: {$testContact->firstname} {$testContact->lastname}");
        $this->line('');

        // Test the bot
        try {
            $service = new EcommerceOrderService($tenantId);
            $result = $service->processMessage($message, $testContact);

            $this->line(str_repeat('=', 50));
            $this->info("Bot Response:");
            $this->line(str_repeat('=', 50));
            
            if ($result['handled']) {
                $this->info("✅ Message was handled by e-commerce bot");
                $this->line('');
                $this->line($result['response']);
            } else {
                $this->warn("⚠️  Message was NOT handled by e-commerce bot");
                if ($result['response']) {
                    $this->line($result['response']);
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
