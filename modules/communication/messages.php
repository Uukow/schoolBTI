<?php
/**
 * Messages Management
 * 
 * Internal messaging system
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Messages';

// Get current user
$currentUser = getCurrentUser();

// Get filter
$filter = $_GET['filter'] ?? 'inbox'; // inbox, sent, unread

// Get messages based on filter
if ($filter == 'sent') {
    $sql = "SELECT m.*, 
            u1.username as sender_name, u1.email as sender_email,
            u2.username as receiver_name, u2.email as receiver_email
            FROM messages m
            LEFT JOIN users u1 ON m.sender_id = u1.id
            LEFT JOIN users u2 ON m.receiver_id = u2.id
            WHERE m.sender_id = ?
            ORDER BY m.sent_at DESC";
    $params = [$currentUser['id']];
    $types = 'i';
} elseif ($filter == 'unread') {
    $sql = "SELECT m.*, 
            u1.username as sender_name, u1.email as sender_email,
            u2.username as receiver_name, u2.email as receiver_email
            FROM messages m
            LEFT JOIN users u1 ON m.sender_id = u1.id
            LEFT JOIN users u2 ON m.receiver_id = u2.id
            WHERE m.receiver_id = ? AND m.is_read = 0
            ORDER BY m.sent_at DESC";
    $params = [$currentUser['id']];
    $types = 'i';
} else {
    // Inbox
    $sql = "SELECT m.*, 
            u1.username as sender_name, u1.email as sender_email,
            u2.username as receiver_name, u2.email as receiver_email
            FROM messages m
            LEFT JOIN users u1 ON m.sender_id = u1.id
            LEFT JOIN users u2 ON m.receiver_id = u2.id
            WHERE m.receiver_id = ?
            ORDER BY m.sent_at DESC";
    $params = [$currentUser['id']];
    $types = 'i';
}

$messages = fetchAll(executeQuery($sql, $types, $params));

// Get unread count
$unreadSql = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0";
$unreadStmt = executeQuery($unreadSql, 'i', [$currentUser['id']]);
$unreadCount = fetchOne($unreadStmt)['count'] ?? 0;

// Get all users for sending messages
$usersSql = "SELECT u.*, r.role_name 
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.id != ? AND u.is_active = 1
             ORDER BY r.role_name, u.username";
$users = fetchAll(executeQuery($usersSql, 'i', [$currentUser['id']]));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">
                                <i class="ri-mail-send-line"></i> Compose Message
                            </button>
                        </div>
                        <h4 class="page-title">
                            Messages
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger ms-2"><?php echo $unreadCount; ?> Unread</span>
                            <?php endif; ?>
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills nav-justified">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $filter == 'inbox' ? 'active' : ''; ?>" 
                                       href="?filter=inbox">
                                        <i class="ri-inbox-line"></i> Inbox
                                        <?php if ($unreadCount > 0): ?>
                                            <span class="badge bg-danger ms-1"><?php echo $unreadCount; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $filter == 'unread' ? 'active' : ''; ?>" 
                                       href="?filter=unread">
                                        <i class="ri-mail-unread-line"></i> Unread
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $filter == 'sent' ? 'active' : ''; ?>" 
                                       href="?filter=sent">
                                        <i class="ri-send-plane-line"></i> Sent
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <?php 
                                echo $filter == 'sent' ? 'Sent Messages' : 
                                    ($filter == 'unread' ? 'Unread Messages' : 'Inbox');
                                ?>
                            </h4>
                            
                            <?php if (!empty($messages)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <?php if ($filter == 'sent'): ?>
                                                <th>To</th>
                                            <?php else: ?>
                                                <th>From</th>
                                            <?php endif; ?>
                                            <th>Subject</th>
                                            <th>Message</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($messages as $message): ?>
                                        <tr class="<?php echo !$message['is_read'] && $filter != 'sent' ? 'table-warning' : ''; ?>">
                                            <td>
                                                <?php if ($filter == 'sent'): ?>
                                                    <strong><?php echo htmlspecialchars($message['receiver_name']); ?></strong>
                                                <?php else: ?>
                                                    <strong><?php echo htmlspecialchars($message['sender_name']); ?></strong>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($message['subject'] ?? '(No Subject)'); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : ''); ?>
                                            </td>
                                            <td><?php echo formatDateTime($message['sent_at']); ?></td>
                                            <td>
                                                <?php if ($filter == 'sent'): ?>
                                                    <span class="badge bg-info">Sent</span>
                                                <?php else: ?>
                                                    <?php if ($message['is_read']): ?>
                                                        <span class="badge bg-success">Read</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Unread</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button onclick="viewMessage(<?php echo $message['id']; ?>)" 
                                                        class="btn btn-sm btn-info" title="View">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                <?php if ($filter != 'sent'): ?>
                                                <button onclick="replyMessage(<?php echo $message['id']; ?>)" 
                                                        class="btn btn-sm btn-primary" title="Reply">
                                                    <i class="ri-reply-line"></i>
                                                </button>
                                                <?php endif; ?>
                                                <button onclick="deleteMessage(<?php echo $message['id']; ?>)" 
                                                        class="btn btn-sm btn-danger" title="Delete">
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
                                <i class="ri-inbox-line font-24"></i>
                                <h5 class="mt-2">No Messages</h5>
                                <p class="mb-0">You have no messages in this folder.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<!-- Compose Modal -->
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compose Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="composeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">To</label>
                        <select class="form-select" name="receiver_id" required>
                            <option value="">Select Recipient</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['username'] . ' (' . $user['role_name'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" placeholder="Message subject">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Message</label>
                        <textarea class="form-control" name="message" rows="6" required placeholder="Type your message..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-send-plane-line"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Compose message
$('#composeForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/communication/send-message.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sent!',
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

// View message
function viewMessage(id) {
    window.location.href = '<?php echo APP_URL; ?>modules/communication/view-message.php?id=' + id;
}

// Reply message
function replyMessage(id) {
    window.location.href = '<?php echo APP_URL; ?>modules/communication/compose.php?reply=' + id;
}

// Delete message
function deleteMessage(id) {
    Swal.fire({
        title: 'Delete Message?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/communication/delete-message.php',
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
</script>

