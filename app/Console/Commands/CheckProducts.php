<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\Product;
use App\Services\EcommerceLogger;

class CheckProducts extends Command
{
    protected $signature = 'ecommerce:check-products {--tenant=1}';
    protected $description = 'Check products in the database for a tenant';

    public function handle()
    {
        $tenantId = (int) $this->option('tenant');
        
        $this->info("🔍 Checking products for tenant {$tenantId}...");
        
        // Check total products
        $totalProducts = Product::where('tenant_id', $tenantId)->count();
        $this->info("📊 Total products: {$totalProducts}");
        
        // Check active products
        $activeProducts = Product::where('tenant_id', $tenantId)->active()->count();
        $this->info("✅ Active products: {$activeProducts}");
        
        // Check in-stock products
        $inStockProducts = Product::where('tenant_id', $tenantId)->active()->inStock()->count();
        $this->info("📦 In-stock products: {$inStockProducts}");
        
        // Show sample products
        $sampleProducts = Product::where('tenant_id', $tenantId)
            ->active()
            ->inStock()
            ->limit(5)
            ->get(['id', 'name', 'price', 'stock_quantity', 'category']);
            
        if ($sampleProducts->count() > 0) {
            $this->info("\n📋 Sample Products:");
            $headers = ['ID', 'Name', 'Price', 'Stock', 'Category'];
            $rows = [];
            
            foreach ($sampleProducts as $product) {
                $rows[] = [
                    $product->id,
                    $product->name,
                    '$' . number_format($product->price, 2),
                    $product->stock_quantity,
                    $product->category
                ];
            }
            
            $this->table($headers, $rows);
        } else {
            $this->warn("⚠️ No products found!");
            
            // Suggest creating sample products
            $this->info("\n💡 To create sample products, run:");
            $this->info("php artisan ecommerce:create-sample-products --tenant={$tenantId}");
        }
        
        return 0;
    }
}
