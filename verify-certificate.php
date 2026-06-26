<?php
/**
 * Certificate Verification Page
 * 
 * Public endpoint to verify certificates using certificate number or QR code
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once 'config/config.php';

$pageTitle = 'Verify Certificate';

$certificate = null;
$student = null;
$errorMessage = '';

// Check verification method
$verificationCode = $_GET['code'] ?? '';
$certificateNumber = $_GET['cert_number'] ?? '';

if ($verificationCode || $certificateNumber) {
    // Perform verification
    if ($verificationCode) {
        $sql = "SELECT c.*, s.student_id, s.first_name, s.last_name, 
                cl.class_name, acs.session_name, ct.template_name, ct.certificate_type,
                b.branch_name
                FROM certificates c
                INNER JOIN students s ON c.student_id = s.id
                LEFT JOIN classes cl ON c.class_id = cl.id
                LEFT JOIN academic_sessions acs ON c.session_id = acs.id
                LEFT JOIN certificate_templates ct ON c.template_id = ct.id
                LEFT JOIN branches b ON s.branch_id = b.id
                WHERE c.verification_code = ?";
        $stmt = executeQuery($sql, 's', [$verificationCode]);
    } else {
        $sql = "SELECT c.*, s.student_id, s.first_name, s.last_name, 
                cl.class_name, acs.session_name, ct.template_name, ct.certificate_type,
                b.branch_name
                FROM certificates c
                INNER JOIN students s ON c.student_id = s.id
                LEFT JOIN classes cl ON c.class_id = cl.id
                LEFT JOIN academic_sessions acs ON c.session_id = acs.id
                LEFT JOIN certificate_templates ct ON c.template_id = ct.id
                LEFT JOIN branches b ON s.branch_id = b.id
                WHERE c.certificate_number = ?";
        $stmt = executeQuery($sql, 's', [$certificateNumber]);
    }
    
    $certificate = fetchOne($stmt);
    
    if (!$certificate) {
        $errorMessage = 'Certificate not found or invalid verification code.';
    } elseif ($certificate['status'] === 'revoked') {
        $errorMessage = 'This certificate has been revoked and is no longer valid.';
    }
}

include 'includes/header.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Certificate Verification</h4>
                    </div>
                </div>
            </div>

            <!-- Verification Form -->
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Verify Certificate Authenticity</h4>
                            
                            <p class="text-muted">
                                Enter the certificate number to verify its authenticity and view details.
                            </p>
                            
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Certificate Number</label>
                                        <input type="text" class="form-control" name="cert_number" 
                                               placeholder="e.g., GRAD-2024-0001" 
                                               value="<?php echo htmlspecialchars($certificateNumber); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="ri-search-line"></i> Verify
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <?php if ($errorMessage): ?>
                            <div class="alert alert-danger mt-4">
                                <i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($errorMessage); ?>
                            </div>
                            <?php endif; ?>

                            <?php if ($certificate && !$errorMessage): ?>
                            <!-- Verification Success -->
                            <div class="alert alert-success mt-4">
                                <h5><i class="ri-checkbox-circle-line"></i> Certificate Verified Successfully!</h5>
                                <p class="mb-0">This certificate is authentic and has been issued by <?php echo APP_NAME; ?>.</p>
                            </div>

                            <!-- Certificate Details -->
                            <div class="card bg-light mt-4">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Certificate Details</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="45%">Certificate Number:</th>
                                                    <td><code><?php echo htmlspecialchars($certificate['certificate_number']); ?></code></td>
                                                </tr>
                                                <tr>
                                                    <th>Certificate Type:</th>
                                                    <td><?php echo ucfirst(str_replace('_', ' ', $certificate['certificate_type'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Student Name:</th>
                                                    <td><strong><?php echo htmlspecialchars($certificate['first_name'] . ' ' . $certificate['last_name']); ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <th>Student ID:</th>
                                                    <td><?php echo htmlspecialchars($certificate['student_id']); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="45%">Class:</th>
                                                    <td><?php echo htmlspecialchars($certificate['class_name']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Academic Session:</th>
                                                    <td><?php echo htmlspecialchars($certificate['session_name']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Branch:</th>
                                                    <td><?php echo htmlspecialchars($certificate['branch_name']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Issue Date:</th>
                                                    <td><?php echo formatDate($certificate['issue_date']); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <?php if ($certificate['gpa']): ?>
                                    <div class="mt-3">
                                        <h6>Academic Performance:</h6>
                                        <p class="mb-1"><strong>GPA:</strong> <?php echo number_format($certificate['gpa'], 2); ?></p>
                                        <?php if ($certificate['cgpa']): ?>
                                        <p class="mb-1"><strong>CGPA:</strong> <?php echo number_format($certificate['cgpa'], 2); ?></p>
                                        <?php endif; ?>
                                        <?php if ($certificate['class_rank']): ?>
                                        <p class="mb-1"><strong>Class Rank:</strong> #<?php echo $certificate['class_rank']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3">
                                        <p class="text-muted mb-0">
                                            <small>
                                                <i class="ri-shield-check-line"></i> 
                                                This certificate was verified on <?php echo date('F j, Y \a\t g:i A'); ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <?php if ($certificate['valid_until']): ?>
                            <div class="alert alert-warning mt-3">
                                <i class="ri-time-line"></i> 
                                <strong>Note:</strong> This certificate is valid until <?php echo formatDate($certificate['valid_until']); ?>
                            </div>
                            <?php endif; ?>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include 'includes/footer.php'; ?>

