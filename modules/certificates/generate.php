<?php
/**
 * Generate Certificates
 * 
 * Generate certificates for students with verified academic data
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Generate Certificates';

$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$branchId = $isSuperAdmin ? ($_GET['branch_id'] ?? null) : $currentUser['branch_id'];

// Get filter for class type
$classFilter = $_GET['class_filter'] ?? 'active'; // 'active', 'graduated', 'all'

// Get classes based on filter - but always include graduated classes in dropdown for certificate generation
$classesSql = "SELECT c.*, b.branch_name,
                (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status IN ('Active', 'Graduated')) as student_count
                FROM classes c 
                LEFT JOIN branches b ON c.branch_id = b.id
                WHERE c.is_active = 1";
                
if ($classFilter === 'graduated') {
    $classesSql .= " AND c.graduation_status = 'Graduated'";
} elseif ($classFilter === 'active') {
    // For active filter, still include graduated classes in dropdown (they'll be marked)
    // This allows generating certificates for graduated classes even when viewing active classes
    // $classesSql .= " AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')";
}
// 'all' shows both - no filter needed

if ($branchId) {
    $classesSql .= " AND c.branch_id = " . intval($branchId);
}
$classesSql .= " ORDER BY c.graduation_status DESC, c.class_order, c.class_name";
$classes = fetchAll(executeQuery($classesSql));

// Get graduated classes separately for quick access
$graduatedClassesSql = "SELECT c.*, b.branch_name,
                        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'Graduated') as graduated_student_count
                        FROM classes c 
                        LEFT JOIN branches b ON c.branch_id = b.id
                        WHERE c.is_active = 1 AND c.graduation_status = 'Graduated'";
if ($branchId) {
    $graduatedClassesSql .= " AND c.branch_id = " . intval($branchId);
}
$graduatedClassesSql .= " ORDER BY c.graduated_at DESC, c.class_name";
$graduatedClasses = fetchAll(executeQuery($graduatedClassesSql));

// Get academic sessions
$sessionsSql = "SELECT * FROM academic_sessions ORDER BY start_date DESC";
$sessions = fetchAll(executeQuery($sessionsSql));

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Get certificate templates
$templatesSql = "SELECT * FROM certificate_templates WHERE is_active = 1";
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

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <div class="btn-group" role="group">
                                <a href="?class_filter=active<?php echo $branchId ? '&branch_id=' . $branchId : ''; ?>" 
                                   class="btn btn-sm <?php echo $classFilter === 'active' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    Active Classes
                                </a>
                                <a href="?class_filter=graduated<?php echo $branchId ? '&branch_id=' . $branchId : ''; ?>" 
                                   class="btn btn-sm <?php echo $classFilter === 'graduated' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    <i class="ri-graduation-cap-line"></i> Graduated Classes
                                </a>
                                <a href="?class_filter=all<?php echo $branchId ? '&branch_id=' . $branchId : ''; ?>" 
                                   class="btn btn-sm <?php echo $classFilter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    All Classes
                                </a>
                            </div>
                        </div>
                        <h4 class="page-title">Generate Certificates</h4>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($graduatedClasses) && $classFilter !== 'graduated'): ?>
            <!-- Quick Action: Generate for Graduated Classes -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h5 class="card-title text-warning">
                                <i class="ri-graduation-cap-line"></i> Quick Generate for Graduated Classes
                            </h5>
                            <p class="text-muted mb-3">
                                Generate certificates for all students in graduated classes at once.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Branch</th>
                                            <th>Graduated Students</th>
                                            <th>Graduation Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($graduatedClasses as $gClass): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($gClass['class_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($gClass['branch_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $gClass['graduated_student_count']; ?> students
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $gClass['graduated_at'] ? date('M d, Y', strtotime($gClass['graduated_at'])) : 'N/A'; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success" 
                                                        onclick="quickGenerateForGraduatedClass(<?php echo $gClass['id']; ?>, '<?php echo htmlspecialchars($gClass['class_name']); ?>')">
                                                    <i class="ri-file-list-line"></i> Generate Certificates
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
            <?php endif; ?>

            <!-- Selection Wizard -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Certificate Generation Wizard</h4>
                            
                            <form id="certificateGenerationForm">
                                <!-- Step 1: Select Students -->
                                <div class="step-section mb-4" id="step1">
                                    <h5 class="text-primary mb-3">
                                        <i class="ri-user-line"></i> Step 1: Select Students
                                    </h5>
                                    
                                    <div class="row">
                                        <?php if ($isSuperAdmin): ?>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Branch</label>
                                            <select class="form-select" name="branch_id" id="branchSelect">
                                                <option value="">All Branches</option>
                                                <?php foreach ($branches as $branch): ?>
                                                    <option value="<?php echo $branch['id']; ?>">
                                                        <?php echo htmlspecialchars($branch['branch_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label required">Class</label>
                                            <select class="form-select" name="class_id" id="classSelect" required>
                                                <option value="">Select Class</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?php echo $class['id']; ?>" 
                                                            data-graduated="<?php echo ($class['graduation_status'] === 'Graduated') ? '1' : '0'; ?>">
                                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                                        <?php if ($class['graduation_status'] === 'Graduated'): ?>
                                                            [Graduated]
                                                        <?php endif; ?>
                                                        <?php if (isset($class['student_count']) && $class['student_count'] > 0): ?>
                                                            (<?php echo $class['student_count']; ?> students)
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <?php 
                                                // Also include graduated classes that might not be in the main list
                                                if ($classFilter !== 'graduated' && !empty($graduatedClasses)) {
                                                    foreach ($graduatedClasses as $gClass) {
                                                        // Check if already in the list
                                                        $alreadyInList = false;
                                                        foreach ($classes as $c) {
                                                            if ($c['id'] == $gClass['id']) {
                                                                $alreadyInList = true;
                                                                break;
                                                            }
                                                        }
                                                        if (!$alreadyInList) {
                                                            echo '<option value="' . $gClass['id'] . '" data-graduated="1">';
                                                            echo htmlspecialchars($gClass['class_name']) . ' [Graduated]';
                                                            if (isset($gClass['graduated_student_count']) && $gClass['graduated_student_count'] > 0) {
                                                                echo ' (' . $gClass['graduated_student_count'] . ' students)';
                                                            }
                                                            echo '</option>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
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
                                        
                                        <div class="col-12 mb-3">
                                            <button type="button" class="btn btn-primary" onclick="loadStudents()">
                                                <i class="ri-search-line"></i> Load Students
                                            </button>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div id="studentsListContainer"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Step 2: Certificate Configuration -->
                                <div class="step-section mb-4" id="step2" style="display:none;">
                                    <h5 class="text-primary mb-3">
                                        <i class="ri-file-text-line"></i> Step 2: Certificate Configuration
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Certificate Template</label>
                                            <select class="form-select" name="template_id" id="templateSelect" required>
                                                <option value="">Select Template</option>
                                                <?php foreach ($templates as $template): ?>
                                                    <option value="<?php echo $template['id']; ?>" <?php echo $template['is_default'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($template['template_name']); ?> 
                                                        (<?php echo ucfirst($template['certificate_type']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Grading Scheme</label>
                                            <select class="form-select" name="grading_scheme_id" required>
                                                <option value="">Select Grading Scheme</option>
                                                <?php foreach ($gradingSchemes as $scheme): ?>
                                                    <option value="<?php echo $scheme['id']; ?>" <?php echo $scheme['is_default'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($scheme['scheme_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Issue Date</label>
                                            <input type="date" class="form-control" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Valid Until</label>
                                            <input type="date" class="form-control" name="valid_until">
                                            <small class="text-muted">Leave blank for no expiration</small>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Additional Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2" 
                                                      placeholder="Optional remarks to include in certificate"></textarea>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <h6>Include in Certificate:</h6>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="include_grades" id="includeGrades" value="1" checked>
                                                        <label class="form-check-label" for="includeGrades">
                                                            Subject Grades
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="include_gpa" id="includeGPA" value="1" checked>
                                                        <label class="form-check-label" for="includeGPA">
                                                            GPA/CGPA
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="include_attendance" id="includeAttendance" value="1">
                                                        <label class="form-check-label" for="includeAttendance">
                                                            Attendance %
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="include_rank" id="includeRank" value="1">
                                                        <label class="form-check-label" for="includeRank">
                                                            Class Rank
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Step 3: Generate -->
                                <div class="step-section" id="step3" style="display:none;">
                                    <h5 class="text-primary mb-3">
                                        <i class="ri-check-line"></i> Step 3: Generate & Download
                                    </h5>
                                    
                                    <div class="alert alert-info">
                                        <i class="ri-information-line"></i> 
                                        Click "Generate Certificates" to create certificates for all selected students. 
                                        Each certificate will be assigned a unique ID and can be verified later.
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success" id="generateBtn">
                                            <i class="ri-file-list-line"></i> Generate Certificates
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                            <i class="ri-refresh-line"></i> Start Over
                                        </button>
                                    </div>
                                    
                                    <div id="generationProgress" class="mt-4" style="display:none;">
                                        <h6>Generation Progress</h6>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" 
                                                 role="progressbar" style="width: 0%">0%</div>
                                        </div>
                                        <div id="progressDetails" class="mt-2"></div>
                                    </div>
                                    
                                    <div id="generationResults" class="mt-4"></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
let selectedStudents = [];

// Load students
function loadStudents() {
    const classId = $('#classSelect').val();
    const sessionId = $('#sessionSelect').val();
    
    if (!classId || !sessionId) {
        Swal.fire('Error', 'Please select both class and session', 'error');
        return;
    }
    
    $('#studentsListContainer').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/certificates/get-students-for-certificate.php',
        type: 'GET',
        data: { 
            class_id: classId, 
            session_id: sessionId,
            branch_id: $('#branchSelect').val() || ''
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                // Check if this is a graduated class
                const selectedClassId = $('#classSelect').val();
                const selectedOption = $('#classSelect option:selected');
                const isGraduatedClass = selectedOption.data('graduated') === '1' || selectedOption.data('graduated') === 1;
                
                let html = '<h6 class="mb-3">Select Students (<span id="selectedCount">0</span> selected)</h6>';
                
                if (isGraduatedClass) {
                    html += '<div class="alert alert-info mb-3">';
                    html += '<i class="ri-graduation-cap-line"></i> <strong>Graduated Class:</strong> All students will be auto-selected. ';
                    html += 'A graduation certificate template is recommended.';
                    html += '</div>';
                }
                
                html += '<div class="mb-2">';
                html += '<button type="button" class="btn btn-sm btn-primary" onclick="selectAllStudents()">Select All</button> ';
                html += '<button type="button" class="btn btn-sm btn-secondary" onclick="deselectAllStudents()">Deselect All</button>';
                html += '</div>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-sm table-hover">';
                html += '<thead><tr><th width="50"><input type="checkbox" id="selectAllCheckbox" onchange="toggleAllStudents(this)"></th>';
                html += '<th>Student ID</th><th>Name</th><th>Status</th><th>Info</th></tr></thead><tbody>';
                
                response.data.forEach(function(student) {
                    const isGraduated = student.status === 'Graduated';
                    html += `<tr>
                        <td><input type="checkbox" class="student-checkbox" value="${student.id}" data-name="${escapeHtml(student.full_name)}" onchange="updateSelectedCount()" ${isGraduatedClass ? 'checked' : ''}></td>
                        <td>${escapeHtml(student.student_id)}</td>
                        <td><strong>${escapeHtml(student.full_name)}</strong></td>
                        <td><span class="badge bg-${isGraduated ? 'success' : (student.status === 'Active' ? 'primary' : 'secondary')}">${student.status}</span></td>
                        <td><small class="text-muted">${escapeHtml(student.section_name || 'No section')}</small></td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                html += '<button type="button" class="btn btn-primary mt-3" onclick="proceedToStep2()">Next: Configure Certificate</button>';
                
                $('#studentsListContainer').html(html);
                
                // Auto-select all if graduated class
                if (isGraduatedClass) {
                    $('.student-checkbox').prop('checked', true);
                    $('#selectAllCheckbox').prop('checked', true);
                    updateSelectedCount();
                    
                    // Auto-select graduation certificate template if available
                    const graduationTemplate = $('#templateSelect option').filter(function() {
                        return $(this).text().toLowerCase().includes('graduation');
                    }).first();
                    
                    if (graduationTemplate.length > 0) {
                        graduationTemplate.prop('selected', true);
                    }
                    
                    // Auto-proceed to step 2 after a short delay
                    setTimeout(function() {
                        proceedToStep2();
                    }, 800);
                }
            } else {
                let errorMsg = response.message || 'No eligible students found for the selected class and session.';
                $('#studentsListContainer').html('<div class="alert alert-warning"><i class="ri-alert-line"></i> ' + errorMsg + '</div>');
            }
        },
        error: function() {
            $('#studentsListContainer').html('<div class="alert alert-danger">Failed to load students</div>');
        }
    });
}

function toggleAllStudents(checkbox) {
    $('.student-checkbox').prop('checked', checkbox.checked);
    updateSelectedCount();
}

function selectAllStudents() {
    $('.student-checkbox').prop('checked', true);
    $('#selectAllCheckbox').prop('checked', true);
    updateSelectedCount();
}

function deselectAllStudents() {
    $('.student-checkbox').prop('checked', false);
    $('#selectAllCheckbox').prop('checked', false);
    updateSelectedCount();
}

function updateSelectedCount() {
    const count = $('.student-checkbox:checked').length;
    $('#selectedCount').text(count);
}

function proceedToStep2() {
    selectedStudents = [];
    $('.student-checkbox:checked').each(function() {
        selectedStudents.push({
            id: $(this).val(),
            name: $(this).data('name')
        });
    });
    
    if (selectedStudents.length === 0) {
        Swal.fire('Error', 'Please select at least one student', 'error');
        return;
    }
    
    $('#step2').show();
    $('#step3').show();
    $('html, body').animate({
        scrollTop: $('#step2').offset().top - 100
    }, 500);
}

function resetForm() {
    $('#certificateGenerationForm')[0].reset();
    $('#studentsListContainer').html('');
    $('#step2, #step3').hide();
    $('#generationProgress, #generationResults').hide();
    selectedStudents = [];
}

// Generate certificates
$('#certificateGenerationForm').on('submit', function(e) {
    e.preventDefault();
    
    if (selectedStudents.length === 0) {
        Swal.fire('Error', 'No students selected', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Generate Certificates?',
        text: `This will generate ${selectedStudents.length} certificate(s)`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, generate'
    }).then((result) => {
        if (result.isConfirmed) {
            generateCertificates();
        }
    });
});

function generateCertificates() {
    $('#generateBtn').prop('disabled', true);
    $('#generationProgress').show();
    $('#generationResults').html('');
    
    const formData = $('#certificateGenerationForm').serialize() + '&students=' + JSON.stringify(selectedStudents);
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/certificates/generate-certificates.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            $('#generateBtn').prop('disabled', false);
            
            if (response.success) {
                $('#progressBar').css('width', '100%').text('100%').removeClass('progress-bar-animated');
                
                let html = '<div class="alert alert-success">';
                html += '<h5><i class="ri-check-line"></i> Certificates Generated Successfully!</h5>';
                html += `<p>${response.data.generated_count} certificate(s) have been generated.</p>`;
                html += '</div>';
                
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered">';
                html += '<thead><tr><th>Student</th><th>Certificate ID</th><th>Actions</th></tr></thead><tbody>';
                
                response.data.certificates.forEach(function(cert) {
                    html += `<tr>
                        <td>${escapeHtml(cert.student_name)}</td>
                        <td><code>${cert.certificate_number}</code></td>
                        <td>
                            <a href="<?php echo APP_URL; ?>modules/certificates/view.php?id=${cert.id}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="ri-eye-line"></i> View
                            </a>
                            <a href="<?php echo APP_URL; ?>modules/certificates/download.php?id=${cert.id}" class="btn btn-sm btn-success">
                                <i class="ri-download-line"></i> Download
                            </a>
                        </td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                
                $('#generationResults').html(html);
            } else {
                $('#generationProgress').hide();
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            $('#generateBtn').prop('disabled', false);
            $('#generationProgress').hide();
            Swal.fire('Error', 'Failed to generate certificates', 'error');
        }
    });
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

// Quick generate for graduated class
function quickGenerateForGraduatedClass(classId, className) {
    // Check if the class exists in the dropdown, if not, add it
    const classSelect = $('#classSelect');
    const classOption = classSelect.find('option[value="' + classId + '"]');
    
    if (classOption.length === 0) {
        // Add the graduated class to the dropdown
        const newOption = $('<option></option>')
            .attr('value', classId)
            .attr('data-graduated', '1')
            .text(className + ' [Graduated]');
        classSelect.append(newOption);
    }
    
    // Set the class in the form
    classSelect.val(classId).trigger('change');
    
    // Ensure a session is selected (use current selection or select first available)
    const sessionSelect = $('#sessionSelect');
    if (sessionSelect.length > 0) {
        let selectedSession = sessionSelect.val();
        if (!selectedSession || selectedSession === '') {
            // Select the first available session (skip the "Select Session" option)
            const firstOption = sessionSelect.find('option').not(':first').first();
            if (firstOption.length > 0) {
                sessionSelect.val(firstOption.val()).trigger('change');
                selectedSession = firstOption.val();
            }
        }
    }
    
    // Scroll to form
    $('html, body').animate({
        scrollTop: $('#certificateGenerationForm').offset().top - 100
    }, 300);
    
    // Auto-load students after a short delay
    setTimeout(function() {
        const sessionId = $('#sessionSelect').val();
        if (sessionId) {
            // Show loading indicator
            $('#studentsListContainer').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading students...</span></div><p class="mt-2">Loading students for ' + escapeHtml(className) + '...</p></div>');
            
            loadStudents();
        } else {
            Swal.fire({
                title: 'Session Required',
                text: 'Please select an academic session first, then click "Load Students".',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }
    }, 500);
}
</script>

