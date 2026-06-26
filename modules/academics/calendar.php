<?php
/**
 * Academic Calendar
 * 
 * Manage academic calendar events (holidays, exams, meetings)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Academic Calendar';

// Get current session
$currentSession = getCurrentSession();
$currentUser = getCurrentUser();

// Get selected month/year
$selectedMonth = $_GET['month'] ?? date('m');
$selectedYear = $_GET['year'] ?? date('Y');

// Get calendar events
$sql = "SELECT ac.*, b.branch_name
        FROM academic_calendar ac
        LEFT JOIN branches b ON ac.branch_id = b.id
        WHERE ac.session_id = ?
        AND MONTH(ac.start_date) = ? 
        AND YEAR(ac.start_date) = ?";

$params = [$currentSession['id'], $selectedMonth, $selectedYear];
$types = 'iii';

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND (ac.branch_id = ? OR ac.branch_id IS NULL)";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY ac.start_date ASC";

$stmt = executeQuery($sql, $types, $params);
$calendarEvents = fetchAll($stmt);

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
                            <?php if (hasRole(['Super Admin', 'Admin'])): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                                <i class="ri-calendar-event-line"></i> Add Event
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Academic Calendar</h4>
                    </div>
                </div>
            </div>

            <!-- Month/Year Selector -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Month</label>
                                    <select name="month" class="form-select">
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" 
                                                    <?php echo ($m == $selectedMonth) ? 'selected' : ''; ?>>
                                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Year</label>
                                    <select name="year" class="form-select">
                                        <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                                            <option value="<?php echo $y; ?>" <?php echo ($y == $selectedYear) ? 'selected' : ''; ?>>
                                                <?php echo $y; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i> View Calendar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Display -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <?php echo date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)); ?>
                                <span class="badge bg-primary ms-2"><?php echo count($calendarEvents); ?> Events</span>
                            </h4>
                            
                            <?php if (!empty($calendarEvents)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Event</th>
                                            <th>Type</th>
                                            <th>Duration</th>
                                            <th>Branch</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($calendarEvents as $event): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo formatDate($event['start_date'], 'd M'); ?></strong>
                                                <?php if ($event['start_date'] != $event['end_date']): ?>
                                                    <br><small class="text-muted">to <?php echo formatDate($event['end_date'], 'd M'); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($event['event_title']); ?></strong>
                                                <?php if ($event['description']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $typeClass = 'secondary';
                                                switch($event['event_type']) {
                                                    case 'Holiday': $typeClass = 'success'; break;
                                                    case 'Exam': $typeClass = 'danger'; break;
                                                    case 'Event': $typeClass = 'primary'; break;
                                                    case 'Meeting': $typeClass = 'warning'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $typeClass; ?>">
                                                    <?php echo htmlspecialchars($event['event_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $days = (strtotime($event['end_date']) - strtotime($event['start_date'])) / 86400 + 1;
                                                echo $days == 1 ? '1 day' : $days . ' days';
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($event['branch_name'] ?? 'All Branches'); ?></td>
                                            <td>
                                                <?php if (hasRole(['Super Admin', 'Admin'])): ?>
                                                <button onclick="deleteEvent(<?php echo $event['id']; ?>)" 
                                                        class="btn btn-sm btn-danger">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="ri-calendar-line font-24"></i>
                                <h5 class="mt-2">No events scheduled</h5>
                                <p class="mb-0">No academic events for this month.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Calendar Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addEventForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Event Title</label>
                        <input type="text" class="form-control" name="event_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Event Type</label>
                        <select class="form-select" name="event_type" required>
                            <option value="">Select Type</option>
                            <option value="Holiday">Holiday</option>
                            <option value="Exam">Examination</option>
                            <option value="Event">Event</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add event
$('#addEventForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/academics/add-calendar-event.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addEventModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete event
function deleteEvent(eventId) {
    confirmAction('Remove this event from academic calendar?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/academics/delete-calendar-event.php',
            type: 'POST',
            data: { id: eventId },
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
</script>

