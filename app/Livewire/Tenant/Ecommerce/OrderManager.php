<?php

namespace App\Livewire\Tenant\Ecommerce;

use App\Models\Tenant\Order;
use App\Models\Tenant\Product;
use App\Models\Tenant\Contact;
use App\Services\GoogleSheetsService;
use App\Services\FeatureService;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class OrderManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $paymentStatusFilter = 'all';
    public $dateRange = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    
    public $showOrderModal = false;
    public $viewingOrder = null;
    public $showCreateOrderModal = false;
    
    // Create Order Form
    public $orderForm = [
        'customer_name' => '',
        'customer_phone' => '',
        'customer_email' => '',
        'customer_address' => '',
        'payment_method' => 'cash_on_delivery',
        'notes' => '',
        'items' => [],
    ];
    
    public $newItem = [
        'product_id' => '',
        'quantity' => 1,
        'price' => '',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'paymentStatusFilter' => ['except' => 'all'],
        'dateRange' => ['except' => 'all'],
        'page' => ['except' => 1],
    ];

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
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingDateRange()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function viewOrder($orderId)
    {
        $this->viewingOrder = Order::where('tenant_id', tenant_id())
            ->with('contact')
            ->findOrFail($orderId);
        $this->showOrderModal = true;
    }

    public function updateOrderStatus($orderId, $status, $notes = null)
    {
        try {
            $order = Order::where('tenant_id', tenant_id())->findOrFail($orderId);
            $order->updateStatus($status, $notes);
            
            // Sync to Google Sheets
            $sheetsService = new GoogleSheetsService();
            $sheetsService->syncOrderToSheets($order);
            
            $this->notify(['type' => 'success', 'message' => 'Order status updated successfully']);
            
            if ($this->viewingOrder && $this->viewingOrder->id == $orderId) {
                $this->viewingOrder->refresh();
            }
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Error updating order: ' . $e->getMessage()]);
        }
    }

    public function updatePaymentStatus($orderId, $paymentStatus)
    {
        try {
            $order = Order::where('tenant_id', tenant_id())->findOrFail($orderId);
            $order->update(['payment_status' => $paymentStatus]);
            
            // Sync to Google Sheets
            $sheetsService = new GoogleSheetsService();
            $sheetsService->syncOrderToSheets($order);
            
            $this->notify(['type' => 'success', 'message' => 'Payment status updated successfully']);
            
            if ($this->viewingOrder && $this->viewingOrder->id == $orderId) {
                $this->viewingOrder->refresh();
            }
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Error updating payment status: ' . $e->getMessage()]);
        }
    }

    public function createOrder()
    {
        $this->resetOrderForm();
        $this->showCreateOrderModal = true;
    }

    public function addItemToOrder()
    {
        if (!$this->newItem['product_id']) {
            $this->addError('newItem.product_id', 'Please select a product');
            return;
        }

        $product = Product::where('tenant_id', tenant_id())->find($this->newItem['product_id']);
        if (!$product) {
            $this->addError('newItem.product_id', 'Product not found');
            return;
        }

        if ($this->newItem['quantity'] <= 0) {
            $this->addError('newItem.quantity', 'Quantity must be greater than 0');
            return;
        }

        if ($this->newItem['quantity'] > $product->stock_quantity) {
            $this->addError('newItem.quantity', 'Insufficient stock available');
            return;
        }

        $price = $this->newItem['price'] ?: $product->effective_price;

        $this->orderForm['items'][] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => $this->newItem['quantity'],
            'price' => $price,
            'total' => $price * $this->newItem['quantity'],
        ];

        // Reset new item form
        $this->newItem = [
            'product_id' => '',
            'quantity' => 1,
            'price' => '',
        ];
    }

    public function removeItemFromOrder($index)
    {
        if (isset($this->orderForm['items'][$index])) {
            unset($this->orderForm['items'][$index]);
            $this->orderForm['items'] = array_values($this->orderForm['items']);
        }
    }

    public function saveOrder()
    {
        $this->validate([
            'orderForm.customer_name' => 'required|string|max:255',
            'orderForm.customer_phone' => 'required|string|max:20',
            'orderForm.customer_email' => 'nullable|email',
            'orderForm.customer_address' => 'required|string',
            'orderForm.payment_method' => 'required|string',
        ]);

        if (empty($this->orderForm['items'])) {
            $this->addError('orderForm.items', 'Please add at least one item to the order');
            return;
        }

        try {
            // Calculate totals
            $subtotal = collect($this->orderForm['items'])->sum('total');
            $taxAmount = $subtotal * 0.1; // 10% tax (configurable)
            $totalAmount = $subtotal + $taxAmount;

            // Create order
            $order = Order::create([
                'tenant_id' => tenant_id(),
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $this->orderForm['customer_name'],
                'customer_phone' => $this->orderForm['customer_phone'],
                'customer_email' => $this->orderForm['customer_email'],
                'customer_address' => $this->orderForm['customer_address'],
                'status' => Order::STATUS_PENDING,
                'items' => $this->orderForm['items'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'currency' => 'USD',
                'payment_method' => $this->orderForm['payment_method'],
                'payment_status' => Order::PAYMENT_PENDING,
                'notes' => $this->orderForm['notes'],
                'source' => 'manual',
            ]);

            // Reduce stock for each item
            foreach ($this->orderForm['items'] as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->reduceStock($item['quantity']);
                }
            }

            // Sync to Google Sheets
            $sheetsService = new GoogleSheetsService();
            $sheetsService->syncOrderToSheets($order);

            $this->notify(['type' => 'success', 'message' => 'Order created successfully']);
            $this->showCreateOrderModal = false;
            $this->resetOrderForm();
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Error creating order: ' . $e->getMessage()]);
        }
    }

    public function resetOrderForm()
    {
        $this->orderForm = [
            'customer_name' => '',
            'customer_phone' => '',
            'customer_email' => '',
            'customer_address' => '',
            'payment_method' => 'cash_on_delivery',
            'notes' => '',
            'items' => [],
        ];
        $this->newItem = [
            'product_id' => '',
            'quantity' => 1,
            'price' => '',
        ];
    }

    public function closeModal()
    {
        $this->showOrderModal = false;
        $this->showCreateOrderModal = false;
        $this->viewingOrder = null;
        $this->resetOrderForm();
    }

    public function getOrderTotal()
    {
        return collect($this->orderForm['items'])->sum('total');
    }

    public function render()
    {
        $query = Order::where('tenant_id', tenant_id())
            ->with('contact');

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_email', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply payment status filter
        if ($this->paymentStatusFilter !== 'all') {
            $query->where('payment_status', $this->paymentStatusFilter);
        }

        // Apply date range filter
        if ($this->dateRange !== 'all') {
            switch ($this->dateRange) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()]);
                    break;
            }
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        $orders = $query->paginate(15);

        // Get available products for order creation
        $products = Product::where('tenant_id', tenant_id())
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        // Get stats
        $stats = [
            'total' => Order::where('tenant_id', tenant_id())->count(),
            'pending' => Order::where('tenant_id', tenant_id())->where('status', Order::STATUS_PENDING)->count(),
            'confirmed' => Order::where('tenant_id', tenant_id())->where('status', Order::STATUS_CONFIRMED)->count(),
            'delivered' => Order::where('tenant_id', tenant_id())->where('status', Order::STATUS_DELIVERED)->count(),
            'total_revenue' => Order::where('tenant_id', tenant_id())
                ->where('payment_status', Order::PAYMENT_PAID)
                ->sum('total_amount'),
        ];

        return view('livewire.tenant.ecommerce.order-manager', [
            'orders' => $orders,
            'products' => $products,
            'stats' => $stats,
            'orderStatuses' => [
                Order::STATUS_PENDING => 'Pending',
                Order::STATUS_CONFIRMED => 'Confirmed',
                Order::STATUS_PROCESSING => 'Processing',
                Order::STATUS_SHIPPED => 'Shipped',
                Order::STATUS_DELIVERED => 'Delivered',
                Order::STATUS_CANCELLED => 'Cancelled',
                Order::STATUS_REFUNDED => 'Refunded',
            ],
            'paymentStatuses' => [
                Order::PAYMENT_PENDING => 'Pending',
                Order::PAYMENT_PAID => 'Paid',
                Order::PAYMENT_FAILED => 'Failed',
                Order::PAYMENT_REFUNDED => 'Refunded',
                Order::PAYMENT_PARTIAL => 'Partial',
            ],
            'paymentMethods' => [
                'cash_on_delivery' => 'Cash on Delivery',
                'bank_transfer' => 'Bank Transfer',
                'upi' => 'UPI Payment',
                'credit_card' => 'Credit Card',
                'paypal' => 'PayPal',
            ],
        ]);
    }
}
