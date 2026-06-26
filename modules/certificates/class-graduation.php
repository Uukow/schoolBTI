<?php
/**
 * Class Graduation & Certificate Issuance
 * 
 * Bulk graduation management with requirements verification and automatic certificate generation
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Class Graduation';

$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$branchId = $isSuperAdmin ? ($_GET['branch_id'] ?? null) : $currentUser['branch_id'];

// Get classes (excluding graduated classes - this is for certificate generation, not graduation management)
$classesSql = "SELECT c.* FROM classes c 
                WHERE c.is_active = 1 
                AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')";
if ($branchId) {
    $classesSql .= " AND c.branch_id = " . intval($branchId);
}
$classesSql .= " ORDER BY c.class_order DESC, c.class_name";
$classes = fetchAll(executeQuery($classesSql));

// Get academic sessions
$sessionsSql = "SELECT * FROM academic_sessions ORDER BY start_date DESC";
$sessions = fetchAll(executeQuery($sessionsSql));

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Get graduation certificate templates
$templatesSql = "SELECT * FROM certificate_templates 
                 WHERE is_active = 1 AND certificate_type = 'graduation'";
if ($branchId) {
    $templatesSql .= " AND (branch_id = " . intval($branchId) . " OR branch_id IS NULL)";
}
$templatesSql .= " ORDER BY is_default DESC, template_name";
$templates = fetchAll(executeQuery($templatesSql));

// Get grading schemes
$schemesSql = "SELECT * FROM grading_schemes WHERE is_active = 1";
if ($branchId) {
    $schemesSql .= " AND (branch_id = " . intval($branchId) . " OR branch_id IS NULL)";
}
$schemesSql .= " ORDER BY is_default DESC, scheme_name";
$gradingSchemes = fetchAll(executeQuery($schemesSql));

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<style>
.graduation-requirements {
    background: #f8f9fa;
    border-left: 4px solid #0d6efd;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.requirement-check {
    display: flex;
    align-items: center;
    margin: 8px 0;
}

.requirement-check i {
    margin-right: 10px;
    font-size: 18px;
}

.requirement-check.passed i {
    color: #28a745;
}

.requirement-check.failed i {
    color: #dc3545;
}

.requirement-check.warning i {
    color: #ffc107;
}

.student-status-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    transition: all 0.3s;
}

.student-status-card.eligible {
    border-color: #28a745;
    background: #f8fff9;
}

.student-status-card.not-eligible {
    border-color: #dc3545;
    background: #fff8f8;
}

.student-status-card.pending {
    border-color: #ffc107;
    background: #fffef8;
}

.stats-card {
    text-align: center;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.stats-card.total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.stats-card.eligible { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
.stats-card.not-eligible { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); color: white; }
.stats-card.pending { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }

.stats-card h3 {
    font-size: 36px;
    font-weight: bold;
    margin: 0;
}

.stats-card p {
    margin: 5px 0 0 0;
    opacity: 0.9;
}
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">
                            <i class="ri-graduation-cap-line"></i> Class Graduation & Certificate Issuance
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Selection Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Select Class for Graduation</h4>
                            
                            <form id="graduationForm" class="row g-3">
                                <?php if ($isSuperAdmin): ?>
                                <div class="col-md-3">
                                    <label class="form-label">Branch</label>
                                    <select class="form-select" name="branch_id" id="branchSelect">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" <?php echo ($branchId == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <div class="col-md-<?php echo $isSuperAdmin ? '4' : '5'; ?>">
                                    <label class="form-label required">Class</label>
                                    <select class="form-select" name="class_id" id="classSelect" required>
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>">
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-<?php echo $isSuperAdmin ? '5' : '7'; ?>">
                                    <label class="form-label required">Academic Session</label>
                                    <select class="form-select" name="session_id" id="sessionSelect" required>
                                        <option value="">Select Session</option>
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo $session['id']; ?>" <?php echo $session['is_active'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" onclick="loadGraduationData()">
                                        <i class="ri-search-line"></i> Load Students & Verify Requirements
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graduation Requirements Configuration -->
            <div class="row" id="requirementsConfig" style="display:none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="ri-settings-3-line"></i> Graduation Requirements</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Minimum GPA/CGPA</label>
                                    <input type="number" step="0.01" class="form-control" id="minGpa" value="2.00" min="0" max="4.00">
                                    <small class="text-muted">Default: 2.00</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Minimum Attendance %</label>
                                    <input type="number" step="0.01" class="form-control" id="minAttendance" value="75.00" min="0" max="100">
                                    <small class="text-muted">Default: 75%</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Required Subjects Completion</label>
                                    <input type="number" class="form-control" id="requiredSubjects" value="0" min="0">
                                    <small class="text-muted">0 = All subjects required</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Minimum Passing Percentage</label>
                                    <input type="number" step="0.01" class="form-control" id="minPassing" value="50.00" min="0" max="100">
                                    <small class="text-muted">Per subject</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Dashboard -->
            <div class="row" id="statsDashboard" style="display:none;">
                <div class="col-md-3">
                    <div class="stats-card total">
                        <h3 id="totalStudents">0</h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card eligible">
                        <h3 id="eligibleStudents">0</h3>
                        <p>Eligible</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card not-eligible">
                        <h3 id="notEligibleStudents">0</h3>
                        <p>Not Eligible</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card pending">
                        <h3 id="pendingStudents">0</h3>
                        <p>Pending Review</p>
                    </div>
                </div>
            </div>

            <!-- Students Verification List -->
            <div class="row" id="studentsListContainer" style="display:none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="ri-user-list-line"></i> Student Graduation Status</h5>
                            <div>
                                <button class="btn btn-sm btn-success" onclick="selectEligible()">
                                    <i class="ri-checkbox-circle-line"></i> Select All Eligible
                                </button>
                                <button class="btn btn-sm btn-secondary" onclick="deselectAll()">
                                    <i class="ri-checkbox-blank-line"></i> Deselect All
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="studentsList"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificate Configuration -->
            <div class="row" id="certificateConfig" style="display:none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="ri-file-certificate-line"></i> Certificate Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form id="certificateConfigForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Certificate Template</label>
                                        <select class="form-select" name="template_id" id="templateSelect" required>
                                            <option value="">Select Template</option>
                                            <?php foreach ($templates as $template): ?>
                                                <option value="<?php echo $template['id']; ?>" <?php echo $template['is_default'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($template['template_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (empty($templates)): ?>
                                            <small class="text-danger">No graduation certificate templates found. Please create one first.</small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Grading Scheme</label>
                                        <select class="form-select" name="grading_scheme_id" id="gradingSchemeSelect" required>
                                            <option value="">Select Scheme</option>
                                            <?php foreach ($gradingSchemes as $scheme): ?>
                                                <option value="<?php echo $scheme['id']; ?>" <?php echo $scheme['is_default'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($scheme['scheme_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Issue Date</label>
                                        <input type="date" class="form-control" name="issue_date" id="issueDate" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Valid Until</label>
                                        <input type="date" class="form-control" name="valid_until" id="validUntil">
                                        <small class="text-muted">Leave blank for no expiration</small>
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Remarks</label>
                                        <textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks for certificates"></textarea>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="ri-information-line"></i> 
                                            <strong>Selected Students:</strong> <span id="selectedCount">0</span> student(s) will be graduated and receive certificates.
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="button" class="btn btn-success btn-lg" onclick="processGraduation()" id="processBtn">
                                            <i class="ri-graduation-cap-line"></i> Process Graduation & Generate Certificates
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="resetGraduation()">
                                            <i class="ri-refresh-line"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="row" id="graduationResults" style="display:none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body" id="resultsContent">
                            <!-- Results will be displayed here -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
let studentsData = [];
let selectedStudentIds = [];

function loadGraduationData() {
    const classId = $('#classSelect').val();
    const sessionId = $('#sessionSelect').val();
    
    if (!classId || !sessionId) {
        Swal.fire('Error', 'Please select both class and session', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Loading...',
        text: 'Verifying graduation requirements for all students',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/certificates/verify-graduation-requirements.php',
        type: 'POST',
        data: {
            class_id: classId,
            session_id: sessionId,
            branch_id: $('#branchSelect').val() || '',
            min_gpa: $('#minGpa').val() || 2.00,
            min_attendance: $('#minAttendance').val() || 75.00,
            required_subjects: $('#requiredSubjects').val() || 0,
            min_passing: $('#minPassing').val() || 50.00
        },
        dataType: 'json',
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                studentsData = response.data.students;
                displayStudentsList(studentsData);
                updateStatistics(studentsData);
                $('#requirementsConfig').show();
                $('#statsDashboard').show();
                $('#studentsListContainer').show();
                $('#certificateConfig').show();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.close();
            Swal.fire('Error', 'Failed to load graduation data', 'error');
        }
    });
}

function displayStudentsList(students) {
    let html = '';
    
    students.forEach(function(student, index) {
        const statusClass = student.eligibility_status === 'eligible' ? 'eligible' : 
                          student.eligibility_status === 'not_eligible' ? 'not-eligible' : 'pending';
        
        html += `<div class="student-status-card ${statusClass}" data-student-id="${student.id}">`;
        html += '<div class="row align-items-center">';
        html += '<div class="col-md-1">';
        html += `<input type="checkbox" class="form-check-input student-checkbox" 
                       value="${student.id}" 
                       data-eligible="${student.eligibility_status === 'eligible' ? '1' : '0'}"
                       ${student.eligibility_status === 'eligible' ? 'checked' : ''}
                       onchange="updateSelectedCount()">`;
        html += '</div>';
        html += '<div class="col-md-3">';
        html += `<strong>${escapeHtml(student.student_id)}</strong><br>`;
        html += `<span class="text-muted">${escapeHtml(student.full_name)}</span>`;
        html += '</div>';
        html += '<div class="col-md-4">';
        html += '<div class="requirement-check ' + (student.requirements.gpa_met ? 'passed' : 'failed') + '">';
        html += '<i class="ri-' + (student.requirements.gpa_met ? 'check-line' : 'close-line') + '"></i>';
        html += `<span>GPA/CGPA: <strong>${student.gpa || 'N/A'}</strong> (Required: ${student.requirements.min_gpa})</span>`;
        html += '</div>';
        html += '<div class="requirement-check ' + (student.requirements.attendance_met ? 'passed' : 'failed') + '">';
        html += '<i class="ri-' + (student.requirements.attendance_met ? 'check-line' : 'close-line') + '"></i>';
        html += `<span>Attendance: <strong>${student.attendance_percentage || 'N/A'}%</strong> (Required: ${student.requirements.min_attendance}%)</span>`;
        html += '</div>';
        html += '<div class="requirement-check ' + (student.requirements.subjects_met ? 'passed' : 'failed') + '">';
        html += '<i class="ri-' + (student.requirements.subjects_met ? 'check-line' : 'close-line') + '"></i>';
        html += `<span>Subjects Completed: <strong>${student.completed_subjects}/${student.total_subjects}</strong></span>`;
        html += '</div>';
        html += '</div>';
        html += '<div class="col-md-2">';
        html += `<span class="badge bg-${student.eligibility_status === 'eligible' ? 'success' : student.eligibility_status === 'not_eligible' ? 'danger' : 'warning'}">`;
        html += student.eligibility_status === 'eligible' ? 'Eligible' : 
                student.eligibility_status === 'not_eligible' ? 'Not Eligible' : 'Pending';
        html += '</span>';
        html += '</div>';
        html += '<div class="col-md-2 text-end">';
        if (student.has_existing_certificate) {
            html += '<small class="text-info"><i class="ri-file-certificate-line"></i> Certificate Exists</small>';
        }
        html += '</div>';
        html += '</div>';
        html += '</div>';
    });
    
    $('#studentsList').html(html);
    updateSelectedCount();
}

function updateStatistics(students) {
    const total = students.length;
    const eligible = students.filter(s => s.eligibility_status === 'eligible').length;
    const notEligible = students.filter(s => s.eligibility_status === 'not_eligible').length;
    const pending = students.filter(s => s.eligibility_status === 'pending').length;
    
    $('#totalStudents').text(total);
    $('#eligibleStudents').text(eligible);
    $('#notEligibleStudents').text(notEligible);
    $('#pendingStudents').text(pending);
}

function selectEligible() {
    $('.student-checkbox[data-eligible="1"]').prop('checked', true);
    updateSelectedCount();
}

function deselectAll() {
    $('.student-checkbox').prop('checked', false);
    updateSelectedCount();
}

function updateSelectedCount() {
    selectedStudentIds = [];
    $('.student-checkbox:checked').each(function() {
        selectedStudentIds.push($(this).val());
    });
    $('#selectedCount').text(selectedStudentIds.length);
}

function processGraduation() {
    if (selectedStudentIds.length === 0) {
        Swal.fire('Error', 'Please select at least one student to graduate', 'error');
        return;
    }
    
    const templateId = $('#templateSelect').val();
    const gradingSchemeId = $('#gradingSchemeSelect').val();
    
    if (!templateId || !gradingSchemeId) {
        Swal.fire('Error', 'Please select certificate template and grading scheme', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Process Graduation?',
        html: `This will:<br>
               • Update ${selectedStudentIds.length} student(s) status to "Graduated"<br>
               • Generate graduation certificates for all selected students<br>
               • Register certificates in the system<br><br>
               <strong>This action cannot be undone!</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Process Graduation',
        confirmButtonColor: '#28a745',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            executeGraduation();
        }
    });
}

function executeGraduation() {
    $('#processBtn').prop('disabled', true);
    
    Swal.fire({
        title: 'Processing Graduation...',
        html: 'Updating student statuses and generating certificates. Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const formData = $('#certificateConfigForm').serialize();
    const classId = $('#classSelect').val();
    const sessionId = $('#sessionSelect').val();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/certificates/process-class-graduation.php',
        type: 'POST',
        data: formData + '&class_id=' + classId + '&session_id=' + sessionId + '&student_ids=' + JSON.stringify(selectedStudentIds),
        dataType: 'json',
        success: function(response) {
            $('#processBtn').prop('disabled', false);
            Swal.close();
            
            if (response.success) {
                displayGraduationResults(response.data);
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            $('#processBtn').prop('disabled', false);
            Swal.close();
            Swal.fire('Error', 'Failed to process graduation', 'error');
        }
    });
}

function displayGraduationResults(data) {
    let html = '<div class="alert alert-success">';
    html += '<h5><i class="ri-check-line"></i> Graduation Processed Successfully!</h5>';
    html += `<p><strong>${data.graduated_count}</strong> student(s) graduated and <strong>${data.certificates_generated}</strong> certificate(s) generated.</p>`;
    html += '</div>';
    
    html += '<div class="table-responsive mt-4">';
    html += '<table class="table table-bordered table-hover">';
    html += '<thead class="table-light">';
    html += '<tr><th>Student</th><th>Status</th><th>Certificate Number</th><th>Actions</th></tr>';
    html += '</thead><tbody>';
    
    data.results.forEach(function(result) {
        html += '<tr>';
        html += `<td>${escapeHtml(result.student_name)}</td>`;
        html += `<td><span class="badge bg-${result.status === 'graduated' ? 'success' : 'warning'}">${result.status === 'graduated' ? 'Graduated' : result.message}</span></td>`;
        html += `<td>${result.certificate_number ? '<code>' + result.certificate_number + '</code>' : '-'}</td>`;
        html += '<td>';
        if (result.certificate_id) {
            html += `<a href="<?php echo APP_URL; ?>modules/certificates/view-certificate.php?id=${result.certificate_id}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="ri-eye-line"></i> View
                     </a> `;
            html += `<a href="<?php echo APP_URL; ?>modules/certificates/download-certificate.php?id=${result.certificate_id}" class="btn btn-sm btn-success">
                        <i class="ri-download-line"></i> Download
                     </a>`;
        }
        html += '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    html += '</div>';
    
    html += '<div class="mt-4 text-center">';
    html += '<button onclick="window.print()" class="btn btn-secondary"><i class="ri-printer-line"></i> Print Report</button> ';
    html += '<button onclick="downloadBulkCertificates()" class="btn btn-primary"><i class="ri-download-line"></i> Download All Certificates (ZIP)</button>';
    html += '</div>';
    
    $('#resultsContent').html(html);
    $('#graduationResults').show();
    $('html, body').animate({
        scrollTop: $('#graduationResults').offset().top - 100
    }, 500);
}

function downloadBulkCertificates() {
    Swal.fire('Info', 'Bulk download feature will be available soon. You can download individual certificates using the Download button.', 'info');
}

function resetGraduation() {
    $('#graduationForm')[0].reset();
    $('#certificateConfigForm')[0].reset();
    $('#requirementsConfig').hide();
    $('#statsDashboard').hide();
    $('#studentsListContainer').hide();
    $('#certificateConfig').hide();
    $('#graduationResults').hide();
    studentsData = [];
    selectedStudentIds = [];
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>


