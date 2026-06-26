<?php
/**
 * Pending Admissions Page
 * 
 * View and review pending admission applications
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Receptionist']);

$pageTitle = 'Pending Admissions';

// Get current user
$currentUser = getCurrentUser();

// Build query for pending applications
$sql = "SELECT a.*, b.branch_name, c.class_name 
        FROM admission_applications a 
        LEFT JOIN branches b ON a.branch_id = b.id 
        LEFT JOIN classes c ON a.class_id = c.id 
        WHERE a.status IN ('Pending', 'Under Review')";

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND a.branch_id = {$currentUser['branch_id']}";
}

$sql .= " ORDER BY a.applied_at DESC";

$applications = fetchAll(executeQuery($sql));

// Get classes for quick actions (excluding graduated classes)
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

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
                        </div>
                        <h4 class="page-title">
                            Pending Admission Applications
                            <span class="badge bg-warning ms-2"><?php echo count($applications); ?></span>
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Applications List -->
            <div class="row">
                <?php if (!empty($applications)): ?>
                    <?php foreach ($applications as $app): ?>
                    <div class="col-md-6">
                        <div class="card">
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
                                    <span class="badge bg-<?php echo ($app['status'] == 'Pending') ? 'warning' : 'info'; ?>">
                                        <?php echo htmlspecialchars($app['status']); ?>
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
                                
                                <div class="mb-3">
                                    <small class="text-muted">Applied:</small>
                                    <p class="mb-0"><?php echo formatDate($app['applied_at'], 'd M Y H:i'); ?></p>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button onclick="acceptApplication(<?php echo $app['id']; ?>)" 
                                            class="btn btn-success btn-sm flex-fill">
                                        <i class="ri-check-line"></i> Accept
                                    </button>
                                    <button onclick="scheduleInterview(<?php echo $app['id']; ?>)" 
                                            class="btn btn-info btn-sm flex-fill">
                                        <i class="ri-calendar-line"></i> Interview
                                    </button>
                                    <button onclick="rejectApplication(<?php echo $app['id']; ?>)" 
                                            class="btn btn-danger btn-sm flex-fill">
                                        <i class="ri-close-line"></i> Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-success text-center">
                        <i class="ri-checkbox-circle-line font-24"></i>
                        <h5 class="mt-2">All Clear!</h5>
                        <p class="mb-0">No pending applications at the moment.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Accept application
function acceptApplication(appId) {
    Swal.fire({
        title: 'Accept Application?',
        text: 'Accept this application? The applicant will be notified.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Accept!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/admissions/update-status.php',
                type: 'POST',
                data: { application_id: appId, status: 'Accepted' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Application Accepted!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred. Please try again.'
                    });
                }
            });
        }
    });
}

// Reject application
function rejectApplication(appId) {
    Swal.fire({
        title: 'Reject Application',
        text: 'Please provide a reason for rejection (optional):',
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Enter rejection reason...',
        inputAttributes: {
            'aria-label': 'Rejection reason'
        },
        showCancelButton: true,
        confirmButtonText: 'Reject Application',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            // Reason is optional, so no validation needed
            return null;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/admissions/update-status.php',
                type: 'POST',
                data: { 
                    application_id: appId, 
                    status: 'Rejected', 
                    reason: result.value || '' 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Application Rejected',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred. Please try again.'
                    });
                }
            });
        }
    });
}

// Schedule interview
function scheduleInterview(appId) {
    Swal.fire({
        title: 'Schedule Interview',
        text: 'Select interview date and time:',
        icon: 'calendar',
        html: `
            <div class="mb-3">
                <label class="form-label">Interview Date</label>
                <input type="date" id="interview-date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Interview Time</label>
                <input type="time" id="interview-time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Notes (Optional)</label>
                <textarea id="interview-notes" class="form-control" rows="3" placeholder="Interview notes..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Schedule Interview',
        confirmButtonColor: '#17a2b8',
        cancelButtonText: 'Cancel',
        didOpen: () => {
            // Set minimum date to today
            const dateInput = document.getElementById('interview-date');
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
            
            // Set default time to 10:00 AM
            document.getElementById('interview-time').value = '10:00';
        },
        preConfirm: () => {
            const date = document.getElementById('interview-date').value;
            const time = document.getElementById('interview-time').value;
            const notes = document.getElementById('interview-notes').value;
            
            if (!date || !time) {
                Swal.showValidationMessage('Please select both date and time');
                return false;
            }
            
            // Combine date and time
            const datetime = date + ' ' + time + ':00';
            
            return {
                datetime: datetime,
                notes: notes
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/admissions/schedule-interview.php',
                type: 'POST',
                data: { 
                    application_id: appId, 
                    interview_date: result.value.datetime,
                    notes: result.value.notes
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Interview Scheduled!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred. Please try again.'
                    });
                }
            });
        }
    });
}
</script>

