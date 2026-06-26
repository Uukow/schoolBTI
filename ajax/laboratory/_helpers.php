<?php
/**
 * Shared helpers for LAB ajax handlers
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

if (!isLoggedIn()) { jsonResponse(false, 'Unauthorized'); }

$LAB_ADMIN_ROLES = ['Super Admin', 'Admin', 'Lab Director', 'Lab Manager'];
$LAB_TECH_ROLES  = array_merge($LAB_ADMIN_ROLES, ['Lab Technician']);
$LAB_ALL_ROLES   = array_merge($LAB_TECH_ROLES, ['Teacher', 'Student', 'Safety Officer', 'Procurement Officer', 'Maintenance Officer']);

$currentUser = getCurrentUser();
