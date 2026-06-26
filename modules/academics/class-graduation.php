<?php
/**
 * Class Graduation & Academic Closure Management
 * 
 * Bulk graduation management system for classes with comprehensive
 * state management and immutable data protection.
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Class Graduation & Academic Closure';

// Get current user
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

// Get branch filter
$branchId = null;
$branchFilter = '';

if ($isSuperAdmin) {
    $branchId = $_GET['branch_id'] ?? null;
    if ($branchId) {
        $branchFilter = " AND c.branch_id = $branchId";
    }
} else {
    $branchId = $currentUser['branch_id'] ?? null;
    if ($branchId) {
        $branchFilter = " AND c.branch_id = $branchId";
    }
}

// Get all classes with graduation status and student counts
$sql = "SELECT c.*, b.branch_name,
        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status IN ('Active', 'Graduated')) as total_students,
        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'Graduated') as graduated_students,
        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'Active') as active_students,
        (SELECT username FROM users WHERE id = c.graduated_by) as graduated_by_username
        FROM classes c
        LEFT JOIN branches b ON c.branch_id = b.id
        WHERE 1=1 $branchFilter
        ORDER BY c.graduation_status DESC, c.class_order, c.class_name";

$classes = fetchAll(executeQuery($sql));

// Get branches for filter (Super Admin only)
$branches = [];
if ($isSuperAdmin) {
    $branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
    $branches = fetchAll(executeQuery($branchesSql));
}

// Get graduation logs
$logsSql = "SELECT cgl.*, c.class_name, c.class_code, b.branch_name, u.username
            FROM class_graduation_logs cgl
            INNER JOIN classes c ON cgl.class_id = c.id
            LEFT JOIN branches b ON c.branch_id = b.id
            LEFT JOIN users u ON cgl.performed_by = u.id
            ORDER BY cgl.created_at DESC
            LIMIT 50";
$graduationLogs = fetchAll(executeQuery($logsSql));

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
                            <button type="button" class="btn btn-primary" onclick="showGraduationModal()">
                                <i class="ri-graduation-cap-line"></i> Graduate Selected Classes
                            </button>
                        </div>
                        <h4 class="page-title">Class Graduation & Academic Closure</h4>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Branch Filter (Super Admin only) -->
            <?php if ($isSuperAdmin && !empty($branches)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Branch</label>
                                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" <?php echo ($branchId == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Warning Alert -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading"><i class="ri-alert-line"></i> <strong>Important:</strong> Class Graduation is Permanent</h5>
                        <p class="mb-2">
                            Once a class is marked as <strong>Graduated</strong>, all academic and financial operations will be permanently disabled:
                        </p>
                        <ul class="mb-2">
                            <li><strong>Academic Operations:</strong> Lessons, timetables, attendance, assignments, exams, and assessments</li>
                            <li><strong>Financial Operations:</strong> Monthly fees, recurring charges, new invoices, and penalties</li>
                            <li><strong>Read-Only Access:</strong> All existing records remain accessible for audit and reporting</li>
                            <li><strong>Student Status:</strong> All students in the class will be updated to "Graduated" status</li>
                        </ul>
                        <p class="mb-0"><strong>This action cannot be easily reversed. Please verify all data before proceeding.</strong></p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0"><?php echo count($classes); ?></h4>
                                    <p class="text-muted mb-0">Total Classes</p>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-primary-lighten rounded-circle">
                                        <i class="ri-book-open-line text-primary font-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0"><?php echo count(array_filter($classes, function($c) { return $c['graduation_status'] === 'Graduated'; })); ?></h4>
                                    <p class="text-muted mb-0">Graduated Classes</p>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-success-lighten rounded-circle">
                                        <i class="ri-graduation-cap-line text-success font-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0"><?php echo count(array_filter($classes, function($c) { return $c['graduation_status'] === 'Active' && $c['active_students'] > 0; })); ?></h4>
                                    <p class="text-muted mb-0">Active Classes</p>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-warning-lighten rounded-circle">
                                        <i class="ri-group-line text-warning font-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0"><?php echo array_sum(array_column($classes, 'active_students')); ?></h4>
                                    <p class="text-muted mb-0">Active Students</p>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-danger-lighten rounded-circle">
                                        <i class="ri-user-star-line text-danger font-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Classes Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Classes (<?php echo count($classes); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th width="30">
                                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                            </th>
                                            <th>Class Name</th>
                                            <th>Branch</th>
                                            <th>Students</th>
                                            <th>Status</th>
                                            <th>Graduated Date</th>
                                            <th>Graduated By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($classes)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No classes found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($classes as $class): ?>
                                                <tr class="<?php echo $class['graduation_status'] === 'Graduated' ? 'table-secondary' : ''; ?>">
                                                    <td>
                                                        <?php if ($class['graduation_status'] === 'Active' && $class['active_students'] > 0): ?>
                                                            <input type="checkbox" class="class-checkbox" value="<?php echo $class['id']; ?>" 
                                                                   data-class-name="<?php echo htmlspecialchars($class['class_name']); ?>"
                                                                   data-students="<?php echo $class['active_students']; ?>">
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($class['class_name']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($class['class_code']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($class['branch_name'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge bg-info">Total: <?php echo $class['total_students']; ?></span>
                                                        <?php if ($class['active_students'] > 0): ?>
                                                            <span class="badge bg-success">Active: <?php echo $class['active_students']; ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($class['graduated_students'] > 0): ?>
                                                            <span class="badge bg-secondary">Graduated: <?php echo $class['graduated_students']; ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($class['graduation_status'] === 'Graduated'): ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="ri-graduation-cap-line"></i> Graduated
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">
                                                                <i class="ri-checkbox-circle-line"></i> Active
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($class['graduated_at']): ?>
                                                            <?php echo formatDate($class['graduated_at'], 'd-m-Y H:i'); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($class['graduated_by_username'] ?? '-'); ?>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <?php if ($class['graduation_status'] === 'Graduated'): ?>
                                                                <button class="btn btn-sm btn-info" onclick="viewGraduationDetails(<?php echo $class['id']; ?>)" title="View Details">
                                                                    <i class="ri-eye-line"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-sm btn-primary" onclick="graduateSingleClass(<?php echo $class['id']; ?>, '<?php echo htmlspecialchars($class['class_name']); ?>', <?php echo $class['active_students']; ?>)" title="Graduate">
                                                                    <i class="ri-graduation-cap-line"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graduation History -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Graduation History</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Class</th>
                                            <th>Branch</th>
                                            <th>Action</th>
                                            <th>Students Affected</th>
                                            <th>Performed By</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($graduationLogs)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No graduation history found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($graduationLogs as $log): ?>
                                                <tr>
                                                    <td><?php echo formatDateTime($log['created_at']); ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($log['class_name']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($log['class_code']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($log['branch_name'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $log['action'] === 'Graduated' ? 'success' : 'warning'; ?>">
                                                            <?php echo htmlspecialchars($log['action']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $log['students_affected']; ?></td>
                                                    <td><?php echo htmlspecialchars($log['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['remarks'] ?? '-'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<!-- Graduation Confirmation Modal -->
<div class="modal fade" id="graduationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="ri-alert-line"></i> Confirm Class Graduation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h5 class="alert-heading"><strong>⚠️ WARNING: This action is PERMANENT and IRREVERSIBLE!</strong></h5>
                    <p class="mb-0">Once you graduate a class, all academic and financial operations will be permanently disabled.</p>
                </div>

                <div id="graduationPreview">
                    <h6>Classes to be Graduated:</h6>
                    <ul id="selectedClassesList"></ul>
                    <p><strong>Total Students Affected: <span id="totalStudentsCount">0</span></strong></p>
                </div>

                <div class="mb-3 mt-3">
                    <label for="graduationRemarks" class="form-label">Remarks (Optional):</label>
                    <textarea class="form-control" id="graduationRemarks" rows="3" 
                              placeholder="Add any remarks about this graduation..."></textarea>
                </div>

                <div class="form-check mt-3">
                    <input type="checkbox" class="form-check-input" id="confirmGraduation" required>
                    <label class="form-check-label" for="confirmGraduation">
                        <strong>I understand that this action will permanently disable all academic and financial operations for the selected classes.</strong>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmGraduationBtn" onclick="processGraduation()" disabled>
                    <i class="ri-graduation-cap-line"></i> Confirm & Graduate Classes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle select all checkbox
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.class-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

// Show graduation modal for selected classes
function showGraduationModal() {
    const checked = document.querySelectorAll('.class-checkbox:checked');
    
    if (checked.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Classes Selected',
            text: 'Please select at least one class to graduate.'
        });
        return;
    }

    let classesList = '';
    let totalStudents = 0;
    
    checked.forEach(cb => {
        const className = cb.getAttribute('data-class-name');
        const students = parseInt(cb.getAttribute('data-students'));
        classesList += `<li><strong>${className}</strong> (${students} students)</li>`;
        totalStudents += students;
    });

    document.getElementById('selectedClassesList').innerHTML = classesList;
    document.getElementById('totalStudentsCount').textContent = totalStudents;
    document.getElementById('graduationRemarks').value = '';
    document.getElementById('confirmGraduation').checked = false;
    document.getElementById('confirmGraduationBtn').disabled = true;

    const modal = new bootstrap.Modal(document.getElementById('graduationModal'));
    modal.show();
}

// Enable/disable confirmation button based on checkbox
document.addEventListener('DOMContentLoaded', function() {
    const confirmCheckbox = document.getElementById('confirmGraduation');
    const confirmBtn = document.getElementById('confirmGraduationBtn');
    
    if (confirmCheckbox && confirmBtn) {
        confirmCheckbox.addEventListener('change', function() {
            confirmBtn.disabled = !this.checked;
        });
    }
});

// Graduate single class
function graduateSingleClass(classId, className, students) {
    Swal.fire({
        title: 'Graduate Class?',
        html: `<p>Are you sure you want to graduate <strong>${className}</strong>?</p>
               <p>This will affect <strong>${students} students</strong> and permanently disable all academic and financial operations.</p>
               <p class="text-danger"><strong>This action cannot be easily reversed!</strong></p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Graduate Class',
        cancelButtonText: 'Cancel',
        input: 'textarea',
        inputPlaceholder: 'Remarks (optional)',
        inputAttributes: {
            'aria-label': 'Remarks'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processSingleGraduation(classId, result.value || '');
        }
    });
}

// Process single class graduation
function processSingleGraduation(classId, remarks) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/graduate-class.php',
        method: 'POST',
        data: {
            class_ids: JSON.stringify([classId]),
            remarks: remarks
        },
        dataType: 'json',
        beforeSend: function() {
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we graduate the class.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Class Graduated!',
                    html: response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while processing the graduation.'
            });
        }
    });
}

// Process bulk graduation
function processGraduation() {
    const checked = document.querySelectorAll('.class-checkbox:checked');
    const classIds = Array.from(checked).map(cb => parseInt(cb.value));
    const remarks = document.getElementById('graduationRemarks').value;

    if (classIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Classes Selected',
            text: 'Please select at least one class to graduate.'
        });
        return;
    }

    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/graduate-class.php',
        method: 'POST',
        data: {
            class_ids: JSON.stringify(classIds),
            remarks: remarks
        },
        dataType: 'json',
        beforeSend: function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('graduationModal'));
            if (modal) modal.hide();
            
            Swal.fire({
                title: 'Processing Graduation...',
                text: 'Please wait while we graduate the selected classes. This may take a moment.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Classes Graduated Successfully!',
                    html: response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Graduation Failed',
                    html: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while processing the graduation. Please try again.'
            });
        }
    });
}

// View graduation details
function viewGraduationDetails(classId) {
    window.location.href = '<?php echo APP_URL; ?>modules/academics/class-graduation-details.php?id=' + classId;
}
</script>

<?php include '../../includes/footer.php'; ?>
