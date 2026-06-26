<?php
/**
 * New Admission Application (Internal)
 * 
 * Staff can create admission applications
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Receptionist']);

$pageTitle = 'New Admission Application';

// Get current session
$currentSession = getCurrentSession();
$currentUser = getCurrentUser();

$errors = [];

// Get branches and classes
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branchId = (int)($_POST['branch_id'] ?? 0);
    $classId = (int)($_POST['class_id'] ?? 0);
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $parentName = sanitize($_POST['parent_name'] ?? '');
    $parentPhone = sanitize($_POST['parent_phone'] ?? '');
    $parentEmail = sanitize($_POST['parent_email'] ?? '');
    $previousSchool = sanitize($_POST['previous_school'] ?? '');
    
    // Validation
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($gender)) $errors[] = 'Gender is required';
    if (empty($dateOfBirth)) $errors[] = 'Date of birth is required';
    if ($branchId <= 0) $errors[] = 'Branch is required';
    if ($classId <= 0) $errors[] = 'Class is required';
    if (empty($parentName)) $errors[] = 'Parent name is required';
    if (empty($parentPhone)) $errors[] = 'Parent phone is required';
    
    if (empty($errors)) {
        // Generate application number
        $sql = "SELECT MAX(id) as max_id FROM admission_applications";
        $result = executeQuery($sql);
        $row = fetchOne($result);
        $nextId = ($row['max_id'] ?? 0) + 1;
        $applicationNo = generateUniqueId(APPLICATION_PREFIX, $nextId, 6);
        
        // Insert application
        $sql = "INSERT INTO admission_applications (
            application_no, branch_id, session_id, class_id,
            first_name, last_name, gender, date_of_birth,
            email, phone, address,
            parent_name, parent_phone, parent_email,
            previous_school, status, reviewed_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Under Review', ?)";
        
        $stmt = executeQuery($sql, 'siiisssssssssssi', [
            $applicationNo, $branchId, $currentSession['id'], $classId,
            $firstName, $lastName, $gender, $dateOfBirth,
            $email, $phone, $address,
            $parentName, $parentPhone, $parentEmail,
            $previousSchool, $currentUser['id']
        ]);
        
        if ($stmt) {
            logActivity($currentUser['id'], 'Create Application', 'Admissions', "Created application: $applicationNo");
            
            $_SESSION['success'] = "Application created successfully! Application No: $applicationNo";
            redirect(APP_URL . 'modules/admissions/list.php');
        } else {
            $errors[] = 'Failed to create application';
        }
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
                        <div class="page-title-right">
                            <a href="<?php echo APP_URL; ?>modules/admissions/list.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Applications
                            </a>
                        </div>
                        <h4 class="page-title">New Admission Application</h4>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Please correct the following:</strong>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Application Form -->
            <form method="POST" action="" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Branch & Class Selection -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Branch & Class</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Branch</label>
                                        <select class="form-select" name="branch_id" required>
                                            <option value="">Select Branch</option>
                                            <?php foreach ($branches as $branch): ?>
                                                <option value="<?php echo $branch['id']; ?>">
                                                    <?php echo htmlspecialchars($branch['branch_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Applying for Class</label>
                                        <select class="form-select" name="class_id" required>
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>">
                                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Student Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Student Information</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">First Name</label>
                                        <input type="text" class="form-control" name="first_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Gender</label>
                                        <select class="form-select" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Date of Birth</label>
                                        <input type="date" class="form-control" name="date_of_birth" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" class="form-control" name="phone">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Parent Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Parent/Guardian Information</h4>
                                
                                <div class="mb-3">
                                    <label class="form-label required">Parent/Guardian Name</label>
                                    <input type="text" class="form-control" name="parent_name" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Parent Phone</label>
                                        <input type="text" class="form-control" name="parent_phone" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Parent Email</label>
                                        <input type="email" class="form-control" name="parent_email">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Previous School (if any)</label>
                                    <input type="text" class="form-control" name="previous_school">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Info Card -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Application Info</h5>
                                <p class="text-muted mb-2">
                                    <i class="ri-information-line"></i> This application will be:
                                </p>
                                <ul class="text-muted mb-0">
                                    <li>Auto-assigned an application number</li>
                                    <li>Set to "Under Review" status</li>
                                    <li>Ready for approval or interview</li>
                                    <li>Can be enrolled directly</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 btn-lg">
                                    <i class="ri-send-plane-line"></i> Submit Application
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

