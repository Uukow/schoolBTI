<?php
/**
 * Add Student Page
 * 
 * Form to add new student
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Receptionist']);

$pageTitle = 'Add New Student';

// Get classes for dropdown (excluding graduated classes)
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get branches for dropdown
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branchId = $_POST['branch_id'] ?? '';
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $middleName = sanitize($_POST['middle_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $bloodGroup = sanitize($_POST['blood_group'] ?? '');
    $religion = sanitize($_POST['religion'] ?? '');
    $nationality = sanitize($_POST['nationality'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $postalCode = sanitize($_POST['postal_code'] ?? '');
    $classId = $_POST['class_id'] ?? null;
    $sectionId = $_POST['section_id'] ?? null;
    $admissionDate = $_POST['admission_date'] ?? date('Y-m-d');
    
    // Discount information
    $discountType = $_POST['discount_type'] ?? null;
    $discountValue = !empty($_POST['discount_value']) ? (float)$_POST['discount_value'] : null;
    
    // Parent information
    $parentFirstName = sanitize($_POST['parent_first_name'] ?? '');
    $parentLastName = sanitize($_POST['parent_last_name'] ?? '');
    $parentEmail = sanitize($_POST['parent_email'] ?? '');
    $parentPhone = sanitize($_POST['parent_phone'] ?? '');
    $relationship = $_POST['relationship'] ?? 'Parent';
    
    // Validation
    $errors = [];
    
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($gender)) $errors[] = 'Gender is required';
    if (empty($dateOfBirth)) $errors[] = 'Date of birth is required';
    if (empty($branchId)) $errors[] = 'Branch is required';
    if (empty($parentPhone)) $errors[] = 'Parent phone is required';
    
    if (empty($errors)) {
        beginTransaction();
        
        try {
            // Generate student ID
            $sql = "SELECT MAX(id) as max_id FROM students";
            $result = executeQuery($sql);
            $row = fetchOne($result);
            $nextId = ($row['max_id'] ?? 0) + 1;
            $studentId = generateUniqueId(STUDENT_ID_PREFIX, $nextId, 6);
            
            // Generate admission number
            $admissionNo = 'ADM' . date('Y') . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            
            // Handle photo upload
            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $uploadResult = uploadFile($_FILES['photo'], STUDENT_PHOTO_PATH, ['jpg', 'jpeg', 'png']);
                if ($uploadResult['success']) {
                    $photoPath = 'uploads/students/photos/' . $uploadResult['filename'];
                }
            }
            
            // Insert student
            $sql = "INSERT INTO students (
                student_id, admission_no, branch_id, first_name, last_name, middle_name,
                gender, date_of_birth, blood_group, religion, nationality,
                email, phone, address, city, state, postal_code,
                photo, admission_date, current_class_id, current_section_id, 
                discount_type, discount_value, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')";
            
            $stmt = executeQuery($sql, 'ssisssssssssssssssiissd', [
                $studentId, $admissionNo, $branchId, $firstName, $lastName, $middleName,
                $gender, $dateOfBirth, $bloodGroup, $religion, $nationality,
                $email, $phone, $address, $city, $state, $postalCode,
                $photoPath, $admissionDate, $classId, $sectionId,
                $discountType, $discountValue
            ]);
            
            $newStudentId = getLastInsertId();
            
            // Create parent if details provided
            if (!empty($parentFirstName) && !empty($parentPhone)) {
                $parentSql = "INSERT INTO parents (first_name, last_name, email, phone, address)
                             VALUES (?, ?, ?, ?, ?)";
                $parentStmt = executeQuery($parentSql, 'sssss', [
                    $parentFirstName, $parentLastName, $parentEmail, $parentPhone, $address
                ]);
                
                $parentId = getLastInsertId();
                
                // Link student to parent
                $linkSql = "INSERT INTO student_parents (student_id, parent_id, relationship, is_primary)
                           VALUES (?, ?, ?, 1)";
                executeQuery($linkSql, 'iis', [$newStudentId, $parentId, $relationship]);
            }
            
            // Log activity
            logActivity(
                getCurrentUser()['id'],
                'Add Student',
                'Students',
                "Added student: $firstName $lastName (ID: $studentId)"
            );
            
            commitTransaction();
            
            $_SESSION['success'] = 'Student added successfully!';
            redirect(APP_URL . 'modules/students/view.php?id=' . $newStudentId);
            
        } catch (Exception $e) {
            rollbackTransaction();
            $errors[] = 'Failed to add student: ' . $e->getMessage();
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
                            <a href="<?php echo APP_URL; ?>modules/students/list.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to List
                            </a>
                        </div>
                        <h4 class="page-title">Add New Student</h4>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line me-2"></i>
                        <strong>Error!</strong>
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

            <!-- Student Form -->
            <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Basic Information</h4>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">First Name</label>
                                        <input type="text" class="form-control" name="first_name" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" name="middle_name">
                                    </div>
                                    <div class="col-md-4 mb-3">
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
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Date of Birth</label>
                                        <input type="date" class="form-control" name="date_of_birth" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Blood Group</label>
                                        <select class="form-select" name="blood_group">
                                            <option value="">Select</option>
                                            <option value="A+">A+</option>
                                            <option value="A-">A-</option>
                                            <option value="B+">B+</option>
                                            <option value="B-">B-</option>
                                            <option value="O+">O+</option>
                                            <option value="O-">O-</option>
                                            <option value="AB+">AB+</option>
                                            <option value="AB-">AB-</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Religion</label>
                                        <input type="text" class="form-control" name="religion">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nationality</label>
                                        <input type="text" class="form-control" name="nationality" value="Somali">
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
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">City</label>
                                        <input type="text" class="form-control" name="city">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">State/Region</label>
                                        <input type="text" class="form-control" name="state">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" name="postal_code">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Parent/Guardian Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Parent/Guardian Information</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Parent First Name</label>
                                        <input type="text" class="form-control" name="parent_first_name">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Parent Last Name</label>
                                        <input type="text" class="form-control" name="parent_last_name">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Parent Phone</label>
                                        <input type="text" class="form-control" name="parent_phone" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Parent Email</label>
                                        <input type="email" class="form-control" name="parent_email">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Relationship</label>
                                        <select class="form-select" name="relationship">
                                            <option value="Father">Father</option>
                                            <option value="Mother">Mother</option>
                                            <option value="Guardian">Guardian</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Photo Upload -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Student Photo</h4>
                                
                                <div class="mb-3">
                                    <label class="form-label">Upload Photo</label>
                                    <input type="file" class="form-control" name="photo" accept="image/*" onchange="previewImage(this, '#photoPreview')">
                                    <small class="text-muted">Max size: 2MB (JPG, PNG)</small>
                                </div>
                                
                                <div class="text-center">
                                    <img id="photoPreview" src="<?php echo APP_URL; ?>template_extracted/assets/images/users/avatar-1.jpg" 
                                         alt="Preview" class="img-fluid rounded" style="max-height: 200px; display: none;">
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Academic Information</h4>
                                
                                <div class="mb-3">
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
                                
                                <div class="mb-3">
                                    <label class="form-label">Class</label>
                                    <select class="form-select" name="class_id" id="classSelect">
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>">
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Section</label>
                                    <select class="form-select" name="section_id" id="sectionSelect">
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Admission Date</label>
                                    <input type="date" class="form-control" name="admission_date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Fee Discount Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Fee Discount (Optional)</h4>
                                <p class="text-muted small mb-3">Set a discount that will be automatically applied to tuition fees for this student.</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Discount Type</label>
                                        <select class="form-select" name="discount_type" id="discountTypeSelect">
                                            <option value="">No Discount</option>
                                            <option value="Fixed">Fixed Amount</option>
                                            <option value="Percentage">Percentage</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Discount Value</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="discount_value" 
                                                   id="discountValueInput" step="0.01" min="0" 
                                                   placeholder="Enter discount amount">
                                            <span class="input-group-text" id="discountUnit">$</span>
                                        </div>
                                        <small class="text-muted" id="discountHelp">Enter the discount amount</small>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> 
                                    <strong>Note:</strong> Discounts are applied automatically during fee assignment and are recorded in the system for audit purposes, but are not displayed to students.
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-save-line"></i> Add Student
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Handle discount type change
$('#discountTypeSelect').change(function() {
    const discountType = $(this).val();
    const discountValueInput = $('#discountValueInput');
    const discountUnit = $('#discountUnit');
    const discountHelp = $('#discountHelp');
    
    if (discountType === '') {
        discountValueInput.prop('disabled', true).val('');
        discountUnit.text('$');
        discountHelp.text('Enter the discount amount');
    } else if (discountType === 'Fixed') {
        discountValueInput.prop('disabled', false).val('');
        discountUnit.text('$');
        discountHelp.text('Enter the fixed discount amount (e.g., 20 for $20 discount)');
    } else if (discountType === 'Percentage') {
        discountValueInput.prop('disabled', false).val('');
        discountUnit.text('%');
        discountHelp.text('Enter the percentage discount (e.g., 10 for 10% discount)');
    }
});

// Load sections when class is selected
$('#classSelect').change(function() {
    const classId = $(this).val();
    const sectionSelect = $('#sectionSelect');
    
    sectionSelect.html('<option value="">Loading...</option>');
    
    if (classId) {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/get-sections.php',
            type: 'GET',
            data: { class_id: classId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Select Section</option>';
                    response.data.forEach(function(section) {
                        options += `<option value="${section.id}">${section.section_name}</option>`;
                    });
                    sectionSelect.html(options);
                } else {
                    sectionSelect.html('<option value="">No sections found</option>');
                }
            },
            error: function() {
                sectionSelect.html('<option value="">Error loading sections</option>');
            }
        });
    } else {
        sectionSelect.html('<option value="">Select Section</option>');
    }
});
</script>

