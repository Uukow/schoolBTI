<?php
ob_start();
require_once '../../config/config.php';
require_once '../../includes/hr-permission.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

hrAjaxRequire('hr_recruitment', 'view');

$applicationId = (int)($_GET['application_id'] ?? 0);
$sql = "SELECT o.*, a.application_no, a.first_name, a.last_name, v.job_title
        FROM hr_offer_letters o
        INNER JOIN hr_job_applications a ON o.application_id = a.id
        INNER JOIN hr_job_vacancies v ON a.vacancy_id = v.id WHERE 1=1";
$params = [];
$types = '';
if ($applicationId) {
    $sql .= " AND o.application_id = ?";
    $params[] = $applicationId;
    $types .= 'i';
}
$sql .= " ORDER BY o.created_at DESC";
jsonResponse(true, 'OK', fetchAll(executeQuery($sql, $types, $params)));
