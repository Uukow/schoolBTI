<?php
/**
 * Student Promotion Page
 * 
 * Promote students to next class/session
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Promote Students';

// Get current session
$currentSession = getCurrentSession();

// Get classes (excluding graduated classes)
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get academic sessions
$sessionsSql = "SELECT * FROM academic_sessions ORDER BY start_date DESC";
$sessions = fetchAll(executeQuery($sessionsSql));

// Get students if class selected
$students = [];
$fromClassId = $_GET['from_class_id'] ?? '';
$fromSectionId = $_GET['from_section_id'] ?? '';

if (!empty($fromClassId)) {
    $sql = "SELECT s.*, c.class_name, sec.section_name
            FROM students s
            LEFT JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            WHERE s.current_class_id = ? AND s.status = 'Active'";
    
    $params = [$fromClassId];
    $types = 'i';
    
    if (!empty($fromSectionId)) {
        $sql .= " AND s.current_section_id = ?";
        $params[] = $fromSectionId;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY s.first_name, s.last_name";
    
    $stmt = executeQuery($sql, $types, $params);
    $students = fetchAll($stmt);
}

// Handle bulk promotion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['promote_students'])) {
    $selectedStudents = $_POST['students'] ?? [];
    $toClassId = $_POST['to_class_id'] ?? 0;
    $toSectionId = $_POST['to_section_id'] ?? null;
    $toSessionId = $_POST['to_session_id'] ?? null;
    
    if (empty($selectedStudents)) {
        $_SESSION['error'] = 'Please select at least one student';
    } elseif (empty($toClassId)) {
        $_SESSION['error'] = 'Please select destination class';
    } else {
        beginTransaction();
        
        try {
            $promotedCount = 0;
            
            foreach ($selectedStudents as $studentId) {
                // Get student details
                $sql = "SELECT * FROM students WHERE id = ?";
                $stmt = executeQuery($sql, 'i', [$studentId]);
                $student = fetchOne($stmt);
                
                if ($student) {
                    // Record promotion history
                    $historySql = "INSERT INTO student_promotions 
                                  (student_id, from_class_id, to_class_id, from_session_id, to_session_id, promotion_date, status)
                                  VALUES (?, ?, ?, ?, ?, CURDATE(), 'Promoted')";
                    
                    executeQuery($historySql, 'iiiis', [
                        $studentId,
                        $student['current_class_id'],
                        $toClassId,
                        $currentSession['id'],
                        $toSessionId ?? $currentSession['id']
                    ]);
                    
                    // Update student's class
                    $updateSql = "UPDATE students 
                                 SET current_class_id = ?, current_section_id = ? 
                                 WHERE id = ?";
                    
                    executeQuery($updateSql, 'iii', [$toClassId, $toSectionId, $studentId]);
                    
                    $promotedCount++;
                }
            }
            
            logActivity(
                getCurrentUser()['id'],
                'Promote Students',
                'Students',
                "Promoted $promotedCount students"
            );
            
            commitTransaction();
            
            $_SESSION['success'] = "$promotedCount students promoted successfully!";
            redirect(APP_URL . 'modules/students/promote.php');
            
        } catch (Exception $e) {
            rollbackTransaction();
            $errors[] = 'Failed to promote students: ' . $e->getMessage();
        }
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
                        <div class="page-title-right">
                            <a href="<?php echo APP_URL; ?>modules/students/list.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Students
                            </a>
                        </div>
                        <h4 class="page-title">Promote Students</h4>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="ri-check-line me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="ri-error-warning-line me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Selection Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Step 1: Select Current Class</h4>
                            
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label required">From Class</label>
                                    <select class="form-select" name="from_class_id" id="fromClassSelect" required>
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" 
                                                    <?php echo ($fromClassId == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">From Section (Optional)</label>
                                    <select class="form-select" name="from_section_id" id="fromSectionSelect">
                                        <option value="">All Sections</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Load Students
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student List for Promotion -->
            <?php if (!empty($students)): ?>
            <form method="POST" action="">
                <input type="hidden" name="promote_students" value="1">
                
                <!-- Destination Selection -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Step 2: Select Destination</h4>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">To Class</label>
                                        <select class="form-select" name="to_class_id" id="toClassSelect" required>
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>">
                                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">To Section (Optional)</label>
                                        <select class="form-select" name="to_section_id" id="toSectionSelect">
                                            <option value="">Select Section</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">To Session (Optional)</label>
                                        <select class="form-select" name="to_session_id">
                                            <option value="">Keep Current Session</option>
                                            <?php foreach ($sessions as $session): ?>
                                                <option value="<?php echo $session['id']; ?>">
                                                    <?php echo htmlspecialchars($session['session_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="header-title mb-0">
                                        Step 3: Select Students to Promote
                                        <span class="badge bg-info ms-2"><?php echo count($students); ?> Students</span>
                                    </h4>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input select-all" 
                                               data-target=".student-checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll">
                                            Select All
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">
                                                    <input type="checkbox" class="form-check-input select-all" 
                                                           data-target=".student-checkbox">
                                                </th>
                                                <th>Student ID</th>
                                                <th>Student Name</th>
                                                <th>Current Class</th>
                                                <th>Current Section</th>
                                                <th>Gender</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input student-checkbox" 
                                                           name="students[]" value="<?php echo $student['id']; ?>">
                                                </td>
                                                <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                                                <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($student['gender'] == 'Male') ? 'primary' : 'info'; ?>">
                                                        <?php echo htmlspecialchars($student['gender']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="ri-arrow-up-circle-line"></i> Promote Selected Students
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <?php endif; ?>

            <?php if (empty($students) && !empty($fromClassId)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ri-information-line"></i> No active students found in selected class.
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Load sections for FROM class
$('#fromClassSelect').change(function() {
    const classId = $(this).val();
    loadSectionsFor(classId, '#fromSectionSelect');
});

// Load sections for TO class
$('#toClassSelect').change(function() {
    const classId = $(this).val();
    loadSectionsFor(classId, '#toSectionSelect');
});

// Load sections on page load if class is selected
$(document).ready(function() {
    <?php if (!empty($fromClassId)): ?>
    loadSectionsFor(<?php echo $fromClassId; ?>, '#fromSectionSelect', <?php echo $fromSectionId ?: 'null'; ?>);
    <?php endif; ?>
});

function loadSectionsFor(classId, targetSelect, selectedId = null) {
    if (!classId) {
        $(targetSelect).html('<option value="">Select Section</option>');
        return;
    }
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/get-sections.php',
        type: 'GET',
        data: { class_id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">All Sections</option>';
                response.data.forEach(function(section) {
                    const selected = (selectedId && section.id == selectedId) ? 'selected' : '';
                    options += `<option value="${section.id}" ${selected}>${section.section_name}</option>`;
                });
                $(targetSelect).html(options);
            }
        }
    });
}

// Select all functionality
$('.select-all').change(function() {
    var target = $(this).data('target');
    $(target).prop('checked', $(this).prop('checked'));
});
</script>

