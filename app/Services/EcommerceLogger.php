<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * E-commerce Dedicated Logger Service
 * Logs all e-commerce activities, errors, and bot interactions to custom log files
 */
class EcommerceLogger
{
    /**
     * Log e-commerce activities to home directory
     */
    public static function log($level, $message, $context = [])
    {
        // Try multiple possible home directories
        $possibleHomeDirs = [
            $_SERVER['HOME'] ?? null,
            $_SERVER['USERPROFILE'] ?? null,
            '/home/' . ($_SERVER['USER'] ?? 'default'),
            getcwd(), // Current working directory as fallback
            storage_path('logs') // Laravel storage as final fallback
        ];
        
        $homeDir = null;
        foreach ($possibleHomeDirs as $dir) {
            if ($dir && is_dir($dir) && is_writable($dir)) {
                $homeDir = $dir;
                break;
            }
        }
        
        // If no writable directory found, use Laravel storage and create it
        if (!$homeDir) {
            $homeDir = storage_path('logs');
            if (!is_dir($homeDir)) {
                mkdir($homeDir, 0755, true);
            }
        }
        
        $logFile = $homeDir . '/ecomorcelog.log';
        
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context, JSON_PRETTY_PRINT) : '';
        
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $contextStr ? "\nContext: " . $contextStr : ''
        );
        
        // Write to log file
        try {
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            // Fallback to Laravel log if file writing fails
            Log::channel('ecommerce')->error("Failed to write to ecommerce log at {$logFile}: " . $e->getMessage());
        }
        
        // Also log to Laravel's e-commerce channel
        Log::channel('ecommerce')->log($level, $message, $context);
    }

    /**
     * Log error messages
     */
    public static function error($message, $context = [])
    {
        self::log('error', $message, $context);
    }

    /**
     * Log info messages
     */
    public static function info($message, $context = [])
    {
        self::log('info', $message, $context);
    }

    /**
     * Log debug messages
     */
    public static function debug($message, $context = [])
    {
        self::log('debug', $message, $context);
    }

    /**
     * Log warning messages
     */
    public static function warning($message, $context = [])
    {
        self::log('warning', $message, $context);
    }

    /**
     * Log WhatsApp bot interactions
     */
    public static function botInteraction($phone, $message, $response, $context = [])
    {
        $logContext = array_merge([
            'phone' => $phone,
            'user_message' => $message,
            'bot_response' => $response,
            'tenant_id' => tenant_id()
        ], $context);

        self::info("Bot Interaction", $logContext);
    }

    /**
     * Log order activities
     */
    public static function orderActivity($orderId, $activity, $context = [])
    {
        $logContext = array_merge([
            'order_id' => $orderId,
            'activity' => $activity,
            'tenant_id' => tenant_id()
        ], $context);

        self::info("Order Activity", $logContext);
    }

    /**
     * Log product activities
     */
    public static function productActivity($productId, $activity, $context = [])
    {
        $logContext = array_merge([
            'product_id' => $productId,
            'activity' => $activity,
            'tenant_id' => tenant_id()
        ], $context);

        self::info("Product Activity", $logContext);
    }

    /**
     * Log Google Sheets sync activities
     */
    public static function sheetsSync($type, $result, $context = [])
    {
        $logContext = array_merge([
            'sync_type' => $type,
            'result' => $result,
            'tenant_id' => tenant_id()
        ], $context);

        self::info("Google Sheets Sync", $logContext);
    }

    /**
     * Log AI processing activities
     */
    public static function aiProcessing($input, $output, $context = [])
    {
        $logContext = array_merge([
            'ai_input' => $input,
            'ai_output' => $output,
            'tenant_id' => tenant_id()
        ], $context);

        self::debug("AI Processing", $logContext);
    }

    /**
     * Log setup and configuration changes
     */
    public static function configChange($setting, $oldValue, $newValue, $context = [])
    {
        $logContext = array_merge([
            'setting' => $setting,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'tenant_id' => tenant_id()
        ], $context);

        self::info("Configuration Change", $logContext);
    }
}
