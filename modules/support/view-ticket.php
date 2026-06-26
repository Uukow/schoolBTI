<?php
/**
 * View Support Ticket
 * 
 * View ticket details and replies, add replies
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'View Ticket';

// Get ticket ID
$ticketId = $_GET['id'] ?? 0;

if (empty($ticketId)) {
    $_SESSION['error'] = 'Invalid ticket ID';
    redirect(APP_URL . 'modules/support/tickets.php');
}

// Get current user
$currentUser = getCurrentUser();

// Get ticket details
$sql = "SELECT t.*, 
        u.username as created_by_name, u.email as created_by_email,
        a.username as assigned_to_name, a.email as assigned_to_email
        FROM support_tickets t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN users a ON t.assigned_to = a.id
        WHERE t.id = ?";

$stmt = executeQuery($sql, 'i', [$ticketId]);
$ticket = fetchOne($stmt);

if (!$ticket) {
    $_SESSION['error'] = 'Ticket not found';
    redirect(APP_URL . 'modules/support/tickets.php');
}

// Check access permission - only owner or admin can view
if (!hasRole(['Super Admin', 'Admin']) && $ticket['user_id'] != $currentUser['id']) {
    $_SESSION['error'] = 'You do not have permission to view this ticket';
    redirect(APP_URL . 'modules/support/tickets.php');
}

// Get ticket replies
$repliesSql = "SELECT tr.*, u.username, u.email
               FROM ticket_replies tr
               LEFT JOIN users u ON tr.user_id = u.id
               WHERE tr.ticket_id = ?
               ORDER BY tr.created_at ASC";
$repliesStmt = executeQuery($repliesSql, 'i', [$ticketId]);
$replies = fetchAll($repliesStmt);

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_reply'])) {
    $replyMessage = sanitize($_POST['reply_message'] ?? '');
    
    if (empty($replyMessage)) {
        $_SESSION['error'] = 'Reply message is required';
    } else {
        $insertReplySql = "INSERT INTO ticket_replies (ticket_id, user_id, message) 
                          VALUES (?, ?, ?)";
        $replyStmt = executeQuery($insertReplySql, 'iis', [$ticketId, $currentUser['id'], $replyMessage]);
        
        if ($replyStmt) {
            // Update ticket status if it was closed/resolved
            if (in_array($ticket['status'], ['Resolved', 'Closed'])) {
                $updateStatusSql = "UPDATE support_tickets SET status = 'Reopened', updated_at = NOW() WHERE id = ?";
                executeQuery($updateStatusSql, 'i', [$ticketId]);
            } else {
                $updateStatusSql = "UPDATE support_tickets SET updated_at = NOW() WHERE id = ?";
                executeQuery($updateStatusSql, 'i', [$ticketId]);
            }
            
            logActivity($currentUser['id'], 'Ticket Reply', 'Support', "Added reply to ticket: {$ticket['ticket_no']}");
            $_SESSION['success'] = 'Reply added successfully';
            redirect(APP_URL . 'modules/support/view-ticket.php?id=' . $ticketId);
        } else {
            $_SESSION['error'] = 'Failed to add reply';
        }
    }
}

// Handle status update (Admin only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && hasRole(['Super Admin', 'Admin'])) {
    $newStatus = $_POST['status'] ?? '';
    $resolution = sanitize($_POST['resolution'] ?? '');
    
    if (!empty($newStatus)) {
        $updateStatusSql = "UPDATE support_tickets SET status = ?, resolution = ?, updated_at = NOW() WHERE id = ?";
        $updateStmt = executeQuery($updateStatusSql, 'ssi', [$newStatus, $resolution, $ticketId]);
        
        if ($updateStmt) {
            logActivity($currentUser['id'], 'Update Ticket Status', 'Support', "Updated ticket {$ticket['ticket_no']} status to: {$newStatus}");
            $_SESSION['success'] = 'Ticket status updated successfully';
            redirect(APP_URL . 'modules/support/view-ticket.php?id=' . $ticketId);
        } else {
            $_SESSION['error'] = 'Failed to update ticket status';
        }
    }
}

// Handle assignment (Admin only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_ticket']) && hasRole(['Super Admin', 'Admin'])) {
    $assignTo = $_POST['assigned_to'] ?? null;
    
    $assignSql = "UPDATE support_tickets SET assigned_to = ?, updated_at = NOW() WHERE id = ?";
    $assignStmt = executeQuery($assignSql, 'ii', [$assignTo ?: null, $ticketId]);
    
    if ($assignStmt) {
        logActivity($currentUser['id'], 'Assign Ticket', 'Support', "Assigned ticket {$ticket['ticket_no']} to user ID: {$assignTo}");
        $_SESSION['success'] = 'Ticket assigned successfully';
        redirect(APP_URL . 'modules/support/view-ticket.php?id=' . $ticketId);
    } else {
        $_SESSION['error'] = 'Failed to assign ticket';
    }
}

// Refresh ticket data
$stmt = executeQuery($sql, 'i', [$ticketId]);
$ticket = fetchOne($stmt);
$repliesStmt = executeQuery($repliesSql, 'i', [$ticketId]);
$replies = fetchAll($repliesStmt);

// Get users for assignment (Admin only)
$usersForAssignment = [];
if (hasRole(['Super Admin', 'Admin'])) {
    $usersSql = "SELECT u.id, u.username, u.email, r.role_name
                 FROM users u
                 LEFT JOIN roles r ON u.role_id = r.id
                 WHERE u.is_active = 1 AND r.role_name IN ('Super Admin', 'Admin', 'Teacher')
                 ORDER BY r.role_name, u.username";
    $usersStmt = executeQuery($usersSql);
    $usersForAssignment = fetchAll($usersStmt);
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
                            <a href="<?php echo APP_URL; ?>modules/support/tickets.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Tickets
                            </a>
                        </div>
                        <h4 class="page-title">View Ticket</h4>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-line me-2"></i><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Ticket Details -->
                <div class="col-lg-8">
                    <!-- Ticket Info Card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="mb-1"><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                                    <p class="text-muted mb-0">Ticket #<?php echo htmlspecialchars($ticket['ticket_no']); ?></p>
                                </div>
                                <div>
                                    <?php
                                    $statusClass = 'secondary';
                                    switch($ticket['status']) {
                                        case 'Open': $statusClass = 'warning'; break;
                                        case 'In Progress': $statusClass = 'info'; break;
                                        case 'Resolved': $statusClass = 'success'; break;
                                        case 'Closed': $statusClass = 'dark'; break;
                                        case 'Reopened': $statusClass = 'danger'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?> fs-6">
                                        <?php echo htmlspecialchars($ticket['status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-2"><strong>Description:</strong></p>
                                <div class="p-3 bg-light rounded">
                                    <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Category:</strong></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($ticket['category'] ?? 'General'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Priority:</strong></p>
                                    <?php
                                    $priorityClass = 'secondary';
                                    switch($ticket['priority']) {
                                        case 'Low': $priorityClass = 'info'; break;
                                        case 'Medium': $priorityClass = 'warning'; break;
                                        case 'High': $priorityClass = 'danger'; break;
                                        case 'Critical': $priorityClass = 'dark'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $priorityClass; ?>">
                                        <?php echo htmlspecialchars($ticket['priority']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if (!empty($ticket['resolution'])): ?>
                            <div class="mb-3">
                                <p class="mb-2"><strong>Resolution:</strong></p>
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <?php echo nl2br(htmlspecialchars($ticket['resolution'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Replies Section -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Replies (<?php echo count($replies); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($replies)): ?>
                                <p class="text-muted text-center py-4">No replies yet. Be the first to reply!</p>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($replies as $reply): ?>
                                        <div class="timeline-item mb-4">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                                        <?php echo strtoupper(substr($reply['username'], 0, 1)); ?>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <div>
                                                                    <h6 class="mb-0"><?php echo htmlspecialchars($reply['username']); ?></h6>
                                                                    <small class="text-muted"><?php echo formatDateTime($reply['created_at'], 'd M Y, h:i A'); ?></small>
                                                                </div>
                                                                <?php if ($reply['user_id'] == $currentUser['id']): ?>
                                                                    <span class="badge bg-info">You</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                                                            <?php if (!empty($reply['attachment'])): ?>
                                                                <div class="mt-2">
                                                                    <a href="<?php echo APP_URL . 'uploads/' . $reply['attachment']; ?>" 
                                                                       class="btn btn-sm btn-outline-primary" download>
                                                                        <i class="ri-download-line"></i> Download Attachment
                                                                    </a>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Add Reply Form -->
                            <?php if ($ticket['status'] != 'Closed'): ?>
                            <hr class="my-4">
                            <h5 class="mb-3">Add Reply</h5>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Your Reply</label>
                                    <textarea class="form-control" name="reply_message" rows="4" required placeholder="Type your reply here..."></textarea>
                                </div>
                                <button type="submit" name="add_reply" class="btn btn-primary">
                                    <i class="ri-send-plane-line"></i> Send Reply
                                </button>
                            </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> This ticket is closed. You cannot add more replies.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Ticket Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Ticket Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="mb-1"><strong>Created By:</strong></p>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($ticket['created_by_name']); ?></p>
                                <small class="text-muted"><?php echo htmlspecialchars($ticket['created_by_email']); ?></small>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-1"><strong>Assigned To:</strong></p>
                                <p class="text-muted mb-0">
                                    <?php echo htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned'); ?>
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-1"><strong>Created:</strong></p>
                                <p class="text-muted mb-0"><?php echo formatDateTime($ticket['created_at'], 'd M Y, h:i A'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-1"><strong>Last Updated:</strong></p>
                                <p class="text-muted mb-0"><?php echo formatDateTime($ticket['updated_at'], 'd M Y, h:i A'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Actions -->
                    <?php if (hasRole(['Super Admin', 'Admin'])): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Admin Actions</h5>
                        </div>
                        <div class="card-body">
                            <!-- Update Status -->
                            <form method="POST" action="" class="mb-3">
                                <div class="mb-3">
                                    <label class="form-label">Update Status</label>
                                    <select name="status" class="form-select">
                                        <option value="Open" <?php echo $ticket['status'] == 'Open' ? 'selected' : ''; ?>>Open</option>
                                        <option value="In Progress" <?php echo $ticket['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="Resolved" <?php echo $ticket['status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        <option value="Closed" <?php echo $ticket['status'] == 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                        <option value="Reopened" <?php echo $ticket['status'] == 'Reopened' ? 'selected' : ''; ?>>Reopened</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Resolution Notes</label>
                                    <textarea class="form-control" name="resolution" rows="3" placeholder="Add resolution notes (optional)"><?php echo htmlspecialchars($ticket['resolution'] ?? ''); ?></textarea>
                                </div>
                                <button type="submit" name="update_status" class="btn btn-warning w-100">
                                    <i class="ri-edit-line"></i> Update Status
                                </button>
                            </form>
                            
                            <hr>
                            
                            <!-- Assign Ticket -->
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Assign To</label>
                                    <select name="assigned_to" class="form-select">
                                        <option value="">Unassign</option>
                                        <?php foreach ($usersForAssignment as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" 
                                                    <?php echo ($ticket['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role_name']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" name="assign_ticket" class="btn btn-info w-100">
                                    <i class="ri-user-add-line"></i> Assign Ticket
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<style>
.timeline-item {
    position: relative;
}
.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 50px;
    bottom: -20px;
    width: 2px;
    background: #e0e0e0;
}
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 16px;
    font-weight: bold;
}
</style>

