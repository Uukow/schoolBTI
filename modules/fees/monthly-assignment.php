<?php
/**
 * Monthly Tuition Fee Assignment
 * 
 * Automatically assign monthly tuition fees to all enrolled students
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Monthly Fee Assignment';

// Get current session
$currentSession = getCurrentSession();

// Get fee types (filter for Tuition Fee)
$feeTypesSql = "SELECT * FROM fee_types WHERE fee_name LIKE '%Tuition%' OR fee_code = 'TUITION' ORDER BY fee_name";
$feeTypes = fetchAll(executeQuery($feeTypesSql));

// Get classes
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get monthly fee structures
$sql = "SELECT fs.*, c.class_name, ft.fee_name, ft.fee_code
        FROM fee_structures fs
        LEFT JOIN classes c ON fs.class_id = c.id
        LEFT JOIN fee_types ft ON fs.fee_type_id = ft.id
        WHERE fs.session_id = ? AND fs.frequency = 'Monthly'
        ORDER BY c.class_order, ft.fee_name";
$monthlyStructures = fetchAll(executeQuery($sql, 'i', [$currentSession['id']]));

// Get recent assignments
$recentSql = "SELECT mfa.*, s.student_id, s.first_name, s.last_name, c.class_name, ft.fee_name
              FROM monthly_fee_assignments mfa
              INNER JOIN students s ON mfa.student_id = s.id
              LEFT JOIN classes c ON mfa.class_id = c.id
              LEFT JOIN fee_types ft ON mfa.fee_type_id = ft.id
              WHERE mfa.session_id = ?
              ORDER BY mfa.assigned_at DESC
              LIMIT 50";
$recentAssignments = fetchAll(executeQuery($recentSql, 'i', [$currentSession['id']]));

// Get statistics
$statsSql = "SELECT 
    COUNT(DISTINCT student_id) as total_students,
    COUNT(*) as total_assignments,
    COALESCE(SUM(assigned_amount), 0) as total_assigned,
    COALESCE(SUM(paid_amount), 0) as total_paid,
    COALESCE(SUM(due_amount), 0) as total_due
    FROM monthly_fee_assignments
    WHERE session_id = ?";
$stats = fetchOne(executeQuery($statsSql, 'i', [$currentSession['id']]));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignMonthlyFeesModal">
                                <i class="ri-calendar-line"></i> Assign Monthly Fees
                            </button>
                        </div>
                        <h4 class="page-title">Monthly Tuition Fee Assignment</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Students</h6>
                                    <h3 class="mb-0"><?php echo number_format($stats['total_students'] ?? 0); ?></h3>
                                </div>
                                <div class="avatar-sm bg-primary bg-opacity-10 rounded">
                                    <i class="ri-user-line fs-3 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Assignments</h6>
                                    <h3 class="mb-0"><?php echo number_format($stats['total_assignments'] ?? 0); ?></h3>
                                </div>
                                <div class="avatar-sm bg-info bg-opacity-10 rounded">
                                    <i class="ri-file-list-line fs-3 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Assigned</h6>
                                    <h3 class="mb-0"><?php echo formatCurrency($stats['total_assigned'] ?? 0); ?></h3>
                                </div>
                                <div class="avatar-sm bg-success bg-opacity-10 rounded">
                                    <i class="ri-money-dollar-circle-line fs-3 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Due</h6>
                                    <h3 class="mb-0"><?php echo formatCurrency($stats['total_due'] ?? 0); ?></h3>
                                </div>
                                <div class="avatar-sm bg-danger bg-opacity-10 rounded">
                                    <i class="ri-alert-line fs-3 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Fee Structures -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Monthly Fee Structures - <?php echo htmlspecialchars($currentSession['session_name']); ?></h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Class</th>
                                            <th>Fee Type</th>
                                            <th>Amount</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($monthlyStructures)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                No monthly fee structures defined. Please create fee structures with "Monthly" frequency.
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($monthlyStructures as $structure): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($structure['class_name']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($structure['fee_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($structure['fee_code']); ?></small>
                                            </td>
                                            <td><strong><?php echo formatCurrency($structure['amount']); ?></strong></td>
                                            <td><?php echo $structure['due_date'] ? formatDate($structure['due_date']) : 'N/A'; ?></td>
                                            <td>
                                                <span class="badge bg-success">Active</span>
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

            <!-- Recent Assignments -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Recent Assignments</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Class</th>
                                            <th>Fee Type</th>
                                            <th>Month</th>
                                            <th>Amount</th>
                                            <th>Paid</th>
                                            <th>Due</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentAssignments as $assignment): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($assignment['student_id']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($assignment['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['fee_name']); ?></td>
                                            <td><?php echo date('M Y', strtotime($assignment['month'] . '-01')); ?></td>
                                            <td><?php echo formatCurrency($assignment['assigned_amount']); ?></td>
                                            <td><?php echo formatCurrency($assignment['paid_amount']); ?></td>
                                            <td><?php echo formatCurrency($assignment['due_amount']); ?></td>
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
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<!-- Assign Monthly Fees Modal -->
<div class="modal fade" id="assignMonthlyFeesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Monthly Fees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignMonthlyFeesForm">
                <input type="hidden" name="session_id" value="<?php echo $currentSession['id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Month</label>
                        <input type="month" class="form-control" name="month" required 
                               value="<?php echo date('Y-m'); ?>" min="<?php echo date('Y-m', strtotime('-12 months')); ?>" 
                               max="<?php echo date('Y-m', strtotime('+12 months')); ?>">
                        <small class="text-muted">Select the month for which fees should be assigned</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Fee Type</label>
                        <select class="form-select" name="fee_type_id" id="feeTypeSelect" required>
                            <option value="">Select Fee Type</option>
                            <?php foreach ($feeTypes as $feeType): ?>
                                <option value="<?php echo $feeType['id']; ?>">
                                    <?php echo htmlspecialchars($feeType['fee_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Class Filter</label>
                        <select class="form-select" name="class_id" id="classFilterSelect">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Leave blank to assign to all enrolled students</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date">
                        <small class="text-muted">Optional: Set a due date for the assigned fees</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line"></i> 
                        <strong>Note:</strong> This will automatically assign fees to all enrolled students based on their class fee structure. 
                        Students already assigned for this month will be skipped.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-calendar-line"></i> Assign Fees
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Assign monthly fees
$('#assignMonthlyFeesForm').on('submit', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: 'Assign Monthly Fees?',
        text: 'This will assign fees to all enrolled students. This action may take a few moments.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, assign fees!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: 'Assigning fees to students. Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/fees/assign-monthly-fees.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: response.message,
                            timer: 3000,
                            showConfirmButton: true
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            html: response.message || 'Failed to assign monthly fees. Please try again.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Failed to assign monthly fees. Please try again.';
                    
                    // Try to parse error response
                    if (xhr.responseText) {
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse.message) {
                                errorMessage = errorResponse.message;
                            }
                        } catch (e) {
                            // If not JSON, use the raw response
                            if (xhr.responseText.length < 200) {
                                errorMessage = xhr.responseText;
                            }
                        }
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage + '<br><small>Check browser console for details.</small>'
                    });
                    
                    console.error('AJAX Error:', {xhr, status, error, response: xhr.responseText});
                }
            });
        }
    });
});
</script>

