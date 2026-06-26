<?php
/**
 * Timetable Management
 * 
 * Create and manage class timetables
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Timetable Management';

// Get current user
$currentUser = getCurrentUser();
$currentSession = getCurrentSession();

// Get filter parameters
$classId = $_GET['class_id'] ?? '';
$sectionId = $_GET['section_id'] ?? '';

// Get classes (excluding graduated classes)
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get subjects
$subjectsSql = "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name";
$subjects = fetchAll(executeQuery($subjectsSql));

// Get staff (teachers)
$staffSql = "SELECT s.id, s.staff_id, s.first_name, s.last_name, s.designation
             FROM staff s 
             WHERE s.status = 'Active' AND s.designation LIKE '%Teacher%'
             ORDER BY s.first_name";
$teachers = fetchAll(executeQuery($staffSql));

// Get timetable if class selected
$timetable = [];
if (!empty($classId) && !empty($sectionId)) {
    $sql = "SELECT t.*, s.subject_name, st.first_name, st.last_name
            FROM timetable t
            LEFT JOIN subjects s ON t.subject_id = s.id
            LEFT JOIN staff st ON t.teacher_id = st.id
            WHERE t.class_id = ? AND t.section_id = ? AND t.session_id = ?
            ORDER BY 
                FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                t.start_time";
    
    $stmt = executeQuery($sql, 'iii', [$classId, $sectionId, $currentSession['id']]);
    $timetable = fetchAll($stmt);
}

// Organize timetable by day
$timetableByDay = [];
foreach ($timetable as $entry) {
    $timetableByDay[$entry['day_of_week']][] = $entry;
}

$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

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
                            <?php if (!empty($classId) && !empty($sectionId)): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
                                <i class="ri-add-line"></i> Add Period
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Timetable Management</h4>
                    </div>
                </div>
            </div>

            <!-- Selection Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Class</label>
                                    <select class="form-select" name="class_id" id="classSelect" required>
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classId == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Section</label>
                                    <select class="form-select" name="section_id" id="sectionSelect" required>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line"></i> Load Timetable
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timetable Display -->
            <?php if (!empty($classId) && !empty($sectionId)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="header-title mb-0">Weekly Timetable</h4>
                                <button onclick="window.print()" class="btn btn-sm btn-secondary no-print">
                                    <i class="ri-printer-line"></i> Print
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th width="12%">Day</th>
                                            <th>Periods</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($daysOfWeek as $day): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo $day; ?></td>
                                            <td>
                                                <?php if (isset($timetableByDay[$day]) && !empty($timetableByDay[$day])): ?>
                                                    <?php foreach ($timetableByDay[$day] as $period): ?>
                                                    <div class="border p-2 mb-2 rounded" style="display: inline-block; margin-right: 10px;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1">
                                                                <strong class="text-primary">
                                                                    <?php echo htmlspecialchars($period['subject_name']); ?>
                                                                </strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <i class="ri-time-line"></i> 
                                                                    <?php echo date('h:i A', strtotime($period['start_time'])); ?> - 
                                                                    <?php echo date('h:i A', strtotime($period['end_time'])); ?>
                                                                </small>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <i class="ri-user-line"></i> 
                                                                    <?php echo htmlspecialchars($period['first_name'] . ' ' . $period['last_name']); ?>
                                                                </small>
                                                                <?php if ($period['room_no']): ?>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <i class="ri-door-line"></i> Room <?php echo htmlspecialchars($period['room_no']); ?>
                                                                </small>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php if (hasRole(['Super Admin', 'Admin'])): ?>
                                                            <div class="ms-2 no-print d-flex gap-1">
                                                                <button onclick="viewPeriodDetails(<?php echo $period['id']; ?>)" 
                                                                        class="btn btn-sm btn-info" title="View Details">
                                                                    <i class="ri-eye-line"></i>
                                                                </button>
                                                                <button onclick="editPeriod(<?php echo $period['id']; ?>)" 
                                                                        class="btn btn-sm btn-warning" title="Edit">
                                                                    <i class="ri-edit-line"></i>
                                                                </button>
                                                                <button onclick="deletePeriod(<?php echo $period['id']; ?>)" 
                                                                        class="btn btn-sm btn-danger" title="Delete">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No periods scheduled</span>
                                                <?php endif; ?>
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

        </div>
    </div>

<!-- Add Period Modal -->
<div class="modal fade" id="addPeriodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Period to Timetable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPeriodForm">
                <input type="hidden" name="class_id" value="<?php echo $classId; ?>">
                <input type="hidden" name="section_id" value="<?php echo $sectionId; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Day of Week</label>
                        <select class="form-select" name="day_of_week" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Subject</label>
                        <select class="form-select" name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Teacher</label>
                        <select class="form-select" name="teacher_id" required>
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                    (<?php echo htmlspecialchars($teacher['staff_id']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Time</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">End Time</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" class="form-control" name="room_no" placeholder="e.g., Room 101">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Period
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Period Details Modal -->
<div class="modal fade" id="viewPeriodModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Period Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewPeriodContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Period Modal -->
<div class="modal fade" id="editPeriodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPeriodForm">
                <input type="hidden" name="id" id="editPeriodId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Day of Week</label>
                        <select class="form-select" name="day_of_week" id="editDayOfWeek" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Subject</label>
                        <select class="form-select" name="subject_id" id="editSubjectId" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Teacher</label>
                        <select class="form-select" name="teacher_id" id="editTeacherId" required>
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                    (<?php echo htmlspecialchars($teacher['staff_id']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Time</label>
                            <input type="time" class="form-control" name="start_time" id="editStartTime" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">End Time</label>
                            <input type="time" class="form-control" name="end_time" id="editEndTime" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" class="form-control" name="room_no" id="editRoomNo" placeholder="e.g., Room 101">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Update Period
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Load sections on page load
$(document).ready(function() {
    <?php if (!empty($classId)): ?>
    loadSections(<?php echo $classId; ?>, <?php echo $sectionId; ?>);
    <?php endif; ?>
});

// Load sections when class changes
$('#classSelect').change(function() {
    loadSections($(this).val());
});

function loadSections(classId, selectedId = null) {
    if (!classId) return;
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/get-sections.php',
        type: 'GET',
        data: { class_id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Section</option>';
                response.data.forEach(function(section) {
                    const selected = (selectedId && section.id == selectedId) ? 'selected' : '';
                    options += `<option value="${section.id}" ${selected}>${section.section_name}</option>`;
                });
                $('#sectionSelect').html(options);
            }
        }
    });
}

// Add period
$('#addPeriodForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/add-timetable-period.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addPeriodModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// View period details
function viewPeriodDetails(periodId) {
    $('#viewPeriodContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#viewPeriodModal').modal('show');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-timetable-period.php',
        type: 'GET',
        data: { id: periodId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const period = response.data;
                
                let html = '<div class="row">';
                html += '<div class="col-md-12 mb-4">';
                html += '<h5 class="mb-3"><i class="ri-information-line"></i> Period Information</h5>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered">';
                html += '<tr><th width="30%">Class</th><td><strong>' + escapeHtml(period.class_name) + '</strong> (' + escapeHtml(period.class_code) + ')</td></tr>';
                html += '<tr><th>Section</th><td>' + escapeHtml(period.section_name) + '</td></tr>';
                html += '<tr><th>Day</th><td><span class="badge bg-primary">' + escapeHtml(period.day_of_week) + '</span></td></tr>';
                html += '<tr><th>Subject</th><td><strong>' + escapeHtml(period.subject_name) + '</strong>';
                if (period.subject_code) {
                    html += ' (' + escapeHtml(period.subject_code) + ')';
                }
                html += '</td></tr>';
                html += '<tr><th>Teacher</th><td>' + escapeHtml(period.first_name + ' ' + period.last_name);
                if (period.staff_id) {
                    html += '<br><small class="text-muted">Staff ID: ' + escapeHtml(period.staff_id) + '</small>';
                }
                html += '</td></tr>';
                html += '<tr><th>Time</th><td><i class="ri-time-line"></i> ' + formatTime(period.start_time) + ' - ' + formatTime(period.end_time) + '</td></tr>';
                if (period.room_no) {
                    html += '<tr><th>Room</th><td><i class="ri-door-line"></i> ' + escapeHtml(period.room_no) + '</td></tr>';
                } else {
                    html += '<tr><th>Room</th><td><em class="text-muted">Not assigned</em></td></tr>';
                }
                html += '</table>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                
                $('#viewPeriodContent').html(html);
            } else {
                $('#viewPeriodContent').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#viewPeriodContent').html('<div class="alert alert-danger">Failed to load period details</div>');
        }
    });
}

// Edit period - Load period data
function editPeriod(periodId) {
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/get-timetable-period.php',
        type: 'GET',
        data: { id: periodId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const period = response.data;
                $('#editPeriodId').val(period.id);
                $('#editDayOfWeek').val(period.day_of_week);
                $('#editSubjectId').val(period.subject_id);
                $('#editTeacherId').val(period.teacher_id);
                $('#editStartTime').val(period.start_time);
                $('#editEndTime').val(period.end_time);
                $('#editRoomNo').val(period.room_no || '');
                $('#editPeriodModal').modal('show');
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            showToast('Failed to load period details', 'error');
        }
    });
}

// Update period
$('#editPeriodForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/edit-timetable-period.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#editPeriodModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete period
function deletePeriod(periodId) {
    confirmAction('Remove this period from timetable?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-timetable-period.php',
            type: 'POST',
            data: { id: periodId },
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
            }
        });
    });
}

// Helper functions
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

function formatTime(timeString) {
    if (!timeString) return '';
    const time = new Date('2000-01-01T' + timeString);
    return time.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}
</script>

