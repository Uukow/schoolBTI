<?php
ob_start();
require_once '../../config/config.php';
require_once '../../includes/hr-permission.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$currentUser = hrAjaxRequire('hr_recruitment', 'update');
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$applicationId = (int)($data['application_id'] ?? 0);
$status = sanitize($data['status'] ?? '');
if (!$applicationId || !$status) jsonResponse(false, 'Application and status required');

$result = RecruitmentService::updateApplicationStatus($applicationId, $status, $data);
jsonResponse($result['success'], $result['message']);
