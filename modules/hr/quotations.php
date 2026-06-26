<?php
/**
 * Quotation Management — RFQ, vendor quotes, public portal
 */
require_once '../../config/config.php';
hrRequirePage('hr_quotations', 'view', ['Accountant']);

$pageTitle = 'Quotations';
$currentUser = getCurrentUser();
$canManage = hasRole(['Super Admin', 'Admin', 'Accountant'])
    || (function_exists('canPerform') && canPerform('hr_quotations', 'approve'));

$branches = fetchAll(executeQuery("SELECT id, branch_name FROM branches WHERE is_active = 1 ORDER BY branch_name"));
$year = (int)date('Y');

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-page">
<div class="content">
<div class="container-fluid">

<div class="row">
<div class="col-12">
<div class="page-title-box">
<div class="page-title-right">
<a href="<?php echo APP_URL; ?>quotation-portal.php" target="_blank" class="btn btn-outline-info me-2">
<i class="ri-external-link-line"></i> Vendor Portal
</a>
<?php if ($canManage): ?>
<button type="button" class="btn btn-primary" id="btnNewQuotation">
<i class="ri-add-line"></i> New RFQ
</button>
<?php endif; ?>
</div>
<h4 class="page-title">Quotation &amp; Procurement RFQ</h4>
<p class="text-muted mb-0 small">Create requests for quotation, collect vendor bids, compare and approve</p>
</div>
</div>
</div>

<!-- KPI -->
<div class="row" id="quoteStats">
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-2"><i class="ri-file-list-3-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Total RFQs</p><h4 class="mb-0" id="statTotal">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-secondary-lighten text-secondary rounded p-2 me-2"><i class="ri-draft-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Draft</p><h4 class="mb-0" id="statDraft">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-warning-lighten text-warning rounded p-2 me-2"><i class="ri-time-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Pending</p><h4 class="mb-0" id="statPending">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-success-lighten text-success rounded p-2 me-2"><i class="ri-check-double-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Approved</p><h4 class="mb-0" id="statApproved">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-info-lighten text-info rounded p-2 me-2"><i class="ri-global-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Public Open</p><h4 class="mb-0" id="statPublic">—</h4></div>
</div></div></div>
</div>
<div class="col-md-2">
<div class="card widget-stat-card"><div class="card-body">
<div class="d-flex align-items-center">
<div class="stat-icon bg-primary-lighten text-primary rounded p-2 me-2"><i class="ri-store-2-line font-22"></i></div>
<div><p class="text-muted mb-1 small">Vendor Quotes</p><h4 class="mb-0" id="statVendors">—</h4></div>
</div></div></div>
</div>
</div>

<!-- Filters -->
<div class="card mb-3"><div class="card-body">
<div class="row g-2 align-items-end">
<div class="col-md-2">
<label class="form-label small">Year</label>
<select id="filterYear" class="form-select">
<option value="">All</option>
<?php for ($y = $year - 1; $y <= $year + 1; $y++): ?>
<option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
<?php endfor; ?>
</select>
</div>
<div class="col-md-2">
<label class="form-label small">Status</label>
<select id="filterStatus" class="form-select">
<option value="">All</option>
<option value="Draft">Draft</option>
<option value="Pending_Approval">Pending Approval</option>
<option value="Approved">Approved</option>
<option value="Rejected">Rejected</option>
<option value="Closed">Closed</option>
</select>
</div>
<?php if (hasRole(['Super Admin'])): ?>
<div class="col-md-2">
<label class="form-label small">Branch</label>
<select id="filterBranch" class="form-select">
<option value="">All</option>
<?php foreach ($branches as $b): ?>
<option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<div class="col-md-3">
<label class="form-label small">Search</label>
<input type="text" id="filterSearch" class="form-control" placeholder="Title, quotation no…">
</div>
<div class="col-md-2">
<button type="button" class="btn btn-primary w-100" id="btnFilter"><i class="ri-filter-line"></i> Apply</button>
</div>
</div>
</div></div>

