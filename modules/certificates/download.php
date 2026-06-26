<?php
/**
 * Download Certificate (Alias)
 * 
 * Redirects to download-certificate.php for backward compatibility
 */

require_once '../../config/config.php';

$certificateId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($certificateId) {
    header('Location: ' . APP_URL . 'modules/certificates/download-certificate.php?id=' . $certificateId);
    exit;
} else {
    die('Invalid certificate ID');
}


