<?php
/**
 * View Certificate
 * 
 * Display certificate in a printable format
 */

require_once '../../config/config.php';

requireLogin();

$certificateId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$templateId = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0; // Allow template override

if (!$certificateId) {
    die('Invalid certificate ID');
}

// Get certificate details
$sql = "SELECT c.*, s.*, 
        CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) as student_name,
        acs.session_name, cl.class_name, b.branch_name,
        u.username as issued_by_name
        FROM certificates c
        INNER JOIN students s ON c.student_id = s.id
        LEFT JOIN academic_sessions acs ON c.session_id = acs.id
        LEFT JOIN classes cl ON c.class_id = cl.id
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN users u ON c.issued_by = u.id
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

// Students can only view their own certificates
if (!$isSuperAdmin && !$isAdmin) {
    $student = getStudentByUserId($currentUser['id']);
    if (!$student || $student['id'] != $certificate['student_id']) {
        die('Access denied. You can only view your own certificates.');
    }
}

// Get template - use override if provided, otherwise use certificate's template
$templateToUse = $templateId > 0 ? $templateId : $certificate['template_id'];

// Get template details
$templateSql = "SELECT * FROM certificate_templates WHERE id = ?";
$templateStmt = executeQuery($templateSql, 'i', [$templateToUse]);
$template = fetchOne($templateStmt);

if (!$template) {
    die('Template not found');
}

// Get all available templates for selector (admin only)
$availableTemplates = [];
if ($isSuperAdmin || $isAdmin) {
    $allTemplatesSql = "SELECT id, template_name, certificate_type FROM certificate_templates WHERE is_active = 1 ORDER BY template_name";
    $availableTemplates = fetchAll(executeQuery($allTemplatesSql));
}

// Parse academic data
$academicData = json_decode($certificate['academic_data'], true) ?? [];

// Replace placeholders in template HTML
$headerHtml = $template['header_html'] ?? '';
$bodyHtml = $template['body_html'] ?? '';
$footerHtml = $template['footer_html'] ?? '';

// Replace placeholders
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
    '{{PRINCIPAL_SIGNATURE}}' => '<div style="border-top:2px solid #000;display:inline-block;padding:5px 30px;min-width:150px;text-align:center;"><strong>' . htmlspecialchars($template['signature_1_label'] ?? 'Principal') . '</strong></div>',
    '{{REGISTRAR_SIGNATURE}}' => '<div style="border-top:2px solid #000;display:inline-block;padding:5px 30px;min-width:150px;text-align:center;"><strong>' . htmlspecialchars($template['signature_2_label'] ?? 'Registrar') . '</strong></div>',
    '{{QR_CODE}}' => $template['include_qr_code'] ? '<div style="text-align:center;margin:20px 0;"><img src="' . htmlspecialchars(generateQRCodeImage($certificate['verification_code'])) . '" style="width:100px;height:100px;" alt="QR Code"></div>' : ''
];

foreach ($replacements as $placeholder => $value) {
    $headerHtml = str_replace($placeholder, $value, $headerHtml);
    $bodyHtml = str_replace($placeholder, $value, $bodyHtml);
    $footerHtml = str_replace($placeholder, $value, $footerHtml);
}

