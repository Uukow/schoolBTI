# Granular Permissions System Documentation

## Overview

The Granular Permissions System is an expert-level, action-based authorization system that provides fine-grained control over user access to modules and actions within the School ERP System.

## Key Features

- **Module-Action Based**: Permissions are defined per module (e.g., Students, Fees) and per action (e.g., Create, View, Update, Delete)
- **Role-Based Permissions**: Assign permissions to roles, and users inherit permissions from their roles
- **User Overrides**: Allow individual users to have custom permission overrides, even within the same role
- **Audit Logging**: Comprehensive audit trail for all permission changes
- **UI & Backend Enforcement**: Permissions are enforced at both UI level (hide/disable buttons) and backend level (server-side validation)
- **Scalable Architecture**: Normalized database structure supports future modules without schema changes

## Database Schema

### Tables

1. **modules**: Stores all system modules (Students, Fees, Exams, etc.)
2. **actions**: Stores all possible actions (Create, View, Update, Delete, Approve, Export, etc.)
3. **role_action_permissions**: Many-to-many relationship between roles, modules, and actions
4. **user_action_overrides**: User-specific permission overrides
5. **permission_audit_log**: Comprehensive audit trail

## Installation

### Step 1: Run Database Migration

```bash
php database/run_granular_permissions_migration.php
```

Or manually execute the SQL file:
```sql
source database/granular_permissions_migration.sql
```

### Step 2: Verify Installation

Access the permissions management page:
```
http://localhost/bti/modules/settings/permissions.php
```

## Usage

### Backend Permission Checking

#### Basic Permission Check

```php
// Check if current user can perform action
if (canPerform('students', 'create')) {
    // User can create students
}

// Check if current user can perform action (alternative syntax)
if (can('students', 'view')) {
    // User can view students
}
```

#### Require Permission (Redirect if Denied)

```php
// Require permission, redirect if user doesn't have it
requirePermission('fees', 'update');
// Code here will only execute if user has permission
```

#### Check Multiple Permissions

```php
// Check if user has ALL permissions
if (canPerformAll('students', ['view', 'update'])) {
    // User can both view and update
}

// Check if user has ANY permission
if (canPerformAny('students', ['view', 'create'])) {
    // User can either view or create
}
```

### UI Permission Helpers

#### Show/Hide Buttons

```php
// Show button only if user has permission
echo permissionButton('students', 'create', 
    '<button class="btn btn-primary">Add Student</button>'
);

// Show alternative content if no permission
echo permissionButton('students', 'delete', 
    '<button class="btn btn-danger">Delete</button>',
    '<span class="text-muted">No permission to delete</span>'
);
```

#### Permission-Aware Buttons

```php
// Generate permission-aware button
echo permissionAwareButton(
    'students',           // Module
    'create',            // Action
    'Add Student',       // Button text
    'students/add.php',  // URL
    'btn btn-primary',   // CSS class
    'ri-add-line'        // Icon class
);
```

#### Action Buttons Group

```php
$actions = [
    'view' => ['url' => 'view.php?id=1', 'text' => 'View', 'icon' => 'ri-eye-line'],
    'update' => ['url' => 'edit.php?id=1', 'text' => 'Edit', 'icon' => 'ri-edit-line'],
    'delete' => ['url' => 'delete.php?id=1', 'text' => 'Delete', 'icon' => 'ri-delete-bin-line']
];

echo permissionActionButtons($actions, 'students');
```

### JavaScript Permission Checking

```javascript
// Check permission client-side
if (PermissionManager.canPerform('students', 'create')) {
    // Show create button
    $('#createButton').show();
} else {
    // Hide create button
    $('#createButton').hide();
}

// Toggle element visibility
PermissionManager.toggleElement('students', 'delete', $('#deleteButton'), 'hide');

// Create permission-aware button
const button = PermissionManager.createButton(
    'students',
    'create',
    'Add Student',
    'students/add.php',
    'btn btn-primary',
    'ri-add-line'
);
$('#buttonContainer').append(button);
```

### Data Attributes for Automatic Hiding

Add `data-permission` attribute to any element:

```html
<button data-permission='{"module":"students","action":"create"}'>
    Add Student
</button>

<!-- Or disable instead of hide -->
<button data-permission='{"module":"students","action":"delete"}' 
        data-permission-action="disable">
    Delete
</button>
```

## Admin Interface

### Managing Role Permissions

1. Navigate to: **Settings → Granular Permissions**
2. Select a role from the left sidebar
3. Use checkboxes to grant/revoke permissions per module and action
4. Click "Save Permissions" to apply changes

### Permission Matrix

