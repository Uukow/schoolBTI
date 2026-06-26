<?php
/**
 * Assignments Management
 * 
 * Create and manage assignments
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Assignments';

// Get current user
$currentUser = getCurrentUser();

// Get filters
$classFilter = $_GET['class_id'] ?? '';
$subjectFilter = $_GET['subject_id'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Get current session
$currentSession = getCurrentSession();

// Build query based on role
if (hasRole(['Super Admin', 'Admin', 'Teacher'])) {
    // Teachers/Admins see all assignments
    $sql = "SELECT a.*, c.class_name, s.subject_name, 
            u.username as created_by_name,
            (SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = a.id) as submission_count
            FROM assignments a
            LEFT JOIN classes c ON a.class_id = c.id
            LEFT JOIN subjects s ON a.subject_id = s.id
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.session_id = ?";
    
    $params = [$currentSession['id']];
    $types = 'i';
    
    if (!empty($classFilter)) {
        $sql .= " AND a.class_id = ?";
        $params[] = $classFilter;
        $types .= 'i';
    }
    
    if (!empty($subjectFilter)) {
        $sql .= " AND a.subject_id = ?";
        $params[] = $subjectFilter;
        $types .= 'i';
    }
    
    if (!empty($statusFilter)) {
        if ($statusFilter == 'Active') {
            $sql .= " AND a.due_date >= NOW()";
        } elseif ($statusFilter == 'Expired') {
            $sql .= " AND a.due_date < NOW()";
        }
    }
} else {
    // Students see only their class assignments
    $student = getStudentByUserId($currentUser['id']);
    if ($student) {
        $sql = "SELECT a.*, c.class_name, s.subject_name,
                (SELECT id FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?) as submission_id,
                (SELECT marks_obtained FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?) as marks_obtained,
                (SELECT submitted_at FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?) as submitted_at
                FROM assignments a
                LEFT JOIN classes c ON a.class_id = c.id
                LEFT JOIN subjects s ON a.subject_id = s.id
                WHERE a.class_id = ? AND a.session_id = ?";
        
        $params = [$student['id'], $student['id'], $student['id'], $student['current_class_id'], $currentSession['id']];
        $types = 'iiiii';
    } else {
        $sql = "SELECT * FROM assignments WHERE 1=0"; // No results
        $params = [];
        $types = '';
    }
}

$sql .= " ORDER BY a.due_date DESC";

$assignments = fetchAll(executeQuery($sql, $types, $params));

// Get classes and subjects for filters (excluding graduated classes)
$classesSql = "SELECT * FROM classes 
                WHERE (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_name";
$classes = fetchAll(executeQuery($classesSql));

$subjectsSql = "SELECT * FROM subjects ORDER BY subject_name";
$subjects = fetchAll(executeQuery($subjectsSql));

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
                            <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
                                <i class="ri-add-line"></i> Create Assignment
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Assignments</h4>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Class</label>
                                    <select class="form-select" name="class_id">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classFilter == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Subject</label>
                                    <select class="form-select" name="subject_id">
                                        <option value="">All Subjects</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>" <?php echo ($subjectFilter == $subject['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="Active" <?php echo ($statusFilter == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Expired" <?php echo ($statusFilter == 'Expired') ? 'selected' : ''; ?>>Expired</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Assignments List -->
            <div class="row">
                <?php foreach ($assignments as $assignment): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                                    <p class="text-muted mb-0">
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($assignment['class_name']); ?></span>
                                        <span class="badge bg-info ms-1"><?php echo htmlspecialchars($assignment['subject_name']); ?></span>
                                    </p>
                                </div>
                                <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                                <div>
                                    <button onclick="viewSubmissions(<?php echo $assignment['id']; ?>)" 
                                            class="btn btn-sm btn-info" title="View Submissions">
                                        <i class="ri-file-list-line"></i>
                                    </button>
                                    <button onclick="deleteAssignment(<?php echo $assignment['id']; ?>)" 
                                            class="btn btn-sm btn-danger" title="Delete">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($assignment['description']): ?>
                            <p class="mb-3"><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Total Marks:</small>
                                    <p class="mb-0"><strong><?php echo number_format($assignment['total_marks'], 2); ?></strong></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Due Date:</small>
                                    <p class="mb-0">
                                        <strong><?php echo formatDateTime($assignment['due_date']); ?></strong>
                                        <?php if (strtotime($assignment['due_date']) < time()): ?>
                                            <span class="badge bg-danger ms-1">Expired</span>
                                        <?php else: ?>
                                            <span class="badge bg-success ms-1">Active</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                            <div class="mb-3">
                                <small class="text-muted">Submissions:</small>
                                <p class="mb-0"><strong><?php echo $assignment['submission_count'] ?? 0; ?></strong></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                            <div class="mb-3">
                                <?php if ($assignment['submission_id']): ?>
                                    <span class="badge bg-success">Submitted</span>
                                    <?php if ($assignment['marks_obtained'] !== null): ?>
                                        <span class="badge bg-info ms-1">Marks: <?php echo number_format($assignment['marks_obtained'], 2); ?>/<?php echo number_format($assignment['total_marks'], 2); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-warning">Not Submitted</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex gap-2">
                                <?php if ($assignment['file_path']): ?>
                                <a href="<?php echo APP_URL . 'uploads/' . $assignment['file_path']; ?>" 
                                   class="btn btn-sm btn-primary" download>
                                    <i class="ri-download-line"></i> Download
                                </a>
                                <?php endif; ?>
                                
                                <?php if (!hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                                    <?php if (!$assignment['submission_id']): ?>
                                    <button onclick="submitAssignment(<?php echo $assignment['id']; ?>)" 
                                            class="btn btn-sm btn-success">
                                        <i class="ri-upload-line"></i> Submit
                                    </button>
                                    <?php else: ?>
                                    <button onclick="viewSubmission(<?php echo $assignment['submission_id']; ?>)" 
                                            class="btn btn-sm btn-info">
                                        <i class="ri-eye-line"></i> View Submission
                                    </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                                <button onclick="viewSubmissions(<?php echo $assignment['id']; ?>)" 
                                        class="btn btn-sm btn-info">
                                    <i class="ri-file-list-line"></i> Submissions (<?php echo $assignment['submission_count'] ?? 0; ?>)
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

<!-- Add Assignment Modal -->
<?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
<div class="modal fade" id="addAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAssignmentForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Class</label>
                            <select class="form-select" name="class_id" id="classSelect" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>">
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Subject</label>
                            <select class="form-select" name="subject_id" id="subjectSelect" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['id']; ?>">
                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Due Date & Time</label>
                            <input type="datetime-local" class="form-control" name="due_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Marks</label>
                            <input type="number" class="form-control" name="total_marks" value="100" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assignment File (Optional)</label>
                        <input type="file" class="form-control" name="assignment_file" accept=".pdf,.doc,.docx,.txt">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Create Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>

<script>
// Create assignment
<?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
$('#addAssignmentForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/lms/add-assignment.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
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
        }
    });
});

// View submissions
function viewSubmissions(id) {
    window.location.href = '<?php echo APP_URL; ?>modules/lms/assignment-submissions.php?id=' + id;
}

// Delete assignment
function deleteAssignment(id) {
    Swal.fire({
        title: 'Delete Assignment?',
        text: 'This will also delete all submissions. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/lms/delete-assignment.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
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
                }
            });
        }
    });
}
<?php endif; ?>

// Submit assignment (Student)
<?php if (!hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
function submitAssignment(id) {
    Swal.fire({
        title: 'Submit Assignment',
        html: `
            <form id="submitForm">
                <div class="mb-3 text-start">
                    <label class="form-label">Submission Text</label>
                    <textarea class="form-control" name="submission_text" rows="4" placeholder="Enter your answer..."></textarea>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Upload File (Optional)</label>
                    <input type="file" class="form-control" name="submission_file" accept=".pdf,.doc,.docx,.txt">
                </div>
            </form>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Submit',
        confirmButtonColor: '#28a745',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const form = document.getElementById('submitForm');
            const formData = new FormData(form);
            formData.append('assignment_id', id);
            return formData;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/lms/submit-assignment.php',
                type: 'POST',
                data: result.value,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Submitted!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
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
                }
            });
        }
    });
}

// View submission
function viewSubmission(id) {
    window.location.href = '<?php echo APP_URL; ?>modules/lms/view-submission.php?id=' + id;
}
<?php endif; ?>
</script>

