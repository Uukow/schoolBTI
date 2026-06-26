<?php
/**
 * Certificate Template Builder
 * 
 * Visual drag-and-drop certificate template designer
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Certificate Template Builder';

$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

// Get template ID if editing
$templateId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$template = null;

if ($templateId) {
    $sql = "SELECT * FROM certificate_templates WHERE id = ?";
    $stmt = executeQuery($sql, 'i', [$templateId]);
    $template = fetchOne($stmt);
    
    if (!$template) {
        redirect('templates.php');
    }
}

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<style>
.template-builder {
    display: flex;
    height: calc(100vh - 200px);
    gap: 15px;
}

.elements-panel {
    width: 280px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    overflow-y: auto;
}

.canvas-container {
    flex: 1;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.canvas-toolbar {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.canvas-area {
    flex: 1;
    overflow: auto;
    padding: 30px;
    background: #f5f5f5;
    background-image: 
        linear-gradient(45deg, #e0e0e0 25%, transparent 25%),
        linear-gradient(-45deg, #e0e0e0 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #e0e0e0 75%),
        linear-gradient(-45deg, transparent 75%, #e0e0e0 75%);
    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
}

.certificate-canvas {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    background: #fff;
    box-shadow: 0 0 20px rgba(0,0,0,0.2);
    position: relative;
    padding: 20mm;
}

.certificate-canvas.landscape {
    width: 297mm;
    min-height: 210mm;
}

.element-item {
    background: #fff;
    border: 2px dashed #dee2e6;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
    cursor: move;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.element-item:hover {
    border-color: #0d6efd;
    background: #f0f7ff;
    transform: translateX(5px);
}

.element-item i {
    font-size: 20px;
    color: #0d6efd;
}

.draggable-element {
    position: relative;
    margin: 10px 0;
    padding: 10px;
    border: 2px dashed transparent;
    cursor: move;
    min-height: 40px;
}

.draggable-element:hover {
    border-color: #0d6efd;
    background: rgba(13, 110, 253, 0.05);
}

.draggable-element.selected {
    border-color: #0d6efd;
    background: rgba(13, 110, 253, 0.1);
}

.draggable-element .element-controls {
    position: absolute;
    top: -10px;
    right: -10px;
    display: none;
    gap: 5px;
}

.draggable-element:hover .element-controls,
.draggable-element.selected .element-controls {
    display: flex;
}

.element-controls button {
    width: 28px;
    height: 28px;
    padding: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.properties-panel {
    width: 320px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    overflow-y: auto;
}

.properties-panel h6 {
    margin-top: 20px;
    margin-bottom: 10px;
    color: #495057;
    font-weight: 600;
}

.properties-panel h6:first-child {
    margin-top: 0;
}

@media print {
    .template-builder,
    .elements-panel,
    .properties-panel,
    .canvas-toolbar {
        display: none !important;
    }
    
    .certificate-canvas {
        box-shadow: none;
        margin: 0;
        padding: 0;
    }
}
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <a href="templates.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Templates
                            </a>
                            <button type="button" class="btn btn-success" onclick="saveTemplate()">
                                <i class="ri-save-line"></i> Save Template
                            </button>
                        </div>
                        <h4 class="page-title">
                            <?php echo $templateId ? 'Edit Template: ' . htmlspecialchars($template['template_name']) : 'Create New Certificate Template'; ?>
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Template Settings Bar -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="templateSettingsForm" class="row g-3">
                                <input type="hidden" name="template_id" id="templateId" value="<?php echo $templateId; ?>">
                                
                                <div class="col-md-3">
                                    <label class="form-label required">Template Name</label>
                                    <input type="text" class="form-control" name="template_name" id="templateName" 
                                           value="<?php echo $template ? htmlspecialchars($template['template_name']) : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label required">Certificate Type</label>
                                    <select class="form-select" name="certificate_type" id="certificateType" required>
                                        <option value="completion" <?php echo ($template && $template['certificate_type'] == 'completion') ? 'selected' : ''; ?>>Completion</option>
                                        <option value="graduation" <?php echo ($template && $template['certificate_type'] == 'graduation') ? 'selected' : ''; ?>>Graduation</option>
                                        <option value="promotion" <?php echo ($template && $template['certificate_type'] == 'promotion') ? 'selected' : ''; ?>>Promotion</option>
                                        <option value="character" <?php echo ($template && $template['certificate_type'] == 'character') ? 'selected' : ''; ?>>Character</option>
                                        <option value="participation" <?php echo ($template && $template['certificate_type'] == 'participation') ? 'selected' : ''; ?>>Participation</option>
                                        <option value="achievement" <?php echo ($template && $template['certificate_type'] == 'achievement') ? 'selected' : ''; ?>>Achievement</option>
                                        <option value="custom" <?php echo ($template && $template['certificate_type'] == 'custom') ? 'selected' : ''; ?>>Custom</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Branch</label>
                                    <select class="form-select" name="branch_id" id="branchId">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" 
                                                    <?php echo ($template && $template['branch_id'] == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label required">Orientation</label>
                                    <select class="form-select" name="page_orientation" id="pageOrientation" required onchange="updateCanvasOrientation()">
                                        <option value="portrait" <?php echo (!$template || $template['page_orientation'] == 'portrait') ? 'selected' : ''; ?>>Portrait</option>
                                        <option value="landscape" <?php echo ($template && $template['page_orientation'] == 'landscape') ? 'selected' : ''; ?>>Landscape</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label required">Page Size</label>
                                    <select class="form-select" name="page_size" id="pageSize" required>
                                        <option value="A4" <?php echo (!$template || $template['page_size'] == 'A4') ? 'selected' : ''; ?>>A4</option>
                                        <option value="Letter" <?php echo ($template && $template['page_size'] == 'Letter') ? 'selected' : ''; ?>>Letter</option>
                                        <option value="Legal" <?php echo ($template && $template['page_size'] == 'Legal') ? 'selected' : ''; ?>>Legal</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_default" id="isDefault" value="1"
                                               <?php echo ($template && $template['is_default']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isDefault">
                                            Default
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Builder -->
            <div class="row">
                <div class="col-12">
                    <div class="template-builder">
                        
                        <!-- Elements Panel -->
                        <div class="elements-panel">
                            <h5 class="mb-3"><i class="ri-layout-grid-line"></i> Elements</h5>
                            
                            <div class="element-item" draggable="true" data-element="heading">
                                <i class="ri-heading"></i>
                                <div>
                                    <strong>Heading</strong>
                                    <small class="d-block text-muted">Large title text</small>
                                </div>
                            </div>
                            
                            <div class="element-item" draggable="true" data-element="text">
                                <i class="ri-text"></i>
                                <div>
                                    <strong>Text</strong>
                                    <small class="d-block text-muted">Regular text block</small>
                                </div>
                            </div>
                            
                            <div class="element-item" draggable="true" data-element="student-name">
                                <i class="ri-user-line"></i>
                                <div>
                                    <strong>Student Name</strong>
                                    <small class="d-block text-muted">{{STUDENT_NAME}}</small>
                                </div>
                            </div>
                            
                            <div class="element-item" draggable="true" data-element="logo">
                                <i class="ri-image-line"></i>
                                <div>
                                    <strong>School Logo</strong>
                                    <small class="d-block text-muted">{{SCHOOL_LOGO}}</small>
                                </div>
                            </div>
                            
                            <div class="element-item" draggable="true" data-element="signature">
                                <i class="ri-pen-nib-line"></i>
                                <div>
                                    <strong>Signature</strong>
                                    <small class="d-block text-muted">Principal/Registrar</small>
                                </div>
                            </div>
                            
                            <div class="element-item" draggable="true" data-element="date">
                                <i class="ri-calendar-line"></i>
                                <div>
                                    <strong>Date</strong>
                                    <small class="d-block text-muted">{{DATE}} / {{ISSUE_DATE}}</small>
                                </div>
                            </div>
                            
                            <div class="element-item" draggable="true" data-element="certificate-number">
                                <i class="ri-barcode-line"></i>
                                <div>
                                    <strong>Certificate No.</strong>
                                    <small class="d-block text-muted">{{CERTIFICATE_ID}}</small>
                                </div>
                            </div>
                            
                            <div class="element-item" draggable="true" data-element="qr-code">
                                <i class="ri-qr-code-line"></i>
                                <div>
                                    <strong>QR Code</strong>
                                    <small class="d-block text-muted">Verification QR</small>
                                </div>
                            </div>
                            
                            <div class="element-item" draggable="true" data-element="divider">
                                <i class="ri-separator"></i>
                                <div>
                                    <strong>Divider Line</strong>
                                    <small class="d-block text-muted">Horizontal line</small>
                                </div>
                            </div>
                            
                            <div class="element-item" draggable="true" data-element="spacer">
                                <i class="ri-space"></i>
                                <div>
                                    <strong>Spacer</strong>
                                    <small class="d-block text-muted">Empty space</small>
                                </div>
                            </div>
                            
                        </div>
                        
                        <!-- Canvas -->
                        <div class="canvas-container">
                            <div class="canvas-toolbar">
                                <div>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="clearCanvas()">
                                        <i class="ri-delete-bin-line"></i> Clear
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="previewTemplate()">
                                        <i class="ri-eye-line"></i> Preview
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="printTemplate()">
                                        <i class="ri-printer-line"></i> Print
                                    </button>
                                </div>
                                <div>
                                    <span class="badge bg-info">Zoom: <span id="zoomLevel">100%</span></span>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="zoomOut()">
                                        <i class="ri-zoom-out-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="zoomIn()">
                                        <i class="ri-zoom-in-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="resetZoom()">
                                        <i class="ri-fullscreen-exit-line"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="canvas-area" id="canvasArea">
                                <div class="certificate-canvas <?php echo ($template && $template['page_orientation'] == 'landscape') ? 'landscape' : 'portrait'; ?>" id="certificateCanvas">
                                    <!-- Elements will be added here via drag and drop -->
                                    <div id="templateContent">
                                        <?php if ($template && $template['body_html']): ?>
                                            <?php 
                                            // Output template data as JSON for JavaScript to parse
                                            $templateData = [
                                                'id' => $template['id'],
                                                'body_html' => $template['body_html'],
                                                'header_html' => $template['header_html'] ?? '',
                                                'footer_html' => $template['footer_html'] ?? ''
                                            ];
                                            ?>
                                            <script>
                                            window.templateData = <?php echo json_encode($templateData); ?>;
                                            </script>
                                        <?php else: ?>
                                            <p class="text-muted" style="text-align: center; padding-top: 50mm;">Drag elements from the left panel to start building your certificate</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Properties Panel -->
                        <div class="properties-panel" id="propertiesPanel">
                            <h5 class="mb-3"><i class="ri-settings-3-line"></i> Properties</h5>
                            <p class="text-muted small">Select an element to edit its properties</p>
                            
                            <div id="propertiesContent">
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> Select an element to edit
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Save Template Modal -->
<div class="modal fade" id="saveTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="saveTemplateForm">
                    <div class="mb-3">
                        <label class="form-label">Additional Options</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_qr_code" id="includeQR" value="1" checked>
                            <label class="form-check-label" for="includeQR">
                                Include QR Code for Verification
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_watermark" id="includeWatermark" value="1">
                            <label class="form-check-label" for="includeWatermark">
                                Include School Logo Watermark
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Signature Labels</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="signature_1_label" placeholder="Principal">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="signature_2_label" placeholder="Registrar">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmSave()">
                    <i class="ri-save-line"></i> Save Template
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<!-- SortableJS for drag and drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
let currentZoom = 1;
let selectedElement = null;
let elementCounter = 0;

// Initialize drag and drop
document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
    initializeCanvas();
});

function initializeDragAndDrop() {
    // Make element items draggable
    const elementItems = document.querySelectorAll('.element-item');
    elementItems.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
    });
    
    // Make canvas a drop zone
    const canvas = document.getElementById('certificateCanvas');
    canvas.addEventListener('dragover', handleDragOver);
    canvas.addEventListener('drop', handleDrop);
    canvas.addEventListener('click', handleCanvasClick);
}

function initializeCanvas() {
    const canvas = document.getElementById('certificateCanvas');
    const templateContent = document.getElementById('templateContent');
    
    // If editing existing template, parse and convert HTML to draggable elements
    if (window.templateData && window.templateData.body_html) {
        parseExistingTemplate(window.templateData.body_html);
    } else {
        // Make any existing elements draggable and selectable
        const elements = canvas.querySelectorAll('.draggable-element');
        elements.forEach(element => {
            makeElementInteractive(element);
        });
    }
}

function parseExistingTemplate(html) {
    const canvas = document.getElementById('certificateCanvas');
    const templateContent = document.getElementById('templateContent');
    
    // Clear existing content
    templateContent.innerHTML = '';
    
    if (!html || html.trim() === '') {
        templateContent.innerHTML = '<p class="text-muted" style="text-align: center; padding-top: 50mm;">Drag elements from the left panel to start building your certificate</p>';
        return;
    }
    
    // Create a temporary container to parse HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    
    // Clean up - remove any existing builder classes
    tempDiv.querySelectorAll('.draggable-element, .element-controls').forEach(el => {
        el.classList.remove('draggable-element', 'element-controls');
        el.removeAttribute('data-element-type');
        el.removeAttribute('data-element-id');
    });
    
    // Get all top-level elements
    const topLevelElements = Array.from(tempDiv.children);
    
    if (topLevelElements.length === 0) {
        // Try to find any meaningful content
        const allElements = tempDiv.querySelectorAll('h1, h2, h3, h4, h5, h6, p, div, img, hr');
        if (allElements.length === 0) {
            templateContent.innerHTML = '<p class="text-muted" style="text-align: center; padding-top: 50mm;">Drag elements from the left panel to start building your certificate</p>';
            return;
        }
        topLevelElements.push(...allElements);
    }
    
    let elementCounter = 0;
    
    topLevelElements.forEach((node) => {
        if (!node || node.nodeType !== Node.ELEMENT_NODE) return;
        
        const tagName = node.tagName.toLowerCase();
        const textContent = node.textContent.trim();
        const innerHTML = node.innerHTML.trim();
        
        // Skip empty elements and script/style tags
        if ((!textContent && !node.querySelector('img')) || ['script', 'style', 'meta', 'link'].includes(tagName)) {
            return;
        }
        
        // Determine element type based on content and tag
        let elementType = 'text';
        if (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(tagName)) {
            elementType = 'heading';
        } else if (textContent.includes('{{STUDENT_NAME}}') || innerHTML.includes('{{STUDENT_NAME}}')) {
            elementType = 'student-name';
        } else if (textContent.includes('{{SCHOOL_LOGO}}') || innerHTML.includes('{{SCHOOL_LOGO}}') || node.querySelector('img')) {
            elementType = 'logo';
        } else if (textContent.includes('{{DATE}}') || textContent.includes('{{ISSUE_DATE}}') || innerHTML.includes('{{DATE}}')) {
            elementType = 'date';
        } else if (textContent.includes('{{CERTIFICATE_ID}}') || innerHTML.includes('{{CERTIFICATE_ID}}') || textContent.toLowerCase().includes('certificate no')) {
            elementType = 'certificate-number';
        } else if (textContent.includes('{{QR_CODE}}') || innerHTML.includes('{{QR_CODE}}') || textContent.toLowerCase().includes('qr code')) {
            elementType = 'qr-code';
        } else if (tagName === 'hr' || textContent.includes('---') || textContent.includes('___')) {
            elementType = 'divider';
        } else if (textContent.toLowerCase().includes('principal') || textContent.toLowerCase().includes('registrar') || textContent.toLowerCase().includes('signature') || textContent.toLowerCase().includes('maamule')) {
            elementType = 'signature';
        }
        
        // Create draggable element wrapper
        const element = document.createElement('div');
        element.className = 'draggable-element';
        element.dataset.elementType = elementType;
        element.dataset.elementId = 'element-' + (++elementCounter);
        element.style.position = 'relative';
        element.style.margin = '10px 0';
        element.style.display = 'inline-block';
        element.style.width = '100%';
        
        // Preserve original HTML structure
        const clonedNode = node.cloneNode(true);
        element.appendChild(clonedNode);
        
        // Add controls
        const controls = document.createElement('div');
        controls.className = 'element-controls';
        controls.innerHTML = `
            <button class="btn btn-sm btn-primary" onclick="selectElement(this)" title="Select">
                <i class="ri-cursor-line"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteElement(this)" title="Delete">
                <i class="ri-delete-bin-line"></i>
            </button>
        `;
        element.appendChild(controls);
        
        makeElementInteractive(element);
        templateContent.appendChild(element);
    });
    
    // If no elements were created, show placeholder
    if (templateContent.children.length === 0) {
        templateContent.innerHTML = '<p class="text-muted" style="text-align: center; padding-top: 50mm;">Drag elements from the left panel to start building your certificate</p>';
    }
}

function handleDragStart(e) {
    e.dataTransfer.setData('element-type', e.target.closest('.element-item').dataset.element);
    e.dataTransfer.effectAllowed = 'copy';
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
}

function handleDrop(e) {
    e.preventDefault();
    const elementType = e.dataTransfer.getData('element-type');
    if (elementType) {
        createElement(elementType, e.clientX, e.clientY);
    }
}

function createElement(type, x, y) {
    const canvas = document.getElementById('certificateCanvas');
    const canvasRect = canvas.getBoundingClientRect();
    const canvasArea = document.getElementById('canvasArea');
    const areaRect = canvasArea.getBoundingClientRect();
    
    // Calculate position relative to canvas
    const relativeX = x - canvasRect.left;
    const relativeY = y - canvasRect.top;
    
    const element = document.createElement('div');
    element.className = 'draggable-element';
    element.dataset.elementType = type;
    element.dataset.elementId = 'element-' + (++elementCounter);
    element.style.position = 'absolute';
    element.style.left = relativeX + 'px';
    element.style.top = relativeY + 'px';
    
    // Create element content based on type
    let content = '';
    let defaultText = '';
    
    switch(type) {
        case 'heading':
            content = '<h1 style="margin:0; font-size: 36px; font-weight: bold;">Certificate of Completion</h1>';
            defaultText = 'Certificate of Completion';
            break;
        case 'text':
            content = '<p style="margin:0; font-size: 16px;">This is to certify that</p>';
            defaultText = 'This is to certify that';
            break;
        case 'student-name':
            content = '<p style="margin:0; font-size: 24px; font-weight: bold; color: #0d6efd;"><strong>{{STUDENT_NAME}}</strong></p>';
            defaultText = '{{STUDENT_NAME}}';
            break;
        case 'logo':
            content = '<div style="text-align:center;"><img src="' + getSchoolLogo() + '" style="max-width: 150px; height: auto;" alt="School Logo"></div>';
            defaultText = '{{SCHOOL_LOGO}}';
            break;
        case 'signature':
            content = '<div style="border-top: 2px solid #000; display: inline-block; padding: 10px 30px; min-width: 150px; text-align: center;"><strong>Principal</strong></div>';
            defaultText = 'Principal';
            break;
        case 'date':
            content = '<p style="margin:0; font-size: 14px;">Date: <strong>{{DATE}}</strong></p>';
            defaultText = '{{DATE}}';
            break;
        case 'certificate-number':
            content = '<p style="margin:0; font-size: 14px;">Certificate No.: <strong>{{CERTIFICATE_ID}}</strong></p>';
            defaultText = '{{CERTIFICATE_ID}}';
            break;
        case 'qr-code':
            content = '<div style="text-align:center;"><div style="width: 100px; height: 100px; border: 2px solid #000; display: inline-block; background: #f0f0f0; line-height: 100px;">QR Code</div></div>';
            defaultText = '{{QR_CODE}}';
            break;
        case 'divider':
            content = '<hr style="margin: 20px 0; border: none; border-top: 2px solid #000;">';
            defaultText = 'Divider';
            break;
        case 'spacer':
            content = '<div style="height: 30px;"></div>';
            defaultText = 'Spacer';
            break;
    }
    
    element.innerHTML = content + `
        <div class="element-controls">
            <button class="btn btn-sm btn-primary" onclick="selectElement(this)" title="Select">
                <i class="ri-cursor-line"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteElement(this)" title="Delete">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    `;
    
    element.dataset.defaultText = defaultText;
    
    makeElementInteractive(element);
    canvas.appendChild(element);
    selectElement(element.querySelector('.element-controls button'));
}

function makeElementInteractive(element) {
    // Make element clickable to select
    element.addEventListener('click', function(e) {
        if (e.target.closest('.element-controls')) return;
        e.stopPropagation();
        selectElement(element);
    });
    
    // Make element draggable
    let isDragging = false;
    let startX, startY, startLeft, startTop;
    let dragStartTime = 0;
    
    element.addEventListener('mousedown', function(e) {
        if (e.target.closest('.element-controls')) return;
        dragStartTime = Date.now();
        isDragging = false; // Will be set to true on mousemove
        startX = e.clientX;
        startY = e.clientY;
        const rect = element.getBoundingClientRect();
        const canvasRect = element.closest('#certificateCanvas').getBoundingClientRect();
        startLeft = rect.left - canvasRect.left;
        startTop = rect.top - canvasRect.top;
    });
    
    document.addEventListener('mousemove', function(e) {
        if (dragStartTime === 0) return;
        
        const deltaX = Math.abs(e.clientX - startX);
        const deltaY = Math.abs(e.clientY - startY);
        
        // Only start dragging if mouse moved more than 5px
        if (deltaX > 5 || deltaY > 5) {
            isDragging = true;
        }
        
        if (isDragging) {
            const canvas = element.closest('#certificateCanvas');
            const canvasRect = canvas.getBoundingClientRect();
            const newLeft = startLeft + (e.clientX - startX);
            const newTop = startTop + (e.clientY - startY);
            
            // Constrain to canvas bounds
            element.style.position = 'absolute';
            element.style.left = Math.max(0, Math.min(newLeft, canvas.offsetWidth - element.offsetWidth)) + 'px';
            element.style.top = Math.max(0, Math.min(newTop, canvas.offsetHeight - element.offsetHeight)) + 'px';
            e.preventDefault();
        }
    });
    
    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
        }
        dragStartTime = 0;
    });
}

function selectElement(button) {
    let element;
    if (button && button.closest) {
        element = button.closest('.draggable-element');
    } else if (button && button.classList && button.classList.contains('draggable-element')) {
        element = button;
    } else {
        return;
    }
    
    if (!element) return;
    
    // Deselect previous
    if (selectedElement) {
        selectedElement.classList.remove('selected');
    }
    
    // Select new
    selectedElement = element;
    element.classList.add('selected');
    
    // Show properties
    showProperties(element);
}

function handleCanvasClick(e) {
    if (e.target.closest('.draggable-element')) return;
    if (selectedElement) {
        selectedElement.classList.remove('selected');
        selectedElement = null;
        showProperties(null);
    }
}

function deleteElement(button) {
    const element = button.closest('.draggable-element');
    if (confirm('Delete this element?')) {
        element.remove();
        selectedElement = null;
        showProperties(null);
    }
}

function showProperties(element) {
    const panel = document.getElementById('propertiesContent');
    
    if (!element) {
        panel.innerHTML = '<div class="alert alert-info"><i class="ri-information-line"></i> Select an element to edit</div>';
        return;
    }
    
    const type = element.dataset.elementType || 'text';
    const contentElement = element.querySelector('h1, h2, h3, h4, h5, h6, p, div, span');
    const currentContent = contentElement ? contentElement.textContent.trim() : element.textContent.trim();
    
    let html = `
        <div class="mb-3">
            <label class="form-label">Element Type</label>
            <input type="text" class="form-control" value="${type}" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea class="form-control" id="elementContent" rows="3">${currentContent}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Text Alignment</label>
            <select class="form-select" id="elementAlign" onchange="updateElementStyle('text-align', this.value)">
                <option value="left">Left</option>
                <option value="center">Center</option>
                <option value="right">Right</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Font Size</label>
            <input type="number" class="form-control" id="elementFontSize" value="16" min="8" max="72" 
                   onchange="updateElementStyle('font-size', this.value + 'px')">
        </div>
        <div class="mb-3">
            <label class="form-label">Font Weight</label>
            <select class="form-select" id="elementFontWeight" onchange="updateElementStyle('font-weight', this.value)">
                <option value="normal">Normal</option>
                <option value="bold">Bold</option>
                <option value="lighter">Light</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Color</label>
            <input type="color" class="form-control form-control-color" id="elementColor" 
                   value="#000000" onchange="updateElementStyle('color', this.value)">
        </div>
        <div class="mb-3">
            <button class="btn btn-primary btn-sm w-100" onclick="updateElementContent()">
                <i class="ri-save-line"></i> Update Content
            </button>
        </div>
    `;
    
    panel.innerHTML = html;
    
    // Set current values
    const firstElement = element.querySelector('h1, h2, h3, h4, h5, h6, p, div, img');
    if (firstElement) {
        const computedStyle = window.getComputedStyle(firstElement);
        document.getElementById('elementAlign').value = computedStyle.textAlign || 'left';
        document.getElementById('elementFontSize').value = parseInt(computedStyle.fontSize) || 16;
        document.getElementById('elementFontWeight').value = computedStyle.fontWeight || 'normal';
        document.getElementById('elementColor').value = rgbToHex(computedStyle.color) || '#000000';
    }
}

function updateElementStyle(property, value) {
    if (!selectedElement) return;
    const contentElement = selectedElement.querySelector('h1, h2, h3, h4, h5, h6, p, div, img');
    if (contentElement) {
        contentElement.style[property] = value;
    }
}

function updateElementContent() {
    if (!selectedElement) return;
    const newContent = document.getElementById('elementContent').value;
    const contentElement = selectedElement.querySelector('h1, h2, h3, h4, h5, h6, p, div');
    if (contentElement) {
        if (contentElement.tagName === 'H1' || contentElement.tagName === 'H2' || contentElement.tagName === 'H3') {
            contentElement.textContent = newContent;
        } else {
            contentElement.innerHTML = newContent;
        }
    }
}

function rgbToHex(rgb) {
    if (!rgb || rgb === 'transparent') return '#000000';
    const match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    if (!match) return rgb;
    return '#' + [1, 2, 3].map(i => ('0' + parseInt(match[i]).toString(16)).slice(-2)).join('');
}

function getSchoolLogo() {
    return '<?php echo APP_URL; ?>template_extracted/assets/images/logo.png';
}

function updateCanvasOrientation() {
    const orientation = document.getElementById('pageOrientation').value;
    const canvas = document.getElementById('certificateCanvas');
    canvas.classList.remove('portrait', 'landscape');
    canvas.classList.add(orientation);
}

function zoomIn() {
    currentZoom = Math.min(currentZoom + 0.1, 2);
    updateZoom();
}

function zoomOut() {
    currentZoom = Math.max(currentZoom - 0.1, 0.5);
    updateZoom();
}

function resetZoom() {
    currentZoom = 1;
    updateZoom();
}

function updateZoom() {
    const canvas = document.getElementById('certificateCanvas');
    canvas.style.transform = `scale(${currentZoom})`;
    canvas.style.transformOrigin = 'top center';
    document.getElementById('zoomLevel').textContent = Math.round(currentZoom * 100) + '%';
}

function clearCanvas() {
    if (confirm('Clear all elements? This cannot be undone.')) {
        const canvas = document.getElementById('certificateCanvas');
        const content = canvas.querySelector('#templateContent');
        if (content) {
            content.innerHTML = '<p class="text-muted" style="text-align: center; padding-top: 50mm;">Drag elements from the left panel to start building your certificate</p>';
        }
        const elements = canvas.querySelectorAll('.draggable-element');
        elements.forEach(el => el.remove());
        selectedElement = null;
        showProperties(null);
    }
}

function previewTemplate() {
    const canvas = document.getElementById('certificateCanvas');
    const previewWindow = window.open('', '_blank');
    previewWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Certificate Preview</title>
            <style>
                body { margin: 0; padding: 20px; background: #f5f5f5; }
                .preview-canvas { 
                    width: 210mm; 
                    min-height: 297mm; 
                    margin: 0 auto; 
                    background: #fff; 
                    padding: 20mm; 
                    box-shadow: 0 0 20px rgba(0,0,0,0.2);
                }
                .preview-canvas.landscape { width: 297mm; min-height: 210mm; }
                @media print {
                    body { background: #fff; padding: 0; }
                    .preview-canvas { box-shadow: none; }
                }
            </style>
        </head>
        <body>
            <div class="preview-canvas ${document.getElementById('pageOrientation').value}">
                ${canvas.innerHTML.replace(/draggable-element|element-controls/g, '').replace(/selected/g, '')}
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()" class="btn btn-primary">Print</button>
            </div>
        </body>
        </html>
    `);
    previewWindow.document.close();
}

function printTemplate() {
    const canvas = document.getElementById('certificateCanvas');
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Certificate Print</title>
            <style>
                @media print {
                    @page { size: ${document.getElementById('pageSize').value} ${document.getElementById('pageOrientation').value}; }
                }
                body { margin: 0; padding: 0; }
                .print-canvas { 
                    width: 210mm; 
                    min-height: 297mm; 
                    padding: 20mm; 
                }
                .print-canvas.landscape { width: 297mm; min-height: 210mm; }
            </style>
        </head>
        <body>
            <div class="print-canvas ${document.getElementById('pageOrientation').value}">
                ${canvas.innerHTML.replace(/draggable-element|element-controls/g, '').replace(/selected/g, '')}
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.onload = function() {
        printWindow.print();
    };
}

function saveTemplate() {
    // Validate form
    const templateName = document.getElementById('templateName').value;
    if (!templateName) {
        Swal.fire('Error', 'Template name is required', 'error');
        return;
    }
    
    // Load existing template data if editing
    if (window.templateData) {
        const t = window.templateData;
        document.querySelector('[name="include_qr_code"]').checked = true; // Default
        document.querySelector('[name="include_watermark"]').checked = false; // Default
        document.querySelector('[name="signature_1_label"]').value = 'Principal';
        document.querySelector('[name="signature_2_label"]').value = 'Registrar';
    }
    
    $('#saveTemplateModal').modal('show');
}

function confirmSave() {
    const canvas = document.getElementById('certificateCanvas');
    let templateContent = canvas.innerHTML;
    
    // Clean up HTML - remove draggable classes and controls
    templateContent = templateContent
        .replace(/draggable-element/g, '')
        .replace(/element-controls/g, '')
        .replace(/selected/g, '')
        .replace(/data-element-type="[^"]*"/g, '')
        .replace(/data-element-id="[^"]*"/g, '')
        .replace(/data-default-text="[^"]*"/g, '')
        .replace(/style="position:\s*absolute[^"]*"/g, '')
        .replace(/onclick="[^"]*"/g, '');
    
    // Get form data
    const formData = {
        template_id: document.getElementById('templateId').value,
        template_name: document.getElementById('templateName').value,
        certificate_type: document.getElementById('certificateType').value,
        branch_id: document.getElementById('branchId').value || null,
        page_orientation: document.getElementById('pageOrientation').value,
        page_size: document.getElementById('pageSize').value,
        body_html: templateContent,
        header_html: '',
        footer_html: '',
        signature_1_label: document.querySelector('[name="signature_1_label"]').value || 'Principal',
        signature_2_label: document.querySelector('[name="signature_2_label"]').value || 'Registrar',
        include_qr_code: document.getElementById('includeQR').checked ? 1 : 0,
        include_watermark: document.getElementById('includeWatermark').checked ? 1 : 0,
        is_default: document.getElementById('isDefault').checked ? 1 : 0
    };
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/certificates/save-template-builder.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    window.location.href = 'templates.php';
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to save template', 'error');
        }
    });
}
</script>

