<?php

namespace App\Services;

use App\Models\Tenant\TenantSheetConfiguration;
use App\Services\EcommerceLogger;
use Carbon\Carbon;

/**
 * Dynamic Sheet Mapper Service
 * 
 * Handles automatic detection and mapping of Google Sheets columns
 * to database fields, enabling universal structure support.
 */
class DynamicSheetMapperService
{
    protected $tenantId;
    protected $sheetType;
    protected $configuration;

    public function __construct(int $tenantId, string $sheetType = 'products')
    {
        $this->tenantId = $tenantId;
        $this->sheetType = $sheetType;
        $this->loadOrCreateConfiguration();
    }

    /**
     * Load or create configuration for this tenant
     */
    protected function loadOrCreateConfiguration(): void
    {
        $this->configuration = TenantSheetConfiguration::firstOrCreate(
            [
                'tenant_id' => $this->tenantId,
                'sheet_type' => $this->sheetType
            ],
            [
                'auto_detect_columns' => true,
                'allow_custom_fields' => true,
                'strict_mode' => false,
                'detection_status' => 'pending',
            ]
        );
    }

    /**
     * Detect and map columns from sheet headers
     */
    public function detectAndMapColumns(array $headers): array
    {
        try {
            EcommerceLogger::info('🔍 DYNAMIC-MAPPER: Detecting columns from sheet', [
                'tenant_id' => $this->tenantId,
                'sheet_type' => $this->sheetType,
                'headers' => $headers,
                'total_columns' => count($headers)
            ]);

            // Clean headers
            $cleanedHeaders = array_map(function($header) {
                return trim($header);
            }, $headers);

            // Auto-map columns
            $columnMapping = $this->configuration->autoMapColumns($cleanedHeaders);
            
            // Update configuration
            $this->configuration->update([
                'detected_columns' => $cleanedHeaders,
                'column_mapping' => $columnMapping,
                'total_columns_detected' => count($cleanedHeaders),
                'mapped_columns_count' => count($columnMapping),
                'detection_status' => 'detected',
                'last_detection_at' => Carbon::now(),
            ]);

            EcommerceLogger::info('✅ DYNAMIC-MAPPER: Columns detected and mapped', [
                'tenant_id' => $this->tenantId,
                'total_detected' => count($cleanedHeaders),
                'total_mapped' => count($columnMapping),
                'mappings' => $columnMapping
            ]);

            return [
                'success' => true,
                'detected_columns' => $cleanedHeaders,
                'column_mapping' => $columnMapping,
                'custom_fields' => $this->extractCustomFields($columnMapping),
                'has_required_fields' => $this->configuration->hasRequiredMappings(),
                'summary' => $this->configuration->getSummary()
            ];

        } catch (\Exception $e) {
            EcommerceLogger::error('❌ DYNAMIC-MAPPER: Column detection failed', [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to detect columns: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Map a single row of sheet data to database fields
     */
    public function mapRowToProduct(array $sheetRow, array $headers): array
    {
        try {
            EcommerceLogger::info('🗺️ Starting mapRowToProduct', [
                'tenant_id' => $this->tenantId,
                'headers_count' => count($headers),
                'row_count' => count($sheetRow),
                'headers' => $headers,
                'row' => $sheetRow
            ]);
            
            // Combine headers with row data
            if (count($headers) !== count($sheetRow)) {
                EcommerceLogger::warning('⚠️ Header/Row count mismatch', [
                    'headers_count' => count($headers),
                    'row_count' => count($sheetRow)
                ]);
            }
            
            $rowData = array_combine($headers, $sheetRow);
            
            EcommerceLogger::info('🔗 Combined row data', [
                'row_data' => $rowData
            ]);
            
            // Store ALL sheet data in meta_data
            $allSheetData = [];
            foreach ($rowData as $header => $value) {
                // Clean header name for key
                $cleanKey = str_replace([' ', '-'], '_', strtolower(trim($header)));
                $allSheetData[$cleanKey] = $value;
            }
            
            // Core product data - only essentials for database functionality
            $productData = [
                'tenant_id' => $this->tenantId,
                'meta_data' => $allSheetData, // Store EVERYTHING here
            ];

            // Only map absolute essentials for searching/filtering
            // Find name
            $nameFields = ['title', 'name', 'Title', 'Name', 'Product Name', 'product_name'];
            foreach ($nameFields as $field) {
                if (isset($rowData[$field]) && !empty($rowData[$field])) {
                    $productData['name'] = $rowData[$field];
                    break;
                }
            }
            
            // Find SKU
            $skuFields = ['product_iD', 'product_id', 'SKU', 'sku', 'Code', 'code'];
            foreach ($skuFields as $field) {
                if (isset($rowData[$field]) && !empty($rowData[$field])) {
                    $productData['sku'] = $rowData[$field];
                    break;
                }
            }
            
            // Find Price (for sorting/filtering)
            $priceFields = ['selling_price', 'Selling Price', 'Price', 'price'];
            foreach ($priceFields as $field) {
                if (isset($rowData[$field]) && !empty($rowData[$field])) {
                    $productData['price'] = (float) $rowData[$field];
                    break;
                }
            }
            
            // Find Stock
            $stockFields = ['quantity', 'Quantity', 'Stock', 'stock_quantity'];
            foreach ($stockFields as $field) {
                if (isset($rowData[$field]) && !empty($rowData[$field])) {
                    $productData['stock_quantity'] = (int) $rowData[$field];
                    break;
                }
            }
            
            // Find Status
            $statusFields = ['status', 'Status'];
            foreach ($statusFields as $field) {
                if (isset($rowData[$field]) && !empty($rowData[$field])) {
                    $status = strtolower(trim($rowData[$field]));
                    $productData['status'] = in_array($status, ['active', 'inactive', 'draft']) ? $status : 'active';
                    break;
                }
            }
            
            // Find Tags
            $tagFields = ['tags', 'Tags'];
            foreach ($tagFields as $field) {
                if (isset($rowData[$field]) && !empty($rowData[$field])) {
                    $productData['tags'] = $this->parseArrayField($rowData[$field]);
                    break;
                }
            }

            EcommerceLogger::info("  📦 Stored complete sheet data in meta_data", [
                'fields_count' => count($allSheetData)
            ]);

            // Set sync metadata
            $productData['sync_status'] = 'synced';
            $productData['last_synced_at'] = Carbon::now();
            
            // Ensure required fields exist
            if (!isset($productData['name']) || empty($productData['name'])) {
                throw new \Exception('Product name is required but not found in mapping or is empty');
            }
            
            if (!isset($productData['sku'])) {
                // Generate SKU from name if not provided
                $productData['sku'] = 'AUTO-' . strtoupper(substr(md5($productData['name']), 0, 8));
                EcommerceLogger::warning('⚠️ SKU not found, generated automatically', [
                    'generated_sku' => $productData['sku']
                ]);
            }
            
            if (!isset($productData['price']) || $productData['price'] === null) {
                $productData['price'] = 0;
                EcommerceLogger::warning('⚠️ Price not found, set to 0', [
                    'product_name' => $productData['name']
                ]);
            }
            
            // Ensure status is NEVER null
            if (!isset($productData['status']) || $productData['status'] === null || $productData['status'] === '') {
                $productData['status'] = 'active';
                EcommerceLogger::info('ℹ️ Status was empty, defaulted to active', [
                    'product_name' => $productData['name']
                ]);
            }

            EcommerceLogger::info('✅ Final mapped product data', [
                'product_data' => $productData
            ]);

            return $productData;
            
        } catch (\Exception $e) {
            EcommerceLogger::error('❌ mapRowToProduct failed', [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
                'headers' => $headers,
                'row' => $sheetRow,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Cast value to appropriate type based on field
     */
    protected function castValue(string $field, $value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Numeric fields
        if (in_array($field, ['price', 'sale_price', 'cost_price', 'weight'])) {
            return (float) $value;
        }

        if (in_array($field, ['stock_quantity', 'low_stock_threshold', 'google_sheet_row_id'])) {
            return (int) $value;
        }

        // Boolean fields
        if (in_array($field, ['featured'])) {
            return in_array(strtolower($value), ['yes', 'true', '1', 'on']);
        }

        // Status field - NEVER return null, always have a default
        if ($field === 'status') {
            if (empty($value)) {
                return 'active'; // Default to active if empty
            }
            $status = strtolower(trim($value));
            return in_array($status, ['active', 'inactive', 'draft']) ? $status : 'active';
        }

        // Default: return as string
        return trim($value);
    }

    /**
     * Parse comma-separated values into array
     */
    protected function parseArrayField(string $value): array
    {
        if (empty($value)) {
            return [];
        }

        return array_map('trim', explode(',', $value));
    }

    /**
     * Extract custom fields from mapping
     */
    protected function extractCustomFields(array $mapping): array
    {
        $customFields = [];
        
        foreach ($mapping as $sheetColumn => $dbField) {
            if (str_starts_with($dbField, 'custom_')) {
                $customFields[] = [
                    'sheet_column' => $sheetColumn,
                    'db_field' => $dbField,
                    'label' => $this->generateLabel($dbField)
                ];
            }
        }
        
        return $customFields;
    }

    /**
     * Generate human-readable label from field name
     */
    protected function generateLabel(string $fieldName): string
    {
        $label = str_replace('custom_', '', $fieldName);
        $label = str_replace('_', ' ', $label);
        return ucwords($label);
    }

    /**
     * Get current configuration
     */
    public function getConfiguration(): TenantSheetConfiguration
    {
        return $this->configuration;
    }

    /**
     * Update column mapping manually
     */
    public function updateMapping(array $newMapping): bool
    {
        try {
            $this->configuration->update([
                'column_mapping' => $newMapping,
                'mapped_columns_count' => count($newMapping),
                'detection_status' => 'configured',
            ]);

            EcommerceLogger::info('✅ DYNAMIC-MAPPER: Mapping updated manually', [
                'tenant_id' => $this->tenantId,
                'new_mapping' => $newMapping
            ]);

            return true;
        } catch (\Exception $e) {
            EcommerceLogger::error('❌ DYNAMIC-MAPPER: Failed to update mapping', [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get configuration summary for UI display
     */
    public function getConfigurationSummary(): array
    {
        return [
            'is_configured' => $this->configuration->detection_status === 'configured' 
                             || $this->configuration->detection_status === 'detected',
            'has_required_fields' => $this->configuration->hasRequiredMappings(),
            'total_columns' => $this->configuration->total_columns_detected,
            'mapped_columns' => $this->configuration->mapped_columns_count,
            'custom_fields' => $this->configuration->getCustomFieldNames(),
            'detected_columns' => $this->configuration->detected_columns,
            'column_mapping' => $this->configuration->column_mapping,
            'last_detection' => $this->configuration->last_detection_at,
            'last_sync' => $this->configuration->last_sync_at,
        ];
    }

    /**
     * Reset configuration (for re-detection)
     */
    public function resetConfiguration(): bool
    {
        try {
            $this->configuration->update([
                'detected_columns' => null,
                'column_mapping' => null,
                'total_columns_detected' => 0,
                'mapped_columns_count' => 0,
                'detection_status' => 'pending',
            ]);

            EcommerceLogger::info('🔄 DYNAMIC-MAPPER: Configuration reset', [
                'tenant_id' => $this->tenantId
            ]);

            return true;
        } catch (\Exception $e) {
            EcommerceLogger::error('❌ DYNAMIC-MAPPER: Failed to reset configuration', [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
