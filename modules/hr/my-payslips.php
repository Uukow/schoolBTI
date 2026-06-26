<?php
require_once '../../config/config.php';
requireLogin();
requireRole(['Teacher', 'Staff']);

$pageTitle = 'My Payslips';
$currentUser = getCurrentUser();
$staff = fetchOne(executeQuery("SELECT id, staff_id, first_name, last_name FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));

if (!$staff) {
    $_SESSION['error'] = 'Staff record not found';
    redirect(APP_URL . 'index.php');
}

$payments = fetchAll(executeQuery(
    "SELECT id, payment_month, net_salary, payment_date, payment_status FROM salary_payments
     WHERE staff_id = ? ORDER BY payment_month DESC",
    'i', [$staff['id']]
));

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>
<div class="content-page"><div class="content"><div class="container-fluid">
<div class="page-title-box"><h4 class="page-title">My Payslips</h4></div>
<div class="card"><div class="card-body">
<?php if (empty($payments)): ?>
<p class="text-muted mb-0">No payslips available yet.</p>
<?php else: ?>
<div class="table-responsive"><table class="table table-hover">
<thead><tr><th>Month</th><th>Net Salary</th><th>Status</th><th>Payment Date</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($payments as $p): ?>
<tr>
<td><?php echo date('F Y', strtotime($p['payment_month'])); ?></td>
<td><?php echo formatCurrency($p['net_salary']); ?></td>
<td><span class="badge bg-<?php echo $p['payment_date'] ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($p['payment_status'] ?? ($p['payment_date'] ? 'Paid' : 'Pending')); ?></span></td>
<td><?php echo $p['payment_date'] ? formatDate($p['payment_date']) : '—'; ?></td>
<td>
<a href="<?php echo APP_URL; ?>modules/hr/payslip.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-primary">View</a>
<a href="<?php echo APP_URL; ?>modules/hr/download-payslip-pdf.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-outline-success">PDF</a>
</td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</div></div></div></div></div>
<?php include '../../includes/footer.php'; ?>
