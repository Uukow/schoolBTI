<?php
if (!defined('ABSPATH')) exit('Direct access forbidden.');

class PayrollService
{
    public static function getStaffStructure($staffId, $asOfDate = null)
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');
        $sql = "SELECT * FROM payroll_structures WHERE staff_id = ? AND effective_from <= ?
                ORDER BY effective_from DESC LIMIT 1";
        return fetchOne(executeQuery($sql, 'is', [$staffId, $asOfDate]));
    }

    public static function calculateNetFromStructure($structure)
    {
        if (!$structure) return ['basic' => 0, 'allowances' => 0, 'deductions' => 0, 'net' => 0];
        $basic = (float) $structure['basic_salary'];
        $allowances = (float) ($structure['house_allowance'] + $structure['transport_allowance']
            + $structure['medical_allowance'] + $structure['other_allowances']);
        $deductions = (float) ($structure['tax_deduction'] + $structure['other_deductions']);
        return [
            'basic' => $basic,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net' => $basic + $allowances - $deductions,
        ];
    }

    public static function getAdvanceRecovery($staffId)
    {
        $sql = "SELECT COALESCE(SUM(monthly_recovery), 0) as total FROM hr_salary_advances
                WHERE staff_id = ? AND status = 'Disbursed'";
        $row = fetchOne(executeQuery($sql, 'i', [$staffId]));
        return (float) ($row['total'] ?? 0);
    }

    public static function getPendingCharges($staffId, $month)
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM hr_other_charges
                WHERE staff_id = ? AND status = 'Pending' AND DATE_FORMAT(charge_month, '%Y-%m') = ?";
        $row = fetchOne(executeQuery($sql, 'is', [$staffId, $month]));
        return (float) ($row['total'] ?? 0);
    }

    public static function createPayrollRun($paymentMonth, $branchId, $processedBy, $remarks = '')
    {
        $runNo = HrNumberService::next('PR-', 'hr_payroll_runs', 'run_no');
        $monthDate = $paymentMonth . '-01';

        $staffSql = "SELECT s.id FROM staff s WHERE s.status = 'Active'";
        $params = [];
        $types = '';
        if ($branchId) {
            $staffSql .= " AND s.branch_id = ?";
            $params[] = $branchId;
            $types .= 'i';
        }
        $staffList = fetchAll(executeQuery($staffSql, $types, $params));

        executeQuery(
            "INSERT INTO hr_payroll_runs (run_no, payment_month, branch_id, status, remarks, processed_by)
             VALUES (?, ?, ?, 'Draft', ?, ?)",
            'ssisi',
            [$runNo, $monthDate, $branchId, $remarks, $processedBy]
        );
        $runId = getLastInsertId();

        $totalAmount = 0;
        $count = 0;

        foreach ($staffList as $staff) {
            $staffId = $staff['id'];
            $exists = fetchOne(executeQuery(
                "SELECT id FROM salary_payments WHERE staff_id = ? AND DATE_FORMAT(payment_month, '%Y-%m') = ?",
                'is', [$staffId, $paymentMonth]
            ));
            if ($exists) continue;

            $structure = self::getStaffStructure($staffId, $monthDate);
            if (!$structure) continue;

            $calc = self::calculateNetFromStructure($structure);
            $advanceRecovery = self::getAdvanceRecovery($staffId);
            $otherCharges = self::getPendingCharges($staffId, $paymentMonth);
            $totalDeductions = $calc['deductions'] + $advanceRecovery + $otherCharges;
            $net = $calc['basic'] + $calc['allowances'] - $totalDeductions;

            $breakdown = json_encode([
                'basic' => $calc['basic'],
                'allowances' => $calc['allowances'],
                'tax' => $structure['tax_deduction'],
                'other_deductions' => $structure['other_deductions'],
                'advance_recovery' => $advanceRecovery,
                'other_charges' => $otherCharges,
            ]);

            executeQuery(
                "INSERT INTO salary_payments (payroll_run_id, staff_id, payment_month, basic_salary, allowances,
                 deductions, net_salary, payment_status, component_breakdown, remarks, processed_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'Draft', ?, ?, ?)",
                'iisddddssi',
                [$runId, $staffId, $monthDate, $calc['basic'], $calc['allowances'], $totalDeductions, $net,
                 $breakdown, $remarks, $processedBy]
            );
            $totalAmount += $net;
            $count++;
        }

        executeQuery(
            "UPDATE hr_payroll_runs SET total_staff = ?, total_amount = ? WHERE id = ?",
            'idi', [$count, $totalAmount, $runId]
        );

        return ['run_id' => $runId, 'run_no' => $runNo, 'staff_count' => $count, 'total_amount' => $totalAmount];
    }

    public static function approvePayrollRun($runId, $userId)
    {
        executeQuery(
            "UPDATE hr_payroll_runs SET status = 'Approved', approved_by = ?, approved_at = NOW() WHERE id = ?",
            'ii', [$userId, $runId]
        );
        executeQuery(
            "UPDATE salary_payments SET payment_status = 'Approved', approved_by = ?, approved_at = NOW()
             WHERE payroll_run_id = ?",
            'ii', [$userId, $runId]
        );
        return true;
    }

    public static function bankExportCsv($runId)
    {
        $sql = "SELECT sp.net_salary, s.first_name, s.last_name, s.bank_account_no, s.bank_name, s.staff_id
                FROM salary_payments sp
                INNER JOIN staff s ON sp.staff_id = s.id
                WHERE sp.payroll_run_id = ? AND sp.payment_status IN ('Approved','Paid')";
        return fetchAll(executeQuery($sql, 'i', [$runId]));
    }
}
