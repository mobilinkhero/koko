<?php

require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Checking ecommerce_configurations table columns:\n\n";
    
    $columns = DB::select('SHOW COLUMNS FROM ecommerce_configurations');
    
    $existingColumns = [];
    foreach ($columns as $column) {
        $existingColumns[] = $column->Field;
        echo "✓ {$column->Field} ({$column->Type})\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "NEEDED COLUMNS CHECK:\n\n";
    
    $neededColumns = [
        'required_customer_fields',
        'enabled_payment_methods', 
        'payment_method_responses',
        'collect_customer_details'
    ];
    
    $missingColumns = [];
    foreach ($neededColumns as $col) {
        if (in_array($col, $existingColumns)) {
            echo "✓ {$col} - EXISTS\n";
        } else {
            echo "✗ {$col} - MISSING\n";
            $missingColumns[] = $col;
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "MISSING COLUMNS: " . (empty($missingColumns) ? "NONE" : implode(', ', $missingColumns)) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