// Generate QR code if needed
function generateQRCodeImage($code) {
    // Generate verification URL
    $verificationUrl = APP_URL . 'verify-certificate.php?code=' . urlencode($code);
    
    // Use online QR code API service (no library required)
    // Alternative: You can use a QR code library like endroid/qr-code if installed
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($verificationUrl);
    
    return $qrCodeUrl;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?php echo htmlspecialchars($certificate['certificate_number']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Crimson+Text:ital,wght@0,400;0,600;1,400&family=Old+Standard+TT:wght@400;700&family=Dancing+Script:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: <?php echo $template['page_size'] ?? 'A4'; ?> <?php echo $template['page_orientation'] ?? 'landscape'; ?>;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
                background: #fff;
            }
            .no-print {
                display: none !important;
            }
            .certificate-wrapper {
                box-shadow: none;
                page-break-inside: avoid;
                page-break-after: always;
                page-break-before: always;
            }
            .certificate-container {
                page-break-inside: avoid;
            }
            .certificate-header,
            .certificate-body,
            .certificate-footer {
                page-break-inside: avoid;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Crimson Text', 'Times New Roman', serif;
            margin: 0;
            padding: 20px;
            background: #e5e5e5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .print-actions {
            text-align: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .print-actions .btn {
            margin: 0 5px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #0d6efd;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .certificate-wrapper {
            width: <?php echo $template['page_orientation'] == 'landscape' ? '297mm' : '210mm'; ?>;
            height: <?php echo $template['page_orientation'] == 'landscape' ? '210mm' : '297mm'; ?>;
            max-height: <?php echo $template['page_orientation'] == 'landscape' ? '210mm' : '297mm'; ?>;
            margin: 0 auto;
            background: #ffffff;
            position: relative;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            page-break-inside: avoid;
            page-break-after: always;
        }
        
        .template-selector {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .template-selector label {
            font-weight: 600;
            margin: 0;
        }
        
        .template-selector select {
            flex: 1;
            min-width: 250px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .template-selector .btn {
            padding: 8px 20px;
        }
        
        /* Top Decorative Border Pattern */
        .certificate-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 25px;
            background: repeating-linear-gradient(
                90deg,
                #1a3a5c 0px,
                #1a3a5c 8px,
                #2d5a8a 8px,
                #2d5a8a 16px,
                #1a3a5c 16px,
                #1a3a5c 24px,
                #2d5a8a 24px,
                #2d5a8a 32px
            );
            z-index: 10;
        }
        
        /* Bottom Decorative Border Pattern */
        .certificate-wrapper::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 25px;
            background: repeating-linear-gradient(
                90deg,
                #1a3a5c 0px,
                #1a3a5c 8px,
                #2d5a8a 8px,
                #2d5a8a 16px,
                #1a3a5c 16px,
                #1a3a5c 24px,
                #2d5a8a 24px,
                #2d5a8a 32px
            );
            z-index: 10;
        }
        
        .certificate-container {
            position: relative;
            z-index: 2;
            padding: 20mm 15mm;
            height: 100%;
            max-height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* Guilloche Pattern Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 80%;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(26, 58, 92, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(26, 58, 92, 0.03) 0%, transparent 50%),
                repeating-linear-gradient(
                    0deg,
                    transparent,
                    transparent 2px,
                    rgba(26, 58, 92, 0.02) 2px,
                    rgba(26, 58, 92, 0.02) 4px
                ),
                repeating-linear-gradient(
                    90deg,
                    transparent,
                    transparent 2px,
                    rgba(26, 58, 92, 0.02) 2px,
                    rgba(26, 58, 92, 0.02) 4px
                );
            z-index: 0;
            pointer-events: none;
            opacity: 0.4;
        }
        
        /* Official Seal with Ribbons */
        .official-seal {
            position: absolute;
            top: 10mm;
            right: 15mm;
            width: <?php echo $template['page_orientation'] == 'landscape' ? '100px' : '90px'; ?>;
            height: <?php echo $template['page_orientation'] == 'landscape' ? '100px' : '90px'; ?>;
            z-index: 5;
        }
        
        .seal-circle {
            width: <?php echo $template['page_orientation'] == 'landscape' ? '80px' : '70px'; ?>;
            height: <?php echo $template['page_orientation'] == 'landscape' ? '80px' : '70px'; ?>;
            border: 3px solid #1a3a5c;
            border-radius: 50%;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .seal-circle::before {
            content: 'AWARD';
            font-size: 14px;
            font-weight: bold;
            color: #1a3a5c;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        
        .seal-circle::after {
            content: '<?php echo date('Y'); ?>';
            font-size: 18px;
            font-weight: bold;
            color: #1a3a5c;
        }
        
        .seal-ribbon {
            position: absolute;
            width: 30px;
            height: 40px;
            background: #1a3a5c;
            border-radius: 0 0 15px 15px;
        }
        
        .seal-ribbon.left {
            left: -15px;
            top: 20px;
            transform: rotate(-20deg);
        }
        
        .seal-ribbon.right {
            right: -15px;
            top: 20px;
            transform: rotate(20deg);
        }
        
        .certificate-header {
            text-align: center;
            margin-bottom: 20px;
            padding-top: 10px;
            position: relative;
            flex-shrink: 0;
        }
        
        .certificate-header h1 {
            font-family: 'Old Standard TT', 'Times New Roman', serif;
            font-size: <?php echo $template['page_orientation'] == 'landscape' ? '56px' : '48px'; ?>;
            font-weight: 700;
            color: #1a3a5c;
            margin: 0;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            font-style: normal;
            line-height: 1.2;
        }
        
        .certificate-header h2 {
            font-family: 'Arial', sans-serif;
            font-size: <?php echo $template['page_orientation'] == 'landscape' ? '18px' : '16px'; ?>;
            font-weight: 600;
            color: #1a3a5c;
            margin: 8px 0 0 0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        /* Ribbon Banner */
        .ribbon-banner {
            background: linear-gradient(135deg, #1a3a5c 0%, #2d5a8a 100%);
            color: #ffffff;
            padding: 8px 30px;
            margin: 15px auto;
            display: inline-block;
            position: relative;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            max-width: 90%;
            flex-shrink: 0;
        }
        
        .ribbon-banner::before,
        .ribbon-banner::after {
            content: '';
            position: absolute;
            top: 100%;
            width: 0;
            height: 0;
            border-style: solid;
        }
        
        .ribbon-banner::before {
            left: 0;
            border-width: 15px 20px 0 0;
            border-color: #1a3a5c transparent transparent transparent;
        }
        
        .ribbon-banner::after {
            right: 0;
            border-width: 15px 0 0 20px;
            border-color: #1a3a5c transparent transparent transparent;
        }
        
        .certificate-body {
            flex: 1;
            padding: 10px 0;
            text-align: center;
            font-size: <?php echo $template['page_orientation'] == 'landscape' ? '15px' : '14px'; ?>;
            line-height: 1.6;
            color: #1a3a5c;
            font-family: 'Crimson Text', serif;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .certificate-body p {
            margin: 8px 0;
            font-size: <?php echo $template['page_orientation'] == 'landscape' ? '15px' : '14px'; ?>;
            color: #1a3a5c;
        }
        
        .student-name-highlight {
            font-family: 'Dancing Script', cursive;
            font-size: <?php echo $template['page_orientation'] == 'landscape' ? '42px' : '36px'; ?>;
            font-weight: 700;
            color: #1a3a5c;
            margin: 15px 0;
            padding: 8px 0;
            display: inline-block;
            min-width: <?php echo $template['page_orientation'] == 'landscape' ? '350px' : '300px'; ?>;
            letter-spacing: 1.5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .certificate-footer {
            margin-top: auto;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            position: relative;
            flex-shrink: 0;
        }
        
        .signature-block {
            text-align: center;
            flex: 1;
            max-width: 200px;
        }
        
        .signature-line {
            border-top: 2px solid #1a3a5c;
            width: 150px;
            margin: 0 auto 8px;
            padding-top: 5px;
        }
        
        .signature-name {
            font-weight: 600;
            font-size: 14px;
            color: #1a3a5c;
            margin-top: 5px;
            font-family: 'Arial', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .signature-title {
            font-size: 12px;
            color: #1a3a5c;
            margin-top: 3px;
            font-family: 'Arial', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .date-block {
            position: absolute;
            left: 0;
            bottom: 0;
            text-align: left;
        }
        
        .date-label {
            font-size: 12px;
            color: #1a3a5c;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 5px;
            font-family: 'Arial', sans-serif;
        }
        
        .date-line {
            border-top: 2px solid #1a3a5c;
            width: 120px;
            padding-top: 5px;
        }
        
        .certificate-number {
            position: absolute;
            bottom: 0;
            right: 0;
            font-size: 10px;
            color: #888;
            font-family: 'Courier New', monospace;
            z-index: 3;
        }
        
        /* Clean up template HTML */
        .certificate-body * {
            max-width: 100%;
        }
        
        .certificate-body img {
            max-width: 200px;
            height: auto;
        }
        
        /* Remove duplicate text patterns */
        .certificate-body {
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <?php if ($isSuperAdmin || $isAdmin): ?>
    <div class="template-selector no-print">
        <label for="templateSelect">Change Template:</label>
        <select id="templateSelect" onchange="changeTemplate()">
            <?php foreach ($availableTemplates as $t): ?>
                <option value="<?php echo $t['id']; ?>" <?php echo $t['id'] == $templateToUse ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($t['template_name']); ?> (<?php echo ucfirst($t['certificate_type']); ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button onclick="updateCertificateTemplate()" class="btn btn-primary">
            <i class="ri-save-line"></i> Save Template Change
        </button>
    </div>
    <?php endif; ?>
    
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="ri-printer-line"></i> Print Certificate
        </button>
        <a href="<?php echo APP_URL; ?>modules/certificates/download-certificate.php?id=<?php echo $certificateId; ?><?php echo $templateId ? '&template_id=' . $templateId : ''; ?>" class="btn btn-success">
            <i class="ri-download-line"></i> Download PDF
        </a>
        <a href="<?php echo APP_URL; ?>modules/student/my-certificates.php" class="btn btn-secondary">
            <i class="ri-arrow-left-line"></i> Back
        </a>
    </div>
    
    <div class="certificate-wrapper">
        <!-- Official Seal with Ribbons -->
        <div class="official-seal">
            <div class="seal-ribbon left"></div>
            <div class="seal-ribbon right"></div>
            <div class="seal-circle"></div>
        </div>
        
        <!-- Watermark Pattern -->
        <?php if ($template['include_watermark']): ?>
        <div class="watermark"></div>
        <?php endif; ?>
        
        <div class="certificate-container">
            <?php if ($headerHtml): ?>
            <div class="certificate-header">
                <?php 
                // Clean and format header HTML
                $cleanHeader = $headerHtml;
                // Remove duplicate text patterns
                $cleanHeader = preg_replace('/(This is to certify that\s*){2,}/i', 'This is to certify that ', $cleanHeader);
                echo $cleanHeader; 
                ?>
            </div>
            <?php else: ?>
            <div class="certificate-header">
                <h1>CERTIFICATE</h1>
                <h2>OF <?php echo strtoupper(str_replace('_', ' ', $certificate['certificate_type'] ?? 'COMPLETION')); ?></h2>
            </div>
            <?php endif; ?>
            
            <!-- Ribbon Banner -->
            <div class="ribbon-banner">
                THIS CERTIFICATE IS PROUDLY PRESENTED TO
            </div>
            
            <div class="certificate-body">
                <?php 
                // Clean and format body HTML
                $cleanBody = $bodyHtml ?? '';
                
                // Ensure we have a string, not null (PHP 8+ compatibility)
                if ($cleanBody === null || !is_string($cleanBody)) {
                    $cleanBody = '';
                }
                
                // Remove unwanted header text that might be in body (only if not empty)
                if (!empty($cleanBody)) {
                    $cleanBody = preg_replace('/Certificate\s+of\s+Completion/i', '', $cleanBody) ?? $cleanBody;
                    $cleanBody = preg_replace('/CERTIFICATE\s+OF\s+COMPLETION/i', '', $cleanBody) ?? $cleanBody;
                    
                    // Remove duplicate text patterns
                    $cleanBody = preg_replace('/(This is to certify that\s*){2,}/i', 'This is to certify that ', $cleanBody) ?? $cleanBody;
                    
                    // Remove empty placeholder characters
                    $cleanBody = preg_replace('/[\x{25A1}\x{25A0}\x{25AA}\x{25AB}]/u', '', $cleanBody) ?? $cleanBody;
                    
                    // Clean up extra whitespace but preserve HTML structure
                    $cleanBody = preg_replace('/\s+/', ' ', $cleanBody) ?? $cleanBody;
                    $cleanBody = preg_replace('/>\s+</', '><', $cleanBody) ?? $cleanBody;
                }
                
                // Extract student name
                $studentName = $certificate['student_name'] ?? '';
                $studentNameHtml = '<div class="student-name-highlight">' . htmlspecialchars($studentName) . '</div>';
                
                // Replace student name placeholder ONLY (not plain text to avoid double replacement)
                $cleanBody = str_replace('{{STUDENT_NAME}}', $studentNameHtml, $cleanBody);
                
                // Only add default text if body is empty or very short
                $bodyTextOnly = strip_tags($cleanBody);
                $bodyTextOnly = trim($bodyTextOnly);
                
                if (empty($bodyTextOnly) || strlen($bodyTextOnly) < 10) {
                    // Body is empty, add default certificate text
                    $cleanBody = '<p>This is to certify that</p>' . $studentNameHtml . '<p>has successfully completed the requirements and is hereby awarded this certificate.</p>';
                } else {
                    // Body has content, ensure student name is styled if present as plain text
                    // Only replace if it's not already styled
                    if (strpos($cleanBody, 'student-name-highlight') === false && !empty($studentName)) {
                        // Check if plain student name exists and replace with styled version
                        $escapedName = htmlspecialchars($studentName);
                        if (strpos($cleanBody, $escapedName) !== false) {
                            // Replace plain name with styled version, but be careful not to double-wrap
                            $cleanBody = preg_replace('/(' . preg_quote($escapedName, '/') . ')/', $studentNameHtml, $cleanBody, 1) ?? $cleanBody;
                        }
                    }
                }
                
                // Final cleanup - remove any remaining placeholders (only if not empty)
                if (!empty($cleanBody)) {
                    $cleanBody = preg_replace('/\{\{[A-Z_]+\}\}/', '', $cleanBody) ?? $cleanBody;
                    
                    // Remove certificate number and date from body if they appear (they should be in footer/header only)
                    $cleanBody = preg_replace('/Certificate\s+No[.:]\s*[A-Z0-9-]+/i', '', $cleanBody) ?? $cleanBody;
                    $cleanBody = preg_replace('/Date[:\s]+[\d-]+/i', '', $cleanBody) ?? $cleanBody;
                }
                
                // Trim and clean up
                $cleanBody = trim($cleanBody);
                
                echo $cleanBody; 
                ?>
            </div>
            
            <?php if ($footerHtml): ?>
            <div class="certificate-footer">
                <?php 
                // Clean footer HTML
                $cleanFooter = $footerHtml;
                $cleanFooter = preg_replace('/(This is to certify that\s*){2,}/i', '', $cleanFooter);
                echo $cleanFooter; 
                ?>
            </div>
            <?php else: ?>
            <div class="certificate-footer">
                <div class="date-block">
                    <div class="date-label">DATE</div>
                    <div class="date-line"><?php echo formatDate($certificate['issue_date']); ?></div>
                </div>
                
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-name"><?php echo htmlspecialchars($template['signature_1_label'] ?? 'Principal'); ?></div>
                    <div class="signature-title">SIGNATURE</div>
                </div>
                
                <?php if ($template['signature_2_label']): ?>
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-name"><?php echo htmlspecialchars($template['signature_2_label']); ?></div>
                    <div class="signature-title">SIGNATURE</div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="certificate-number">Certificate No: <?php echo htmlspecialchars($certificate['certificate_number']); ?></div>
        </div>
    </div>
    
    <?php if ($isSuperAdmin || $isAdmin): ?>
    <script>
    function changeTemplate() {
        const templateId = document.getElementById('templateSelect').value;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('template_id', templateId);
        window.location.href = currentUrl.toString();
    }
    
    function updateCertificateTemplate() {
        const templateId = document.getElementById('templateSelect').value;
        const certificateId = <?php echo $certificateId; ?>;
        
        if (confirm('Are you sure you want to change the template for this certificate? This will update the certificate permanently.')) {
            fetch('<?php echo APP_URL; ?>ajax/certificates/update-certificate-template.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'certificate_id=' + certificateId + '&template_id=' + templateId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Template updated successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error updating template');
                console.error('Error:', error);
            });
        }
    }
    </script>
    <?php endif; ?>
</body>
</html>

