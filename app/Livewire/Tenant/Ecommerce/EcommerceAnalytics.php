<?php

namespace App\Livewire\Tenant\Ecommerce;

use App\Models\Tenant\Order;
use App\Models\Tenant\Product;
use App\Services\FeatureService;
use Carbon\Carbon;
use Livewire\Component;

class EcommerceAnalytics extends Component
{
    public $dateRange = '30_days';
    public $stats = [];
    public $chartData = [];

    public function mount(FeatureService $featureService)
    {
        if (!checkPermission('tenant.ecommerce.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        // Check if user has access to Ecommerce Bot feature
        if (!$featureService->hasAccess('ecommerce_bot')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note') . ' - Ecommerce Bot feature is not available in your plan.'], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $this->loadAnalytics();
    }

    public function updatedDateRange()
    {
        $this->loadAnalytics();
    }

    public function loadAnalytics()
    {
        $tenantId = tenant_id();
        $startDate = $this->getStartDate();

        $this->stats = [
            'total_orders' => Order::where('tenant_id', $tenantId)
                ->where('created_at', '>=', $startDate)
                ->count(),
            
            'total_revenue' => Order::where('tenant_id', $tenantId)
                ->where('created_at', '>=', $startDate)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            
            'average_order_value' => Order::where('tenant_id', $tenantId)
                ->where('created_at', '>=', $startDate)
                ->where('payment_status', 'paid')
                ->avg('total_amount'),
            
            'top_products' => $this->getTopProducts($startDate),
            'daily_sales' => $this->getDailySales($startDate),
        ];
    }

    protected function getStartDate()
    {
        return match($this->dateRange) {
            '7_days' => Carbon::now()->subDays(7),
            '30_days' => Carbon::now()->subDays(30),
            '90_days' => Carbon::now()->subDays(90),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30)
        };
    }

    protected function getTopProducts($startDate)
    {
        return Order::where('tenant_id', tenant_id())
            ->where('created_at', '>=', $startDate)
            ->get()
            ->flatMap(function ($order) {
                return collect($order->items ?? []);
            })
            ->groupBy('product_id')
            ->map(function ($items, $productId) {
                $product = Product::find($productId);
                return [
                    'product_name' => $product ? $product->name : 'Unknown Product',
                    'total_quantity' => $items->sum('quantity'),
                    'total_revenue' => $items->sum('total')
                ];
            })
            ->sortByDesc('total_quantity')
            ->take(5)
            ->values();
    }

    protected function getDailySales($startDate)
    {
        return Order::where('tenant_id', tenant_id())
            ->where('created_at', '>=', $startDate)
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function render()
    {
        return view('livewire.tenant.ecommerce.analytics', [
            'stats' => $this->stats,
        ]);
    }
}
