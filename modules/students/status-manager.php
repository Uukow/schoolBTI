<?php
/**
 * Student Status Manager
 * 
 * Bulk update student statuses
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Student Status Manager';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $studentIds = $_POST['student_ids'] ?? [];
    $newStatus = $_POST['new_status'] ?? '';
    
    if (!empty($studentIds) && !empty($newStatus)) {
        try {
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            $sql = "UPDATE students SET status = ? WHERE id IN ($placeholders)";
            
            $params = array_merge([$newStatus], $studentIds);
            $types = 's' . str_repeat('i', count($studentIds));
            
            executeQuery($sql, $types, $params);
            
            // Log activity
            logActivity(
                getCurrentUser()['id'],
                'Bulk Update Status',
                'Students',
                "Updated status to $newStatus for " . count($studentIds) . " student(s)"
            );
            
            $_SESSION['success'] = count($studentIds) . " student(s) status updated to $newStatus successfully!";
            redirect($_SERVER['PHP_SELF']);
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to update status: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'Please select students and status';
    }
}

// Get all students grouped by status
$sql = "SELECT s.*, c.class_name, sec.section_name 
        FROM students s 
        LEFT JOIN classes c ON s.current_class_id = c.id 
        LEFT JOIN sections sec ON s.current_section_id = sec.id";

// Branch filter for non-super admins
if (!hasRole(['Super Admin'])) {
    $sql .= " WHERE s.branch_id = ?";
    $stmt = executeQuery($sql, 'i', [$currentUser['branch_id']]);
} else {
    $stmt = executeQuery($sql);
}

$allStudents = fetchAll($stmt);

// Group by status
$studentsByStatus = [
    'Active' => [],
    'Inactive' => [],
    'Graduated' => [],
    'Transferred' => [],
    'Suspended' => [],
    'Expelled' => []
];

foreach ($allStudents as $student) {
    $studentsByStatus[$student['status']][] = $student;
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
                            <a href="<?php echo APP_URL; ?>modules/students/list.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to List
                            </a>
                        </div>
                        <h4 class="page-title">Student Status Manager</h4>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line me-2"></i>
                <?php 
                echo htmlspecialchars($_SESSION['success']); 
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Status Overview -->
            <div class="row">
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <h5 class="text-muted fw-normal mt-0">Active</h5>
                            <h3 class="mt-3 mb-3 text-success"><?php echo count($studentsByStatus['Active']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <h5 class="text-muted fw-normal mt-0">Inactive</h5>
                            <h3 class="mt-3 mb-3 text-warning"><?php echo count($studentsByStatus['Inactive']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <h5 class="text-muted fw-normal mt-0">Graduated</h5>
                            <h3 class="mt-3 mb-3 text-info"><?php echo count($studentsByStatus['Graduated']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <h5 class="text-muted fw-normal mt-0">Transferred</h5>
                            <h3 class="mt-3 mb-3 text-primary"><?php echo count($studentsByStatus['Transferred']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <h5 class="text-muted fw-normal mt-0">Suspended</h5>
                            <h3 class="mt-3 mb-3 text-dark"><?php echo count($studentsByStatus['Suspended']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <h5 class="text-muted fw-normal mt-0">Expelled</h5>
                            <h3 class="mt-3 mb-3 text-danger"><?php echo count($studentsByStatus['Expelled']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Status Update Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Bulk Status Update</h4>
                            
                            <form method="POST" id="statusForm">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Select New Status</label>
                                        <select name="new_status" class="form-select" required>
                                            <option value="">Choose status...</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="Graduated">Graduated</option>
                                            <option value="Transferred">Transferred</option>
                                            <option value="Suspended">Suspended</option>
                                            <option value="Expelled">Expelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">&nbsp;</label>
                                        <div>
                                            <button type="submit" name="update_status" class="btn btn-primary">
                                                <i class="ri-refresh-line"></i> Update Selected Students
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="selectAll()">
                                                <i class="ri-checkbox-multiple-line"></i> Select All
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="deselectAll()">
                                                <i class="ri-checkbox-blank-line"></i> Deselect All
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Students by Status -->
                                <?php foreach ($studentsByStatus as $status => $students): ?>
                                    <?php if (!empty($students)): ?>
                                    <div class="mb-4">
                                        <h5 class="mb-3">
                                            <?php
                                            $statusClass = 'secondary';
                                            switch($status) {
                                                case 'Active': $statusClass = 'success'; break;
                                                case 'Inactive': $statusClass = 'warning'; break;
                                                case 'Graduated': $statusClass = 'info'; break;
                                                case 'Transferred': $statusClass = 'primary'; break;
                                                case 'Expelled': $statusClass = 'danger'; break;
                                                case 'Suspended': $statusClass = 'dark'; break;
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo $status; ?> (<?php echo count($students); ?>)
                                            </span>
                                        </h5>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th width="30">
                                                            <input type="checkbox" class="form-check-input select-group" 
                                                                   data-status="<?php echo $status; ?>">
                                                        </th>
                                                        <th>Student ID</th>
                                                        <th>Name</th>
                                                        <th>Class</th>
                                                        <th>Section</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($students as $student): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="form-check-input student-checkbox" 
                                                                   name="student_ids[]" value="<?php echo $student['id']; ?>"
                                                                   data-status="<?php echo $status; ?>">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                                        <td>
                                                            <a href="view.php?id=<?php echo $student['id']; ?>">
                                                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                                                        <td>
                                                            <a href="view.php?id=<?php echo $student['id']; ?>" 
                                                               class="btn btn-sm btn-info" title="View">
                                                                <i class="ri-eye-line"></i>
                                                            </a>
                                                            <a href="edit.php?id=<?php echo $student['id']; ?>" 
                                                               class="btn btn-sm btn-warning" title="Edit">
                                                                <i class="ri-edit-line"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Select all students
function selectAll() {
    $('.student-checkbox').prop('checked', true);
}

// Deselect all students
function deselectAll() {
    $('.student-checkbox').prop('checked', false);
}

// Select/deselect by status group
$('.select-group').change(function() {
    const status = $(this).data('status');
    const isChecked = $(this).prop('checked');
    $(`.student-checkbox[data-status="${status}"]`).prop('checked', isChecked);
});

// Form submission confirmation
$('#statusForm').submit(function(e) {
    const selectedCount = $('.student-checkbox:checked').length;
    const newStatus = $('select[name="new_status"]').val();
    
    if (selectedCount === 0) {
        e.preventDefault();
        showToast('Please select at least one student', 'error');
        return false;
    }
    
    if (!newStatus) {
        e.preventDefault();
        showToast('Please select a status', 'error');
        return false;
    }
    
    if (!confirm(`Are you sure you want to change status to "${newStatus}" for ${selectedCount} student(s)?`)) {
        e.preventDefault();
        return false;
    }
});
</script>








