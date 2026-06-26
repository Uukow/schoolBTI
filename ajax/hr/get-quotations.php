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

    if (!hasRole(['Super Admin', 'Admin', 'Accountant'])

        && !(function_exists('canPerform') && canPerform('hr_quotations', 'view'))) {

        jsonResponse(false, 'Permission denied');

    }



    $status = sanitize($_GET['status'] ?? '');

    $search = sanitize($_GET['q'] ?? '');

    $year = (int)($_GET['year'] ?? 0);

    $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;

    $publicOnly = isset($_GET['public']) && $_GET['public'] === '1';



    $sql = "SELECT q.*, b.branch_name,

            CONCAT(s.first_name, ' ', s.last_name) AS requested_by_name,

            (SELECT COUNT(*) FROM hr_quotation_vendors v WHERE v.quotation_id = q.id) AS vendor_count,
            (SELECT COUNT(*) FROM hr_quotation_items i WHERE i.quotation_id = q.id) AS item_count,
            (SELECT COALESCE(SUM(i.line_total), 0) FROM hr_quotation_items i WHERE i.quotation_id = q.id) AS items_total,

            (SELECT MIN(v.quoted_amount) FROM hr_quotation_vendors v WHERE v.quotation_id = q.id) AS lowest_quote,

            (SELECT MAX(v.quoted_amount) FROM hr_quotation_vendors v WHERE v.quotation_id = q.id) AS highest_quote,

            (SELECT v.vendor_name FROM hr_quotation_vendors v WHERE v.quotation_id = q.id AND v.is_selected = 1 LIMIT 1) AS selected_vendor

            FROM hr_quotations q

            LEFT JOIN branches b ON q.branch_id = b.id

            LEFT JOIN staff s ON q.requested_by = s.id

            WHERE 1=1";

    $params = [];

    $types = '';



    if ($status) {

        $sql .= " AND q.status = ?";

        $params[] = $status;

        $types .= 's';

    }

    if ($branchId) {

        $sql .= " AND q.branch_id = ?";

        $params[] = $branchId;

        $types .= 'i';

    }

    if ($year > 0) {

        $sql .= " AND YEAR(q.created_at) = ?";

        $params[] = $year;

        $types .= 'i';

    }

    if ($publicOnly) {

        $sql .= " AND q.is_public = 1";

    }

    if ($search) {

        $sql .= " AND (q.title LIKE ? OR q.quotation_no LIKE ? OR q.description LIKE ?)";

        $like = '%' . $search . '%';

        $params = array_merge($params, [$like, $like, $like]);

        $types .= 'sss';

    }



    $sql .= " ORDER BY q.created_at DESC";

    $quotations = fetchAll(executeQuery($sql, $types, $params));



    $stats = fetchOne(executeQuery(

        "SELECT

            COUNT(*) AS total,

            SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) AS draft,

            SUM(CASE WHEN status = 'Pending_Approval' THEN 1 ELSE 0 END) AS pending,

            SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved,

            SUM(CASE WHEN is_public = 1 THEN 1 ELSE 0 END) AS public_open,

            (SELECT COUNT(*) FROM hr_quotation_vendors) AS vendor_quotes,

            COALESCE(SUM(total_estimated), 0) AS total_estimated_value

         FROM hr_quotations"

    ));



    jsonResponse(true, 'OK', ['quotations' => $quotations, 'stats' => $stats]);

} catch (Throwable $e) {

    error_log('get-quotations.php: ' . $e->getMessage());

    jsonResponse(false, 'Server error: ' . $e->getMessage());

}

