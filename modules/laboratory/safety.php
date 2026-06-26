<?php
/**
 * LAB Management - Safety Management
 */

require_once '../../config/config.php';
requireLabRoles(labStaffRoles());

$pageTitle = 'Lab Safety Management';
$currentUser = getCurrentUser();

$incidents  = fetchAll(executeQuery("SELECT i.*, s.section_name, u.username as reporter_name FROM lab_safety_incidents i LEFT JOIN lab_sections s ON i.section_id = s.id LEFT JOIN users u ON i.reported_by = u.id WHERE 1=1" . labBranchWhere('i', null, false) . " ORDER BY i.incident_date DESC"));
$checklists = fetchAll(executeQuery("SELECT c.*, s.section_name, u.username as inspector_name FROM lab_safety_checklists c LEFT JOIN lab_sections s ON c.section_id = s.id LEFT JOIN users u ON c.inspector_id = u.id WHERE 1=1" . labBranchWhere('c', null, false) . " ORDER BY c.inspection_date DESC LIMIT 20"));

$base = "SELECT COUNT(*) as c FROM lab_safety_incidents WHERE 1=1" . labBranchWhere('', null, false);
$sTotal  = fetchOne(executeQuery($base))['c'] ?? 0;
$sOpen   = fetchOne(executeQuery($base . " AND status IN('reported','under_investigation')"))['c'] ?? 0;
$sCrit   = fetchOne(executeQuery($base . " AND severity='critical'"))['c'] ?? 0;
$sClosed = fetchOne(executeQuery($base . " AND status='closed'"))['c'] ?? 0;

