<?php

namespace App\Services;

use App\Models\Tenant\EcommerceConfiguration;
use App\Services\EcommerceLogger;
use Illuminate\Support\Facades\Http;

/**
 * Google Sheets Direct API Service
 * Creates sheets using direct Google Sheets API v4 calls
 */
class GoogleSheetsDirectApiService
{
    protected $apiKey;
    protected $baseUrl = 'https://sheets.googleapis.com/v4/spreadsheets';

    public function __construct()
    {
        // Get API key from config or environment
        $this->apiKey = config('services.google.api_key') ?? env('GOOGLE_SHEETS_API_KEY');
    }

    /**
     * Create e-commerce sheets with one click
     */
    public function createEcommerceSheetsOneClick(EcommerceConfiguration $config): array
    {
        try {
            EcommerceLogger::info('Starting one-click sheet creation', [
                'tenant_id' => $config->tenant_id,
                'sheets_url' => $config->google_sheets_url
            ]);

            $spreadsheetId = $this->extractSpreadsheetId($config->google_sheets_url);
            if (!$spreadsheetId) {
                return [
                    'success' => false,
                    'message' => 'Invalid Google Sheets URL format'
                ];
            }

            // Method 1: Try using batch update (requires edit access)
            $result = $this->createSheetsViaBatchUpdate($spreadsheetId, $config);
            
            if (!$result['success']) {
                // Method 2: Generate import instructions
                $result = $this->generateImportInstructions($config);
            }

            return $result;

        } catch (\Exception $e) {
            EcommerceLogger::error('One-click sheet creation failed', [
                'tenant_id' => $config->tenant_id,
                'exception' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create sheets automatically. ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create sheets using Google Sheets API batch update
     */
    protected function createSheetsViaBatchUpdate(string $spreadsheetId, EcommerceConfiguration $config): array
    {
        try {
            $requests = [];
            $requiredSheets = $this->getRequiredSheetsStructure();
            
            // Create requests for each sheet
            foreach ($requiredSheets as $sheetName => $sheetData) {
                // Add sheet creation request
                $requests[] = [
                    'addSheet' => [
                        'properties' => [
                            'title' => $sheetName,
                            'gridProperties' => [
                                'rowCount' => 1000,
                                'columnCount' => count($sheetData['columns'])
                            ]
                        ]
                    ]
                ];
            }

            // Execute batch update
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/{$spreadsheetId}:batchUpdate" . ($this->apiKey ? "?key={$this->apiKey}" : ""), [
                'requests' => $requests
            ]);

            if ($response->successful()) {
                // Now add headers and formatting
                $this->addHeadersAndFormatting($spreadsheetId, $requiredSheets, $config);
                
                return [
                    'success' => true,
                    'message' => 'All e-commerce sheets created successfully with proper structure!',
                    'created_sheets' => array_keys($requiredSheets)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create sheets via API. Response: ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'API method failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add headers and formatting to created sheets
     */
    protected function addHeadersAndFormatting(string $spreadsheetId, array $requiredSheets, EcommerceConfiguration $config): void
    {
        try {
            foreach ($requiredSheets as $sheetName => $sheetData) {
                // Add headers
                $this->addSheetHeaders($spreadsheetId, $sheetName, $sheetData['columns']);
                
                // Add sample data
                if (!empty($sheetData['sample_data'])) {
                    $this->addSampleData($spreadsheetId, $sheetName, $sheetData['sample_data']);
                }
            }

            EcommerceLogger::info('Headers and formatting added successfully', [
                'tenant_id' => $config->tenant_id,
                'sheets' => array_keys($requiredSheets)
            ]);

        } catch (\Exception $e) {
            EcommerceLogger::error('Failed to add headers and formatting', [
                'tenant_id' => $config->tenant_id,
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Add headers to a specific sheet
     */
    protected function addSheetHeaders(string $spreadsheetId, string $sheetName, array $headers): void
    {
        try {
            $range = "{$sheetName}!A1:" . chr(65 + count($headers) - 1) . "1";
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->put("{$this->baseUrl}/{$spreadsheetId}/values/{$range}" . ($this->apiKey ? "?valueInputOption=RAW&key={$this->apiKey}" : "?valueInputOption=RAW"), [
                'values' => [$headers]
            ]);

            if (!$response->successful()) {
                throw new \Exception("Failed to add headers to {$sheetName}: " . $response->body());
            }

        } catch (\Exception $e) {
            EcommerceLogger::error("Failed to add headers to {$sheetName}", [
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Add sample data to a sheet
     */
    protected function addSampleData(string $spreadsheetId, string $sheetName, array $sampleData): void
    {
        try {
            $startRow = 2; // Start from row 2 (after headers)
            $endCol = chr(65 + count($sampleData[0]) - 1);
            $endRow = $startRow + count($sampleData) - 1;
            $range = "{$sheetName}!A{$startRow}:{$endCol}{$endRow}";
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->put("{$this->baseUrl}/{$spreadsheetId}/values/{$range}" . ($this->apiKey ? "?valueInputOption=RAW&key={$this->apiKey}" : "?valueInputOption=RAW"), [
                'values' => $sampleData
            ]);

            if (!$response->successful()) {
                throw new \Exception("Failed to add sample data to {$sheetName}: " . $response->body());
            }

        } catch (\Exception $e) {
            EcommerceLogger::error("Failed to add sample data to {$sheetName}", [
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate import instructions as fallback
     */
    protected function generateImportInstructions(EcommerceConfiguration $config): array
    {
        try {
            $requiredSheets = $this->getRequiredSheetsStructure();
            $importData = [];

            foreach ($requiredSheets as $sheetName => $sheetData) {
                $importData[$sheetName] = [
                    'headers' => $sheetData['columns'],
                    'sample_data' => $sheetData['sample_data'] ?? [],
                    'csv_content' => $this->generateCsvForSheet($sheetData)
                ];
            }

            return [
                'success' => true,
                'message' => 'Sheet structure prepared. Use the import method below.',
                'method' => 'import',
                'import_data' => $importData,
                'instructions' => $this->generateImportInstructionsText($config->google_sheets_url)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate import instructions: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate CSV content for a sheet
     */
    protected function generateCsvForSheet(array $sheetData): string
    {
        $csv = [];
        
        // Add headers
        $csv[] = implode(',', array_map(function($col) {
            return '"' . str_replace('"', '""', $col) . '"';
        }, $sheetData['columns']));
        
        // Add sample data
        if (!empty($sheetData['sample_data'])) {
            foreach ($sheetData['sample_data'] as $row) {
                $csv[] = implode(',', array_map(function($cell) {
                    return '"' . str_replace('"', '""', (string)$cell) . '"';
                }, $row));
            }
        }
        
        return implode("\n", $csv);
    }

    /**
     * Generate import instructions text
     */
    protected function generateImportInstructionsText(string $sheetsUrl): array
    {
        return [
            "1. Open your Google Sheet: {$sheetsUrl}",
            "2. For each required sheet (Products, Orders, Customers):",
            "   • Create a new sheet tab with the exact name",
            "   • Copy the headers from the data below",
            "   • Paste them in row 1",
            "   • Add sample data in row 2",
            "3. Save the sheet",
            "4. Your e-commerce system is ready!"
        ];
    }

    /**
     * Extract spreadsheet ID from URL
     */
    protected function extractSpreadsheetId(string $url): ?string
    {
        if (preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get required sheets structure
     */
    protected function getRequiredSheetsStructure(): array
    {
        return [
            'Products' => [
                'columns' => [
                    'ID', 'Name', 'SKU', 'Description', 'Price', 'Sale Price',
                    'Category', 'Stock Quantity', 'Low Stock Threshold',
                    'Status', 'Featured', 'Created At', 'Updated At'
                ],
                'sample_data' => [
                    [1, 'Sample Product', 'SAMPLE-001', 'This is a sample product',
                     29.99, '', 'Electronics', 100, 10, 'active', 'FALSE',
                     date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
                ]
            ],
            'Orders' => [
                'columns' => [
                    'Order Number', 'Customer Name', 'Customer Phone', 'Customer Email',
                    'Customer Address', 'Items', 'Subtotal', 'Tax Amount', 'Shipping Amount',
                    'Total Amount', 'Currency', 'Payment Method', 'Payment Status',
                    'Order Status', 'Notes', 'Created At'
                ],
                'sample_data' => [
                    ['ORD-001', 'John Doe', '+1234567890', 'john@example.com',
                     '123 Main St', 'Sample Product x1', 29.99, 2.40, 5.00,
                     37.39, 'USD', 'cash_on_delivery', 'pending', 'pending',
                     'Sample order', date('Y-m-d H:i:s')]
                ]
            ],
            'Customers' => [
                'columns' => [
                    'Phone', 'Name', 'Email', 'Address', 'Total Orders',
                    'Total Spent', 'Last Order Date', 'Status', 'Created At'
                ],
                'sample_data' => [
                    ['+1234567890', 'John Doe', 'john@example.com', '123 Main St',
                     1, 37.39, date('Y-m-d'), 'active', date('Y-m-d H:i:s')]
                ]
            ]
        ];
    }
}
