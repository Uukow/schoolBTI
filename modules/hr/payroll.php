<?php
/**
 * Payroll Management
 * 
 * Manage staff payroll and salary payments
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

hrRequirePage('hr_payroll', 'view', ['Accountant']);

$pageTitle = 'Payroll Management';

// Get current user
$currentUser = getCurrentUser();

// Get filters
$monthFilter = $_GET['month'] ?? date('Y-m');
$staffFilter = $_GET['staff_id'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Get salary payments
$sql = "SELECT sp.*, s.first_name, s.last_name, s.staff_id, s.designation,
        b.branch_name, u.username as processed_by_name
        FROM salary_payments sp
        INNER JOIN staff s ON sp.staff_id = s.id
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN users u ON sp.processed_by = u.id
        WHERE DATE_FORMAT(sp.payment_month, '%Y-%m') = ?";

$params = [$monthFilter];
$types = 's';

if (!empty($staffFilter)) {
    $sql .= " AND sp.staff_id = ?";
    $params[] = $staffFilter;
    $types .= 'i';
}

if (!empty($statusFilter)) {
    if ($statusFilter == 'Paid') {
        $sql .= " AND sp.payment_date IS NOT NULL";
    } else {
        $sql .= " AND sp.payment_date IS NULL";
    }
}

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY sp.created_at DESC";

$payments = fetchAll(executeQuery($sql, $types, $params));

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total_payments,
    SUM(CASE WHEN payment_date IS NOT NULL THEN 1 ELSE 0 END) as paid,
    SUM(CASE WHEN payment_date IS NULL THEN 1 ELSE 0 END) as pending,
    SUM(net_salary) as total_amount,
    SUM(CASE WHEN payment_date IS NOT NULL THEN net_salary ELSE 0 END) as paid_amount
    FROM salary_payments
    WHERE DATE_FORMAT(payment_month, '%Y-%m') = ?";

$statsParams = [$monthFilter];
$statsTypes = 's';

if (!hasRole(['Super Admin'])) {
    $statsSql .= " AND staff_id IN (SELECT id FROM staff WHERE branch_id = ?)";
    $statsParams[] = $currentUser['branch_id'];
    $statsTypes .= 'i';
}

$stats = fetchOne(executeQuery($statsSql, $statsTypes, $statsParams));

// Get active staff
$staffSql = "SELECT s.*, b.branch_name FROM staff s 
             LEFT JOIN branches b ON s.branch_id = b.id 
             WHERE s.status = 'Active' 
             ORDER BY s.first_name";
$staff = fetchAll(executeQuery($staffSql));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#processPayrollModal">
                                <i class="ri-add-line"></i> Process Payroll
                            </button>
                            <button onclick="window.print()" class="btn btn-secondary ms-2 no-print">
                                <i class="ri-printer-line"></i> Print
                            </button>
                            <button onclick="exportTableToExcel('payrollTable', 'payroll_<?php echo $monthFilter; ?>')" class="btn btn-success ms-2 no-print">
                                <i class="ri-file-excel-line"></i> Export
                            </button>
                        </div>
                        <h4 class="page-title">Payroll Management</h4>
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
                                    <h5 class="mt-0 mb-1 text-muted">Total Payments</h5>
                                    <h2 class="mb-0"><?php echo $stats['total_payments'] ?? 0; ?></h2>
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
                                        <i class="ri-check-double-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Paid</h5>
                                    <h2 class="mb-0"><?php echo $stats['paid'] ?? 0; ?></h2>
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
                                    <h5 class="mt-0 mb-1 text-muted">Pending</h5>
                                    <h2 class="mb-0"><?php echo $stats['pending'] ?? 0; ?></h2>
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
                                        <i class="ri-money-dollar-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Amount</h5>
                                    <h2 class="mb-0"><?php echo formatCurrency($stats['total_amount'] ?? 0); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Payment Month</label>
                                    <input type="month" class="form-control" name="month" value="<?php echo $monthFilter; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Staff</label>
                                    <select class="form-select" name="staff_id">
                                        <option value="">All Staff</option>
                                        <?php foreach ($staff as $s): ?>
                                            <option value="<?php echo $s['id']; ?>" <?php echo ($staffFilter == $s['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($s['staff_id'] . ' - ' . $s['first_name'] . ' ' . $s['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="Paid" <?php echo ($statusFilter == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="Pending" <?php echo ($statusFilter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payroll List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Salary Payments - <?php echo date('F Y', strtotime($monthFilter . '-01')); ?></h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export" id="payrollTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Staff</th>
                                            <th>Designation</th>
                                            <th>Basic Salary</th>
                                            <th>Allowances</th>
                                            <th>Deductions</th>
                                            <th>Net Salary</th>
                                            <th>Payment Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($payment['staff_id']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($payment['designation']); ?></td>
                                            <td><?php echo formatCurrency($payment['basic_salary']); ?></td>
                                            <td><?php echo formatCurrency($payment['allowances']); ?></td>
                                            <td><?php echo formatCurrency($payment['deductions']); ?></td>
                                            <td><strong><?php echo formatCurrency($payment['net_salary']); ?></strong></td>
                                            <td><?php echo $payment['payment_date'] ? formatDate($payment['payment_date']) : '-'; ?></td>
                                            <td>
                                                <?php if ($payment['payment_date']): ?>
                                                    <span class="badge bg-success">Paid</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button onclick="recordPayment(<?php echo $payment['id']; ?>)" 
                                                        class="btn btn-sm btn-success" title="Record Payment">
                                                    <i class="ri-money-dollar-circle-line"></i>
                                                </button>
                                                <button onclick="viewPayslip(<?php echo $payment['id']; ?>)" 
                                                        class="btn btn-sm btn-info" title="View Payslip">
                                                    <i class="ri-file-line"></i>
                                                </button>
                                                <button onclick="deletePayment(<?php echo $payment['id']; ?>)" 
                                                        class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
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

<!-- Process Payroll Modal -->
<div class="modal fade" id="processPayrollModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="processPayrollForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Payment Month</label>
                        <input type="month" class="form-control" name="payment_month" value="<?php echo date('Y-m'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Process Type</label>
                        <select class="form-select" name="process_type" id="processType" required>
                            <option value="specific">Specific Staff</option>
                            <option value="all">All Staff</option>
                        </select>
                    </div>
                    <div id="specificStaffSection">
                        <div class="mb-3">
                            <label class="form-label required">Select Staff</label>
                            <select class="form-select" name="staff_id" id="staffSelect">
                                <option value="">Choose Staff</option>
                                <?php foreach ($staff as $s): ?>
                                    <option value="<?php echo $s['id']; ?>" data-staff-id="<?php echo $s['id']; ?>">
                                        <?php echo htmlspecialchars($s['staff_id'] . ' - ' . $s['first_name'] . ' ' . $s['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Basic Salary</label>
                                <input type="number" class="form-control" name="basic_salary" id="basicSalary" step="0.01" min="0" readonly style="background-color: #f8f9fa;">
                                <small class="text-muted">Automatically loaded from staff payroll structure</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Allowances</label>
                                <input type="number" class="form-control" name="allowances" id="allowances" step="0.01" min="0" value="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Deductions</label>
                                <input type="number" class="form-control" name="deductions" id="deductions" step="0.01" min="0" value="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Net Salary</label>
                                <input type="number" class="form-control" name="net_salary" id="netSalary" step="0.01" min="0" readonly>
                            </div>
                        </div>
                    </div>
                    <div id="allStaffSection" style="display: none;">
                        <div class="alert alert-info">
                            <i class="ri-information-line"></i> Payroll will be processed for all active staff using their payroll structure. Staff without payroll structure will be skipped.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Process Payroll
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Toggle between all staff and specific staff
$('#processType').on('change', function() {
    const processType = $(this).val();
    if (processType === 'all') {
        $('#specificStaffSection').hide();
        $('#allStaffSection').show();
        $('#staffSelect, #basicSalary, #allowances, #deductions').removeAttr('required');
    } else {
        $('#specificStaffSection').show();
        $('#allStaffSection').hide();
        $('#staffSelect').attr('required', 'required');
    }
});

// Load staff salary when staff is selected
$('#staffSelect').on('change', function() {
    const staffId = $(this).val();
    if (staffId) {
        // Fetch staff salary from payroll structure
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/hr/get-staff-salary.php',
            type: 'POST',
            data: { staff_id: staffId },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    $('#basicSalary').val(response.data.basic_salary || 0);
                    // Recalculate net salary
                    calculateNetSalary();
                } else {
                    $('#basicSalary').val(0);
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Salary Found',
                        text: response.message || 'This staff member does not have a payroll structure. Please add salary first.',
                        timer: 3000
                    });
                }
            },
            error: function() {
                $('#basicSalary').val(0);
            }
        });
    } else {
        $('#basicSalary').val(0);
        $('#allowances').val(0);
        $('#deductions').val(0);
        $('#netSalary').val(0);
    }
});

// Calculate net salary
function calculateNetSalary() {
    const basic = parseFloat($('#basicSalary').val()) || 0;
    const allowances = parseFloat($('#allowances').val()) || 0;
    const deductions = parseFloat($('#deductions').val()) || 0;
    const net = basic + allowances - deductions;
    $('#netSalary').val(net.toFixed(2));
}

$('#allowances, #deductions').on('input', function() {
    calculateNetSalary();
});

// Process payroll
$('#processPayrollForm').on('submit', function(e) {
    e.preventDefault();
    
    const processType = $('#processType').val();
    const paymentMonth = $('input[name="payment_month"]').val();
    
    if (processType === 'all') {
        Swal.fire({
            title: 'Process Payroll for All Staff?',
            text: `This will process payroll for all active staff for ${paymentMonth}. Continue?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Process All!'
        }).then((result) => {
            if (result.isConfirmed) {
                processAllStaffPayroll();
            }
        });
    } else {
        // Validate specific staff form
        if (!$('#staffSelect').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select staff member.'
            });
            return;
        }
        
        const basicSalary = parseFloat($('#basicSalary').val()) || 0;
        if (basicSalary <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Selected staff does not have a salary configured. Please add salary first.'
            });
            return;
        }
        
        processSpecificStaffPayroll();
    }
});

function processSpecificStaffPayroll() {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/hr/process-payroll.php',
        type: 'POST',
        data: $('#processPayrollForm').serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
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

function processAllStaffPayroll() {
    Swal.fire({
        title: 'Processing...',
        html: 'Processing payroll for all staff. Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/hr/process-payroll.php',
        type: 'POST',
        data: $('#processPayrollForm').serialize() + '&process_type=all',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: response.message,
                    timer: 3000,
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
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while processing payroll.'
            });
        }
    });
}

// Record payment
function recordPayment(id) {
    Swal.fire({
        title: 'Record Payment',
        html: `
            <form id="paymentForm">
                <div class="mb-3 text-start">
                    <label class="form-label">Payment Date</label>
                    <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" name="payment_method" required>
                        <option value="">Select Method</option>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Mobile Money">Mobile Money</option>
                    </select>
                </div>
            </form>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Record Payment',
        confirmButtonColor: '#28a745',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const paymentDate = Swal.getPopup().querySelector('input[name="payment_date"]').value;
            const paymentMethod = Swal.getPopup().querySelector('select[name="payment_method"]').value;
            
            if (!paymentDate || !paymentMethod) {
                Swal.showValidationMessage('Please fill all fields.');
                return false;
            }
            return { payment_date: paymentDate, payment_method: paymentMethod };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/hr/record-payment.php',
                type: 'POST',
                data: {
                    id: id,
                    payment_date: result.value.payment_date,
                    payment_method: result.value.payment_method
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Recorded!',
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

// View payslip
function viewPayslip(id) {
    window.open('<?php echo APP_URL; ?>modules/hr/payslip.php?id=' + id, '_blank');
}

// Delete payment
function deletePayment(id) {
    Swal.fire({
        title: 'Delete Payment?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/hr/delete-payment.php',
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

