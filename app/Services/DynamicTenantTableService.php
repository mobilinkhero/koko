<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

class DynamicTenantTableService
{
    /**
     * Create a tenant-specific products table based on sheet structure
     */
    public function createTenantProductsTable(int $tenantId, array $headers): bool
    {
        try {
            $tableName = "tenant_{$tenantId}_products";
            
            Log::info("🔨 Starting table creation", [
                'table_name' => $tableName,
                'headers' => $headers,
                'header_count' => count($headers)
            ]);
            
            // Drop if exists (fresh start)
            if (Schema::hasTable($tableName)) {
                Schema::drop($tableName);
                Log::info("✅ Dropped existing table: {$tableName}");
            }
            
            // Prepare columns info for logging
            $columnsToCreate = [];
            
            // Create new table with dynamic columns
            Schema::create($tableName, function (Blueprint $table) use ($headers, &$columnsToCreate) {
                // Standard columns
                $table->id();
                $table->timestamps();
                
                // Dynamic columns from sheet headers
                foreach ($headers as $header) {
                    $columnName = $this->sanitizeColumnName($header);
                    
                    // Skip if empty or invalid
                    if (empty($columnName) || $columnName === '_') {
                        Log::warning("⚠️ Skipped invalid column", ['header' => $header]);
                        continue;
                    }
                    
                    // Determine column type based on header name
                    $this->addDynamicColumn($table, $columnName, $header);
                    $columnsToCreate[] = $columnName;
                    
                    Log::info("  ✅ Added column: {$columnName}");
                }
            });
            
            // Verify table was created
            if (!Schema::hasTable($tableName)) {
                throw new \Exception("Table was not created successfully");
            }
            
            Log::info("✅ Successfully created tenant table", [
                'table_name' => $tableName,
                'total_columns' => count($columnsToCreate),
                'columns' => $columnsToCreate
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("❌ Failed to create tenant table", [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * Add a dynamic column based on header name
     */
    protected function addDynamicColumn(Blueprint $table, string $columnName, string $originalHeader): void
    {
        $lower = strtolower($originalHeader);
        
        // Price fields
        if (str_contains($lower, 'price') || str_contains($lower, 'amount')) {
            $table->decimal($columnName, 10, 2)->nullable();
        }
        // Quantity/numeric fields
        elseif (str_contains($lower, 'quantity') || str_contains($lower, 'qty') || str_contains($lower, 'stock')) {
            $table->integer($columnName)->nullable();
        }
        // Date/time fields
        elseif (str_contains($lower, 'date') || str_contains($lower, 'time') || str_contains($lower, '_at')) {
            $table->timestamp($columnName)->nullable();
        }
        // Boolean fields
        elseif (str_contains($lower, 'is_') || str_contains($lower, 'has_')) {
            $table->boolean($columnName)->nullable();
        }
        // Status/enum fields
        elseif ($lower === 'status') {
            $table->string($columnName, 50)->nullable();
        }
        // Long text fields
        elseif (str_contains($lower, 'description') || str_contains($lower, 'details') || str_contains($lower, 'url')) {
            $table->text($columnName)->nullable();
        }
        // Default: varchar
        else {
            $table->string($columnName)->nullable();
        }
    }
    
    /**
     * Sanitize column name for database
     */
    protected function sanitizeColumnName(string $header): string
    {
        // Remove special characters, replace spaces with underscore
        $clean = preg_replace('/[^a-zA-Z0-9_]/', '_', $header);
        $clean = preg_replace('/_+/', '_', $clean); // Remove multiple underscores
        $clean = trim($clean, '_');
        $clean = strtolower($clean);
        
        // Ensure it doesn't start with a number
        if (is_numeric(substr($clean, 0, 1))) {
            $clean = 'col_' . $clean;
        }
        
        // Reserved keywords
        $reserved = ['id', 'created_at', 'updated_at', 'order', 'group', 'index'];
        if (in_array($clean, $reserved)) {
            $clean = 'col_' . $clean;
        }
        
        return $clean;
    }
    
    /**
     * Insert data into tenant table
     */
    public function insertProducts(int $tenantId, array $headers, array $rows): array
    {
        $tableName = "tenant_{$tenantId}_products";
        
        if (!Schema::hasTable($tableName)) {
            return [
                'success' => false,
                'message' => "Table {$tableName} does not exist"
            ];
        }
        
        $inserted = 0;
        $errors = 0;
        
        foreach ($rows as $rowIndex => $row) {
            try {
                if (empty($row) || empty($row[0])) {
                    continue;
                }
                
                // Pad row to match header length
                $row = array_pad($row, count($headers), null);
                
                // Combine headers with row data
                $data = [];
                foreach ($headers as $index => $header) {
                    $columnName = $this->sanitizeColumnName($header);
                    
                    if (empty($columnName) || $columnName === '_') {
                        continue;
                    }
                    
                    $value = $row[$index] ?? null;
                    
                    // Cast value based on column type
                    $data[$columnName] = $this->castValue($columnName, $value);
                }
                
                // Insert into tenant table
                DB::table($tableName)->insert($data);
                $inserted++;
                
            } catch (\Exception $e) {
                Log::error("Failed to insert row", [
                    'row_index' => $rowIndex,
                    'error' => $e->getMessage()
                ]);
                $errors++;
            }
        }
        
        return [
            'success' => true,
            'inserted' => $inserted,
            'errors' => $errors,
            'message' => "Inserted {$inserted} products. {$errors} errors."
        ];
    }
    
    /**
     * Cast value based on column name
     */
    protected function castValue(string $columnName, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        $lower = strtolower($columnName);
        
        // Price/amount fields
        if (str_contains($lower, 'price') || str_contains($lower, 'amount')) {
            return (float) $value;
        }
        
        // Quantity/numeric fields
        if (str_contains($lower, 'quantity') || str_contains($lower, 'qty') || str_contains($lower, 'stock')) {
            return (int) $value;
        }
        
        // Boolean fields
        if (str_contains($lower, 'is_') || str_contains($lower, 'has_')) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
        }
        
        return trim($value);
    }
    
    /**
     * Drop tenant products table
     */
    public function dropTenantProductsTable(int $tenantId): bool
    {
        try {
            $tableName = "tenant_{$tenantId}_products";
            
            if (Schema::hasTable($tableName)) {
                Schema::drop($tableName);
                
                Log::info("Dropped tenant table: {$tableName}");
                
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error("Failed to drop tenant table", [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Get tenant table name
     */
    public function getTenantTableName(int $tenantId): string
    {
        return "tenant_{$tenantId}_products";
    }
    
    /**
     * Check if tenant table exists
     */
    public function tenantTableExists(int $tenantId): bool
    {
        return Schema::hasTable($this->getTenantTableName($tenantId));
    }
    
    /**
     * Get all products from tenant table
     */
    public function getTenantProducts(int $tenantId, int $limit = 100)
    {
        $tableName = $this->getTenantTableName($tenantId);
        
        if (!Schema::hasTable($tableName)) {
            return collect();
        }
        
        return DB::table($tableName)->limit($limit)->get();
    }
}
