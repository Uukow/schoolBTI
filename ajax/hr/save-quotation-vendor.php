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

    $canManage = hasRole(['Super Admin', 'Admin', 'Accountant'])
        || (function_exists('canPerform') && canPerform('hr_quotations', 'approve'));
    if (!$canManage) {
        jsonResponse(false, 'Permission denied');
    }

    $currentUser = getCurrentUser();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = sanitize($data['action'] ?? 'add');
    $quotationId = (int)($data['quotation_id'] ?? 0);
    $vendorId = (int)($data['vendor_id'] ?? 0);

    if ($action === 'select' && $vendorId && $quotationId) {
        executeQuery("UPDATE hr_quotation_vendors SET is_selected = 0 WHERE quotation_id = ?", 'i', [$quotationId]);
        executeQuery("UPDATE hr_quotation_vendors SET is_selected = 1 WHERE id = ? AND quotation_id = ?", 'ii', [$vendorId, $quotationId]);
        $winner = fetchOne(executeQuery("SELECT quoted_amount FROM hr_quotation_vendors WHERE id = ?", 'i', [$vendorId]));
        if ($winner) {
            executeQuery("UPDATE hr_quotations SET total_estimated = ? WHERE id = ?", 'di', [(float)$winner['quoted_amount'], $quotationId]);
        }
        logActivity($currentUser['id'], 'Select Vendor Quote', 'HR', "Quotation $quotationId, vendor $vendorId");
        jsonResponse(true, 'Winning vendor selected');
    }

    if ($action === 'remove' && $vendorId) {
        executeQuery("DELETE FROM hr_quotation_vendors WHERE id = ?", 'i', [$vendorId]);
        jsonResponse(true, 'Vendor quote removed');
    }

    if ($action === 'add' && $quotationId) {
        $name = sanitize($data['vendor_name'] ?? '');
        $amount = (float)($data['quoted_amount'] ?? 0);
        if (empty($name) || $amount <= 0) {
            jsonResponse(false, 'Vendor name and quoted amount are required');
        }
        executeQuery(
            "INSERT INTO hr_quotation_vendors (quotation_id, vendor_name, vendor_contact, quoted_amount, delivery_days, notes)
             VALUES (?, ?, ?, ?, ?, ?)",
            'issdis',
            [
                $quotationId,
                $name,
                sanitize($data['vendor_contact'] ?? ''),
                $amount,
                !empty($data['delivery_days']) ? (int)$data['delivery_days'] : null,
                sanitize($data['notes'] ?? ''),
            ]
        );
        jsonResponse(true, 'Vendor quote added');
    }

    jsonResponse(false, 'Invalid request');
} catch (Throwable $e) {
    error_log('save-quotation-vendor.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
