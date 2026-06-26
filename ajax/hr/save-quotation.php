<?php

ob_start();

require_once '../../config/config.php';

error_reporting(E_ALL);

ini_set('display_errors', 0);

ob_clean();

header('Content-Type: application/json; charset=utf-8');



/**

 * @return float Sum of line totals saved

 */

function saveQuotationItems($quotationId, array $items)

{

    executeQuery("DELETE FROM hr_quotation_items WHERE quotation_id = ?", 'i', [$quotationId]);

    $sum = 0.0;

    $lineNo = 1;

    foreach ($items as $row) {

        if (!is_array($row)) {

            continue;

        }

        $name = sanitize($row['item_name'] ?? '');

        if ($name === '') {

            continue;

        }

        $qty = max(0, (float)($row['quantity'] ?? 1));

        if ($qty <= 0) {

            $qty = 1;

        }

        $unitPrice = max(0, (float)($row['unit_price'] ?? 0));

        $lineTotal = round($qty * $unitPrice, 2);

        $sum += $lineTotal;

        executeQuery(

            "INSERT INTO hr_quotation_items (quotation_id, line_no, item_name, description, quantity, unit, unit_price, line_total)

             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",

            'iissdsdd',

            [

                $quotationId,

                $lineNo++,

                $name,

                sanitize($row['description'] ?? ''),

                $qty,

                sanitize($row['unit'] ?? 'pcs'),

                $unitPrice,

                $lineTotal,

            ]

        );

    }

    return $sum;

}



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

    $id = (int)($data['id'] ?? 0);

    $action = sanitize($data['action'] ?? '');

    $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : null;



    $staffRow = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));



    if ($id > 0 && in_array($action, ['publish_public', 'unpublish_public'], true)) {

        if ($action === 'publish_public') {

            $row = fetchOne(executeQuery("SELECT * FROM hr_quotations WHERE id = ?", 'i', [$id]));

            if (!$row) {

                jsonResponse(false, 'Quotation not found');

            }

            $token = $row['public_token'] ?: bin2hex(random_bytes(16));

            $deadline = !empty($data['public_deadline'])

                ? sanitize($data['public_deadline'])

                : ($row['required_by_date'] ?? date('Y-m-d', strtotime('+14 days')));

            executeQuery(

                "UPDATE hr_quotations SET is_public = 1, public_token = ?, public_deadline = ?, published_at = NOW(),

                 status = CASE WHEN status = 'Draft' THEN 'Pending_Approval' ELSE status END WHERE id = ?",

                'ssi', [$token, $deadline, $id]

            );

            logActivity($currentUser['id'], 'Publish Quotation', 'HR', "Quotation ID $id published for vendors");

            jsonResponse(true, 'Quotation published for vendor submissions', [

                'public_token' => $token,

                'public_url' => APP_URL . 'quotation-portal.php?t=' . $token,

            ]);

        }

        executeQuery("UPDATE hr_quotations SET is_public = 0 WHERE id = ?", 'i', [$id]);

        logActivity($currentUser['id'], 'Unpublish Quotation', 'HR', "Quotation ID $id");

        jsonResponse(true, 'Public vendor portal closed');

    }



    if ($id > 0 && !empty($data['status']) && empty($data['title'])) {

        $allowed = ['Draft', 'Pending_Approval', 'Approved', 'Rejected', 'Closed'];

        $status = sanitize($data['status']);

        if (!in_array($status, $allowed, true)) {

            jsonResponse(false, 'Invalid status');

        }

        if (in_array($status, ['Approved', 'Rejected'], true)) {

            executeQuery(

                "UPDATE hr_quotations SET status = ?, approved_by = ?, approved_at = NOW(), is_public = 0 WHERE id = ?",

                'sii', [$status, $currentUser['id'], $id]

            );

        } else {

            executeQuery("UPDATE hr_quotations SET status = ? WHERE id = ?", 'si', [$status, $id]);

        }

        logActivity($currentUser['id'], 'Update Quotation Status', 'HR', "Quotation ID $id → $status");

        jsonResponse(true, 'Quotation status updated');

    }



    if ($id > 0) {

        $title = sanitize($data['title'] ?? '');

        if (empty($title)) {

            jsonResponse(false, 'Title is required');

        }

        $requiredBy = !empty($data['required_by_date']) ? sanitize($data['required_by_date']) : null;

        $totalEstimated = (float)($data['total_estimated'] ?? 0);

        $branchId = isset($data['branch_id']) && $data['branch_id'] !== '' ? (int)$data['branch_id'] : null;

        $status = sanitize($data['status'] ?? 'Draft');



        if ($items !== null) {

            $itemsSum = saveQuotationItems($id, $items);

            if ($itemsSum > 0) {

                $totalEstimated = $itemsSum;

            }

        }



        $stmt = executeQuery(

            "UPDATE hr_quotations SET title=?, description=?, required_by_date=?, total_estimated=?, branch_id=?, status=? WHERE id=?",

            'sssdisi',

            [$title, sanitize($data['description'] ?? ''), $requiredBy, $totalEstimated, $branchId, $status, $id]

        );

        if ($stmt) {

            logActivity($currentUser['id'], 'Update Quotation', 'HR', "Quotation ID: $id");

            jsonResponse(true, 'Quotation updated successfully');

        }

        jsonResponse(false, 'Failed to update quotation');

    }



    $title = sanitize($data['title'] ?? '');

    $description = sanitize($data['description'] ?? '');

    $requiredBy = !empty($data['required_by_date']) ? sanitize($data['required_by_date']) : null;

    $totalEstimated = (float)($data['total_estimated'] ?? 0);

    $status = sanitize($data['status'] ?? 'Draft');

    $branchId = isset($data['branch_id']) && $data['branch_id'] !== ''

        ? (int)$data['branch_id']

        : ($currentUser['branch_id'] ?? null);



    if (empty($title)) {

        jsonResponse(false, 'Title is required');

    }



    $quotationNo = HrNumberService::next('QUO-', 'hr_quotations', 'quotation_no');

    $requestedBy = $staffRow['id'] ?? null;

    $stmt = executeQuery(

        "INSERT INTO hr_quotations (quotation_no, title, description, requested_by, branch_id, required_by_date, total_estimated, status)

         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",

        'sssiisds',

        [$quotationNo, $title, $description, $requestedBy, $branchId, $requiredBy, $totalEstimated, $status]

    );



    if ($stmt) {

        $newId = (int)getDBConnection()->insert_id;

        if ($newId && $items !== null) {

            $itemsSum = saveQuotationItems($newId, $items);

            if ($itemsSum > 0) {

                executeQuery("UPDATE hr_quotations SET total_estimated = ? WHERE id = ?", 'di', [$itemsSum, $newId]);

            }

        }

        logActivity($currentUser['id'], 'Create Quotation', 'HR', "Quotation: $quotationNo");

        jsonResponse(true, 'Quotation created successfully', ['quotation_no' => $quotationNo, 'id' => $newId]);

    }



    global $conn;

    jsonResponse(false, 'Failed to create quotation: ' . ($conn->error ?? 'Unknown error'));

} catch (Throwable $e) {

    error_log('save-quotation.php: ' . $e->getMessage());

    jsonResponse(false, 'Server error: ' . $e->getMessage());

}

