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



    $status = sanitize($_GET['status'] ?? '');

    $search = sanitize($_GET['q'] ?? '');

    $year = (int)($_GET['year'] ?? 0);

    $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;



    $sql = "SELECT p.*, b.branch_name,

            CONCAT(f.first_name, ' ', f.last_name) AS facilitator_name,

            f.staff_id AS facilitator_code,

            (SELECT COUNT(*) FROM hr_ppdp_participants pt WHERE pt.program_id = p.id) AS participant_count,

            (SELECT COUNT(*) FROM hr_ppdp_participants pt WHERE pt.program_id = p.id AND pt.status = 'Completed') AS completed_count,

            DATEDIFF(p.end_date, p.start_date) + 1 AS duration_days

            FROM hr_ppdp_programs p

            LEFT JOIN branches b ON p.branch_id = b.id

            LEFT JOIN staff f ON p.facilitator_id = f.id

            WHERE 1=1";

    $params = [];

    $types = '';



    if ($status) {

        $sql .= " AND p.status = ?";

        $params[] = $status;

        $types .= 's';

    }

    if ($branchId) {

        $sql .= " AND p.branch_id = ?";

        $params[] = $branchId;

        $types .= 'i';

    }

    if ($year > 0) {

        $sql .= " AND YEAR(p.start_date) = ?";

        $params[] = $year;

        $types .= 'i';

    }

    if ($search) {

        $sql .= " AND (p.program_name LIKE ? OR p.program_code LIKE ? OR p.description LIKE ?)";

        $like = '%' . $search . '%';

        $params = array_merge($params, [$like, $like, $like]);

        $types .= 'sss';

    }



    $sql .= " ORDER BY p.start_date DESC, p.id DESC";

    $programs = fetchAll(executeQuery($sql, $types, $params));



    $stats = fetchOne(executeQuery(

        "SELECT

            COUNT(*) AS total_programs,

            SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) AS open_programs,

            SUM(CASE WHEN status = 'In_Progress' THEN 1 ELSE 0 END) AS in_progress,

            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_programs,

            (SELECT COUNT(*) FROM hr_ppdp_participants) AS total_enrollments,

            (SELECT COUNT(*) FROM hr_ppdp_participants WHERE status = 'Completed') AS completions,

            (SELECT COUNT(*) FROM hr_ppdp_programs WHERE status IN ('Open','In_Progress') AND start_date <= CURDATE() AND end_date >= CURDATE()) AS active_now

         FROM hr_ppdp_programs"

    ));



    jsonResponse(true, 'OK', ['programs' => $programs, 'stats' => $stats]);

} catch (Throwable $e) {

    error_log('get-ppdp-programs.php: ' . $e->getMessage());

    jsonResponse(false, 'Server error: ' . $e->getMessage());

}

