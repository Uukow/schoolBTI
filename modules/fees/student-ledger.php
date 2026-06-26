<?php
/**
 * Student Fee Ledger
 * 
 * Comprehensive fee ledger showing all transactions, balances, and payment history
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Student Fee Ledger';

// Get current session
$currentSession = getCurrentSession();

// Get student ID from query
$studentId = $_GET['student_id'] ?? 0;
$selectedMonth = $_GET['month'] ?? '';

// Get students for filter
$studentsSql = "SELECT s.id, s.student_id, s.first_name, s.last_name, c.class_name
                FROM students s
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.status = 'Active'";
if (!hasRole(['Super Admin'])) {
    $studentsSql .= " AND s.branch_id = ?";
    $students = fetchAll(executeQuery($studentsSql, 'i', [getCurrentUser()['branch_id']]));
} else {
    $students = fetchAll(executeQuery($studentsSql));
}

// Get student details if selected
$student = null;
$balance = null;
$ledger = [];
$monthlyAssignments = [];
$payments = [];
$advanceCredits = [];

if ($studentId) {
    // Get student details
    $studentSql = "SELECT s.*, c.class_name, b.branch_name
                    FROM students s
                    LEFT JOIN classes c ON s.current_class_id = c.id
                    LEFT JOIN branches b ON s.branch_id = b.id
                    WHERE s.id = ?";
    $student = fetchOne(executeQuery($studentSql, 'i', [$studentId]));
    
    if ($student) {
        // Get balance summary
        $balanceSql = "SELECT * FROM student_fee_balance 
                       WHERE student_id = ? AND session_id = ?";
        $balance = fetchOne(executeQuery($balanceSql, 'ii', [$studentId, $currentSession['id']]));
        
        if (!$balance) {
            // Initialize balance if doesn't exist
            $balance = [
                'total_assigned' => 0,
                'total_paid' => 0,
                'total_due' => 0,
                'advance_credit' => 0,
                'overdue_amount' => 0
            ];
        }
        
        // Get ledger entries
        $ledgerSql = "SELECT * FROM student_fee_ledger 
                      WHERE student_id = ? AND session_id = ?";
        $params = [$studentId, $currentSession['id']];
        $types = 'ii';
        
        if (!empty($selectedMonth)) {
            $ledgerSql .= " AND month = ?";
            $params[] = $selectedMonth;
            $types .= 's';
        }
        
        $ledgerSql .= " ORDER BY created_at DESC, id DESC";
        $ledger = fetchAll(executeQuery($ledgerSql, $types, $params));
        
        // Get monthly assignments
        $assignmentsSql = "SELECT mfa.*, ft.fee_name
                           FROM monthly_fee_assignments mfa
                           LEFT JOIN fee_types ft ON mfa.fee_type_id = ft.id
                           WHERE mfa.student_id = ? AND mfa.session_id = ?";
        $assignParams = [$studentId, $currentSession['id']];
        $assignTypes = 'ii';
        
        if (!empty($selectedMonth)) {
            $assignmentsSql .= " AND mfa.month = ?";
            $assignParams[] = $selectedMonth;
            $assignTypes .= 's';
        }
        
        $assignmentsSql .= " ORDER BY mfa.month DESC, mfa.assigned_at DESC";
        $monthlyAssignments = fetchAll(executeQuery($assignmentsSql, $assignTypes, $assignParams));
        
        // Get payments
        $paymentsSql = "SELECT fp.*, pa.allocation_type, pa.reference_id, pa.amount as allocated_amount
                        FROM fee_payments fp
                        LEFT JOIN payment_allocations pa ON fp.id = pa.payment_id
                        WHERE fp.student_id = ?";
        $paymentsSql .= " ORDER BY fp.payment_date DESC, fp.created_at DESC";
        $payments = fetchAll(executeQuery($paymentsSql, 'i', [$studentId]));
        
        // Get advance credits
        $advanceSql = "SELECT * FROM student_advance_credits 
                       WHERE student_id = ? AND session_id = ?
                       ORDER BY created_at DESC";
        $advanceCredits = fetchAll(executeQuery($advanceSql, 'ii', [$studentId, $currentSession['id']]));
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
                        <h4 class="page-title">Student Fee Ledger</h4>
                    </div>
                </div>
            </div>

            <!-- Student Selection -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label">Select Student</label>
                                    <select class="form-select" name="student_id" required onchange="this.form.submit()">
                                        <option value="">Choose a student...</option>
                                        <?php foreach ($students as $s): ?>
                                            <option value="<?php echo $s['id']; ?>" <?php echo ($studentId == $s['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($s['student_id'] . ' - ' . $s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['class_name'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($studentId): ?>
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Month</label>
                                    <input type="month" class="form-control" name="month" value="<?php echo htmlspecialchars($selectedMonth); ?>" onchange="this.form.submit()">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <a href="student-ledger.php?student_id=<?php echo $studentId; ?>" class="btn btn-secondary d-block">Clear Filter</a>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($student && $balance): ?>
            
            <!-- Balance Summary -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Assigned</h6>
                            <h3 class="mb-0 text-primary"><?php echo formatCurrency($balance['total_assigned']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Paid</h6>
                            <h3 class="mb-0 text-success"><?php echo formatCurrency($balance['total_paid']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Outstanding Balance</h6>
                            <h3 class="mb-0 text-danger"><?php echo formatCurrency($balance['total_due']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Advance Credit</h6>
                            <h3 class="mb-0 text-info"><?php echo formatCurrency($balance['advance_credit']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Information -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Student Information</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Student ID:</strong></p>
                                    <p><?php echo htmlspecialchars($student['student_id']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Name:</strong></p>
                                    <p><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Class:</strong></p>
                                    <p><?php echo htmlspecialchars($student['class_name']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Branch:</strong></p>
                                    <p><?php echo htmlspecialchars($student['branch_name'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Assignments -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Monthly Fee Assignments</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Month</th>
                                            <th>Fee Type</th>
                                            <th>Assigned Amount</th>
                                            <th>Paid Amount</th>
                                            <th>Due Amount</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($monthlyAssignments)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No monthly assignments found</td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($monthlyAssignments as $assignment): ?>
                                        <tr>
                                            <td><?php echo date('F Y', strtotime($assignment['month'] . '-01')); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['fee_name']); ?></td>
                                            <td><?php echo formatCurrency($assignment['assigned_amount']); ?></td>
                                            <td class="text-success"><?php echo formatCurrency($assignment['paid_amount']); ?></td>
                                            <td class="text-danger"><?php echo formatCurrency($assignment['due_amount']); ?></td>
                                            <td><?php echo $assignment['due_date'] ? formatDate($assignment['due_date']) : 'N/A'; ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                switch($assignment['status']) {
                                                    case 'Paid': $statusClass = 'success'; break;
                                                    case 'Partially Paid': $statusClass = 'warning'; break;
                                                    case 'Overdue': $statusClass = 'danger'; break;
                                                    case 'Assigned': $statusClass = 'info'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($assignment['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fee Ledger -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Fee Ledger (All Transactions)</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Month</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($ledger)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No ledger entries found</td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($ledger as $entry): ?>
                                        <tr>
                                            <td><?php echo formatDateTime($entry['created_at']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $entry['transaction_type'] == 'Payment' ? 'success' : 
                                                        ($entry['transaction_type'] == 'Assignment' ? 'info' : 'warning'); 
                                                ?>">
                                                    <?php echo htmlspecialchars($entry['transaction_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($entry['description'] ?? 'N/A'); ?></td>
                                            <td><?php echo $entry['month'] ? date('M Y', strtotime($entry['month'] . '-01')) : 'N/A'; ?></td>
                                            <td class="text-danger"><?php echo $entry['debit_amount'] > 0 ? formatCurrency($entry['debit_amount']) : '-'; ?></td>
                                            <td class="text-success"><?php echo $entry['credit_amount'] > 0 ? formatCurrency($entry['credit_amount']) : '-'; ?></td>
                                            <td><strong><?php echo formatCurrency($entry['balance']); ?></strong></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advance Credits -->
            <?php if (!empty($advanceCredits)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Advance Credits</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Allocated</th>
                                            <th>Available</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($advanceCredits as $credit): ?>
                                        <tr>
                                            <td><?php echo formatDate($credit['created_at']); ?></td>
                                            <td><?php echo formatCurrency($credit['amount']); ?></td>
                                            <td><?php echo formatCurrency($credit['allocated_amount']); ?></td>
                                            <td class="text-success"><strong><?php echo formatCurrency($credit['available_amount']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($credit['description'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

