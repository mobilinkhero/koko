<?php

/**
 * E-commerce System Test Script
 * Run this to verify your e-commerce setup is working
 * 
 * Usage: php test_ecommerce.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 E-commerce System Test\n";
echo str_repeat("=", 50) . "\n\n";

// Test 1: Check Products
echo "📦 Test 1: Checking Products...\n";
try {
    $productCount = \App\Models\Tenant\Product::count();
    echo "   ✅ Found {$productCount} products in database\n";
    
    if ($productCount > 0) {
        $sampleProduct = \App\Models\Tenant\Product::first();
        echo "   📝 Sample Product:\n";
        echo "      - Name: {$sampleProduct->name}\n";
        echo "      - Price: \${$sampleProduct->price}\n";
        echo "      - Stock: {$sampleProduct->stock_quantity}\n";
        echo "      - SKU: {$sampleProduct->sku}\n";
    } else {
        echo "   ⚠️  No products found. Please sync from Google Sheets.\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Check Configuration
echo "⚙️  Test 2: Checking E-commerce Configuration...\n";
try {
    $config = \App\Models\Tenant\EcommerceConfiguration::first();
    if ($config) {
        echo "   ✅ Configuration found\n";
        echo "      - Google Sheets URL: " . ($config->google_sheets_url ? "✓ Set" : "✗ Not set") . "\n";
        echo "      - Currency: {$config->currency}\n";
        echo "      - Tax Rate: {$config->tax_rate}%\n";
        echo "      - Payment Methods: " . count($config->payment_methods ?? []) . " configured\n";
        echo "      - Last Sync: " . ($config->last_sync_at ? $config->last_sync_at->diffForHumans() : "Never") . "\n";
    } else {
        echo "   ⚠️  No configuration found. Please complete setup.\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Check Service Account
echo "🔐 Test 3: Checking Service Account...\n";
try {
    $serviceAccountPath = base_path('google-service-account.json');
    if (file_exists($serviceAccountPath)) {
        echo "   ✅ Service Account JSON found\n";
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
        if ($serviceAccount && isset($serviceAccount['client_email'])) {
            echo "      - Email: {$serviceAccount['client_email']}\n";
            echo "      - Project: " . ($serviceAccount['project_id'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   ⚠️  Service Account JSON not found at: {$serviceAccountPath}\n";
        echo "      You can still use public sheet access.\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Check Recent Orders
echo "📋 Test 4: Checking Orders...\n";
try {
    $orderCount = \App\Models\Tenant\Order::count();
    echo "   ✅ Found {$orderCount} orders in database\n";
    
    if ($orderCount > 0) {
        $recentOrder = \App\Models\Tenant\Order::latest()->first();
        echo "   📝 Most Recent Order:\n";
        echo "      - Order #: {$recentOrder->order_number}\n";
        echo "      - Status: {$recentOrder->status}\n";
        echo "      - Total: \${$recentOrder->total_amount}\n";
        echo "      - Date: {$recentOrder->created_at->diffForHumans()}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Check Categories
echo "🏷️  Test 5: Checking Product Categories...\n";
try {
    $categories = \App\Models\Tenant\Product::distinct()->pluck('category')->filter();
    if ($categories->count() > 0) {
        echo "   ✅ Found {$categories->count()} categories:\n";
        foreach ($categories as $category) {
            $count = \App\Models\Tenant\Product::where('category', $category)->count();
            echo "      - {$category}: {$count} products\n";
        }
    } else {
        echo "   ⚠️  No categories found. Consider organizing products into categories.\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Check Stock Levels
echo "📊 Test 6: Checking Stock Levels...\n";
try {
    $lowStock = \App\Models\Tenant\Product::where('stock_quantity', '<', 5)
                                         ->where('stock_quantity', '>', 0)
                                         ->count();
    $outOfStock = \App\Models\Tenant\Product::where('stock_quantity', '<=', 0)->count();
    $inStock = \App\Models\Tenant\Product::where('stock_quantity', '>=', 5)->count();
    
    echo "   ✅ Stock Status:\n";
    echo "      - In Stock: {$inStock} products\n";
    echo "      - Low Stock: {$lowStock} products\n";
    echo "      - Out of Stock: {$outOfStock} products\n";
    
    if ($lowStock > 0) {
        echo "   ⚠️  Low stock products:\n";
        $lowStockProducts = \App\Models\Tenant\Product::where('stock_quantity', '<', 5)
                                                       ->where('stock_quantity', '>', 0)
                                                       ->get(['name', 'stock_quantity']);
        foreach ($lowStockProducts as $product) {
            echo "      - {$product->name}: {$product->stock_quantity} left\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Summary
echo str_repeat("=", 50) . "\n";
echo "📊 Test Summary\n";
echo str_repeat("=", 50) . "\n";

$allGood = true;

if ($productCount > 0) {
    echo "✅ Products: Ready\n";
} else {
    echo "❌ Products: Need to sync from Google Sheets\n";
    $allGood = false;
}

if ($config && $config->google_sheets_url) {
    echo "✅ Configuration: Complete\n";
} else {
    echo "❌ Configuration: Incomplete\n";
    $allGood = false;
}

if (file_exists($serviceAccountPath)) {
    echo "✅ Service Account: Configured\n";
} else {
    echo "⚠️  Service Account: Not configured (optional)\n";
}

echo "\n";

if ($allGood) {
    echo "🎉 Your e-commerce system is ready to test!\n\n";
    echo "Next steps:\n";
    echo "1. Send 'shop' to your WhatsApp Business number\n";
    echo "2. Browse products and test ordering\n";
    echo "3. Check orders in admin panel\n";
} else {
    echo "⚠️  Please complete the setup before testing:\n";
    if ($productCount == 0) {
        echo "   - Sync products from Google Sheets\n";
    }
    if (!$config || !$config->google_sheets_url) {
        echo "   - Complete e-commerce configuration\n";
    }
}

echo "\n";
