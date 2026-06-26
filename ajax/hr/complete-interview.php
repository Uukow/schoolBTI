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

$interviewId = (int)($data['interview_id'] ?? 0);
if (!$interviewId) jsonResponse(false, 'Interview ID required');

$result = RecruitmentService::completeInterview($interviewId, $data);
jsonResponse($result['success'], $result['message']);
