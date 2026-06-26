<?php
/**
 * Public Vendor Quotation Portal — RFQ listings & online vendor submissions
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Vendor Quotation Portal — ' . APP_NAME;
$token = sanitize($_GET['t'] ?? '');
$single = null;

if ($token) {
    $single = fetchOne(executeQuery(
        "SELECT q.*, b.branch_name FROM hr_quotations q
         LEFT JOIN branches b ON q.branch_id = b.id
         WHERE q.public_token = ? AND q.is_public = 1",
        's', [$token]
    ));
}

$quotations = fetchAll(executeQuery(
    "SELECT q.*, b.branch_name,
     (SELECT COUNT(*) FROM hr_quotation_vendors v WHERE v.quotation_id = q.id) AS vendor_count,
     (SELECT COUNT(*) FROM hr_quotation_items i WHERE i.quotation_id = q.id) AS item_count
     FROM hr_quotations q
     LEFT JOIN branches b ON q.branch_id = b.id
     WHERE q.is_public = 1
       AND q.status NOT IN ('Approved','Rejected','Closed')
       AND (q.public_deadline IS NULL OR q.public_deadline >= CURDATE())
     ORDER BY q.published_at DESC"
));

function quotationOpen($q) {
    if (in_array($q['status'], ['Approved', 'Rejected', 'Closed'], true)) return false;
    if (!empty($q['public_deadline']) && $q['public_deadline'] < date('Y-m-d')) return false;
    return true;
}

function getQuotationItems($quotationId) {
    return fetchAll(executeQuery(
        "SELECT * FROM hr_quotation_items WHERE quotation_id = ? ORDER BY line_no ASC, id ASC",
        'i', [(int)$quotationId]
    ));
}

$singleItems = $single ? getQuotationItems($single['id']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background: #f4f6fb; }
        .hero { background: linear-gradient(135deg, #0f766e, #115e59); color: #fff; padding: 56px 0; }
        .rfq-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .rfq-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.12); }
    </style>
</head>
<body>
<div class="hero text-center">
    <div class="container">
        <h1 class="display-6 fw-bold"><i class="ri-file-list-3-line"></i> Vendor Quotation Portal</h1>
        <p class="lead opacity-75 mb-0">Submit competitive quotes for open procurement requests from <?php echo htmlspecialchars(APP_NAME); ?></p>
        <a href="<?php echo APP_URL; ?>login.php" class="btn btn-outline-light btn-sm mt-3">Staff Login</a>
    </div>
</div>

<div class="container py-5">
<?php if ($single): ?>
    <?php if (!quotationOpen($single)): ?>
    <div class="alert alert-warning">This quotation is closed for submissions.</div>
    <?php else: ?>
    <div class="card rfq-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <span class="badge bg-teal text-white" style="background:#0f766e"><?php echo htmlspecialchars($single['quotation_no']); ?></span>
                    <h3 class="mt-2 mb-1"><?php echo htmlspecialchars($single['title']); ?></h3>
                    <p class="text-muted mb-0">
                        <i class="ri-building-line"></i> <?php echo htmlspecialchars($single['branch_name'] ?? 'All branches'); ?>
                        <?php if ($single['required_by_date']): ?>
                        &nbsp;·&nbsp; <i class="ri-calendar-line"></i> Required by <?php echo date('d M Y', strtotime($single['required_by_date'])); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <?php if ($single['public_deadline']): ?>
                <div class="text-end">
                    <small class="text-danger d-block">Submit quotes by</small>
                    <strong><?php echo date('d M Y', strtotime($single['public_deadline'])); ?></strong>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($single['description']): ?>
            <div class="bg-light rounded p-3 mb-3"><?php echo nl2br(htmlspecialchars($single['description'])); ?></div>
            <?php endif; ?>

            <?php if (!empty($singleItems)): ?>
            <h5 class="mb-2"><i class="ri-list-check"></i> Items Requested</h5>
            <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm">
            <thead class="table-light">
            <tr><th>#</th><th>Item</th><th>Qty</th><th>Unit</th><th>Est. unit price</th><th>Line total</th><th>Specifications</th></tr>
            </thead>
            <tbody>
            <?php foreach ($singleItems as $it): ?>
            <tr>
            <td><?php echo (int)$it['line_no']; ?></td>
            <td><strong><?php echo htmlspecialchars($it['item_name']); ?></strong></td>
            <td><?php echo number_format((float)$it['quantity'], 2); ?></td>
            <td><?php echo htmlspecialchars($it['unit'] ?? 'pcs'); ?></td>
            <td><?php echo CURRENCY_SYMBOL . number_format((float)$it['unit_price'], 2); ?></td>
            <td><?php echo CURRENCY_SYMBOL . number_format((float)$it['line_total'], 2); ?></td>
            <td><small><?php echo htmlspecialchars($it['description'] ?? '—'); ?></small></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr class="table-light">
            <td colspan="5" class="text-end fw-semibold">Indicative total</td>
            <td colspan="2"><strong><?php echo CURRENCY_SYMBOL . number_format((float)$single['total_estimated'], 2); ?></strong></td>
            </tr>
            </tfoot>
            </table>
            </div>
            <?php elseif ($single['total_estimated'] > 0): ?>
            <p class="small text-muted mb-4">Estimated budget: <strong><?php echo CURRENCY_SYMBOL . number_format($single['total_estimated'], 2); ?></strong> (indicative)</p>
            <?php endif; ?>

            <h5 class="mb-3"><i class="ri-send-plane-line"></i> Submit Your Quote</h5>
            <form id="vendorQuoteForm" enctype="multipart/form-data">
                <input type="hidden" name="public_token" value="<?php echo htmlspecialchars($single['public_token']); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Company / Vendor Name *</label>
                        <input type="text" name="vendor_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact (email or phone) *</label>
                        <input type="text" name="vendor_contact" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quoted Amount (<?php echo CURRENCY_SYMBOL; ?>) *</label>
                        <input type="number" step="0.01" min="0.01" name="quoted_amount" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Delivery (days)</label>
                        <input type="number" min="1" name="delivery_days" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quotation document</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes / terms</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Payment terms, warranty, inclusions…"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                            <i class="ri-check-line"></i> Submit Quotation
                        </button>
                    </div>
                </div>
            </form>
            <div id="formMsg" class="mt-3"></div>
        </div>
    </div>
    <p class="text-center"><a href="<?php echo APP_URL; ?>quotation-portal.php">&larr; View all open RFQs</a></p>
    <?php endif; ?>

<?php else: ?>
    <h4 class="mb-4">Open Requests for Quotation</h4>
    <?php if (empty($quotations)): ?>
    <div class="alert alert-info text-center">No open quotation requests at this time.</div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($quotations as $q): ?>
        <div class="col-md-6">
            <div class="card rfq-card h-100">
                <div class="card-body">
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($q['quotation_no']); ?></span>
                    <h5 class="card-title mt-2"><?php echo htmlspecialchars($q['title']); ?></h5>
                    <p class="text-muted small mb-2">
                        <i class="ri-building-line"></i> <?php echo htmlspecialchars($q['branch_name'] ?? 'All branches'); ?>
                        <?php if ($q['public_deadline']): ?>
                        &nbsp;·&nbsp; <i class="ri-time-line"></i> Deadline: <?php echo date('d M Y', strtotime($q['public_deadline'])); ?>
                        <?php endif; ?>
                    </p>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($q['description'] ?? '', 0, 180))); ?><?php echo strlen($q['description'] ?? '') > 180 ? '…' : ''; ?></p>
                    <p class="small text-muted"><?php echo (int)$q['vendor_count']; ?> quote(s) · <?php echo (int)$q['item_count']; ?> item(s)</p>
                    <a href="<?php echo APP_URL; ?>quotation-portal.php?t=<?php echo urlencode($q['public_token']); ?>" class="btn btn-primary">
                        <i class="ri-send-plane-line"></i> Submit Quote
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
</div>

<script>
(function () {
    var form = document.getElementById('vendorQuoteForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('submitBtn');
        var msg = document.getElementById('formMsg');
        btn.disabled = true;
        msg.innerHTML = '';
        fetch('<?php echo APP_URL; ?>ajax/public/submit-vendor-quote.php', {
            method: 'POST',
            body: new FormData(form)
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (res.success) {
                msg.innerHTML = '<div class="alert alert-success">' + res.message + '</div>';
                form.reset();
            } else {
                msg.innerHTML = '<div class="alert alert-danger">' + (res.message || 'Submission failed') + '</div>';
                btn.disabled = false;
            }
        }).catch(function () {
            msg.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
            btn.disabled = false;
        });
    });
})();
</script>
</body>
</html>
