<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) {
        jsonResponse(false, 'Permission denied');
    }

    $status = sanitize($_GET['status'] ?? '');
    $search = sanitize($_GET['q'] ?? '');
    $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;

    $sql = "SELECT v.*, b.branch_name,
            (SELECT COUNT(*) FROM hr_job_applications a WHERE a.vacancy_id = v.id) as application_count,
            (SELECT COUNT(*) FROM hr_job_applications a WHERE a.vacancy_id = v.id AND a.status = 'Hired') as hired_count
            FROM hr_job_vacancies v
            LEFT JOIN branches b ON v.branch_id = b.id WHERE 1=1";
    $params = [];
    $types = '';

    if ($status) {
        $sql .= " AND v.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    if ($branchId) {
        $sql .= " AND v.branch_id = ?";
        $params[] = $branchId;
        $types .= 'i';
    }
    if ($search) {
        $sql .= " AND (v.job_title LIKE ? OR v.department LIKE ? OR v.vacancy_no LIKE ?)";
        $like = '%' . $search . '%';
        $params = array_merge($params, [$like, $like, $like]);
        $types .= 'sss';
    }

    $sql .= " ORDER BY v.created_at DESC";
    $vacancies = fetchAll(executeQuery($sql, $types, $params));

    $stats = fetchOne(executeQuery(
        "SELECT
            SUM(CASE WHEN v.status = 'Published' THEN 1 ELSE 0 END) as open_vacancies,
            (SELECT COUNT(*) FROM hr_job_applications) as total_applications,
            (SELECT COUNT(*) FROM hr_job_applications WHERE status = 'Interview') as in_interview,
            (SELECT COUNT(*) FROM hr_job_applications WHERE status = 'Offer') as offers_pending,
            (SELECT COUNT(*) FROM hr_job_applications WHERE status = 'Hired') as hired_total,
            (SELECT COUNT(*) FROM hr_interviews WHERE status = 'Scheduled') as scheduled_interviews
         FROM hr_job_vacancies v"
    ));

    jsonResponse(true, 'OK', ['vacancies' => $vacancies, 'stats' => $stats]);
} catch (Throwable $e) {
    error_log('get-vacancies.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
