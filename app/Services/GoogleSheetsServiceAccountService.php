<?php

namespace App\Services;

use App\Models\Tenant\EcommerceConfiguration;
use App\Services\EcommerceLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Google Sheets Service Account Service
 * Full automatic sheet creation using Service Account authentication
 */
class GoogleSheetsServiceAccountService
{
    protected $serviceAccountPath;
    protected $accessToken;
    protected $baseUrl = 'https://sheets.googleapis.com/v4/spreadsheets';
    protected $driveUrl = 'https://www.googleapis.com/drive/v3';

    public function __construct()
    {
        // Use global service account file for all tenants
        $this->serviceAccountPath = base_path('google-service-account.json');
    }

    /**
     * Fully automatic sheet creation with Service Account
     */
    public function createEcommerceSheetsAutomatic(EcommerceConfiguration $config): array
    {
        try {
            EcommerceLogger::info('Starting automatic sheet creation with Service Account', [
                'tenant_id' => $config->tenant_id,
                'sheets_url' => $config->google_sheets_url
            ]);

            // Check if service account file exists
            if (!file_exists($this->serviceAccountPath)) {
                return [
                    'success' => false,
                    'message' => 'Service account JSON file not found. Please upload it first.',
                    'setup_required' => true
                ];
            }

            // Get access token
            $tokenResult = $this->getAccessToken();
            if (!$tokenResult['success']) {
                return $tokenResult;
            }

            $spreadsheetId = $this->extractSpreadsheetId($config->google_sheets_url);
            if (!$spreadsheetId) {
                return [
                    'success' => false,
                    'message' => 'Invalid Google Sheets URL format'
                ];
            }

            // Get existing sheets
            $existingSheets = $this->getExistingSheetNames($spreadsheetId);
            
            // Get required sheets
            $requiredSheets = $this->getRequiredSheetsStructure();
            
            $createdSheets = [];
            $errors = [];

            // Create missing sheets
            foreach ($requiredSheets as $sheetName => $sheetConfig) {
                if (!in_array($sheetName, $existingSheets)) {
                    $result = $this->createSheet($spreadsheetId, $sheetName, $sheetConfig);
                    if ($result['success']) {
                        $createdSheets[] = $sheetName;
                        
                        // Add headers and sample data
                        $this->populateSheet($spreadsheetId, $sheetName, $sheetConfig);
                        
                        EcommerceLogger::info("Created and populated sheet: {$sheetName}", [
                            'tenant_id' => $config->tenant_id,
                            'columns' => count($sheetConfig['columns'])
                        ]);
                    } else {
                        $errors[] = "Failed to create {$sheetName}: " . $result['message'];
                    }
                }
            }

            // Delete default Sheet1 if it exists and is empty
            if (in_array('Sheet1', $existingSheets) && count($existingSheets) > 1) {
                $this->deleteSheet($spreadsheetId, 'Sheet1');
            }

            $message = 'Automatic sheet creation completed! ';
            if (!empty($createdSheets)) {
                $message .= 'Created: ' . implode(', ', $createdSheets) . '. ';
            } else {
                $message = 'All required sheets already exist. ';
            }
            
            if (!empty($errors)) {
                $message .= 'Errors: ' . implode('; ', $errors);
            }

            EcommerceLogger::info('Automatic sheet creation completed', [
                'tenant_id' => $config->tenant_id,
                'created_sheets' => $createdSheets,
                'errors' => $errors
            ]);

            return [
                'success' => true,
                'message' => $message,
                'created_sheets' => $createdSheets,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            EcommerceLogger::error('Automatic sheet creation failed', [
                'tenant_id' => $config->tenant_id,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create sheets automatically: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get OAuth2 access token using Service Account
     */
    public function getAccessToken(): array
    {
        try {
            // Check cache first
            $cacheKey = 'google_sheets_access_token';
            $cachedToken = Cache::get($cacheKey);
            
            if ($cachedToken) {
                $this->accessToken = $cachedToken;
                return [
                    'success' => true,
                    'token' => $cachedToken
                ];
            }

            // Load service account credentials
            $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);
            
            if (!$serviceAccount) {
                return [
                    'success' => false,
                    'message' => 'Invalid service account JSON file'
                ];
            }

            // Create JWT token
            $jwt = $this->createJWT($serviceAccount);
            
            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $this->accessToken = $tokenData['access_token'];
                
                // Cache token for 50 minutes (expires in 60)
                Cache::put($cacheKey, $this->accessToken, 50 * 60);
                
                return [
                    'success' => true,
                    'token' => $this->accessToken
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token: ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Access token error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create JWT token for Service Account authentication
     */
    protected function createJWT(array $serviceAccount): string
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $now = time();
        $payload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/spreadsheets https://www.googleapis.com/auth/drive',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $data = $headerEncoded . '.' . $payloadEncoded;
        
        // Sign with private key
        $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);
        
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $data . '.' . $signatureEncoded;
    }

    /**
     * Base64 URL encode
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Get existing sheet names from spreadsheet
     */
    protected function getExistingSheetNames(string $spreadsheetId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json'
            ])->get("{$this->baseUrl}/{$spreadsheetId}");

            if ($response->successful()) {
                $data = $response->json();
                $sheetNames = [];
                
                if (isset($data['sheets'])) {
                    foreach ($data['sheets'] as $sheet) {
                        $sheetNames[] = $sheet['properties']['title'];
                    }
                }
                
                return $sheetNames;
            }

            return ['Sheet1']; // Default fallback

        } catch (\Exception $e) {
            EcommerceLogger::error('Failed to get existing sheets', [
                'spreadsheet_id' => $spreadsheetId,
                'error' => $e->getMessage()
            ]);
            return ['Sheet1'];
        }
    }

    /**
     * Create a single sheet
     */
    protected function createSheet(string $spreadsheetId, string $sheetName, array $sheetConfig): array
    {
        try {
            $request = [
                'requests' => [
                    [
                        'addSheet' => [
                            'properties' => [
                                'title' => $sheetName,
                                'gridProperties' => [
                                    'rowCount' => 1000,
                                    'columnCount' => count($sheetConfig['columns'])
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json'
            ])->post("{$this->baseUrl}/{$spreadsheetId}:batchUpdate", $request);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Sheet '{$sheetName}' created successfully"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'API Error: ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Populate sheet with headers and sample data
     */
    protected function populateSheet(string $spreadsheetId, string $sheetName, array $sheetConfig): void
    {
        try {
            // Add headers
            $headerRange = "{$sheetName}!A1:" . chr(65 + count($sheetConfig['columns']) - 1) . "1";
            
            $this->updateRange($spreadsheetId, $headerRange, [$sheetConfig['columns']]);
            
            // Format headers (bold, blue background)
            $this->formatHeaders($spreadsheetId, $sheetName, count($sheetConfig['columns']));
            
            // Add sample data
            if (!empty($sheetConfig['sample_data'])) {
                $dataRange = "{$sheetName}!A2:" . chr(65 + count($sheetConfig['columns']) - 1) . (1 + count($sheetConfig['sample_data']));
                $this->updateRange($spreadsheetId, $dataRange, $sheetConfig['sample_data']);
            }

        } catch (\Exception $e) {
            EcommerceLogger::error("Failed to populate sheet: {$sheetName}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update range in spreadsheet
     */
    protected function updateRange(string $spreadsheetId, string $range, array $values): void
    {
        Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json'
        ])->put("{$this->baseUrl}/{$spreadsheetId}/values/{$range}?valueInputOption=RAW", [
            'values' => $values
        ]);
    }

    /**
     * Format headers with bold and blue background
     */
    protected function formatHeaders(string $spreadsheetId, string $sheetName, int $columnCount): void
    {
        try {
            $sheetId = $this->getSheetId($spreadsheetId, $sheetName);
            
            $request = [
                'requests' => [
                    [
                        'repeatCell' => [
                            'range' => [
                                'sheetId' => $sheetId,
                                'startRowIndex' => 0,
                                'endRowIndex' => 1,
                                'startColumnIndex' => 0,
                                'endColumnIndex' => $columnCount
                            ],
                            'cell' => [
                                'userEnteredFormat' => [
                                    'backgroundColor' => [
                                        'red' => 0.259,
                                        'green' => 0.522,
                                        'blue' => 0.957
                                    ],
                                    'textFormat' => [
                                        'foregroundColor' => [
                                            'red' => 1.0,
                                            'green' => 1.0,
                                            'blue' => 1.0
                                        ],
                                        'bold' => true
                                    ]
                                ]
                            ],
                            'fields' => 'userEnteredFormat(backgroundColor,textFormat)'
                        ]
                    ]
                ]
            ];

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json'
            ])->post("{$this->baseUrl}/{$spreadsheetId}:batchUpdate", $request);

        } catch (\Exception $e) {
            EcommerceLogger::error("Failed to format headers for {$sheetName}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get sheet ID by name
     */
    protected function getSheetId(string $spreadsheetId, string $sheetName): int
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
        ])->get("{$this->baseUrl}/{$spreadsheetId}");

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['sheets'])) {
                foreach ($data['sheets'] as $sheet) {
                    if ($sheet['properties']['title'] === $sheetName) {
                        return $sheet['properties']['sheetId'];
                    }
                }
            }
        }

        return 0; // Default
    }

    /**
     * Delete a sheet
     */
    protected function deleteSheet(string $spreadsheetId, string $sheetName): void
    {
        try {
            $sheetId = $this->getSheetId($spreadsheetId, $sheetName);
            
            $request = [
                'requests' => [
                    [
                        'deleteSheet' => [
                            'sheetId' => $sheetId
                        ]
                    ]
                ]
            ];

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json'
            ])->post("{$this->baseUrl}/{$spreadsheetId}:batchUpdate", $request);

        } catch (\Exception $e) {
            // Ignore delete errors
        }
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

    /**
     * Check if service account is properly configured
     */
    public function checkServiceAccountSetup(): array
    {
        if (!file_exists($this->serviceAccountPath)) {
            return [
                'configured' => false,
                'message' => 'Service account JSON file not found',
                'path' => $this->serviceAccountPath
            ];
        }

        try {
            $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);
            
            if (!$serviceAccount || !isset($serviceAccount['client_email'])) {
                return [
                    'configured' => false,
                    'message' => 'Invalid service account JSON file'
                ];
            }

            return [
                'configured' => true,
                'message' => 'Service account configured',
                'email' => $serviceAccount['client_email']
            ];

        } catch (\Exception $e) {
            return [
                'configured' => false,
                'message' => 'Error reading service account: ' . $e->getMessage()
            ];
        }
    }
}
