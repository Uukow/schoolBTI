# Multi-Branch Filter Implementation Guide

## Overview
This document describes the implementation of multi-branch filtering for Super Admin users in the TacliinHub mobile application. Super Admins can now view all branches or filter by a specific branch, while other roles are restricted to their assigned branch.

## Architecture

### 1. Core Components

#### BranchFilterProvider (`lib/presentation/providers/branch_filter_provider.dart`)
- Manages branch selection state
- Handles Super Admin vs. regular user logic
- Persists branch selection in secure storage
- Provides `getBranchIdForApi()` method for repositories

#### BranchSelector Widget (`lib/presentation/widgets/branch_selector.dart`)
- `BranchSelector`: Full dropdown selector for pages
- `BranchSelectorChip`: Compact selector for app bars

#### BranchHelper (`lib/core/branch_helper.dart`)
- Utility class for repositories to get branch_id
- Provides helper methods: `getBranchId()`, `addBranchToParams()`

### 2. Integration Points

#### Main App (`lib/main.dart`)
- BranchFilterProvider is added to MultiProvider
- Initialized after AuthProvider is ready

#### Dashboard (`lib/presentation/pages/dashboard_page.dart`)
- BranchSelector added to body
- BranchSelectorChip added to app bar
- BranchFilterProvider initialized on dashboard load

## Implementation Steps

### Step 1: Update Repositories

All repositories that fetch data should accept an optional `branchId` parameter and include it in API calls.

**Example for StudentRepository:**

```dart
Future<List<Student>> getAllStudents({
  String? status,
  int? classId,
  int? sectionId,
  String? search,
  int? branchId, // Add this
}) async {
  try {
    final uri = Uri.parse('$baseUrl/ajax/students/get-all.php');
    final queryParams = <String, String>{};
    
    // Add branch_id if provided
    if (branchId != null) {
      queryParams['branch_id'] = branchId.toString();
    }
    
    if (status != null) queryParams['status'] = status;
    // ... other params
    
    final response = await http.get(uri.replace(queryParameters: queryParams));
    // ... rest of implementation
  }
}
```

**Using BranchHelper in repositories:**

```dart
import '../../core/branch_helper.dart';

// In method:
final branchId = BranchHelper.getBranchId(context);
if (branchId != null) {
  queryParams['branch_id'] = branchId.toString();
}
```

### Step 2: Update API Endpoints

All API endpoints should:
1. Accept `branch_id` parameter
2. Check user role (Super Admin vs. others)
3. Apply branch filtering accordingly

**Example PHP endpoint pattern:**

```php
<?php
require_once '../../config/config.php';

// Authentication
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
$currentUser = null;
$isSuperAdmin = false;

if ($userId) {
    // Flutter/mobile authentication
    $sql = "SELECT u.*, r.role_name FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    $isSuperAdmin = ($currentUser['role_name'] ?? '') === 'Super Admin';
} else {
    // Web session authentication
    if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
    $currentUser = getCurrentUser();
    $isSuperAdmin = hasRole(['Super Admin']);
}

// Get branch filter
$branchId = null;
if ($isSuperAdmin) {
    // Super Admin can filter by branch or view all
    $branchId = $_GET['branch_id'] ?? null;
} else {
    // Other roles restricted to their branch
    $branchId = $currentUser['branch_id'] ?? null;
}

// Build query with branch filter
$sql = "SELECT * FROM table_name WHERE 1=1";
if ($branchId) {
    $sql .= " AND branch_id = ?";
    $params[] = $branchId;
    $types .= 'i';
}

// Execute query...
```

### Step 3: Update Providers

Providers should accept and pass branch_id to repositories:

```dart
class StudentProvider with ChangeNotifier {
  Future<void> loadStudents({
    String? status,
    int? classId,
    int? sectionId,
    String? search,
    BuildContext? context, // Add context for BranchHelper
  }) async {
    final branchId = BranchHelper.getBranchId(context);
    
    _students = await _repository.getAllStudents(
      status: status,
      classId: classId,
      sectionId: sectionId,
      search: search,
      branchId: branchId, // Pass branch_id
    );
  }
}
```

### Step 4: Add BranchSelector to Pages

For pages that need branch filtering:

```dart
import '../widgets/branch_selector.dart';

// In build method:
Column(
  children: [
    const BranchSelector(), // Add at top
    // ... rest of page content
  ],
)
```

Or in AppBar:

```dart
AppBar(
  actions: [
    BranchSelectorChip(), // Compact version
  ],
)
```

### Step 5: Listen to Branch Changes

When branch selection changes, reload data:

```dart
Consumer<BranchFilterProvider>(
  builder: (context, branchProvider, child) {
    // Reload data when branch changes
    if (branchProvider.selectedBranchId != _lastBranchId) {
      _lastBranchId = branchProvider.selectedBranchId;
      _loadData();
    }
    return YourWidget();
  },
)
```

## Files to Update

### Repositories (Add branch_id parameter):
- `lib/data/repositories/student_repository.dart`
- `lib/data/repositories/teacher_repository.dart`
- `lib/data/repositories/academic_repository.dart`
- `lib/data/repositories/fees_repository.dart`
- `lib/data/repositories/hr_repository.dart`
- `lib/data/repositories/examination_repository.dart`
- `lib/data/repositories/attendance_repository.dart`
- `lib/data/repositories/reports_repository.dart`
- `lib/data/repositories/facilities_repository.dart`
- `lib/data/repositories/library_repository.dart`
- `lib/data/repositories/lms_repository.dart`
- `lib/data/repositories/communication_repository.dart`
- `lib/data/repositories/events_repository.dart`

### API Endpoints (Add branch filtering):
- `ajax/students/get-all.php` ✅ (Already created)
- `ajax/teacher/get-students.php`
- `ajax/academics/get-classes.php`
- `ajax/fees/get-structures.php`
- `ajax/hr/get-staff.php`
- `ajax/exams/get-exams.php`
- `ajax/attendance/get-attendance.php`
- `ajax/reports/generate-*.php`
- All other data-fetching endpoints

### Pages (Add BranchSelector):
- Dashboard ✅ (Already added)
- Students List
- Teachers List
- Classes
- Fees
- HR/Staff
- Reports
- All admin pages

## Testing Checklist

- [ ] Super Admin can see branch selector
- [ ] Super Admin can select "All Branches"
- [ ] Super Admin can select specific branch
- [ ] Branch selection persists across app restarts
- [ ] Data updates when branch selection changes
- [ ] Non-Super Admin users don't see branch selector
- [ ] Non-Super Admin users only see their branch data
- [ ] All pages show correct data based on branch filter
- [ ] API endpoints properly filter by branch
- [ ] No performance issues with branch filtering

## Notes

- Branch selection is stored in secure storage
- Default for Super Admin is "All Branches" (null)
- Branch filtering is applied at the API level for security
- All data automatically updates when branch selection changes
- The implementation follows the same pattern as the web version

