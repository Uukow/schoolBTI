<?php
/**
 * Support Tickets System
 * 
 * Helpdesk ticketing system
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Support Tickets';

// Get current user
$currentUser = getCurrentUser();

// Get filter
$statusFilter = $_GET['status'] ?? '';

// Build query
$sql = "SELECT t.*, u.username as created_by_name, a.username as assigned_to_name
        FROM support_tickets t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN users a ON t.assigned_to = a.id
        WHERE 1=1";

$params = [];
$types = '';

// Show only own tickets for non-admin
if (!hasRole(['Super Admin', 'Admin'])) {
    $sql .= " AND t.user_id = ?";
    $params[] = $currentUser['id'];
    $types .= 'i';
}

if (!empty($statusFilter)) {
    $sql .= " AND t.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
$tickets = fetchAll($stmt);

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
    FROM support_tickets WHERE 1=1";

if (!hasRole(['Super Admin', 'Admin'])) {
    $statsSql .= " AND user_id = " . $currentUser['id'];
}

$statsResult = executeQuery($statsSql);
$stats = fetchOne($statsResult);

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                                <i class="ri-add-line"></i> Create Ticket
                            </button>
                        </div>
                        <h4 class="page-title">Support Tickets</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-customer-service-2-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total</h5>
                                    <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-warning-lighten text-warning">
                                        <i class="ri-time-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Open</h5>
                                    <h2 class="mb-0"><?php echo $stats['open']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-info-lighten text-info">
                                        <i class="ri-loader-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">In Progress</h5>
                                    <h2 class="mb-0"><?php echo $stats['in_progress']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-check-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Resolved</h5>
                                    <h2 class="mb-0"><?php echo $stats['resolved']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Tickets (<?php echo count($tickets); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th>Ticket No</th>
                                            <th>Subject</th>
                                            <th>Category</th>
                                            <th>Priority</th>
                                            <th>Created By</th>
                                            <th>Assigned To</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $ticket): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($ticket['ticket_no']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($ticket['category'] ?? 'General'); ?>
                                                </span>
                                            </td>
                                            <td>
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
                                            </td>
                                            <td><?php echo htmlspecialchars($ticket['created_by_name']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned'); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                switch($ticket['status']) {
                                                    case 'Open': $statusClass = 'warning'; break;
                                                    case 'In Progress': $statusClass = 'info'; break;
                                                    case 'Resolved': $statusClass = 'success'; break;
                                                    case 'Closed': $statusClass = 'dark'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($ticket['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($ticket['created_at']); ?></td>
                                            <td>
                                                <a href="view-ticket.php?id=<?php echo $ticket['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="ri-eye-line"></i>
                                                </a>
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

<!-- Create Ticket Modal -->
<div class="modal fade" id="createTicketModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Support Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTicketForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Subject</label>
                        <input type="text" class="form-control" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Description</label>
                        <textarea class="form-control" name="description" rows="5" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="Technical">Technical</option>
                                <option value="Academic">Academic</option>
                                <option value="Financial">Financial</option>
                                <option value="General">General</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-send-plane-line"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Create ticket
$('#createTicketForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/support/create-ticket.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#createTicketModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});
</script>

