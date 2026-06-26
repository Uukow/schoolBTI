<?php
/**
 * Custom Reports Builder
 * 
 * Build custom reports with flexible filters
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin']);

$pageTitle = 'Custom Reports';

// Get current user
$currentUser = getCurrentUser();

// Get available tables/entities
$availableReports = [
    'students' => [
        'name' => 'Students',
        'fields' => ['student_id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'email', 'phone', 'status', 'admission_date']
    ],
    'staff' => [
        'name' => 'Staff',
        'fields' => ['staff_id', 'first_name', 'last_name', 'designation', 'department', 'email', 'phone', 'status', 'joining_date']
    ],
    'fees' => [
        'name' => 'Fee Payments',
        'fields' => ['invoice_no', 'student_id', 'amount', 'payment_date', 'payment_method', 'status']
    ],
    'attendance' => [
        'name' => 'Attendance',
        'fields' => ['student_id', 'attendance_date', 'status', 'remarks']
    ],
    'exams' => [
        'name' => 'Exam Results',
        'fields' => ['student_id', 'exam_name', 'subject', 'marks_obtained', 'total_marks', 'grade']
    ]
];

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
                            <button onclick="window.print()" class="btn btn-secondary no-print">
                                <i class="ri-printer-line"></i> Print
                            </button>
                            <button onclick="exportToExcel()" class="btn btn-success ms-2 no-print">
                                <i class="ri-file-excel-line"></i> Export Excel
                            </button>
                        </div>
                        <h4 class="page-title">Custom Reports Builder</h4>
                    </div>
                </div>
            </div>

            <!-- Report Builder -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Report Configuration</h4>
                            
                            <form id="customReportForm">
                                <div class="mb-3">
                                    <label class="form-label required">Report Name</label>
                                    <input type="text" class="form-control" name="report_name" required placeholder="e.g., Monthly Student Report">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label required">Data Source</label>
                                    <select class="form-select" name="data_source" id="dataSource" required>
                                        <option value="">Select Data Source</option>
                                        <?php foreach ($availableReports as $key => $report): ?>
                                            <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($report['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Select Fields</label>
                                    <div id="fieldsContainer" class="border p-3" style="max-height: 300px; overflow-y: auto;">
                                        <p class="text-muted">Select a data source first</p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Date Range</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="date" class="form-control" name="start_date" placeholder="From">
                                        </div>
                                        <div class="col-6">
                                            <input type="date" class="form-control" name="end_date" placeholder="To">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Group By</label>
                                    <select class="form-select" name="group_by" id="groupBy">
                                        <option value="">None</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Order By</label>
                                    <select class="form-select" name="order_by" id="orderBy">
                                        <option value="">Default</option>
                                    </select>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-file-chart-line"></i> Generate Report
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Report Preview</h4>
                            
                            <div id="reportPreview">
                                <div class="alert alert-info text-center">
                                    <i class="ri-file-chart-line font-24"></i>
                                    <h5 class="mt-2">No Report Generated</h5>
                                    <p class="mb-0">Configure and generate a custom report using the form on the left.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
const availableReports = <?php echo json_encode($availableReports); ?>;

// Update fields when data source changes
$('#dataSource').on('change', function() {
    const source = $(this).val();
    const fieldsContainer = $('#fieldsContainer');
    const groupBy = $('#groupBy');
    const orderBy = $('#orderBy');
    
    if (source && availableReports[source]) {
        const fields = availableReports[source].fields;
        let html = '';
        
        fields.forEach(function(field) {
            html += `
                <div class="form-check">
                    <input class="form-check-input field-checkbox" type="checkbox" name="fields[]" value="${field}" id="field_${field}">
                    <label class="form-check-label" for="field_${field}">
                        ${field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </label>
                </div>
            `;
        });
        
        fieldsContainer.html(html);
        
        // Update group by and order by options
        let groupOptions = '<option value="">None</option>';
        let orderOptions = '<option value="">Default</option>';
        fields.forEach(function(field) {
            groupOptions += `<option value="${field}">${field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</option>`;
            orderOptions += `<option value="${field}">${field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</option>`;
        });
        groupBy.html(groupOptions);
        orderBy.html(orderOptions);
    } else {
        fieldsContainer.html('<p class="text-muted">Select a data source first</p>');
        groupBy.html('<option value="">None</option>');
        orderBy.html('<option value="">Default</option>');
    }
});

// Generate custom report
$('#customReportForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    const selectedFields = $('.field-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedFields.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'No Fields Selected',
            text: 'Please select at least one field to include in the report.'
        });
        return;
    }
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/reports/generate-custom.php',
        type: 'POST',
        data: formData + '&selected_fields=' + JSON.stringify(selectedFields),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#reportPreview').html(response.html);
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

function exportToExcel() {
    const table = document.querySelector('#reportPreview table');
    if (!table) {
        Swal.fire({
            icon: 'error',
            title: 'No Data',
            text: 'Please generate a report first.'
        });
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            row.push(cols[j].innerText);
        }
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'custom_report_' + new Date().getTime() + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

