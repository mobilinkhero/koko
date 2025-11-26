<?php

// Temporary debug route - ADD TO YOUR LIVE SERVER
Route::get('/debug-server', function() {
    return response()->json([
        'status' => 'Server is working',
        'php_version' => phpversion(),
        'laravel_version' => app()->version(),
        'env' => [
            'APP_DEBUG' => config('app.debug'),
            'APP_ENV' => config('app.env'),
            'LOG_CHANNEL' => config('logging.default')
        ],
        'storage_writable' => is_writable(storage_path('logs')),
        'cache_writable' => is_writable(bootstrap_path('cache')),
        'logs_exist' => file_exists(storage_path('logs/laravel.log')),
        'timestamp' => now()->toDateTimeString()
    ]);
});

// Test database connection
Route::get('/debug-db', function() {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'database' => 'Connected successfully',
            'driver' => config('database.default'),
            'host' => config('database.connections.mysql.host'),
            'database' => config('database.connections.mysql.database')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'database' => 'Connection failed',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Test logging
Route::get('/debug-log', function() {
    try {
        \Log::info('Debug log test from route');
        return response()->json(['logging' => 'Test log written']);
    } catch (\Exception $e) {
        return response()->json(['logging' => 'Failed: ' . $e->getMessage()], 500);
    }
});
