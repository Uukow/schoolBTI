<?php
/**
 * Online Quizzes Management
 * 
 * Create and manage online quizzes
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Online Quizzes';

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
    // Teachers/Admins see all quizzes
    $sql = "SELECT q.*, c.class_name, s.subject_name,
            u.username as created_by_name,
            (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id) as attempt_count,
            (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
            FROM quizzes q
            LEFT JOIN classes c ON q.class_id = c.id
            LEFT JOIN subjects s ON q.subject_id = s.id
            LEFT JOIN users u ON q.created_by = u.id
            WHERE q.session_id = ?";
    
    $params = [$currentSession['id']];
    $types = 'i';
    
    if (!empty($classFilter)) {
        $sql .= " AND q.class_id = ?";
        $params[] = $classFilter;
        $types .= 'i';
    }
    
    if (!empty($subjectFilter)) {
        $sql .= " AND q.subject_id = ?";
        $params[] = $subjectFilter;
        $types .= 'i';
    }
    
    if (!empty($statusFilter)) {
        if ($statusFilter == 'Upcoming') {
            $sql .= " AND q.start_time > NOW()";
        } elseif ($statusFilter == 'Active') {
            $sql .= " AND q.start_time <= NOW() AND q.end_time >= NOW()";
        } elseif ($statusFilter == 'Ended') {
            $sql .= " AND q.end_time < NOW()";
        }
    }
} else {
    // Students see only their class quizzes
    $student = getStudentByUserId($currentUser['id']);
    if ($student) {
        $sql = "SELECT q.*, c.class_name, s.subject_name,
                (SELECT id FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ?) as attempt_id,
                (SELECT marks_obtained FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ?) as marks_obtained,
                (SELECT status FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ?) as attempt_status
                FROM quizzes q
                LEFT JOIN classes c ON q.class_id = c.id
                LEFT JOIN subjects s ON q.subject_id = s.id
                WHERE q.class_id = ? AND q.session_id = ?";
        
        $params = [$student['id'], $student['id'], $student['id'], $student['current_class_id'], $currentSession['id']];
        $types = 'iiiii';
    } else {
        $sql = "SELECT * FROM quizzes WHERE 1=0"; // No results
        $params = [];
        $types = '';
    }
}

$sql .= " ORDER BY q.start_time DESC";

$quizzes = fetchAll(executeQuery($sql, $types, $params));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                                <i class="ri-add-line"></i> Create Quiz
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Online Quizzes</h4>
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
                                        <option value="Upcoming" <?php echo ($statusFilter == 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                                        <option value="Active" <?php echo ($statusFilter == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Ended" <?php echo ($statusFilter == 'Ended') ? 'selected' : ''; ?>>Ended</option>
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

            <!-- Quizzes List -->
            <div class="row">
                <?php foreach ($quizzes as $quiz): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                    <p class="text-muted mb-0">
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($quiz['class_name']); ?></span>
                                        <span class="badge bg-info ms-1"><?php echo htmlspecialchars($quiz['subject_name']); ?></span>
                                    </p>
                                </div>
                                <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                                <div>
                                    <button onclick="manageQuestions(<?php echo $quiz['id']; ?>)" 
                                            class="btn btn-sm btn-warning" title="Manage Questions">
                                        <i class="ri-question-line"></i>
                                    </button>
                                    <button onclick="viewResults(<?php echo $quiz['id']; ?>)" 
                                            class="btn btn-sm btn-info" title="View Results">
                                        <i class="ri-bar-chart-line"></i>
                                    </button>
                                    <button onclick="deleteQuiz(<?php echo $quiz['id']; ?>)" 
                                            class="btn btn-sm btn-danger" title="Delete">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($quiz['description']): ?>
                            <p class="mb-3"><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Total Marks:</small>
                                    <p class="mb-0"><strong><?php echo number_format($quiz['total_marks'], 2); ?></strong></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Passing Marks:</small>
                                    <p class="mb-0"><strong><?php echo number_format($quiz['passing_marks'], 2); ?></strong></p>
                                </div>
                            </div>
                            
                            <?php if ($quiz['duration_minutes']): ?>
                            <div class="mb-3">
                                <small class="text-muted">Duration:</small>
                                <p class="mb-0"><strong><?php echo $quiz['duration_minutes']; ?> minutes</strong></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <small class="text-muted">Start Time:</small>
                                <p class="mb-0"><?php echo formatDateTime($quiz['start_time']); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">End Time:</small>
                                <p class="mb-0"><?php echo formatDateTime($quiz['end_time']); ?></p>
                            </div>
                            
                            <?php
                            $now = time();
                            $startTime = strtotime($quiz['start_time']);
                            $endTime = strtotime($quiz['end_time']);
                            $statusBadge = '';
                            if ($now < $startTime) {
                                $statusBadge = '<span class="badge bg-info">Upcoming</span>';
                            } elseif ($now >= $startTime && $now <= $endTime) {
                                $statusBadge = '<span class="badge bg-success">Active</span>';
                            } else {
                                $statusBadge = '<span class="badge bg-secondary">Ended</span>';
                            }
                            ?>
                            <div class="mb-3">
                                <?php echo $statusBadge; ?>
                            </div>
                            
                            <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                            <div class="mb-3">
                                <small class="text-muted">Questions:</small>
                                <p class="mb-0"><strong><?php echo $quiz['question_count'] ?? 0; ?></strong></p>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Attempts:</small>
                                <p class="mb-0"><strong><?php echo $quiz['attempt_count'] ?? 0; ?></strong></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                            <div class="mb-3">
                                <?php if ($quiz['attempt_id']): ?>
                                    <span class="badge bg-success">Attempted</span>
                                    <?php if ($quiz['marks_obtained'] !== null): ?>
                                        <span class="badge bg-info ms-1">Marks: <?php echo number_format($quiz['marks_obtained'], 2); ?>/<?php echo number_format($quiz['total_marks'], 2); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-warning">Not Attempted</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex gap-2">
                                <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                                <button onclick="manageQuestions(<?php echo $quiz['id']; ?>)" 
                                        class="btn btn-sm btn-warning">
                                    <i class="ri-question-line"></i> Manage Questions
                                </button>
                                <button onclick="viewResults(<?php echo $quiz['id']; ?>)" 
                                        class="btn btn-sm btn-info">
                                    <i class="ri-bar-chart-line"></i> Results
                                </button>
                                <?php else: ?>
                                    <?php if (!$quiz['attempt_id'] && $now >= $startTime && $now <= $endTime): ?>
                                    <button onclick="startQuiz(<?php echo $quiz['id']; ?>)" 
                                            class="btn btn-sm btn-success">
                                        <i class="ri-play-line"></i> Start Quiz
                                    </button>
                                    <?php elseif ($quiz['attempt_id']): ?>
                                    <button onclick="viewAttempt(<?php echo $quiz['attempt_id']; ?>)" 
                                            class="btn btn-sm btn-info">
                                        <i class="ri-eye-line"></i> View Attempt
                                    </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

<!-- Add Quiz Modal -->
<?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
<div class="modal fade" id="addQuizModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addQuizForm">
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
                            <select class="form-select" name="class_id" required>
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
                            <select class="form-select" name="subject_id" required>
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
                            <label class="form-label">Total Marks</label>
                            <input type="number" class="form-control" name="total_marks" value="100" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Passing Marks</label>
                            <input type="number" class="form-control" name="passing_marks" value="40" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" name="duration_minutes" min="1" placeholder="e.g., 60">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Time</label>
                            <input type="datetime-local" class="form-control" name="start_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">End Time</label>
                        <input type="datetime-local" class="form-control" name="end_time" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Create Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>

<script>
// Create quiz
<?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
$('#addQuizForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/lms/add-quiz.php',
        type: 'POST',
        data: $(this).serialize(),
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
                    window.location.href = '<?php echo APP_URL; ?>modules/lms/quiz-questions.php?id=' + response.quiz_id;
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

// Manage questions
function manageQuestions(id) {
    window.location.href = '<?php echo APP_URL; ?>modules/lms/quiz-questions.php?id=' + id;
}

// View results
function viewResults(id) {
    window.location.href = '<?php echo APP_URL; ?>modules/lms/quiz-results.php?id=' + id;
}

// Delete quiz
function deleteQuiz(id) {
    Swal.fire({
        title: 'Delete Quiz?',
        text: 'This will also delete all questions and attempts. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/lms/delete-quiz.php',
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

// Start quiz (Student)
<?php if (!hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
function startQuiz(id) {
    Swal.fire({
        title: 'Start Quiz?',
        text: 'Are you ready to start the quiz? Make sure you have a stable internet connection.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Start!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo APP_URL; ?>modules/lms/take-quiz.php?id=' + id;
        }
    });
}

// View attempt
function viewAttempt(id) {
    window.location.href = '<?php echo APP_URL; ?>modules/lms/view-attempt.php?id=' + id;
}
<?php endif; ?>
</script>

