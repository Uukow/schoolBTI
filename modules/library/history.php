<?php
/**
 * Library History
 * 
 * View library transaction history
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Library History';

// Get current user and filters
$currentUser = getCurrentUser();
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$statusFilter = $_GET['status'] ?? '';
$studentFilter = $_GET['student_id'] ?? '';

// Build query
$sql = "SELECT li.*, b.book_title, b.book_code, b.author,
        s.student_id, s.first_name, s.last_name, c.class_name,
        DATEDIFF(COALESCE(li.return_date, CURDATE()), li.due_date) as days_overdue
        FROM library_issues li
        INNER JOIN library_books b ON li.book_id = b.id
        INNER JOIN students s ON li.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE DATE(li.created_at) BETWEEN ? AND ?";

$params = [$startDate, $endDate];
$types = 'ss';

if (!empty($statusFilter)) {
    $sql .= " AND li.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($studentFilter)) {
    $sql .= " AND li.student_id = ?";
    $params[] = $studentFilter;
    $types .= 'i';
}

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY li.created_at DESC";

$history = fetchAll(executeQuery($sql, $types, $params));

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total_transactions,
    SUM(CASE WHEN status = 'Issued' THEN 1 ELSE 0 END) as issued,
    SUM(CASE WHEN status = 'Returned' THEN 1 ELSE 0 END) as returned,
    SUM(CASE WHEN status = 'Overdue' THEN 1 ELSE 0 END) as overdue,
    SUM(fine_amount) as total_fines
    FROM library_issues
    WHERE DATE(created_at) BETWEEN ? AND ?";

$statsParams = [$startDate, $endDate];
$statsTypes = 'ss';

if (!hasRole(['Super Admin'])) {
    $statsSql .= " AND student_id IN (SELECT id FROM students WHERE branch_id = ?)";
    $statsParams[] = $currentUser['branch_id'];
    $statsTypes .= 'i';
}

$stats = fetchOne(executeQuery($statsSql, $statsTypes, $statsParams));

// Get students for filter
$studentsSql = "SELECT DISTINCT s.id, s.student_id, s.first_name, s.last_name 
                FROM students s
                INNER JOIN library_issues li ON s.id = li.student_id
                WHERE s.status = 'Active'
                ORDER BY s.first_name";
$students = fetchAll(executeQuery($studentsSql));

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
                            <button onclick="exportTableToExcel('historyTable', 'library_history')" class="btn btn-success ms-2 no-print">
                                <i class="ri-file-excel-line"></i> Export
                            </button>
                        </div>
                        <h4 class="page-title">Library Transaction History</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-file-list-3-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total</h5>
                                    <h2 class="mb-0"><?php echo $stats['total_transactions'] ?? 0; ?></h2>
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
                                        <i class="ri-book-open-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Issued</h5>
                                    <h2 class="mb-0"><?php echo $stats['issued'] ?? 0; ?></h2>
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
                                        <i class="ri-checkbox-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Returned</h5>
                                    <h2 class="mb-0"><?php echo $stats['returned'] ?? 0; ?></h2>
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
                                    <div class="stat-icon bg-danger-lighten text-danger">
                                        <i class="ri-money-dollar-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Fines</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($stats['total_fines'] ?? 0); ?></h2>
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
                                <div class="col-md-2">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="Issued" <?php echo ($statusFilter == 'Issued') ? 'selected' : ''; ?>>Issued</option>
                                        <option value="Returned" <?php echo ($statusFilter == 'Returned') ? 'selected' : ''; ?>>Returned</option>
                                        <option value="Overdue" <?php echo ($statusFilter == 'Overdue') ? 'selected' : ''; ?>>Overdue</option>
                                        <option value="Lost" <?php echo ($statusFilter == 'Lost') ? 'selected' : ''; ?>>Lost</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Student</label>
                                    <select class="form-select" name="student_id">
                                        <option value="">All Students</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>" <?php echo ($studentFilter == $student['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Transaction History</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export" id="historyTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Book</th>
                                            <th>Student</th>
                                            <th>Issue Date</th>
                                            <th>Due Date</th>
                                            <th>Return Date</th>
                                            <th>Status</th>
                                            <th>Fine</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history as $record): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($record['book_title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($record['book_code']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($record['student_id']); ?></small>
                                            </td>
                                            <td><?php echo formatDate($record['issue_date']); ?></td>
                                            <td><?php echo formatDate($record['due_date']); ?></td>
                                            <td><?php echo $record['return_date'] ? formatDate($record['return_date']) : '-'; ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                switch($record['status']) {
                                                    case 'Issued': $statusClass = 'info'; break;
                                                    case 'Returned': $statusClass = 'success'; break;
                                                    case 'Overdue': $statusClass = 'danger'; break;
                                                    case 'Lost': $statusClass = 'dark'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($record['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($record['fine_amount'] > 0): ?>
                                                    <strong class="text-danger"><?php echo formatCurrency($record['fine_amount']); ?></strong>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($record['remarks'] ?? '-'); ?></td>
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

<?php include '../../includes/footer.php'; ?>

