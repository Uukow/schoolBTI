<?php
/**
 * Download Certificate as PDF
 * 
 * Generate and download certificate as PDF file
 */

require_once '../../config/config.php';

requireLogin();

$certificateId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$certificateId) {
    die('Invalid certificate ID');
}

// Get certificate details
$sql = "SELECT c.*, ct.*, s.*, 
        CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) as student_name,
        acs.session_name, cl.class_name, b.branch_name
        FROM certificates c
        INNER JOIN certificate_templates ct ON c.template_id = ct.id
        INNER JOIN students s ON c.student_id = s.id
        LEFT JOIN academic_sessions acs ON c.session_id = acs.id
        LEFT JOIN classes cl ON c.class_id = cl.id
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE c.id = ?";

$stmt = executeQuery($sql, 'i', [$certificateId]);
$certificate = fetchOne($stmt);

if (!$certificate) {
    die('Certificate not found');
}

// Check access permission
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin = hasRole(['Admin']);

if (!$isSuperAdmin && !$isAdmin) {
    $student = getStudentByUserId($currentUser['id']);
    if (!$student || $student['id'] != $certificate['student_id']) {
        die('Access denied');
    }
}

// Check if mPDF is available
if (!class_exists('Mpdf\Mpdf')) {
    // Fallback: redirect to view page
    header('Location: ' . APP_URL . 'modules/certificates/view-certificate.php?id=' . $certificateId);
    exit;
}

try {
    require_once '../../vendor/autoload.php';
    
    // Create PDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => $certificate['page_size'] ?? 'A4',
        'orientation' => $certificate['page_orientation'] ?? 'L',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
    ]);
    
    // Replace placeholders
    $headerHtml = $certificate['header_html'] ?? '';
    $bodyHtml = $certificate['body_html'] ?? '';
    $footerHtml = $certificate['footer_html'] ?? '';
    
    $replacements = [
        '{{SCHOOL_NAME}}' => APP_NAME,
        '{{BRANCH_NAME}}' => $certificate['branch_name'] ?? '',
        '{{STUDENT_NAME}}' => $certificate['student_name'],
        '{{STUDENT_ID}}' => $certificate['student_id'] ?? '',
        '{{CLASS}}' => $certificate['class_name'] ?? '',
        '{{SESSION}}' => $certificate['session_name'] ?? '',
        '{{DATE}}' => formatDate($certificate['issue_date']),
        '{{ISSUE_DATE}}' => formatDate($certificate['issue_date']),
        '{{CERTIFICATE_ID}}' => $certificate['certificate_number'],
        '{{GPA}}' => $certificate['gpa'] ?? 'N/A',
        '{{CGPA}}' => $certificate['cgpa'] ?? 'N/A',
        '{{ATTENDANCE}}' => $certificate['attendance_percentage'] ? $certificate['attendance_percentage'] . '%' : 'N/A',
        '{{RANK}}' => $certificate['class_rank'] ?? 'N/A',
        '{{PRINCIPAL_SIGNATURE}}' => '<div style="border-top:2px solid #000;display:inline-block;padding:5px 30px;min-width:150px;text-align:center;"><strong>' . htmlspecialchars($certificate['signature_1_label'] ?? 'Principal') . '</strong></div>',
        '{{REGISTRAR_SIGNATURE}}' => '<div style="border-top:2px solid #000;display:inline-block;padding:5px 30px;min-width:150px;text-align:center;"><strong>' . htmlspecialchars($certificate['signature_2_label'] ?? 'Registrar') . '</strong></div>',
    ];
    
    foreach ($replacements as $placeholder => $value) {
        $headerHtml = str_replace($placeholder, $value, $headerHtml);
        $bodyHtml = str_replace($placeholder, $value, $bodyHtml);
        $footerHtml = str_replace($placeholder, $value, $footerHtml);
    }
    
    // Add QR code if enabled
    if ($certificate['include_qr_code']) {
        $qrUrl = APP_URL . 'verify-certificate.php?code=' . $certificate['verification_code'];
        $footerHtml .= '<div style="text-align:center;margin-top:20px;">';
        $footerHtml .= '<img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($qrUrl) . '" style="width:100px;height:100px;" alt="QR Code">';
        $footerHtml .= '</div>';
    }
    
    // Combine HTML
    $html = '';
    if ($headerHtml) {
        $html .= '<div style="margin-bottom:20px;">' . $headerHtml . '</div>';
    }
    $html .= '<div style="margin:30px 0;">' . $bodyHtml . '</div>';
    if ($footerHtml) {
        $html .= '<div style="margin-top:30px;">' . $footerHtml . '</div>';
    }
    
    // Write HTML to PDF
    $mpdf->WriteHTML($html);
    
    // Output PDF
    $filename = 'Certificate_' . $certificate['certificate_number'] . '.pdf';
    $mpdf->Output($filename, 'D'); // D = Download
    
} catch (Exception $e) {
    // Fallback: redirect to view page
    header('Location: ' . APP_URL . 'modules/certificates/view-certificate.php?id=' . $certificateId);
    exit;
}


