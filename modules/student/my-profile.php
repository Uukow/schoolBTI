<?php
/**
 * My Profile - Student Portal
 * 
 * View student's own profile
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(studentPortalRoles(), APP_URL . 'modules/student/dashboard.php');

$pageTitle = 'My Profile';

// Get current user and student record
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);

$student = null;
$studentId = null;

if ($isSuperAdmin) {
    $studentId = null;
} else {
    $student = getStudentByUserId($currentUser['id']);
    if (!$student) {
        $_SESSION['error'] = 'Student profile not found. Please contact administrator to link your user account to a student record.';
        $studentId = null;
    } else {
        $studentId = $student['id'];
    }
}

// Get additional student information
if (!$isSuperAdmin && $student) {
    // Get class and section info
    $sql = "SELECT s.*, c.class_name, sec.section_name, b.branch_name
            FROM students s
            LEFT JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE s.id = ?";
    $stmt = executeQuery($sql, 'i', [$studentId]);
    $student = fetchOne($stmt);

    // Get parent information
    $parentSql = "SELECT p.*, sp.relationship, sp.is_primary
                  FROM parents p
                  INNER JOIN student_parents sp ON p.id = sp.parent_id
                  WHERE sp.student_id = ?
                  ORDER BY sp.is_primary DESC";
    $parents = fetchAll(executeQuery($parentSql, 'i', [$studentId]));

    // Get attendance summary
    $attendanceSql = "SELECT 
        COUNT(*) as total_days,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
        SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_days
        FROM student_attendance 
        WHERE student_id = ?";
    $attendanceStats = fetchOne(executeQuery($attendanceSql, 'i', [$studentId]));
    
    $attendancePercentage = 0;
    if ($attendanceStats['total_days'] > 0) {
        $attendancePercentage = round(($attendanceStats['present_days'] / $attendanceStats['total_days']) * 100, 2);
    }
}

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
                        <h4 class="page-title">My Profile</h4>
                    </div>
                </div>
            </div>

            <?php if (!$isSuperAdmin && $student): ?>
            <div class="row">
                <!-- Profile Information -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Personal Information</h4>
                            
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <?php if (!empty($student['photo'])): ?>
                                        <img src="<?php echo APP_URL . $student['photo']; ?>" alt="Student Photo" class="img-thumbnail" style="max-width: 200px;">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 200px; height: 200px;">
                                            <i class="ri-user-3-line" style="font-size: 80px; color: #ccc;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-9">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Student ID:</th>
                                            <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Admission No:</th>
                                            <td><?php echo htmlspecialchars($student['admission_no']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Name:</th>
                                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Date of Birth:</th>
                                            <td><?php echo formatDate($student['date_of_birth']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Gender:</th>
                                            <td><?php echo htmlspecialchars($student['gender']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Phone:</th>
                                            <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <h5 class="mb-3">Academic Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Branch:</th>
                                    <td><?php echo htmlspecialchars($student['branch_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Class:</th>
                                    <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Section:</th>
                                    <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Admission Date:</th>
                                    <td><?php echo formatDate($student['admission_date']); ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-<?php echo $student['status'] == 'Active' ? 'success' : 'secondary'; ?>">
                                            <?php echo htmlspecialchars($student['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <?php if (!empty($student['address'])): ?>
                            <hr>
                            <h5 class="mb-3">Contact Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Address:</th>
                                    <td><?php echo nl2br(htmlspecialchars($student['address'])); ?></td>
                                </tr>
                                <?php if (!empty($student['city'])): ?>
                                <tr>
                                    <th>City:</th>
                                    <td><?php echo htmlspecialchars($student['city']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($student['state'])): ?>
                                <tr>
                                    <th>State:</th>
                                    <td><?php echo htmlspecialchars($student['state']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Statistics & Parents -->
                <div class="col-xl-4">
                    <!-- Attendance Summary -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Attendance Summary</h4>
                            <div class="text-center mb-3">
                                <h2 class="mb-0"><?php echo $attendancePercentage; ?>%</h2>
                                <p class="text-muted">Overall Attendance</p>
                            </div>
                            <table class="table table-sm">
                                <tr>
                                    <td>Total Days:</td>
                                    <td class="text-end"><strong><?php echo $attendanceStats['total_days']; ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Present:</td>
                                    <td class="text-end text-success"><strong><?php echo $attendanceStats['present_days']; ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Absent:</td>
                                    <td class="text-end text-danger"><strong><?php echo $attendanceStats['absent_days']; ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Late:</td>
                                    <td class="text-end text-warning"><strong><?php echo $attendanceStats['late_days']; ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Parent/Guardian Information -->
                    <?php if (!empty($parents)): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Parent/Guardian</h4>
                            <?php foreach ($parents as $parent): ?>
                                <div class="mb-3">
                                    <h5><?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?></h5>
                                    <p class="mb-1">
                                        <strong>Relationship:</strong> <?php echo htmlspecialchars($parent['relationship']); ?>
                                        <?php if ($parent['is_primary']): ?>
                                            <span class="badge bg-primary ms-1">Primary</span>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($parent['phone'])): ?>
                                        <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($parent['phone']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($parent['email'])): ?>
                                        <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($parent['email']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($parent !== end($parents)): ?><hr><?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="ri-information-line"></i> Student profile information is not available.
            </div>
            <?php endif; ?>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

