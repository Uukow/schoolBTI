<?php
/**
 * LAB Management - Practical Experiments Management
 */

require_once '../../config/config.php';
requireLabRoles(labParticipantRoles());

$pageTitle = 'Practical Experiments';
$currentUser = getCurrentUser();

$branchFilter = labBranchWhere('e', null, false);

$sql = "SELECT e.*, s.section_name, u.username as instructor_name
        FROM lab_experiments e
        LEFT JOIN lab_sections s ON e.section_id = s.id
        LEFT JOIN users u ON e.instructor_id = u.id
        WHERE 1=1" . $branchFilter . " ORDER BY e.experiment_title";
$experiments = fetchAll(executeQuery($sql));

$sessFilter = labBranchWhere('ss', null, false);
$sessSQL = "SELECT ss.*, e.experiment_title, s.section_name, u.username as instructor_name
            FROM lab_experiment_sessions ss
            LEFT JOIN lab_experiments e ON ss.experiment_id = e.id
            LEFT JOIN lab_sections s ON ss.section_id = s.id
            LEFT JOIN users u ON ss.instructor_id = u.id
            WHERE 1=1" . $sessFilter . " ORDER BY ss.session_date DESC, ss.start_time LIMIT 30";
$sessions = fetchAll(executeQuery($sessSQL));

$sections  = fetchAll(executeQuery(
    "SELECT id, section_name FROM lab_sections WHERE 1=1" . labBranchWhere('', null, false) . " ORDER BY section_name"
));
$teachers = getLabTeachers($currentUser);
$editId   = (int)($_GET['edit'] ?? 0);

