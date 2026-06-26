<?php
/**
 * Online Admission Application Form (Public)
 * 
 * Public-facing form for admission applications
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

defined('ABSPATH') or define('ABSPATH', dirname(__FILE__) . '/');
require_once ABSPATH . 'config/config.php';

$success = false;
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branchId = $_POST['branch_id'] ?? '';
    $classId = $_POST['class_id'] ?? '';
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
    if (empty($branchId)) $errors[] = 'Branch is required';
    if (empty($classId)) $errors[] = 'Class is required';
    if (empty($parentName)) $errors[] = 'Parent name is required';
    if (empty($parentPhone)) $errors[] = 'Parent phone is required';
    
    if (empty($errors)) {
        // Get current session
        $session = getCurrentSession();
        $sessionId = $session['id'] ?? 1;
        
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
            previous_school, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = executeQuery($sql, 'siiisssssssssss', [
            $applicationNo, $branchId, $sessionId, $classId,
            $firstName, $lastName, $gender, $dateOfBirth,
            $email, $phone, $address,
            $parentName, $parentPhone, $parentEmail,
            $previousSchool
        ]);
        
        if ($stmt) {
            $success = true;
            
            // Send confirmation email if available
            if (function_exists('sendEmail') && !empty($parentEmail)) {
                $subject = "Admission Application Received - " . APP_NAME;
                $message = "Dear $parentName,\n\nYour admission application for $firstName $lastName has been received.\n\nApplication Number: $applicationNo\n\nWe will review your application and contact you soon.\n\nThank you!";
                sendEmail($parentEmail, $subject, $message);
            }
            
            // Store application number for display
            $submittedApplicationNo = $applicationNo;
        } else {
            $errors[] = 'Failed to submit application. Please try again.';
        }
    }
}

// Get branches and classes for form
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

$classesSql = "SELECT * FROM classes WHERE is_active = 1 ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Online Admission Application - <?php echo APP_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>template_extracted/assets/images/favicon.ico">
    
    <!-- Vendor css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    
    <!-- App css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    
    <!-- Icons css -->
    <link href="<?php echo APP_URL; ?>template_extracted/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        .application-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .application-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .application-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 12px 12px 0 0;
        }
        .application-body {
            padding: 30px;
        }
        .success-icon {
            font-size: 80px;
            color: #0acf97;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="application-container">
        <div class="application-card">
            <div class="application-header">
                <h2><i class="ri-file-user-line"></i> Online Admission Application</h2>
                <p class="mb-0"><?php echo APP_NAME; ?></p>
            </div>
            
            <div class="application-body">
                <?php if ($success): ?>
                    <!-- Success Message -->
                    <div class="text-center">
                        <div class="success-icon">
                            <i class="ri-checkbox-circle-line"></i>
                        </div>
                        <h3 class="mb-3">Application Submitted Successfully!</h3>
                        <div class="alert alert-success">
                            <h5>Your Application Number:</h5>
                            <h2 class="mb-0"><?php echo $submittedApplicationNo; ?></h2>
                        </div>
                        <p class="text-muted">
                            Thank you for applying to <?php echo APP_NAME; ?>. 
                            Your application has been received and will be reviewed shortly.
                            We will contact you via phone or email regarding the next steps.
                        </p>
                        <p class="text-muted">
                            <strong>Please save your application number for future reference.</strong>
                        </p>
                        <a href="apply-admission.php" class="btn btn-primary mt-3">
                            <i class="ri-file-add-line"></i> Submit Another Application
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Application Form -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Please correct the following errors:</strong>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <h5 class="mb-3">Branch & Class Selection</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Select Branch</label>
                                <select class="form-select" name="branch_id" required>
                                    <option value="">Choose Branch</option>
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
                                    <option value="">Choose Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>">
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <h5 class="mb-3">Student Information</h5>
                        
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
                        
                        <hr class="my-4">
                        <h5 class="mb-3">Parent/Guardian Information</h5>
                        
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
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="termsCheck" required>
                                <label class="form-check-label" for="termsCheck">
                                    I agree to the terms and conditions and certify that the information provided is accurate.
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="ri-send-plane-line"></i> Submit Application
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="<?php echo APP_URL; ?>login.php" class="text-muted">
                                <i class="ri-arrow-left-line me-1"></i>Back to Login
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <p class="text-white">
                <small>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</small>
            </p>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/vendor.min.js"></script>
    
    <!-- App js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/app.js"></script>
</body>
</html>

