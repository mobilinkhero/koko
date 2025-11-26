<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Order Model for E-commerce
 * Synced with Google Sheets
 */
class Order extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'google_sheet_row_id',
        'order_number',
        'contact_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'status',
        'items',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'payment_method',
        'payment_status',
        'payment_details',
        'notes',
        'delivery_date',
        'tracking_number',
        'whatsapp_message_id',
        'source',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'tenant_id' => 'int',
        'contact_id' => 'int',
        'google_sheet_row_id' => 'int',
        'items' => 'json',
        'payment_details' => 'json',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'delivery_date' => 'date',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Order statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Payment statuses
     */
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_REFUNDED = 'refunded';
    const PAYMENT_PARTIAL = 'partial';

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get contact relationship
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get order items with product details
     */
    public function getOrderItemsAttribute()
    {
        $items = $this->items ?? [];
        $productIds = collect($items)->pluck('product_id')->filter();
        
        if ($productIds->isEmpty()) {
            return $items;
        }

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        
        return collect($items)->map(function ($item) use ($products) {
            if (isset($item['product_id']) && isset($products[$item['product_id']])) {
                $product = $products[$item['product_id']];
                $item['product_name'] = $product->name;
                $item['product_image'] = $product->primary_image;
                $item['product_sku'] = $product->sku;
            }
            return $item;
        })->toArray();
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return collect($this->items ?? [])->sum('quantity');
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute(): string
    {
        return ($this->currency ?? '$') . ' ' . number_format($this->total_amount, 2);
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Check if order can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->status === self::STATUS_DELIVERED && 
               $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * Scope for orders by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for recent orders
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope for orders by payment status
     */
    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Update order status
     */
    public function updateStatus(string $status, ?string $notes = null): bool
    {
        $validStatuses = [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_REFUNDED
        ];

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $this->status = $status;
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          Carbon::now()->format('Y-m-d H:i:s') . ': ' . $notes;
        }

        return $this->save();
    }

    /**
     * Add item to order
     */
    public function addItem(array $item): void
    {
        $items = $this->items ?? [];
        $items[] = $item;
        $this->items = $items;
        $this->calculateTotals();
    }

    /**
     * Remove item from order
     */
    public function removeItem(int $index): void
    {
        $items = $this->items ?? [];
        if (isset($items[$index])) {
            unset($items[$index]);
            $this->items = array_values($items);
            $this->calculateTotals();
        }
    }

    /**
     * Calculate order totals
     */
    public function calculateTotals(): void
    {
        $items = $this->items ?? [];
        $subtotal = collect($items)->sum(function ($item) {
            return ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
        });

        $this->subtotal = $subtotal;
        $this->tax_amount = $subtotal * 0.1; // 10% tax (configurable)
        $this->total_amount = $this->subtotal + $this->tax_amount + 
                            ($this->shipping_amount ?? 0) - ($this->discount_amount ?? 0);
    }
}
