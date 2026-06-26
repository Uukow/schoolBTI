<?php
/**
 * Events & Calendar
 * 
 * Manage school events and calendar
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Events & Calendar';

// Get current user
$currentUser = getCurrentUser();

// Get selected month/year
$selectedMonth = $_GET['month'] ?? date('m');
$selectedYear = $_GET['year'] ?? date('Y');

// Get events for the month
$sql = "SELECT e.*, b.branch_name
        FROM events e
        LEFT JOIN branches b ON e.branch_id = b.id
        WHERE MONTH(e.event_date) = ? AND YEAR(e.event_date) = ?";

$params = [$selectedMonth, $selectedYear];
$types = 'ii';

// Branch filter
if (!hasRole(['Super Admin'])) {
    $sql .= " AND (e.branch_id = ? OR e.branch_id IS NULL)";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY e.event_date ASC";

$stmt = executeQuery($sql, $types, $params);
$events = fetchAll($stmt);

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
                            <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                                <i class="ri-calendar-event-line"></i> Add Event
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Events & Calendar</h4>
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
                                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                                            <option value="<?php echo $y; ?>" <?php echo ($y == $selectedYear) ? 'selected' : ''; ?>>
                                                <?php echo $y; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i> View
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Events List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                Events for <?php echo date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)); ?>
                                <span class="badge bg-primary ms-2"><?php echo count($events); ?> Events</span>
                            </h4>
                            
                            <?php if (!empty($events)): ?>
                            <div class="timeline">
                                <?php foreach ($events as $event): ?>
                                <div class="timeline-item">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="text-center">
                                                <h3 class="mb-0"><?php echo date('d', strtotime($event['event_date'])); ?></h3>
                                                <small class="text-muted"><?php echo date('M', strtotime($event['event_date'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <?php echo htmlspecialchars($event['event_title']); ?>
                                                        <?php if ($event['event_type']): ?>
                                                            <span class="badge bg-info ms-2"><?php echo htmlspecialchars($event['event_type']); ?></span>
                                                        <?php endif; ?>
                                                    </h5>
                                                    <?php if ($event['event_description']): ?>
                                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
                                                    <?php endif; ?>
                                                    
                                                    <div class="mt-2">
                                                        <?php if ($event['start_time']): ?>
                                                            <small class="text-muted">
                                                                <i class="ri-time-line me-1"></i><?php echo date('h:i A', strtotime($event['start_time'])); ?>
                                                                <?php if ($event['end_time']): ?>
                                                                    - <?php echo date('h:i A', strtotime($event['end_time'])); ?>
                                                                <?php endif; ?>
                                                            </small>
                                                        <?php endif; ?>
                                                        <?php if ($event['venue']): ?>
                                                            <small class="text-muted ms-3">
                                                                <i class="ri-map-pin-line me-1"></i><?php echo htmlspecialchars($event['venue']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                        <?php if ($event['branch_name']): ?>
                                                            <small class="text-muted ms-3">
                                                                <i class="ri-building-line me-1"></i><?php echo htmlspecialchars($event['branch_name']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <?php if (hasRole(['Super Admin', 'Admin'])): ?>
                                                    <div class="mt-2 text-end">
                                                        <button onclick="deleteEvent(<?php echo $event['id']; ?>)" class="btn btn-sm btn-danger">
                                                            <i class="ri-delete-bin-line"></i> Delete
                                                        </button>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="ri-calendar-line font-24"></i>
                                <h5 class="mt-2">No events scheduled</h5>
                                <p class="mb-0">No events found for this month.</p>
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
                <h5 class="modal-title">Add Event</h5>
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
                        <textarea class="form-control" name="event_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Event Type</label>
                        <input type="text" class="form-control" name="event_type" list="eventTypeList">
                        <datalist id="eventTypeList">
                            <option value="Academic">
                            <option value="Sports">
                            <option value="Cultural">
                            <option value="Holiday">
                            <option value="Meeting">
                            <option value="Exam">
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Event Date</label>
                        <input type="date" class="form-control" name="event_date" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Venue</label>
                        <input type="text" class="form-control" name="venue">
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
        url: '<?php echo APP_URL; ?>ajax/events/add-event.php',
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
    confirmAction('Are you sure you want to delete this event?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/events/delete-event.php',
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

