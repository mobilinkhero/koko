<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Traits\BelongsToTenant;

/**
 * Product Model for E-commerce
 * Synced with Google Sheets
 */
class Product extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'google_sheet_row_id',
        'sku',
        'name',
        'description',
        'price',
        'sale_price',
        'cost_price',
        'stock_quantity',
        'low_stock_threshold',
        'category',
        'subcategory',
        'tags',
        'images',
        'weight',
        'dimensions',
        'status',
        'featured',
        'meta_data',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'tenant_id' => 'int',
        'google_sheet_row_id' => 'int',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'int',
        'low_stock_threshold' => 'int',
        'weight' => 'decimal:2',
        'tags' => 'array',
        'images' => 'array',
        'dimensions' => 'array',
        'meta_data' => 'array', // Used for dynamic custom fields from Google Sheets
        'featured' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get effective price (sale price if available, otherwise regular price)
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price && $this->sale_price < $this->price 
            ? (float) $this->sale_price 
            : (float) $this->price;
    }

    /**
     * Check if product is on sale
     */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price && $this->sale_price < $this->price;
    }

    /**
     * Check if product is low in stock
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= ($this->low_stock_threshold ?? 5);
    }

    /**
     * Check if product is in stock
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->effective_price, 2);
    }

    /**
     * Get primary image
     */
    public function getPrimaryImageAttribute(): ?string
    {
        $images = $this->images ?? [];
        return is_array($images) && count($images) > 0 ? $images[0] : null;
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for in-stock products
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope for featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for products by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Reduce stock quantity
     */
    public function reduceStock(int $quantity): bool
    {
        if ($this->stock_quantity >= $quantity) {
            $this->decrement('stock_quantity', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Restore stock quantity
     */
    public function restoreStock(int $quantity): bool
    {
        $this->increment('stock_quantity', $quantity);
        return true;
    }

    /**
     * Get related products (same category)
     */
    public function getRelatedProducts($limit = 4)
    {
        return static::where('tenant_id', $this->tenant_id)
            ->where('category', $this->category)
            ->where('id', '!=', $this->id)
            ->where('status', 'active')
            ->inStock()
            ->limit($limit)
            ->get();
    }

    /**
     * Get a custom field value from meta_data
     */
    public function getCustomField(string $fieldName, $default = null)
    {
        $metaData = $this->meta_data ?? [];
        return $metaData[$fieldName] ?? $default;
    }

    /**
     * Set a custom field value in meta_data
     */
    public function setCustomField(string $fieldName, $value): void
    {
        $metaData = $this->meta_data ?? [];
        $metaData[$fieldName] = $value;
        $this->meta_data = $metaData;
    }

    /**
     * Get all custom fields
     */
    public function getCustomFieldsAttribute(): array
    {
        $metaData = $this->meta_data ?? [];
        $customFields = [];
        
        foreach ($metaData as $key => $value) {
            if (str_starts_with($key, 'custom_')) {
                $customFields[$key] = $value;
            }
        }
        
        return $customFields;
    }

    /**
     * Check if product has custom fields
     */
    public function hasCustomFields(): bool
    {
        return !empty($this->custom_fields);
    }
}
