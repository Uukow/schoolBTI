<?php
/**
 * Exams Management
 * 
 * Create and manage examinations
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Manage Exams';

// Get current session
$currentSession = getCurrentSession();

// Get exams
$sql = "SELECT e.*, et.exam_name as exam_type_name, c.class_name, 
        (SELECT COUNT(*) FROM exam_schedule es WHERE es.exam_id = e.id) as subject_count
        FROM exams e
        LEFT JOIN exam_types et ON e.exam_type_id = et.id
        LEFT JOIN classes c ON e.class_id = c.id
        WHERE e.session_id = ?
        ORDER BY e.start_date DESC";

$stmt = executeQuery($sql, 'i', [$currentSession['id']]);
$exams = fetchAll($stmt);

// Get exam types
$examTypesSql = "SELECT * FROM exam_types ORDER BY exam_name";
$examTypes = fetchAll(executeQuery($examTypesSql));

// Get classes (excluding graduated classes)
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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal">
                                <i class="ri-file-add-line"></i> Create Exam
                            </button>
                        </div>
                        <h4 class="page-title">Manage Examinations</h4>
                    </div>
                </div>
            </div>

            <!-- Exams List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Exams (<?php echo count($exams); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th>Exam Name</th>
                                            <th>Type</th>
                                            <th>Class</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Subjects</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($exams as $exam): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo htmlspecialchars($exam['exam_type_name']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($exam['class_name']); ?></td>
                                            <td><?php echo formatDate($exam['start_date']); ?></td>
                                            <td><?php echo formatDate($exam['end_date']); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $exam['subject_count']; ?> Subjects</span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="schedule.php?exam_id=<?php echo $exam['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Schedule">
                                                        <i class="ri-calendar-line"></i>
                                                    </a>
                                                    <a href="marks-entry.php?exam_id=<?php echo $exam['id']; ?>" 
                                                       class="btn btn-sm btn-success" title="Enter Marks">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button onclick="deleteExam(<?php echo $exam['id']; ?>)" 
                                                            class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
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

<!-- Add Exam Modal -->
<div class="modal fade" id="addExamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Examination</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addExamForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Exam Name</label>
                        <input type="text" class="form-control" name="exam_name" placeholder="e.g., First Term Exam 2025" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Exam Type</label>
                        <select class="form-select" name="exam_type_id" required>
                            <option value="">Select Type</option>
                            <?php foreach ($examTypes as $type): ?>
                                <option value="<?php echo $type['id']; ?>">
                                    <?php echo htmlspecialchars($type['exam_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Class</label>
                        <select class="form-select" name="class_id" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Create Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add exam
$('#addExamForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/exams/add-exam.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addExamModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete exam
function deleteExam(examId) {
    confirmAction('Are you sure you want to delete this exam? This will delete all related schedules and marks.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/exams/delete-exam.php',
            type: 'POST',
            data: { id: examId },
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
</script>

