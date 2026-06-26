<?php
/**
 * Academic Transcripts Generation
 * 
 * Generate detailed academic transcripts with subject-wise grades, credits, and CGPA
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Academic Transcripts';

$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$branchId = $isSuperAdmin ? ($_GET['branch_id'] ?? null) : $currentUser['branch_id'];

// Get students
$studentsSql = "SELECT s.*, c.class_name, b.branch_name 
                FROM students s 
                LEFT JOIN classes c ON s.current_class_id = c.id
                LEFT JOIN branches b ON s.branch_id = b.id
                WHERE s.status IN ('Active', 'Graduated')";
if ($branchId) {
    $studentsSql .= " AND s.branch_id = " . intval($branchId);
}
$studentsSql .= " ORDER BY s.first_name, s.last_name";
$students = fetchAll(executeQuery($studentsSql));

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

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
                        <h4 class="page-title">Academic Transcripts</h4>
                    </div>
                </div>
            </div>

            <!-- Selection Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Generate Academic Transcript</h4>
                            
                            <form id="transcriptForm">
                                <div class="row">
                                    <?php if ($isSuperAdmin): ?>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Branch</label>
                                        <select class="form-select" name="branch_id" id="branchSelect" onchange="filterStudents()">
                                            <option value="">All Branches</option>
                                            <?php foreach ($branches as $branch): ?>
                                                <option value="<?php echo $branch['id']; ?>">
                                                    <?php echo htmlspecialchars($branch['branch_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="col-md-<?php echo $isSuperAdmin ? '5' : '8'; ?> mb-3">
                                        <label class="form-label required">Select Student</label>
                                        <select class="form-select select2" name="student_id" id="studentSelect" required>
                                            <option value="">Choose Student</option>
                                            <?php foreach ($students as $student): ?>
                                                <option value="<?php echo $student['id']; ?>" data-branch="<?php echo $student['branch_id']; ?>">
                                                    <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['class_name'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Grading Scheme</label>
                                        <select class="form-select" name="grading_scheme_id" required>
                                            <option value="">Select Scheme</option>
                                            <?php foreach ($gradingSchemes as $scheme): ?>
                                                <option value="<?php echo $scheme['id']; ?>" <?php echo $scheme['is_default'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($scheme['scheme_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <h6>Transcript Options:</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="include_all_sessions" id="includeAllSessions" value="1" checked>
                                                    <label class="form-check-label" for="includeAllSessions">
                                                        All Academic Sessions
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="include_semester_gpa" id="includeSemesterGPA" value="1" checked>
                                                    <label class="form-check-label" for="includeSemesterGPA">
                                                        Semester-wise GPA
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="include_credits" id="includeCredits" value="1" checked>
                                                    <label class="form-check-label" for="includeCredits">
                                                        Credit Hours
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="include_attendance" id="includeTranscriptAttendance" value="1">
                                                    <label class="form-check-label" for="includeTranscriptAttendance">
                                                        Attendance Summary
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-file-text-line"></i> Generate Transcript
                                        </button>
                                        <button type="button" class="btn btn-info ms-2" onclick="previewTranscript()">
                                            <i class="ri-eye-line"></i> Preview
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transcript Preview/Results -->
            <div class="row" id="transcriptResultsContainer" style="display:none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body" id="transcriptContent">
                            <!-- Transcript content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Initialize Select2
$(document).ready(function() {
    $('#studentSelect').select2({
        placeholder: 'Search student by name or ID',
        allowClear: true
    });
});

// Filter students by branch
function filterStudents() {
    const branchId = $('#branchSelect').val();
    $('#studentSelect option').each(function() {
        const studentBranch = $(this).data('branch');
        if (!branchId || !studentBranch || studentBranch == branchId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    $('#studentSelect').val('').trigger('change');
}

// Generate transcript
$('#transcriptForm').on('submit', function(e) {
    e.preventDefault();
    
    const studentId = $('#studentSelect').val();
    if (!studentId) {
        Swal.fire('Error', 'Please select a student', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Generating Transcript...',
        text: 'Please wait while we compile the academic data',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/certificates/generate-transcript.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                displayTranscript(response.data);
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.close();
            Swal.fire('Error', 'Failed to generate transcript', 'error');
        }
    });
});

// Display transcript
function displayTranscript(data) {
    const transcript = data.transcript;
    const student = data.student;
    const sessions = data.sessions;
    
    let html = '<div class="transcript-document">';
    
    // Header
    html += '<div class="text-center mb-4">';
    html += '<h3 class="mb-1"><?php echo APP_NAME; ?></h3>';
    html += '<h4 class="mb-1">ACADEMIC TRANSCRIPT</h4>';
    html += '<p class="text-muted">Official Academic Record</p>';
    html += '</div>';
    
    // Student Information
    html += '<div class="row mb-4">';
    html += '<div class="col-md-6">';
    html += '<table class="table table-sm table-bordered">';
    html += '<tr><th width="40%">Student ID:</th><td>' + escapeHtml(student.student_id) + '</td></tr>';
    html += '<tr><th>Name:</th><td><strong>' + escapeHtml(student.full_name) + '</strong></td></tr>';
    html += '<tr><th>Date of Birth:</th><td>' + formatDate(student.date_of_birth) + '</td></tr>';
    html += '</table>';
    html += '</div>';
    html += '<div class="col-md-6">';
    html += '<table class="table table-sm table-bordered">';
    html += '<tr><th width="40%">Branch:</th><td>' + escapeHtml(student.branch_name) + '</td></tr>';
    html += '<tr><th>Current Class:</th><td>' + escapeHtml(student.class_name) + '</td></tr>';
    html += '<tr><th>Status:</th><td>' + escapeHtml(student.status) + '</td></tr>';
    html += '</table>';
    html += '</div>';
    html += '</div>';
    
    // Academic Records by Session
    sessions.forEach(function(session) {
        html += '<h5 class="mb-3">' + escapeHtml(session.session_name) + '</h5>';
        html += '<div class="table-responsive mb-4">';
        html += '<table class="table table-bordered table-sm">';
        html += '<thead class="table-light">';
        html += '<tr><th>Subject Code</th><th>Subject Name</th><th>Credits</th><th>Marks</th><th>Grade</th><th>Grade Points</th></tr>';
        html += '</thead><tbody>';
        
        if (session.subjects && session.subjects.length > 0) {
            session.subjects.forEach(function(subject) {
                html += '<tr>';
                html += '<td>' + escapeHtml(subject.subject_code) + '</td>';
                html += '<td>' + escapeHtml(subject.subject_name) + '</td>';
                html += '<td>' + (subject.credits || '-') + '</td>';
                html += '<td>' + (subject.marks !== null ? subject.marks : '-') + '</td>';
                html += '<td><strong>' + (subject.grade || '-') + '</strong></td>';
                html += '<td>' + (subject.grade_points !== null ? subject.grade_points : '-') + '</td>';
                html += '</tr>';
            });
        } else {
            html += '<tr><td colspan="6" class="text-center text-muted">No records found for this session</td></tr>';
        }
        
        html += '</tbody>';
        
        // Session summary
        if (session.gpa) {
            html += '<tfoot class="table-light">';
            html += '<tr>';
            html += '<td colspan="2"><strong>Session Summary:</strong></td>';
            html += '<td><strong>' + (session.total_credits || '0') + '</strong></td>';
            html += '<td colspan="2" class="text-end"><strong>GPA:</strong></td>';
            html += '<td><strong>' + session.gpa + '</strong></td>';
            html += '</tr>';
            html += '</tfoot>';
        }
        
        html += '</table>';
        html += '</div>';
    });
    
    // Overall Summary
    html += '<div class="row mt-4">';
    html += '<div class="col-md-6 offset-md-6">';
    html += '<table class="table table-bordered">';
    html += '<tr class="table-light"><th>Total Credits Earned:</th><td><strong>' + (transcript.total_credits || '0') + '</strong></td></tr>';
    html += '<tr class="table-light"><th>Cumulative GPA (CGPA):</th><td><strong>' + (transcript.cgpa || '0.00') + '</strong></td></tr>';
    if (transcript.overall_percentage) {
        html += '<tr class="table-light"><th>Overall Percentage:</th><td><strong>' + transcript.overall_percentage + '%</strong></td></tr>';
    }
    html += '</table>';
    html += '</div>';
    html += '</div>';
    
    // Footer
    html += '<div class="row mt-5">';
    html += '<div class="col-6 text-center">';
    html += '<div style="border-top: 1px solid #000; display: inline-block; padding: 5px 40px; margin-top: 50px;">Registrar</div>';
    html += '</div>';
    html += '<div class="col-6 text-center">';
    html += '<div style="border-top: 1px solid #000; display: inline-block; padding: 5px 40px; margin-top: 50px;">Principal</div>';
    html += '</div>';
    html += '</div>';
    
    html += '<div class="mt-4 text-muted text-center">';
    html += '<small>Generated on ' + new Date().toLocaleDateString() + ' | Transcript ID: ' + (transcript.transcript_number || 'N/A') + '</small>';
    html += '</div>';
    
    html += '</div>';
    
    // Action buttons
    html += '<div class="mt-4 text-center no-print">';
    html += '<button onclick="window.print()" class="btn btn-secondary"><i class="ri-printer-line"></i> Print</button> ';
    html += '<a href="<?php echo APP_URL; ?>modules/certificates/download-transcript.php?id=' + transcript.id + '" class="btn btn-success"><i class="ri-download-line"></i> Download PDF</a>';
    html += '</div>';
    
    $('#transcriptContent').html(html);
    $('#transcriptResultsContainer').show();
    $('html, body').animate({
        scrollTop: $('#transcriptResultsContainer').offset().top - 100
    }, 500);
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString();
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

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .transcript-document {
        padding: 20px;
    }
}
</style>

