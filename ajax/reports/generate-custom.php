<?php
/**
 * AJAX: Generate Custom Report
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$dataSource = $_POST['data_source'] ?? '';
$reportName = sanitize($_POST['report_name'] ?? '');
$selectedFields = json_decode($_POST['selected_fields'] ?? '[]', true);
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
$groupBy = $_POST['group_by'] ?? '';
$orderBy = $_POST['order_by'] ?? '';

if (empty($dataSource) || empty($selectedFields)) {
    jsonResponse(false, 'Data source and fields are required');
}

// Build query based on data source
$sql = '';
$params = [];
$types = '';

switch ($dataSource) {
    case 'students':
        $fields = implode(', ', array_map(function($f) {
            return "s.$f";
        }, $selectedFields));
        $sql = "SELECT $fields, c.class_name 
                FROM students s
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE 1=1";
        
        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND s.admission_date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
            $types = 'ss';
        }
        
        if (!empty($groupBy) && in_array($groupBy, $selectedFields)) {
            $sql .= " GROUP BY s.$groupBy";
        }
        
        if (!empty($orderBy) && in_array($orderBy, $selectedFields)) {
            $sql .= " ORDER BY s.$orderBy";
        } else {
            $sql .= " ORDER BY s.first_name";
        }
        break;
        
    case 'staff':
        $fields = implode(', ', array_map(function($f) {
            return "s.$f";
        }, $selectedFields));
        $sql = "SELECT $fields 
                FROM staff s
                WHERE 1=1";
        
        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND s.joining_date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
            $types = 'ss';
        }
        
        if (!empty($groupBy) && in_array($groupBy, $selectedFields)) {
            $sql .= " GROUP BY s.$groupBy";
        }
        
        if (!empty($orderBy) && in_array($orderBy, $selectedFields)) {
            $sql .= " ORDER BY s.$orderBy";
        } else {
            $sql .= " ORDER BY s.first_name";
        }
        break;
        
    case 'fees':
        $fields = implode(', ', array_map(function($f) {
            return "fp.$f";
        }, $selectedFields));
        $sql = "SELECT $fields, s.first_name, s.last_name
                FROM fee_payments fp
                LEFT JOIN fee_invoices fi ON fp.invoice_id = fi.id
                LEFT JOIN students s ON fi.student_id = s.id
                WHERE 1=1";
        
        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND fp.payment_date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
            $types = 'ss';
        }
        
        if (!empty($groupBy) && in_array($groupBy, $selectedFields)) {
            $sql .= " GROUP BY fp.$groupBy";
        }
        
        if (!empty($orderBy) && in_array($orderBy, $selectedFields)) {
            $sql .= " ORDER BY fp.$orderBy";
        } else {
            $sql .= " ORDER BY fp.payment_date DESC";
        }
        break;
        
    case 'attendance':
        $fields = implode(', ', array_map(function($f) {
            return "sa.$f";
        }, $selectedFields));
        $sql = "SELECT $fields, s.first_name, s.last_name, c.class_name
                FROM student_attendance sa
                LEFT JOIN students s ON sa.student_id = s.id
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE 1=1";
        
        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND sa.attendance_date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
            $types = 'ss';
        }
        
        if (!empty($groupBy) && in_array($groupBy, $selectedFields)) {
            $sql .= " GROUP BY sa.$groupBy";
        }
        
        if (!empty($orderBy) && in_array($orderBy, $selectedFields)) {
            $sql .= " ORDER BY sa.$orderBy";
        } else {
            $sql .= " ORDER BY sa.attendance_date DESC";
        }
        break;
        
    case 'exams':
        $fields = implode(', ', array_map(function($f) {
            return "sm.$f";
        }, $selectedFields));
        $sql = "SELECT $fields, s.first_name, s.last_name, e.exam_name, sub.subject_name
                FROM student_marks sm
                LEFT JOIN students s ON sm.student_id = s.id
                LEFT JOIN exam_schedule es ON sm.exam_schedule_id = es.id
                LEFT JOIN exams e ON es.exam_id = e.id
                LEFT JOIN subjects sub ON es.subject_id = sub.id
                WHERE 1=1";
        
        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND e.start_date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
            $types = 'ss';
        }
        
        if (!empty($groupBy) && in_array($groupBy, $selectedFields)) {
            $sql .= " GROUP BY sm.$groupBy";
        }
        
        if (!empty($orderBy) && in_array($orderBy, $selectedFields)) {
            $sql .= " ORDER BY sm.$orderBy";
        } else {
            $sql .= " ORDER BY e.start_date DESC";
        }
        break;
        
    default:
        jsonResponse(false, 'Invalid data source');
}

try {
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $data = fetchAll($stmt);
    
    if (empty($data)) {
        jsonResponse(false, 'No data found for the selected criteria');
    }
    
    // Generate HTML table
    $html = '<div class="table-responsive"><table class="table table-bordered table-hover"><thead class="table-light"><tr>';
    
    // Header row
    foreach ($selectedFields as $field) {
        $header = ucwords(str_replace('_', ' ', $field));
        $html .= "<th>$header</th>";
    }
    
    // Add additional headers if needed
    if ($dataSource == 'students' && !in_array('class_name', $selectedFields)) {
        $html .= '<th>Class</th>';
    }
    if ($dataSource == 'fees' || $dataSource == 'attendance' || $dataSource == 'exams') {
        $html .= '<th>Student Name</th>';
    }
    
    $html .= '</tr></thead><tbody>';
    
    // Data rows
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($selectedFields as $field) {
            $value = $row[$field] ?? '-';
            if (is_numeric($value) && strpos($field, 'date') === false) {
                $html .= "<td>" . number_format($value, 2) . "</td>";
            } else {
                $html .= "<td>" . htmlspecialchars($value) . "</td>";
            }
        }
        
        // Additional columns
        if ($dataSource == 'students' && isset($row['class_name'])) {
            $html .= "<td>" . htmlspecialchars($row['class_name']) . "</td>";
        }
        if (($dataSource == 'fees' || $dataSource == 'attendance' || $dataSource == 'exams') && isset($row['first_name'])) {
            $html .= "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        }
        
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table></div>';
    $html .= '<div class="mt-3"><strong>Total Records:</strong> ' . count($data) . '</div>';
    
    logActivity(getCurrentUser()['id'], 'Generate Custom Report', 'Reports', "Generated report: $reportName");
    jsonResponse(true, 'Report generated successfully', ['html' => $html]);
    
} catch (Exception $e) {
    jsonResponse(false, 'Error generating report: ' . $e->getMessage());
}

