<?php
/**
 * Library Books Management
 * 
 * Manage library book catalog
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();

$pageTitle = 'Library Books';

// Get current user
$currentUser = getCurrentUser();

// Get filter parameters
$categoryFilter = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Build query
$sql = "SELECT b.*, br.branch_name
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
    $sql .= " AND (b.book_title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
    $search = "%$searchQuery%";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= 'sss';
}

// Branch filter
if (!hasRole(['Super Admin'])) {
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
    SUM(quantity - available_quantity) as issued_copies
    FROM library_books WHERE 1=1";

if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
    $statsSql .= " AND branch_id = " . $currentUser['branch_id'];
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
                        <div class="page-title-right">
                            <?php if (hasRole(['Super Admin', 'Admin', 'Librarian'])): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                                <i class="ri-book-add-line"></i> Add Book
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Library Books</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-book-2-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Books</h5>
                                    <h2 class="mb-0"><?php echo $stats['total_books'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-info-lighten text-info">
                                        <i class="ri-stack-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Copies</h5>
                                    <h2 class="mb-0"><?php echo $stats['total_copies'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-success-lighten text-success">
                                        <i class="ri-checkbox-circle-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Available</h5>
                                    <h2 class="mb-0"><?php echo $stats['available_copies'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-warning-lighten text-warning">
                                        <i class="ri-book-read-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Issued</h5>
                                    <h2 class="mb-0"><?php echo $stats['issued_copies'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
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
                                <div class="col-md-5">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo htmlspecialchars($searchQuery); ?>" 
                                           placeholder="Search by title, author, or ISBN">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-search-line"></i> Search
                                    </button>
                                    <a href="books.php" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
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
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable-export">
                                    <thead>
                                        <tr>
                                            <th>Book Code</th>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Category</th>
                                            <th>ISBN</th>
                                            <th>Total</th>
                                            <th>Available</th>
                                            <th>Issued</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($book['book_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($book['book_title']); ?></td>
                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($book['category'] ?? 'Uncategorized'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></td>
                                            <td><?php echo $book['quantity']; ?></td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $book['available_quantity']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning"><?php echo $book['quantity'] - $book['available_quantity']; ?></span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if ($book['available_quantity'] > 0 && hasRole(['Super Admin', 'Admin', 'Librarian'])): ?>
                                                    <button onclick="issueBook(<?php echo $book['id']; ?>, '<?php echo htmlspecialchars($book['book_title']); ?>')" 
                                                            class="btn btn-sm btn-success" title="Issue Book">
                                                        <i class="ri-arrow-right-circle-line"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if (hasRole(['Super Admin', 'Admin', 'Librarian'])): ?>
                                                    <button onclick="editBook(<?php echo $book['id']; ?>)" 
                                                            class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button onclick="deleteBook(<?php echo $book['id']; ?>)" 
                                                            class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addBookForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Book Title</label>
                            <input type="text" class="form-control" name="book_title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Book Code</label>
                            <input type="text" class="form-control" name="book_code" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Author</label>
                            <input type="text" class="form-control" name="author" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" class="form-control" name="isbn">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Publisher</label>
                            <input type="text" class="form-control" name="publisher">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Publication Year</label>
                            <input type="number" class="form-control" name="publication_year" min="1900" max="2099">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" name="category" list="categoryList">
                            <datalist id="categoryList">
                                <option value="Fiction">
                                <option value="Non-Fiction">
                                <option value="Science">
                                <option value="Mathematics">
                                <option value="History">
                                <option value="Literature">
                                <option value="Reference">
                            </datalist>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Quantity</label>
                            <input type="number" class="form-control" name="quantity" value="1" min="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" step="0.01">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" placeholder="Shelf/Rack">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Add Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Issue Book Modal -->
<div class="modal fade" id="issueBookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="issueBookTitle">Issue Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="issueBookForm">
                <input type="hidden" name="book_id" id="issueBookId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Student</label>
                        <select class="form-select" name="student_id" id="studentSelect" required>
                            <option value="">Select Student</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Issue Date</label>
                        <input type="date" class="form-control" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Due Date</label>
                        <input type="date" class="form-control" name="due_date" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-arrow-right-circle-line"></i> Issue Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Add book
$('#addBookForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/library/add-book.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#addBookModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Issue book
function issueBook(bookId, bookTitle) {
    $('#issueBookId').val(bookId);
    $('#issueBookTitle').text('Issue: ' + bookTitle);
    
    // Load students
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/fees/get-students.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Student</option>';
                response.data.forEach(function(student) {
                    options += `<option value="${student.id}">${student.student_id} - ${student.first_name} ${student.last_name}</option>`;
                });
                $('#studentSelect').html(options);
            }
        }
    });
    
    $('#issueBookModal').modal('show');
}

$('#issueBookForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/library/issue-book.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#issueBookModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        }
    });
});

// Delete book
function deleteBook(bookId) {
    confirmAction('Are you sure you want to delete this book?', function() {
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/library/delete-book.php',
            type: 'POST',
            data: { id: bookId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(response.message, 'error');
                }
            }
        });
    });
}
</script>

