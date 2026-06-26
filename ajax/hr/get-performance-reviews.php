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

        && !(function_exists('canPerform') && canPerform('hr_reports', 'view'))) {

        jsonResponse(false, 'Permission denied');

    }



    $status = sanitize($_GET['status'] ?? '');

    $search = sanitize($_GET['q'] ?? '');

    $year = (int)($_GET['year'] ?? 0);

    $staffId = (int)($_GET['staff_id'] ?? 0);

    $department = sanitize($_GET['department'] ?? '');



    $sql = "SELECT pr.*, s.first_name, s.last_name, s.staff_id AS staff_code, s.designation, s.department,

            CONCAT(rv.first_name, ' ', rv.last_name) AS reviewer_name,

            u.username AS reviewer_username

            FROM hr_performance_reviews pr

            INNER JOIN staff s ON pr.staff_id = s.id

            LEFT JOIN users u ON pr.reviewer_id = u.id

            LEFT JOIN staff rv ON rv.user_id = u.id

            WHERE 1=1";

    $params = [];

    $types = '';



    if ($status) {

        $sql .= " AND pr.status = ?";

        $params[] = $status;

        $types .= 's';

    }

    if ($staffId) {

        $sql .= " AND pr.staff_id = ?";

        $params[] = $staffId;

        $types .= 'i';

    }

    if ($department) {

        $sql .= " AND s.department = ?";

        $params[] = $department;

        $types .= 's';

    }

    if ($year > 0) {

        $sql .= " AND YEAR(pr.review_date) = ?";

        $params[] = $year;

        $types .= 'i';

    }

    if ($search) {

        $sql .= " AND (pr.review_period LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.staff_id LIKE ?)";

        $like = '%' . $search . '%';

        $params = array_merge($params, [$like, $like, $like, $like]);

        $types .= 'ssss';

    }



    $sql .= " ORDER BY pr.review_date DESC, pr.id DESC";

    $reviews = fetchAll(executeQuery($sql, $types, $params));



    foreach ($reviews as &$r) {

        if (!empty($r['kpis']) && is_string($r['kpis'])) {

            $r['kpis'] = json_decode($r['kpis'], true) ?: [];

        }

    }

    unset($r);



    $stats = fetchOne(executeQuery(

        "SELECT

            COUNT(*) AS total,

            SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) AS draft,

            SUM(CASE WHEN status = 'Submitted' THEN 1 ELSE 0 END) AS submitted,

            SUM(CASE WHEN status = 'Acknowledged' THEN 1 ELSE 0 END) AS acknowledged,

            ROUND(AVG(CASE WHEN rating IS NOT NULL AND YEAR(review_date) = YEAR(CURDATE()) THEN rating END), 2) AS avg_rating_year,

            SUM(CASE WHEN rating >= 4.5 AND status != 'Archived' THEN 1 ELSE 0 END) AS top_performers,

            SUM(CASE WHEN QUARTER(review_date) = QUARTER(CURDATE()) AND YEAR(review_date) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS this_quarter

         FROM hr_performance_reviews"

    ));



    jsonResponse(true, 'OK', ['reviews' => $reviews, 'stats' => $stats]);

} catch (Throwable $e) {

    error_log('get-performance-reviews.php: ' . $e->getMessage());

    jsonResponse(false, 'Server error: ' . $e->getMessage());

}

