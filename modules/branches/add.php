<?php
/**
 * Add Branch Page
 * 
 * Form to add new school branch/campus
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin']);

$pageTitle = 'Add New Branch';

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branchName = sanitize($_POST['branch_name'] ?? '');
    $branchCode = sanitize($_POST['branch_code'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $managerId = $_POST['manager_id'] ?? null;
    $establishedDate = $_POST['established_date'] ?? null;
    
    // Validation
    if (empty($branchName)) $errors[] = 'Branch name is required';
    if (empty($branchCode)) $errors[] = 'Branch code is required';
    
    // Check if branch code exists
    if (!empty($branchCode)) {
        $checkSql = "SELECT id FROM branches WHERE branch_code = ?";
        $stmt = executeQuery($checkSql, 's', [$branchCode]);
        if (fetchOne($stmt)) {
            $errors[] = 'Branch code already exists';
        }
    }
    
    if (empty($errors)) {
        // Handle logo upload
        $logoPath = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $uploadDir = ABSPATH . 'uploads/branches/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadResult = uploadFile($_FILES['logo'], $uploadDir, ['jpg', 'jpeg', 'png']);
            if ($uploadResult['success']) {
                $logoPath = 'uploads/branches/' . $uploadResult['filename'];
            }
        }
        
        // Insert branch
        $sql = "INSERT INTO branches (branch_name, branch_code, address, phone, email, manager_id, established_date, logo, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $stmt = executeQuery($sql, 'sssssiss', [
            $branchName, $branchCode, $address, $phone, $email, $managerId, $establishedDate, $logoPath
        ]);
        
        if ($stmt) {
            logActivity(
                getCurrentUser()['id'],
                'Add Branch',
                'Branches',
                "Created branch: $branchName ($branchCode)"
            );
            
            $_SESSION['success'] = 'Branch added successfully!';
            redirect(APP_URL . 'modules/branches/list.php');
        } else {
            $errors[] = 'Failed to add branch';
        }
    }
}

// Get potential managers (staff)
$managersSql = "SELECT s.id, s.first_name, s.last_name, s.staff_id 
                FROM staff s 
                WHERE s.status = 'Active' 
                AND s.designation IN ('Principal', 'Vice Principal', 'Manager')
                ORDER BY s.first_name";
$managers = fetchAll(executeQuery($managersSql));

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
                            <a href="<?php echo APP_URL; ?>modules/branches/list.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to List
                            </a>
                        </div>
                        <h4 class="page-title">Add New Branch</h4>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show">
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

            <!-- Branch Form -->
            <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Branch Information</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Branch Name</label>
                                        <input type="text" class="form-control" name="branch_name" 
                                               placeholder="e.g., Main Campus" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Branch Code</label>
                                        <input type="text" class="form-control" name="branch_code" 
                                               placeholder="e.g., MAIN" required>
                                        <small class="text-muted">Unique identifier for this branch</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="3" 
                                              placeholder="Complete address of the branch"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" name="phone" 
                                               placeholder="+252 XXX XXXXXXX">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" name="email" 
                                               placeholder="branch@school.com">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Branch Manager</label>
                                        <select class="form-select" name="manager_id">
                                            <option value="">Select Manager (Optional)</option>
                                            <?php foreach ($managers as $manager): ?>
                                                <option value="<?php echo $manager['id']; ?>">
                                                    <?php echo htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']); ?>
                                                    (<?php echo htmlspecialchars($manager['staff_id']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Assign a manager to this branch</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Established Date</label>
                                        <input type="date" class="form-control" name="established_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Branch Logo -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Branch Logo</h4>
                                
                                <div class="mb-3">
                                    <label class="form-label">Upload Logo</label>
                                    <input type="file" class="form-control" name="logo" accept="image/*" 
                                           onchange="previewImage(this, '#logoPreview')">
                                    <small class="text-muted">Max size: 2MB (JPG, PNG)</small>
                                </div>
                                
                                <div class="text-center">
                                    <img id="logoPreview" src="#" alt="Logo Preview" 
                                         class="img-fluid rounded" 
                                         style="max-height: 200px; display: none;">
                                </div>
                            </div>
                        </div>

                        <!-- Info Card -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Branch Information</h5>
                                <p class="text-muted mb-2">
                                    <i class="ri-information-line"></i> Each branch can have its own:
                                </p>
                                <ul class="text-muted mb-0">
                                    <li>Students and staff</li>
                                    <li>Classes and sections</li>
                                    <li>Financial records</li>
                                    <li>Separate statistics</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 btn-lg">
                                    <i class="ri-save-line"></i> Add Branch
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

