<?php
/**
 * Staff Management
 * 
 * Manage teaching and non-teaching staff
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

hrRequirePage('hr_payroll', 'view');

$pageTitle = 'Staff Management';

// Get current user
$currentUser = getCurrentUser();

// Get filters
$designationFilter = $_GET['designation'] ?? '';
$statusFilter = $_GET['status'] ?? 'Active';

// Build query
$sql = "SELECT s.*, b.branch_name, u.username
        FROM staff s
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN users u ON s.user_id = u.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($designationFilter)) {
    $sql .= " AND s.designation = ?";
    $params[] = $designationFilter;
    $types .= 's';
}

if (!empty($statusFilter)) {
    $sql .= " AND s.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND s.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY s.first_name, s.last_name";

$stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
$staffList = fetchAll($stmt);

// Get unique designations
$designationSql = "SELECT DISTINCT designation FROM staff WHERE designation IS NOT NULL ORDER BY designation";
$designations = fetchAll(executeQuery($designationSql));

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN employment_type = 'Full Time' THEN 1 ELSE 0 END) as full_time,
    SUM(CASE WHEN designation LIKE '%Teacher%' THEN 1 ELSE 0 END) as teachers
    FROM staff WHERE 1=1";

if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
    $statsSql .= " AND branch_id = " . $currentUser['branch_id'];
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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                                <i class="ri-user-add-line"></i> Add Staff
                            </button>
                        </div>
                        <h4 class="page-title">Staff Management</h4>
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
                                        <i class="ri-user-settings-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Staff</h5>
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
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-checkbox-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Active</h5>
                                    <h2 class="mb-0"><?php echo $stats['active']; ?></h2>
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
                                        <i class="ri-time-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Full Time</h5>
                                    <h2 class="mb-0"><?php echo $stats['full_time']; ?></h2>
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
                                        <i class="ri-graduation-cap-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Teachers</h5>
                                    <h2 class="mb-0"><?php echo $stats['teachers']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Designation</label>
                                    <select name="designation" class="form-select">
                                        <option value="">All Designations</option>
                                        <?php foreach ($designations as $des): ?>
                                            <option value="<?php echo htmlspecialchars($des['designation']); ?>" 
                                                    <?php echo ($designationFilter == $des['designation']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($des['designation']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="Active" <?php echo ($statusFilter == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo ($statusFilter == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="Resigned" <?php echo ($statusFilter == 'Resigned') ? 'selected' : ''; ?>>Resigned</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Filter
                                    </button>
                                    <a href="staff.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">All Staff (<?php echo count($staffList); ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable-export">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Staff ID</th>
                                            <th>Name</th>
                                            <th>Designation</th>
                                            <th>Phone</th>
                                            <th>Joining Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($staffList as $staff): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($staff['photo'])): ?>
                                                    <img src="<?php echo APP_URL . $staff['photo']; ?>" 
                                                         alt="<?php echo htmlspecialchars($staff['first_name']); ?>" 
                                                         class="rounded-circle" width="40" height="40">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-info text-white d-inline-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <?php echo strtoupper(substr($staff['first_name'], 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($staff['staff_id']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo htmlspecialchars($staff['designation']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($staff['phone']); ?></td>
                                            <td><?php echo formatDate($staff['joining_date']); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = $staff['status'] == 'Active' ? 'success' : 'warning';
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($staff['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view-staff.php?id=<?php echo $staff['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit-staff.php?id=<?php echo $staff['id']; ?>" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-secondary btn-staff-docs"
                                                            title="Documents"
                                                            data-id="<?php echo (int)$staff['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name'], ENT_QUOTES); ?>"
                                                            data-code="<?php echo htmlspecialchars($staff['staff_id'], ENT_QUOTES); ?>">
                                                        <i class="ri-folder-upload-line"></i>
                                                    </button>
                                                    <button onclick="deleteStaff(<?php echo $staff['id']; ?>)" 
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

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addStaffForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Gender</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Designation</label>
                            <input type="text" class="form-control" name="designation" list="designationList" required>
                            <datalist id="designationList">
                                <option value="Principal">
                                <option value="Vice Principal">
                                <option value="Senior Teacher">
                                <option value="Teacher">
                                <option value="Assistant Teacher">
                                <option value="Accountant">
                                <option value="Librarian">
                                <option value="Receptionist">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Phone</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Branch</label>
                            <select class="form-select" name="branch_id" required>
                                <option value="">Select Branch</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo $branch['id']; ?>">
                                        <?php echo htmlspecialchars($branch['branch_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Joining Date</label>
                            <input type="date" class="form-control" name="joining_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Qualification</label>
                            <input type="text" class="form-control" name="qualification">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Experience (Years)</label>
                            <input type="number" class="form-control" name="experience_years" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Basic Salary</label>
                            <input type="number" class="form-control" name="basic_salary" step="0.01" min="0" required placeholder="Enter basic salary">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Staff Documents Modal -->
<div class="modal fade" id="staffDocsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-folder-upload-line"></i> Employee Documents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <strong id="docsStaffLabel">—</strong>
                    <span class="text-muted" id="docsStaffCode"></span>
                </div>

                <h6 class="mb-2">Existing Documents</h6>
                <div class="table-responsive mb-4" style="max-height:200px;overflow-y:auto">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>Type</th><th>Name</th><th>Expiry</th><th>File</th></tr>
                        </thead>
                        <tbody id="docsExistingBody">
                            <tr><td colspan="4" class="text-muted text-center">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Upload Documents</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addDocRowBtn">
                        <i class="ri-add-line"></i> Add Document
                    </button>
                </div>
                <form id="staffDocsForm" enctype="multipart/form-data">
                    <input type="hidden" name="staff_id" id="docsStaffId" value="">
                    <div id="docsUploadRows"></div>
                    <div id="docsUploadEmpty" class="text-center text-muted py-4 border rounded bg-light">
                        <i class="ri-file-upload-line font-24 d-block mb-2"></i>
                        No documents added. Click <strong>Add Document</strong> to attach files.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <a href="#" id="docsFullPageLink" class="btn btn-link me-auto">Open Documents Module</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="uploadStaffDocsBtn" disabled>
                    <i class="ri-upload-line"></i> Upload
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add staff
$('#addStaffForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/hr/add-staff.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addStaffModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr, status, error, xhr.responseText);
            let errorMsg = 'An error occurred. Please try again.';
            
            // Try to parse error response
            if (xhr.responseText) {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMsg = errorResponse.message;
                    }
                } catch (e) {
                    // If not JSON, check if it's a short error message
                    if (xhr.responseText.length < 200) {
                        errorMsg = xhr.responseText;
                    }
                }
            }
            
            showToast(errorMsg, 'error');
        }
    });
});

// Delete staff function
function deleteStaff(staffId) {
    confirmAction('Are you sure you want to delete this staff member? This action cannot be undone.', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/hr/delete-staff.php',
            type: 'POST',
            data: { id: staffId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Failed to delete staff', 'error');
            }
        });
    }, {
        title: 'Delete Staff?',
        icon: 'warning',
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: '#d33'
    });
}

// Staff documents modal
var staffDocsModal = null;
var DOC_TYPE_OPTIONS = ['ID Copy', 'Contract', 'Certificate', 'Medical', 'Resume', 'License', 'Passport', 'Other'];

function updateDocUploadUi() {
    var rows = document.querySelectorAll('#docsUploadRows .doc-upload-row');
    var empty = document.getElementById('docsUploadEmpty');
    var uploadBtn = document.getElementById('uploadStaffDocsBtn');
    empty.style.display = rows.length ? 'none' : 'block';
    var ready = false;
    rows.forEach(function(row) {
        var file = row.querySelector('input[type="file"]');
        if (file && file.files && file.files.length) ready = true;
    });
    uploadBtn.disabled = !ready;
}

function addDocUploadRow() {
    var container = document.getElementById('docsUploadRows');
    var row = document.createElement('div');
    row.className = 'doc-upload-row border rounded p-3 mb-2 bg-white';
    var options = DOC_TYPE_OPTIONS.map(function(t) {
        return '<option value="' + t + '">' + t + '</option>';
    }).join('');
    row.innerHTML =
        '<div class="d-flex justify-content-between align-items-start mb-2">' +
        '<span class="badge bg-light text-dark">New document</span>' +
        '<button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-doc" title="Remove">' +
        '<i class="ri-close-circle-line font-18"></i></button></div>' +
        '<div class="row g-2 align-items-end">' +
        '<div class="col-md-3"><label class="form-label small mb-1">Type</label>' +
        '<select name="doc_type[]" class="form-select form-select-sm doc-type-select">' + options + '</select></div>' +
        '<div class="col-md-3"><label class="form-label small mb-1">Name <span class="text-danger">*</span></label>' +
        '<input type="text" name="doc_name[]" class="form-control form-control-sm doc-name-input" placeholder="Document title"></div>' +
        '<div class="col-md-2"><label class="form-label small mb-1">Expiry</label>' +
        '<input type="date" name="doc_expiry[]" class="form-control form-control-sm"></div>' +
        '<div class="col-md-4"><label class="form-label small mb-1">File <span class="text-danger">*</span></label>' +
        '<input type="file" name="doc_file[]" class="form-control form-control-sm doc-file-input" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"></div>' +
        '</div>';
    container.appendChild(row);
    row.querySelector('.doc-type-select').addEventListener('change', function() {
        var nameInput = row.querySelector('.doc-name-input');
        if (!nameInput.value.trim()) nameInput.value = this.value;
    });
    row.querySelector('.doc-file-input').addEventListener('change', updateDocUploadUi);
    row.querySelector('.btn-remove-doc').addEventListener('click', function() {
        row.remove();
        updateDocUploadUi();
    });
    updateDocUploadUi();
    row.querySelector('.doc-name-input').focus();
}

function resetDocUploadRows() {
    document.getElementById('docsUploadRows').innerHTML = '';
    updateDocUploadUi();
}

function loadStaffDocuments(staffId) {
    var tbody = document.getElementById('docsExistingBody');
    tbody.innerHTML = '<tr><td colspan="4" class="text-muted text-center">Loading…</td></tr>';
    $.getJSON('<?php echo APP_URL; ?>ajax/hr/get-employee-documents.php?staff_id=' + staffId, function(res) {
        if (!res.success || !res.data || !res.data.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-muted text-center">No documents uploaded yet</td></tr>';
            return;
        }
        tbody.innerHTML = res.data.map(function(d) {
            var exp = d.expiry_date ? d.expiry_date.substring(0, 10) : '—';
            var link = d.file_path ? '<a href="<?php echo APP_URL; ?>' + d.file_path + '" target="_blank">View</a>' : '—';
            return '<tr><td>' + escapeHtml(d.document_type) + '</td><td>' + escapeHtml(d.document_name) + '</td><td>' + exp + '</td><td>' + link + '</td></tr>';
        }).join('');
    }).fail(function() {
        tbody.innerHTML = '<tr><td colspan="4" class="text-danger text-center">Failed to load documents</td></tr>';
    });
}

function escapeHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function openStaffDocuments(staffId, staffName, staffCode) {
    document.getElementById('docsStaffId').value = staffId;
    document.getElementById('docsStaffLabel').textContent = staffName;
    document.getElementById('docsStaffCode').textContent = ' (' + staffCode + ')';
    document.getElementById('docsFullPageLink').href = '<?php echo APP_URL; ?>modules/hr/employee-documents.php?staff_id=' + staffId;
    document.getElementById('staffDocsForm').reset();
    document.getElementById('docsStaffId').value = staffId;
    resetDocUploadRows();
    loadStaffDocuments(staffId);
    if (!staffDocsModal) {
        staffDocsModal = new bootstrap.Modal(document.getElementById('staffDocsModal'));
    }
    staffDocsModal.show();
}

$(document).on('click', '.btn-staff-docs', function() {
    openStaffDocuments($(this).data('id'), $(this).data('name'), $(this).data('code'));
});

$('#addDocRowBtn').on('click', function() {
    addDocUploadRow();
});

$('#uploadStaffDocsBtn').on('click', function() {
    var form = document.getElementById('staffDocsForm');
    var rows = form.querySelectorAll('.doc-upload-row');
    if (!rows.length) {
        showToast('Add at least one document', 'error');
        return;
    }
    var validRows = [];
    var missing = false;
    rows.forEach(function(row) {
        var file = row.querySelector('.doc-file-input');
        var name = row.querySelector('.doc-name-input');
        var type = row.querySelector('.doc-type-select');
        if (!file.files || !file.files.length) return;
        if (!name.value.trim()) {
            name.value = type.value || 'Document';
        }
        validRows.push(row);
    });
    if (!validRows.length) {
        showToast('Select a file for at least one document', 'error');
        return;
    }
    var btn = this;
    btn.disabled = true;
    var fd = new FormData();
    fd.append('staff_id', document.getElementById('docsStaffId').value);
    validRows.forEach(function(row) {
        fd.append('doc_type[]', row.querySelector('.doc-type-select').value);
        fd.append('doc_name[]', row.querySelector('.doc-name-input').value.trim());
        fd.append('doc_expiry[]', row.querySelector('input[name="doc_expiry[]"]').value);
        fd.append('doc_file[]', row.querySelector('.doc-file-input').files[0]);
    });
    fetch('<?php echo APP_URL; ?>ajax/hr/save-employee-documents-batch.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                showToast(res.message, 'success');
                resetDocUploadRows();
                loadStaffDocuments(document.getElementById('docsStaffId').value);
            } else {
                showToast(res.message || 'Upload failed', 'error');
                updateDocUploadUi();
            }
        })
        .catch(function() {
            showToast('Upload failed. Please try again.', 'error');
            updateDocUploadUi();
        });
});
</script>