The admin interface displays a matrix:
- **Rows**: Modules (Students, Fees, Exams, etc.)
- **Columns**: Actions (Create, View, Update, Delete, Approve, Export, etc.)
- **Checkboxes**: Grant/revoke permissions

### User Overrides

To set user-specific overrides, use the PermissionManager API:

```php
// Grant override
PermissionManager::setUserOverride($userId, 'students', 'delete', true, $adminId);

// Deny override
PermissionManager::setUserOverride($userId, 'students', 'delete', false, $adminId);

// Remove override (user inherits from role)
PermissionManager::removeUserOverride($userId, 'students', 'delete', $adminId);
```

## Available Modules

- `dashboard` - Dashboard
- `students` - Students Management
- `admissions` - Admissions
- `academics` - Academics
- `attendance` - Attendance
- `exams` - Examinations
- `fees` - Fees & Finance
- `library` - Library
- `transport` - Transport
- `hostel` - Hostel
- `hr` - HR & Payroll
- `lms` - Learning Management
- `communication` - Communication
- `events` - Events & Calendar
- `certificates` - Certificates
- `reports` - Reports & Analytics
- `settings` - Settings
- `branches` - Branches

## Available Actions

- `create` - Create new records
- `view` - View and read records
- `update` - Edit and modify records
- `delete` - Delete records
- `approve` - Approve requests
- `reject` - Reject requests
- `export` - Export data
- `print` - Print documents
- `import` - Import data
- `manage` - Full management access

## Permission Priority

1. **User Overrides** (Highest Priority)
   - User-specific overrides take precedence over role permissions
   
2. **Role Permissions**
   - Permissions assigned to the user's role
   
3. **Super Admin**
   - Super Admin always has all permissions

## Audit Logging

All permission changes are automatically logged to `permission_audit_log` table with:
- User who made the change
- Target (role or user)
- Module and action
- Change type (grant, revoke, override, etc.)
- Old and new values
- Timestamp and IP address

### Viewing Audit Log

Access audit log via:
- Admin interface: Click "View Audit Log" button
- Programmatically: `PermissionManager::getAuditLog($filters)`

## Best Practices

### 1. Always Check Permissions on Backend

```php
// ✅ Good: Check permission before processing
if (canPerform('students', 'delete')) {
    // Delete student
} else {
    jsonResponse(false, 'Permission denied');
}

// ❌ Bad: Only checking on frontend
// Frontend checks can be bypassed
```

### 2. Use Permission Helpers in UI

```php
// ✅ Good: Use helper functions
echo permissionAwareButton('students', 'create', 'Add Student', 'add.php');

// ❌ Bad: Manual permission checks everywhere
if (canPerform('students', 'create')) {
    echo '<a href="add.php">Add Student</a>';
}
```

### 3. Cache Permissions

The PermissionManager automatically caches permissions for performance. Clear cache when needed:

```php
PermissionManager::clearCache();
```

### 4. Use Descriptive Module and Action Keys

```php
// ✅ Good: Clear and descriptive
canPerform('student_fees', 'create_payment')

// ❌ Bad: Unclear
canPerform('sf', 'cp')
```

## Security Considerations

1. **Server-Side Validation**: Always validate permissions on the server, not just the client
2. **SQL Injection Prevention**: All queries use prepared statements
3. **Audit Trail**: All changes are logged for compliance
4. **Super Admin Protection**: Super Admin role cannot be modified
5. **Session Security**: Permissions are checked on each request

## Troubleshooting

### Permissions Not Working

1. Check if migration was run successfully
2. Verify user has a role assigned
3. Check if permissions are assigned to the role
4. Clear permission cache: `PermissionManager::clearCache()`
5. Check audit log for permission changes

### Performance Issues

1. Permissions are cached automatically
2. Use indexes on permission tables
3. Consider caching user permissions in session (with invalidation on changes)

## API Reference

### PermissionManager Class

```php
// Check permission
PermissionManager::canPerform($userId, $moduleKey, $actionKey)

// Get role permissions
PermissionManager::getRolePermissions($roleId)

// Get user permissions
PermissionManager::getUserPermissions($userId)

// Save role permissions
PermissionManager::saveRolePermissions($roleId, $permissions, $changedBy)

// Set user override
PermissionManager::setUserOverride($userId, $moduleKey, $actionKey, $granted, $changedBy)

// Get audit log
PermissionManager::getAuditLog($filters, $limit, $offset)
```

## Support

For issues or questions:
- Check audit log for permission changes
- Review database schema
- Contact system administrator

---

**Version**: 2.0.0  
**Last Updated**: December 2025  
**Author**: School ERP Development Team