<!-- Table -->
<div class="card">
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle" id="quotationsTable">
<thead class="table-light">
<tr>
<th>RFQ No</th>
<th>Title</th>
<th>Branch</th>
<th>Required By</th>
<th>Est. / Quotes</th>
<th>Vendors</th>
<th>Status</th>
<th>Public</th>
<th style="min-width:170px">Actions</th>
</tr>
</thead>
<tbody><tr><td colspan="9" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div>
</div>

</div>
</div>
</div>

<!-- Create / Edit -->
<div class="modal fade" id="quotationModal" tabindex="-1">
<div class="modal-dialog modal-xl">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="quotationModalTitle"><i class="ri-file-add-line"></i> New RFQ</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<form id="quotationForm">
<input type="hidden" id="quotationId" value="">
<div class="row g-3">
<div class="col-md-8">
<label class="form-label">Title <span class="text-danger">*</span></label>
<input type="text" class="form-control" id="quoteTitle" required placeholder="e.g. ICT Lab Equipment Supply">
</div>
<div class="col-md-4">
<label class="form-label">Status</label>
<select class="form-select" id="quoteStatus">
<option value="Draft">Draft</option>
<option value="Pending_Approval">Pending Approval</option>
<option value="Approved">Approved</option>
<option value="Rejected">Rejected</option>
<option value="Closed">Closed</option>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Required by</label>
<input type="date" class="form-control" id="requiredBy">
</div>
<div class="col-md-4">
<label class="form-label">Estimated budget (<?php echo CURRENCY_SYMBOL; ?>)</label>
<input type="number" step="0.01" class="form-control" id="totalEst" placeholder="Auto from items">
<small class="text-muted">Calculated from line items when added</small>
</div>
<div class="col-md-4">
<label class="form-label">Branch</label>
<select class="form-select" id="quoteBranch">
<option value="">All / HQ</option>
<?php foreach ($branches as $b): ?>
<option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-12">
<label class="form-label">General notes</label>
<textarea class="form-control" id="quoteDescription" rows="2" placeholder="Delivery terms, evaluation criteria, special instructions…"></textarea>
</div>
<div class="col-12">
<div class="d-flex justify-content-between align-items-center mb-2">
<label class="form-label mb-0">Line items <span class="text-danger">*</span></label>
<button type="button" class="btn btn-sm btn-outline-primary" id="btnAddLineItem"><i class="ri-add-line"></i> Add Item</button>
</div>
<div class="table-responsive border rounded">
<table class="table table-sm align-middle mb-0" id="lineItemsTable">
<thead class="table-light">
<tr>
<th style="width:28%">Item / Description</th>
<th style="width:10%">Qty</th>
<th style="width:10%">Unit</th>
<th style="width:14%">Unit price</th>
<th style="width:14%">Line total</th>
<th style="width:20%">Specs</th>
<th style="width:4%"></th>
</tr>
</thead>
<tbody id="lineItemsBody"></tbody>
<tfoot>
<tr class="table-light">
<td colspan="4" class="text-end fw-semibold">Estimated total</td>
<td colspan="3"><span id="itemsGrandTotal"><?php echo CURRENCY_SYMBOL; ?>0.00</span></td>
</tr>
</tfoot>
</table>
</div>
<p class="text-muted small mt-1 mb-0" id="lineItemsEmpty">No items yet — click <strong>Add Item</strong> to build your RFQ list.</p>
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="saveQuotationBtn"><i class="ri-save-line"></i> Save</button>
</div>
</div>
</div>
</div>

<!-- View -->
<div class="modal fade" id="viewQuotationModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">RFQ Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body" id="viewQuotationBody"></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
</div>
</div>
</div>

