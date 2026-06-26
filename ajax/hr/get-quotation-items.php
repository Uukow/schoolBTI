<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }

    $quotationId = (int)($_GET['quotation_id'] ?? 0);
    if (!$quotationId) {
        jsonResponse(false, 'Quotation ID required');
    }

    $items = fetchAll(executeQuery(
        "SELECT * FROM hr_quotation_items WHERE quotation_id = ? ORDER BY line_no ASC, id ASC",
        'i', [$quotationId]
    ));

    jsonResponse(true, 'OK', $items);
} catch (Throwable $e) {
    error_log('get-quotation-items.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
