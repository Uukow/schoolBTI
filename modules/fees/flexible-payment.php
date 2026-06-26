<?php
/**
 * Flexible Payment Recording
 * 
 * Record payments with flexible allocation to multiple fee assignments
 * Supports partial payments, advance payments, and automatic allocation
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Flexible Payment Recording';

// Get current session
$currentSession = getCurrentSession();

// Get student ID from query
$studentId = $_GET['student_id'] ?? 0;

// Get students for selection
$studentsSql = "SELECT s.id, s.student_id, s.first_name, s.last_name, 
                       c.class_name, sec.section_name
                FROM students s
                LEFT JOIN classes c ON s.current_class_id = c.id
                LEFT JOIN sections sec ON s.current_section_id = sec.id
                WHERE s.status = 'Active'";
if (!hasRole(['Super Admin'])) {
    $studentsSql .= " AND s.branch_id = ?";
    $studentsSql .= " ORDER BY s.student_id ASC";
    $students = fetchAll(executeQuery($studentsSql, 'i', [getCurrentUser()['branch_id']]));
} else {
    $studentsSql .= " ORDER BY s.student_id ASC";
    $students = fetchAll(executeQuery($studentsSql));
}

// Get student details and pending fees if student selected
$student = null;
$pendingAssignments = [];
$advanceCredit = 0;
$totalDue = 0;

if ($studentId) {
    $studentSql = "SELECT s.*, c.class_name, b.branch_name
                    FROM students s
                    LEFT JOIN classes c ON s.current_class_id = c.id
                    LEFT JOIN branches b ON s.branch_id = b.id
                    WHERE s.id = ?";
    $student = fetchOne(executeQuery($studentSql, 'i', [$studentId]));
    
    if ($student) {
        // Get pending monthly assignments (oldest first)
        $pendingSql = "SELECT mfa.*, ft.fee_name
                       FROM monthly_fee_assignments mfa
                       LEFT JOIN fee_types ft ON mfa.fee_type_id = ft.id
                       WHERE mfa.student_id = ? AND mfa.session_id = ? AND mfa.due_amount > 0
                       ORDER BY mfa.month ASC, mfa.due_date ASC";
        $pendingAssignments = fetchAll(executeQuery($pendingSql, 'ii', [$studentId, $currentSession['id']]));
        
        // Get available advance credit
        $advanceSql = "SELECT COALESCE(SUM(available_amount), 0) as total_advance
                       FROM student_advance_credits
                       WHERE student_id = ? AND session_id = ?";
        $advanceResult = fetchOne(executeQuery($advanceSql, 'ii', [$studentId, $currentSession['id']]));
        $advanceCredit = $advanceResult['total_advance'] ?? 0;
        
        // Calculate total due amount
        $totalDue = 0;
        foreach ($pendingAssignments as $assignment) {
            $totalDue += (float)$assignment['due_amount'];
        }
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
                        <h4 class="page-title">Flexible Payment Recording</h4>
                    </div>
                </div>
            </div>

            <!-- Student Selection -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Select Student</label>
                                    <select class="form-select" name="student_id" id="studentSelect" required>
                                        <option value="">Choose a student...</option>
                                        <?php foreach ($students as $s): ?>
                                            <option value="<?php echo $s['id']; ?>" 
                                                    data-student-id="<?php echo htmlspecialchars($s['student_id']); ?>"
                                                    data-name="<?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?>"
                                                    data-class="<?php echo htmlspecialchars($s['class_name'] ?? ''); ?>"
                                                    <?php echo ($studentId == $s['id']) ? 'selected' : ''; ?>>
                                                <?php 
                                                $displayText = $s['student_id'] . ' - ' . $s['first_name'] . ' ' . $s['last_name'];
                                                if (!empty($s['class_name'])) {
                                                    $displayText .= ' (' . $s['class_name'];
                                                    if (!empty($s['section_name'])) {
                                                        $displayText .= ' - ' . $s['section_name'];
                                                    }
                                                    $displayText .= ')';
                                                }
                                                echo htmlspecialchars($displayText); 
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Type to search by student ID, name, or class</small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($student): ?>
            
            <!-- Student Information -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <p class="mb-1"><strong>Student ID:</strong></p>
                                    <p><?php echo htmlspecialchars($student['student_id']); ?></p>
                                </div>
                                <div class="col-md-2">
                                    <p class="mb-1"><strong>Name:</strong></p>
                                    <p><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                                </div>
                                <div class="col-md-2">
                                    <p class="mb-1"><strong>Class:</strong></p>
                                    <p><?php echo htmlspecialchars($student['class_name']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Total Amount Due:</strong></p>
                                    <p class="text-danger"><strong style="font-size: 1.2em;"><?php echo formatCurrency($totalDue ?? 0); ?></strong></p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Advance Credit:</strong></p>
                                    <p class="text-info"><strong><?php echo formatCurrency($advanceCredit); ?></strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Fees -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0">Pending Fee Assignments</h4>
                                <div>
                                    <span class="text-muted me-2">Total Due:</span>
                                    <span class="text-danger fw-bold" style="font-size: 1.1em;"><?php echo formatCurrency($totalDue ?? 0); ?></span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAllAssignments" onchange="toggleAllAssignments()">
                                            </th>
                                            <th>Month</th>
                                            <th>Fee Type</th>
                                            <th>Due Amount</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pendingAssignmentsTable">
                                        <?php if (empty($pendingAssignments)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No pending fees</td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($pendingAssignments as $assignment): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="assignment-checkbox" 
                                                       data-assignment-id="<?php echo $assignment['id']; ?>"
                                                       data-due-amount="<?php echo $assignment['due_amount']; ?>"
                                                       onchange="updatePaymentForm()">
                                            </td>
                                            <td><?php echo date('F Y', strtotime($assignment['month'] . '-01')); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['fee_name']); ?></td>
                                            <td class="text-danger"><strong><?php echo formatCurrency($assignment['due_amount']); ?></strong></td>
                                            <td><?php echo $assignment['due_date'] ? formatDate($assignment['due_date']) : 'N/A'; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $assignment['status'] == 'Overdue' ? 'danger' : 'warning'; ?>">
                                                    <?php echo htmlspecialchars($assignment['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <?php if (!empty($pendingAssignments)): ?>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total Amount Due:</strong></td>
                                            <td class="text-danger"><strong style="font-size: 1.1em;"><?php echo formatCurrency($totalDue ?? 0); ?></strong></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Record Payment</h4>
                            <form id="flexiblePaymentForm">
                                <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">
                                <input type="hidden" name="session_id" value="<?php echo $currentSession['id']; ?>">
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required">Payment Amount</label>
                                            <input type="number" class="form-control" name="amount" id="paymentAmount" 
                                                   step="0.01" min="0" required oninput="updatePaymentForm()">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required">Payment Method</label>
                                            <select class="form-select" name="payment_method" required>
                                                <option value="Cash">Cash</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                                <option value="Credit Card">Credit Card</option>
                                                <option value="Debit Card">Debit Card</option>
                                                <option value="Online">Online</option>
                                                <option value="EVC">EVC</option>
                                                <option value="Zaad">Zaad</option>
                                                <option value="Mobile Money">Mobile Money</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required">Payment Date</label>
                                            <input type="date" class="form-control" name="payment_date" 
                                                   value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Transaction ID</label>
                                            <input type="text" class="form-control" name="transaction_id">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Payment Type</label>
                                            <select class="form-select" name="payment_type" id="paymentType" onchange="updatePaymentForm()">
                                                <option value="normal">Normal Payment (Apply to selected fees)</option>
                                                <option value="advance">Advance Payment (Credit for future)</option>
                                                <option value="auto">Auto Allocate (Oldest fees first)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="2"></textarea>
                                </div>
                                
                                <!-- Allocation Summary -->
                                <div id="allocationSummary" class="alert alert-info" style="display: none;">
                                    <h6>Payment Allocation Summary:</h6>
                                    <div id="allocationDetails"></div>
                                    <div class="mt-2">
                                        <strong>Remaining Amount: <span id="remainingAmount" class="text-warning">0.00</span></strong>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-money-dollar-circle-line"></i> Record Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Initialize Select2 for student dropdown
$(document).ready(function() {
    $('#studentSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search by student ID, name, or class...',
        allowClear: true,
        width: '100%',
        matcher: function(params, data) {
            // If there is no search term, return all data
            if ($.trim(params.term) === '') {
                return data;
            }
            
            // Get search term
            const searchTerm = params.term.toLowerCase().trim();
            
            // Get option element to access data attributes
            const $option = $(data.element);
            const studentId = ($option.data('student-id') || '').toLowerCase();
            const name = ($option.data('name') || '').toLowerCase();
            const className = ($option.data('class') || '').toLowerCase();
            const text = (data.text || '').toLowerCase();
            
            // Check if search term matches student ID, name, or class
            if (studentId.includes(searchTerm) || 
                name.includes(searchTerm) || 
                className.includes(searchTerm) ||
                text.includes(searchTerm)) {
                return data;
            }
            
            // Return null if no match
            return null;
        }
    }).on('select2:select', function(e) {
        // Auto-submit form when student is selected
        const selectedValue = $(this).val();
        if (selectedValue && selectedValue !== '') {
            $(this).closest('form').submit();
        }
    });
    
    // Set selected value if student is already selected
    <?php if ($studentId): ?>
    $('#studentSelect').val('<?php echo $studentId; ?>').trigger('change');
    <?php endif; ?>
});

let pendingAssignments = <?php echo json_encode($pendingAssignments); ?>;
let advanceCredit = <?php echo $advanceCredit; ?>;

function toggleAllAssignments() {
    const selectAll = document.getElementById('selectAllAssignments');
    const checkboxes = document.querySelectorAll('.assignment-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updatePaymentForm();
}

function updatePaymentForm() {
    const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const paymentType = document.getElementById('paymentType').value;
    const summaryDiv = document.getElementById('allocationSummary');
    const detailsDiv = document.getElementById('allocationDetails');
    const remainingSpan = document.getElementById('remainingAmount');
    
    if (paymentAmount <= 0) {
        summaryDiv.style.display = 'none';
        return;
    }
    
    let allocated = 0;
    let details = '<ul class="mb-0">';
    
    if (paymentType === 'auto') {
        // Auto allocate to oldest fees first
        let remaining = paymentAmount;
        pendingAssignments.forEach(assignment => {
            if (remaining > 0 && assignment.due_amount > 0) {
                const allocation = Math.min(remaining, parseFloat(assignment.due_amount));
                allocated += allocation;
                remaining -= allocation;
                details += `<li>${assignment.month} - ${assignment.fee_name}: ${allocation.toFixed(2)}</li>`;
            }
        });
    } else if (paymentType === 'normal') {
        // Allocate to selected assignments
        const selected = document.querySelectorAll('.assignment-checkbox:checked');
        let remaining = paymentAmount;
        selected.forEach(checkbox => {
            const assignmentId = checkbox.dataset.assignmentId;
            const dueAmount = parseFloat(checkbox.dataset.dueAmount);
            const assignment = pendingAssignments.find(a => a.id == assignmentId);
            
            if (assignment && remaining > 0) {
                const allocation = Math.min(remaining, dueAmount);
                allocated += allocation;
                remaining -= allocation;
                details += `<li>${assignment.month} - ${assignment.fee_name}: ${allocation.toFixed(2)}</li>`;
            }
        });
    } else if (paymentType === 'advance') {
        details += '<li>Will be added as advance credit for future payments</li>';
    }
    
    details += '</ul>';
    
    const remaining = paymentAmount - allocated;
    
    if (paymentType !== 'advance' && allocated > 0) {
        detailsDiv.innerHTML = details;
        remainingSpan.textContent = remaining.toFixed(2);
        summaryDiv.style.display = 'block';
    } else if (paymentType === 'advance') {
        detailsDiv.innerHTML = details;
        remainingSpan.textContent = paymentAmount.toFixed(2);
        summaryDiv.style.display = 'block';
    } else {
        summaryDiv.style.display = 'none';
    }
}

// Record flexible payment
$('#flexiblePaymentForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    const paymentType = $('#paymentType').val();
    const selectedAssignments = [];
    
    if (paymentType === 'normal') {
        $('.assignment-checkbox:checked').each(function() {
            selectedAssignments.push($(this).data('assignment-id'));
        });
        
        if (selectedAssignments.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one fee assignment to pay'
            });
            return;
        }
    }
    
    const data = formData + '&payment_type=' + paymentType + '&assignment_ids=' + JSON.stringify(selectedAssignments);
    
    Swal.fire({
        title: 'Record Payment?',
        text: 'This will record the payment and update student balance.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, record payment!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/fees/record-flexible-payment.php',
                type: 'POST',
                data: data,
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
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Failed to record payment. Please try again.';
                    
                    // Try to parse error response
                    if (xhr.responseText) {
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse.message) {
                                errorMessage = errorResponse.message;
                            }
                        } catch (e) {
                            // If not JSON, use the raw response if short
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

