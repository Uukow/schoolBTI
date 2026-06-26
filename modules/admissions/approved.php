<?php
/**
 * Approved Admissions Page
 * 
 * View approved admission applications ready for enrollment
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Receptionist']);

$pageTitle = 'Approved Admissions';

// Get current user
$currentUser = getCurrentUser();

// Build query for approved applications
$sql = "SELECT a.*, b.branch_name, c.class_name 
        FROM admission_applications a 
        LEFT JOIN branches b ON a.branch_id = b.id 
        LEFT JOIN classes c ON a.class_id = c.id 
        WHERE a.status = 'Accepted'";

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND a.branch_id = {$currentUser['branch_id']}";
}

$sql .= " ORDER BY a.applied_at DESC";

$applications = fetchAll(executeQuery($sql));

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Enrolled' THEN 1 ELSE 0 END) as enrolled
    FROM admission_applications";

if (!hasRole(['Super Admin'])) {
    $statsSql .= " WHERE branch_id = {$currentUser['branch_id']}";
}

$stats = fetchOne(executeQuery($statsSql));

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
                        <div class="page-title-right">
                            <a href="<?php echo APP_URL; ?>modules/admissions/list.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> All Applications
                            </a>
                            <a href="<?php echo APP_URL; ?>modules/admissions/pending.php" class="btn btn-warning ms-2">
                                <i class="ri-time-line"></i> Pending Review
                            </a>
                        </div>
                        <h4 class="page-title">
                            Approved Admission Applications
                            <span class="badge bg-success ms-2"><?php echo count($applications); ?></span>
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-checkbox-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Approved</h5>
                                    <h2 class="mb-0"><?php echo $stats['approved'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-info-lighten text-info">
                                        <i class="ri-user-add-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Ready to Enroll</h5>
                                    <h2 class="mb-0"><?php echo count($applications); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-user-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Enrolled</h5>
                                    <h2 class="mb-0"><?php echo $stats['enrolled'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applications List -->
            <div class="row">
                <?php if (!empty($applications)): ?>
                    <?php foreach ($applications as $app): ?>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title mb-1">
                                            <?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?>
                                        </h5>
                                        <p class="text-muted mb-0">
                                            <small>Application No: <strong><?php echo htmlspecialchars($app['application_no']); ?></strong></small>
                                        </p>
                                    </div>
                                    <span class="badge bg-success">
                                        <i class="ri-check-line"></i> Approved
                                    </span>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Gender:</small>
                                        <p class="mb-0"><strong><?php echo htmlspecialchars($app['gender']); ?></strong></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">DOB:</small>
                                        <p class="mb-0"><strong><?php echo formatDate($app['date_of_birth']); ?></strong></p>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Class:</small>
                                        <p class="mb-0"><strong><?php echo htmlspecialchars($app['class_name']); ?></strong></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Branch:</small>
                                        <p class="mb-0"><strong><?php echo htmlspecialchars($app['branch_name']); ?></strong></p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Parent:</small>
                                    <p class="mb-0">
                                        <i class="ri-user-line me-1"></i><?php echo htmlspecialchars($app['parent_name']); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="ri-phone-line me-1"></i><?php echo htmlspecialchars($app['parent_phone']); ?>
                                    </p>
                                </div>
                                
                                <?php if ($app['interview_date']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Interview Date:</small>
                                    <p class="mb-0">
                                        <i class="ri-calendar-line me-1"></i><?php echo formatDateTime($app['interview_date'], 'd M Y H:i'); ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Approved:</small>
                                    <p class="mb-0"><?php echo formatDate($app['updated_at'], 'd M Y H:i'); ?></p>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="<?php echo APP_URL; ?>modules/admissions/view.php?id=<?php echo $app['id']; ?>" 
                                       class="btn btn-info btn-sm flex-fill">
                                        <i class="ri-eye-line"></i> View Details
                                    </a>
                                    <button onclick="enrollStudent(<?php echo $app['id']; ?>)" 
                                            class="btn btn-success btn-sm flex-fill">
                                        <i class="ri-user-add-line"></i> Enroll Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="ri-information-line font-24"></i>
                        <h5 class="mt-2">No Approved Applications</h5>
                        <p class="mb-0">There are no approved applications ready for enrollment at the moment.</p>
                        <a href="<?php echo APP_URL; ?>modules/admissions/pending.php" class="btn btn-warning mt-3">
                            <i class="ri-time-line"></i> Review Pending Applications
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Enroll student
function enrollStudent(appId) {
    Swal.fire({
        title: 'Enroll Student?',
        html: `
            <p>Enroll this applicant as a student?</p>
            <p class="text-muted small">This will:</p>
            <ul class="text-start small">
                <li>Create a student account</li>
                <li>Generate student ID</li>
                <li>Create parent record</li>
                <li>Link student to parent</li>
                <li>Update application status to "Enrolled"</li>
            </ul>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="ri-user-add-line"></i> Yes, Enroll!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Enrolling...',
                text: 'Please wait while we create the student record.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/admissions/enroll.php',
                type: 'POST',
                data: { application_id: appId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Student Enrolled!',
                            html: `
                                <p>${response.message}</p>
                                <p class="text-muted small mt-2">The student can now access the student portal.</p>
                            `,
                            confirmButtonText: 'View Student List',
                            showCancelButton: true,
                            cancelButtonText: 'Stay Here'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?php echo APP_URL; ?>modules/students/list.php';
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Enrollment Failed!',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while enrolling the student. Please try again.'
                    });
                }
            });
        }
    });
}
</script>
