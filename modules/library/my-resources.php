<?php
/**
 * My Library Resources - Teacher Portal
 * 
 * Browse library catalog and available resources
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Teacher', 'Super Admin'], APP_URL . 'modules/teacher/dashboard.php');

$pageTitle = 'Library Resources';

// Get current user
$currentUser = getCurrentUser();
$isTeacher = hasRole(['Teacher']);

// Get filter parameters
$categoryFilter = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$availableOnly = $_GET['available'] ?? '1';

// Build query
$sql = "SELECT b.*, br.branch_name,
        (b.quantity - b.available_quantity) as issued_count
        FROM library_books b
        LEFT JOIN branches br ON b.branch_id = br.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($categoryFilter)) {
    $sql .= " AND b.category = ?";
    $params[] = $categoryFilter;
    $types .= 's';
}

if (!empty($searchQuery)) {
    $sql .= " AND (b.book_title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.publisher LIKE ?)";
    $search = "%$searchQuery%";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= 'ssss';
}

if ($availableOnly == '1') {
    $sql .= " AND b.available_quantity > 0";
}

// Branch filter
if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
    $sql .= " AND b.branch_id = ?";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

$sql .= " ORDER BY b.book_title";

$stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
$books = fetchAll($stmt);

// Get unique categories
$categorySql = "SELECT DISTINCT category FROM library_books WHERE category IS NOT NULL ORDER BY category";
$categories = fetchAll(executeQuery($categorySql));

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total_books,
    SUM(quantity) as total_copies,
    SUM(available_quantity) as available_copies,
    SUM(quantity - available_quantity) as issued_copies,
    COUNT(DISTINCT category) as total_categories
    FROM library_books WHERE 1=1";

if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
    $statsSql .= " AND branch_id = " . intval($currentUser['branch_id']);
}

$statsResult = executeQuery($statsSql);
$stats = fetchOne($statsResult);

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
                        <h4 class="page-title">Library Resources</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>modules/teacher/dashboard.php">Teacher Portal</a></li>
                                <li class="breadcrumb-item active">Library Resources</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-book-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="Total Books">Total Books</h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['total_books'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-info me-2"><?php echo $stats['total_categories'] ?? 0; ?> Categories</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-stack-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="Total Copies">Total Copies</h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['total_copies'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2">All Books</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-checkbox-circle-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="Available Copies">Available</h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['available_copies'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2">Ready to Issue</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="ri-book-open-line widget-icon"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0" title="Issued Books">Issued</h5>
                            <h3 class="mt-3 mb-3"><?php echo $stats['issued_copies'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-warning me-2">Currently Issued</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Search by title, author, ISBN, publisher..." 
                                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                                    <?php echo ($categoryFilter == $cat['category']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['category']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Availability</label>
                                    <select name="available" class="form-select">
                                        <option value="">All Books</option>
                                        <option value="1" <?php echo ($availableOnly == '1') ? 'selected' : ''; ?>>Available Only</option>
                                        <option value="0" <?php echo ($availableOnly == '0') ? 'selected' : ''; ?>>All (Inc. Issued)</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-search-line"></i> Search
                                    </button>
                                    <a href="my-resources.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Books Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Library Catalog (<?php echo count($books); ?> Books)</h4>
                            
                            <?php if (empty($books)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No books found matching your search criteria.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="books-table">
                                        <thead>
                                            <tr>
                                                <th>Book Code</th>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th>Publisher</th>
                                                <th>Category</th>
                                                <th>ISBN</th>
                                                <th>Year</th>
                                                <th>Total Copies</th>
                                                <th>Available</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($books as $book): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($book['book_code']); ?></strong></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($book['book_title']); ?></strong>
                                                        <?php if (!empty($book['location'])): ?>
                                                            <br><small class="text-muted">
                                                                <i class="ri-map-pin-line"></i> <?php echo htmlspecialchars($book['location']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                                    <td><?php echo htmlspecialchars($book['publisher'] ?? '-'); ?></td>
                                                    <td>
                                                        <?php if (!empty($book['category'])): ?>
                                                            <span class="badge bg-info"><?php echo htmlspecialchars($book['category']); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($book['isbn'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($book['publication_year'] ?? '-'); ?></td>
                                                    <td class="text-center">
                                                        <span class="badge bg-secondary"><?php echo $book['quantity']; ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($book['available_quantity'] > 0): ?>
                                                            <span class="badge bg-success"><?php echo $book['available_quantity']; ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">0</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($book['available_quantity'] > 0): ?>
                                                            <span class="badge bg-success">
                                                                <i class="ri-checkbox-circle-fill"></i> Available
                                                            </span>
                                                        <?php elseif ($book['available_quantity'] == 0 && $book['quantity'] > 0): ?>
                                                            <span class="badge bg-warning">
                                                                <i class="ri-time-line"></i> All Issued
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="ri-close-circle-line"></i> Not Available
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</div>

<script>
$(document).ready(function() {
    $('#books-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'asc']],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            {
                extend: 'excel',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            {
                extend: 'pdf',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            'print'
        ]
    });
});
</script>





