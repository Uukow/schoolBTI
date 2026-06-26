<?php
require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Librarian'])) jsonResponse(false, 'Permission denied');

$bookId = $_POST['id'] ?? 0;

if (empty($bookId)) jsonResponse(false, 'Invalid book ID');

// Check if book is issued
$checkSql = "SELECT COUNT(*) as count FROM library_issues WHERE book_id = ? AND status = 'Issued'";
$stmt = executeQuery($checkSql, 'i', [$bookId]);
$result = fetchOne($stmt);

if ($result['count'] > 0) {
    jsonResponse(false, 'Cannot delete book that is currently issued');
}

$sql = "DELETE FROM library_books WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$bookId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Book', 'Library', "Deleted book ID: $bookId");
    jsonResponse(true, 'Book deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete book');
}

