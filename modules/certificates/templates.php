<?php
/**
 * Certificate Templates Management
 * 
 * Configure certificate templates with customizable fields
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Certificate Templates';

$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$branchId = $isSuperAdmin ? ($_GET['branch_id'] ?? null) : $currentUser['branch_id'];

// Get certificate templates
$sql = "SELECT ct.*, b.branch_name,
        (SELECT COUNT(*) FROM certificates c WHERE c.template_id = ct.id) as usage_count
        FROM certificate_templates ct
        LEFT JOIN branches b ON ct.branch_id = b.id
        WHERE 1=1";

if ($branchId) {
    $sql .= " AND ct.branch_id = " . intval($branchId);
}
$sql .= " ORDER BY ct.is_default DESC, ct.created_at DESC";

$templates = fetchAll(executeQuery($sql));

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

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
                            <button type="button" class="btn btn-info me-2" onclick="createProfessionalTemplate()">
                                <i class="ri-file-certificate-line"></i> Professional Template
                            </button>
                            <a href="template-builder.php" class="btn btn-success me-2">
                                <i class="ri-brush-line"></i> Design New Template
                            </a>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                                <i class="ri-add-line"></i> Add Template
                            </button>
                        </div>
                        <h4 class="page-title">Certificate Templates</h4>
                    </div>
                </div>
            </div>

            <?php if ($isSuperAdmin): ?>
            <!-- Branch Filter -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Branch</label>
                                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" <?php echo ($branchId == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Templates Grid -->
            <div class="row">
                <?php if (empty($templates)): ?>
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="ri-alert-line"></i> No certificate templates found. Create your first template to get started.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($templates as $template): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($template['template_name']); ?></h5>
                                    <?php if ($template['is_default']): ?>
                                        <span class="badge bg-primary">Default</span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-muted mb-2">
                                    <strong>Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $template['certificate_type'])); ?>
                                </p>
                                
                                <p class="text-muted mb-2">
                                    <strong>Branch:</strong> <?php echo htmlspecialchars($template['branch_name'] ?? 'All Branches'); ?>
                                </p>
                                
                                <p class="text-muted mb-2">
                                    <strong>Orientation:</strong> <?php echo ucfirst($template['page_orientation']); ?>
                                </p>
                                
                                <?php if ($template['usage_count'] > 0): ?>
                                <p class="text-muted mb-2">
                                    <i class="ri-file-list-line"></i> Used <?php echo $template['usage_count']; ?> times
                                </p>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <span class="badge <?php echo $template['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $template['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                                
                                <div class="mt-3 d-flex gap-2 flex-wrap">
                                    <button onclick="designTemplate(<?php echo $template['id']; ?>)" 
                                            class="btn btn-sm btn-primary flex-fill">
                                        <i class="ri-brush-line"></i> Design
                                    </button>
                                    <button onclick="viewTemplate(<?php echo $template['id']; ?>)" 
                                            class="btn btn-sm btn-info">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button onclick="editTemplate(<?php echo $template['id']; ?>)" 
                                            class="btn btn-sm btn-warning">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <?php if (!$template['is_default']): ?>
                                    <button onclick="setAsDefault(<?php echo $template['id']; ?>)" 
                                            class="btn btn-sm btn-success" title="Set as Default">
                                        <i class="ri-star-line"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button onclick="deleteTemplate(<?php echo $template['id']; ?>)" 
                                            class="btn btn-sm btn-danger">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

<!-- Add/Edit Template Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalTitle">Add Certificate Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTemplateForm">
                <input type="hidden" name="id" id="templateId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Template Name</label>
                            <input type="text" class="form-control" name="template_name" required 
                                   placeholder="e.g., Graduation Certificate 2024">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Certificate Type</label>
                            <select class="form-select" name="certificate_type" required>
                                <option value="completion">Completion Certificate</option>
                                <option value="graduation">Graduation Certificate</option>
                                <option value="promotion">Promotion Certificate</option>
                                <option value="character">Character Certificate</option>
                                <option value="participation">Participation Certificate</option>
                                <option value="achievement">Achievement Certificate</option>
                                <option value="custom">Custom Certificate</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Branch</label>
                            <select class="form-select" name="branch_id">
                                <option value="">All Branches</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo $branch['id']; ?>">
                                        <?php echo htmlspecialchars($branch['branch_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Page Orientation</label>
                            <select class="form-select" name="page_orientation" required>
                                <option value="landscape">Landscape</option>
                                <option value="portrait">Portrait</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Page Size</label>
                            <select class="form-select" name="page_size" required>
                                <option value="A4">A4</option>
                                <option value="Letter">Letter</option>
                                <option value="Legal">Legal</option>
                            </select>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Layout & Content</h5>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Header HTML</label>
                            <textarea class="form-control font-monospace" name="header_html" rows="3" 
                                      placeholder="<div style='text-align:center'><h2>{{SCHOOL_NAME}}</h2></div>"></textarea>
                            <small class="text-muted">Use placeholders: {{SCHOOL_NAME}}, {{SCHOOL_LOGO}}, {{BRANCH_NAME}}</small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Body HTML</label>
                            <textarea class="form-control font-monospace" name="body_html" rows="6" required
                                      placeholder="<p>This is to certify that <strong>{{STUDENT_NAME}}</strong> has successfully completed...</p>"></textarea>
                            <small class="text-muted">Placeholders: {{STUDENT_NAME}}, {{STUDENT_ID}}, {{CLASS}}, {{SESSION}}, {{DATE}}, {{CERTIFICATE_ID}}</small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Footer HTML</label>
                            <textarea class="form-control font-monospace" name="footer_html" rows="3"
                                      placeholder="<div>{{PRINCIPAL_SIGNATURE}} {{REGISTRAR_SIGNATURE}}</div>"></textarea>
                            <small class="text-muted">Placeholders: {{PRINCIPAL_SIGNATURE}}, {{REGISTRAR_SIGNATURE}}, {{ISSUE_DATE}}, {{QR_CODE}}</small>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Signature Configuration</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Signature 1 Label</label>
                            <input type="text" class="form-control" name="signature_1_label" placeholder="Principal">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Signature 2 Label</label>
                            <input type="text" class="form-control" name="signature_2_label" placeholder="Registrar">
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Options</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_qr_code" id="includeQR" value="1" checked>
                                <label class="form-check-label" for="includeQR">
                                    Include QR Code for Verification
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_watermark" id="includeWatermark" value="1">
                                <label class="form-check-label" for="includeWatermark">
                                    Include School Logo Watermark
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_default" id="isDefaultTemplate" value="1">
                                <label class="form-check-label" for="isDefaultTemplate">
                                    Set as Default Template
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="templateSubmitBtn">
                        <i class="ri-save-line"></i> Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Template Modal -->
<div class="modal fade" id="viewTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewTemplateModalTitle">
                    <i class="ri-file-preview-line"></i> Template Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="viewTemplateContent" style="background: #f5f7fa; overflow-y: auto; max-height: calc(100vh - 200px);">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line"></i> Close
                </button>
                <button type="button" class="btn btn-info" onclick="printPreview()">
                    <i class="ri-printer-line"></i> Print Preview
                </button>
                <button type="button" class="btn btn-success" onclick="downloadPreview()">
                    <i class="ri-download-line"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.certificate-preview-container {
    padding: 30px;
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: flex-start;
    gap: 20px;
    min-height: 600px;
    overflow-x: auto;
    overflow-y: auto;
}

.certificate-preview {
    width: 210mm;
    max-width: 100%;
    min-height: 297mm;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 8px solid #d4af37;
    border-radius: 4px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
    position: relative;
    padding: 15mm;
    margin: 0;
    overflow: hidden;
    flex-shrink: 0;
}

.certificate-preview.landscape {
    width: 297mm;
    max-width: 100%;
    min-height: 210mm;
}

/* Decorative Border */
.certificate-preview::before {
    content: '';
    position: absolute;
    top: 10mm;
    left: 10mm;
    right: 10mm;
    bottom: 10mm;
    border: 2px solid #c9a961;
    border-radius: 2px;
    pointer-events: none;
}

