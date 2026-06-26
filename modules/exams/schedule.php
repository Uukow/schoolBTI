<?php
/**
 * Exam Schedule Page
 * 
 * Create and view exam schedules by subject
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Exam Schedule';

// Get exam ID from URL
$examId = $_GET['exam_id'] ?? '';

// Get exam details
$exam = null;
if ($examId) {
    $sql = "SELECT e.*, c.class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id WHERE e.id = ?";
    $stmt = executeQuery($sql, 'i', [$examId]);
    $exam = fetchOne($stmt);
}

// Get exams for dropdown
$examsSql = "SELECT e.*, c.class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id ORDER BY e.start_date DESC";
$exams = fetchAll(executeQuery($examsSql));

// Get exam schedule if exam selected
$schedule = [];
if ($examId) {
    $sql = "SELECT es.*, s.subject_name, s.subject_code
            FROM exam_schedule es
            LEFT JOIN subjects s ON es.subject_id = s.id
            WHERE es.exam_id = ?
            ORDER BY es.exam_date, es.start_time";
    
    $stmt = executeQuery($sql, 'i', [$examId]);
    $schedule = fetchAll($stmt);
}

// Get subjects for form
$subjectsSql = "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name";
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
                            <?php if ($examId): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                                <i class="ri-add-line"></i> Add Subject
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Exam Schedule</h4>
                    </div>
                </div>
            </div>

            <!-- Exam Selection -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Select Exam</label>
                                    <select class="form-select" name="exam_id" onchange="this.form.submit()" required>
                                        <option value="">Choose Exam</option>
                                        <?php foreach ($exams as $e): ?>
                                            <option value="<?php echo $e['id']; ?>" <?php echo ($examId == $e['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($e['exam_name']); ?> - <?php echo htmlspecialchars($e['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Display -->
            <?php if ($exam): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h4 class="header-title mb-1"><?php echo htmlspecialchars($exam['exam_name']); ?></h4>
                                    <p class="text-muted mb-0">
                                        Class: <strong><?php echo htmlspecialchars($exam['class_name']); ?></strong> | 
                                        Period: <?php echo formatDate($exam['start_date']); ?> to <?php echo formatDate($exam['end_date']); ?>
                                    </p>
                                </div>
                                <button onclick="window.print()" class="btn btn-sm btn-secondary no-print">
                                    <i class="ri-printer-line"></i> Print Schedule
                                </button>
                            </div>
                            
                            <?php if (!empty($schedule)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Date</th>
                                            <th>Subject</th>
                                            <th>Time</th>
                                            <th>Room</th>
                                            <th>Total Marks</th>
                                            <th>Passing Marks</th>
                                            <th class="no-print">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($schedule as $item): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo formatDate($item['exam_date']); ?></strong><br>
                                                <small class="text-muted"><?php echo date('l', strtotime($item['exam_date'])); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['subject_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($item['subject_code']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo date('h:i A', strtotime($item['start_time'])); ?> - 
                                                <?php echo date('h:i A', strtotime($item['end_time'])); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['room_no'] ?? 'TBA'); ?></td>
                                            <td><?php echo number_format($item['total_marks'], 0); ?></td>
                                            <td><?php echo number_format($item['passing_marks'], 0); ?></td>
                                            <td class="no-print">
                                                <button onclick="deleteSchedule(<?php echo $item['id']; ?>)" 
                                                        class="btn btn-sm btn-danger">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="ri-information-line"></i> No subjects scheduled yet. Click "Add Subject" to create schedule.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Subject to Exam Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addScheduleForm">
                <input type="hidden" name="exam_id" value="<?php echo $examId; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Subject</label>
                        <select class="form-select" name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Exam Date</label>
                        <input type="date" class="form-control" name="exam_date" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Time</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">End Time</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" class="form-control" name="room_no" placeholder="e.g., Hall A">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Marks</label>
                            <input type="number" class="form-control" name="total_marks" value="100" step="0.01">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Passing Marks</label>
                            <input type="number" class="form-control" name="passing_marks" value="40" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add to Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add schedule
$('#addScheduleForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/exams/add-schedule.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addScheduleModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete schedule
function deleteSchedule(scheduleId) {
    confirmAction('Remove this subject from exam schedule?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/exams/delete-schedule.php',
            type: 'POST',
            data: { id: scheduleId },
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

