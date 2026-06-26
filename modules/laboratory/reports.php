<?php
/**
 * LAB Management - Reports & Analytics
 */

require_once '../../config/config.php';
requireLabRoles(labStaffRoles());

$pageTitle = 'Laboratory Reports';
$currentUser = getCurrentUser();

$reportType = $_GET['report']   ?? 'inventory';
$dateFrom   = $_GET['date_from'] ?? date('Y-m-01');
$dateTo     = $_GET['date_to']   ?? date('Y-m-d');

$data = [];
$columns = [];
$reportTitle = '';

switch ($reportType) {
    case 'inventory':
        $reportTitle = 'Inventory Report';
        $columns = ['Code', 'Title', 'Category', 'Section', 'Qty', 'Available', 'Status', 'Unit Cost', 'Total Cost'];
        $sql = "SELECT i.item_code, i.item_title, c.category_name, s.section_name,
                       i.quantity, i.available_qty, i.status, i.unit_cost, i.total_cost
                FROM lab_inventory_items i
                LEFT JOIN lab_inventory_categories c ON i.category_id = c.id
                LEFT JOIN lab_sections s ON i.section_id = s.id
                WHERE 1=1" . labBranchWhere('i', null, false) . " ORDER BY i.item_title";
        $data = fetchAll(executeQuery($sql));
        break;

    case 'requests':
        $reportTitle = 'Material Requests Report';
        $columns = ['Request #', 'Requester', 'Type', 'Date', 'Required By', 'Status', 'Approved By'];
        $sql = "SELECT r.request_number, u.username, r.requester_type, r.request_date,
                       r.required_date, r.status, a.username as approver
                FROM lab_material_requests r
                LEFT JOIN users u ON r.requester_id = u.id
                LEFT JOIN users a ON r.approved_by = a.id
                WHERE r.request_date BETWEEN ? AND ?" . labBranchWhere('r', null, false) . " ORDER BY r.request_date DESC";
        $data = fetchAll(executeQuery($sql, 'ss', [$dateFrom, $dateTo]));
        break;

    case 'maintenance':
        $reportTitle = 'Maintenance Report';
        $columns = ['Ref #', 'Item', 'Type', 'Severity', 'Technician', 'Cost', 'Scheduled', 'Completed', 'Status'];
        $sql = "SELECT m.maintenance_number, i.item_title, m.maintenance_type, m.severity,
                       t.username as technician, m.cost, m.scheduled_date, m.completed_date, m.status
                FROM lab_maintenance_records m
                LEFT JOIN lab_inventory_items i ON m.item_id = i.id
                LEFT JOIN users t ON m.assigned_technician = t.id
                WHERE 1=1" . labBranchWhere('m', null, false) . " ORDER BY m.created_at DESC";
        $data = fetchAll(executeQuery($sql));
        break;

    case 'safety':
        $reportTitle = 'Safety Incidents Report';
        $columns = ['Incident #', 'Type', 'Date', 'Section', 'Severity', 'Injured', 'Status'];
        $sql = "SELECT i.incident_number, i.incident_type, i.incident_date, s.section_name,
                       i.severity, i.injured_person, i.status
                FROM lab_safety_incidents i
                LEFT JOIN lab_sections s ON i.section_id = s.id
                WHERE i.incident_date BETWEEN ? AND ?" . labBranchWhere('i', null, false) . " ORDER BY i.incident_date DESC";
        $data = fetchAll(executeQuery($sql, 'ss', [$dateFrom, $dateTo]));
        break;

    case 'visitors':
        $reportTitle = 'Visitor Report';
        $columns = ['Name', 'Organization', 'Purpose', 'Section', 'Entry', 'Exit', 'Status'];
        $sql = "SELECT v.visitor_name, v.organization, v.purpose, s.section_name,
                       v.entry_time, v.exit_time, v.status
                FROM lab_visitors v
                LEFT JOIN lab_sections s ON v.section_id = s.id
                WHERE DATE(COALESCE(v.entry_time, v.created_at)) BETWEEN ? AND ?" . labBranchWhere('v', null, false) . " ORDER BY v.created_at DESC";
        $data = fetchAll(executeQuery($sql, 'ss', [$dateFrom, $dateTo]));
        break;

    case 'procurement':
        $reportTitle = 'Procurement Report';
        $columns = ['PO #', 'Supplier', 'Description', 'Qty', 'Total Price', 'Purchase Date', 'Status'];
        $sql = "SELECT p.purchase_number, p.supplier_name, p.item_description,
                       p.quantity, p.total_price, p.purchase_date, p.status
                FROM lab_procurement p
                WHERE p.purchase_date BETWEEN ? AND ?" . labBranchWhere('p', null, false) . " ORDER BY p.purchase_date DESC";
        $data = fetchAll(executeQuery($sql, 'ss', [$dateFrom, $dateTo]));
        break;

    case 'bookings':
        $reportTitle = 'Lab Bookings Report';
        $columns = ['Booking #', 'Section', 'Requester', 'Date', 'Start', 'End', 'Attendees', 'Status'];
        $sql = "SELECT b.booking_number, s.section_name, COALESCE(b.requester_name, u.username) as requester,
                       b.booking_date, b.start_time, b.end_time, b.attendees_count, b.status
                FROM lab_bookings b
                LEFT JOIN lab_sections s ON b.section_id = s.id
                LEFT JOIN users u ON b.requester_id = u.id
                WHERE b.booking_date BETWEEN ? AND ?" . labBranchWhere('b', null, false) . " ORDER BY b.booking_date DESC";
        $data = fetchAll(executeQuery($sql, 'ss', [$dateFrom, $dateTo]));
        break;

    case 'issues':
        $reportTitle = 'Issues Report';
        $columns = ['Issue #', 'Title', 'Type', 'Priority', 'Section', 'Reporter', 'Status', 'Date'];
        $sql = "SELECT i.issue_number, i.title, t.type_name, i.priority, s.section_name,
                       r.username as reporter, i.status, DATE(i.created_at) as report_date
                FROM lab_issues i
                LEFT JOIN lab_issue_types t ON i.issue_type_id = t.id
                LEFT JOIN lab_sections s ON i.section_id = s.id
                LEFT JOIN users r ON i.reported_by = r.id
                WHERE DATE(i.created_at) BETWEEN ? AND ?" . labBranchWhere('i', null, false) . " ORDER BY i.created_at DESC";
        $data = fetchAll(executeQuery($sql, 'ss', [$dateFrom, $dateTo]));
        break;

    case 'experiments':
        $reportTitle = 'Experiments & Sessions Report';
        $columns = ['Date', 'Experiment', 'Section', 'Instructor', 'Students', 'Start', 'End', 'Status'];
        $sql = "SELECT ss.session_date, e.experiment_title, s.section_name, u.username as instructor,
                       ss.student_count, ss.start_time, ss.end_time, ss.status
                FROM lab_experiment_sessions ss
                LEFT JOIN lab_experiments e ON ss.experiment_id = e.id
                LEFT JOIN lab_sections s ON ss.section_id = s.id
                LEFT JOIN users u ON ss.instructor_id = u.id
                WHERE ss.session_date BETWEEN ? AND ?" . labBranchWhere('ss', null, false) . " ORDER BY ss.session_date DESC";
        $data = fetchAll(executeQuery($sql, 'ss', [$dateFrom, $dateTo]));
        break;
}

