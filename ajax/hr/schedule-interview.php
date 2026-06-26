<?php
ob_start();
require_once '../../config/config.php';
require_once '../../includes/hr-permission.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$currentUser = hrAjaxRequire('hr_recruitment', 'create');
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$applicationId = (int)($data['application_id'] ?? 0);
if (!$applicationId) jsonResponse(false, 'Application required');
if (empty($data['interview_date'])) jsonResponse(false, 'Interview date required');

$result = RecruitmentService::scheduleInterview($applicationId, $data);
logActivity($currentUser['id'], 'Schedule Interview', 'HR', "Application ID: $applicationId");
jsonResponse($result['success'], $result['message']);
