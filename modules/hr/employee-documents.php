<?php

require_once '../../config/config.php';

hrRequirePage('hr_payroll', 'view');

$pageTitle = 'Employee Documents';

$staff = fetchAll(executeQuery("SELECT id, staff_id, first_name, last_name FROM staff WHERE status='Active' ORDER BY first_name"));

$preselectStaff = (int)($_GET['staff_id'] ?? 0);

include '../../includes/header.php';

include '../../includes/sidebar.php';

?>

<div class="content-page"><div class="content"><div class="container-fluid">

<div class="row"><div class="col-12"><div class="page-title-box">

<div class="page-title-right"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#docModal"><i class="ri-upload-line"></i> Upload Documents</button></div>

<h4 class="page-title">Employee Documents</h4></div></div></div>

<div class="row mb-3"><div class="col-md-4">

<select class="form-select" id="filterStaff"><option value="">All Staff</option>

<?php foreach ($staff as $s): ?><option value="<?php echo $s['id']; ?>"<?php echo ($preselectStaff === (int)$s['id']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($s['staff_id'].' - '.$s['first_name'].' '.$s['last_name']); ?></option><?php endforeach; ?>

</select></div></div>

<div class="card"><div class="card-body"><div class="table-responsive">

<table class="table table-hover" id="dataTable"><thead><tr><th>Staff</th><th>Type</th><th>Name</th><th>Expiry</th><th>Verified</th><th>File</th></tr></thead><tbody></tbody></table>

</div></div></div>

</div></div></div>



<div class="modal fade" id="docModal"><div class="modal-dialog modal-lg"><div class="modal-content">

<div class="modal-header"><h5 class="modal-title"><i class="ri-folder-upload-line"></i> Upload Documents</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

<div class="modal-body">

<div class="mb-3"><label class="form-label">Staff <span class="text-danger">*</span></label>

<select id="docStaffSelect" class="form-select" required>

<option value="">Select staff member</option>

<?php foreach ($staff as $s): ?><option value="<?php echo $s['id']; ?>"<?php echo ($preselectStaff === (int)$s['id']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($s['staff_id'].' - '.$s['first_name'].' '.$s['last_name']); ?></option><?php endforeach; ?>

</select></div>

<div class="d-flex justify-content-between align-items-center mb-2">

<span class="text-muted small">Add one or more documents, then upload.</span>

<button type="button" class="btn btn-sm btn-outline-primary" id="addDocRowBtn"><i class="ri-add-line"></i> Add Document</button>

</div>

<div id="docsUploadRows"></div>

<div id="docsUploadEmpty" class="text-center text-muted py-4 border rounded bg-light">

<i class="ri-file-upload-line font-24 d-block mb-2"></i>No documents added. Click <strong>Add Document</strong>.

</div>

</div>

<div class="modal-footer">

<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

<button type="button" class="btn btn-primary" id="uploadDocsBtn" disabled><i class="ri-upload-line"></i> Upload</button>

</div>

</div></div></div>



<?php include '../../includes/footer.php'; ?>

<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>

<script>

(function(){

    var H = window.HrModule;

    var DOC_TYPES = ['ID Copy','Contract','Certificate','Medical','Resume','License','Passport','Other'];



    function updateUi(){

        var rows = document.querySelectorAll('#docsUploadRows .doc-upload-row');

        document.getElementById('docsUploadEmpty').style.display = rows.length ? 'none' : 'block';

        var ready = false;

        rows.forEach(function(r){

            var f = r.querySelector('.doc-file-input');

            if (f && f.files && f.files.length) ready = true;

        });

        document.getElementById('uploadDocsBtn').disabled = !ready;

    }



    function addRow(){

        var row = document.createElement('div');

        row.className = 'doc-upload-row border rounded p-3 mb-2';

        var opts = DOC_TYPES.map(function(t){ return '<option value="'+t+'">'+t+'</option>'; }).join('');

        row.innerHTML =

            '<div class="d-flex justify-content-between mb-2"><span class="badge bg-light text-dark">Document</span>'+

            '<button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-doc"><i class="ri-close-circle-line font-18"></i></button></div>'+

            '<div class="row g-2"><div class="col-md-3"><label class="form-label small">Type</label>'+

            '<select class="form-select form-select-sm doc-type-select">'+opts+'</select></div>'+

            '<div class="col-md-3"><label class="form-label small">Name</label>'+

            '<input type="text" class="form-control form-control-sm doc-name-input" placeholder="Title"></div>'+

            '<div class="col-md-2"><label class="form-label small">Expiry</label>'+

            '<input type="date" class="form-control form-control-sm doc-expiry-input"></div>'+

            '<div class="col-md-4"><label class="form-label small">File</label>'+

            '<input type="file" class="form-control form-control-sm doc-file-input" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"></div></div>';

        document.getElementById('docsUploadRows').appendChild(row);

        row.querySelector('.doc-type-select').onchange = function(){

            var n = row.querySelector('.doc-name-input');

            if (!n.value.trim()) n.value = this.value;

        };

        row.querySelector('.doc-file-input').onchange = updateUi;

        row.querySelector('.btn-remove-doc').onclick = function(){ row.remove(); updateUi(); };

        updateUi();

    }



    function resetRows(){

        document.getElementById('docsUploadRows').innerHTML = '';

        updateUi();

    }



    function load(){

        var sid = document.getElementById('filterStaff').value;

        H.get('ajax/hr/get-employee-documents.php' + (sid ? '?staff_id='+sid : '')).then(function(res){

            var tb = document.querySelector('#dataTable tbody');

            if(!res.success||!res.data.length){ tb.innerHTML='<tr><td colspan="6" class="text-muted">No documents</td></tr>'; return; }

            tb.innerHTML = res.data.map(function(d){

                return '<tr><td>'+H.escapeHtml(d.staff_code||d.staff_id)+'</td><td>'+H.escapeHtml(d.document_type)+'</td><td>'+H.escapeHtml(d.document_name)+'</td><td>'+H.formatDate(d.expiry_date)+'</td><td>'+(d.is_verified==1?'Yes':'No')+'</td><td><a href="'+H.apiUrl()+d.file_path+'" target="_blank">View</a></td></tr>';

            }).join('');

        });

    }



    document.getElementById('filterStaff').addEventListener('change', load);

    document.getElementById('addDocRowBtn').addEventListener('click', addRow);

    document.getElementById('docModal').addEventListener('show.bs.modal', resetRows);

    document.getElementById('uploadDocsBtn').addEventListener('click', function(){

        var staffId = document.getElementById('docStaffSelect').value;

        if (!staffId){ H.error('Select a staff member'); return; }

        var rows = document.querySelectorAll('#docsUploadRows .doc-upload-row');

        var fd = new FormData();

        fd.append('staff_id', staffId);

        var count = 0;

        rows.forEach(function(row){

            var file = row.querySelector('.doc-file-input');

            if (!file.files || !file.files.length) return;

            var type = row.querySelector('.doc-type-select').value;

            var name = row.querySelector('.doc-name-input').value.trim() || type;

            fd.append('doc_type[]', type);

            fd.append('doc_name[]', name);

            fd.append('doc_expiry[]', row.querySelector('.doc-expiry-input').value);

            fd.append('doc_file[]', file.files[0]);

            count++;

        });

        if (!count){ H.error('Add at least one file'); return; }

        fetch(H.apiUrl()+'ajax/hr/save-employee-documents-batch.php', { method:'POST', body: fd })

            .then(H.parseJson).then(function(res){

                if(res.success){

                    bootstrap.Modal.getInstance(document.getElementById('docModal')).hide();

                    H.success(res.message, load);

                } else H.error(res.message);

            });

    });

    load();

})();

</script>


