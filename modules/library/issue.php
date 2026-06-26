<?php
/**
 * Issue Books
 * 
 * Issue library books to students
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Librarian']);

$pageTitle = 'Issue Books';

// Get available books
$booksSql = "SELECT * FROM library_books WHERE available_quantity > 0 ORDER BY book_title";
$books = fetchAll(executeQuery($booksSql));

// Get active students
$studentsSql = "SELECT s.*, c.class_name FROM students s 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                WHERE s.status = 'Active' 
                ORDER BY s.first_name, s.last_name";
$students = fetchAll(executeQuery($studentsSql));

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
                        <h4 class="page-title">Issue Library Books</h4>
                    </div>
                </div>
            </div>

            <!-- Issue Form -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Issue Book to Student</h4>
                            
                            <form id="issueBookForm">
                                <div class="mb-3">
                                    <label class="form-label required">Select Student</label>
                                    <select class="form-select" name="student_id" id="studentSelect" required>
                                        <option value="">Choose Student</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>">
                                                <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['class_name'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label required">Select Book</label>
                                    <select class="form-select" name="book_id" id="bookSelect" required>
                                        <option value="">Choose Book</option>
                                        <?php foreach ($books as $book): ?>
                                            <option value="<?php echo $book['id']; ?>" data-available="<?php echo $book['available_quantity']; ?>">
                                                <?php echo htmlspecialchars($book['book_title'] . ' by ' . $book['author'] . ' (Available: ' . $book['available_quantity'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Issue Date</label>
                                        <input type="date" class="form-control" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Due Date</label>
                                        <input type="date" class="form-control" name="due_date" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="3" placeholder="Additional notes..."></textarea>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="ri-book-open-line"></i> Issue Book
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Quick Info</h5>
                            <p class="text-muted">
                                <i class="ri-information-line"></i> Books are issued for 14 days by default.
                            </p>
                            <p class="text-muted">
                                <i class="ri-information-line"></i> Only available books can be issued.
                            </p>
                            <p class="text-muted">
                                <i class="ri-information-line"></i> Available quantity is automatically updated.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Issue book
$('#issueBookForm').on('submit', function(e) {
    e.preventDefault();
    
    const bookSelect = $('#bookSelect');
    const available = bookSelect.find(':selected').data('available');
    
    if (available <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Book Not Available',
            text: 'This book is not available for issue.'
        });
        return;
    }
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/library/issue-book.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Book Issued!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
            }
        }
    });
});
</script>

