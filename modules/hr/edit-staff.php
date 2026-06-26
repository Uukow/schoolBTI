<?php
/**
 * Edit Staff Page
 * 
 * Edit existing staff information
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

hrRequirePage('hr_payroll', 'update');

$pageTitle = 'Edit Staff';

$staffId = $_GET['id'] ?? 0;

if (empty($staffId)) {
    $_SESSION['error'] = 'Invalid staff ID';
    redirect(APP_URL . 'modules/hr/staff.php');
}

// Get current user
$currentUser = getCurrentUser();

// Get staff details
$sql = "SELECT * FROM staff WHERE id = ?";
$params = [$staffId];
$types = 'i';

// Branch filter for non-super admins
if (!hasRole(['Super Admin'])) {
    $sql .= " AND branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$stmt = executeQuery($sql, $types, $params);
$staff = fetchOne($stmt);

if (!$staff) {
    $_SESSION['error'] = 'Staff not found or access denied';
    redirect(APP_URL . 'modules/hr/staff.php');
}

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Get current payroll structure (latest)
$salarySql = "SELECT * FROM payroll_structures WHERE staff_id = ? ORDER BY effective_from DESC LIMIT 1";
$salaryStmt = executeQuery($salarySql, 'i', [$staffId]);
$currentSalary = fetchOne($salaryStmt);

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branchId = $_POST['branch_id'] ?? '';
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $postalCode = sanitize($_POST['postal_code'] ?? '');
    $designation = sanitize($_POST['designation'] ?? '');
    $department = sanitize($_POST['department'] ?? '');
    $qualification = sanitize($_POST['qualification'] ?? '');
    $experienceYears = $_POST['experience_years'] ?? null;
    $joiningDate = $_POST['joining_date'] ?? '';
    $leavingDate = $_POST['leaving_date'] ?? null;
    $employmentType = $_POST['employment_type'] ?? 'Full Time';
    $status = $_POST['status'] ?? 'Active';
    $bankAccountNo = sanitize($_POST['bank_account_no'] ?? '');
    $bankName = sanitize($_POST['bank_name'] ?? '');
    $emergencyContact = sanitize($_POST['emergency_contact'] ?? '');
    $emergencyPhone = sanitize($_POST['emergency_phone'] ?? '');
    $basicSalary = (float)($_POST['basic_salary'] ?? 0);
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($gender) || empty($dateOfBirth) || empty($phone) || empty($designation) || empty($branchId) || empty($joiningDate)) {
        $errors[] = 'All required fields must be filled';
    }
    
    if (!empty($email) && !validateEmail($email)) {
        $errors[] = 'Invalid email address';
    }
    
    // Handle photo upload
    $photoPath = $staff['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $uploadResult = uploadFile($_FILES['photo'], STAFF_PHOTO_PATH, ['jpg', 'jpeg', 'png']);
        if ($uploadResult['success']) {
            // Delete old photo
            if ($staff['photo'] && file_exists(ABSPATH . $staff['photo'])) {
                deleteFile(ABSPATH . $staff['photo']);
            }
            $photoPath = 'uploads/staff/photos/' . $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['message'] ?? 'Failed to upload photo';
        }
    }
    
    if (empty($errors)) {
        // Update staff
        $sql = "UPDATE staff SET 
                branch_id = ?, first_name = ?, last_name = ?, gender = ?, 
                date_of_birth = ?, email = ?, phone = ?, address = ?, 
                city = ?, state = ?, postal_code = ?, photo = ?, 
                designation = ?, department = ?, qualification = ?, 
                experience_years = ?, joining_date = ?, leaving_date = ?, 
                employment_type = ?, status = ?, bank_account_no = ?, 
                bank_name = ?, emergency_contact = ?, emergency_phone = ?
                WHERE id = ?";
        
        $stmt = executeQuery($sql, 'isssssssssssssssssssssssi', [
            $branchId, $firstName, $lastName, $gender, $dateOfBirth, 
            $email, $phone, $address, $city, $state, $postalCode, $photoPath,
            $designation, $department, $qualification, $experienceYears, 
            $joiningDate, $leavingDate, $employmentType, $status, 
            $bankAccountNo, $bankName, $emergencyContact, $emergencyPhone, $staffId
        ]);
        
        if ($stmt) {
            // Update or create payroll structure if salary is provided
            if ($basicSalary > 0) {
                if ($currentSalary) {
                    // Check if salary changed
                    if ((float)$currentSalary['basic_salary'] != $basicSalary) {
                        // Create new payroll structure entry with new effective date
                        $structureSql = "INSERT INTO payroll_structures (staff_id, basic_salary, effective_from)
                                        VALUES (?, ?, ?)";
                        executeQuery($structureSql, 'ids', [$staffId, $basicSalary, date('Y-m-d')]);
                    } else {
                        // Update existing structure if no change in effective date needed
                        $updateSql = "UPDATE payroll_structures SET basic_salary = ? WHERE id = ?";
                        executeQuery($updateSql, 'di', [$basicSalary, $currentSalary['id']]);
                    }
                } else {
                    // Create new payroll structure
                    $structureSql = "INSERT INTO payroll_structures (staff_id, basic_salary, effective_from)
                                    VALUES (?, ?, ?)";
                    executeQuery($structureSql, 'ids', [$staffId, $basicSalary, $joiningDate]);
                }
            }
            
            logActivity(
                getCurrentUser()['id'],
                'Update Staff',
                'HR',
                "Updated staff: $firstName $lastName (ID: {$staff['staff_id']})"
            );
            
            $_SESSION['success'] = 'Staff updated successfully!';
            redirect(APP_URL . 'modules/hr/view-staff.php?id=' . $staffId);
        } else {
            $errors[] = 'Failed to update staff';
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
                            <a href="<?php echo APP_URL; ?>modules/hr/view-staff.php?id=<?php echo $staffId; ?>" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Profile
                            </a>
                        </div>
                        <h4 class="page-title">Edit Staff</h4>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show">
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

            <!-- Edit Form -->
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Basic Information</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">First Name</label>
                                        <input type="text" class="form-control" name="first_name" 
                                               value="<?php echo htmlspecialchars($staff['first_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" 
                                               value="<?php echo htmlspecialchars($staff['last_name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Gender</label>
                                        <select class="form-select" name="gender" required>
                                            <option value="Male" <?php echo ($staff['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo ($staff['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo ($staff['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Date of Birth</label>
                                        <input type="date" class="form-control" name="date_of_birth" 
                                               value="<?php echo $staff['date_of_birth']; ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Employment Information</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Designation</label>
                                        <input type="text" class="form-control" name="designation" 
                                               value="<?php echo htmlspecialchars($staff['designation']); ?>" 
                                               list="designationList" required>
                                        <datalist id="designationList">
                                            <option value="Principal">
                                            <option value="Vice Principal">
                                            <option value="Senior Teacher">
                                            <option value="Teacher">
                                            <option value="Assistant Teacher">
                                            <option value="Accountant">
                                            <option value="Librarian">
                                            <option value="Receptionist">
                                        </datalist>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Department</label>
                                        <input type="text" class="form-control" name="department" 
                                               value="<?php echo htmlspecialchars($staff['department'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Qualification</label>
                                        <input type="text" class="form-control" name="qualification" 
                                               value="<?php echo htmlspecialchars($staff['qualification'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Experience (Years)</label>
                                        <input type="number" class="form-control" name="experience_years" 
                                               value="<?php echo $staff['experience_years'] ?? ''; ?>" min="0">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Basic Salary</label>
                                        <input type="number" class="form-control" name="basic_salary" 
                                               value="<?php echo $currentSalary ? number_format($currentSalary['basic_salary'], 2, '.', '') : ''; ?>" 
                                               step="0.01" min="0" placeholder="Enter basic salary">
                                        <small class="text-muted">Current salary: <?php echo $currentSalary ? formatCurrency($currentSalary['basic_salary']) : 'Not set'; ?></small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Joining Date</label>
                                        <input type="date" class="form-control" name="joining_date" 
                                               value="<?php echo $staff['joining_date']; ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Leaving Date</label>
                                        <input type="date" class="form-control" name="leaving_date" 
                                               value="<?php echo $staff['leaving_date'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Employment Type</label>
                                        <select class="form-select" name="employment_type">
                                            <option value="Full Time" <?php echo ($staff['employment_type'] == 'Full Time') ? 'selected' : ''; ?>>Full Time</option>
                                            <option value="Part Time" <?php echo ($staff['employment_type'] == 'Part Time') ? 'selected' : ''; ?>>Part Time</option>
                                            <option value="Contract" <?php echo ($staff['employment_type'] == 'Contract') ? 'selected' : ''; ?>>Contract</option>
                                            <option value="Temporary" <?php echo ($staff['employment_type'] == 'Temporary') ? 'selected' : ''; ?>>Temporary</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Contact Information</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($staff['email'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Phone</label>
                                        <input type="text" class="form-control" name="phone" 
                                               value="<?php echo htmlspecialchars($staff['phone']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($staff['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">City</label>
                                        <input type="text" class="form-control" name="city" 
                                               value="<?php echo htmlspecialchars($staff['city'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">State/Region</label>
                                        <input type="text" class="form-control" name="state" 
                                               value="<?php echo htmlspecialchars($staff['state'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" name="postal_code" 
                                               value="<?php echo htmlspecialchars($staff['postal_code'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Emergency Contact</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Name</label>
                                        <input type="text" class="form-control" name="emergency_contact" 
                                               value="<?php echo htmlspecialchars($staff['emergency_contact'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="text" class="form-control" name="emergency_phone" 
                                               value="<?php echo htmlspecialchars($staff['emergency_phone'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Bank Information</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" class="form-control" name="bank_name" 
                                               value="<?php echo htmlspecialchars($staff['bank_name'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" class="form-control" name="bank_account_no" 
                                               value="<?php echo htmlspecialchars($staff['bank_account_no'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Photo -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Staff Photo</h4>
                                
                                <div class="text-center mb-3">
                                    <?php if (!empty($staff['photo'])): ?>
                                        <img id="photoPreview" src="<?php echo APP_URL . $staff['photo']; ?>" 
                                             alt="Current Photo" class="img-fluid rounded" style="max-height: 200px;">
                                    <?php else: ?>
                                        <img id="photoPreview" src="<?php echo APP_URL; ?>template_extracted/assets/images/users/avatar-1.jpg" 
                                             alt="Preview" class="img-fluid rounded" style="max-height: 200px; display: none;">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Change Photo</label>
                                    <input type="file" class="form-control" name="photo" accept="image/*" 
                                           onchange="previewImage(this, '#photoPreview')">
                                    <small class="text-muted">Max size: 2MB (JPG, PNG)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Staff Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Staff Information</h4>
                                
                                <div class="mb-3">
                                    <label class="form-label">Staff ID</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($staff['staff_id']); ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label required">Branch</label>
                                    <select class="form-select" name="branch_id" required>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" 
                                                    <?php echo ($staff['branch_id'] == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="Active" <?php echo ($staff['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo ($staff['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="Resigned" <?php echo ($staff['status'] == 'Resigned') ? 'selected' : ''; ?>>Resigned</option>
                                        <option value="Terminated" <?php echo ($staff['status'] == 'Terminated') ? 'selected' : ''; ?>>Terminated</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-warning w-100 btn-lg">
                                    <i class="ri-save-line"></i> Update Staff
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>


