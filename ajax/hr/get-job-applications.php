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

    $vacancyId = (int)($_GET['vacancy_id'] ?? 0);
    $status = sanitize($_GET['status'] ?? '');
    $search = sanitize($_GET['q'] ?? '');

    $sql = "SELECT a.*, v.job_title, v.department, v.vacancy_no
            FROM hr_job_applications a
            INNER JOIN hr_job_vacancies v ON a.vacancy_id = v.id WHERE 1=1";
    $params = [];
    $types = '';

    if ($vacancyId) {
        $sql .= " AND a.vacancy_id = ?";
        $params[] = $vacancyId;
        $types .= 'i';
    }
    if ($status) {
        $sql .= " AND a.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    if ($search) {
        $sql .= " AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ? OR a.application_no LIKE ?)";
        $like = '%' . $search . '%';
        $params = array_merge($params, [$like, $like, $like, $like]);
        $types .= 'ssss';
    }

    $sql .= " ORDER BY a.applied_at DESC";
    $applications = fetchAll(executeQuery($sql, $types, $params));

    jsonResponse(true, 'OK', $applications);
} catch (Throwable $e) {
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