<!-- Vendors / Compare -->
<div class="modal fade" id="vendorsModal" tabindex="-1">
<div class="modal-dialog modal-xl">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="vendorsModalTitle"><i class="ri-store-2-line"></i> Vendor Quotes</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" id="vendorsQuotationId" value="">
<?php if ($canManage): ?>
<div class="card bg-light mb-3"><div class="card-body">
<h6 class="card-title">Add vendor quote manually</h6>
<div class="row g-2">
<div class="col-md-3"><input type="text" id="manualVendorName" class="form-control form-control-sm" placeholder="Vendor name"></div>
<div class="col-md-3"><input type="text" id="manualVendorContact" class="form-control form-control-sm" placeholder="Contact"></div>
<div class="col-md-2"><input type="number" step="0.01" id="manualAmount" class="form-control form-control-sm" placeholder="Amount"></div>
<div class="col-md-2"><input type="number" id="manualDelivery" class="form-control form-control-sm" placeholder="Days"></div>
<div class="col-md-2"><button type="button" class="btn btn-sm btn-primary w-100" id="btnAddVendor">Add</button></div>
</div>
</div></div>
<?php endif; ?>
<div class="table-responsive">
<table class="table table-hover align-middle" id="vendorsTable">
<thead class="table-light">
<tr>
<th>Vendor</th>
<th>Contact</th>
<th>Quoted</th>
<th>Delivery</th>
<th>Attachment</th>
<th>Notes</th>
<th>Winner</th>
<?php if ($canManage): ?><th>Actions</th><?php endif; ?>
</tr>
</thead>
<tbody><tr><td colspan="<?php echo $canManage ? 8 : 7; ?>" class="text-muted text-center">Loading…</td></tr></tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<!-- Publish public -->
<div class="modal fade" id="publishModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title"><i class="ri-global-line"></i> Publish for Vendors</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<input type="hidden" id="publishQuotationId" value="">
<p class="text-muted">Vendors can submit quotes via the public portal using a unique link.</p>
<label class="form-label">Submission deadline</label>
<input type="date" id="publicDeadline" class="form-control mb-3">
<div id="publicLinkBox" class="d-none">
<label class="form-label">Public link</label>
<div class="input-group">
<input type="text" class="form-control" id="publicLink" readonly>
<button type="button" class="btn btn-outline-secondary" id="btnCopyLink"><i class="ri-file-copy-line"></i></button>
</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
<button type="button" class="btn btn-success" id="btnConfirmPublish"><i class="ri-global-line"></i> Publish Now</button>
</div>
</div>
</div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function () {
    var H = window.HrModule;
    var CAN_MANAGE = <?php echo $canManage ? 'true' : 'false'; ?>;
    var CURRENCY = <?php echo json_encode(CURRENCY_SYMBOL); ?>;
    var APP_URL = <?php echo json_encode(APP_URL); ?>;

    var quotesCache = [];
    var vendorsCache = [];
    var activeQuotationId = 0;
    var quotationModal = null;
    var viewModal = null;
    var vendorsModal = null;
    var publishModal = null;

    var statusMap = {
        Draft: 'secondary',
        Pending_Approval: 'warning',
        Approved: 'success',
        Rejected: 'danger',
        Closed: 'dark'
    };

    function statusBadge(s, map) {
        return H.badge(String(s).replace(/_/g, ' '), (map && map[s]) || 'secondary');
    }

    function lineItemRowHtml(data) {
        data = data || {};
        return '<tr class="line-item-row">' +
            '<td><input type="text" class="form-control form-control-sm item-name" value="' + H.escapeHtml(data.item_name || '') + '" placeholder="Item name" required></td>' +
            '<td><input type="number" step="0.01" min="0.01" class="form-control form-control-sm item-qty" value="' + (data.quantity != null ? data.quantity : 1) + '"></td>' +
            '<td><input type="text" class="form-control form-control-sm item-unit" value="' + H.escapeHtml(data.unit || 'pcs') + '"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm item-price" value="' + (data.unit_price != null ? data.unit_price : '') + '" placeholder="0.00"></td>' +
            '<td class="item-line-total fw-semibold">' + CURRENCY + '0.00</td>' +
            '<td><input type="text" class="form-control form-control-sm item-spec" value="' + H.escapeHtml(data.description || '') + '" placeholder="Specs"></td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="Remove"><i class="ri-close-line"></i></button></td>' +
            '</tr>';
    }

    function addLineItemRow(data) {
        var tbody = document.getElementById('lineItemsBody');
        tbody.insertAdjacentHTML('beforeend', lineItemRowHtml(data));
        var rows = tbody.querySelectorAll('.line-item-row');
        recalcLineRow(rows[rows.length - 1]);
        toggleLineItemsEmpty();
    }

    function clearLineItems() {
        document.getElementById('lineItemsBody').innerHTML = '';
        recalcGrandTotal();
        toggleLineItemsEmpty();
    }

    function toggleLineItemsEmpty() {
        var n = document.querySelectorAll('#lineItemsBody .line-item-row').length;
        document.getElementById('lineItemsEmpty').classList.toggle('d-none', n > 0);
    }

    function recalcLineRow(row) {
        if (!row) return;
        var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        var price = parseFloat(row.querySelector('.item-price').value) || 0;
        var total = Math.round(qty * price * 100) / 100;
        row.querySelector('.item-line-total').textContent = CURRENCY + total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        recalcGrandTotal();
    }

    function recalcGrandTotal() {
        var sum = 0;
        document.querySelectorAll('#lineItemsBody .line-item-row').forEach(function (row) {
            var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            var price = parseFloat(row.querySelector('.item-price').value) || 0;
            sum += qty * price;
        });
        sum = Math.round(sum * 100) / 100;
        document.getElementById('itemsGrandTotal').textContent = CURRENCY + sum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('totalEst').value = sum > 0 ? sum : '';
    }

    function collectLineItems() {
        var items = [];
        document.querySelectorAll('#lineItemsBody .line-item-row').forEach(function (row) {
            var name = row.querySelector('.item-name').value.trim();
            if (!name) return;
            items.push({
                item_name: name,
                description: row.querySelector('.item-spec').value.trim(),
                quantity: row.querySelector('.item-qty').value,
                unit: row.querySelector('.item-unit').value.trim() || 'pcs',
                unit_price: row.querySelector('.item-price').value
            });
        });
        return items;
    }

    function renderItemsTableHtml(items) {
        if (!items || !items.length) return '<p class="text-muted">No line items</p>';
        var html = '<div class="table-responsive"><table class="table table-sm table-bordered"><thead class="table-light"><tr>' +
            '<th>#</th><th>Item</th><th>Qty</th><th>Unit</th><th>Unit price</th><th>Total</th><th>Specs</th></tr></thead><tbody>';
        items.forEach(function (it, idx) {
            html += '<tr><td>' + (it.line_no || idx + 1) + '</td>' +
                '<td><strong>' + H.escapeHtml(it.item_name) + '</strong></td>' +
                '<td>' + Number(it.quantity) + '</td>' +
                '<td>' + H.escapeHtml(it.unit || 'pcs') + '</td>' +
                '<td>' + CURRENCY + Number(it.unit_price).toLocaleString() + '</td>' +
                '<td>' + CURRENCY + Number(it.line_total).toLocaleString() + '</td>' +
                '<td><small>' + H.escapeHtml(it.description || '—') + '</small></td></tr>';
        });
        html += '</tbody></table></div>';
        return html;
    }

    function buildQuery() {
        var q = [];
        var year = document.getElementById('filterYear').value;
        if (year) q.push('year=' + year);
        var st = document.getElementById('filterStatus').value;
        if (st) q.push('status=' + encodeURIComponent(st));
        var br = document.getElementById('filterBranch');
        if (br && br.value) q.push('branch_id=' + br.value);
        var search = document.getElementById('filterSearch').value.trim();
        if (search) q.push('q=' + encodeURIComponent(search));
        return q.length ? '?' + q.join('&') : '';
    }

    function updateStats(s) {
        s = s || {};
        document.getElementById('statTotal').textContent = s.total || 0;
        document.getElementById('statDraft').textContent = s.draft || 0;
        document.getElementById('statPending').textContent = s.pending || 0;
        document.getElementById('statApproved').textContent = s.approved || 0;
        document.getElementById('statPublic').textContent = s.public_open || 0;
        document.getElementById('statVendors').textContent = s.vendor_quotes || 0;
    }

    function quoteActions(q) {
        var html = '<div class="btn-group btn-group-sm">' +
            '<button type="button" class="btn btn-outline-info btn-view" data-id="' + q.id + '" title="View"><i class="ri-eye-line"></i></button>' +
            '<button type="button" class="btn btn-outline-secondary btn-vendors" data-id="' + q.id + '" data-title="' + H.escapeHtml(q.title) + '" title="Vendor quotes"><i class="ri-store-2-line"></i></button>';
        if (CAN_MANAGE) {
            html += '<button type="button" class="btn btn-outline-primary btn-edit" data-id="' + q.id + '" title="Edit"><i class="ri-edit-line"></i></button>' +
                '<div class="btn-group btn-group-sm">' +
                '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>' +
                '<ul class="dropdown-menu dropdown-menu-end">';
            if (!q.is_public || q.is_public == 0) {
                html += '<li><a class="dropdown-item btn-publish" data-id="' + q.id + '" href="#"><i class="ri-global-line"></i> Publish for vendors</a></li>';
            } else {
                html += '<li><a class="dropdown-item btn-copy-public" data-token="' + H.escapeHtml(q.public_token || '') + '" href="#"><i class="ri-link"></i> Copy public link</a></li>';
                html += '<li><a class="dropdown-item btn-unpublish" data-id="' + q.id + '" href="#">Close public portal</a></li>';
            }
            if (q.status === 'Pending_Approval') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-success quote-status" data-id="' + q.id + '" data-status="Approved" href="#">Approve</a></li>';
                html += '<li><a class="dropdown-item text-danger quote-status" data-id="' + q.id + '" data-status="Rejected" href="#">Reject</a></li>';
            }
            if (q.status !== 'Closed') {
                html += '<li><a class="dropdown-item quote-status" data-id="' + q.id + '" data-status="Closed" href="#">Close RFQ</a></li>';
            }
            html += '</ul></div>';
        }
        html += '</div>';
        return html;
    }

    function publicBadge(q) {
        if (!q.is_public || q.is_public == 0) return '<span class="text-muted">—</span>';
        var dl = q.public_deadline ? H.formatDate(q.public_deadline) : 'Open';
        return '<span class="badge bg-info">Public</span><br><small class="text-muted">Until ' + dl + '</small>';
    }

    function load() {
        var tb = document.querySelector('#quotationsTable tbody');
        tb.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-quotations.php' + buildQuery()).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            var payload = res.data || {};
            quotesCache = payload.quotations || [];
            updateStats(payload.stats);
            if (!quotesCache.length) {
                tb.innerHTML = '<tr><td colspan="9" class="text-muted text-center py-4">No quotations found</td></tr>';
                return;
            }
            tb.innerHTML = quotesCache.map(function (q) {
                var est = q.total_estimated > 0 ? CURRENCY + Number(q.total_estimated).toLocaleString() : '—';
                var range = '';
                if (q.lowest_quote != null) {
                    range = '<br><small class="text-success">Low: ' + CURRENCY + Number(q.lowest_quote).toLocaleString() + '</small>';
                    if (q.highest_quote != null && q.highest_quote != q.lowest_quote) {
                        range += ' <small class="text-muted">High: ' + CURRENCY + Number(q.highest_quote).toLocaleString() + '</small>';
                    }
                }
                return '<tr>' +
                    '<td><code>' + H.escapeHtml(q.quotation_no) + '</code></td>' +
                    '<td><strong>' + H.escapeHtml(q.title) + '</strong>' +
                    (q.item_count > 0 ? '<br><small class="text-muted"><i class="ri-list-check"></i> ' + q.item_count + ' item(s)</small>' : '') +
                    (q.selected_vendor ? '<br><small class="text-success"><i class="ri-award-line"></i> ' + H.escapeHtml(q.selected_vendor) + '</small>' : '') + '</td>' +
                    '<td>' + H.escapeHtml(q.branch_name || 'All') + '</td>' +
                    '<td>' + H.formatDate(q.required_by_date) + '</td>' +
                    '<td>' + est + range + '</td>' +
                    '<td><span class="badge bg-light text-dark">' + (q.vendor_count || 0) + '</span></td>' +
                    '<td>' + statusBadge(q.status, statusMap) + '</td>' +
                    '<td>' + publicBadge(q) + '</td>' +
                    '<td>' + quoteActions(q) + '</td></tr>';
            }).join('');
        });
    }

    function openForm(data) {
        document.getElementById('quotationForm').reset();
        clearLineItems();
        document.getElementById('quotationId').value = data ? data.id : '';
        document.getElementById('quotationModalTitle').innerHTML = data
            ? '<i class="ri-edit-line"></i> Edit RFQ'
            : '<i class="ri-file-add-line"></i> New RFQ';
        if (data) {
            document.getElementById('quoteTitle').value = data.title || '';
            document.getElementById('quoteStatus').value = data.status || 'Draft';
            document.getElementById('requiredBy').value = (data.required_by_date || '').substring(0, 10);
            document.getElementById('totalEst').value = data.total_estimated || '';
            document.getElementById('quoteBranch').value = data.branch_id || '';
            document.getElementById('quoteDescription').value = data.description || '';
            H.get('ajax/hr/get-quotation-items.php?quotation_id=' + data.id).then(function (res) {
                if (res.success && res.data && res.data.length) {
                    res.data.forEach(function (it) { addLineItemRow(it); });
                } else {
                    addLineItemRow();
                }
            });
        } else {
            addLineItemRow();
        }
        if (!quotationModal) quotationModal = new bootstrap.Modal(document.getElementById('quotationModal'));
        quotationModal.show();
    }

    function viewQuotation(id) {
        var q = quotesCache.find(function (x) { return String(x.id) === String(id); });
        if (!q) return;
        var publicHtml = '';
        if (q.is_public == 1 && q.public_token) {
            publicHtml = '<div class="col-12"><strong>Public vendor link</strong><br><a href="' + APP_URL + 'quotation-portal.php?t=' + encodeURIComponent(q.public_token) + '" target="_blank">' + APP_URL + 'quotation-portal.php?t=' + q.public_token + '</a></div>';
        }
        document.getElementById('viewQuotationBody').innerHTML =
            '<div class="row g-3">' +
            '<div class="col-md-4"><strong>RFQ No</strong><br><code>' + H.escapeHtml(q.quotation_no) + '</code></div>' +
            '<div class="col-md-4"><strong>Status</strong><br>' + statusBadge(q.status, statusMap) + '</div>' +
            '<div class="col-md-4"><strong>Requested by</strong><br>' + H.escapeHtml(q.requested_by_name || '—') + '</div>' +
            '<div class="col-12"><strong>Title</strong><br>' + H.escapeHtml(q.title) + '</div>' +
            '<div class="col-md-4"><strong>Branch</strong><br>' + H.escapeHtml(q.branch_name || 'All') + '</div>' +
            '<div class="col-md-4"><strong>Required by</strong><br>' + H.formatDate(q.required_by_date) + '</div>' +
            '<div class="col-md-4"><strong>Estimated budget</strong><br>' + (q.total_estimated > 0 ? CURRENCY + Number(q.total_estimated).toLocaleString() : '—') + '</div>' +
            '<div class="col-12"><strong>Notes</strong><br>' + H.escapeHtml(q.description || '—').replace(/\n/g, '<br>') + '</div>' +
            '<div class="col-12"><strong>Line items</strong><div id="viewItemsPlaceholder" class="text-muted">Loading…</div></div>' +
            publicHtml + '</div>';
        H.get('ajax/hr/get-quotation-items.php?quotation_id=' + id).then(function (res) {
            document.getElementById('viewItemsPlaceholder').innerHTML = res.success ? renderItemsTableHtml(res.data) : '—';
        });
        if (!viewModal) viewModal = new bootstrap.Modal(document.getElementById('viewQuotationModal'));
        viewModal.show();
    }

    function loadVendors(quotationId) {
        activeQuotationId = quotationId;
        document.getElementById('vendorsQuotationId').value = quotationId;
        var cols = CAN_MANAGE ? 8 : 7;
        var tb = document.querySelector('#vendorsTable tbody');
        tb.innerHTML = '<tr><td colspan="' + cols + '" class="text-center text-muted">Loading…</td></tr>';
        H.get('ajax/hr/get-quotation-vendors.php?quotation_id=' + quotationId).then(function (res) {
            if (!res.success) { H.error(res.message); return; }
            vendorsCache = res.data || [];
            if (!vendorsCache.length) {
                tb.innerHTML = '<tr><td colspan="' + cols + '" class="text-muted text-center py-3">No vendor quotes yet</td></tr>';
                return;
            }
            tb.innerHTML = vendorsCache.map(function (v) {
                var att = v.attachment_path
                    ? '<a href="' + APP_URL + v.attachment_path + '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="ri-attachment-line"></i></a>'
                    : '—';
                var winner = v.is_selected == 1 ? '<span class="badge bg-success">Selected</span>' : '—';
                var acts = '';
                if (CAN_MANAGE) {
                    acts = '<div class="btn-group btn-group-sm">' +
                        (v.is_selected != 1 ? '<button type="button" class="btn btn-outline-success btn-select-vendor" data-id="' + v.id + '" title="Select winner"><i class="ri-award-line"></i></button>' : '') +
                        '<button type="button" class="btn btn-outline-danger btn-remove-vendor" data-id="' + v.id + '" title="Remove"><i class="ri-delete-bin-line"></i></button>' +
                        '</div>';
                }
                return '<tr' + (v.is_selected == 1 ? ' class="table-success"' : '') + '>' +
                    '<td><strong>' + H.escapeHtml(v.vendor_name) + '</strong></td>' +
                    '<td><small>' + H.escapeHtml(v.vendor_contact || '—') + '</small></td>' +
                    '<td><strong>' + CURRENCY + Number(v.quoted_amount).toLocaleString() + '</strong></td>' +
                    '<td>' + (v.delivery_days ? v.delivery_days + ' days' : '—') + '</td>' +
                    '<td>' + att + '</td>' +
                    '<td><small>' + H.escapeHtml((v.notes || '').substring(0, 60)) + '</small></td>' +
                    '<td>' + winner + '</td>' +
                    (CAN_MANAGE ? '<td>' + acts + '</td>' : '') + '</tr>';
            }).join('');
        });
    }

    function openVendors(id, title) {
        document.getElementById('vendorsModalTitle').innerHTML = '<i class="ri-store-2-line"></i> Vendor Quotes — ' + H.escapeHtml(title || '');
        loadVendors(id);
        if (!vendorsModal) vendorsModal = new bootstrap.Modal(document.getElementById('vendorsModal'));
        vendorsModal.show();
    }

    function openPublish(id) {
        document.getElementById('publishQuotationId').value = id;
        document.getElementById('publicLinkBox').classList.add('d-none');
        var q = quotesCache.find(function (x) { return String(x.id) === String(id); });
        var dl = q && q.required_by_date ? q.required_by_date.substring(0, 10) : '';
        if (!dl) {
            var d = new Date(); d.setDate(d.getDate() + 14);
            dl = d.toISOString().slice(0, 10);
        }
        document.getElementById('publicDeadline').value = dl;
        if (!publishModal) publishModal = new bootstrap.Modal(document.getElementById('publishModal'));
        publishModal.show();
    }

    function copyLink(url) {
        navigator.clipboard.writeText(url).then(function () {
            H.success('Link copied to clipboard');
        }).catch(function () {
            prompt('Copy this link:', url);
        });
    }

    if (CAN_MANAGE) {
        document.getElementById('btnNewQuotation').addEventListener('click', function () { openForm(null); });
        document.getElementById('btnAddLineItem').addEventListener('click', function () { addLineItemRow(); });
        document.getElementById('lineItemsBody').addEventListener('input', function (e) {
            if (e.target.matches('.item-qty, .item-price')) {
                recalcLineRow(e.target.closest('.line-item-row'));
            }
        });
        document.getElementById('lineItemsBody').addEventListener('click', function (e) {
            if (e.target.closest('.btn-remove-item')) {
                e.target.closest('.line-item-row').remove();
                recalcGrandTotal();
                toggleLineItemsEmpty();
            }
        });
        document.getElementById('saveQuotationBtn').addEventListener('click', function () {
            var title = document.getElementById('quoteTitle').value.trim();
            var items = collectLineItems();
            if (!title) { H.error('Title is required'); return; }
            if (!items.length) { H.error('Add at least one line item'); return; }
            H.post('ajax/hr/save-quotation.php', {
                id: document.getElementById('quotationId').value,
                title: title,
                description: document.getElementById('quoteDescription').value,
                required_by_date: document.getElementById('requiredBy').value,
                total_estimated: document.getElementById('totalEst').value,
                branch_id: document.getElementById('quoteBranch').value,
                status: document.getElementById('quoteStatus').value,
                items: items
            }).then(function (r) {
                if (r.success) {
                    bootstrap.Modal.getInstance(document.getElementById('quotationModal')).hide();
                    H.success(r.message, load);
                } else H.error(r.message);
            });
        });
        document.getElementById('btnConfirmPublish').addEventListener('click', function () {
            var id = document.getElementById('publishQuotationId').value;
            H.post('ajax/hr/save-quotation.php', {
                id: id,
                action: 'publish_public',
                public_deadline: document.getElementById('publicDeadline').value
            }).then(function (r) {
                if (r.success) {
                    var url = (r.data && r.data.public_url) || '';
                    if (url) {
                        document.getElementById('publicLink').value = url;
                        document.getElementById('publicLinkBox').classList.remove('d-none');
                    }
                    H.success(r.message, load);
                } else H.error(r.message);
            });
        });
        document.getElementById('btnCopyLink').addEventListener('click', function () {
            copyLink(document.getElementById('publicLink').value);
        });
        document.getElementById('btnAddVendor').addEventListener('click', function () {
            H.post('ajax/hr/save-quotation-vendor.php', {
                action: 'add',
                quotation_id: activeQuotationId,
                vendor_name: document.getElementById('manualVendorName').value,
                vendor_contact: document.getElementById('manualVendorContact').value,
                quoted_amount: document.getElementById('manualAmount').value,
                delivery_days: document.getElementById('manualDelivery').value
            }).then(function (r) {
                if (r.success) {
                    document.getElementById('manualVendorName').value = '';
                    document.getElementById('manualVendorContact').value = '';
                    document.getElementById('manualAmount').value = '';
                    document.getElementById('manualDelivery').value = '';
                    H.success(r.message, function () { loadVendors(activeQuotationId); load(); });
                } else H.error(r.message);
            });
        });
    }

    document.getElementById('btnFilter').addEventListener('click', load);
    document.getElementById('filterYear').addEventListener('change', load);

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-id], [data-token], [data-status]');
        if (!t) return;

        if (t.classList.contains('btn-view')) { viewQuotation(t.dataset.id); return; }
        if (t.classList.contains('btn-edit')) {
            var q = quotesCache.find(function (x) { return String(x.id) === String(t.dataset.id); });
            if (q) openForm(q);
            return;
        }
        if (t.classList.contains('btn-vendors')) {
            openVendors(t.dataset.id, t.dataset.title || '');
            return;
        }
        if (t.classList.contains('btn-publish')) {
            e.preventDefault();
            openPublish(t.dataset.id);
            return;
        }
        if (t.classList.contains('btn-unpublish')) {
            e.preventDefault();
            H.post('ajax/hr/save-quotation.php', { id: t.dataset.id, action: 'unpublish_public' })
                .then(function (r) { r.success ? H.success(r.message, load) : H.error(r.message); });
            return;
        }
        if (t.classList.contains('btn-copy-public')) {
            e.preventDefault();
            copyLink(APP_URL + 'quotation-portal.php?t=' + t.dataset.token);
            return;
        }
        if (t.classList.contains('quote-status')) {
            e.preventDefault();
            H.post('ajax/hr/save-quotation.php', { id: t.dataset.id, status: t.dataset.status })
                .then(function (r) { r.success ? H.success(r.message, load) : H.error(r.message); });
            return;
        }
        if (t.classList.contains('btn-select-vendor')) {
            H.post('ajax/hr/save-quotation-vendor.php', {
                action: 'select',
                quotation_id: activeQuotationId,
                vendor_id: t.dataset.id
            }).then(function (r) {
                r.success ? H.success(r.message, function () { loadVendors(activeQuotationId); load(); }) : H.error(r.message);
            });
            return;
        }
        if (t.classList.contains('btn-remove-vendor')) {
            if (!confirm('Remove this vendor quote?')) return;
            H.post('ajax/hr/save-quotation-vendor.php', { action: 'remove', vendor_id: t.dataset.id })
                .then(function (r) {
                    r.success ? H.success(r.message, function () { loadVendors(activeQuotationId); load(); }) : H.error(r.message);
                });
        }
    });

    load();
})();
</script>
