<?php

namespace App\Livewire\Tenant\Ecommerce;

use App\Models\Tenant\EcommerceConfiguration;
use App\Models\Tenant\Order;
use App\Models\Tenant\Product;
use App\Services\GoogleSheetsService;
use App\Services\Loggers\EcommerceLogger;
use App\Services\FeatureService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EcommerceDashboard extends Component
{
    public $config;
    public $isConfigured = false;
    public $stats = [];

    public function mount(FeatureService $featureService)
    {
        // Check permissions
        if (!checkPermission('tenant.ecommerce.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        // Check if user has access to Ecommerce Bot feature
        if (!$featureService->hasAccess('ecommerce_bot')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note') . ' - Ecommerce Bot feature is not available in your plan.'], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $this->loadConfiguration();
        $this->loadStats();
    }

    public function loadConfiguration()
    {
        $this->config = EcommerceConfiguration::where('tenant_id', tenant_id())->first();
        $this->isConfigured = $this->config && $this->config->isFullyConfigured();
    }

    public function loadStats()
    {
        $tenantId = tenant_id();
        
        // Check if tenant table exists
        $tableService = new \App\Services\DynamicTenantTableService();
        $tableName = $tableService->getTenantTableName($tenantId);
        
        if (Schema::hasTable($tableName)) {
            // Use dynamic tenant table
            $totalProducts = DB::table($tableName)->count();
            
            // Try to count active products if status column exists
            $activeProducts = 0;
            if (Schema::hasColumn($tableName, 'status')) {
                $activeProducts = DB::table($tableName)->where('status', 'Active')->count();
            }
            
            $lowStockProducts = 0; // Not relevant for dynamic tables
        } else {
            // Fallback to 0 if table doesn't exist
            $totalProducts = 0;
            $activeProducts = 0;
            $lowStockProducts = 0;
        }
        
        $this->stats = [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'low_stock_products' => $lowStockProducts,
            
            'total_orders' => Order::where('tenant_id', $tenantId)->count(),
            'pending_orders' => Order::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'completed_orders' => Order::where('tenant_id', $tenantId)->where('status', 'delivered')->count(),
            
            'total_revenue' => Order::where('tenant_id', $tenantId)
                ->where('payment_status', 'paid')->sum('total_amount'),
            'monthly_revenue' => Order::where('tenant_id', $tenantId)
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [now()->startOfMonth(), now()])
                ->sum('total_amount'),
            
            'recent_orders' => Order::where('tenant_id', $tenantId)
                ->with('contact')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    public function redirectToSetup()
    {
        return redirect()->to(tenant_route('tenant.ecommerce.setup'));
    }

    public function syncNow()
    {
        if (!$this->isConfigured) {
            EcommerceLogger::warning('Sync attempted without configuration', [
                'tenant_id' => tenant_id(),
                'config_exists' => !is_null($this->config)
            ]);
            $this->notify(['type' => 'danger', 'message' => 'E-commerce not configured yet']);
            return;
        }

        try {
            EcommerceLogger::info('Manual sync started from dashboard', [
                'tenant_id' => tenant_id(),
                'user_id' => auth()->id()
            ]);

            $sheetsService = new GoogleSheetsService();
            $result = $sheetsService->syncProductsFromSheets();
            
            if ($result['success']) {
                EcommerceLogger::info('Manual sync successful', ['result' => $result]);
                $this->notify(['type' => 'success', 'message' => $result['message']]);
                $this->loadStats(); // Refresh stats
            } else {
                EcommerceLogger::error('Manual sync failed', ['result' => $result]);
                $this->notify(['type' => 'danger', 'message' => $result['message']]);
            }
        } catch (\Exception $e) {
            EcommerceLogger::error('Manual sync failed', [
                'tenant_id' => tenant_id(),
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            $this->notify(['type' => 'danger', 'message' => 'Sync failed: ' . $e->getMessage()]);
        }
    }

    public function clearAllProducts()
    {
        try {
            $tenantId = tenant_id();
            
            // Use dynamic table service
            $tableService = new \App\Services\DynamicTenantTableService();
            $tableName = $tableService->getTenantTableName($tenantId);
            
            $productCount = 0;
            if (Schema::hasTable($tableName)) {
                $productCount = DB::table($tableName)->count();
            }

            if ($productCount === 0) {
                $this->notify([
                    'type' => 'info',
                    'message' => 'No products to clear.'
                ]);
                return;
            }

            EcommerceLogger::info('Product clear initiated from dashboard', [
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'product_count' => $productCount,
                'table_name' => $tableName
            ]);

            // Drop the tenant table
            $tableService->dropTenantProductsTable($tenantId);

            // Also clear dynamic mapper configuration
            \App\Models\Tenant\TenantSheetConfiguration::where('tenant_id', $tenantId)
                ->where('sheet_type', 'products')
                ->delete();

            EcommerceLogger::info('Products table dropped successfully', [
                'tenant_id' => $tenantId,
                'deleted_count' => $productCount
            ]);

            $this->notify([
                'type' => 'success',
                'message' => "Successfully cleared {$productCount} products. Sync again to get products from your new sheet."
            ]);

            $this->loadStats(); // Refresh stats to show 0 products

        } catch (\Exception $e) {
            EcommerceLogger::error('Product clear failed', [
                'tenant_id' => tenant_id(),
                'exception' => $e->getMessage()
            ]);

            $this->notify([
                'type' => 'danger',
                'message' => 'Failed to clear products: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.tenant.ecommerce.dashboard', [
            'config' => $this->config,
            'isConfigured' => $this->isConfigured,
            'stats' => $this->stats,
        ]);
    }
}
