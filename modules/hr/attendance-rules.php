<?php
/**
 * Attendance Rules Configuration
 */

require_once '../../config/config.php';

hrRequirePage('hr_attendance', 'view');

$pageTitle = 'Attendance Rules';

$branches = fetchAll(executeQuery("SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name"));

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ruleModal">
                                <i class="ri-add-line"></i> Add Rule
                            </button>
                        </div>
                        <h4 class="page-title">Attendance Rules & Working Hours</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-centered table-hover" id="rulesTable">
                                    <thead>
                                        <tr>
                                            <th>Rule Name</th>
                                            <th>Branch</th>
                                            <th>Work Hours</th>
                                            <th>Grace (min)</th>
                                            <th>Weekend Days</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="ruleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Attendance Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="ruleForm">
                    <input type="hidden" id="ruleId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rule Name *</label>
                            <input type="text" class="form-control" id="ruleName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch (blank = global)</label>
                            <select class="form-select" id="ruleBranch">
                                <option value="">Global Default</option>
                                <?php foreach ($branches as $b): ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="workStart" value="08:00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" id="workEnd" value="17:00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Break (min)</label>
                            <input type="number" class="form-control" id="breakMinutes" value="60">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Grace Period (min)</label>
                            <input type="number" class="form-control" id="gracePeriod" value="15">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Half Day Threshold (hrs)</label>
                            <input type="number" step="0.5" class="form-control" id="halfDayThreshold" value="4">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">OT Threshold (min)</label>
                            <input type="number" class="form-control" id="overtimeThreshold" value="30">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Weekend Days (0=Sun)</label>
                            <input type="text" class="form-control" id="weekendDays" value="5,6" placeholder="5,6">
                        </div>
                        <div class="col-md-4 mb-3 form-check mt-4">
                            <input type="checkbox" class="form-check-input" id="ruleActive" checked>
                            <label class="form-check-label" for="ruleActive">Active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveRuleBtn">Save Rule</button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Use window.APP_URL from footer.php — do not redeclare const APP_URL (causes SyntaxError)
(function () {
    var apiUrl = window.APP_URL || '<?php echo rtrim(APP_URL, '/'); ?>/';

function showSuccess(message, thenFn) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        timer: 2000,
        showConfirmButton: false
    }).then(() => { if (thenFn) thenFn(); });
}

function showError(message) {
    Swal.fire({ icon: 'error', title: 'Error!', text: message });
}

function parseJsonResponse(r) {
    return r.text().then(text => {
        try { return JSON.parse(text); }
        catch (e) { throw new Error(text || 'Server returned invalid response (HTTP ' + r.status + ')'); }
    });
}

function formatTime(t) {
    if (!t) return '—';
    return String(t).substring(0, 5);
}

function loadRules() {
    const tbody = document.querySelector('#rulesTable tbody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-muted">Loading...</td></tr>';

    fetch(apiUrl + 'ajax/hr/get-attendance-rules.php')
        .then(parseJsonResponse)
        .then(res => {
            if (!res.success) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-danger">' + (res.message || 'Failed to load rules') + '</td></tr>';
                return;
            }
            if (!res.data || !res.data.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-muted">No rules configured. Click "Add Rule" to create one.</td></tr>';
                return;
            }
            tbody.innerHTML = res.data.map(r => `
                <tr>
                    <td>${escapeHtml(r.rule_name)}</td>
                    <td>${escapeHtml(r.branch_name || 'Global')}</td>
                    <td>${formatTime(r.work_start_time)} - ${formatTime(r.work_end_time)}</td>
                    <td>${r.grace_period_minutes ?? 0}</td>
                    <td>${escapeHtml(r.weekend_days || '')}</td>
                    <td>${r.is_active == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-rule" data-rule="${encodeURIComponent(JSON.stringify(r))}">Edit</button>
                        <button class="btn btn-sm btn-outline-danger delete-rule" data-id="${r.id}">Delete</button>
                    </td>
                </tr>
            `).join('');
        })
        .catch(err => {
            tbody.innerHTML = '<tr><td colspan="7" class="text-danger">Failed to load rules</td></tr>';
            showError(err.message || 'Failed to load attendance rules');
        });
}

function escapeHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.getElementById('saveRuleBtn').addEventListener('click', function() {
    const payload = {
        id: document.getElementById('ruleId').value,
        rule_name: document.getElementById('ruleName').value,
        branch_id: document.getElementById('ruleBranch').value,
        work_start_time: document.getElementById('workStart').value.length === 5
            ? document.getElementById('workStart').value + ':00'
            : document.getElementById('workStart').value,
        work_end_time: document.getElementById('workEnd').value.length === 5
            ? document.getElementById('workEnd').value + ':00'
            : document.getElementById('workEnd').value,
        break_minutes: document.getElementById('breakMinutes').value,
        grace_period_minutes: document.getElementById('gracePeriod').value,
        half_day_threshold_hours: document.getElementById('halfDayThreshold').value,
        overtime_threshold_minutes: document.getElementById('overtimeThreshold').value,
        weekend_days: document.getElementById('weekendDays').value,
        is_active: document.getElementById('ruleActive').checked ? 1 : 0
    };
    fetch(apiUrl + 'ajax/hr/save-attendance-rule.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(parseJsonResponse)
    .then(res => {
        if (res.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('ruleModal'));
            if (modal) modal.hide();
            document.getElementById('ruleForm').reset();
            document.getElementById('ruleId').value = '';
            showSuccess(res.message || 'Attendance rule saved successfully', loadRules);
        } else {
            showError(res.message || 'Failed to save rule');
        }
    })
    .catch(err => showError(err.message || 'Failed to save rule'));
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-rule')) {
        const r = JSON.parse(decodeURIComponent(e.target.dataset.rule));
        document.getElementById('ruleId').value = r.id;
        document.getElementById('ruleName').value = r.rule_name;
        document.getElementById('ruleBranch').value = r.branch_id || '';
        document.getElementById('workStart').value = r.work_start_time.substring(0, 5);
        document.getElementById('workEnd').value = r.work_end_time.substring(0, 5);
        document.getElementById('breakMinutes').value = r.break_minutes;
        document.getElementById('gracePeriod').value = r.grace_period_minutes;
        document.getElementById('halfDayThreshold').value = r.half_day_threshold_hours;
        document.getElementById('overtimeThreshold').value = r.overtime_threshold_minutes;
        document.getElementById('weekendDays').value = r.weekend_days;
        document.getElementById('ruleActive').checked = r.is_active == 1;
        new bootstrap.Modal(document.getElementById('ruleModal')).show();
    }
    if (e.target.classList.contains('delete-rule')) {
        Swal.fire({
            title: 'Delete Rule?',
            text: 'This attendance rule will be permanently removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (!result.isConfirmed) return;
            fetch(apiUrl + 'ajax/hr/delete-attendance-rule.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: e.target.dataset.id })
            })
            .then(parseJsonResponse)
            .then(res => {
                if (res.success) showSuccess(res.message || 'Rule deleted', loadRules);
                else showError(res.message);
            })
            .catch(err => showError(err.message));
        });
    }
});

// Run immediately after footer scripts
loadRules();
})();
</script>
