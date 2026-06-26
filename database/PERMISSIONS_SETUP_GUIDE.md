# Granular Permissions System - Setup Guide

## Quick Start

### Step 1: Run the Migration

Execute the migration script:

```bash
php database/run_granular_permissions_migration.php
```

Or manually run the SQL:

```bash
mysql -u root -p schoolerp_db < database/granular_permissions_migration.sql
```

### Step 2: Verify Installation

1. Log in as Super Admin
2. Navigate to: **Settings → Granular Permissions**
3. You should see a permissions matrix with modules and actions

### Step 3: Configure Permissions

1. Select a role from the left sidebar
2. Use checkboxes to grant/revoke permissions
3. Click "Save Permissions"

## Default Setup

After migration:
- **Super Admin** automatically gets all permissions for all modules
- All other roles start with no permissions (you need to assign them)
- Default modules and actions are pre-populated

## Default Modules

- Dashboard
- Students
- Admissions
- Academics
- Attendance
- Examinations
- Fees & Finance
- Library
- Transport
- Hostel
- HR & Payroll
- Learning Management
- Communication
- Events & Calendar
- Certificates
- Reports & Analytics
- Settings
- Branches

## Default Actions

- Create
- View
- Update
- Delete
- Approve
- Reject
- Export
- Print
- Import
- Manage (full access)

## Common Permission Scenarios

### Scenario 1: Teacher Role
- **Students**: View only
- **Attendance**: Create, View, Update
- **Exams**: Create, View, Update (for marks entry)
- **LMS**: Create, View, Update (for assignments)

### Scenario 2: Accountant Role
- **Fees**: All actions (Create, View, Update, Delete, Export, Print)
- **Students**: View only (to see fee records)
- **Reports**: View, Export, Print (for financial reports)

### Scenario 3: Librarian Role
- **Library**: All actions
- **Students**: View only (to see library members)

## Troubleshooting

### Issue: Permissions not working

**Solution:**
1. Clear permission cache: `PermissionManager::clearCache()`
2. Check if user has a role assigned
3. Verify permissions are saved in database
4. Check audit log for recent changes

### Issue: Migration fails

**Solution:**
1. Check database connection
2. Verify MySQL version (5.7+ required)
3. Check for existing tables (migration uses IF NOT EXISTS)
4. Review error messages in migration output

### Issue: Super Admin can't access permissions page

**Solution:**
1. Verify user has "Super Admin" role
2. Check role_name in users table matches exactly
3. Clear browser cache and session

## Next Steps

1. **Configure Role Permissions**: Set up permissions for each role
2. **Test Permissions**: Verify permissions work in different modules
3. **Set User Overrides** (if needed): Grant/deny specific permissions to individual users
4. **Review Audit Log**: Monitor permission changes

## Support

For detailed documentation, see:
- `docs/PERMISSIONS_SYSTEM.md` - Full system documentation
- `docs/PERMISSIONS_EXAMPLES.php` - Code examples

---

**Note**: Always backup your database before running migrations!

