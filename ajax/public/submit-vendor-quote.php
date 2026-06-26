<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $token = sanitize($_POST['public_token'] ?? '');
    $quotationId = (int)($_POST['quotation_id'] ?? 0);

    if ($token) {
        $quotation = fetchOne(executeQuery(
            "SELECT * FROM hr_quotations WHERE public_token = ? AND is_public = 1",
            's', [$token]
        ));
    } elseif ($quotationId) {
        $quotation = fetchOne(executeQuery(
            "SELECT * FROM hr_quotations WHERE id = ? AND is_public = 1",
            'i', [$quotationId]
        ));
    } else {
        jsonResponse(false, 'Invalid quotation reference');
    }

    if (!$quotation) {
        jsonResponse(false, 'This quotation is not open for vendor submissions');
    }
    if (!empty($quotation['public_deadline']) && $quotation['public_deadline'] < date('Y-m-d')) {
        jsonResponse(false, 'The submission deadline has passed');
    }
    if (in_array($quotation['status'], ['Approved', 'Rejected', 'Closed'], true)) {
        jsonResponse(false, 'This quotation is no longer accepting submissions');
    }

    $vendorName = sanitize($_POST['vendor_name'] ?? '');
    $vendorContact = sanitize($_POST['vendor_contact'] ?? '');
    $quotedAmount = (float)($_POST['quoted_amount'] ?? 0);
    $deliveryDays = !empty($_POST['delivery_days']) ? (int)$_POST['delivery_days'] : null;
    $notes = sanitize($_POST['notes'] ?? '');

    if (empty($vendorName) || $quotedAmount <= 0) {
        jsonResponse(false, 'Company name and quoted amount are required');
    }
    if (empty($vendorContact)) {
        jsonResponse(false, 'Contact email or phone is required');
    }

    $attachmentPath = null;
    if (!empty($_FILES['attachment']['name'])) {
        if (!is_dir(QUOTATION_VENDOR_PATH)) {
            @mkdir(QUOTATION_VENDOR_PATH, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'])) {
            jsonResponse(false, 'Attachment must be PDF, Word, Excel, or image');
        }
        $filename = 'quote_' . $quotation['id'] . '_' . time() . '_' . preg_replace('/[^a-z0-9]/i', '', $vendorName) . '.' . $ext;
        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], QUOTATION_VENDOR_PATH . $filename)) {
            jsonResponse(false, 'Failed to upload attachment');
        }
        $attachmentPath = 'uploads/quotations/vendors/' . $filename;
    }

    executeQuery(
        "INSERT INTO hr_quotation_vendors (quotation_id, vendor_name, vendor_contact, quoted_amount, delivery_days, attachment_path, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        'issdiss',
        [
            (int)$quotation['id'],
            $vendorName,
            $vendorContact,
            $quotedAmount,
            $deliveryDays,
            $attachmentPath,
            $notes,
        ]
    );

    jsonResponse(true, 'Your quotation has been submitted successfully. Thank you!');
} catch (Throwable $e) {
    error_log('submit-vendor-quote.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