// Stats
$branchCond = labBranchWhere('', null, false);
$totalExp     = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_experiments WHERE 1=1" . $branchCond))['c'] ?? 0;
$activeExp    = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_experiments WHERE status='active'" . $branchCond))['c'] ?? 0;
$upcomingSess = fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_experiment_sessions WHERE session_date >= CURDATE() AND status='scheduled'" . $branchCond))['c'] ?? 0;
$completedSess= fetchOne(executeQuery("SELECT COUNT(*) as c FROM lab_experiment_sessions WHERE status='completed'" . $branchCond))['c'] ?? 0;

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
                            <?php if (hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Teacher'])): ?>
                            <button class="btn btn-success me-1" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                                <i class="ri-calendar-add-line"></i> Schedule Session
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExperimentModal">
                                <i class="ri-add-line"></i> Add Experiment
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Practical Experiments Management</h4>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <?php foreach ([
                    ['Total Experiments','primary','ri-flask-line',$totalExp],
                    ['Active','success','ri-checkbox-circle-line',$activeExp],
                    ['Upcoming Sessions','info','ri-calendar-event-line',$upcomingSess],
                    ['Completed Sessions','secondary','ri-check-double-line',$completedSess],
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
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#expTab">Experiments</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sessTab">Sessions / Schedule</a></li>
            </ul>

            <div class="tab-content">
                <!-- Experiments Tab -->
                <div class="tab-pane fade show active" id="expTab">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable-export">
                                    <thead>
                                        <tr><th>Code</th><th>Title</th><th>Category</th><th>Section</th><th>Difficulty</th><th>Instructor</th><th>Duration</th><th>Status</th><th>Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($experiments as $exp): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($exp['experiment_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($exp['experiment_title']); ?></td>
                                            <td><?php echo htmlspecialchars($exp['category'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($exp['section_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php $dc=['beginner'=>'success','intermediate'=>'warning','advanced'=>'danger']; ?>
                                                <span class="badge bg-<?php echo $dc[$exp['difficulty_level']] ?? 'secondary'; ?>"><?php echo ucfirst($exp['difficulty_level']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($exp['instructor_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo $exp['duration_hours'] ? $exp['duration_hours'] . ' hrs' : 'N/A'; ?></td>
                                            <td>
                                                <?php $sc=['draft'=>'secondary','active'=>'success','inactive'=>'danger']; ?>
                                                <span class="badge bg-<?php echo $sc[$exp['status']] ?? 'secondary'; ?>"><?php echo ucfirst($exp['status']); ?></span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="viewExperiment(<?php echo $exp['id']; ?>)" class="btn btn-sm btn-info"><i class="ri-eye-line"></i></button>
                                                    <?php if (hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Teacher'])): ?>
                                                    <button onclick="editExperiment(<?php echo $exp['id']; ?>)" class="btn btn-sm btn-warning"><i class="ri-edit-line"></i></button>
                                                    <button onclick="deleteExperiment(<?php echo $exp['id']; ?>)" class="btn btn-sm btn-danger"><i class="ri-delete-bin-line"></i></button>
                                                    <?php endif; ?>
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

                <!-- Sessions Tab -->
                <div class="tab-pane fade" id="sessTab">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable-export">
                                    <thead>
                                        <tr><th>Date</th><th>Experiment</th><th>Section</th><th>Time</th><th>Instructor</th><th>Students</th><th>Status</th><th>Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sessions as $sess): ?>
                                        <tr>
                                            <td><?php echo formatDate($sess['session_date']); ?></td>
                                            <td><?php echo htmlspecialchars($sess['experiment_title'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($sess['section_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo substr($sess['start_time'],0,5) . ' - ' . substr($sess['end_time'],0,5); ?></td>
                                            <td><?php echo htmlspecialchars($sess['instructor_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo $sess['student_count']; ?></td>
                                            <td>
                                                <?php $sc2=['scheduled'=>'primary','in_progress'=>'warning','completed'=>'success','cancelled'=>'danger']; ?>
                                                <span class="badge bg-<?php echo $sc2[$sess['status']] ?? 'secondary'; ?>"><?php echo ucfirst(str_replace('_',' ',$sess['status'])); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($sess['status'] === 'scheduled' && hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Teacher'])): ?>
                                                <button onclick="updateSession(<?php echo $sess['id']; ?>,'in_progress')" class="btn btn-sm btn-warning" title="Start"><i class="ri-play-line"></i></button>
                                                <button onclick="updateSession(<?php echo $sess['id']; ?>,'cancelled')" class="btn btn-sm btn-danger" title="Cancel"><i class="ri-close-line"></i></button>
                                                <?php endif; ?>
                                                <?php if ($sess['status'] === 'in_progress' && hasRole(['Super Admin','Admin','Lab Director','Lab Manager','Teacher'])): ?>
                                                <button onclick="updateSession(<?php echo $sess['id']; ?>,'completed')" class="btn btn-sm btn-success" title="Complete"><i class="ri-check-line"></i></button>
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

        </div>
    </div>
</div>

<!-- Add Experiment Modal -->
<div class="modal fade" id="addExperimentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Practical Experiment</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addExperimentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label required">Experiment Title</label><input type="text" class="form-control" name="experiment_title" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Code</label><input type="text" class="form-control" name="experiment_code" placeholder="Auto-generated"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Category</label><input type="text" class="form-control" name="category" placeholder="e.g. Circuits, Titration"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Lab Section</label><select class="form-select" name="section_id"><option value="">Select</option><?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Instructor</label><select class="form-select select2-teacher" id="addInstructorSelect" name="instructor_id"><option value="">Search teacher...</option><?php foreach ($teachers as $t): ?><option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['label']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-2 mb-3"><label class="form-label">Duration (hrs)</label><input type="number" class="form-control" name="duration_hours" step="0.5" min="0.5"></div>
                        <div class="col-md-2 mb-3"><label class="form-label">Difficulty</label><select class="form-select" name="difficulty_level"><option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option></select></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Objectives</label><textarea class="form-control" name="objectives" rows="3"></textarea></div>
                    <div class="mb-3"><label class="form-label">Experiment Instructions</label><textarea class="form-control" name="instructions" rows="5"></textarea></div>
                    <div class="mb-3"><label class="form-label">Safety Guidelines</label><textarea class="form-control" name="safety_guidelines" rows="3"></textarea></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Required Materials</label><textarea class="form-control" name="required_materials" rows="3" placeholder="List materials needed"></textarea></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Required Equipment</label><textarea class="form-control" name="required_equipment" rows="3" placeholder="List equipment needed"></textarea></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="draft">Draft</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Add Session Modal -->
<div class="modal fade" id="addSessionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Schedule Experiment Session</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addSessionForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Experiment</label>
                            <select class="form-select" name="experiment_id" required>
                                <option value="">Select Experiment</option>
                                <?php foreach ($experiments as $exp): ?>
                                <option value="<?php echo $exp['id']; ?>"><?php echo htmlspecialchars($exp['experiment_title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lab Section</label>
                            <select class="form-select" name="section_id">
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label required">Session Date</label><input type="date" class="form-control" name="session_date" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label required">Start Time</label><input type="time" class="form-control" name="start_time" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label required">End Time</label><input type="time" class="form-control" name="end_time" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Instructor</label>
                            <select class="form-select select2-teacher" id="sessionInstructorSelect" name="instructor_id">
                                <option value="">Search teacher...</option>
                                <?php foreach ($teachers as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3"><label class="form-label">Student Count</label><input type="number" class="form-control" name="student_count" min="1" value="20"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Class/Group</label><input type="text" class="form-control" name="class_group"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-success"><i class="ri-calendar-check-line"></i> Schedule</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Experiment Modal -->
<div class="modal fade" id="editExperimentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Practical Experiment</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="editExperimentForm">
                <input type="hidden" name="id" id="editExperimentId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label required">Experiment Title</label><input type="text" class="form-control" name="experiment_title" id="editExperimentTitle" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label required">Code</label><input type="text" class="form-control" name="experiment_code" id="editExperimentCode" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Category</label><input type="text" class="form-control" name="category" id="editCategory" placeholder="e.g. Circuits, Titration"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Lab Section</label><select class="form-select" name="section_id" id="editSectionId"><option value="">Select</option><?php foreach ($sections as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Instructor</label><select class="form-select select2-teacher" id="editInstructorSelect" name="instructor_id"><option value="">Search teacher...</option><?php foreach ($teachers as $t): ?><option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['label']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-2 mb-3"><label class="form-label">Duration (hrs)</label><input type="number" class="form-control" name="duration_hours" id="editDurationHours" step="0.5" min="0.5"></div>
                        <div class="col-md-2 mb-3"><label class="form-label">Difficulty</label><select class="form-select" name="difficulty_level" id="editDifficultyLevel"><option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option></select></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" id="editDescription" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Objectives</label><textarea class="form-control" name="objectives" id="editObjectives" rows="3"></textarea></div>
                    <div class="mb-3"><label class="form-label">Experiment Instructions</label><textarea class="form-control" name="instructions" id="editInstructions" rows="5"></textarea></div>
                    <div class="mb-3"><label class="form-label">Safety Guidelines</label><textarea class="form-control" name="safety_guidelines" id="editSafetyGuidelines" rows="3"></textarea></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Required Materials</label><textarea class="form-control" name="required_materials" id="editRequiredMaterials" rows="3"></textarea></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Required Equipment</label><textarea class="form-control" name="required_equipment" id="editRequiredEquipment" rows="3"></textarea></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status" id="editStatus"><option value="draft">Draft</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Update</button></div>
            </form>
        </div>
    </div>
</div>

<!-- View Experiment Modal -->
<div class="modal fade" id="viewExperimentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="viewExpTitle">Experiment Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="viewExpBody"></div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
function initTeacherSelect($el, $modal) {
    if (!$el.length || !$.fn.select2) return;
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }
    $el.select2({
        dropdownParent: $modal,
        placeholder: 'Search teacher by name...',
        allowClear: true,
        width: '100%'
    });
}

$('#addExperimentModal').on('shown.bs.modal', function() {
    initTeacherSelect($('#addInstructorSelect'), $('#addExperimentModal'));
});

$('#addSessionModal').on('shown.bs.modal', function() {
    initTeacherSelect($('#sessionInstructorSelect'), $('#addSessionModal'));
});

$('#editExperimentModal').on('shown.bs.modal', function() {
    initTeacherSelect($('#editInstructorSelect'), $('#editExperimentModal'));
});

$('#addExperimentForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-experiment.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addExperimentModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

$('#editExperimentForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/edit-experiment.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#editExperimentModal').modal('hide');setTimeout(()=>location.href='<?php echo APP_URL; ?>modules/laboratory/experiments.php',1200);}else showToast(r.message,'error');}
    });
});

$('#addSessionForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/add-session.php', type:'POST', data:$(this).serialize(), dataType:'json',
        success:function(r){if(r.success){showToast(r.message,'success');$('#addSessionModal').modal('hide');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
    });
});

function viewExperiment(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-experiment.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(r.success){
                var d=r.data;
                $('#viewExpTitle').text(d.experiment_title);
                $('#viewExpBody').html(`
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Code:</strong> ${d.experiment_code}</div>
                        <div class="col-md-4"><strong>Section:</strong> ${d.section_name||'N/A'}</div>
                        <div class="col-md-4"><strong>Difficulty:</strong> ${d.difficulty_level}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Instructor:</strong> ${d.instructor_name||'N/A'}</div>
                        <div class="col-md-4"><strong>Duration:</strong> ${d.duration_hours||'N/A'} hrs</div>
                        <div class="col-md-4"><strong>Status:</strong> ${d.status}</div>
                    </div>
                    <hr>
                    <div class="mb-2"><strong>Objectives:</strong><p>${d.objectives||'N/A'}</p></div>
                    <div class="mb-2"><strong>Instructions:</strong><p style="white-space:pre-line">${d.instructions||'N/A'}</p></div>
                    <div class="mb-2"><strong>Required Materials:</strong><p>${d.required_materials||'N/A'}</p></div>
                    <div class="mb-2"><strong>Required Equipment:</strong><p>${d.required_equipment||'N/A'}</p></div>
                    <div class="mb-2"><strong>Safety Guidelines:</strong><p class="text-danger">${d.safety_guidelines||'N/A'}</p></div>`);
                $('#viewExperimentModal').modal('show');
            }
        }
    });
}

function deleteExperiment(id) {
    confirmAction('Delete this experiment?', function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/delete-experiment.php', type:'POST', data:{id:id}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}

function editExperiment(id) {
    $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/get-experiment.php', type:'GET', data:{id:id}, dataType:'json',
        success:function(r){
            if(!r.success){ showToast(r.message || 'Experiment not found','error'); return; }
            var d = r.data;
            $('#editExperimentId').val(d.id);
            $('#editExperimentTitle').val(d.experiment_title || '');
            $('#editExperimentCode').val(d.experiment_code || '');
            $('#editCategory').val(d.category || '');
            $('#editSectionId').val(d.section_id || '');
            $('#editInstructorSelect').val(d.instructor_id || '');
            $('#editDurationHours').val(d.duration_hours || '');
            $('#editDifficultyLevel').val(d.difficulty_level || 'beginner');
            $('#editDescription').val(d.description || '');
            $('#editObjectives').val(d.objectives || '');
            $('#editInstructions').val(d.instructions || '');
            $('#editSafetyGuidelines').val(d.safety_guidelines || '');
            $('#editRequiredMaterials').val(d.required_materials || '');
            $('#editRequiredEquipment').val(d.required_equipment || '');
            $('#editStatus').val(d.status || 'draft');
            $('#editExperimentModal').modal('show');
            setTimeout(function() {
                $('#editInstructorSelect').val(d.instructor_id || '').trigger('change');
            }, 150);
        },
        error:function(){ showToast('Failed to load experiment','error'); }
    });
}

<?php if ($editId > 0): ?>
$(function(){ editExperiment(<?php echo $editId; ?>); });
<?php endif; ?>

function updateSession(id, status) {
    var msg = status === 'completed' ? 'Mark session as completed?' : (status === 'cancelled' ? 'Cancel this session?' : 'Start this session?');
    confirmAction(msg, function(){
        $.ajax({ url:'<?php echo APP_URL; ?>ajax/laboratory/update-session.php', type:'POST', data:{id:id, status:status}, dataType:'json',
            success:function(r){if(r.success){showToast(r.message,'success');setTimeout(()=>location.reload(),1200);}else showToast(r.message,'error');}
        });
    });
}
</script>
