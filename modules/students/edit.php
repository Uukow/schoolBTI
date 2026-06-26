<?php
/**
 * Edit Student Page
 * 
 * Edit existing student information
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Receptionist']);

$pageTitle = 'Edit Student';

$studentId = $_GET['id'] ?? 0;

if (empty($studentId)) {
    $_SESSION['error'] = 'Invalid student ID';
    redirect(APP_URL . 'modules/students/list.php');
}

// Get student details
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$studentId]);
$student = fetchOne($stmt);

if (!$student) {
    $_SESSION['error'] = 'Student not found';
    redirect(APP_URL . 'modules/students/list.php');
}

// Get classes (excluding graduated classes)
$classesSql = "SELECT * FROM classes 
                WHERE is_active = 1 
                AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                ORDER BY class_order";
$classes = fetchAll(executeQuery($classesSql));

// Get branches
$branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
$branches = fetchAll(executeQuery($branchesSql));

// Get sections for current class
$sections = [];
if ($student['current_class_id']) {
    $sectionsSql = "SELECT * FROM sections WHERE class_id = ? AND is_active = 1";
    $sections = fetchAll(executeQuery($sectionsSql, 'i', [$student['current_class_id']]));
}

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
    $status = $_POST['status'] ?? 'Active';
    
    // Discount information
    $discountType = $_POST['discount_type'] ?? null;
    $discountValue = !empty($_POST['discount_value']) ? (float)$_POST['discount_value'] : null;
    
    // Handle photo upload
    $photoPath = $student['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $uploadResult = uploadFile($_FILES['photo'], STUDENT_PHOTO_PATH, ['jpg', 'jpeg', 'png']);
        if ($uploadResult['success']) {
            // Delete old photo
            if ($student['photo'] && file_exists(ABSPATH . $student['photo'])) {
                deleteFile(ABSPATH . $student['photo']);
            }
            $photoPath = 'uploads/students/photos/' . $uploadResult['filename'];
        }
    }
    
    // Update student
    $sql = "UPDATE students SET 
            branch_id = ?, first_name = ?, last_name = ?, middle_name = ?,
            gender = ?, date_of_birth = ?, blood_group = ?, religion = ?, nationality = ?,
            email = ?, phone = ?, address = ?, city = ?, state = ?, postal_code = ?,
            photo = ?, current_class_id = ?, current_section_id = ?, 
            discount_type = ?, discount_value = ?, status = ?
            WHERE id = ?";
    
    // Type string: i (branch_id), s (first_name), s (last_name), s (middle_name),
    //              s (gender), s (date_of_birth), s (blood_group), s (religion), s (nationality),
    //              s (email), s (phone), s (address), s (city), s (state), s (postal_code),
    //              s (photo), i (current_class_id), i (current_section_id),
    //              s (discount_type), d (discount_value), s (status), i (id)
    // Total: 22 parameters: 1i + 15s + 2i + 1s + 1d + 1s + 1i = isssssssssssssssiidsi
    // Breakdown: i(branch) + 15s(first_name through photo) + i(class) + i(section) + s(discount_type) + d(discount_value) + s(status) + i(id)
    // Type: i + 15s + i + i + s + d + s + i = 22 chars
    $stmt = executeQuery($sql, 'issssssssssssssiissdsi', [
        $branchId, $firstName, $lastName, $middleName,
        $gender, $dateOfBirth, $bloodGroup, $religion, $nationality,
        $email, $phone, $address, $city, $state, $postalCode,
        $photoPath, $classId, $sectionId, $discountType, $discountValue, $status, $studentId
    ]);
    
    if ($stmt) {
        logActivity(
            getCurrentUser()['id'],
            'Update Student',
            'Students',
            "Updated student: $firstName $lastName (ID: {$student['student_id']})"
        );
        
        $_SESSION['success'] = 'Student updated successfully!';
        redirect(APP_URL . 'modules/students/view.php?id=' . $studentId);
    } else {
        $errors[] = 'Failed to update student';
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
                            <a href="<?php echo APP_URL; ?>modules/students/view.php?id=<?php echo $studentId; ?>" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Profile
                            </a>
                        </div>
                        <h4 class="page-title">Edit Student</h4>
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
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">First Name</label>
                                        <input type="text" class="form-control" name="first_name" 
                                               value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" name="middle_name" 
                                               value="<?php echo htmlspecialchars($student['middle_name'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" 
                                               value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Gender</label>
                                        <select class="form-select" name="gender" required>
                                            <option value="Male" <?php echo ($student['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo ($student['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo ($student['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Date of Birth</label>
                                        <input type="date" class="form-control" name="date_of_birth" 
                                               value="<?php echo $student['date_of_birth']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Blood Group</label>
                                        <select class="form-select" name="blood_group">
                                            <option value="">Select</option>
                                            <?php foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg): ?>
                                                <option value="<?php echo $bg; ?>" <?php echo ($student['blood_group'] == $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Religion</label>
                                        <input type="text" class="form-control" name="religion" 
                                               value="<?php echo htmlspecialchars($student['religion'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nationality</label>
                                        <input type="text" class="form-control" name="nationality" 
                                               value="<?php echo htmlspecialchars($student['nationality'] ?? ''); ?>">
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
                                               value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" class="form-control" name="phone" 
                                               value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">City</label>
                                        <input type="text" class="form-control" name="city" 
                                               value="<?php echo htmlspecialchars($student['city'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">State/Region</label>
                                        <input type="text" class="form-control" name="state" 
                                               value="<?php echo htmlspecialchars($student['state'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" name="postal_code" 
                                               value="<?php echo htmlspecialchars($student['postal_code'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Photo -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Student Photo</h4>
                                
                                <div class="text-center mb-3">
                                    <?php if (!empty($student['photo'])): ?>
                                        <img id="photoPreview" src="<?php echo APP_URL . $student['photo']; ?>" 
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

                        <!-- Academic Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Academic Information</h4>
                                
                                <div class="mb-3">
                                    <label class="form-label">Student ID</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label required">Branch</label>
                                    <select class="form-select" name="branch_id" required>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" 
                                                    <?php echo ($student['branch_id'] == $branch['id']) ? 'selected' : ''; ?>>
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
                                            <option value="<?php echo $class['id']; ?>" 
                                                    <?php echo ($student['current_class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Section</label>
                                    <select class="form-select" name="section_id" id="sectionSelect">
                                        <option value="">Select Section</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?php echo $section['id']; ?>" 
                                                    <?php echo ($student['current_section_id'] == $section['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($section['section_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="Active" <?php echo ($student['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo ($student['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="Graduated" <?php echo ($student['status'] == 'Graduated') ? 'selected' : ''; ?>>Graduated</option>
                                        <option value="Transferred" <?php echo ($student['status'] == 'Transferred') ? 'selected' : ''; ?>>Transferred</option>
                                        <option value="Suspended" <?php echo ($student['status'] == 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
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
                                            <option value="Fixed" <?php echo ($student['discount_type'] ?? '') == 'Fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                                            <option value="Percentage" <?php echo ($student['discount_type'] ?? '') == 'Percentage' ? 'selected' : ''; ?>>Percentage</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Discount Value</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="discount_value" 
                                                   id="discountValueInput" step="0.01" min="0" 
                                                   value="<?php echo htmlspecialchars($student['discount_value'] ?? ''); ?>"
                                                   placeholder="Enter discount amount">
                                            <span class="input-group-text" id="discountUnit">
                                                <?php echo ($student['discount_type'] ?? '') == 'Percentage' ? '%' : '$'; ?>
                                            </span>
                                        </div>
                                        <small class="text-muted" id="discountHelp">
                                            <?php 
                                            if (($student['discount_type'] ?? '') == 'Percentage') {
                                                echo 'Enter the percentage discount (e.g., 10 for 10% discount)';
                                            } elseif (($student['discount_type'] ?? '') == 'Fixed') {
                                                echo 'Enter the fixed discount amount (e.g., 20 for $20 discount)';
                                            } else {
                                                echo 'Enter the discount amount';
                                            }
                                            ?>
                                        </small>
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
                                <button type="submit" class="btn btn-warning w-100 btn-lg">
                                    <i class="ri-save-line"></i> Update Student
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
        discountValueInput.prop('disabled', false);
        discountUnit.text('$');
        discountHelp.text('Enter the fixed discount amount (e.g., 20 for $20 discount)');
    } else if (discountType === 'Percentage') {
        discountValueInput.prop('disabled', false);
        discountUnit.text('%');
        discountHelp.text('Enter the percentage discount (e.g., 10 for 10% discount)');
    }
});

// Initialize discount field state
$(document).ready(function() {
    const discountType = $('#discountTypeSelect').val();
    if (!discountType) {
        $('#discountValueInput').prop('disabled', true);
    }
});

// Load sections when class changes
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
            }
        });
    } else {
        sectionSelect.html('<option value="">Select Section</option>');
    }
});
</script>