$sections   = fetchAll(executeQuery(
    "SELECT id, section_name FROM lab_sections WHERE 1=1" . labBranchWhere('', null, false) . " ORDER BY section_name"
));
$inspectors = getLabInspectors($currentUser);
$defaultChecklistItems = getLabDefaultChecklistItems();
$editIncidentId  = (int)($_GET['edit_incident'] ?? 0);
$editChecklistId = (int)($_GET['edit_checklist'] ?? 0);

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
                            <a href="dashboard.php" class="btn btn-secondary me-1"><i class="ri-arrow-left-line"></i> Dashboard</a>
                            <?php if (hasRole(['Super Admin','Admin','Lab Director','Safety Officer'])): ?>
                            <button class="btn btn-info me-1" data-bs-toggle="modal" data-bs-target="#addChecklistModal">
                                <i class="ri-checkbox-multiple-line"></i> Safety Checklist
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addIncidentModal">
                                <i class="ri-alarm-warning-line"></i> Report Incident
                            </button>
                        </div>
                        <h4 class="page-title">Laboratory Safety Management</h4>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <?php foreach ([
                    ['Total Incidents','primary','ri-file-shield-line',$sTotal],
                    ['Open / Investigating','warning','ri-search-eye-line',$sOpen],
                    ['Critical','danger','ri-alarm-warning-line',$sCrit],
                    ['Closed','success','ri-checkbox-circle-line',$sClosed],
                ] as [$label,$color,$icon,$val]): ?>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-<?php echo $color; ?>-lighten text-<?php echo $color; ?>"><i class="<?php echo $icon; ?> font-24"></i></div>
                                <div class="flex-grow-1 ms-3"><h5 class="mt-0 mb-1 text-muted"><?php echo $label; ?></h5><h2 class="mb-0"><?php echo $val; ?></h2></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#incidentTab">Safety Incidents</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#checklistTab">Safety Checklists</a></li>
            </ul>

            <div class="tab-content">
                <!-- Incidents Tab -->
                <div class="tab-pane fade show active" id="incidentTab">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable-export">
                                    <thead><tr><th>Incident #</th><th>Type</th><th>Date</th><th>Section</th><th>Reporter</th><th>Severity</th><th>Injured</th><th>Status</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($incidents as $inc): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($inc['incident_number']); ?></strong></td>
                                            <td><span class="badge bg-light text-dark"><?php echo ucfirst(str_replace('_',' ',$inc['incident_type'])); ?></span></td>
                                            <td><?php echo formatDate($inc['incident_date']); ?></td>
                                            <td><?php echo htmlspecialchars($inc['section_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($inc['reporter_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php $sv=['minor'=>'success','moderate'=>'warning','serious'=>'danger','critical'=>'dark']; ?>
                                                <span class="badge bg-<?php echo $sv[$inc['severity']] ?? 'secondary'; ?>"><?php echo ucfirst($inc['severity']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($inc['injured_person'] ?? 'None'); ?></td>
                                            <td>
                                                <?php $st=['reported'=>'warning','under_investigation'=>'info','resolved'=>'success','closed'=>'secondary']; ?>
                                                <span class="badge bg-<?php echo $st[$inc['status']] ?? 'secondary'; ?>"><?php echo ucfirst(str_replace('_',' ',$inc['status'])); ?></span>
                                            </td>
                                            <td>
                                                <button onclick="viewIncident(<?php echo $inc['id']; ?>)" class="btn btn-sm btn-info"><i class="ri-eye-line"></i></button>
                                                <?php if (hasRole(['Super Admin','Admin','Lab Director','Safety Officer']) && $inc['status'] !== 'closed'): ?>
                                                <button onclick="updateIncident(<?php echo $inc['id']; ?>)" class="btn btn-sm btn-warning"><i class="ri-edit-line"></i></button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($incidents)): ?><tr><td colspan="9" class="text-center text-muted">No incidents reported</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checklists Tab -->
                <div class="tab-pane fade" id="checklistTab">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable-export">
                                    <thead><tr><th>Checklist Name</th><th>Section</th><th>Date</th><th>Inspector</th><th>Result</th><th>Remarks</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($checklists as $ch): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ch['checklist_name']); ?></td>
                                            <td><?php echo htmlspecialchars($ch['section_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo formatDate($ch['inspection_date']); ?></td>
                                            <td><?php echo htmlspecialchars($ch['inspector_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php $cr=['passed'=>'success','failed'=>'danger','needs_attention'=>'warning']; ?>
                                                <span class="badge bg-<?php echo $cr[$ch['overall_status']] ?? 'secondary'; ?>"><?php echo ucfirst(str_replace('_',' ',$ch['overall_status'])); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($ch['remarks'] ?? '', 0, 50)); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="viewChecklist(<?php echo $ch['id']; ?>)" class="btn btn-sm btn-info" title="View"><i class="ri-eye-line"></i></button>
                                                    <?php if (hasRole(['Super Admin','Admin','Lab Director','Safety Officer'])): ?>
                                                    <button onclick="editChecklist(<?php echo $ch['id']; ?>)" class="btn btn-sm btn-warning" title="Edit"><i class="ri-edit-line"></i></button>
                                                    <button onclick="deleteChecklist(<?php echo $ch['id']; ?>)" class="btn btn-sm btn-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($checklists)): ?><tr><td colspan="7" class="text-center text-muted">No checklists yet</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Report Incident Modal -->
<div class="modal fade" id="addIncidentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title text-danger"><i class="ri-alarm-warning-line me-1"></i> Report Safety Incident</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addIncidentForm">
                <div class="modal-body">
                    <div class="alert alert-danger"><i class="ri-information-line"></i> Report all safety incidents immediately. Do not delay reporting to seek treatment first if needed.</div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label required">Incident Type</label>
                            <select class="form-select" name="incident_type" required>
                                <?php foreach (['accident','near_miss','safety_inspection','hazard_report'] as $t): ?><option value="<?php echo $t; ?>"><?php echo ucfirst(str_replace('_',' ',$t)); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3"><label class="form-label required">Date</label><input type="date" class="form-control" name="incident_date" value="<?php echo date('Y-m-d'); ?>" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Time</label><input type="time" class="form-control" name="incident_time" value="<?php echo date('H:i'); ?>"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Lab Section</label>
                            <select class="form-select" name="section_id">
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Specific Location</label><input type="text" class="form-control" name="location" placeholder="e.g. Bench 3, Storage room"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Injured Person (if any)</label><input type="text" class="form-control" name="injured_person"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Severity</label>
                            <select class="form-select" name="severity">
                                <?php foreach (['minor','moderate','serious','critical'] as $sv): ?><option value="<?php echo $sv; ?>"><?php echo ucfirst($sv); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label required">Description</label><textarea class="form-control" name="description" rows="4" required></textarea></div>
                    <div class="mb-3"><label class="form-label">Treatment Given</label><textarea class="form-control" name="treatment_given" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Corrective Action Taken</label><textarea class="form-control" name="corrective_action" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger"><i class="ri-alarm-warning-line"></i> Submit Report</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Safety Checklist Modal -->
<div class="modal fade" id="addChecklistModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Safety Inspection Checklist</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addChecklistForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3"><label class="form-label required">Checklist Name</label><input type="text" class="form-control" name="checklist_name" required placeholder="e.g. Monthly Safety Inspection - Electrical Lab"></div>
                        <div class="col-md-4 mb-3"><label class="form-label required">Inspection Date</label><input type="date" class="form-control" name="inspection_date" value="<?php echo date('Y-m-d'); ?>" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Lab Section</label>
                            <select class="form-select" name="section_id">
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Inspector</label>
                            <select class="form-select select2-inspector" id="addInspectorSelect" name="inspector_id">
                                <option value="">Search teacher or admin...</option>
                                <?php foreach ($inspectors as $insp): ?><option value="<?php echo $insp['id']; ?>"><?php echo htmlspecialchars($insp['label']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <h6 class="mb-3">Checklist Items</h6>
                    <div id="checklistItems">
                        <?php foreach ($defaultChecklistItems as $item): ?>
                        <div class="d-flex align-items-center mb-2 checklist-row">
                            <div class="flex-grow-1 me-2"><input type="text" class="form-control form-control-sm" name="check_items[]" value="<?php echo htmlspecialchars($item); ?>"></div>
                            <div class="me-2">
                                <select class="form-select form-select-sm" name="check_results[]" style="width:120px">
                                    <option value="pass">Pass</option>
                                    <option value="fail">Fail</option>
                                    <option value="na">N/A</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-check-item" title="Remove"><i class="ri-delete-bin-line"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addChecklistItemBtn"><i class="ri-add-line"></i> Add Check Item</button>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3"><label class="form-label">Overall Result</label>
                            <select class="form-select" name="overall_status">
                                <option value="passed">Passed</option>
                                <option value="needs_attention">Needs Attention</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-info"><i class="ri-save-line"></i> Save Checklist</button></div>
            </form>
        </div>
    </div>
</div>

<!-- View Checklist Modal -->
<div class="modal fade" id="viewChecklistModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewChecklistTitle">Checklist Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewChecklistBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <?php if (hasRole(['Super Admin','Admin','Lab Director','Safety Officer'])): ?>
                <button type="button" class="btn btn-warning" id="viewChecklistEditBtn"><i class="ri-edit-line"></i> Edit</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- View Incident Modal -->
<div class="modal fade" id="viewIncidentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Incident Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="viewIncidentBody"></div>
        </div>
    </div>
</div>

<!-- Edit Incident Modal (full expert form) -->
<div class="modal fade" id="editIncidentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="ri-edit-line me-1"></i> Edit Safety Incident</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="editIncidentForm">
                <input type="hidden" name="id" id="editIncidentId">
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-md-6"><label class="form-label text-muted">Incident #</label><input type="text" class="form-control" id="editIncidentNumber" readonly></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Status</label>
                            <select class="form-select" name="status" id="editIncidentStatus">
                                <?php foreach (['reported','under_investigation','resolved','closed'] as $s): ?><option value="<?php echo $s; ?>"><?php echo ucfirst(str_replace('_',' ',$s)); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label required">Incident Type</label>
                            <select class="form-select" name="incident_type" id="editIncidentType" required>
                                <?php foreach (['accident','near_miss','safety_inspection','hazard_report'] as $t): ?><option value="<?php echo $t; ?>"><?php echo ucfirst(str_replace('_',' ',$t)); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3"><label class="form-label required">Date</label><input type="date" class="form-control" name="incident_date" id="editIncidentDate" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Time</label><input type="time" class="form-control" name="incident_time" id="editIncidentTime"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Lab Section</label>
                            <select class="form-select" name="section_id" id="editIncidentSection">
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Specific Location</label><input type="text" class="form-control" name="location" id="editIncidentLocation"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Injured Person (if any)</label><input type="text" class="form-control" name="injured_person" id="editIncidentInjured"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Severity</label>
                            <select class="form-select" name="severity" id="editIncidentSeverity">
                                <?php foreach (['minor','moderate','serious','critical'] as $sv): ?><option value="<?php echo $sv; ?>"><?php echo ucfirst($sv); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label required">Description</label><textarea class="form-control" name="description" id="editIncidentDescription" rows="4" required></textarea></div>
                    <div class="mb-3"><label class="form-label">Treatment Given</label><textarea class="form-control" name="treatment_given" id="editIncidentTreatment" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Corrective Action Taken</label><textarea class="form-control" name="corrective_action" id="editIncidentCorrective" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Update Incident</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Checklist Modal -->
<div class="modal fade" id="editChecklistModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="ri-edit-line me-1"></i> Edit Safety Checklist</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="editChecklistForm">
                <input type="hidden" name="id" id="editChecklistId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3"><label class="form-label required">Checklist Name</label><input type="text" class="form-control" name="checklist_name" id="editChecklistName" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label required">Inspection Date</label><input type="date" class="form-control" name="inspection_date" id="editChecklistDate" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Lab Section</label>
                            <select class="form-select" name="section_id" id="editChecklistSection">
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Inspector</label>
                            <select class="form-select select2-inspector" id="editInspectorSelect" name="inspector_id">
                                <option value="">Search teacher or admin...</option>
                                <?php foreach ($inspectors as $insp): ?><option value="<?php echo $insp['id']; ?>"><?php echo htmlspecialchars($insp['label']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <h6 class="mb-3">Checklist Items</h6>
                    <div id="editChecklistItems"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="editAddChecklistItemBtn"><i class="ri-add-line"></i> Add Check Item</button>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3"><label class="form-label">Overall Result</label>
                            <select class="form-select" name="overall_status" id="editChecklistOverall">
                                <option value="passed">Passed</option>
                                <option value="needs_attention">Needs Attention</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" id="editChecklistRemarks" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-info"><i class="ri-save-line"></i> Update Checklist</button></div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
function initInspectorSelect($el, $modal) {
    if (!$el.length || !$.fn.select2) return;
    if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
    $el.select2({
        dropdownParent: $modal,
        placeholder: 'Search teacher or admin...',
        allowClear: true,
        width: '100%'
    });
}

$('#addChecklistModal').on('shown.bs.modal', function() {
    initInspectorSelect($('#addInspectorSelect'), $('#addChecklistModal'));
});
$('#editChecklistModal').on('shown.bs.modal', function() {
    initInspectorSelect($('#editInspectorSelect'), $('#editChecklistModal'));
});

function checklistRowHtml(item, result) {
    item = item || '';
    result = result || 'pass';
    return `<div class="d-flex align-items-center mb-2 checklist-row">
        <div class="flex-grow-1 me-2"><input type="text" class="form-control form-control-sm" name="check_items[]" value="${$('<div>').text(item).html()}"></div>
        <div class="me-2"><select class="form-select form-select-sm" name="check_results[]" style="width:120px">
            <option value="pass"${result==='pass'?' selected':''}>Pass</option>
            <option value="fail"${result==='fail'?' selected':''}>Fail</option>
            <option value="na"${result==='na'?' selected':''}>N/A</option>
        </select></div>
        <button type="button" class="btn btn-sm btn-outline-danger remove-check-item" title="Remove"><i class="ri-delete-bin-line"></i></button>
    </div>`;
}

$('#addChecklistItemBtn').on('click', function() {
    $('#checklistItems').append(checklistRowHtml('', 'pass'));
});
$('#editAddChecklistItemBtn').on('click', function() {
    $('#editChecklistItems').append(checklistRowHtml('', 'pass'));
});
$(document).on('click', '.remove-check-item', function() {
    $(this).closest('.checklist-row').remove();
});

$('#addIncidentForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-safety-incident.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addIncidentModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

$('#addChecklistForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-checklist.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addChecklistModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function viewIncident(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-incident.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                $('#viewIncidentBody').html(`
                    <table class="table table-bordered table-sm">
                        <tr><th>Incident #</th><td>${d.incident_number}</td><th>Status</th><td>${d.status}</td></tr>
                        <tr><th>Type</th><td>${d.incident_type}</td><th>Date</th><td>${d.incident_date}</td></tr>
                        <tr><th>Section</th><td>${d.section_name||'N/A'}</td><th>Location</th><td>${d.location||'N/A'}</td></tr>
                        <tr><th>Severity</th><td>${d.severity}</td><th>Injured Person</th><td>${d.injured_person||'None'}</td></tr>
                        <tr><th colspan="4">Description</th></tr>
                        <tr><td colspan="4">${d.description||'N/A'}</td></tr>
                        <tr><th colspan="4">Treatment Given</th></tr>
                        <tr><td colspan="4">${d.treatment_given||'N/A'}</td></tr>
                        <tr><th colspan="4">Corrective Action</th></tr>
                        <tr><td colspan="4">${d.corrective_action||'N/A'}</td></tr>
                    </table>`);
                $('#viewIncidentModal').modal('show');
            }
        }
    });
}

function updateIncident(id) { editIncident(id); }

function editIncident(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-incident.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(!r.success){ showToast(r.message || 'Incident not found','error'); return; }
            var d = r.data;
            $('#editIncidentId').val(d.id);
            $('#editIncidentNumber').val(d.incident_number || '');
            $('#editIncidentStatus').val(d.status || 'reported');
            $('#editIncidentType').val(d.incident_type || 'accident');
            $('#editIncidentDate').val(d.incident_date || '');
            $('#editIncidentTime').val(d.incident_time ? d.incident_time.substring(0,5) : '');
            $('#editIncidentSection').val(d.section_id || '');
            $('#editIncidentLocation').val(d.location || '');
            $('#editIncidentInjured').val(d.injured_person || '');
            $('#editIncidentSeverity').val(d.severity || 'minor');
            $('#editIncidentDescription').val(d.description || '');
            $('#editIncidentTreatment').val(d.treatment_given || '');
            $('#editIncidentCorrective').val(d.corrective_action || '');
            $('#editIncidentModal').modal('show');
        },
        error:function(){ showToast('Failed to load incident','error'); }
    });
}

$('#editIncidentForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/edit-safety-incident.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#editIncidentModal').modal('hide');setTimeout(()=>location.href='<?php echo APP_URL; ?>modules/laboratory/safety.php',1200);}else showToast(r.message,'error');}
    });
});

function viewChecklist(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-checklist.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(!r.success){ showToast(r.message || 'Checklist not found','error'); return; }
            var d = r.data;
            var resultBadge = {passed:'success', failed:'danger', needs_attention:'warning'};
            var itemBadge = {pass:'success', fail:'danger', na:'secondary'};
            var itemRows = (d.items || []).map(function(pair) {
                var label = pair[0] || '';
                var res = pair[1] || 'pass';
                var badge = itemBadge[res] || 'secondary';
                return `<tr><td>${label}</td><td><span class="badge bg-${badge}">${res.toUpperCase()}</span></td></tr>`;
            }).join('');
            if (!itemRows) itemRows = '<tr><td colspan="2" class="text-muted text-center">No checklist items</td></tr>';

            $('#viewChecklistTitle').text(d.checklist_name || 'Checklist Details');
            $('#viewChecklistBody').html(`
                <table class="table table-bordered table-sm mb-3">
                    <tr><th>Section</th><td>${d.section_name||'N/A'}</td><th>Inspection Date</th><td>${d.inspection_date||'N/A'}</td></tr>
                    <tr><th>Inspector</th><td>${d.inspector_name||'N/A'}</td><th>Overall Result</th><td><span class="badge bg-${resultBadge[d.overall_status]||'secondary'}">${(d.overall_status||'').replace(/_/g,' ')}</span></td></tr>
                    <tr><th colspan="4">Remarks</th></tr>
                    <tr><td colspan="4">${d.remarks||'N/A'}</td></tr>
                </table>
                <h6 class="mb-2">Checklist Items</h6>
                <table class="table table-sm table-striped table-bordered">
                    <thead><tr><th>Item</th><th style="width:100px">Result</th></tr></thead>
                    <tbody>${itemRows}</tbody>
                </table>`);
            $('#viewChecklistEditBtn').off('click').on('click', function() {
                $('#viewChecklistModal').modal('hide');
                editChecklist(id);
            });
            $('#viewChecklistModal').modal('show');
        },
        error:function(){ showToast('Failed to load checklist','error'); }
    });
}

function deleteChecklist(id) {
    confirmAction('Delete this safety checklist? This cannot be undone.', function() {
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/delete-checklist.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}

function editChecklist(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-checklist.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(!r.success){ showToast(r.message || 'Checklist not found','error'); return; }
            var d = r.data;
            $('#editChecklistId').val(d.id);
            $('#editChecklistName').val(d.checklist_name || '');
            $('#editChecklistDate').val(d.inspection_date || '');
            $('#editChecklistSection').val(d.section_id || '');
            $('#editInspectorSelect').val(d.inspector_id || '');
            $('#editChecklistOverall').val(d.overall_status || 'passed');
            $('#editChecklistRemarks').val(d.remarks || '');
            var html = '';
            (d.items || []).forEach(function(pair) {
                html += checklistRowHtml(pair[0] || '', pair[1] || 'pass');
            });
            if (!html) html = checklistRowHtml('', 'pass');
            $('#editChecklistItems').html(html);
            $('#editChecklistModal').modal('show');
            setTimeout(function() { $('#editInspectorSelect').val(d.inspector_id || '').trigger('change'); }, 150);
        },
        error:function(){ showToast('Failed to load checklist','error'); }
    });
}

$('#editChecklistForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/edit-checklist.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#editChecklistModal').modal('hide');setTimeout(()=>location.href='<?php echo APP_URL; ?>modules/laboratory/safety.php',1200);}else showToast(r.message,'error');}
    });
});

<?php if ($editIncidentId > 0): ?>
$(function(){ editIncident(<?php echo $editIncidentId; ?>); });
<?php endif; ?>
<?php if ($editChecklistId > 0): ?>
$(function(){ editChecklist(<?php echo $editChecklistId; ?>); });
<?php endif; ?>
</script>
