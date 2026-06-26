<?php
ob_start();
require_once '../../config/config.php';
require_once '../../includes/hr-permission.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$currentUser = hrAjaxRequire('hr_recruitment', 'approve');
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$applicationId = (int)($data['application_id'] ?? 0);
if (!$applicationId) jsonResponse(false, 'Application required');

$result = RecruitmentService::hireApplication($applicationId, $data, $currentUser);
jsonResponse($result['success'], $result['message'], $result['success'] ? [
    'staff_id' => $result['staff_id'],
    'staff_code' => $result['staff_code'],
] : null);
