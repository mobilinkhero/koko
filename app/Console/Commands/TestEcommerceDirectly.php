<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EcommerceOrderService;
use App\Models\Tenant\Contact;

class TestEcommerceDirectly extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ecommerce:test-direct {message} {--tenant=1} {--phone=923306055177}';

    /**
     * The console command description.
     */
    protected $description = 'Test ecommerce service directly bypassing webhook';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = (int) $this->option('tenant');
        $phone = $this->option('phone');
        $message = $this->argument('message');

        $this->info("🚀 Testing Ecommerce Service Directly");
        $this->info("Tenant ID: {$tenantId}");
        $this->info("Phone: {$phone}");
        $this->info("Message: {$message}");
        $this->info("----------------------------------------");

        try {
            // Get tenant subdomain
            $tenant = \App\Models\Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("❌ Tenant not found: {$tenantId}");
                return 1;
            }

            $this->info("✅ Tenant found: {$tenant->subdomain}");

            // Set tenant context manually
            session(['current_tenant_id' => $tenant->id]);
            config(['app.current_tenant_id' => $tenant->id]);

            // Get or create contact
            $contact = Contact::fromTenant($tenant->subdomain)->firstOrCreate(
                ['phone' => $phone, 'tenant_id' => $tenantId],
                [
                    'firstname' => 'Test', 
                    'lastname' => 'Customer', 
                    'type' => 'guest',
                    'status_id' => 1, 
                    'source_id' => 1, 
                    'addedfrom' => 1
                ]
            );

            $this->info("✅ Contact found/created: {$contact->id}");
            
            // Create ecommerce service
            $this->info("📞 Creating EcommerceOrderService...");
            $ecommerceService = new EcommerceOrderService($tenantId);
            
            $this->info("📞 Calling processMessage...");
            
            // Call processMessage
            $result = $ecommerceService->processMessage($message, $contact);
            
            $this->info("📞 Result received:");
            $this->info("Handled: " . ($result['handled'] ? 'YES' : 'NO'));
            $this->info("Response: " . ($result['response'] ?? 'No response'));
            
            if (!empty($result['buttons'])) {
                $this->info("Buttons: " . count($result['buttons']));
            }
            
            $this->info("----------------------------------------");
            $this->info("📋 Full Result:");
            $this->line(json_encode($result, JSON_PRETTY_PRINT));

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
            $this->error("File: " . $e->getFile());
            $this->error("Line: " . $e->getLine());
            $this->error("----------------------------------------");
            $this->error("Stack trace:");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
