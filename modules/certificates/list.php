<?php
/**
 * Certificates List & Management
 * 
 * View and manage all issued certificates
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Certificates List';

$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$branchId = $isSuperAdmin ? ($_GET['branch_id'] ?? null) : $currentUser['branch_id'];

// Get filter parameters
$filterType = $_GET['certificate_type'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterClass = $_GET['class_id'] ?? '';
$filterSession = $_GET['session_id'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT c.*, ct.template_name, ct.certificate_type,
        s.student_id, s.first_name, s.last_name,
        CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) as student_name,
        acs.session_name, cl.class_name, b.branch_name,
        u.username as issued_by_name
        FROM certificates c
        LEFT JOIN certificate_templates ct ON c.template_id = ct.id
        LEFT JOIN students s ON c.student_id = s.id
        LEFT JOIN academic_sessions acs ON c.session_id = acs.id
        LEFT JOIN classes cl ON c.class_id = cl.id
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN users u ON c.issued_by = u.id
        WHERE 1=1";

if ($branchId) {
    $sql .= " AND s.branch_id = " . intval($branchId);
}

if ($filterType) {
    $sql .= " AND c.certificate_type = '" . sanitize($filterType) . "'";
}

if ($filterStatus) {
    $sql .= " AND c.status = '" . sanitize($filterStatus) . "'";
}

if ($filterClass) {
    $sql .= " AND c.class_id = " . intval($filterClass);
}

if ($filterSession) {
    $sql .= " AND c.session_id = " . intval($filterSession);
}

if ($search) {
    $searchTerm = sanitize($search);
    $sql .= " AND (c.certificate_number LIKE '%$searchTerm%' 
             OR s.student_id LIKE '%$searchTerm%' 
             OR s.first_name LIKE '%$searchTerm%' 
             OR s.last_name LIKE '%$searchTerm%')";
}

$sql .= " ORDER BY c.issue_date DESC, c.created_at DESC";

$certificates = fetchAll(executeQuery($sql));

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Get classes (excluding graduated classes)
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')";
if ($branchId) {
    $classesSql .= " AND branch_id = " . intval($branchId);
}
$classesSql .= " ORDER BY class_order, class_name";
$classes = fetchAll(executeQuery($classesSql));

// Get sessions
$sessionsSql = "SELECT * FROM academic_sessions ORDER BY start_date DESC";
$sessions = fetchAll(executeQuery($sessionsSql));

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<style>
.certificate-type-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.certificate-type-badge.completion { background: #e7f3ff; color: #0066cc; }
.certificate-type-badge.graduation { background: #fff4e6; color: #cc6600; }
.certificate-type-badge.promotion { background: #e6f7ff; color: #0066aa; }
.certificate-type-badge.character { background: #f0f9ff; color: #0055aa; }
.certificate-type-badge.achievement { background: #fff0f5; color: #cc0066; }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <a href="generate.php" class="btn btn-primary">
                                <i class="ri-add-line"></i> Generate Certificates
                            </a>
                        </div>
                        <h4 class="page-title">
                            <i class="ri-file-certificate-line"></i> Certificates List
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <?php if ($isSuperAdmin): ?>
                                <div class="col-md-2">
                                    <label class="form-label">Branch</label>
                                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" <?php echo ($branchId == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Certificate Type</label>
                                    <select name="certificate_type" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Types</option>
                                        <option value="completion" <?php echo $filterType == 'completion' ? 'selected' : ''; ?>>Completion</option>
                                        <option value="graduation" <?php echo $filterType == 'graduation' ? 'selected' : ''; ?>>Graduation</option>
                                        <option value="promotion" <?php echo $filterType == 'promotion' ? 'selected' : ''; ?>>Promotion</option>
                                        <option value="character" <?php echo $filterType == 'character' ? 'selected' : ''; ?>>Character</option>
                                        <option value="achievement" <?php echo $filterType == 'achievement' ? 'selected' : ''; ?>>Achievement</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Status</option>
                                        <option value="issued" <?php echo $filterStatus == 'issued' ? 'selected' : ''; ?>>Issued</option>
                                        <option value="reissued" <?php echo $filterStatus == 'reissued' ? 'selected' : ''; ?>>Reissued</option>
                                        <option value="revoked" <?php echo $filterStatus == 'revoked' ? 'selected' : ''; ?>>Revoked</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Class</label>
                                    <select name="class_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo $filterClass == $class['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Session</label>
                                    <select name="session_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Sessions</option>
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo $session['id']; ?>" <?php echo $filterSession == $session['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control" placeholder="Certificate No, Student ID, Name" 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i> Filter
                                    </button>
                                    <a href="list.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-certificate widget-icon text-primary"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Certificates</h5>
                            <h3 class="mt-3 mb-3"><?php echo count($certificates); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-checkbox-circle-line widget-icon text-success"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Issued</h5>
                            <h3 class="mt-3 mb-3"><?php echo count(array_filter($certificates, fn($c) => $c['status'] === 'issued')); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-refresh-line widget-icon text-warning"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Reissued</h5>
                            <h3 class="mt-3 mb-3"><?php echo count(array_filter($certificates, fn($c) => $c['status'] === 'reissued')); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-close-circle-line widget-icon text-danger"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Revoked</h5>
                            <h3 class="mt-3 mb-3"><?php echo count(array_filter($certificates, fn($c) => $c['status'] === 'revoked')); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificates Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Certificates (<?php echo count($certificates); ?>)</h4>
                            
                            <?php if (empty($certificates)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No certificates found matching your criteria.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover datatable">
                                        <thead>
                                            <tr>
                                                <th>Certificate No.</th>
                                                <th>Student</th>
                                                <th>Type</th>
                                                <th>Class</th>
                                                <th>Session</th>
                                                <th>Issue Date</th>
                                                <th>GPA</th>
                                                <th>Status</th>
                                                <th>Issued By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($certificates as $cert): ?>
                                            <tr>
                                                <td>
                                                    <code><?php echo htmlspecialchars($cert['certificate_number']); ?></code>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($cert['verification_code']); ?></small>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($cert['student_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($cert['student_id']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="certificate-type-badge <?php echo htmlspecialchars($cert['certificate_type']); ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $cert['certificate_type'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($cert['class_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($cert['session_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo formatDate($cert['issue_date']); ?></td>
                                                <td>
                                                    <?php if ($cert['gpa']): ?>
                                                        <strong><?php echo number_format($cert['gpa'], 2); ?></strong>
                                                        <?php if ($cert['cgpa']): ?>
                                                            <br><small class="text-muted">CGPA: <?php echo number_format($cert['cgpa'], 2); ?></small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
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
                                                    <small><?php echo htmlspecialchars($cert['issued_by_name'] ?? 'N/A'); ?></small>
                                                    <br>
                                                    <small class="text-muted"><?php echo formatDate($cert['created_at']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="view-certificate.php?id=<?php echo $cert['id']; ?>" 
                                                           target="_blank" class="btn btn-sm btn-primary" title="View">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                        <a href="download-certificate.php?id=<?php echo $cert['id']; ?>" 
                                                           class="btn btn-sm btn-success" title="Download">
                                                            <i class="ri-download-line"></i>
                                                        </a>
                                                        <a href="<?php echo APP_URL; ?>verify-certificate.php?code=<?php echo urlencode($cert['verification_code']); ?>" 
                                                           target="_blank" class="btn btn-sm btn-info" title="Verify">
                                                            <i class="ri-shield-check-line"></i>
                                                        </a>
                                                        <?php if ($cert['status'] !== 'revoked'): ?>
                                                        <button onclick="revokeCertificate(<?php echo $cert['id']; ?>)" 
                                                                class="btn btn-sm btn-danger" title="Revoke">
                                                            <i class="ri-close-circle-line"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
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

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Revoke certificate
function revokeCertificate(certificateId) {
    Swal.fire({
        title: 'Revoke Certificate?',
        text: 'This will mark the certificate as revoked. This action can be reversed.',
        icon: 'warning',
        input: 'text',
        inputLabel: 'Reason for revocation',
        inputPlaceholder: 'Enter reason (optional)',
        showCancelButton: true,
        confirmButtonText: 'Yes, revoke it',
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/certificates/revoke-certificate.php',
                type: 'POST',
                data: {
                    id: certificateId,
                    reason: result.value || ''
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Revoked!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to revoke certificate', 'error');
                }
            });
        }
    });
}
</script>


