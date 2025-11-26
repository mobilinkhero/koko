<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Tenant Sheet Configuration Model
 * 
 * Stores dynamic column mappings for each tenant's Google Sheets,
 * enabling universal structure support across all tenants.
 */
class TenantSheetConfiguration extends Model
{
    protected $fillable = [
        'tenant_id',
        'sheet_type',
        'sheet_name',
        'sheet_id',
        'column_mapping',
        'detected_columns',
        'required_field_mapping',
        'custom_fields_config',
        'column_types',
        'auto_detect_columns',
        'allow_custom_fields',
        'strict_mode',
        'detection_status',
        'total_columns_detected',
        'mapped_columns_count',
        'last_detection_at',
        'last_sync_at',
    ];

    protected $casts = [
        'column_mapping' => 'array',
        'detected_columns' => 'array',
        'required_field_mapping' => 'array',
        'custom_fields_config' => 'array',
        'column_types' => 'array',
        'auto_detect_columns' => 'boolean',
        'allow_custom_fields' => 'boolean',
        'strict_mode' => 'boolean',
        'last_detection_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Get the default core field mappings for products
     */
    public static function getDefaultProductFieldMappings(): array
    {
        return [
            // Core fields (required for basic functionality)
            'core_fields' => [
                'name' => ['Name', 'Product Name', 'Title', 'Product', 'title'],
                'price' => ['Price', 'Product Price', 'Cost', 'Amount', 'Selling Price', 'selling_price'],
                'sku' => ['SKU', 'Product Code', 'Code', 'Item Code', 'product_iD', 'Product ID'],
            ],
            
            // Optional standard fields
            'optional_fields' => [
                'description' => ['Description', 'Details', 'Product Description'],
                'sale_price' => ['Sale Price', 'Discounted Price', 'Offer Price'],
                'stock_quantity' => ['Stock', 'Stock Quantity', 'Qty', 'Quantity', 'Available'],
                'category' => ['Category', 'Product Category', 'Type'],
                'subcategory' => ['Subcategory', 'Sub Category', 'Subtype'],
                'status' => ['Status', 'Product Status', 'Active'],
                'featured' => ['Featured', 'Is Featured', 'Highlight'],
                'weight' => ['Weight', 'Product Weight'],
                'tags' => ['Tags', 'Keywords', 'Labels'],
                'images' => ['Images', 'Image URLs', 'Photos', 'Images (URLs)'],
            ]
        ];
    }

    /**
     * Auto-map columns based on similarity
     */
    public function autoMapColumns(array $detectedColumns): array
    {
        $mappings = [];
        $defaultMappings = self::getDefaultProductFieldMappings();
        
        foreach ($detectedColumns as $column) {
            $columnLower = strtolower(trim($column));
            
            // Check core fields first
            foreach ($defaultMappings['core_fields'] as $dbField => $possibleNames) {
                foreach ($possibleNames as $possibleName) {
                    if ($columnLower === strtolower($possibleName)) {
                        $mappings[$column] = $dbField;
                        continue 3; // Skip to next detected column
                    }
                }
            }
            
            // Check optional fields
            foreach ($defaultMappings['optional_fields'] as $dbField => $possibleNames) {
                foreach ($possibleNames as $possibleName) {
                    if ($columnLower === strtolower($possibleName)) {
                        $mappings[$column] = $dbField;
                        continue 3;
                    }
                }
            }
            
            // If no match found, mark as custom field
            if ($this->allow_custom_fields) {
                $customFieldName = 'custom_' . strtolower(str_replace([' ', '-'], '_', $column));
                $mappings[$column] = $customFieldName;
            }
        }
        
        return $mappings;
    }

    /**
     * Get mapped value for a database field from sheet data
     */
    public function getMappedValue(array $sheetRow, string $dbField)
    {
        $mapping = $this->column_mapping ?? [];
        
        // Find which sheet column maps to this db field
        foreach ($mapping as $sheetColumn => $mappedField) {
            if ($mappedField === $dbField) {
                return $sheetRow[$sheetColumn] ?? null;
            }
        }
        
        return null;
    }

    /**
     * Get all custom field values from a sheet row
     */
    public function getCustomFieldValues(array $sheetRow): array
    {
        $customFields = [];
        $mapping = $this->column_mapping ?? [];
        
        foreach ($mapping as $sheetColumn => $mappedField) {
            if (str_starts_with($mappedField, 'custom_')) {
                $customFields[$mappedField] = $sheetRow[$sheetColumn] ?? null;
            }
        }
        
        return $customFields;
    }

    /**
     * Validate if all required fields are mapped
     */
    public function hasRequiredMappings(): bool
    {
        $mapping = $this->column_mapping ?? [];
        $requiredFields = ['name', 'price', 'sku'];
        
        foreach ($requiredFields as $field) {
            if (!in_array($field, $mapping)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get configuration summary
     */
    public function getSummary(): array
    {
        return [
            'sheet_type' => $this->sheet_type,
            'total_columns' => $this->total_columns_detected,
            'mapped_columns' => $this->mapped_columns_count,
            'has_required_fields' => $this->hasRequiredMappings(),
            'custom_fields_count' => count($this->getCustomFieldNames()),
            'last_detection' => $this->last_detection_at?->diffForHumans(),
            'status' => $this->detection_status,
        ];
    }

    /**
     * Get list of custom field names
     */
    public function getCustomFieldNames(): array
    {
        $mapping = $this->column_mapping ?? [];
        $customFields = [];
        
        foreach ($mapping as $sheetColumn => $mappedField) {
            if (str_starts_with($mappedField, 'custom_')) {
                $customFields[] = [
                    'sheet_column' => $sheetColumn,
                    'field_name' => $mappedField,
                    'label' => str_replace('_', ' ', ucfirst(substr($mappedField, 7)))
                ];
            }
        }
        
        return $customFields;
    }
}
