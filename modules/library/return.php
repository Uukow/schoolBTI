<?php
/**
 * Return Books
 * 
 * Return issued library books
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Librarian']);

$pageTitle = 'Return Books';

// Get issued books
$sql = "SELECT li.*, b.book_title, b.book_code, b.author,
        s.student_id, s.first_name, s.last_name, c.class_name,
        DATEDIFF(CURDATE(), li.due_date) as days_overdue
        FROM library_issues li
        INNER JOIN library_books b ON li.book_id = b.id
        INNER JOIN students s ON li.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE li.status = 'Issued'
        ORDER BY li.due_date ASC";

$issuedBooks = fetchAll(executeQuery($sql));

// Calculate statistics
$statsSql = "SELECT 
    COUNT(*) as total_issued,
    SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) > 0 THEN 1 ELSE 0 END) as overdue_count
    FROM library_issues
    WHERE status = 'Issued'";
$stats = fetchOne(executeQuery($statsSql));

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
                        <h4 class="page-title">Return Library Books</h4>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-primary-lighten text-primary">
                                        <i class="ri-book-open-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Total Issued</h5>
                                    <h2 class="mb-0"><?php echo $stats['total_issued'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card widget-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon bg-danger-lighten text-danger">
                                        <i class="ri-time-warning-line font-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0 mb-1 text-muted">Overdue</h5>
                                    <h2 class="mb-0"><?php echo $stats['overdue_count'] ?? 0; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Issued Books List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Issued Books</h4>
                            
                            <?php if (!empty($issuedBooks)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Book</th>
                                            <th>Student</th>
                                            <th>Issue Date</th>
                                            <th>Due Date</th>
                                            <th>Days Overdue</th>
                                            <th>Fine Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($issuedBooks as $issue): ?>
                                        <tr class="<?php echo $issue['days_overdue'] > 0 ? 'table-danger' : ''; ?>">
                                            <td>
                                                <strong><?php echo htmlspecialchars($issue['book_title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($issue['book_code']); ?> | <?php echo htmlspecialchars($issue['author']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($issue['first_name'] . ' ' . $issue['last_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($issue['student_id']); ?> | <?php echo htmlspecialchars($issue['class_name']); ?></small>
                                            </td>
                                            <td><?php echo formatDate($issue['issue_date']); ?></td>
                                            <td><?php echo formatDate($issue['due_date']); ?></td>
                                            <td>
                                                <?php if ($issue['days_overdue'] > 0): ?>
                                                    <span class="badge bg-danger"><?php echo $issue['days_overdue']; ?> days</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">On time</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $fine = $issue['days_overdue'] > 0 ? $issue['days_overdue'] * 5 : 0; // 5 per day
                                                ?>
                                                <strong><?php echo formatCurrency($fine); ?></strong>
                                            </td>
                                            <td>
                                                <button onclick="returnBook(<?php echo $issue['id']; ?>, <?php echo $fine; ?>)" 
                                                        class="btn btn-sm btn-success">
                                                    <i class="ri-check-line"></i> Return
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="ri-information-line font-24"></i>
                                <h5 class="mt-2">No Issued Books</h5>
                                <p class="mb-0">All books have been returned.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Return book
function returnBook(issueId, fineAmount) {
    Swal.fire({
        title: 'Return Book?',
        html: fineAmount > 0 ? 
            `<p>Return this book?</p><p class="text-danger">Fine Amount: <strong>${fineAmount.toFixed(2)}</strong></p>` :
            '<p>Return this book?</p>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Return!',
        input: fineAmount > 0 ? 'number' : null,
        inputLabel: fineAmount > 0 ? 'Fine Amount (if different)' : null,
        inputValue: fineAmount > 0 ? fineAmount : null,
        inputAttributes: {
            step: '0.01',
            min: '0'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const fine = fineAmount > 0 && result.value ? result.value : fineAmount;
            
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/library/return-book.php',
                type: 'POST',
                data: { 
                    issue_id: issueId,
                    fine_amount: fine
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Book Returned!',
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
        }
    });
}
</script>

