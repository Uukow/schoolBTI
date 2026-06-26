<?php
/**
 * Syllabus Management
 * 
 * Upload and manage curriculum/syllabus
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Syllabus Management';

// Get current session
$currentSession = getCurrentSession();

// Get filter parameters
$classFilter = $_GET['class_id'] ?? '';
$subjectFilter = $_GET['subject_id'] ?? '';

// Build query (excluding graduated classes)
$sql = "SELECT c.*, cl.class_name, s.subject_name
        FROM curriculum c
        LEFT JOIN classes cl ON c.class_id = cl.id
        LEFT JOIN subjects s ON c.subject_id = s.id
        WHERE c.session_id = ?
        AND (cl.graduation_status IS NULL OR cl.graduation_status != 'Graduated')";

$params = [$currentSession['id']];
$types = 'i';

if (!empty($classFilter)) {
    $sql .= " AND c.class_id = ?";
    $params[] = $classFilter;
    $types .= 'i';
}

if (!empty($subjectFilter)) {
    $sql .= " AND c.subject_id = ?";
    $params[] = $subjectFilter;
    $types .= 'i';
}

$sql .= " ORDER BY cl.class_order, s.subject_name";

$stmt = executeQuery($sql, $types, $params);
$syllabuses = fetchAll($stmt);

// Get classes and subjects for filters and form (excluding graduated classes)
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadSyllabusModal">
                                <i class="ri-upload-cloud-line"></i> Upload Syllabus
                            </button>
                        </div>
                        <h4 class="page-title">Syllabus & Curriculum</h4>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
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
                                <div class="col-md-4">
                                    <label class="form-label">Subject</label>
                                    <select name="subject_id" class="form-select">
                                        <option value="">All Subjects</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>" <?php echo ($subjectFilter == $subject['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Filter
                                    </button>
                                    <a href="syllabus.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Syllabus List -->
            <div class="row">
                <?php if (!empty($syllabuses)): ?>
                    <?php foreach ($syllabuses as $syllabus): ?>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="card-title mb-1">
                                            <?php echo htmlspecialchars($syllabus['class_name']); ?> - 
                                            <?php echo htmlspecialchars($syllabus['subject_name']); ?>
                                        </h5>
                                        <p class="text-muted mb-0">
                                            <small>Session: <?php echo htmlspecialchars($currentSession['session_name']); ?></small>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php if ($syllabus['syllabus']): ?>
                                <div class="mb-2">
                                    <small class="text-muted">Syllabus:</small>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars(substr($syllabus['syllabus'], 0, 200))); ?>...</p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($syllabus['learning_objectives']): ?>
                                <div class="mb-2">
                                    <small class="text-muted">Learning Objectives:</small>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars(substr($syllabus['learning_objectives'], 0, 150))); ?>...</p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                    <?php if ($syllabus['file_path']): ?>
                                    <a href="<?php echo APP_URL . $syllabus['file_path']; ?>" 
                                       class="btn btn-sm btn-primary" target="_blank" download>
                                        <i class="ri-download-line"></i> Download
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted"><small>No file attached</small></span>
                                    <?php endif; ?>
                                    
                                    <?php if (hasRole(['Super Admin', 'Admin'])): ?>
                                    <button onclick="deleteSyllabus(<?php echo $syllabus['id']; ?>)" 
                                            class="btn btn-sm btn-danger">
                                        <i class="ri-delete-bin-line"></i> Delete
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="ri-information-line font-24"></i>
                        <h5 class="mt-2">No syllabus uploaded yet</h5>
                        <p class="mb-0">Upload your first syllabus to get started!</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

<!-- Upload Syllabus Modal -->
<div class="modal fade" id="uploadSyllabusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Syllabus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadSyllabusForm" enctype="multipart/form-data">
                <div class="modal-body">
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
                    <div class="mb-3">
                        <label class="form-label">Syllabus Content</label>
                        <textarea class="form-control" name="syllabus" rows="4" 
                                  placeholder="Enter syllabus content..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Learning Objectives</label>
                        <textarea class="form-control" name="learning_objectives" rows="3" 
                                  placeholder="Enter learning objectives..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File (Optional)</label>
                        <input type="file" class="form-control" name="file" accept=".pdf,.doc,.docx">
                        <small class="text-muted">PDF or Word document</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-upload-cloud-line"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Upload syllabus
$('#uploadSyllabusForm').on('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/upload-syllabus.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#uploadSyllabusModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete syllabus
function deleteSyllabus(syllabusId) {
    confirmAction('Are you sure you want to delete this syllabus?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-syllabus.php',
            type: 'POST',
            data: { id: syllabusId },
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

