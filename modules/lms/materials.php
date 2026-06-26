<?php
/**
 * Study Materials Management
 * 
 * Upload and manage study materials
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Study Materials';

// Get current user
$currentUser = getCurrentUser();
$roleName = $currentUser['role_name'];

// Get filter parameters
$classFilter = $_GET['class_id'] ?? '';
$subjectFilter = $_GET['subject_id'] ?? '';

// Build query
$sql = "SELECT m.*, c.class_name, s.subject_name, u.username as uploaded_by_name
        FROM study_materials m
        LEFT JOIN classes c ON m.class_id = c.id
        LEFT JOIN subjects s ON m.subject_id = s.id
        LEFT JOIN users u ON m.uploaded_by = u.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($classFilter)) {
    $sql .= " AND m.class_id = ?";
    $params[] = $classFilter;
    $types .= 'i';
}

if (!empty($subjectFilter)) {
    $sql .= " AND m.subject_id = ?";
    $params[] = $subjectFilter;
    $types .= 'i';
}

$sql .= " ORDER BY m.uploaded_at DESC";

$stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
$materials = fetchAll($stmt);

// Get classes and subjects for filters (excluding graduated classes)
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
                            <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadMaterialModal">
                                <i class="ri-upload-cloud-line"></i> Upload Material
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Study Materials</h4>
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
                                    <a href="materials.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Materials Grid -->
            <div class="row">
                <?php foreach ($materials as $material): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-primary-lighten text-primary rounded">
                                            <?php
                                            $extension = strtolower(pathinfo($material['file_path'], PATHINFO_EXTENSION));
                                            $icon = 'ri-file-line';
                                            if (in_array($extension, ['pdf'])) $icon = 'ri-file-pdf-line';
                                            elseif (in_array($extension, ['doc', 'docx'])) $icon = 'ri-file-word-line';
                                            elseif (in_array($extension, ['ppt', 'pptx'])) $icon = 'ri-file-ppt-line';
                                            elseif (in_array($extension, ['xls', 'xlsx'])) $icon = 'ri-file-excel-line';
                                            ?>
                                            <i class="<?php echo $icon; ?> font-24"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($material['title']); ?></h5>
                                    <p class="text-muted text-truncate mb-2">
                                        <?php echo htmlspecialchars($material['description'] ?? 'No description'); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($material['class_name']); ?></span>
                                <span class="badge bg-info"><?php echo htmlspecialchars($material['subject_name']); ?></span>
                                <?php if ($material['file_size']): ?>
                                <span class="badge bg-secondary"><?php echo round($material['file_size'] / 1024, 2); ?> KB</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="ri-user-line"></i> <?php echo htmlspecialchars($material['uploaded_by_name']); ?>
                                    <br>
                                    <i class="ri-calendar-line"></i> <?php echo formatDate($material['uploaded_at']); ?>
                                </small>
                                <div>
                                    <a href="<?php echo APP_URL . $material['file_path']; ?>" 
                                       class="btn btn-sm btn-primary" download target="_blank">
                                        <i class="ri-download-line"></i> Download
                                    </a>
                                    <?php if (hasRole(['Super Admin', 'Admin']) || $material['uploaded_by'] == $currentUser['id']): ?>
                                    <button onclick="deleteMaterial(<?php echo $material['id']; ?>)" 
                                            class="btn btn-sm btn-danger">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($materials)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="ri-information-line font-24"></i>
                        <h5 class="mt-2">No study materials found</h5>
                        <p class="mb-0">Upload your first study material to get started!</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

<!-- Upload Material Modal -->
<div class="modal fade" id="uploadMaterialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Study Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadMaterialForm" enctype="multipart/form-data">
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
                    <div class="mb-3">
                        <label class="form-label required">File</label>
                        <input type="file" class="form-control" name="file" required>
                        <small class="text-muted">Supported: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX (Max 10MB)</small>
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
// Upload material
$('#uploadMaterialForm').on('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/lms/upload-material.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#uploadMaterialModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete material
function deleteMaterial(materialId) {
    confirmAction('Are you sure you want to delete this material?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/lms/delete-material.php',
            type: 'POST',
            data: { id: materialId },
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

