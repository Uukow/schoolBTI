<?php
/**
 * Permission System Usage Examples
 * 
 * This file contains practical examples of how to use the granular permissions system
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

// ============================================
// EXAMPLE 1: Basic Permission Check
// ============================================

// Check if user can create students
if (canPerform('students', 'create')) {
    echo "User can create students";
}

// Check if user can view fees
if (can('fees', 'view')) {
    echo "User can view fees";
}


// ============================================
// EXAMPLE 2: Require Permission (Redirect if Denied)
// ============================================

// At the top of a page that requires permission
requirePermission('students', 'view');
// If user doesn't have permission, they'll be redirected


// ============================================
// EXAMPLE 3: Permission-Aware Buttons
// ============================================

// In a student list page
echo permissionAwareButton(
    'students',                    // Module
    'create',                      // Action
    'Add New Student',            // Button text
    'students/add.php',           // URL
    'btn btn-primary',            // CSS class
    'ri-add-line'                 // Icon
);

echo permissionAwareButton(
    'students',
    'export',
    'Export Students',
    'students/export.php',
    'btn btn-success',
    'ri-download-line'
);


// ============================================
// EXAMPLE 4: Action Buttons Group
// ============================================

// In a student detail page
$actions = [
    'view' => [
        'url' => 'students/view.php?id=' . $studentId,
        'text' => 'View',
        'icon' => 'ri-eye-line',
        'class' => 'btn btn-sm btn-info'
    ],
    'update' => [
        'url' => 'students/edit.php?id=' . $studentId,
        'text' => 'Edit',
        'icon' => 'ri-edit-line',
        'class' => 'btn btn-sm btn-warning'
    ],
    'delete' => [
        'url' => 'students/delete.php?id=' . $studentId,
        'text' => 'Delete',
        'icon' => 'ri-delete-bin-line',
        'class' => 'btn btn-sm btn-danger'
    ]
];

echo permissionActionButtons($actions, 'students');


// ============================================
// EXAMPLE 5: AJAX Endpoint with Permission Check
// ============================================

// In ajax/students/delete.php
require_once '../../config/config.php';

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

// Check permission
if (!canPerform('students', 'delete')) {
    jsonResponse(false, 'You do not have permission to delete students');
}

// Proceed with deletion
$studentId = (int)$_POST['student_id'];
// ... deletion logic ...


// ============================================
// EXAMPLE 6: Conditional Content Display
// ============================================

// Show different content based on permissions
if (canPerform('fees', 'create')) {
    echo '<div class="alert alert-info">You can create fee records</div>';
}

if (canPerformAll('students', ['view', 'update', 'delete'])) {
    echo '<div class="alert alert-success">You have full access to students</div>';
}

if (canPerformAny('fees', ['approve', 'reject'])) {
    echo '<div class="alert alert-warning">You can approve or reject fee payments</div>';
}


// ============================================
// EXAMPLE 7: Data Attributes for Auto-Hiding
// ============================================

// In HTML template
?>
<button class="btn btn-primary" 
        data-permission='{"module":"students","action":"create"}'>
    Add Student
</button>

<button class="btn btn-danger" 
        data-permission='{"module":"students","action":"delete"}'
        data-permission-action="disable">
    Delete Student
</button>
<?php


// ============================================
// EXAMPLE 8: JavaScript Permission Checking
// ============================================

// In JavaScript
if (PermissionManager.canPerform('students', 'create')) {
    $('#addStudentButton').show();
} else {
    $('#addStudentButton').hide();
}

// Toggle element
PermissionManager.toggleElement('students', 'delete', $('#deleteButton'), 'hide');


// ============================================
// EXAMPLE 9: Complex Permission Logic
// ============================================

// Check multiple conditions
$canManageStudents = canPerformAll('students', ['create', 'view', 'update', 'delete']);
$canViewReports = canPerform('reports', 'view');
$canExportData = canPerformAny('students', ['export', 'print']);

if ($canManageStudents && $canViewReports) {
    echo "User has full student management and report viewing access";
}


// ============================================
// EXAMPLE 10: Permission Class Helper
// ============================================

// Add/remove CSS classes based on permission
$buttonClass = permissionClass('students', 'delete', 'btn-danger', 'btn-secondary disabled');
echo "<button class='{$buttonClass}'>Delete</button>";


// ============================================
// EXAMPLE 11: Form Field Permissions
// ============================================

// In a form
?>
<form>
    <div class="mb-3">
        <label>Student Name</label>
        <input type="text" name="name" 
               <?php echo canPerform('students', 'update') ? '' : 'readonly'; ?>>
    </div>
    
    <?php if (canPerform('students', 'delete')): ?>
    <button type="button" class="btn btn-danger" onclick="deleteStudent()">
        Delete Student
    </button>
    <?php endif; ?>
</form>
<?php


// ============================================
// EXAMPLE 12: Menu Item Permissions
// ============================================

// In sidebar navigation
if (canPerform('students', 'view')) {
    echo '<li><a href="students/list.php">Students</a></li>';
}

if (canPerform('fees', 'view')) {
    echo '<li><a href="fees/list.php">Fees</a></li>';
}


// ============================================
// EXAMPLE 13: Bulk Operations with Permissions
// ============================================

// Check permissions before bulk operations
if (canPerform('students', 'delete')) {
    echo '<button onclick="bulkDelete()">Bulk Delete</button>';
}

if (canPerform('students', 'export')) {
    echo '<button onclick="exportStudents()">Export All</button>';
}


// ============================================
// EXAMPLE 14: Permission-Aware DataTables
// ============================================

// In a DataTable, show action buttons based on permissions
$actionButtons = '';

if (canPerform('students', 'view')) {
    $actionButtons .= '<a href="view.php?id=' . $row['id'] . '" class="btn btn-sm btn-info">View</a> ';
}

if (canPerform('students', 'update')) {
    $actionButtons .= '<a href="edit.php?id=' . $row['id'] . '" class="btn btn-sm btn-warning">Edit</a> ';
}

if (canPerform('students', 'delete')) {
    $actionButtons .= '<a href="delete.php?id=' . $row['id'] . '" class="btn btn-sm btn-danger">Delete</a>';
}

echo $actionButtons;


// ============================================
// EXAMPLE 15: API Endpoint with Permission Check
// ============================================

// In a REST API endpoint
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? '';

if (!canPerform($module, $action)) {
    http_response_code(403);
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Process request
echo json_encode(['success' => true, 'data' => []]);

