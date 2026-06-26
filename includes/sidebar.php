<?php
/**
 * Sidebar Navigation Component
 * 
 * Reusable sidebar navigation for all pages
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

$currentUser = getCurrentUser();
$roleName = $currentUser['role_name'];

// Role helpers for cleaner menu logic
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin      = hasRole(['Super Admin', 'Admin']);
$isTeacher    = hasRole(['Teacher']);
$isStudent    = hasRole(['Student']);
$isStaff      = hasRole(['Staff']);
$isAccountant = hasRole(['Accountant']);
$isHrAdmin    = $isAdmin;
$isHrSelf     = ($isTeacher || $isStaff) && !$isAdmin;
$showHrMenu   = $isAdmin || $isHrSelf || $isAccountant;

/**
 * Render a second-level collapsible group with third-level links.
 */
function sidebarThirdLevelMenu(string $collapseId, string $label, array $links): void
{
    if (empty($links)) {
        return;
    }
    $safeId = htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8');
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    echo '<li class="side-nav-item">';
    echo '<a data-bs-toggle="collapse" href="#' . $safeId . '" aria-expanded="false" aria-controls="' . $safeId . '">';
    echo '<span>' . $safeLabel . '</span><span class="menu-arrow"></span></a>';
    echo '<div class="collapse" id="' . $safeId . '"><ul class="side-nav-third-level">';
    foreach ($links as $link) {
        $url = htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8');
        echo '<li><a href="' . $url . '">' . $text . '</a></li>';
    }
    echo '</ul></div></li>';
}

// Get system logo from settings
$systemLogo = null;
if (class_exists('SettingsManager')) {
    $settingsManager = SettingsManager::getInstance();
    $systemLogo = $settingsManager->get('system_logo');
} else {
    // Fallback: direct database query
    try {
        $logoSql = "SELECT system_logo FROM system_settings LIMIT 1";
        $logoStmt = executeQuery($logoSql);
        $logoSettings = fetchOne($logoStmt);
        $systemLogo = $logoSettings['system_logo'] ?? null;
    } catch (Exception $e) {
        $systemLogo = null;
    }
}

// Determine logo URLs
$logoLight = !empty($systemLogo) && file_exists(ABSPATH . $systemLogo) 
    ? APP_URL . $systemLogo 
    : APP_URL . 'template_extracted/assets/images/logo.png';
    
$logoDark = !empty($systemLogo) && file_exists(ABSPATH . $systemLogo) 
    ? APP_URL . $systemLogo 
    : APP_URL . 'template_extracted/assets/images/logo-dark.png';
    
$logoSm = !empty($systemLogo) && file_exists(ABSPATH . $systemLogo) 
    ? APP_URL . $systemLogo 
    : APP_URL . 'template_extracted/assets/images/logo-sm.png';
    
$logoDarkSm = !empty($systemLogo) && file_exists(ABSPATH . $systemLogo) 
    ? APP_URL . $systemLogo 
    : APP_URL . 'template_extracted/assets/images/logo-dark-sm.png';
?>

