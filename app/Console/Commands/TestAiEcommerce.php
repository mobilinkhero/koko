<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiEcommerceService;
use App\Models\Tenant\EcommerceConfiguration;
use App\Models\Tenant\Contact;

class TestAiEcommerce extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ecommerce:test-ai {message} {--tenant=1} {--phone=1234567890}';

    /**
     * The console command description.
     */
    protected $description = 'Test AI-powered e-commerce bot with a sample message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');
        $phone = $this->option('phone');
        $message = $this->argument('message');

        // Initialize tenant context
        try {
            $tenant = \App\Models\Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("Tenant {$tenantId} not found");
                return 1;
            }

            // Manually set tenant context for this command
            session(['current_tenant_id' => $tenant->id]);
            config(['app.current_tenant_id' => $tenant->id]);
            
            $this->info("🤖 Testing AI E-commerce Bot");
            $this->info("Tenant: {$tenantId}");
            $this->info("Phone: {$phone}");
            $this->info("Message: {$message}");
            $this->info("----------------------------------------");

            // Check AI configuration
            $config = EcommerceConfiguration::where('tenant_id', $tenantId)->first();
            
            if (!$config) {
                $this->error("❌ E-commerce not configured for tenant {$tenantId}");
                return 1;
            }

            if (!$config->ai_powered_mode) {
                $this->error("❌ AI mode is not enabled");
                $this->info("💡 Enable AI mode in the dashboard settings");
                return 1;
            }

            if (empty($config->openai_api_key)) {
                $this->error("❌ OpenAI API key not configured");
                return 1;
            }

            if (empty($config->google_sheets_url)) {
                $this->error("❌ Google Sheets URL not configured");
                return 1;
            }

            $this->info("✅ AI Configuration validated");
            $this->info("Model: " . ($config->openai_model ?: 'gpt-3.5-turbo'));
            $this->info("Temperature: " . ($config->ai_temperature ?: 0.7));
            $this->info("Direct Sheets: " . ($config->direct_sheets_integration ? 'Yes' : 'No'));

            // Get tenant subdomain for proper table name
            $tenantSubdomain = $tenant->subdomain;
            
            // Create or get test contact using proper fromTenant method
            $contact = Contact::fromTenant($tenantSubdomain)->firstOrCreate(
                ['phone' => $phone, 'tenant_id' => $tenantId],
                [
                    'firstname' => 'Test',
                    'lastname' => 'Customer',
                    'type' => 'guest',
                    'status_id' => 1, // Default status
                    'source_id' => 1, // Default source
                    'addedfrom' => 1 // Default user
                ]
            );

            $this->info("👤 Using contact: {$contact->firstname} {$contact->lastname} ({$contact->phone})");
            $this->info("----------------------------------------");

            // Test AI service
            $aiService = new AiEcommerceService($tenantId);
            
            $this->info("🧠 Processing with AI...");
            $startTime = microtime(true);
            
            $result = $aiService->processMessage($message, $contact);
            
            $endTime = microtime(true);
            $processingTime = round(($endTime - $startTime) * 1000, 2);

            $this->info("⏱️  Processing time: {$processingTime}ms");
            $this->info("----------------------------------------");

            if ($result['handled']) {
                $this->info("✅ AI handled the message successfully!");
                $this->info("📝 Response:");
                $this->line($result['response']);
                
                if (!empty($result['buttons'])) {
                    $this->info("\n🔘 Buttons:");
                    foreach ($result['buttons'] as $button) {
                        $this->line("  - {$button['text']} (ID: {$button['id']})");
                    }
                }

                if (!empty($result['actions'])) {
                    $this->info("\n⚡ Actions to execute:");
                    foreach ($result['actions'] as $action) {
                        $this->line("  - Type: {$action['type']}");
                        if (isset($action['data'])) {
                            $this->line("    Data: " . json_encode($action['data'], JSON_PRETTY_PRINT));
                        }
                    }
                }
            } else {
                $this->error("❌ AI could not handle the message");
                $this->error("Response: " . $result['response']);
            }

            $this->info("----------------------------------------");
            $this->info("🎉 Test completed!");

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
