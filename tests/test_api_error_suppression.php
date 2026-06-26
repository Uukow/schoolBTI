<?php
// Debug script to check if HTML errors are suppressed
// Usage: php tests/test_api_error_suppression.php

// Mock server vars
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';

ob_start();
require_once __DIR__ . '/../api/config.php';

// Trigger a notice/warning
$undefined_var = $undefined_array['key']; 

// Trigger a custom trigger_error
trigger_error("This is a test warning that should not appear in output", E_USER_WARNING);

$output = ob_get_clean();

echo "Output length: " . strlen($output) . "\n";
echo "Raw Output:\n" . $output . "\n";

if (strpos($output, '<br') !== false || strpos($output, 'Warning') !== false) {
    echo "FAIL: HTML errors are leaking.\n";
} else {
    echo "PASS: No HTML errors detected.\n";
}
