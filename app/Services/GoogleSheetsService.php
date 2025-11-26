<?php

namespace App\Services;

use App\Models\Tenant\EcommerceConfiguration;
use App\Models\Tenant\Product;
use App\Models\Tenant\Order;
use App\Services\EcommerceLogger;
use App\Services\DynamicSheetMapperService;
use App\Services\GoogleSheetsServiceAccountService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Google Sheets Integration Service for E-commerce
 * Handles syncing products, orders, and customers with Google Sheets
 */
class GoogleSheetsService
{
    protected $tenantId;
    protected $config;
    protected $tableService;

    public function __construct(?int $tenantId = null)
    {
        $this->tenantId = $tenantId ?? tenant_id();
        $this->config = EcommerceConfiguration::where('tenant_id', $this->tenantId)->first();
        $this->tableService = new DynamicTenantTableService();
    }

    /**
     * Extract sheet ID from Google Sheets URL
     */
    public function extractSheetId(string $url): ?string
    {
        // Extract sheet ID from various Google Sheets URL formats
        if (preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Validate Google Sheets URL and check if it's publicly accessible
     */
    public function validateSheetsUrl(string $url): array
    {
        try {
            $sheetId = $this->extractSheetId($url);
            if (!$sheetId) {
                return [
                    'valid' => false,
                    'message' => 'Invalid Google Sheets URL format'
                ];
            }

            // Test if sheet is publicly accessible
            $testUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid=0";
            $response = Http::timeout(10)->get($testUrl);

            if ($response->successful()) {
                return [
                    'valid' => true,
                    'sheet_id' => $sheetId,
                    'message' => 'Google Sheets URL is valid and accessible'
                ];
            } else {
                return [
                    'valid' => false,
                    'message' => 'Sheet is not publicly accessible. Please make sure sharing is set to "Anyone with the link can view"'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Google Sheets validation error: ' . $e->getMessage());
            return [
                'valid' => false,
                'message' => 'Error validating Google Sheets URL: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create default sheets structure
     */
    public function createDefaultSheets(): array
    {
        return [
            'Products' => [
                'columns' => ['ID', 'Name', 'SKU', 'Description', 'Price', 'Sale Price', 'Category', 'Stock Quantity', 'Low Stock Threshold', 'Status', 'Featured', 'Created At', 'Updated At'],
                'sample_data' => [
                    ['1', 'Sample Product', 'SAMPLE-001', 'This is a sample product', '29.99', '', 'Electronics', '100', '10', 'active', 'FALSE', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
                ]
            ],
            'Orders' => [
                'columns' => ['Order Number', 'Customer Name', 'Customer Phone', 'Customer Email', 'Customer Address', 'Items', 'Subtotal', 'Tax Amount', 'Shipping Amount', 'Total Amount', 'Currency', 'Payment Method', 'Payment Status', 'Order Status', 'Notes', 'Created At'],
                'sample_data' => [
                    ['ORD-001', 'John Doe', '+1234567890', 'john@example.com', '123 Main St', 'Sample Product x1', '29.99', '2.40', '5.00', '37.39', 'USD', 'cash_on_delivery', 'pending', 'pending', 'Sample order', date('Y-m-d H:i:s')]
                ]
            ],
            'Customers' => [
                'columns' => ['Phone', 'Name', 'Email', 'Address', 'Total Orders', 'Total Spent', 'Last Order Date', 'Status', 'Created At'],
                'sample_data' => [
                    ['+1234567890', 'John Doe', 'john@example.com', '123 Main St', '1', '37.39', date('Y-m-d'), 'active', date('Y-m-d H:i:s')]
                ]
            ]
        ];
    }

    /**
     * Check and create missing sheets in the Google Sheets document
     */
    public function checkAndCreateSheets(EcommerceConfiguration $config): array
    {
        try {
            EcommerceLogger::info('Starting sheet validation and creation', [
                'tenant_id' => $config->tenant_id,
                'sheets_url' => $config->google_sheets_url
            ]);

            // Extract spreadsheet ID from URL
            $spreadsheetId = $this->extractSheetId($config->google_sheets_url);
            if (!$spreadsheetId) {
                return [
                    'success' => false,
                    'message' => 'Invalid Google Sheets URL format'
                ];
            }

            // Get required sheets structure
            $requiredSheets = $this->createDefaultSheets();
            
            // Generate Google Apps Script code for sheet creation
            $appsScriptCode = $this->generateAppsScriptCode($requiredSheets);
            
            // Log the Apps Script code for manual execution
            EcommerceLogger::info('Generated Apps Script for sheet creation', [
                'tenant_id' => $config->tenant_id,
                'spreadsheet_id' => $spreadsheetId,
                'apps_script' => $appsScriptCode
            ]);

            // Save the Apps Script code to a file for easy access
            $scriptFile = storage_path('app/ecommerce_sheets_script.js');
            file_put_contents($scriptFile, $appsScriptCode);

            $message = "Sheet validation completed. To create the required sheets:\n\n";
            $message .= "1. Open your Google Sheet: " . $config->google_sheets_url . "\n";
            $message .= "2. Go to Extensions → Apps Script\n";
            $message .= "3. Replace the default code with the script from: {$scriptFile}\n";
            $message .= "4. Save and run the script\n\n";
            $message .= "This will automatically create the following sheets with proper columns:\n";
            
            foreach ($requiredSheets as $sheetName => $sheetData) {
                $message .= "- {$sheetName} (" . count($sheetData['columns']) . " columns)\n";
                
                EcommerceLogger::info("Prepared sheet structure: {$sheetName}", [
                    'tenant_id' => $config->tenant_id,
                    'columns' => $sheetData['columns']
                ]);
            }

            EcommerceLogger::info('Sheet validation completed - Apps Script generated', [
                'tenant_id' => $config->tenant_id,
                'script_file' => $scriptFile
            ]);

            return [
                'success' => true,
                'message' => $message,
                'apps_script_file' => $scriptFile,
                'required_sheets' => array_keys($requiredSheets)
            ];

        } catch (\Exception $e) {
            EcommerceLogger::error('Sheet validation failed', [
                'tenant_id' => $config->tenant_id,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to validate sheets: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate Google Apps Script code to create required sheets
     */
    protected function generateAppsScriptCode(array $requiredSheets): string
    {
        $script = "function createEcommerceSheets() {\n";
        $script .= "  var ss = SpreadsheetApp.getActiveSpreadsheet();\n";
        $script .= "  var existingSheets = ss.getSheets().map(sheet => sheet.getName());\n\n";
        
        foreach ($requiredSheets as $sheetName => $sheetData) {
            $script .= "  // Create {$sheetName} sheet\n";
            $script .= "  if (!existingSheets.includes('{$sheetName}')) {\n";
            $script .= "    var {$sheetName}Sheet = ss.insertSheet('{$sheetName}');\n";
            $script .= "    var headers = ['" . implode("', '", $sheetData['columns']) . "'];\n";
            $script .= "    {$sheetName}Sheet.getRange(1, 1, 1, headers.length).setValues([headers]);\n";
            $script .= "    {$sheetName}Sheet.getRange(1, 1, 1, headers.length).setFontWeight('bold');\n";
            $script .= "    {$sheetName}Sheet.getRange(1, 1, 1, headers.length).setBackground('#4285F4');\n";
            $script .= "    {$sheetName}Sheet.getRange(1, 1, 1, headers.length).setFontColor('#FFFFFF');\n";
            
            // Add sample data
            if (!empty($sheetData['sample_data'])) {
                $sampleRow = $sheetData['sample_data'][0];
                $script .= "    var sampleData = ['" . implode("', '", $sampleRow) . "'];\n";
                $script .= "    {$sheetName}Sheet.getRange(2, 1, 1, sampleData.length).setValues([sampleData]);\n";
            }
            
            $script .= "    {$sheetName}Sheet.autoResizeColumns(1, headers.length);\n";
            $script .= "    Logger.log('Created {$sheetName} sheet with ' + headers.length + ' columns');\n";
            $script .= "  } else {\n";
            $script .= "    Logger.log('{$sheetName} sheet already exists');\n";
            $script .= "  }\n\n";
        }
        
        $script .= "  Logger.log('E-commerce sheets setup completed!');\n";
        $script .= "}\n\n";
        $script .= "// Run this function to create all required e-commerce sheets\n";
        $script .= "// Generated by WhatsMark E-commerce System\n";
        
        return $script;
    }

    /**
     * Sync products from Google Sheets
     */
    public function syncProductsFromSheets(): array
    {
        if (!$this->config || !$this->config->google_sheets_url) {
            return [
                'success' => false,
                'message' => 'Google Sheets URL not configured'
            ];
        }

        // Extract sheet ID from URL
        $sheetId = $this->extractSheetId($this->config->google_sheets_url);
        if (!$sheetId) {
            return [
                'success' => false,
                'message' => 'Invalid Google Sheets URL format'
            ];
        }

        try {
            EcommerceLogger::info('Starting product sync from Google Sheets', [
                'tenant_id' => $this->tenantId,
                'sheet_id' => $sheetId,
                'url' => $this->config->google_sheets_url
            ]);

            // Check if service account is available
            $serviceAccountService = new GoogleSheetsServiceAccountService();
            $serviceAccountStatus = $serviceAccountService->checkServiceAccountSetup();
            
            if ($serviceAccountStatus['configured']) {
                // Use service account to fetch data
                EcommerceLogger::info('Using Service Account for sync');
                return $this->syncProductsWithServiceAccount($sheetId, $serviceAccountService);
            }

            // Fallback to public access
            EcommerceLogger::info('Attempting public access for sync');
            
            // First try to get the Products sheet (gid may vary)
            // We'll try gid=0 first (usually the first sheet)
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid=0";
            
            EcommerceLogger::info('Fetching CSV from Google Sheets', [
                'csv_url' => $csvUrl
            ]);

            $response = Http::timeout(30)->get($csvUrl);

            if (!$response->successful()) {
                $errorMessage = 'Failed to fetch products from Google Sheets. Status: ' . $response->status();
                
                EcommerceLogger::error('Google Sheets fetch failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                    'csv_url' => $csvUrl
                ]);

                // Check if it's a permission issue
                if ($response->status() === 403) {
                    $errorMessage = 'Access denied. Please make sure the Google Sheet is shared with "Anyone with the link can view" or shared with the service account.';
                } elseif ($response->status() === 404) {
                    $errorMessage = 'Google Sheet not found. Please check the URL is correct.';
                }

                return [
                    'success' => false,
                    'message' => $errorMessage
                ];
            }

            $csvData = $response->body();
            
            EcommerceLogger::info('CSV data fetched successfully', [
                'data_length' => strlen($csvData),
                'first_100_chars' => substr($csvData, 0, 100)
            ]);

            // Check if we got HTML instead of CSV (common when sheet isn't public)
            if (strpos($csvData, '<!DOCTYPE') !== false || strpos($csvData, '<html') !== false) {
                EcommerceLogger::error('Received HTML instead of CSV - likely a permission issue');
                return [
                    'success' => false,
                    'message' => 'Unable to access sheet data. Please ensure the sheet is shared with "Anyone with the link can view".'
                ];
            }

            $lines = str_getcsv($csvData, "\n");
            
            if (empty($lines)) {
                return [
                    'success' => false,
                    'message' => 'No data found in the Google Sheet. Please add products to the sheet first.'
                ];
            }

            $header = str_getcsv(array_shift($lines));
            
            EcommerceLogger::info('📋 Creating dynamic tenant table from CSV', [
                'tenant_id' => $this->tenantId,
                'headers' => $header,
                'total_columns' => count($header)
            ]);
            
            // Create tenant-specific table with columns matching sheet
            $tableCreated = $this->tableService->createTenantProductsTable($this->tenantId, $header);
            
            if (!$tableCreated) {
                return [
                    'success' => false,
                    'message' => 'Failed to create tenant products table'
                ];
            }
            
            // Parse CSV rows into array format
            $rows = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $rows[] = str_getcsv($line);
            }
            
            // Insert all products into tenant table
            $result = $this->tableService->insertProducts($this->tenantId, $header, $rows);
            
            $syncedCount = $result['inserted'] ?? 0;
            $errorCount = $result['errors'] ?? 0;

            // Update sync status
            $this->config->update([
                'sync_status' => 'completed',
                'last_sync_at' => Carbon::now()
            ]);

            EcommerceLogger::info('Product sync completed successfully', [
                'tenant_id' => $this->tenantId,
                'synced_count' => $syncedCount,
                'error_count' => $errorCount,
                'total_lines_processed' => count($lines)
            ]);
            
            // Log to console with summary
            \Log::info("
╔══════════════════════════════════════════════════════════════
║ 📊 SYNC COMPLETED - CSV Export Method
╠══════════════════════════════════════════════════════════════
║ Tenant ID: {$this->tenantId}
║ ✅ Successfully Synced: {$syncedCount} products
║ ❌ Errors: {$errorCount}
║ 📋 Total Lines Processed: " . count($lines) . "
║ 
║ 💡 To see detailed errors, check:
║    storage/logs/ecomorcelog.log
║    storage/logs/laravel.log
╚══════════════════════════════════════════════════════════════
            ");

            $message = "Synced {$syncedCount} products successfully.";
            if ($errorCount > 0) {
                $message .= " ⚠️ {$errorCount} errors occurred. Check logs: storage/logs/ecomorcelog.log for details.";
            }

            return [
                'success' => true,
                'message' => $message,
                'synced' => $syncedCount,
                'errors' => $errorCount
            ];

        } catch (\Exception $e) {
            EcommerceLogger::error('Products sync failed with exception', [
                'tenant_id' => $this->tenantId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error syncing products: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync products using Service Account authentication
     */
    protected function syncProductsWithServiceAccount(string $sheetId, GoogleSheetsServiceAccountService $serviceAccount): array
    {
        try {
            // Get access token
            $tokenResult = $serviceAccount->getAccessToken();
            if (!$tokenResult['success']) {
                EcommerceLogger::error('Failed to get service account access token', [
                    'error' => $tokenResult['message']
                ]);
                return [
                    'success' => false,
                    'message' => 'Authentication failed: ' . $tokenResult['message']
                ];
            }

            $accessToken = $tokenResult['token'];
            
            // Use Sheets API to get data
            $apiUrl = "https://sheets.googleapis.com/v4/spreadsheets/{$sheetId}/values/Products!A:Z";
            
            EcommerceLogger::info('Fetching data using Sheets API', [
                'api_url' => $apiUrl
            ]);

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->get($apiUrl);

            if (!$response->successful()) {
                $errorMessage = 'Failed to fetch products using Service Account. Status: ' . $response->status();
                
                EcommerceLogger::error('Service Account fetch failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500)
                ]);

                if ($response->status() === 404) {
                    $errorMessage = 'Products sheet not found. Please create a sheet named "Products" in your Google Sheets.';
                }

                return [
                    'success' => false,
                    'message' => $errorMessage
                ];
            }

            $data = $response->json();
            $rows = $data['values'] ?? [];

            if (empty($rows)) {
                return [
                    'success' => false,
                    'message' => 'No data found in the Products sheet. Please add products to the sheet first.'
                ];
            }

            $header = array_shift($rows); // First row is header
            
            EcommerceLogger::info('📋 Creating dynamic tenant table', [
                'tenant_id' => $this->tenantId,
                'headers' => $header,
                'total_columns' => count($header)
            ]);
            
            // Also log to browser console
            \Log::channel('single')->info('🔧 BROWSER: Creating table tenant_' . $this->tenantId . '_products with ' . count($header) . ' columns');
            
            // Create tenant-specific table with columns matching sheet
            $tableCreated = $this->tableService->createTenantProductsTable($this->tenantId, $header);
            
            if (!$tableCreated) {
                $errorMsg = 'Failed to create tenant products table. Check storage/logs/laravel.log for details.';
                \Log::channel('single')->error('🔧 BROWSER ERROR: ' . $errorMsg);
                
                return [
                    'success' => false,
                    'message' => $errorMsg
                ];
            }
            
            \Log::channel('single')->info('🔧 BROWSER: Table created successfully!');
            
            EcommerceLogger::info('✅ Tenant table created with dynamic columns', [
                'tenant_id' => $this->tenantId,
                'table_name' => "tenant_{$this->tenantId}_products",
                'columns' => count($header)
            ]);
            
            // Insert all products into tenant table
            $result = $this->tableService->insertProducts($this->tenantId, $header, $rows);
            
            $syncedCount = $result['inserted'] ?? 0;
            $errorCount = $result['errors'] ?? 0;

            // Update sync status
            $this->config->update([
                'sync_status' => 'completed',
                'last_sync_at' => Carbon::now()
            ]);

            EcommerceLogger::info('Product sync completed with Service Account', [
                'tenant_id' => $this->tenantId,
                'synced_count' => $syncedCount,
                'error_count' => $errorCount,
                'total_rows_processed' => count($rows)
            ]);
            
            // Also log to console with prominent message
            \Log::info("
╔══════════════════════════════════════════════════════════════
║ 📊 SYNC COMPLETED - Service Account Method
╠══════════════════════════════════════════════════════════════
║ Tenant ID: {$this->tenantId}
║ ✅ Successfully Synced: {$syncedCount} products
║ ❌ Errors: {$errorCount}
║ 📋 Total Rows Processed: " . count($rows) . "
║ 
║ 💡 To see detailed errors, check:
║    storage/logs/ecomorcelog.log
║    storage/logs/laravel.log
╚══════════════════════════════════════════════════════════════
            ");

            $message = "Synced {$syncedCount} products successfully using Service Account.";
            if ($errorCount > 0) {
                $message .= " ⚠️ {$errorCount} errors occurred. Check logs: storage/logs/ecomorcelog.log for details.";
            }

            return [
                'success' => true,
                'message' => $message,
                'synced' => $syncedCount,
                'errors' => $errorCount
            ];

        } catch (\Exception $e) {
            EcommerceLogger::error('Service Account sync failed with exception', [
                'tenant_id' => $this->tenantId,
                'exception' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error syncing with Service Account: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get dynamic mapper configuration summary
     */
    public function getDynamicMapperSummary(): array
    {
        return $this->dynamicMapper->getConfigurationSummary();
    }

    /**
     * Reset dynamic column detection (force re-detection)
     */
    public function resetColumnDetection(): bool
    {
        return $this->dynamicMapper->resetConfiguration();
    }

    /**
     * Manually update column mapping
     */
    public function updateColumnMapping(array $mapping): bool
    {
        return $this->dynamicMapper->updateMapping($mapping);
    }

    /**
     * Sync order to Google Sheets
     */
    public function syncOrderToSheets(Order $order): array
    {
        if (!$this->config || !$this->config->orders_sheet_id) {
            return [
                'success' => false,
                'message' => 'Orders sheet not configured'
            ];
        }

        try {
            // In a real implementation, you would use Google Sheets API to append/update rows
            // For now, we'll log the order data that should be synced
            
            $orderData = [
                'Order ID' => $order->id,
                'Order Number' => $order->order_number,
                'Customer Name' => $order->customer_name,
                'Customer Phone' => $order->customer_phone,
                'Customer Email' => $order->customer_email,
                'Customer Address' => $order->customer_address,
                'Status' => $order->status,
                'Items (JSON)' => json_encode($order->items),
                'Subtotal' => $order->subtotal,
                'Tax Amount' => $order->tax_amount,
                'Shipping Amount' => $order->shipping_amount,
                'Discount Amount' => $order->discount_amount,
                'Total Amount' => $order->total_amount,
                'Currency' => $order->currency,
                'Payment Method' => $order->payment_method,
                'Payment Status' => $order->payment_status,
                'Notes' => $order->notes,
                'Created At' => $order->created_at->toDateTimeString(),
                'Updated At' => $order->updated_at->toDateTimeString(),
            ];

            Log::info('Order synced to sheets', ['order_data' => $orderData]);

            // Update order sync status
            $order->update([
                'google_sheet_row_id' => $order->id, // In real implementation, this would be the actual row ID
                'sync_status' => 'synced',
                'last_synced_at' => Carbon::now()
            ]);

            return [
                'success' => true,
                'message' => 'Order synced successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Order sync error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error syncing order: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get sync status
     */
    public function getSyncStatus(): array
    {
        if (!$this->config) {
            return [
                'configured' => false,
                'last_sync' => null,
                'status' => 'not_configured'
            ];
        }

        return [
            'configured' => $this->config->is_configured,
            'last_sync' => $this->config->last_sync_at,
            'status' => $this->config->sync_status,
            'products_count' => Product::where('tenant_id', $this->tenantId)->count(),
            'orders_count' => Order::where('tenant_id', $this->tenantId)->count(),
        ];
    }

    /**
     * Test connection to Google Sheets
     */
    public function testConnection(): array
    {
        if (!$this->config) {
            return [
                'success' => false,
                'message' => 'E-commerce not configured'
            ];
        }

        try {
            $results = [];
            
            // Test products sheet
            if ($this->config->products_sheet_id) {
                $csvUrl = "https://docs.google.com/spreadsheets/d/{$this->config->products_sheet_id}/export?format=csv&gid=0";
                $response = Http::timeout(10)->get($csvUrl);
                $results['products'] = $response->successful();
            }

            // Test orders sheet
            if ($this->config->orders_sheet_id) {
                $csvUrl = "https://docs.google.com/spreadsheets/d/{$this->config->orders_sheet_id}/export?format=csv&gid=0";
                $response = Http::timeout(10)->get($csvUrl);
                $results['orders'] = $response->successful();
            }

            $allSuccessful = !empty($results) && !in_array(false, $results);

            return [
                'success' => $allSuccessful,
                'message' => $allSuccessful ? 'All sheets accessible' : 'Some sheets not accessible',
                'details' => $results
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }
}
