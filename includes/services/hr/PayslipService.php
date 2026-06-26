<?php
if (!defined('ABSPATH')) exit;

class PayslipService
{
    public static function getPayslipData($paymentId, $branchId = null, $isSuperAdmin = true)
    {
        $sql = "SELECT sp.*, s.first_name, s.last_name, s.staff_id, s.designation, s.phone, s.email,
                s.bank_account_no, s.bank_name, b.branch_name, b.address as branch_address
                FROM salary_payments sp
                INNER JOIN staff s ON sp.staff_id = s.id
                LEFT JOIN branches b ON s.branch_id = b.id WHERE sp.id = ?";
        $params = [$paymentId];
        $types = 'i';
        if (!$isSuperAdmin && $branchId) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $branchId;
            $types .= 'i';
        }
        return fetchOne(executeQuery($sql, $types, $params));
    }

    public static function renderHtml($p)
    {
        $breakdown = [];
        if (!empty($p['component_breakdown'])) {
            $breakdown = json_decode($p['component_breakdown'], true) ?: [];
        }
        $month = date('F Y', strtotime($p['payment_month']));
        $html = '<html><head><style>
            body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#333}
            .header{text-align:center;border-bottom:2px solid #1a56db;padding-bottom:10px;margin-bottom:20px}
            .title{font-size:20px;color:#1a56db;font-weight:bold}
            table{width:100%;border-collapse:collapse;margin:15px 0}
            th,td{border:1px solid #ddd;padding:8px;text-align:left}
            th{background:#f0f4ff}
            .total{font-size:16px;font-weight:bold;color:#1a56db}
        </style></head><body>';
        $html .= '<div class="header"><div class="title">' . htmlspecialchars(APP_NAME) . '</div>';
        $html .= '<div>Salary Payslip — ' . htmlspecialchars($month) . '</div></div>';
        $html .= '<table><tr><th>Employee</th><td>' . htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) . '</td>';
        $html .= '<th>Staff ID</th><td>' . htmlspecialchars($p['staff_id']) . '</td></tr>';
        $html .= '<tr><th>Designation</th><td>' . htmlspecialchars($p['designation']) . '</td>';
        $html .= '<th>Branch</th><td>' . htmlspecialchars($p['branch_name'] ?? '') . '</td></tr></table>';
        $html .= '<table><tr><th>Description</th><th style="text-align:right">Amount (' . CURRENCY_SYMBOL . ')</th></tr>';
        $html .= '<tr><td>Basic Salary</td><td style="text-align:right">' . number_format($p['basic_salary'], 2) . '</td></tr>';
        $html .= '<tr><td>Allowances</td><td style="text-align:right">' . number_format($p['allowances'], 2) . '</td></tr>';
        if (!empty($breakdown['advance_recovery'])) {
            $html .= '<tr><td>Advance Recovery</td><td style="text-align:right">-' . number_format($breakdown['advance_recovery'], 2) . '</td></tr>';
        }
        $html .= '<tr><td>Deductions</td><td style="text-align:right">-' . number_format($p['deductions'], 2) . '</td></tr>';
        $html .= '<tr class="total"><td><strong>Net Salary</strong></td><td style="text-align:right"><strong>' . number_format($p['net_salary'], 2) . '</strong></td></tr>';
        $html .= '</table>';
        if (!empty($p['bank_account_no'])) {
            $html .= '<p><strong>Bank:</strong> ' . htmlspecialchars($p['bank_name'] ?? '') . ' — ' . htmlspecialchars($p['bank_account_no']) . '</p>';
        }
        $html .= '<p style="margin-top:30px;font-size:10px;color:#888">Generated on ' . date('d M Y H:i') . ' — ' . APP_NAME . '</p>';
        $html .= '</body></html>';
        return $html;
    }

    public static function downloadPdf($paymentId, $branchId = null, $isSuperAdmin = true)
    {
        $p = self::getPayslipData($paymentId, $branchId, $isSuperAdmin);
        if (!$p) return false;

        if (!class_exists('Mpdf\Mpdf')) {
            require_once ABSPATH . 'vendor/autoload.php';
        }
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->WriteHTML(self::renderHtml($p));
        $filename = 'Payslip_' . $p['staff_id'] . '_' . date('Y-m', strtotime($p['payment_month'])) . '.pdf';
        $mpdf->Output($filename, 'D');
        return true;
    }
}