.certificate-preview::after {
    content: '';
    position: absolute;
    top: 12mm;
    left: 12mm;
    right: 12mm;
    bottom: 12mm;
    border: 1px solid #e8d9b0;
    pointer-events: none;
}

/* Watermark Effect */
.certificate-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 120px;
    font-weight: bold;
    color: rgba(212, 175, 55, 0.08);
    z-index: 0;
    pointer-events: none;
    white-space: nowrap;
    font-family: 'Times New Roman', serif;
}

/* Certificate Content */
.certificate-content {
    position: relative;
    z-index: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.certificate-header {
    text-align: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 3px solid #d4af37;
}

.certificate-header h1 {
    font-family: 'Times New Roman', serif;
    font-size: 42px;
    font-weight: bold;
    color: #1a1a1a;
    margin: 0;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.certificate-header h2 {
    font-family: 'Times New Roman', serif;
    font-size: 28px;
    font-weight: normal;
    color: #4a4a4a;
    margin: 10px 0 0 0;
    font-style: italic;
}

.certificate-body {
    flex: 1;
    padding: 30px 20px;
    text-align: center;
    font-family: 'Georgia', serif;
    font-size: 18px;
    line-height: 1.8;
    color: #2c2c2c;
}

.certificate-body p {
    margin: 15px 0;
}

.student-name-highlight {
    font-size: 32px;
    font-weight: bold;
    color: #1a5490;
    margin: 20px 0;
    padding: 10px 0;
    border-bottom: 2px solid #d4af37;
    border-top: 2px solid #d4af37;
    display: inline-block;
    min-width: 300px;
    font-family: 'Georgia', serif;
}

.certificate-footer {
    margin-top: auto;
    padding-top: 30px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.signature-block {
    text-align: center;
    flex: 1;
    max-width: 200px;
}

.signature-line {
    border-top: 2px solid #1a1a1a;
    width: 150px;
    margin: 0 auto 8px;
    padding-top: 5px;
}

.signature-name {
    font-weight: bold;
    font-size: 16px;
    color: #1a1a1a;
    margin-top: 5px;
}

.signature-title {
    font-size: 14px;
    color: #666;
    font-style: italic;
}

.certificate-seal {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 80px;
    height: 80px;
    border: 3px solid #d4af37;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.9);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.certificate-seal::before {
    content: '✓';
    font-size: 40px;
    color: #d4af37;
    font-weight: bold;
}

.certificate-number {
    position: absolute;
    bottom: 15mm;
    right: 15mm;
    font-size: 11px;
    color: #888;
    font-family: 'Courier New', monospace;
}

.certificate-date {
    position: absolute;
    bottom: 15mm;
    left: 15mm;
    font-size: 14px;
    color: #555;
}

.template-info-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    flex-shrink: 0;
    position: sticky;
    top: 30px;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
}

.template-info-card h6 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.info-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    margin: 3px;
}

.info-badge-primary { background: #e7f3ff; color: #0066cc; }
.info-badge-success { background: #d4edda; color: #155724; }
.info-badge-info { background: #d1ecf1; color: #0c5460; }

@media (max-width: 1200px) {
    .certificate-preview-container {
        flex-direction: column;
        align-items: center;
    }
    
    .template-info-card {
        width: 100% !important;
        max-width: 100%;
        position: relative;
        top: 0;
        margin-bottom: 20px;
    }
    
    .certificate-preview {
        width: 100%;
        max-width: 100%;
    }
    
    .certificate-preview.landscape {
        width: 100%;
        max-width: 100%;
    }
}

@media print {
    .certificate-preview {
        box-shadow: none;
        border: 8px solid #d4af37;
        page-break-after: always;
    }
    
    .template-info-card {
        display: none;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>

<script>
// Reset form when modal is closed
$('#addTemplateModal').on('hidden.bs.modal', function() {
    $('#addTemplateForm')[0].reset();
    $('#templateId').val('');
    $('#templateModalTitle').text('Add Certificate Template');
    $('#templateSubmitBtn').html('<i class="ri-save-line"></i> Save Template');
});

// Edit template
function editTemplate(templateId) {
    $('#templateModalTitle').text('Edit Certificate Template');
    $('#templateSubmitBtn').html('<i class="ri-save-line"></i> Update Template');
    $('#addTemplateModal').modal('show');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/certificates/get-template.php',
        type: 'GET',
        data: { id: templateId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const t = response.data;
                
                // Populate form fields
                $('#templateId').val(t.id);
                $('input[name="template_name"]').val(t.template_name);
                $('select[name="certificate_type"]').val(t.certificate_type);
                $('select[name="branch_id"]').val(t.branch_id || '');
                $('select[name="page_orientation"]').val(t.page_orientation);
                $('select[name="page_size"]').val(t.page_size);
                $('textarea[name="header_html"]').val(t.header_html || '');
                $('textarea[name="body_html"]').val(t.body_html || '');
                $('textarea[name="footer_html"]').val(t.footer_html || '');
                $('input[name="signature_1_label"]').val(t.signature_1_label || 'Principal');
                $('input[name="signature_2_label"]').val(t.signature_2_label || 'Registrar');
                $('input[name="include_qr_code"]').prop('checked', t.include_qr_code == 1);
                $('input[name="include_watermark"]').prop('checked', t.include_watermark == 1);
                $('input[name="is_default"]').prop('checked', t.is_default == 1);
            } else {
                Swal.fire('Error', response.message, 'error');
                $('#addTemplateModal').modal('hide');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load template', 'error');
            $('#addTemplateModal').modal('hide');
        }
    });
}

// Add/Update template
$('#addTemplateForm').on('submit', function(e) {
    e.preventDefault();
    
    const templateId = $('#templateId').val();
    const url = templateId 
        ? '<?php echo APP_URL; ?>ajax/certificates/update-template.php'
        : '<?php echo APP_URL; ?>ajax/certificates/add-template.php';
    
    $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to save template', 'error');
        }
    });
});

// View template with professional preview
function viewTemplate(templateId) {
    $('#viewTemplateContent').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#viewTemplateModal').modal('show');
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/certificates/get-template.php',
        type: 'GET',
        data: { id: templateId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const t = response.data;
                renderProfessionalPreview(t);
            } else {
                $('#viewTemplateContent').html('<div class="alert alert-danger m-4">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#viewTemplateContent').html('<div class="alert alert-danger m-4">Failed to load template. Please try again.</div>');
        }
    });
}

