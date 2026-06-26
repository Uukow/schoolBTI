<?php
/**
 * Fee Defaulters Page
 * 
 * View students with overdue fee payments
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Fee Defaulters';

// Get current user and filters
$currentUser = getCurrentUser();
$classFilter = $_GET['class_id'] ?? '';
$daysOverdue = $_GET['days'] ?? 30;

// Get classes
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Build query for defaulters
$sql = "SELECT i.*, s.student_id, s.first_name, s.last_name, s.phone, s.email,
        c.class_name, b.branch_name,
        (SELECT SUM(amount) FROM fee_payments WHERE invoice_id = i.id) as paid_amount,
        (i.total_amount - i.discount - COALESCE((SELECT SUM(amount) FROM fee_payments WHERE invoice_id = i.id), 0)) as outstanding_amount,
        DATEDIFF(CURDATE(), i.due_date) as days_overdue
        FROM fee_invoices i
        INNER JOIN students s ON i.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE i.status = 'Unpaid' 
        AND i.due_date < CURDATE()
        AND (i.total_amount - i.discount - COALESCE((SELECT SUM(amount) FROM fee_payments WHERE invoice_id = i.id), 0)) > 0";

$params = [];
$types = '';

if (!empty($classFilter)) {
    $sql .= " AND s.current_class_id = ?";
    $params[] = $classFilter;
    $types .= 'i';
}

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " HAVING days_overdue >= ?
        ORDER BY days_overdue DESC, outstanding_amount DESC";

$params[] = $daysOverdue;
$types .= 'i';

$defaulters = fetchAll(executeQuery($sql, $types, $params));

// Calculate statistics
$statsSql = "SELECT 
    COUNT(DISTINCT i.student_id) as total_defaulters,
    COUNT(i.id) as total_invoices,
    SUM(i.total_amount - i.discount - COALESCE((SELECT SUM(amount) FROM fee_payments WHERE invoice_id = i.id), 0)) as total_outstanding
    FROM fee_invoices i
    INNER JOIN students s ON i.student_id = s.id
    WHERE i.status = 'Unpaid' 
    AND i.due_date < CURDATE()
    AND (i.total_amount - i.discount - COALESCE((SELECT SUM(amount) FROM fee_payments WHERE invoice_id = i.id), 0)) > 0";

$statsParams = [];
$statsTypes = '';

if (!hasRole(['Super Admin'])) {
    $statsSql .= " AND s.branch_id = ?";
    $statsParams[] = $currentUser['branch_id'];
    $statsTypes .= 'i';
}

$stats = fetchOne(executeQuery($statsSql, $statsTypes, $statsParams));

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
                            <button onclick="window.print()" class="btn btn-secondary no-print">
                                <i class="ri-printer-line"></i> Print
                            </button>
                            <button onclick="exportTableToExcel('defaultersTable', 'fee_defaulters')" class="btn btn-success ms-2 no-print">
                                <i class="ri-file-excel-line"></i> Export
                            </button>
                            <button onclick="sendReminders()" class="btn btn-warning ms-2 no-print">
                                <i class="ri-mail-send-line"></i> Send Reminders
                            </button>
                        </div>
                        <h4 class="page-title">Fee Defaulters</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-danger-lighten text-danger">
                                        <i class="ri-user-unfollow-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Defaulters</h5>
                                    <h2 class="mb-0"><?php echo $stats['total_defaulters'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-warning-lighten text-warning">
                                        <i class="ri-file-list-3-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Overdue Invoices</h5>
                                    <h2 class="mb-0"><?php echo $stats['total_invoices'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-danger-lighten text-danger">
                                        <i class="ri-money-dollar-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Outstanding</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($stats['total_outstanding'] ?? 0); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row no-print">
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
                                    <label class="form-label">Days Overdue (Minimum)</label>
                                    <select class="form-select" name="days">
                                        <option value="0" <?php echo ($daysOverdue == 0) ? 'selected' : ''; ?>>All Overdue</option>
                                        <option value="7" <?php echo ($daysOverdue == 7) ? 'selected' : ''; ?>>7+ Days</option>
                                        <option value="15" <?php echo ($daysOverdue == 15) ? 'selected' : ''; ?>>15+ Days</option>
                                        <option value="30" <?php echo ($daysOverdue == 30) ? 'selected' : ''; ?>>30+ Days</option>
                                        <option value="60" <?php echo ($daysOverdue == 60) ? 'selected' : ''; ?>>60+ Days</option>
                                        <option value="90" <?php echo ($daysOverdue == 90) ? 'selected' : ''; ?>>90+ Days</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Defaulters List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Fee Defaulters List</h4>
                            
                            <?php if (!empty($defaulters)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export" id="defaultersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Class</th>
                                            <th>Invoice No</th>
                                            <th>Total Amount</th>
                                            <th>Paid</th>
                                            <th>Outstanding</th>
                                            <th>Due Date</th>
                                            <th>Days Overdue</th>
                                            <th>Contact</th>
                                            <th class="no-print">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($defaulters as $defaulter): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($defaulter['first_name'] . ' ' . $defaulter['last_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($defaulter['student_id']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($defaulter['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($defaulter['invoice_no']); ?></td>
                                            <td><?php echo formatCurrency($defaulter['total_amount']); ?></td>
                                            <td><?php echo formatCurrency($defaulter['paid_amount'] ?? 0); ?></td>
                                            <td>
                                                <strong class="text-danger">
                                                    <?php echo formatCurrency($defaulter['outstanding_amount']); ?>
                                                </strong>
                                            </td>
                                            <td><?php echo formatDate($defaulter['due_date']); ?></td>
                                            <td>
                                                <?php
                                                $days = $defaulter['days_overdue'];
                                                $badgeClass = $days >= 90 ? 'danger' : ($days >= 60 ? 'warning' : 'info');
                                                ?>
                                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                                    <?php echo $days; ?> days
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($defaulter['phone']): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($defaulter['phone']); ?>" class="text-primary">
                                                        <i class="ri-phone-line"></i> <?php echo htmlspecialchars($defaulter['phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="no-print">
                                                <a href="<?php echo APP_URL; ?>modules/fees/invoices.php?invoice_id=<?php echo $defaulter['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="View Invoice">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <button onclick="sendReminder(<?php echo $defaulter['id']; ?>)" 
                                                        class="btn btn-sm btn-warning" title="Send Reminder">
                                                    <i class="ri-mail-send-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-success text-center">
                                <i class="ri-checkbox-circle-line font-24"></i>
                                <h5 class="mt-2">No Defaulters Found!</h5>
                                <p class="mb-0">All students have paid their fees on time.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Send reminder to single defaulter
function sendReminder(invoiceId) {
    Swal.fire({
        title: 'Send Reminder?',
        text: 'Send fee payment reminder to this student/parent?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Send Reminder!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/fees/send-reminder.php',
                type: 'POST',
                data: { invoice_id: invoiceId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reminder Sent!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
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

// Send reminders to all defaulters
function sendReminders() {
    Swal.fire({
        title: 'Send Reminders to All?',
        text: 'Send fee payment reminders to all defaulters?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Send All!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sending Reminders...',
                text: 'Please wait while we send reminders to all defaulters.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/fees/send-reminders-bulk.php',
                type: 'POST',
                data: { 
                    class_id: '<?php echo $classFilter; ?>',
                    days: '<?php echo $daysOverdue; ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reminders Sent!',
                            html: `Reminders sent to <strong>${response.count}</strong> defaulters.`,
                            timer: 3000,
                            showConfirmButton: false
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

