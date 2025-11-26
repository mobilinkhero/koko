<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\Product;

class CreateSampleProducts extends Command
{
    protected $signature = 'ecommerce:create-sample-products {--tenant=1} {--force}';
    protected $description = 'Create sample products for testing';

    public function handle()
    {
        $tenantId = (int) $this->option('tenant');
        $force = $this->option('force');
        
        // Check if products already exist
        $existingProducts = Product::where('tenant_id', $tenantId)->count();
        
        if ($existingProducts > 0 && !$force) {
            $this->warn("⚠️ Products already exist for tenant {$tenantId}!");
            $this->info("Use --force to create sample products anyway.");
            return 1;
        }
        
        $this->info("🚀 Creating sample products for tenant {$tenantId}...");
        
        $sampleProducts = [
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with long battery life. Perfect for office work and gaming.',
                'price' => 29.99,
                'stock_quantity' => 50,
                'category' => 'Electronics',
                'subcategory' => 'Computer Accessories',
                'sku' => 'WM001',
                'status' => 'active',
                'featured' => true,
            ],
            [
                'name' => 'Bluetooth Keyboard',
                'description' => 'Compact Bluetooth keyboard compatible with all devices. Sleek design with backlit keys.',
                'price' => 79.99,
                'sale_price' => 69.99,
                'stock_quantity' => 25,
                'category' => 'Electronics',
                'subcategory' => 'Computer Accessories',
                'sku' => 'BK002',
                'status' => 'active',
                'featured' => true,
            ],
            [
                'name' => 'USB-C Charging Cable',
                'description' => 'High-speed USB-C charging cable 1.5m length. Supports fast charging and data transfer.',
                'price' => 15.99,
                'stock_quantity' => 100,
                'category' => 'Electronics',
                'subcategory' => 'Cables & Adapters',
                'sku' => 'CC003',
                'status' => 'active',
                'featured' => false,
            ],
            [
                'name' => 'Adjustable Phone Stand',
                'description' => 'Universal adjustable phone stand for desk. Compatible with all phone sizes.',
                'price' => 12.99,
                'stock_quantity' => 75,
                'category' => 'Accessories',
                'subcategory' => 'Phone Accessories',
                'sku' => 'PS004',
                'status' => 'active',
                'featured' => false,
            ],
            [
                'name' => 'Laptop Cooling Pad',
                'description' => 'Laptop cooling pad with dual fans. Prevents overheating and improves performance.',
                'price' => 35.99,
                'stock_quantity' => 30,
                'category' => 'Electronics',
                'subcategory' => 'Laptop Accessories',
                'sku' => 'CP005',
                'status' => 'active',
                'featured' => false,
            ],
            [
                'name' => 'Portable Power Bank',
                'description' => '10000mAh portable power bank with fast charging. Multiple device compatibility.',
                'price' => 24.99,
                'stock_quantity' => 60,
                'category' => 'Electronics',
                'subcategory' => 'Power & Charging',
                'sku' => 'PB006',
                'status' => 'active',
                'featured' => true,
            ],
        ];
        
        foreach ($sampleProducts as $productData) {
            $productData['tenant_id'] = $tenantId;
            
            $product = Product::create($productData);
            $this->info("✅ Created: {$product->name} (#{$product->id})");
        }
        
        $this->info("\n🎉 Successfully created " . count($sampleProducts) . " sample products!");
        $this->info("📱 You can now test the AI bot with these products.");
        
        return 0;
    }
}