function renderProfessionalPreview(t) {
    const orientation = t.page_orientation || 'portrait';
    const certificateClass = orientation === 'landscape' ? 'landscape' : '';
    const currentDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    
    let html = '<div class="certificate-preview-container">';
    
    // Template Info Card
    html += '<div class="template-info-card" style="width: 300px;">';
    html += '<h6><i class="ri-information-line"></i> Template Information</h6>';
    html += '<div class="mb-3">';
    html += '<strong class="d-block mb-2">' + escapeHtml(t.template_name) + '</strong>';
    html += '<span class="info-badge info-badge-primary">' + escapeHtml(t.certificate_type).charAt(0).toUpperCase() + escapeHtml(t.certificate_type).slice(1) + '</span>';
    html += '<span class="info-badge info-badge-info">' + escapeHtml(t.page_orientation).charAt(0).toUpperCase() + escapeHtml(t.page_orientation).slice(1) + '</span>';
    html += '<span class="info-badge info-badge-info">' + escapeHtml(t.page_size) + '</span>';
    html += '</div>';
    
    html += '<div class="mb-3">';
    html += '<small class="text-muted d-block mb-1">Configuration:</small>';
    html += '<div class="d-flex flex-column gap-1">';
    html += '<div><i class="ri-' + (t.include_qr_code ? 'check-line text-success' : 'close-line text-danger') + '"></i> QR Code Verification</div>';
    html += '<div><i class="ri-' + (t.include_watermark ? 'check-line text-success' : 'close-line text-danger') + '"></i> Watermark</div>';
    html += '<div><i class="ri-' + (t.is_default ? 'star-fill text-warning' : 'star-line text-muted') + '"></i> ' + (t.is_default ? 'Default Template' : 'Not Default') + '</div>';
    html += '</div>';
    html += '</div>';
    
    html += '<div class="mt-3 pt-3 border-top">';
    html += '<small class="text-muted">Branch:</small><br>';
    html += '<strong>' + (t.branch_name ? escapeHtml(t.branch_name) : 'All Branches') + '</strong>';
    html += '</div>';
    html += '</div>';
    
    // Certificate Preview
    html += '<div class="certificate-preview ' + certificateClass + '" id="certificatePreview">';
    
    // Watermark
    if (t.include_watermark) {
        html += '<div class="certificate-watermark"><?php echo APP_NAME; ?></div>';
    }
    
    // Official Seal
    html += '<div class="certificate-seal"></div>';
    
    // Certificate Content
    html += '<div class="certificate-content">';
    
    // Header Section
    html += '<div class="certificate-header">';
    if (t.header_html) {
        let headerPreview = t.header_html
            .replace(/{{SCHOOL_NAME}}/g, '<?php echo APP_NAME; ?>')
            .replace(/{{BRANCH_NAME}}/g, t.branch_name || 'Main Campus')
            .replace(/{{SCHOOL_LOGO}}/g, '<img src="<?php echo APP_URL; ?>template_extracted/assets/images/logo.png" style="max-height: 80px; margin-bottom: 10px;" alt="School Logo">');
        html += headerPreview;
    } else {
        html += '<h1>CERTIFICATE OF COMPLETION</h1>';
        html += '<h2><?php echo APP_NAME; ?></h2>';
    }
    html += '</div>';
    
    // Body Section
    html += '<div class="certificate-body">';
    
    // Process body HTML with placeholders
    let bodyContent = t.body_html || '<p>This is to certify that</p>';
    
    // Replace placeholders with sample data
    bodyContent = bodyContent
        .replace(/{{STUDENT_NAME}}/g, '<span class="student-name-highlight">John Michael Anderson</span>')
        .replace(/{{STUDENT_ID}}/g, '<strong>STU-2024-001</strong>')
        .replace(/{{CLASS}}/g, '<strong>Grade 12 - Science Stream</strong>')
        .replace(/{{SESSION}}/g, '<strong>2023-2024 Academic Year</strong>')
        .replace(/{{DATE}}/g, '<strong>' + currentDate + '</strong>')
        .replace(/{{CERTIFICATE_ID}}/g, '<strong>CERT-2024-001</strong>');
    
    // Clean up any duplicate text issues
    bodyContent = bodyContent.replace(/(This is to certify that\s*){2,}/gi, 'This is to certify that ');
    
    html += bodyContent;
    html += '</div>';
    
    // Footer Section
    html += '<div class="certificate-footer">';
    if (t.footer_html) {
        let footerContent = t.footer_html
            .replace(/{{PRINCIPAL_SIGNATURE}}/g, '<div class="signature-block"><div class="signature-line"></div><div class="signature-name">' + escapeHtml(t.signature_1_label || 'Principal') + '</div><div class="signature-title">Principal</div></div>')
            .replace(/{{REGISTRAR_SIGNATURE}}/g, '<div class="signature-block"><div class="signature-line"></div><div class="signature-name">' + escapeHtml(t.signature_2_label || 'Registrar') + '</div><div class="signature-title">Registrar</div></div>')
            .replace(/{{ISSUE_DATE}}/g, currentDate)
            .replace(/{{QR_CODE}}/g, t.include_qr_code ? '<div style="text-align:center;margin-top:20px;"><div style="width:80px;height:80px;border:2px solid #000;display:inline-block;background:#fff;line-height:80px;font-size:10px;">QR CODE</div></div>' : '');
        html += footerContent;
    } else {
        // Default footer with signatures
        html += '<div class="signature-block">';
        html += '<div class="signature-line"></div>';
        html += '<div class="signature-name">' + escapeHtml(t.signature_1_label || 'Principal') + '</div>';
        html += '<div class="signature-title">Principal</div>';
        html += '</div>';
        
        if (t.signature_2_label) {
            html += '<div class="signature-block">';
            html += '<div class="signature-line"></div>';
            html += '<div class="signature-name">' + escapeHtml(t.signature_2_label) + '</div>';
            html += '<div class="signature-title">Registrar</div>';
            html += '</div>';
        }
    }
    html += '</div>';
    
    // Certificate Number and Date
    html += '<div class="certificate-number">Certificate No: CERT-2024-001</div>';
    html += '<div class="certificate-date">Issued: ' + currentDate + '</div>';
    
    html += '</div>'; // certificate-content
    html += '</div>'; // certificate-preview
    html += '</div>'; // certificate-preview-container
    
    $('#viewTemplateContent').html(html);
}