<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">

    <!-- Brand Logo Light -->
    <a href="<?php echo APP_URL; ?>dashboard.php" class="logo logo-light">
        <span class="logo-lg">
            <img src="<?php echo $logoLight; ?>" alt="logo" style="max-height: 40px; width: auto;">
        </span>
        <span class="logo-sm">
            <img src="<?php echo $logoSm; ?>" alt="small logo" style="max-height: 32px; width: auto;">
        </span>
    </a>

    <!-- Brand Logo Dark -->
    <a href="<?php echo APP_URL; ?>dashboard.php" class="logo logo-dark">
        <span class="logo-lg">
            <img src="<?php echo $logoDark; ?>" alt="dark logo" style="max-height: 40px; width: auto;">
        </span>
        <span class="logo-sm">
            <img src="<?php echo $logoDarkSm; ?>" alt="small logo" style="max-height: 32px; width: auto;">
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>

    <!-- Full Sidebar Menu Close Button -->
    <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div>

    <!-- Sidebar -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <!-- Leftbar User -->
        <div class="leftbar-user">
            <a href="<?php echo APP_URL; ?>profile.php">
                <?php if (!empty($currentUser['photo'])): ?>
                    <img src="<?php echo APP_URL . $currentUser['photo']; ?>" alt="user-image" height="42" class="rounded-circle shadow-sm">
                <?php else: ?>
                    <img src="<?php echo APP_URL; ?>template_extracted/assets/images/users/avatar-1.jpg" alt="user-image" height="42" class="rounded-circle shadow-sm">
                <?php endif; ?>
                <span class="leftbar-user-name mt-2"><?php echo htmlspecialchars($currentUser['username']); ?></span>
            </a>
        </div>

        <!--- Sidemenu -->
        <ul class="side-nav">

            <li class="side-nav-title">Navigation</li>

            <!-- Dashboard -->
            <li class="side-nav-item">
                <a href="<?php echo APP_URL; ?>dashboard.php" class="side-nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                    <i class="ri-dashboard-3-line"></i>
                    <span> Dashboard </span>
                </a>
            </li>

            <?php if ($isTeacher || $isSuperAdmin): ?>
            <li class="side-nav-title">My Portal</li>
            <!-- Teacher Portal -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarTeacher" aria-expanded="false" aria-controls="sidebarTeacher" class="side-nav-link">
                    <i class="ri-user-star-line"></i>
                    <span> Teacher Portal </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarTeacher">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/teacher/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/teacher/my-classes.php">My Classes</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/teacher/my-timetable.php">My Timetable</a></li>
                        <?php if ($isTeacher): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/teacher/my-profile.php">My Profile</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <?php if ($isStudent || $isSuperAdmin): ?>
            <!-- Student Portal -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarStudent" aria-expanded="false" aria-controls="sidebarStudent" class="side-nav-link">
                    <i class="ri-graduation-cap-line"></i>
                    <span> Student Portal </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarStudent">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/student/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/student/my-classes.php">My Classes</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/student/my-timetable.php">My Timetable</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/student/my-attendance.php">My Attendance</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/student/my-marks.php">My Marks</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/student/my-assignments.php">My Assignments</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/student/my-certificates.php">My Certificates</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/student/my-fees.php">My Fees & Invoices</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/student/my-payments.php">My Payments</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/student/my-receipts.php">My Receipts</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/student/financial-statement.php">Financial Statement</a></li>
                        <?php if (hasRole(['Student'])): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/student/my-profile.php">My Profile</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin || hasRole(['Receptionist', 'Teacher'])): ?>
            <li class="side-nav-title">School Management</li>

            <?php if ($isAdmin): ?>
            <!-- Branch Management -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarBranches" aria-expanded="false" aria-controls="sidebarBranches" class="side-nav-link">
                    <i class="ri-building-line"></i>
                    <span> Branches </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarBranches">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/branches/list.php">All Branches</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/branches/add.php">Add Branch</a></li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <!-- Students -->
            <?php if (hasRole(['Super Admin', 'Admin', 'Receptionist', 'Teacher'])): ?>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarStudents" aria-expanded="false" aria-controls="sidebarStudents" class="side-nav-link">
                    <i class="ri-user-3-line"></i>
                    <span> Students </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarStudents">
                    <ul class="side-nav-second-level">
                        <?php if (hasRole(['Super Admin', 'Admin', 'Receptionist'])): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/students/list.php">All Students</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/students/add.php">Add Student</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/students/assign-sections.php">Assign Sections</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/students/promote.php">Promote Students</a></li>
                        <?php endif; ?>
                        <?php if (hasRole(['Teacher'])): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/teacher/my-students.php">My Students</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo APP_URL; ?>modules/students/reports.php">Student Reports</a></li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <!-- Admissions -->
            <?php if (hasRole(['Super Admin', 'Admin', 'Receptionist'])): ?>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarAdmissions" aria-expanded="false" aria-controls="sidebarAdmissions" class="side-nav-link">
                    <i class="ri-file-user-line"></i>
                    <span> Admissions </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarAdmissions">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/admissions/list.php">Applications</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/admissions/pending.php">Pending Review</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/admissions/approved.php">Approved</a></li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <!-- Academics -->
            <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarAcademics" aria-expanded="false" aria-controls="sidebarAcademics" class="side-nav-link">
                    <i class="ri-book-open-line"></i>
                    <span> Academics </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarAcademics">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/academics/classes.php">Classes</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/academics/subjects.php">Subjects</a></li>
                        <?php if ($isAdmin): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/academics/assignments.php">Assignments</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo APP_URL; ?>modules/academics/timetable.php">Timetable</a></li>
                        <?php if ($isAdmin || $isTeacher): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/academics/lesson-plans.php">Lesson Plans</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo APP_URL; ?>modules/academics/syllabus.php">Syllabus</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/academics/calendar.php">Academic Calendar</a></li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($isAdmin || $isTeacher): ?>
            <li class="side-nav-title">Academic Operations</li>

            <!-- Attendance -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarAttendance" aria-expanded="false" aria-controls="sidebarAttendance" class="side-nav-link">
                    <i class="ri-calendar-check-line"></i>
                    <span> Attendance </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarAttendance">
                    <ul class="side-nav-second-level">
                        <?php if (hasRole(['Admin', 'Super Admin'])): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/attendance/dashboard.php">Attendance Dashboard</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/attendance/view-students.php">View Students List</a></li>
                        <?php endif; ?>
                        <?php if (hasRole(['Teacher', 'Admin', 'Super Admin'])): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/attendance/student.php">Student Attendance</a></li>
                        <?php endif; ?>
                        <?php if (hasRole(['Admin', 'Super Admin'])): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/attendance/staff.php">Staff Attendance</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo APP_URL; ?>modules/attendance/reports.php">Reports</a></li>
                    </ul>
                </div>
            </li>

            <!-- Examinations -->
            <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarExams" aria-expanded="false" aria-controls="sidebarExams" class="side-nav-link">
                    <i class="ri-file-list-3-line"></i>
                    <span> Examinations </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarExams">
                    <ul class="side-nav-second-level">
                        <?php if (hasRole(['Admin', 'Super Admin'])): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/exams/manage.php">Manage Exams</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/exams/schedule.php">Exam Schedule</a></li>
                        <?php endif; ?>
                        <?php if (hasRole(['Teacher', 'Admin', 'Super Admin'])): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/exams/marks-entry.php">Enter Marks</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo APP_URL; ?>modules/exams/results.php">Results</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/exams/report-cards.php">Report Cards</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/exams/analytics.php">Analytics</a></li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <!-- Certificates & Transcripts -->
            <?php if ($isAdmin): ?>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarCertificates" aria-expanded="false" aria-controls="sidebarCertificates" class="side-nav-link">
                    <i class="ri-award-line"></i>
                    <span> Certificates </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarCertificates">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/certificates/list.php">Certificates List</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/certificates/grading-schemes.php">Grading Schemes</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/certificates/templates.php">Certificate Templates</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/certificates/generate.php">Generate Certificates</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/certificates/class-graduation.php">Class Graduation</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/certificates/transcripts.php">Academic Transcripts</a></li>
                        <li><a href="<?php echo APP_URL; ?>verify-certificate.php">Verify Certificate</a></li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Library (teachers, students, librarians) -->
            <?php if (hasRole(['Librarian', 'Teacher', 'Student']) && !$isAdmin && !$isAccountant): ?>
            <li class="side-nav-title">Resources</li>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarLibrary" aria-expanded="false" aria-controls="sidebarLibrary" class="side-nav-link">
                    <i class="ri-book-2-line"></i>
                    <span> Library </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarLibrary">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/library/books.php">Books</a></li>
                        <?php if (hasRole(['Librarian'])): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/library/issue.php">Issue Book</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/library/return.php">Return Book</a></li>
                        <?php endif; ?>
                        <?php if ($isTeacher): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/library/my-resources.php">My Resources</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo APP_URL; ?>modules/library/history.php">Issue History</a></li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin || $isAccountant): ?>
            <li class="side-nav-title">Finance &amp; Resources</li>

            <!-- Fees & Finance -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarFees" aria-expanded="false" aria-controls="sidebarFees" class="side-nav-link">
                    <i class="ri-money-dollar-circle-line"></i>
                    <span> Fees & Finance </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarFees">
                    <ul class="side-nav-second-level">
                        <?php
                        sidebarThirdLevelMenu('sidebarFeesSetup', 'Fee Setup', [
                            ['url' => APP_URL . 'modules/fees/structure.php', 'label' => 'Fee Structure'],
                            ['url' => APP_URL . 'modules/fees/monthly-assignment.php', 'label' => 'Monthly Fee Assignment'],
                        ]);
                        sidebarThirdLevelMenu('sidebarFeesBilling', 'Billing', [
                            ['url' => APP_URL . 'modules/fees/student-ledger.php', 'label' => 'Student Fee Ledger'],
                            ['url' => APP_URL . 'modules/fees/invoices.php', 'label' => 'Invoices'],
                            ['url' => APP_URL . 'modules/fees/defaulters.php', 'label' => 'Defaulters'],
                        ]);
                        sidebarThirdLevelMenu('sidebarFeesPayments', 'Payments & Accounts', [
                            ['url' => APP_URL . 'modules/fees/flexible-payment.php', 'label' => 'Flexible Payment'],
                            ['url' => APP_URL . 'modules/fees/payments.php', 'label' => 'Payments'],
                            ['url' => APP_URL . 'modules/fees/income.php', 'label' => 'Income'],
                            ['url' => APP_URL . 'modules/fees/expenses.php', 'label' => 'Expenses'],
                        ]);
                        ?>
                        <li><a href="<?php echo APP_URL; ?>modules/fees/reports.php">Finance Reports</a></li>
                    </ul>
                </div>
            </li>

            <!-- Library -->
            <?php if ($isAdmin): ?>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarLibraryAdmin" aria-expanded="false" aria-controls="sidebarLibraryAdmin" class="side-nav-link">
                    <i class="ri-book-2-line"></i>
                    <span> Library </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarLibraryAdmin">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/library/books.php">Books</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/library/issue.php">Issue Book</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/library/return.php">Return Book</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/library/history.php">Issue History</a></li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <!-- Transport & Hostel -->
            <?php if ($isAdmin): ?>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarFacilities" aria-expanded="false" aria-controls="sidebarFacilities" class="side-nav-link">
                    <i class="ri-bus-line"></i>
                    <span> Facilities </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarFacilities">
                    <ul class="side-nav-second-level">
                        <?php
                        sidebarThirdLevelMenu('sidebarFacilitiesHostel', 'Hostels', [
                            ['url' => APP_URL . 'modules/facilities/hostels.php', 'label' => 'Hostels'],
                            ['url' => APP_URL . 'modules/facilities/hostel-rooms.php', 'label' => 'Hostel Rooms'],
                            ['url' => APP_URL . 'modules/facilities/hostel-allocations.php', 'label' => 'Hostel Allocations'],
                        ]);
                        sidebarThirdLevelMenu('sidebarFacilitiesTransport', 'Transport', [
                            ['url' => APP_URL . 'modules/facilities/transport-routes.php', 'label' => 'Transport Routes'],
                            ['url' => APP_URL . 'modules/facilities/transport-vehicles.php', 'label' => 'Vehicles'],
                            ['url' => APP_URL . 'modules/facilities/transport-assignments.php', 'label' => 'Transport Assignments'],
                            ['url' => APP_URL . 'modules/facilities/transport-maintenance.php', 'label' => 'Vehicle Maintenance'],
                        ]);
                        ?>
                    </ul>
                </div>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($showHrMenu): ?>
            <li class="side-nav-title">Human Resources</li>

            <!-- HR & Payroll -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarHR" aria-expanded="false" aria-controls="sidebarHR" class="side-nav-link">
                    <i class="ri-team-line"></i>
                    <span> HR Management </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarHR">
                    <ul class="side-nav-second-level">

                        <?php if ($isAdmin || $isAccountant): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/hr/dashboard.php">HR Dashboard</a></li>
                        <?php endif; ?>

                        <?php if ($isHrAdmin): ?>
                        <?php
                        sidebarThirdLevelMenu('sidebarHrWorkforce', 'Workforce', [
                            ['url' => APP_URL . 'modules/hr/staff.php', 'label' => 'Staff Directory'],
                            ['url' => APP_URL . 'modules/hr/employee-documents.php', 'label' => 'Employee Documents'],
                            ['url' => APP_URL . 'modules/hr/contracts.php', 'label' => 'Contracts'],
                            ['url' => APP_URL . 'modules/hr/performance.php', 'label' => 'Performance Reviews'],
                        ]);
                        sidebarThirdLevelMenu('sidebarHrPayroll', 'Payroll', [
                            ['url' => APP_URL . 'modules/hr/payroll.php', 'label' => 'Payroll Setup'],
                            ['url' => APP_URL . 'modules/hr/payroll-runs.php', 'label' => 'Payroll Runs'],
                            ['url' => APP_URL . 'modules/hr/advance-salary.php', 'label' => 'Advance Salary'],
                        ]);
                        sidebarThirdLevelMenu('sidebarHrTimeAttendance', 'Time & Attendance', [
                            ['url' => APP_URL . 'modules/hr/attendance-rules.php', 'label' => 'Attendance Rules'],
                            ['url' => APP_URL . 'modules/hr/holidays.php', 'label' => 'Public Holidays'],
                            ['url' => APP_URL . 'modules/hr/qr-attendance.php', 'label' => 'QR Attendance'],
                            ['url' => APP_URL . 'modules/hr/attendance-corrections.php', 'label' => 'Attendance Corrections'],
                        ]);
                        sidebarThirdLevelMenu('sidebarHrLeave', 'Leave', [
                            ['url' => APP_URL . 'modules/hr/leaves.php', 'label' => 'Leave Management'],
                            ['url' => APP_URL . 'modules/hr/leave-calendar.php', 'label' => 'Leave Calendar'],
                        ]);
                        sidebarThirdLevelMenu('sidebarHrOperations', 'Operations', [
                            ['url' => APP_URL . 'modules/hr/vacancies.php', 'label' => 'Recruitment'],
                            ['url' => APP_URL . 'modules/hr/ppdp-programs.php', 'label' => 'PPDP Programs'],
                            ['url' => APP_URL . 'modules/hr/quotations.php', 'label' => 'Quotations'],
                            ['url' => APP_URL . 'modules/hr/item-requests.php', 'label' => 'Item Requests'],
                            ['url' => APP_URL . 'modules/hr/grievances.php', 'label' => 'Grievances'],
                        ]);
                        ?>
                        <li><a href="<?php echo APP_URL; ?>modules/hr/reports.php">HR Reports</a></li>

                        <?php elseif ($isHrSelf): ?>
                        <?php
                        sidebarThirdLevelMenu('sidebarHrSelfLeave', 'Leave & Time', [
                            ['url' => APP_URL . 'modules/hr/leaves.php', 'label' => 'Leave Management'],
                            ['url' => APP_URL . 'modules/hr/leave-calendar.php', 'label' => 'Leave Calendar'],
                            ['url' => APP_URL . 'modules/hr/attendance-corrections.php', 'label' => 'Attendance Corrections'],
                        ]);
                        sidebarThirdLevelMenu('sidebarHrSelfPayroll', 'My Payroll', [
                            ['url' => APP_URL . 'modules/hr/my-payslips.php', 'label' => 'My Payslips'],
                            ['url' => APP_URL . 'modules/hr/advance-salary.php', 'label' => 'Advance Salary'],
                        ]);
                        sidebarThirdLevelMenu('sidebarHrSelfServices', 'Employee Services', [
                            ['url' => APP_URL . 'modules/hr/grievances.php', 'label' => 'Grievances'],
                            ['url' => APP_URL . 'modules/hr/item-requests.php', 'label' => 'Item Requests'],
                            ['url' => APP_URL . 'modules/hr/ppdp-programs.php', 'label' => 'PPDP Programs'],
                        ]);
                        ?>

                        <?php elseif ($isAccountant): ?>
                        <?php
                        sidebarThirdLevelMenu('sidebarHrAcctPayroll', 'Payroll', [
                            ['url' => APP_URL . 'modules/hr/payroll.php', 'label' => 'Payroll Setup'],
                            ['url' => APP_URL . 'modules/hr/payroll-runs.php', 'label' => 'Payroll Runs'],
                            ['url' => APP_URL . 'modules/hr/advance-salary.php', 'label' => 'Advance Salary'],
                        ]);
                        sidebarThirdLevelMenu('sidebarHrAcctReports', 'Reviews & Reports', [
                            ['url' => APP_URL . 'modules/hr/performance.php', 'label' => 'Performance Reviews'],
                            ['url' => APP_URL . 'modules/hr/reports.php', 'label' => 'HR Reports'],
                        ]);
                        ?>
                        <?php endif; ?>

                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <?php if ($isTeacher || $isStudent || $isAdmin): ?>
            <li class="side-nav-title">Learning &amp; Communication</li>

            <!-- Learning Management -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarLMS" aria-expanded="false" aria-controls="sidebarLMS" class="side-nav-link">
                    <i class="ri-graduation-cap-line"></i>
                    <span> LMS </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarLMS">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/lms/materials.php">Study Materials</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/lms/assignments.php">Assignments</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/lms/quizzes.php">Online Quizzes</a></li>
                    </ul>
                </div>
            </li>

            <!-- Communication -->
            <?php if (hasRole(['Super Admin', 'Admin', 'Teacher'])): ?>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarComm" aria-expanded="false" aria-controls="sidebarComm" class="side-nav-link">
                    <i class="ri-message-3-line"></i>
                    <span> Communication </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarComm">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/communication/announcements.php">Announcements</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/communication/messages.php">Messages</a></li>
                        <?php if (hasRole(['Admin', 'Super Admin'])): ?>
                        <li><a href="<?php echo APP_URL; ?>modules/communication/send-sms.php">Send SMS</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/communication/send-email.php">Send Email</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <!-- Events & Calendar -->
            <?php if (hasRole(['Super Admin', 'Admin', 'Teacher', 'Student'])): ?>
            <li class="side-nav-item">
                <a href="<?php echo APP_URL; ?>modules/events/calendar.php" class="side-nav-link">
                    <i class="ri-calendar-event-line"></i>
                    <span> Events & Calendar </span>
                </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
            <li class="side-nav-title">Reports &amp; Analytics</li>

            <!-- Reports & Analytics -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarReports" aria-expanded="false" aria-controls="sidebarReports" class="side-nav-link">
                    <i class="ri-bar-chart-box-line"></i>
                    <span> Reports </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarReports">
                    <ul class="side-nav-second-level">
                        <li><a href="<?php echo APP_URL; ?>modules/reports/students.php">Student Reports</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/reports/academic.php">Academic Reports</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/reports/financial.php">Financial Reports</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/reports/attendance-reports.php">Attendance Reports</a></li>
                        <li><a href="<?php echo APP_URL; ?>modules/reports/custom-reports.php">Custom Reports</a></li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <li class="side-nav-title">System</li>

            <!-- Settings -->
            <?php if (hasRole(['Super Admin', 'Admin'])): ?>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarSettings" aria-expanded="false" aria-controls="sidebarSettings" class="side-nav-link">
                    <i class="ri-settings-3-line"></i>
                    <span> Settings </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarSettings">
                    <ul class="side-nav-second-level">
                        <?php
                        sidebarThirdLevelMenu('sidebarSettingsSystem', 'System', [
                            ['url' => APP_URL . 'modules/settings/general.php', 'label' => 'General Settings'],
                            ['url' => APP_URL . 'modules/settings/academic.php', 'label' => 'Academic Settings'],
                            ['url' => APP_URL . 'modules/settings/backup.php', 'label' => 'Backup & Restore'],
                            ['url' => APP_URL . 'modules/settings/about.php', 'label' => 'About & License'],
                        ]);
                        $accessLinks = [
                            ['url' => APP_URL . 'modules/settings/users.php', 'label' => 'User Management'],
                            ['url' => APP_URL . 'modules/settings/roles.php', 'label' => 'Roles & Permissions'],
                        ];
                        if ($isSuperAdmin) {
                            $accessLinks[] = ['url' => APP_URL . 'modules/settings/permissions.php', 'label' => 'Granular Permissions'];
                        }
                        sidebarThirdLevelMenu('sidebarSettingsAccess', 'Access Control', $accessLinks);
                        ?>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <!-- Support -->
            <li class="side-nav-item">
                <a href="<?php echo APP_URL; ?>modules/support/tickets.php" class="side-nav-link">
                    <i class="ri-customer-service-2-line"></i>
                    <span> Support Tickets </span>
                </a>
            </li>

        </ul>
        <!--- End Sidemenu -->

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->