$settings   = fetchOne(executeQuery("SELECT school_name, school_address, school_phone, school_email, system_logo FROM system_settings LIMIT 1")) ?: [];
$schoolName = $settings['school_name'] ?? APP_NAME;
$schoolAddr = trim($settings['school_address'] ?? '');
$schoolPhone = trim($settings['school_phone'] ?? '');
$schoolEmail = trim($settings['school_email'] ?? '');
$logoUrl    = (!empty($settings['system_logo']) && file_exists(ABSPATH . $settings['system_logo']))
    ? APP_URL . $settings['system_logo'] : '';
$branchName = hasRole(['Super Admin']) ? 'All Branches' : ($currentUser['branch_name'] ?? 'N/A');
$generatedAt = date('d M Y, h:i A');
$printedBy  = $currentUser['username'] ?? 'System';
$recordCount = count($data);
$dateRangeLabel = ($reportType === 'inventory' || $reportType === 'maintenance')
    ? 'All dates'
    : formatDate($dateFrom) . ' — ' . formatDate($dateTo);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row no-print">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <a href="dashboard.php" class="btn btn-secondary me-1"><i class="ri-arrow-left-line"></i> Dashboard</a>
                        </div>
                        <h4 class="page-title">Laboratory Reports &amp; Analytics</h4>
                    </div>
                </div>
            </div>

            <!-- Report Selector -->
            <div class="card no-print">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Report Type</label>
                            <select name="report" class="form-select">
                                <?php
                                $rTypes = [
                                    'inventory'   => 'Inventory Report',
                                    'requests'    => 'Material Requests Report',
                                    'experiments' => 'Experiments & Sessions Report',
                                    'maintenance' => 'Maintenance Report',
                                    'safety'      => 'Safety Incidents Report',
                                    'visitors'    => 'Visitor Report',
                                    'procurement' => 'Procurement Report',
                                    'bookings'    => 'Lab Bookings Report',
                                    'issues'      => 'Issues Report',
                                ];
                                foreach ($rTypes as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo $reportType === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="ri-bar-chart-box-line"></i> Generate</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Output -->
            <div class="card lab-report-card" id="labReportCard">
                <div class="card-body p-0">
                    <div class="no-print d-flex justify-content-between align-items-center px-3 pt-3 pb-2 border-bottom">
                        <h4 class="header-title mb-0"><?php echo htmlspecialchars($reportTitle); ?>
                            <span class="badge bg-secondary ms-1"><?php echo $recordCount; ?> records</span>
                        </h4>
                        <div class="report-actions">
                            <button type="button" onclick="printReport()" class="btn btn-outline-secondary btn-sm me-1"><i class="ri-printer-line"></i> Print</button>
                            <button type="button" onclick="exportToExcel()" class="btn btn-outline-success btn-sm me-1"><i class="ri-file-excel-line"></i> Excel</button>
                            <button type="button" onclick="printReport()" class="btn btn-outline-danger btn-sm"><i class="ri-file-pdf-line"></i> PDF</button>
                        </div>
                    </div>

                    <div class="lab-report-document" id="labReportDocument">
                        <!-- Print Header -->
                        <header class="lab-report-header">
                            <div class="lab-report-header-top">
                                <?php if ($logoUrl): ?>
                                <div class="lab-report-logo">
                                    <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logo">
                                </div>
                                <?php endif; ?>
                                <div class="lab-report-org">
                                    <h1 class="lab-report-school"><?php echo htmlspecialchars($schoolName); ?></h1>
                                    <p class="lab-report-dept">Laboratory Management System — Official Report</p>
                                    <?php if ($schoolAddr || $schoolPhone || $schoolEmail): ?>
                                    <p class="lab-report-contact">
                                        <?php
                                        $contact = array_filter([$schoolAddr, $schoolPhone ? 'Tel: ' . $schoolPhone : '', $schoolEmail]);
                                        echo htmlspecialchars(implode(' | ', $contact));
                                        ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <div class="lab-report-ref">
                                    <div class="lab-report-ref-box">
                                        <span class="label">Report Ref</span>
                                        <strong>LAB-RPT-<?php echo strtoupper(substr($reportType, 0, 3)); ?>-<?php echo date('Ymd'); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="lab-report-title-bar">
                                <h2><?php echo htmlspecialchars($reportTitle); ?></h2>
                            </div>
                        </header>

                        <!-- Meta strip -->
                        <div class="lab-report-meta">
                            <div class="lab-report-meta-item">
                                <span class="meta-label">Branch</span>
                                <span class="meta-value"><?php echo htmlspecialchars($branchName); ?></span>
                            </div>
                            <div class="lab-report-meta-item">
                                <span class="meta-label">Period</span>
                                <span class="meta-value"><?php echo htmlspecialchars($dateRangeLabel); ?></span>
                            </div>
                            <div class="lab-report-meta-item">
                                <span class="meta-label">Total Records</span>
                                <span class="meta-value"><?php echo number_format($recordCount); ?></span>
                            </div>
                            <div class="lab-report-meta-item">
                                <span class="meta-label">Generated</span>
                                <span class="meta-value"><?php echo htmlspecialchars($generatedAt); ?></span>
                            </div>
                            <div class="lab-report-meta-item">
                                <span class="meta-label">Prepared By</span>
                                <span class="meta-value"><?php echo htmlspecialchars($printedBy); ?></span>
                            </div>
                        </div>

                        <!-- Data table -->
                        <div class="lab-report-table-wrap">
                            <table class="lab-report-table" id="reportTable">
                                <thead>
                                    <tr>
                                        <th class="col-num">#</th>
                                        <?php foreach ($columns as $col): ?>
                                        <th><?php echo htmlspecialchars($col); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $i => $row): ?>
                                    <tr>
                                        <td class="col-num"><?php echo $i + 1; ?></td>
                                        <?php foreach (array_values($row) as $val): ?>
                                        <td><?php echo htmlspecialchars($val ?? '—'); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($data)): ?>
                                    <tr>
                                        <td colspan="<?php echo count($columns) + 1; ?>" class="lab-report-empty">
                                            No data found for the selected report and date range.
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($data)): ?>
                                <tfoot>
                                    <tr class="lab-report-summary">
                                        <td colspan="<?php echo count($columns) + 1; ?>">
                                            <strong>Summary:</strong> <?php echo number_format($recordCount); ?> record<?php echo $recordCount !== 1 ? 's' : ''; ?> listed
                                            &nbsp;|&nbsp; Period: <?php echo htmlspecialchars($dateRangeLabel); ?>
                                        </td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- Print Footer -->
                        <footer class="lab-report-footer">
                            <div class="lab-report-signatures">
                                <div class="sig-block">
                                    <div class="sig-line"></div>
                                    <span>Lab Director / Safety Officer</span>
                                </div>
                                <div class="sig-block">
                                    <div class="sig-line"></div>
                                    <span>Authorized Signature</span>
                                </div>
                                <div class="sig-block">
                                    <div class="sig-line"></div>
                                    <span>Date</span>
                                </div>
                            </div>
                            <div class="lab-report-footer-note">
                                <span>This is a computer-generated laboratory report from <?php echo htmlspecialchars(APP_NAME); ?>.</span>
                                <span class="lab-report-page-num"></span>
                            </div>
                        </footer>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
