<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$currentUser = hrAjaxRequire('hr_recruitment', 'update');
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$offerId = (int)($data['offer_id'] ?? 0);
if (!$offerId) jsonResponse(false, 'Offer ID required');

$result = RecruitmentService::sendOfferEmail($offerId);
logActivity($currentUser['id'], 'Send Offer Letter', 'HR', "Offer ID: $offerId");
jsonResponse($result['success'], $result['message']);
