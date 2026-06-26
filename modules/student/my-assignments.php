<?php
/**
 * My Assignments - Student Portal
 * 
 * View assignments for the student
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Student', 'Super Admin'], APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'My Assignments';

// Get current user and student record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

$student = null;
$studentId = null;

if ($isSuperAdmin) {
    $studentId = null;
} else {
    $student = getStudentByUserId($currentUser['id']);
    if (!$student) {
        $_SESSION['error'] = 'Student profile not found. Please contact administrator to link your user account to a student record.';
        $studentId = null;
    } else {
        $studentId = $student['id'];
    }
}

$currentSession = getCurrentSession();

// Get filter
$statusFilter = $_GET['status'] ?? '';

// Check if student's class is graduated
$classGraduated = false;
if (!$isSuperAdmin && $student && isset($student['current_class_id']) && $student['current_class_id']) {
    $classGraduated = isClassGraduated($student['current_class_id']);
}

// Get assignments
if ($isSuperAdmin) {
    $assignments = [];
} else {
    if ($student && isset($student['current_class_id']) && $student['current_class_id']) {
        $sql = "SELECT a.*, c.class_name, c.graduation_status, s.subject_name,
                (SELECT id FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?) as submission_id,
                (SELECT marks_obtained FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?) as marks_obtained,
                (SELECT submitted_at FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?) as submitted_at
                FROM assignments a
                LEFT JOIN classes c ON a.class_id = c.id
                LEFT JOIN subjects s ON a.subject_id = s.id
                WHERE a.class_id = ? AND a.session_id = ?";
        
        $params = [$studentId, $studentId, $studentId, $student['current_class_id'], $currentSession['id']];
        $types = 'iiiii';
    
    if ($statusFilter == 'submitted') {
        $sql .= " AND EXISTS (SELECT 1 FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?)";
        $params[] = $studentId;
        $types .= 'i';
    } elseif ($statusFilter == 'pending') {
        $sql .= " AND NOT EXISTS (SELECT 1 FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?) AND a.due_date >= CURDATE()";
        $params[] = $studentId;
        $types .= 'i';
    } elseif ($statusFilter == 'overdue') {
        $sql .= " AND NOT EXISTS (SELECT 1 FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?) AND a.due_date < CURDATE()";
        $params[] = $studentId;
        $types .= 'i';
    }
    
        $sql .= " ORDER BY a.due_date DESC";
        
        $stmt = executeQuery($sql, $types, $params);
        $assignments = fetchAll($stmt);
    } else {
        $assignments = [];
    }
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
                        <h4 class="page-title">My Assignments</h4>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <?php if (!$isSuperAdmin): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Filter Assignments</h4>
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="">All Assignments</option>
                                                <option value="pending" <?php echo $statusFilter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="submitted" <?php echo $statusFilter == 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                                                <option value="overdue" <?php echo $statusFilter == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="ri-search-line"></i> Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Assignments List -->
            <div class="row">
                <div class="col-12">
                    <?php if (!$isSuperAdmin && $classGraduated): ?>
                    <div class="alert alert-warning">
                        <h5><i class="ri-graduation-cap-line"></i> Class Graduated</h5>
                        <p>Your class has been graduated. You can view your assignments and historical data, but no new submissions can be made.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Assignments</h4>
                            
                            <?php if (empty($assignments)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No assignments found.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="assignments-table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Subject</th>
                                                <th>Due Date</th>
                                                <th>Total Marks</th>
                                                <th>Status</th>
                                                <th>Marks Obtained</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assignments as $assignment): ?>
                                                <?php
                                                $isOverdue = strtotime($assignment['due_date']) < time() && !$assignment['submission_id'];
                                                $statusBadge = 'bg-secondary';
                                                $statusText = 'Not Submitted';
                                                
                                                if ($assignment['submission_id']) {
                                                    $statusBadge = 'bg-success';
                                                    $statusText = 'Submitted';
                                                } elseif ($isOverdue) {
                                                    $statusBadge = 'bg-danger';
                                                    $statusText = 'Overdue';
                                                } elseif (strtotime($assignment['due_date']) >= time()) {
                                                    $statusBadge = 'bg-warning';
                                                    $statusText = 'Pending';
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                                                        <?php if ($assignment['description']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($assignment['description'], 0, 100)); ?>...</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($assignment['subject_name']); ?></td>
                                                    <td>
                                                        <?php echo formatDateTime($assignment['due_date'], 'd M Y, h:i A'); ?>
                                                        <?php if ($isOverdue): ?>
                                                            <br><small class="text-danger">Overdue</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo $assignment['total_marks']; ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($assignment['marks_obtained'] !== null): ?>
                                                            <strong><?php echo $assignment['marks_obtained']; ?></strong> / <?php echo $assignment['total_marks']; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo APP_URL; ?>modules/lms/assignments.php?view=<?php echo $assignment['id']; ?>" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="ri-eye-line"></i> View
                                                        </a>
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

    <?php include '../../includes/footer.php'; ?>
</div>

<script>
$(document).ready(function() {
    $('#assignments-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[2, 'desc']]
    });
});
</script>

