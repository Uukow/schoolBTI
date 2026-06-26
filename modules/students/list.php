<?php
/**
 * Students List Page
 * 
 * Display all students with DataTables
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Receptionist']);
$pageTitle = 'Students List';

// Get current user
$currentUser = getCurrentUser();

// Get filter parameters
$classFilter = $_GET['class_id'] ?? '';
$sectionFilter = $_GET['section_id'] ?? '';
$statusFilter = $_GET['status'] ?? 'Active';

// Build query
$sql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name, u.id as user_account_id, u.username as portal_username
        FROM students s 
        LEFT JOIN classes c ON s.current_class_id = c.id 
        LEFT JOIN sections sec ON s.current_section_id = sec.id 
        LEFT JOIN branches b ON s.branch_id = b.id 
        LEFT JOIN users u ON s.user_id = u.id
        WHERE 1=1";

$params = [];
$types = '';

// Apply filters
if (!empty($classFilter)) {
    $sql .= " AND s.current_class_id = ?";
    $params[] = $classFilter;
    $types .= 'i';
}

if (!empty($sectionFilter)) {
    $sql .= " AND s.current_section_id = ?";
    $params[] = $sectionFilter;
    $types .= 'i';
}

if (!empty($statusFilter)) {
    $sql .= " AND s.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

// Branch filter for non-super admins
if (!hasRole(['Super Admin']) && $currentUser && isset($currentUser['branch_id'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY s.first_name, s.last_name";

$stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
$students = fetchAll($stmt);

// Get classes for filter (excluding graduated classes)
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
                            <a href="<?php echo APP_URL; ?>modules/students/assign-sections.php" class="btn btn-info me-2">
                                <i class="ri-group-line"></i> Assign Sections
                            </a>
                            <a href="<?php echo APP_URL; ?>modules/students/add.php" class="btn btn-primary">
                                <i class="ri-user-add-line"></i> Add New Student
                            </a>
                        </div>
                        <h4 class="page-title">Students</h4>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Class</label>
                                    <select name="class_id" class="form-select">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classFilter == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="Active" <?php echo ($statusFilter == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo ($statusFilter == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="Graduated" <?php echo ($statusFilter == 'Graduated') ? 'selected' : ''; ?>>Graduated</option>
                                        <option value="Transferred" <?php echo ($statusFilter == 'Transferred') ? 'selected' : ''; ?>>Transferred</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Filter
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

            <!-- Students Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Students (<?php echo count($students); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable-export">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Class</th>
                                            <th>Section</th>
                                            <th>Gender</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Portal Access</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($student['photo'])): ?>
                                                    <img src="<?php echo APP_URL . $student['photo']; ?>" 
                                                         alt="<?php echo htmlspecialchars($student['first_name']); ?>" 
                                                         class="rounded-circle" width="40" height="40">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <?php echo strtoupper(substr($student['first_name'], 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($student['student_id']); ?></strong>
                                            </td>
                                            <td>
                                                <a href="view.php?id=<?php echo $student['id']; ?>">
                                                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($student['gender'] == 'Male') ? 'primary' : 'info'; ?>">
                                                    <?php echo htmlspecialchars($student['gender']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                switch($student['status']) {
                                                    case 'Active': $statusClass = 'success'; break;
                                                    case 'Inactive': $statusClass = 'warning'; break;
                                                    case 'Graduated': $statusClass = 'info'; break;
                                                    case 'Transferred': $statusClass = 'primary'; break;
                                                    case 'Expelled': $statusClass = 'danger'; break;
                                                    case 'Suspended': $statusClass = 'dark'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($student['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($student['user_account_id'])): ?>
                                                    <span class="badge bg-success" title="Username: <?php echo htmlspecialchars($student['portal_username'] ?? 'N/A'); ?>">
                                                        <i class="ri-checkbox-circle-line"></i> Enabled
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="ri-close-circle-line"></i> Not Enabled
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view.php?id=<?php echo $student['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <?php if (hasRole(['Super Admin', 'Admin'])): ?>
                                                    <a href="edit.php?id=<?php echo $student['id']; ?>" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <?php if (empty($student['user_account_id'])): ?>
                                                        <?php if (empty($student['email'])): ?>
                                                            <button class="btn btn-sm btn-secondary" 
                                                                    title="Student must have email to enable portal" 
                                                                    disabled>
                                                                <i class="ri-lock-line"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button onclick="enableStudentPortal(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>')" 
                                                                    class="btn btn-sm btn-success" 
                                                                    title="Enable Student Portal">
                                                                <i class="ri-user-add-line"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <button onclick="resetStudentPassword(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>', '<?php echo htmlspecialchars($student['portal_username'] ?? ''); ?>')" 
                                                                class="btn btn-sm btn-warning" 
                                                                title="Reset Password">
                                                            <i class="ri-lock-password-line"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button onclick="deleteStudent(<?php echo $student['id']; ?>)" 
                                                            class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Delete student function
function deleteStudent(studentId) {
    confirmAction('Are you sure you want to delete this student? This action cannot be undone.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/students/delete.php',
            type: 'POST',
            data: { id: studentId },
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
            },
            error: function() {
                showToast('Failed to delete student', 'error');
            }
        });
    });
}

// Enable student portal function
function enableStudentPortal(studentId, studentName) {
    confirmAction('Enable portal access for ' + studentName + '? This will create a user account and allow them to login to the student portal.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/students/enable-portal.php',
            type: 'POST',
            data: { student_id: studentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var message = response.message + '<br><br>';
                    if (response.data) {
                        message += '<strong>Portal Credentials:</strong><br>';
                        message += 'Username: <code style="font-size: 16px; padding: 5px;">' + response.data.username + '</code><br>';
                        message += 'Temporary Password: <code style="font-size: 16px; padding: 5px;">' + response.data.temp_password + '</code><br><br>';
                        message += '<small class="text-muted">' + response.data.message + '</small>';
                    }
                    
                    // Show credentials in alert
                    Swal.fire({
                        title: 'Portal Access Enabled!',
                        html: message,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        width: '500px',
                        allowOutsideClick: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Failed to enable portal access', 'error');
            }
        });
    });
}

// Reset student password function
function resetStudentPassword(studentId, studentName, username) {
    confirmAction('Reset password for ' + studentName + ' (Username: ' + username + ')? A new temporary password will be generated.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/students/reset-student-password.php',
            type: 'POST',
            data: { student_id: studentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var message = response.message + '<br><br>';
                    if (response.data) {
                        message += '<strong>New Credentials:</strong><br>';
                        message += 'Username: <code style="font-size: 16px; padding: 5px;">' + response.data.username + '</code><br>';
                        message += 'New Password: <code style="font-size: 16px; padding: 5px;">' + response.data.new_password + '</code><br><br>';
                        message += '<small class="text-muted">' + response.data.message + '</small>';
                    }
                    
                    // Show credentials in alert
                    Swal.fire({
                        title: 'Password Reset Successful!',
                        html: message,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        width: '500px',
                        allowOutsideClick: false
                    });
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Failed to reset password', 'error');
            }
        });
    });
}
</script>