function printReport() {
    window.print();
}

function exportToExcel() {
    if (typeof exportTableToExcel === 'function') {
        exportTableToExcel('reportTable', '<?php echo addslashes($reportTitle); ?>');
    } else {
        var table = document.getElementById('reportTable');
        var title = '<?php echo addslashes($reportTitle); ?>';
        var html = '<html><head><meta charset="utf-8"><title>' + title + '</title></head><body>';
        html += '<h2>' + title + '</h2>';
        html += '<p>Period: <?php echo addslashes($dateRangeLabel); ?> | Records: <?php echo $recordCount; ?></p>';
        html += table.outerHTML + '</body></html>';
        var blob = new Blob([html], { type: 'application/vnd.ms-excel' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = title.replace(/[^a-z0-9]/gi, '_') + '.xls';
        a.click();
    }
}

function exportToPDF() {
    printReport();
}
</script>

<style>
/* ── Screen preview ── */
.lab-report-card {
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
}
.lab-report-document {
    background: #fff;
    padding: 32px 36px 28px;
    max-width: 100%;
}
.lab-report-header-top {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding-bottom: 18px;
    border-bottom: 2px solid #1e3a5f;
}
.lab-report-logo img {
    max-height: 72px;
    max-width: 120px;
    object-fit: contain;
}
.lab-report-org { flex: 1; }
.lab-report-school {
    font-size: 1.35rem;
    font-weight: 700;
    color: #1e3a5f;
    margin: 0 0 4px;
    letter-spacing: -0.02em;
}
.lab-report-dept {
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin: 0 0 6px;
}
.lab-report-contact {
    font-size: 0.75rem;
    color: #64748b;
    margin: 0;
    line-height: 1.4;
}
.lab-report-ref-box {
    text-align: right;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 10px 14px;
    background: #f8fafc;
    min-width: 160px;
}
.lab-report-ref-box .label {
    display: block;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    margin-bottom: 4px;
}
.lab-report-ref-box strong {
    font-size: 0.8rem;
    color: #1e293b;
    word-break: break-all;
}
.lab-report-title-bar {
    background: linear-gradient(90deg, #1e3a5f 0%, #2563eb 100%);
    margin: 16px 0 0;
    padding: 12px 18px;
    border-radius: 4px;
}
.lab-report-title-bar h2 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    color: #fff;
    letter-spacing: 0.02em;
}
.lab-report-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0;
    margin: 18px 0 20px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    background: #f8fafc;
}
.lab-report-meta-item {
    flex: 1 1 140px;
    padding: 10px 14px;
    border-right: 1px solid #e2e8f0;
}
.lab-report-meta-item:last-child { border-right: none; }
.meta-label {
    display: block;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #94a3b8;
    margin-bottom: 3px;
}
.meta-value {
    font-size: 0.82rem;
    font-weight: 600;
    color: #1e293b;
}
.lab-report-table-wrap { overflow-x: auto; }
.lab-report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
    color: #1e293b;
}
.lab-report-table thead th {
    background: #1e3a5f;
    color: #fff;
    font-weight: 600;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 10px 12px;
    border: 1px solid #1e3a5f;
    white-space: nowrap;
}
.lab-report-table tbody td {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    vertical-align: top;
    line-height: 1.35;
}
.lab-report-table tbody tr:nth-child(even) { background: #f8fafc; }
.lab-report-table .col-num {
    width: 36px;
    text-align: center;
    color: #64748b;
    font-weight: 600;
}
.lab-report-table tfoot .lab-report-summary td {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    padding: 10px 12px;
    font-size: 0.78rem;
    color: #1e40af;
}
.lab-report-empty {
    text-align: center;
    padding: 32px !important;
    color: #94a3b8;
    font-style: italic;
}
.lab-report-footer {
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}
.lab-report-signatures {
    display: flex;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 20px;
}
.sig-block {
    flex: 1;
    text-align: center;
    max-width: 200px;
}
.sig-line {
    border-bottom: 1px solid #334155;
    height: 40px;
    margin-bottom: 6px;
}
.sig-block span {
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 500;
}
.lab-report-footer-note {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.68rem;
    color: #94a3b8;
    border-top: 1px dashed #e2e8f0;
    padding-top: 10px;
}

/* ── Print ── */
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm 12mm 14mm 12mm;
    }

    body {
        background: #fff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .leftside-menu,
    .navbar-custom,
    .footer,
    .no-print,
    .page-title-box,
    .report-actions,
    .datatable-export_wrapper .dataTables_filter,
    .datatable-export_wrapper .dataTables_length,
    .datatable-export_wrapper .dataTables_paginate,
    .datatable-export_wrapper .dataTables_info {
        display: none !important;
    }

    .wrapper,
    .content-page,
    .content,
    .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .content-page { margin-left: 0 !important; }

    .lab-report-card {
        border: none !important;
        box-shadow: none !important;
    }

    .lab-report-document {
        padding: 0 !important;
    }

    .lab-report-header-top {
        border-bottom-color: #1e3a5f !important;
    }

    .lab-report-title-bar {
        background: #1e3a5f !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .lab-report-meta {
        background: #f8fafc !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .lab-report-table thead th {
        background: #1e3a5f !important;
        color: #fff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .lab-report-table {
        font-size: 8pt;
        page-break-inside: auto;
    }

    .lab-report-table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    .lab-report-table thead {
        display: table-header-group;
    }

    .lab-report-table tfoot {
        display: table-footer-group;
    }

    .lab-report-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #fff;
        padding-top: 8px;
    }

    .lab-report-table-wrap {
        margin-bottom: 90px;
    }

    .lab-report-signatures .sig-line {
        height: 28px;
    }
}
</style>
