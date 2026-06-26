<?php
/**
 * Fee Structure Management
 * 
 * Manage fee structures for classes
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Accountant']);

$pageTitle = 'Fee Structure';

// Get current session
$currentSession = getCurrentSession();

// Get classes
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get fee types
$feeTypesSql = "SELECT * FROM fee_types ORDER BY fee_name";
$feeTypes = fetchAll(executeQuery($feeTypesSql));

// Get fee structures
$sql = "SELECT fs.*, c.class_name, ft.fee_name, ft.fee_code, s.session_name
        FROM fee_structures fs
        LEFT JOIN classes c ON fs.class_id = c.id
        LEFT JOIN fee_types ft ON fs.fee_type_id = ft.id
        LEFT JOIN academic_sessions s ON fs.session_id = s.id
        WHERE fs.session_id = ?
        ORDER BY c.class_order, ft.fee_name";
$structures = fetchAll(executeQuery($sql, 'i', [$currentSession['id']]));

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStructureModal">
                                <i class="ri-add-line"></i> Add Fee Structure
                            </button>
                        </div>
                        <h4 class="page-title">Fee Structure Management</h4>
                    </div>
                </div>
            </div>

            <!-- Fee Structures List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Fee Structures - <?php echo htmlspecialchars($currentSession['session_name']); ?></h4>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable-export">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Class</th>
                                            <th>Fee Type</th>
                                            <th>Amount</th>
                                            <th>Frequency</th>
                                            <th>Due Date</th>
                                            <th>Mandatory</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($structures as $structure): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($structure['class_name']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($structure['fee_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($structure['fee_code']); ?></small>
                                            </td>
                                            <td><strong><?php echo formatCurrency($structure['amount']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($structure['frequency']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $structure['due_date'] ? formatDate($structure['due_date']) : 'N/A'; ?></td>
                                            <td>
                                                <?php if ($structure['is_mandatory']): ?>
                                                    <span class="badge bg-success">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="viewStructureDetails(<?php echo $structure['id']; ?>)" 
                                                            class="btn btn-sm btn-primary" title="View Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button onclick="editStructure(<?php echo $structure['id']; ?>)" 
                                                            class="btn btn-sm btn-info" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button onclick="deleteStructure(<?php echo $structure['id']; ?>)" 
                                                            class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
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

<!-- Add Structure Modal -->
<div class="modal fade" id="addStructureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Fee Structure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addStructureForm">
                <input type="hidden" name="session_id" value="<?php echo $currentSession['id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Class</label>
                        <select class="form-select" name="class_id" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Fee Type</label>
                        <select class="form-select" name="fee_type_id" required>
                            <option value="">Select Fee Type</option>
                            <?php foreach ($feeTypes as $feeType): ?>
                                <option value="<?php echo $feeType['id']; ?>">
                                    <?php echo htmlspecialchars($feeType['fee_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Amount</label>
                        <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Frequency</label>
                        <select class="form-select" name="frequency" required>
                            <option value="One Time">One Time</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Annually">Annually</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_mandatory" value="1" id="isMandatory" checked>
                            <label class="form-check-label" for="isMandatory">
                                Mandatory Fee
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Structure
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Structure Details Modal -->
<div class="modal fade" id="viewStructureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fee Structure Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="viewStructureContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Structure Modal -->
<div class="modal fade" id="editStructureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Fee Structure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editStructureForm">
                <input type="hidden" name="id" id="editStructureId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Class</label>
                        <select class="form-select" name="class_id" id="editClassId" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Fee Type</label>
                        <select class="form-select" name="fee_type_id" id="editFeeTypeId" required>
                            <option value="">Select Fee Type</option>
                            <?php foreach ($feeTypes as $feeType): ?>
                                <option value="<?php echo $feeType['id']; ?>">
                                    <?php echo htmlspecialchars($feeType['fee_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Amount</label>
                        <input type="number" class="form-control" name="amount" id="editAmount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Frequency</label>
                        <select class="form-select" name="frequency" id="editFrequency" required>
                            <option value="One Time">One Time</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Annually">Annually</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date" id="editDueDate">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_mandatory" value="1" id="editIsMandatory">
                            <label class="form-check-label" for="editIsMandatory">
                                Mandatory Fee
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Update Structure
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add structure
$('#addStructureForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/add-structure.php',
        type: 'POST',
        data: $(this).serialize(),
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
});

// Delete structure
function deleteStructure(id) {
    Swal.fire({
        title: 'Delete Fee Structure?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/fees/delete-structure.php',
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

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}


// Helper function to format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// View structure details
function viewStructureDetails(id) {
    $('#viewStructureContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    const viewModal = new bootstrap.Modal(document.getElementById('viewStructureModal'));
    viewModal.show();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/get-structure-details.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const structure = response.data;
                
                let html = '<div class="row">';
                
                // Fee Structure Information
                html += '<div class="col-md-12 mb-4">';
                html += '<h5 class="mb-3"><i class="ri-information-line"></i> Fee Structure Information</h5>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered">';
                html += '<tr><th width="35%">Class</th><td><strong>' + escapeHtml(structure.class_name || 'N/A') + '</strong>';
                if (structure.class_code) {
                    html += '<br><small class="text-muted">Code: ' + escapeHtml(structure.class_code) + '</small>';
                }
                html += '</td></tr>';
                html += '<tr><th>Fee Type</th><td><strong>' + escapeHtml(structure.fee_name || 'N/A') + '</strong>';
                if (structure.fee_code) {
                    html += '<br><small class="text-muted">Code: ' + escapeHtml(structure.fee_code) + '</small>';
                }
                html += '</td></tr>';
                html += '<tr><th>Amount</th><td><strong class="text-primary fs-5"><?php echo CURRENCY_SYMBOL ?? ""; ?>' + parseFloat(structure.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></td></tr>';
                html += '<tr><th>Frequency</th><td><span class="badge bg-info">' + escapeHtml(structure.frequency) + '</span></td></tr>';
                html += '<tr><th>Due Date</th><td>' + (structure.due_date ? formatDate(structure.due_date) : '<em class="text-muted">Not set</em>') + '</td></tr>';
                html += '<tr><th>Mandatory Fee</th><td>' + (structure.is_mandatory == 1 ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>') + '</td></tr>';
                html += '<tr><th>Academic Session</th><td>' + escapeHtml(structure.session_name || 'N/A') + '</td></tr>';
                html += '</table>';
                html += '</div>';
                html += '</div>';
                
                html += '</div>';
                
                // Action buttons
                html += '<div class="row mt-3">';
                html += '<div class="col-12 text-center">';
                html += '<button onclick="$(\'#viewStructureModal\').modal(\'hide\'); setTimeout(function() { editStructure(' + id + '); }, 300);" class="btn btn-info me-2">';
                html += '<i class="ri-edit-line"></i> Edit Structure';
                html += '</button>';
                html += '</div>';
                html += '</div>';
                
                $('#viewStructureContent').html(html);
            } else {
                $('#viewStructureContent').html('<div class="alert alert-danger">' + escapeHtml(response.message) + '</div>');
            }
        },
        error: function() {
            $('#viewStructureContent').html('<div class="alert alert-danger">Failed to load fee structure details</div>');
        }
    });
}

// Edit structure
function editStructure(id) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/get-structure.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const structure = response.data;
                $('#editStructureId').val(structure.id);
                $('#editClassId').val(structure.class_id);
                $('#editFeeTypeId').val(structure.fee_type_id);
                $('#editAmount').val(structure.amount);
                $('#editFrequency').val(structure.frequency);
                $('#editDueDate').val(structure.due_date || '');
                $('#editIsMandatory').prop('checked', structure.is_mandatory == 1);
                
                const editModal = new bootstrap.Modal(document.getElementById('editStructureModal'));
                editModal.show();
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
                text: 'Failed to load fee structure details'
            });
        }
    });
}

// Update structure
$('#editStructureForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/update-structure.php',
        type: 'POST',
        data: $(this).serialize(),
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
});
</script>

