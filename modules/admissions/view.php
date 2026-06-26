<?php
/**
 * View Admission Application
 * 
 * View detailed information about an admission application
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Receptionist']);

$pageTitle = 'View Application';

// Get application ID
$applicationId = (int)($_GET['id'] ?? 0);

if (empty($applicationId)) {
    $_SESSION['error'] = 'Invalid application ID';
    redirect(APP_URL . 'modules/admissions/list.php');
}

// Get application details
$sql = "SELECT a.*, b.branch_name, c.class_name, s.session_name,
        st.first_name as reviewer_first, st.last_name as reviewer_last,
        u.username as reviewer_username
        FROM admission_applications a
        LEFT JOIN branches b ON a.branch_id = b.id
        LEFT JOIN classes c ON a.class_id = c.id
        LEFT JOIN academic_sessions s ON a.session_id = s.id
        LEFT JOIN users u ON a.reviewed_by = u.id
        LEFT JOIN staff st ON u.id = st.user_id
        WHERE a.id = ?";

$stmt = executeQuery($sql, 'i', [$applicationId]);
$application = fetchOne($stmt);

if (!$application) {
    $_SESSION['error'] = 'Application not found';
    redirect(APP_URL . 'modules/admissions/list.php');
}

// Check branch access
$currentUser = getCurrentUser();
if (!hasRole(['Super Admin']) && $application['branch_id'] != $currentUser['branch_id']) {
    $_SESSION['error'] = 'Access denied';
    redirect(APP_URL . 'modules/admissions/list.php');
}

// Calculate age
$age = '';
if (!empty($application['date_of_birth'])) {
    $birthDate = new DateTime($application['date_of_birth']);
    $today = new DateTime();
    $age = $birthDate->diff($today)->y;
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
                        <div class="page-title-right">
                            <a href="<?php echo APP_URL; ?>modules/admissions/list.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to List
                            </a>
                            <button onclick="window.print()" class="btn btn-info ms-2 no-print">
                                <i class="ri-printer-line"></i> Print
                            </button>
                        </div>
                        <h4 class="page-title">Admission Application Details</h4>
                    </div>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-<?php 
                        echo $application['status'] == 'Accepted' ? 'success' : 
                            ($application['status'] == 'Rejected' ? 'danger' : 
                            ($application['status'] == 'Enrolled' ? 'info' : 'warning')); 
                    ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Application Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo $application['status'] == 'Accepted' ? 'success' : 
                                        ($application['status'] == 'Rejected' ? 'danger' : 
                                        ($application['status'] == 'Enrolled' ? 'info' : 'warning')); 
                                ?> ms-2"><?php echo htmlspecialchars($application['status']); ?></span>
                            </div>
                            <div class="no-print">
                                <?php if ($application['status'] != 'Enrolled' && $application['status'] != 'Rejected'): ?>
                                    <button type="button" class="btn btn-sm btn-success" onclick="updateStatus('Accepted')">
                                        <i class="ri-check-line"></i> Accept
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="updateStatus('Rejected')">
                                        <i class="ri-close-line"></i> Reject
                                    </button>
                                    <?php if ($application['status'] == 'Accepted'): ?>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="enrollStudent()">
                                            <i class="ri-user-add-line"></i> Enroll Student
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Application Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-file-text-line"></i> Application Information
                            </h4>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted">Application Number</label>
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($application['application_no']); ?></strong></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted">Application Date</label>
                                    <p class="mb-0"><?php echo formatDate($application['applied_at']); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted">Branch</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($application['branch_name']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted">Academic Session</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($application['session_name']); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted">Applying for Class</label>
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($application['class_name']); ?></strong></p>
                                </div>
                                <?php if ($application['reviewed_by']): ?>
                                <div class="col-md-6">
                                    <label class="text-muted">Reviewed By</label>
                                    <p class="mb-0">
                                        <?php 
                                        if (!empty($application['reviewer_first']) && !empty($application['reviewer_last'])) {
                                            echo htmlspecialchars($application['reviewer_first'] . ' ' . $application['reviewer_last']);
                                        } else {
                                            echo htmlspecialchars($application['reviewer_username'] ?? 'N/A');
                                        }
                                        ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Student Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-user-line"></i> Student Information
                            </h4>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted">Full Name</label>
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></strong></p>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted">Gender</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($application['gender']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted">Age</label>
                                    <p class="mb-0"><?php echo $age; ?> years</p>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted">Date of Birth</label>
                                    <p class="mb-0"><?php echo formatDate($application['date_of_birth']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted">Previous School</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($application['previous_school'] ?: 'N/A'); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted">Email</label>
                                    <p class="mb-0">
                                        <?php if ($application['email']): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($application['email']); ?>">
                                                <?php echo htmlspecialchars($application['email']); ?>
                                            </a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted">Phone</label>
                                    <p class="mb-0">
                                        <?php if ($application['phone']): ?>
                                            <a href="tel:<?php echo htmlspecialchars($application['phone']); ?>">
                                                <?php echo htmlspecialchars($application['phone']); ?>
                                            </a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($application['address']): ?>
                            <div class="mb-3">
                                <label class="text-muted">Address</label>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($application['address'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Parent/Guardian Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-parent-line"></i> Parent/Guardian Information
                            </h4>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted">Parent/Guardian Name</label>
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($application['parent_name']); ?></strong></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted">Parent Phone</label>
                                    <p class="mb-0">
                                        <a href="tel:<?php echo htmlspecialchars($application['parent_phone']); ?>">
                                            <?php echo htmlspecialchars($application['parent_phone']); ?>
                                        </a>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($application['parent_email']): ?>
                            <div class="mb-3">
                                <label class="text-muted">Parent Email</label>
                                <p class="mb-0">
                                    <a href="mailto:<?php echo htmlspecialchars($application['parent_email']); ?>">
                                        <?php echo htmlspecialchars($application['parent_email']); ?>
                                    </a>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Interview & Notes -->
                    <?php if ($application['interview_date'] || $application['interview_notes'] || $application['rejection_reason']): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="ri-calendar-check-line"></i> Review Details
                            </h4>
                            
                            <?php if ($application['interview_date']): ?>
                            <div class="mb-3">
                                <label class="text-muted">Interview Date</label>
                                <p class="mb-0"><?php echo formatDateTime($application['interview_date']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($application['interview_notes']): ?>
                            <div class="mb-3">
                                <label class="text-muted">Interview Notes</label>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($application['interview_notes'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($application['rejection_reason']): ?>
                            <div class="mb-3">
                                <label class="text-muted">Rejection Reason</label>
                                <p class="mb-0 text-danger"><?php echo nl2br(htmlspecialchars($application['rejection_reason'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card no-print">
                        <div class="card-body">
                            <h5 class="card-title">Quick Actions</h5>
                            
                            <div class="d-grid gap-2">
                                <?php if ($application['status'] == 'Accepted'): ?>
                                    <button type="button" class="btn btn-primary" onclick="enrollStudent()">
                                        <i class="ri-user-add-line"></i> Enroll as Student
                                    </button>
                                <?php endif; ?>
                                
                                <button type="button" class="btn btn-info" onclick="scheduleInterview()">
                                    <i class="ri-calendar-line"></i> Schedule Interview
                                </button>
                                
                                <button type="button" class="btn btn-warning" onclick="sendNotification()">
                                    <i class="ri-notification-line"></i> Send Notification
                                </button>
                                
                                <a href="mailto:<?php echo htmlspecialchars($application['parent_email'] ?: $application['email']); ?>" 
                                   class="btn btn-secondary">
                                    <i class="ri-mail-line"></i> Send Email
                                </a>
                                
                                <a href="tel:<?php echo htmlspecialchars($application['parent_phone']); ?>" 
                                   class="btn btn-secondary">
                                    <i class="ri-phone-line"></i> Call Parent
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Application Timeline -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Timeline</h5>
                            
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6>Application Submitted</h6>
                                        <p class="text-muted mb-0"><?php echo formatDateTime($application['applied_at']); ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($application['updated_at'] != $application['applied_at']): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6>Last Updated</h6>
                                        <p class="text-muted mb-0"><?php echo formatDateTime($application['updated_at']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($application['status'] == 'Enrolled'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6>Student Enrolled</h6>
                                        <p class="text-muted mb-0">Student account created</p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Application Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateStatusForm">
                <input type="hidden" name="application_id" value="<?php echo $applicationId; ?>">
                <input type="hidden" name="status" id="newStatus">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <input type="text" class="form-control" id="statusDisplay" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes/Reason</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Enter notes or reason for this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Interview Modal -->
<div class="modal fade" id="interviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="interviewForm">
                <input type="hidden" name="application_id" value="<?php echo $applicationId; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Interview Date & Time</label>
                        <input type="datetime-local" class="form-control" name="interview_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Interview notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Interview</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 14px;
}

@media print {
    .no-print {
        display: none !important;
    }
}
</style>

<script>
// Update status
function updateStatus(status) {
    $('#newStatus').val(status);
    $('#statusDisplay').val(status);
    $('#updateStatusModal').modal('show');
}

$('#updateStatusForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/admissions/update-status.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#updateStatusModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Enroll student
function enrollStudent() {
    confirmAction('Enroll this applicant as a student? This will create a student account.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/admissions/enroll.php',
            type: 'POST',
            data: { application_id: <?php echo $applicationId; ?> },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(response.message, 'error');
                }
            }
        });
    });
}

// Schedule interview
function scheduleInterview() {
    $('#interviewModal').modal('show');
}

$('#interviewForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/admissions/schedule-interview.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#interviewModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Send notification
function sendNotification() {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/admissions/send-notification.php',
        type: 'POST',
        data: { application_id: <?php echo $applicationId; ?> },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
            } else {
                showToast(response.message, 'error');
            }
        }
    });
}
</script>

