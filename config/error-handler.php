<?php
/**
 * Error Handler for Production
 * 
 * Catches and logs errors without displaying them
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Only use this if EnvironmentDetector fails
if (!function_exists('productionErrorHandler')) {
    function productionErrorHandler($errno, $errstr, $errfile, $errline) {
        // Log error
        $logFile = defined('ABSPATH') ? ABSPATH . 'logs/error.log' : __DIR__ . '/../logs/error.log';
        $message = date('Y-m-d H:i:s') . " - Error [$errno]: $errstr in $errfile on line $errline\n";
        @file_put_contents($logFile, $message, FILE_APPEND);
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    function productionShutdownHandler() {
        $error = error_get_last();
        if ($error !== NULL && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $logFile = defined('ABSPATH') ? ABSPATH . 'logs/error.log' : __DIR__ . '/../logs/error.log';
            $message = date('Y-m-d H:i:s') . " - Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}\n";
            @file_put_contents($logFile, $message, FILE_APPEND);
        }
    }
}

