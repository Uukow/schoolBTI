<?php
/**
 * HR Service Loader
 * Loads all HR service classes
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

$hrServicesPath = ABSPATH . 'includes/services/hr/';
$hrServices = [
    'HrNumberService.php',
    'AttendanceCalculationService.php',
    'LeaveBalanceService.php',
    'HrDashboardService.php',
    'HrAuditService.php',
    'PayrollService.php',
    'PayslipService.php',
    'RecruitmentService.php',
    'OfferLetterService.php',
    'HrReportService.php',
];

foreach ($hrServices as $serviceFile) {
    $path = $hrServicesPath . $serviceFile;
    if (file_exists($path)) {
        require_once $path;
    }
}

if (file_exists(ABSPATH . 'includes/services/NotificationService.php')) {
    require_once ABSPATH . 'includes/services/NotificationService.php';
}
