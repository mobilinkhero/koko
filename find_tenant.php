<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Finding Tenant Information\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Check if there's an ecommerce configuration
    $configs = \App\Models\Tenant\EcommerceConfiguration::all();
    
    if ($configs->isEmpty()) {
        echo "❌ No e-commerce configurations found in database\n";
        echo "\nPlease complete the e-commerce setup first:\n";
        echo "1. Go to your admin panel\n";
        echo "2. Navigate to E-commerce Setup\n";
        echo "3. Complete the configuration wizard\n";
    } else {
        echo "✅ Found " . $configs->count() . " e-commerce configuration(s)\n\n";
        
        foreach ($configs as $config) {
            echo "Tenant ID: {$config->tenant_id}\n";
            echo "   - Configured: " . ($config->is_configured ? 'Yes' : 'No') . "\n";
            echo "   - Google Sheets: " . ($config->google_sheets_url ? 'Set' : 'Not set') . "\n";
            echo "   - Currency: {$config->currency}\n";
            echo "   - Products: " . \App\Models\Tenant\Product::where('tenant_id', $config->tenant_id)->count() . "\n";
            echo "\n";
            
            if ($config->isFullyConfigured()) {
                echo "✅ This tenant is ready for testing!\n";
                echo "\nRun this command to test:\n";
                echo "php artisan ecommerce:test-bot shop --tenant={$config->tenant_id}\n";
            } else {
                echo "⚠️  This tenant needs configuration\n";
            }
            echo "\n" . str_repeat("-", 50) . "\n\n";
        }
    }
    
    // Also check for tenants table
    echo "\n📊 Checking tenants table...\n";
    $tenants = DB::table('tenants')->get();
    
    if ($tenants->isEmpty()) {
        echo "❌ No tenants found\n";
    } else {
        echo "✅ Found " . $tenants->count() . " tenant(s):\n\n";
        foreach ($tenants as $tenant) {
            echo "   - ID: {$tenant->id}\n";
            if (isset($tenant->name)) echo "     Name: {$tenant->name}\n";
            if (isset($tenant->domain)) echo "     Domain: {$tenant->domain}\n";
            if (isset($tenant->subdomain)) echo "     Subdomain: {$tenant->subdomain}\n";
            echo "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
