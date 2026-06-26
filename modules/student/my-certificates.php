<?php
/**
 * My Certificates - Student Portal
 * 
 * View and download own certificates and transcripts
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Student', 'Super Admin']);

$pageTitle = 'My Certificates';

// Get current user and student record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

$student = null;
$studentId = null;

if ($isSuperAdmin) {
    $studentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;
} else {
    $student = getStudentByUserId($currentUser['id']);
    if ($student) {
        $studentId = $student['id'];
    }
}

// Get student certificates
$certificates = [];
if ($studentId) {
    $certSql = "SELECT c.*, ct.template_name, ct.certificate_type,
                acs.session_name, cl.class_name, u.username as issued_by_name
                FROM certificates c
                LEFT JOIN certificate_templates ct ON c.template_id = ct.id
                LEFT JOIN academic_sessions acs ON c.session_id = acs.id
                LEFT JOIN classes cl ON c.class_id = cl.id
                LEFT JOIN users u ON c.issued_by = u.id
                WHERE c.student_id = ?
                ORDER BY c.issue_date DESC, c.created_at DESC";
    $certStmt = executeQuery($certSql, 'i', [$studentId]);
    $certificates = fetchAll($certStmt);
}

// Get student transcripts
$transcripts = [];
if ($studentId) {
    $transSql = "SELECT t.*, u.username as generated_by_name
                FROM transcripts t
                LEFT JOIN users u ON t.generated_by = u.id
                WHERE t.student_id = ?
                ORDER BY t.generated_at DESC";
    $transStmt = executeQuery($transSql, 'i', [$studentId]);
    $transcripts = fetchAll($transStmt);
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">My Certificates & Transcripts</h4>
                    </div>
                </div>
            </div>

            <?php if (!$studentId): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="ri-alert-line"></i> 
                        Student profile not found. Please contact administrator to link your account.
                    </div>
                </div>
            </div>
            <?php else: ?>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-6 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-award-line widget-icon text-primary"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Certificates</h5>
                            <h3 class="mt-3 mb-3"><?php echo count($certificates); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2">
                                    <?php echo count(array_filter($certificates, fn($c) => $c['status'] === 'issued')); ?> Active
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-6 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-file-list-line widget-icon text-success"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Academic Transcripts</h5>
                            <h3 class="mt-3 mb-3"><?php echo count($transcripts); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2">
                                    <?php echo count(array_filter($transcripts, fn($t) => $t['status'] === 'issued')); ?> Available
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificates Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">My Certificates</h4>
                            
                            <?php if (empty($certificates)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> 
                                    You don't have any certificates yet. Certificates will appear here once issued by your school.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover datatable">
                                        <thead>
                                            <tr>
                                                <th>Certificate ID</th>
                                                <th>Type</th>
                                                <th>Session</th>
                                                <th>Class</th>
                                                <th>Issue Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($certificates as $cert): ?>
                                            <tr>
                                                <td><code><?php echo htmlspecialchars($cert['certificate_number']); ?></code></td>
                                                <td><?php echo ucfirst(str_replace('_', ' ', $cert['certificate_type'])); ?></td>
                                                <td><?php echo htmlspecialchars($cert['session_name']); ?></td>
                                                <td><?php echo htmlspecialchars($cert['class_name']); ?></td>
                                                <td><?php echo formatDate($cert['issue_date']); ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'success';
                                                    if ($cert['status'] === 'revoked') $statusClass = 'danger';
                                                    elseif ($cert['status'] === 'reissued') $statusClass = 'warning';
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                                        <?php echo ucfirst($cert['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($cert['status'] === 'issued' || $cert['status'] === 'reissued'): ?>
                                                    <a href="<?php echo APP_URL; ?>modules/certificates/view-certificate.php?id=<?php echo $cert['id']; ?>" 
                                                       target="_blank" class="btn btn-sm btn-primary" title="View Certificate">
                                                        <i class="ri-eye-line"></i> View
                                                    </a>
                                                    <a href="<?php echo APP_URL; ?>modules/certificates/download-certificate.php?id=<?php echo $cert['id']; ?>" 
                                                       class="btn btn-sm btn-success" title="Download PDF">
                                                        <i class="ri-download-line"></i> Download
                                                    </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transcripts Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Academic Transcripts</h4>
                            
                            <?php if (empty($transcripts)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> 
                                    You don't have any transcripts yet. Your academic transcript will be generated upon request.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover datatable">
                                        <thead>
                                            <tr>
                                                <th>Transcript ID</th>
                                                <th>CGPA</th>
                                                <th>Total Credits</th>
                                                <th>Generated Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($transcripts as $trans): ?>
                                            <tr>
                                                <td><code><?php echo htmlspecialchars($trans['transcript_number']); ?></code></td>
                                                <td><strong><?php echo number_format($trans['cgpa'], 2); ?></strong></td>
                                                <td><?php echo $trans['total_credits'] ?? 'N/A'; ?></td>
                                                <td><?php echo formatDate($trans['generated_at']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $trans['status'] === 'issued' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($trans['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($trans['status'] === 'issued'): ?>
                                                    <a href="<?php echo APP_URL; ?>modules/certificates/view-transcript.php?id=<?php echo $trans['id']; ?>" 
                                                       target="_blank" class="btn btn-sm btn-primary" title="View Transcript">
                                                        <i class="ri-eye-line"></i> View
                                                    </a>
                                                    <a href="<?php echo APP_URL; ?>modules/certificates/download-transcript.php?id=<?php echo $trans['id']; ?>" 
                                                       class="btn btn-sm btn-success" title="Download PDF">
                                                        <i class="ri-download-line"></i> Download
                                                    </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php endif; ?>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

