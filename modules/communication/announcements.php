<?php
/**
 * Announcements Management
 * 
 * Create and manage announcements
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Announcements';

// Get current user
$currentUser = getCurrentUser();

// Get announcements
$sql = "SELECT a.*, u.username as created_by_name, b.branch_name
        FROM announcements a
        LEFT JOIN users u ON a.created_by = u.id
        LEFT JOIN branches b ON a.branch_id = b.id
        WHERE 1=1";

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND (a.branch_id = {$currentUser['branch_id']} OR a.branch_id IS NULL)";
}

$sql .= " ORDER BY a.created_at DESC";

$announcements = fetchAll(executeQuery($sql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                                <i class="ri-megaphone-line"></i> New Announcement
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Announcements</h4>
                    </div>
                </div>
            </div>

            <!-- Announcements List -->
            <div class="row">
                <?php foreach ($announcements as $announcement): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="card-title">
                                        <?php if ($announcement['is_urgent']): ?>
                                            <span class="badge bg-danger me-2">URGENT</span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($announcement['title']); ?>
                                    </h5>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="ri-user-line"></i> <?php echo htmlspecialchars($announcement['created_by_name'] ?? 'System'); ?>
                                    <span class="mx-2">|</span>
                                    <i class="ri-calendar-line"></i> <?php echo formatDate($announcement['created_at']); ?>
                                    <span class="mx-2">|</span>
                                    <i class="ri-group-line"></i> <?php echo htmlspecialchars($announcement['target_audience']); ?>
                                    <?php if ($announcement['branch_name']): ?>
                                        <span class="mx-2">|</span>
                                        <i class="ri-building-line"></i> <?php echo htmlspecialchars($announcement['branch_name']); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            
                            <?php if (hasRole(['Super Admin', 'Admin']) && $announcement['created_by'] == $currentUser['id']): ?>
                            <div class="mt-3 text-end">
                                <button onclick="deleteAnnouncement(<?php echo $announcement['id']; ?>)" 
                                        class="btn btn-sm btn-danger">
                                    <i class="ri-delete-bin-line"></i> Delete
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($announcements)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="ri-information-line font-24"></i>
                        <h5 class="mt-2">No announcements yet</h5>
                        <p class="mb-0">Create your first announcement to get started!</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAnnouncementForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Content</label>
                        <textarea class="form-control" name="content" rows="5" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Target Audience</label>
                            <select class="form-select" name="target_audience" required>
                                <option value="All">All Users</option>
                                <option value="Students">Students Only</option>
                                <option value="Teachers">Teachers Only</option>
                                <option value="Parents">Parents Only</option>
                                <option value="Staff">Staff Only</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid Until</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_urgent" value="1" id="isUrgent">
                            <label class="form-check-label" for="isUrgent">
                                Mark as Urgent
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-send-plane-line"></i> Publish Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add announcement
$('#addAnnouncementForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/communication/add-announcement.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addAnnouncementModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete announcement
function deleteAnnouncement(announcementId) {
    confirmAction('Are you sure you want to delete this announcement?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/communication/delete-announcement.php',
            type: 'POST',
            data: { id: announcementId },
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

