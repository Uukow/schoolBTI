<?php
/**
 * Assign Sections to Students
 * 
 * Bulk assign students to sections for better organization
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Assign Sections to Students';

// Get current user
$currentUser = getCurrentUser();

// Get filter parameters
$classId = $_GET['class_id'] ?? '';
$sectionId = $_GET['section_id'] ?? '';

// Get classes (excluding graduated classes)
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get sections for selected class
$sections = [];
if (!empty($classId)) {
    $sectionsSql = "SELECT * FROM sections WHERE class_id = ? AND is_active = 1 ORDER BY section_name";
    $sections = fetchAll(executeQuery($sectionsSql, 'i', [$classId]));
}

// Get students for selected class
$students = [];
if (!empty($classId)) {
    $sql = "SELECT s.*, sec.section_name, sec.id as current_section_id
            FROM students s
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            WHERE s.current_class_id = ? AND s.status = 'Active'
            ORDER BY s.first_name, s.last_name";
    $students = fetchAll(executeQuery($sql, 'i', [$classId]));
}

// Handle bulk section assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_sections'])) {
    $selectedStudents = $_POST['students'] ?? [];
    $targetSectionId = $_POST['section_id'] ?? null;
    
    if (empty($selectedStudents)) {
        $_SESSION['error'] = 'Please select at least one student';
    } elseif (empty($targetSectionId)) {
        $_SESSION['error'] = 'Please select a section to assign';
    } else {
        beginTransaction();
        
        try {
            $assignedCount = 0;
            
            foreach ($selectedStudents as $studentId) {
                // Verify student belongs to the selected class
                $studentSql = "SELECT current_class_id FROM students WHERE id = ? AND current_class_id = ?";
                $student = fetchOne(executeQuery($studentSql, 'ii', [$studentId, $classId]));
                
                if ($student) {
                    // Update student's section
                    $updateSql = "UPDATE students SET current_section_id = ? WHERE id = ?";
                    executeQuery($updateSql, 'ii', [$targetSectionId, $studentId]);
                    $assignedCount++;
                }
            }
            
            // Get section name for logging
            $sectionSql = "SELECT section_name FROM sections WHERE id = ?";
            $section = fetchOne(executeQuery($sectionSql, 'i', [$targetSectionId]));
            $sectionName = $section['section_name'] ?? 'Unknown';
            
            logActivity(
                getCurrentUser()['id'],
                'Assign Sections',
                'Students',
                "Assigned $assignedCount students to section: $sectionName"
            );
            
            commitTransaction();
            
            $_SESSION['success'] = "$assignedCount student(s) assigned to section successfully!";
            redirect(APP_URL . 'modules/students/assign-sections.php?class_id=' . $classId);
            
        } catch (Exception $e) {
            rollbackTransaction();
            $_SESSION['error'] = 'Failed to assign sections: ' . $e->getMessage();
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
                        <h4 class="page-title">Assign Sections to Students</h4>
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

            <!-- Instructions Card -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5 class="alert-heading"><i class="ri-information-line"></i> How to Assign Students to Sections</h5>
                        <ol class="mb-0">
                            <li><strong>Step 1:</strong> Select a class from the dropdown below</li>
                            <li><strong>Step 2:</strong> The system will load all students in that class</li>
                            <li><strong>Step 3:</strong> Select the students you want to assign to a section</li>
                            <li><strong>Step 4:</strong> Choose the target section from the dropdown</li>
                            <li><strong>Step 5:</strong> Click "Assign to Section" to save</li>
                        </ol>
                        <hr>
                        <p class="mb-0"><strong>Note:</strong> If sections don't exist for a class, go to <strong>Academics → Classes</strong>, click on the class, and add sections first.</p>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Step 1: Select Class</h4>
                            
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Class</label>
                                    <select class="form-select" name="class_id" id="classSelect" required onchange="this.form.submit()">
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classId == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i> Load Students
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students List and Assignment -->
            <?php if (!empty($students)): ?>
            <form method="POST" action="">
                <input type="hidden" name="assign_sections" value="1">
                <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($classId); ?>">
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Step 2: Select Students and Assign Section</h4>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label required">Assign Selected Students to Section</label>
                                        <select class="form-select" name="section_id" required>
                                            <option value="">Select Section</option>
                                            <?php foreach ($sections as $section): ?>
                                                <option value="<?php echo $section['id']; ?>" <?php echo ($sectionId == $section['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($section['section_name']); ?> 
                                                    (Capacity: <?php echo $section['capacity']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (empty($sections)): ?>
                                        <small class="text-danger">
                                            <i class="ri-error-warning-line"></i> 
                                            No sections found for this class. 
                                            <a href="<?php echo APP_URL; ?>modules/academics/classes.php" target="_blank">
                                                Create sections first
                                            </a>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <button type="submit" class="btn btn-success btn-lg" <?php echo empty($sections) ? 'disabled' : ''; ?>>
                                            <i class="ri-check-line"></i> Assign to Section
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        Students in Class (<?php echo count($students); ?>)
                                    </h5>
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
                                                <th>Name</th>
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
                                                <td>
                                                    <?php if (!empty($student['section_name'])): ?>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($student['section_name']); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Not Assigned</span>
                                                    <?php endif; ?>
                                                </td>
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
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <?php elseif (!empty($classId)): ?>
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
// Select all functionality
$('.select-all').change(function() {
    var target = $(this).data('target');
    $(target).prop('checked', $(this).prop('checked'));
});

// Update select all when individual checkboxes change
$('.student-checkbox').change(function() {
    var total = $('.student-checkbox').length;
    var checked = $('.student-checkbox:checked').length;
    $('.select-all').prop('checked', total === checked);
});
</script>