function printPreview() {
    const printContent = document.getElementById('certificatePreview');
    if (!printContent) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Certificate Preview</title>
            <style>
                @page { size: ${document.querySelector('#certificatePreview').classList.contains('landscape') ? 'A4 landscape' : 'A4 portrait'}; margin: 0; }
                body { margin: 0; padding: 0; background: #f5f7fa; }
                .certificate-preview { 
                    width: 210mm; 
                    min-height: 297mm; 
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                    border: 8px solid #d4af37;
                    padding: 15mm;
                    margin: 0 auto;
                    position: relative;
                }
                .certificate-preview.landscape { width: 297mm; min-height: 210mm; }
                ${document.querySelector('style').textContent}
            </style>
        </head>
        <body>
            ${printContent.outerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.onload = function() {
        printWindow.print();
    };
}

function downloadPreview() {
    Swal.fire({
        title: 'Download PDF',
        text: 'PDF download feature will be available soon. Use Print Preview and save as PDF.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

// Set as default
function setAsDefault(templateId) {
    Swal.fire({
        title: 'Set as Default?',
        text: 'This will make this template the default for this certificate type',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, set as default'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/certificates/set-default-template.php',
                type: 'POST',
                data: { id: templateId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
}

// Delete template
function deleteTemplate(templateId) {
    Swal.fire({
        title: 'Delete Template?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/certificates/delete-template.php',
                type: 'POST',
                data: { id: templateId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
}

// Design template
function designTemplate(templateId) {
    window.location.href = 'template-builder.php?id=' + templateId;
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

// Create Professional Template
function createProfessionalTemplate() {
    // Reset form
    $('#addTemplateForm')[0].reset();
    $('#templateId').val('');
    $('#templateModalTitle').text('Create Professional Certificate Template');
    $('#templateSubmitBtn').html('<i class="ri-save-line"></i> Create Template');
    
    // Fill with professional template content
    $('input[name="template_name"]').val('Professional Certificate Template');
    $('select[name="certificate_type"]').val('completion');
    $('select[name="page_orientation"]').val('landscape');
    $('select[name="page_size"]').val('A4');
    
    // Professional Header HTML
    const professionalHeader = `<div style="text-align: center; margin-bottom: 30px;">
    <h1 style="font-family: 'Old Standard TT', 'Times New Roman', serif; font-size: 72px; font-weight: 700; color: #1a3a5c; margin: 0; letter-spacing: 4px; text-transform: uppercase;">CERTIFICATE</h1>
    <h2 style="font-family: 'Arial', sans-serif; font-size: 20px; font-weight: 600; color: #1a3a5c; margin: 10px 0 0 0; letter-spacing: 3px; text-transform: uppercase;">OF COMPLETION</h2>
</div>`;
    
    // Professional Body HTML
    const professionalBody = `<p style="font-size: 18px; color: #1a3a5c; margin: 20px 0; font-family: 'Crimson Text', serif;">This is to certify that</p>
<div class="student-name-highlight">{{STUDENT_NAME}}</div>
<p style="font-size: 16px; color: #1a3a5c; margin: 20px 0; font-family: 'Crimson Text', serif;">has successfully completed the requirements for <strong>{{CLASS}}</strong> during the academic session <strong>{{SESSION}}</strong> and is hereby awarded this certificate.</p>
<p style="font-size: 14px; color: #666; margin-top: 15px;">Certificate Number: <strong>{{CERTIFICATE_ID}}</strong></p>`;
    
    // Professional Footer HTML
    const professionalFooter = `<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 50px;">
    <div style="text-align: left; flex: 1;">
        <div style="font-size: 12px; color: #1a3a5c; text-transform: uppercase; font-weight: 600; margin-bottom: 5px;">DATE</div>
        <div style="border-top: 2px solid #1a3a5c; width: 150px; padding-top: 5px;">{{ISSUE_DATE}}</div>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 2px solid #1a3a5c; width: 180px; margin: 0 auto 8px; padding-top: 5px;"></div>
        <div style="font-weight: 600; font-size: 14px; color: #1a3a5c; margin-top: 5px;">{{PRINCIPAL_SIGNATURE}}</div>
        <div style="font-size: 12px; color: #1a3a5c; text-transform: uppercase; margin-top: 3px;">PRINCIPAL</div>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 2px solid #1a3a5c; width: 180px; margin: 0 auto 8px; padding-top: 5px;"></div>
        <div style="font-weight: 600; font-size: 14px; color: #1a3a5c; margin-top: 5px;">{{REGISTRAR_SIGNATURE}}</div>
        <div style="font-size: 12px; color: #1a3a5c; text-transform: uppercase; margin-top: 3px;">REGISTRAR</div>
    </div>
</div>
{{QR_CODE}}`;
    
    $('textarea[name="header_html"]').val(professionalHeader);
    $('textarea[name="body_html"]').val(professionalBody);
    $('textarea[name="footer_html"]').val(professionalFooter);
    
    $('input[name="signature_1_label"]').val('Principal');
    $('input[name="signature_2_label"]').val('Registrar');
    
    // Enable professional features
    $('input[name="include_qr_code"]').prop('checked', true);
    $('input[name="include_watermark"]').prop('checked', true);
    $('input[name="is_default"]').prop('checked', false);
    
    // Show modal
    $('#addTemplateModal').modal('show');
}
</script>

