<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * HR Reports — data aggregation and professional PDF rendering.
 */
class HrReportService
{
    public static function generate(array $opts): array
    {
        $type = $opts['type'] ?? 'summary';
        $month = $opts['month'] ?? date('Y-m');
        $year = (int)($opts['year'] ?? (int)substr($month, 0, 4));
        $department = trim($opts['department'] ?? '');
        $status = trim($opts['status'] ?? '');
        $branchId = isset($opts['branch_id']) && $opts['branch_id'] !== '' ? (int)$opts['branch_id'] : null;
        $isSuperAdmin = !empty($opts['is_super_admin']);
        $userBranchId = $opts['user_branch_id'] ?? null;

        if (!$isSuperAdmin && $branchId === null && $userBranchId) {
            $branchId = (int)$userBranchId;
        }

        $handlers = [
            'summary' => 'reportSummary',
            'headcount' => 'reportHeadcount',
            'employee_master' => 'reportEmployeeMaster',
            'department' => 'reportDepartment',
            'payroll' => 'reportPayroll',
            'advances' => 'reportAdvances',
            'leave' => 'reportLeave',
            'leave_balance' => 'reportLeaveBalance',
            'attendance' => 'reportAttendance',
            'attendance_late' => 'reportAttendanceLate',
            'grievances' => 'reportGrievances',
            'performance' => 'reportPerformance',
            'ppdp' => 'reportPpdp',
            'recruitment' => 'reportRecruitment',
        ];

        if (!isset($handlers[$type])) {
            throw new InvalidArgumentException('Unknown report type');
        }

        $method = $handlers[$type];
        $report = self::$method($month, $year, $department, $status, $branchId, $isSuperAdmin);
        $report['type'] = $type;
        $report['filters'] = [
            'month' => $month,
            'year' => $year,
            'department' => $department,
            'status' => $status,
            'branch_id' => $branchId,
        ];
        $report['generated_at'] = date('Y-m-d H:i:s');

        return $report;
    }

    private static function branchClause(string $alias, ?int $branchId, bool $isSuperAdmin): array
    {
        if ($isSuperAdmin && !$branchId) {
            return ['', '', []];
        }
        if ($branchId) {
            return [" AND {$alias}.branch_id = ?", 'i', [$branchId]];
        }
        return ['', '', []];
    }

    private static function deptClause(string $alias, string $department): array
    {
        if ($department === '') {
            return ['', '', []];
        }
        return [" AND {$alias}.department = ?", 's', [$department]];
    }

    private static function tableExists(string $table): bool
    {
        global $conn;
        $row = fetchOne(executeQuery(
            "SELECT 1 AS ok FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
            's',
            [$table]
        ));
        return !empty($row);
    }

    private static function reportSummary(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);
        $extra = $bf . $df;
        $types = $bt . $dt;
        $params = array_merge($bp, $dp);

        $active = (int)(fetchOne(executeQuery(
            "SELECT COUNT(*) AS c FROM staff s WHERE s.status = 'Active' $extra",
            $types,
            $params
        ))['c'] ?? 0);

        $payrollMtd = (float)(fetchOne(executeQuery(
            "SELECT COALESCE(SUM(sp.net_salary), 0) AS t FROM salary_payments sp
             INNER JOIN staff s ON sp.staff_id = s.id
             WHERE DATE_FORMAT(sp.payment_month, '%Y-%m') = ? $extra",
            's' . $types,
            array_merge([$month], $params)
        ))['t'] ?? 0);

        $pendingLeaves = (int)(fetchOne(executeQuery(
            "SELECT COUNT(*) AS c FROM leave_applications la
             INNER JOIN staff s ON la.staff_id = s.id
             WHERE la.approval_stage IN ('Pending', 'Manager_Approved') $extra",
            $types,
            $params
        ))['c'] ?? 0);

        $openGrievances = 0;
        if (self::tableExists('hr_grievances')) {
            $openGrievances = (int)(fetchOne(executeQuery(
                "SELECT COUNT(*) AS c FROM hr_grievances g
                 LEFT JOIN staff s ON g.staff_id = s.id
                 WHERE g.status NOT IN ('Resolved', 'Closed') $extra",
                $types,
                $params
            ))['c'] ?? 0);
        }

        $avgAttendance = (float)(fetchOne(executeQuery(
            "SELECT ROUND(AVG(pct), 1) AS avg_pct FROM (
                SELECT sa.staff_id,
                SUM(CASE WHEN sa.status IN ('Present', 'Late') THEN 1 ELSE 0 END) * 100.0 /
                NULLIF(COUNT(*), 0) AS pct
                FROM staff_attendance sa
                INNER JOIN staff s ON sa.staff_id = s.id
                WHERE DATE_FORMAT(sa.attendance_date, '%Y-%m') = ? $extra
                GROUP BY sa.staff_id
             ) t",
            's' . $types,
            array_merge([$month], $params)
        ))['avg_pct'] ?? 0);

        $openVacancies = 0;
        if (self::tableExists('hr_job_vacancies')) {
            $openVacancies = (int)(fetchOne(executeQuery(
                "SELECT COUNT(*) AS c FROM hr_job_vacancies v WHERE v.status = 'Published'"
            ))['c'] ?? 0);
        }

        $outstandingAdvances = 0;
        if (self::tableExists('hr_salary_advances')) {
            $outstandingAdvances = (float)(fetchOne(executeQuery(
                "SELECT COALESCE(SUM(a.approved_amount - a.total_recovered), 0) AS t
                 FROM hr_salary_advances a
                 INNER JOIN staff s ON a.staff_id = s.id
                 WHERE a.status IN ('Disbursed', 'Approved') $extra",
                $types,
                $params
            ))['t'] ?? 0);
        }

        $kpis = [
            ['label' => 'Active Staff', 'value' => $active, 'icon' => 'users'],
            ['label' => 'Payroll MTD', 'value' => CURRENCY_SYMBOL . number_format($payrollMtd, 2), 'icon' => 'money'],
            ['label' => 'Pending Leaves', 'value' => $pendingLeaves, 'icon' => 'calendar'],
            ['label' => 'Open Grievances', 'value' => $openGrievances, 'icon' => 'alert'],
            ['label' => 'Avg Attendance %', 'value' => $avgAttendance . '%', 'icon' => 'check'],
            ['label' => 'Open Vacancies', 'value' => $openVacancies, 'icon' => 'briefcase'],
        ];

        return [
            'title' => 'HR Executive Summary',
            'subtitle' => date('F Y', strtotime($month . '-01')),
            'format' => 'dashboard',
            'kpis' => $kpis,
            'columns' => [],
            'rows' => [],
            'summary' => [
                'outstanding_advances' => $outstandingAdvances,
                'payroll_mtd' => $payrollMtd,
            ],
        ];
    }

    private static function reportHeadcount(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);

        $rows = fetchAll(executeQuery(
            "SELECT COALESCE(s.department, 'Unassigned') AS department,
             COUNT(*) AS headcount,
             SUM(CASE WHEN s.employment_type = 'Full Time' THEN 1 ELSE 0 END) AS full_time,
             SUM(CASE WHEN s.employment_type = 'Contract' THEN 1 ELSE 0 END) AS contract
             FROM staff s WHERE s.status = 'Active' $bf $df
             GROUP BY s.department ORDER BY headcount DESC",
            $bt . $dt,
            array_merge($bp, $dp)
        ));

        $total = array_sum(array_column($rows, 'headcount'));

        return [
            'title' => 'Headcount by Department',
            'subtitle' => 'Active employees as of ' . date('d M Y'),
            'format' => 'table',
            'columns' => [
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'headcount', 'label' => 'Total', 'align' => 'right'],
                ['key' => 'full_time', 'label' => 'Full Time', 'align' => 'right'],
                ['key' => 'contract', 'label' => 'Contract', 'align' => 'right'],
                ['key' => 'share_pct', 'label' => '% of Total', 'align' => 'right'],
            ],
            'rows' => array_map(function ($r) use ($total) {
                $r['share_pct'] = $total > 0 ? round(($r['headcount'] / $total) * 100, 1) . '%' : '0%';
                return $r;
            }, $rows),
            'summary' => ['total_headcount' => $total],
        ];
    }

    private static function reportEmployeeMaster(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);
        $sf = $status !== '' ? " AND s.status = ?" : '';
        $st = $status !== '' ? 's' : '';
        $sp = $status !== '' ? [$status] : [];

        $rows = fetchAll(executeQuery(
            "SELECT s.staff_id, CONCAT(s.first_name, ' ', s.last_name) AS employee_name,
             s.designation, COALESCE(s.department, '—') AS department, s.employment_type,
             s.gender, s.joining_date, s.status, b.branch_name
             FROM staff s
             LEFT JOIN branches b ON s.branch_id = b.id
             WHERE 1=1 $bf $df $sf
             ORDER BY s.first_name, s.last_name",
            $bt . $dt . $st,
            array_merge($bp, $dp, $sp)
        ));

        return [
            'title' => 'Employee Master List',
            'subtitle' => $status ? "Status: $status" : 'All employees',
            'format' => 'table',
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'employee_name', 'label' => 'Name'],
                ['key' => 'designation', 'label' => 'Designation'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'employment_type', 'label' => 'Type'],
                ['key' => 'joining_date', 'label' => 'Joined'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'branch_name', 'label' => 'Branch'],
            ],
            'rows' => $rows,
            'summary' => ['count' => count($rows)],
        ];
    }

    private static function reportDepartment(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);

        $rows = fetchAll(executeQuery(
            "SELECT COALESCE(s.department, 'Unassigned') AS department,
             COUNT(DISTINCT s.id) AS headcount,
             ROUND(AVG(att.pct), 1) AS attendance_pct,
             COALESCE(SUM(sp.net_salary), 0) AS payroll_total
             FROM staff s
             LEFT JOIN (
                SELECT sa.staff_id,
                SUM(CASE WHEN sa.status IN ('Present', 'Late') THEN 1 ELSE 0 END) * 100.0 /
                NULLIF(COUNT(*), 0) AS pct
                FROM staff_attendance sa
                WHERE DATE_FORMAT(sa.attendance_date, '%Y-%m') = ?
                GROUP BY sa.staff_id
             ) att ON att.staff_id = s.id
             LEFT JOIN salary_payments sp ON sp.staff_id = s.id AND DATE_FORMAT(sp.payment_month, '%Y-%m') = ?
             WHERE s.status = 'Active' $bf $df
             GROUP BY s.department ORDER BY headcount DESC",
            'ss' . $bt . $dt,
            array_merge([$month, $month], $bp, $dp)
        ));

        foreach ($rows as &$r) {
            $r['payroll_total'] = number_format((float)$r['payroll_total'], 2);
            $r['attendance_pct'] = $r['attendance_pct'] !== null ? $r['attendance_pct'] . '%' : '—';
        }
        unset($r);

        return [
            'title' => 'Department Overview',
            'subtitle' => date('F Y', strtotime($month . '-01')),
            'format' => 'table',
            'columns' => [
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'headcount', 'label' => 'Headcount', 'align' => 'right'],
                ['key' => 'attendance_pct', 'label' => 'Avg Attendance', 'align' => 'right'],
                ['key' => 'payroll_total', 'label' => 'Payroll (' . CURRENCY_SYMBOL . ')', 'align' => 'right'],
            ],
            'rows' => $rows,
            'summary' => ['departments' => count($rows)],
        ];
    }

    private static function reportPayroll(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);
        $sf = $status !== '' ? " AND sp.payment_status = ?" : '';
        $st = $status !== '' ? 's' : '';
        $sp = $status !== '' ? [$status] : [];

        $rows = fetchAll(executeQuery(
            "SELECT s.staff_id, CONCAT(s.first_name, ' ', s.last_name) AS employee_name,
             COALESCE(s.department, '—') AS department,
             sp.basic_salary, sp.allowances, sp.deductions, sp.net_salary,
             COALESCE(sp.payment_status, IF(sp.payment_date IS NOT NULL, 'Paid', 'Pending')) AS payment_status,
             sp.payment_date
             FROM salary_payments sp
             INNER JOIN staff s ON sp.staff_id = s.id
             WHERE DATE_FORMAT(sp.payment_month, '%Y-%m') = ? $bf $df $sf
             ORDER BY s.first_name",
            's' . $bt . $dt . $st,
            array_merge([$month], $bp, $dp, $sp)
        ));

        $totalNet = array_sum(array_column($rows, 'net_salary'));

        return [
            'title' => 'Payroll Register',
            'subtitle' => date('F Y', strtotime($month . '-01')),
            'format' => 'table',
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'employee_name', 'label' => 'Employee'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'basic_salary', 'label' => 'Basic', 'align' => 'right', 'format' => 'money'],
                ['key' => 'allowances', 'label' => 'Allowances', 'align' => 'right', 'format' => 'money'],
                ['key' => 'deductions', 'label' => 'Deductions', 'align' => 'right', 'format' => 'money'],
                ['key' => 'net_salary', 'label' => 'Net', 'align' => 'right', 'format' => 'money'],
                ['key' => 'payment_status', 'label' => 'Status'],
                ['key' => 'payment_date', 'label' => 'Paid On'],
            ],
            'rows' => $rows,
            'summary' => ['count' => count($rows), 'total_net' => $totalNet],
        ];
    }

    private static function reportAdvances(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        if (!self::tableExists('hr_salary_advances')) {
            return self::emptyReport('Salary Advances', 'Module not available');
        }

        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);
        $sf = $status !== '' ? " AND a.status = ?" : " AND a.status IN ('Approved', 'Disbursed')";
        $st = $status !== '' ? 's' : '';
        $sp = $status !== '' ? [$status] : [];

        $rows = fetchAll(executeQuery(
            "SELECT a.advance_no, s.staff_id, CONCAT(s.first_name, ' ', s.last_name) AS employee_name,
             a.approved_amount, a.total_recovered,
             (COALESCE(a.approved_amount, 0) - COALESCE(a.total_recovered, 0)) AS outstanding,
             a.monthly_recovery, a.status, a.disbursed_at
             FROM hr_salary_advances a
             INNER JOIN staff s ON a.staff_id = s.id
             WHERE 1=1 $bf $df $sf
             ORDER BY outstanding DESC",
            $bt . $dt . $st,
            array_merge($bp, $dp, $sp)
        ));

        $totalOutstanding = array_sum(array_map(function ($r) {
            return (float)$r['outstanding'];
        }, $rows));

        return [
            'title' => 'Salary Advances Outstanding',
            'subtitle' => 'Active advances and recovery status',
            'format' => 'table',
            'columns' => [
                ['key' => 'advance_no', 'label' => 'Advance No'],
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'employee_name', 'label' => 'Employee'],
                ['key' => 'approved_amount', 'label' => 'Approved', 'align' => 'right', 'format' => 'money'],
                ['key' => 'total_recovered', 'label' => 'Recovered', 'align' => 'right', 'format' => 'money'],
                ['key' => 'outstanding', 'label' => 'Outstanding', 'align' => 'right', 'format' => 'money'],
                ['key' => 'monthly_recovery', 'label' => 'Monthly', 'align' => 'right', 'format' => 'money'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'rows' => $rows,
            'summary' => ['count' => count($rows), 'total_outstanding' => $totalOutstanding],
        ];
    }

    private static function reportLeave(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);

        $rows = fetchAll(executeQuery(
            "SELECT lt.leave_name, COUNT(*) AS applications,
             SUM(la.total_days) AS total_days,
             ROUND(AVG(la.total_days), 1) AS avg_days
             FROM leave_applications la
             INNER JOIN staff s ON la.staff_id = s.id
             INNER JOIN leave_types lt ON la.leave_type_id = lt.id
             WHERE la.approval_stage = 'Approved' AND DATE_FORMAT(la.start_date, '%Y-%m') = ? $bf $df
             GROUP BY lt.leave_name ORDER BY total_days DESC",
            's' . $bt . $dt,
            array_merge([$month], $bp, $dp)
        ));

        return [
            'title' => 'Leave Utilization',
            'subtitle' => date('F Y', strtotime($month . '-01')),
            'format' => 'table',
            'columns' => [
                ['key' => 'leave_name', 'label' => 'Leave Type'],
                ['key' => 'applications', 'label' => 'Applications', 'align' => 'right'],
                ['key' => 'total_days', 'label' => 'Total Days', 'align' => 'right'],
                ['key' => 'avg_days', 'label' => 'Avg Days', 'align' => 'right'],
            ],
            'rows' => $rows,
            'summary' => ['total_days' => array_sum(array_column($rows, 'total_days'))],
        ];
    }

    private static function reportLeaveBalance(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        if (!self::tableExists('hr_leave_balances')) {
            return self::emptyReport('Leave Balance', 'Leave balance module not migrated');
        }

        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);

        $rows = fetchAll(executeQuery(
            "SELECT s.staff_id, CONCAT(s.first_name, ' ', s.last_name) AS employee_name,
             lt.leave_name, lb.allocated_days, lb.used_days,
             (lb.allocated_days + lb.carried_forward - lb.used_days) AS remaining
             FROM hr_leave_balances lb
             INNER JOIN staff s ON lb.staff_id = s.id
             INNER JOIN leave_types lt ON lb.leave_type_id = lt.id
             WHERE lb.year = ? AND s.status = 'Active' $bf $df
             ORDER BY s.first_name, lt.leave_name",
            'i' . $bt . $dt,
            array_merge([$year], $bp, $dp)
        ));

        return [
            'title' => 'Leave Balance Report',
            'subtitle' => "Year $year",
            'format' => 'table',
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'employee_name', 'label' => 'Employee'],
                ['key' => 'leave_name', 'label' => 'Leave Type'],
                ['key' => 'allocated_days', 'label' => 'Allocated', 'align' => 'right'],
                ['key' => 'used_days', 'label' => 'Used', 'align' => 'right'],
                ['key' => 'remaining', 'label' => 'Remaining', 'align' => 'right'],
            ],
            'rows' => $rows,
            'summary' => ['count' => count($rows)],
        ];
    }

    private static function reportAttendance(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);

        $rows = fetchAll(executeQuery(
            "SELECT s.staff_id, CONCAT(s.first_name, ' ', s.last_name) AS employee_name,
             COALESCE(s.department, '—') AS department,
             SUM(CASE WHEN sa.status IN ('Present', 'Late') THEN 1 ELSE 0 END) AS present,
             SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) AS absent,
             SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) AS late,
             ROUND(SUM(CASE WHEN sa.status IN ('Present', 'Late') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0), 1) AS attendance_pct
             FROM staff_attendance sa
             INNER JOIN staff s ON sa.staff_id = s.id
             WHERE DATE_FORMAT(sa.attendance_date, '%Y-%m') = ? $bf $df
             GROUP BY s.id ORDER BY attendance_pct ASC",
            's' . $bt . $dt,
            array_merge([$month], $bp, $dp)
        ));

        foreach ($rows as &$r) {
            $r['attendance_pct'] = ($r['attendance_pct'] ?? 0) . '%';
        }
        unset($r);

        return [
            'title' => 'Attendance Summary',
            'subtitle' => date('F Y', strtotime($month . '-01')),
            'format' => 'table',
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'employee_name', 'label' => 'Employee'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'present', 'label' => 'Present', 'align' => 'right'],
                ['key' => 'late', 'label' => 'Late', 'align' => 'right'],
                ['key' => 'absent', 'label' => 'Absent', 'align' => 'right'],
                ['key' => 'attendance_pct', 'label' => 'Rate', 'align' => 'right'],
            ],
            'rows' => $rows,
            'summary' => ['staff_count' => count($rows)],
        ];
    }

    private static function reportAttendanceLate(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);

        $rows = fetchAll(executeQuery(
            "SELECT s.staff_id, CONCAT(s.first_name, ' ', s.last_name) AS employee_name,
             COALESCE(s.department, '—') AS department,
             COUNT(*) AS late_count,
             MIN(sa.attendance_date) AS first_late,
             MAX(sa.attendance_date) AS last_late
             FROM staff_attendance sa
             INNER JOIN staff s ON sa.staff_id = s.id
             WHERE sa.status = 'Late' AND DATE_FORMAT(sa.attendance_date, '%Y-%m') = ? $bf $df
             GROUP BY s.id HAVING late_count > 0
             ORDER BY late_count DESC",
            's' . $bt . $dt,
            array_merge([$month], $bp, $dp)
        ));

        return [
            'title' => 'Late Arrival Report',
            'subtitle' => date('F Y', strtotime($month . '-01')),
            'format' => 'table',
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'employee_name', 'label' => 'Employee'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'late_count', 'label' => 'Late Days', 'align' => 'right'],
                ['key' => 'first_late', 'label' => 'First'],
                ['key' => 'last_late', 'label' => 'Last'],
            ],
            'rows' => $rows,
            'summary' => ['total_late_incidents' => array_sum(array_column($rows, 'late_count'))],
        ];
    }

    private static function reportGrievances(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        if (!self::tableExists('hr_grievances')) {
            return self::emptyReport('Grievance Summary', 'Module not available');
        }

        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);
        $sf = $status !== '' ? " AND g.status = ?" : '';
        $st = $status !== '' ? 's' : '';
        $sp = $status !== '' ? [$status] : [];

        $rows = fetchAll(executeQuery(
            "SELECT REPLACE(g.category, '_', ' ') AS category, g.status, g.priority,
             COUNT(*) AS total,
             SUM(CASE WHEN g.is_anonymous = 1 THEN 1 ELSE 0 END) AS anonymous_count
             FROM hr_grievances g
             LEFT JOIN staff s ON g.staff_id = s.id
             WHERE DATE_FORMAT(g.created_at, '%Y-%m') = ? $bf $df $sf
             GROUP BY g.category, g.status, g.priority
             ORDER BY total DESC",
            's' . $bt . $dt . $st,
            array_merge([$month], $bp, $dp, $sp)
        ));

        return [
            'title' => 'Grievance Summary',
            'subtitle' => date('F Y', strtotime($month . '-01')),
            'format' => 'table',
            'columns' => [
                ['key' => 'category', 'label' => 'Category'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'priority', 'label' => 'Priority'],
                ['key' => 'total', 'label' => 'Cases', 'align' => 'right'],
                ['key' => 'anonymous_count', 'label' => 'Anonymous', 'align' => 'right'],
            ],
            'rows' => $rows,
            'summary' => ['total_cases' => array_sum(array_column($rows, 'total'))],
        ];
    }

    private static function reportPerformance(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        if (!self::tableExists('hr_performance_reviews')) {
            return self::emptyReport('Performance Reviews', 'Module not available');
        }

        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);
        $sf = $status !== '' ? " AND pr.status = ?" : '';
        $st = $status !== '' ? 's' : '';
        $sp = $status !== '' ? [$status] : '';

        $rows = fetchAll(executeQuery(
            "SELECT s.staff_id, CONCAT(s.first_name, ' ', s.last_name) AS employee_name,
             pr.review_period, pr.rating, pr.status, pr.review_date
             FROM hr_performance_reviews pr
             INNER JOIN staff s ON pr.staff_id = s.id
             WHERE YEAR(pr.review_date) = ? $bf $df $sf
             ORDER BY pr.review_date DESC",
            'i' . $bt . $dt . $st,
            array_merge([$year], $bp, $dp, $sp)
        ));

        $ratings = array_filter(array_column($rows, 'rating'), function ($v) {
            return $v !== null;
        });

        return [
            'title' => 'Performance Review Summary',
            'subtitle' => "Year $year",
            'format' => 'table',
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'employee_name', 'label' => 'Employee'],
                ['key' => 'review_period', 'label' => 'Period'],
                ['key' => 'rating', 'label' => 'Rating', 'align' => 'right'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'review_date', 'label' => 'Date'],
            ],
            'rows' => $rows,
            'summary' => [
                'count' => count($rows),
                'avg_rating' => count($ratings) ? round(array_sum($ratings) / count($ratings), 2) : null,
            ],
        ];
    }

    private static function reportPpdp(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        if (!self::tableExists('hr_ppdp_programs')) {
            return self::emptyReport('PPDP Completion', 'Module not available');
        }

        [$bf, $bt, $bp] = self::branchClause('s', $branchId, $isSuperAdmin);
        [$df, $dt, $dp] = self::deptClause('s', $department);

        $rows = fetchAll(executeQuery(
            "SELECT p.program_code, p.program_name, p.status AS program_status,
             COUNT(pt.id) AS participants,
             SUM(CASE WHEN pt.status = 'Completed' THEN 1 ELSE 0 END) AS completed,
             ROUND(SUM(CASE WHEN pt.status = 'Completed' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(pt.id), 0), 1) AS completion_pct
             FROM hr_ppdp_programs p
             LEFT JOIN hr_ppdp_participants pt ON pt.program_id = p.id
             LEFT JOIN staff s ON pt.staff_id = s.id
             WHERE YEAR(p.start_date) = ? $bf $df
             GROUP BY p.id ORDER BY p.start_date DESC",
            'i' . $bt . $dt,
            array_merge([$year], $bp, $dp)
        ));

        foreach ($rows as &$r) {
            $r['completion_pct'] = ($r['completion_pct'] ?? 0) . '%';
        }
        unset($r);

        return [
            'title' => 'PPDP Program Completion',
            'subtitle' => "Year $year",
            'format' => 'table',
            'columns' => [
                ['key' => 'program_code', 'label' => 'Code'],
                ['key' => 'program_name', 'label' => 'Program'],
                ['key' => 'program_status', 'label' => 'Status'],
                ['key' => 'participants', 'label' => 'Enrolled', 'align' => 'right'],
                ['key' => 'completed', 'label' => 'Completed', 'align' => 'right'],
                ['key' => 'completion_pct', 'label' => 'Rate', 'align' => 'right'],
            ],
            'rows' => $rows,
            'summary' => ['programs' => count($rows)],
        ];
    }

    private static function reportRecruitment(string $month, int $year, string $department, string $status, ?int $branchId, bool $isSuperAdmin): array
    {
        if (!self::tableExists('hr_job_applications')) {
            return self::emptyReport('Recruitment Pipeline', 'Module not available');
        }

        $bf = '';
        $types = 's';
        $params = [$month];
        if ($branchId) {
            $bf = ' AND v.branch_id = ?';
            $types .= 'i';
            $params[] = $branchId;
        }
        if ($department !== '') {
            $bf .= ' AND v.department = ?';
            $types .= 's';
            $params[] = $department;
        }

        $rows = fetchAll(executeQuery(
            "SELECT v.vacancy_no, v.job_title, v.department, v.status AS vacancy_status,
             COUNT(a.id) AS applications,
             SUM(CASE WHEN a.status = 'Shortlisted' THEN 1 ELSE 0 END) AS shortlisted,
             SUM(CASE WHEN a.status = 'Interview' THEN 1 ELSE 0 END) AS interview,
             SUM(CASE WHEN a.status = 'Hired' THEN 1 ELSE 0 END) AS hired
             FROM hr_job_vacancies v
             LEFT JOIN hr_job_applications a ON a.vacancy_id = v.id
             WHERE DATE_FORMAT(v.created_at, '%Y-%m') <= ? $bf
             GROUP BY v.id ORDER BY applications DESC",
            $types,
            $params
        ));

        return [
            'title' => 'Recruitment Pipeline',
            'subtitle' => 'Vacancy funnel analysis',
            'format' => 'table',
            'columns' => [
                ['key' => 'vacancy_no', 'label' => 'Vacancy No'],
                ['key' => 'job_title', 'label' => 'Position'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'vacancy_status', 'label' => 'Status'],
                ['key' => 'applications', 'label' => 'Applied', 'align' => 'right'],
                ['key' => 'shortlisted', 'label' => 'Shortlisted', 'align' => 'right'],
                ['key' => 'interview', 'label' => 'Interview', 'align' => 'right'],
                ['key' => 'hired', 'label' => 'Hired', 'align' => 'right'],
            ],
            'rows' => $rows,
            'summary' => ['vacancies' => count($rows), 'total_applications' => array_sum(array_column($rows, 'applications'))],
        ];
    }

    private static function emptyReport(string $title, string $message): array
    {
        return [
            'title' => $title,
            'subtitle' => $message,
            'format' => 'table',
            'columns' => [],
            'rows' => [],
            'summary' => [],
        ];
    }

    public static function formatCell($value, ?string $format = null): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        if ($format === 'money') {
            return CURRENCY_SYMBOL . number_format((float)$value, 2);
        }
        return (string)$value;
    }

    public static function renderPdfHtml(array $report, array $meta = []): string
    {
        $title = htmlspecialchars($report['title'] ?? 'HR Report');
        $subtitle = htmlspecialchars($report['subtitle'] ?? '');
        $generated = htmlspecialchars($report['generated_at'] ?? date('Y-m-d H:i:s'));
        $generatedBy = htmlspecialchars($meta['user_name'] ?? 'System');
        $branchName = htmlspecialchars($meta['branch_name'] ?? 'All Branches');
        $appName = htmlspecialchars(APP_NAME);
        $currency = CURRENCY_SYMBOL;

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>
            body{font-family:DejaVu Sans,Arial,sans-serif;font-size:9pt;color:#1e293b;line-height:1.45;margin:0}
            .brand-bar{background:#1a56db;color:#fff;padding:22px 24px 18px;margin:0 0 18px 0}
            .brand-title{font-size:20pt;font-weight:bold;letter-spacing:0.3px;margin:0 0 4px 0}
            .brand-sub{font-size:10pt;opacity:0.92;margin:0}
            .report-name{font-size:14pt;font-weight:bold;color:#1a56db;margin:0 0 4px 0}
            .report-period{font-size:9.5pt;color:#64748b;margin:0 0 14px 0}
            .meta-grid{width:100%;border-collapse:collapse;margin-bottom:16px}
            .meta-grid td{font-size:8.5pt;padding:6px 10px;border:1px solid #e2e8f0;background:#f8fafc}
            .meta-label{color:#64748b;font-weight:bold;width:22%}
            .kpi-row{width:100%;border-collapse:separate;border-spacing:8px 0;margin:0 0 16px 0}
            .kpi-box{border:1px solid #dbeafe;background:#eff6ff;border-radius:6px;padding:12px 10px;text-align:center;width:16%}
            .kpi-val{font-size:16pt;font-weight:bold;color:#1a56db;margin:0}
            .kpi-lbl{font-size:7.5pt;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;margin-top:4px}
            table.data{width:100%;border-collapse:collapse;margin-top:6px}
            table.data th{background:#1a56db;color:#fff;font-size:8.5pt;padding:8px 7px;text-align:left;font-weight:bold}
            table.data th.right,table.data td.right{text-align:right}
            table.data td{font-size:8.5pt;padding:7px;border-bottom:1px solid #e2e8f0}
            table.data tr:nth-child(even) td{background:#f8fafc}
            .totals-row td{font-weight:bold;background:#eff6ff !important;border-top:2px solid #1a56db}
            .summary-box{margin-top:14px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:4px;font-size:8.5pt}
            .bar-wrap{background:#e2e8f0;height:8px;border-radius:4px;margin-top:4px}
            .bar-fill{background:#1a56db;height:8px;border-radius:4px}
            .footer-note{font-size:7.5pt;color:#94a3b8;margin-top:20px;padding-top:10px;border-top:1px solid #e2e8f0}
            .confidential{font-size:7pt;color:#94a3b8;text-align:center;margin-top:6px}
        </style></head><body>';

        $html .= '<div class="brand-bar">';
        $html .= '<div class="brand-title">' . $appName . '</div>';
        $html .= '<div class="brand-sub">Human Resources — Confidential Report</div>';
        $html .= '</div>';

        $html .= '<div class="report-name">' . $title . '</div>';
        if ($subtitle) {
            $html .= '<div class="report-period">' . $subtitle . '</div>';
        }

        $html .= '<table class="meta-grid"><tr>';
        $html .= '<td class="meta-label">Generated</td><td>' . $generated . '</td>';
        $html .= '<td class="meta-label">Prepared By</td><td>' . $generatedBy . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td class="meta-label">Branch</td><td>' . $branchName . '</td>';
        $html .= '<td class="meta-label">Report ID</td><td>HR-' . strtoupper($report['type'] ?? 'RPT') . '-' . date('YmdHis') . '</td>';
        $html .= '</tr></table>';

        if (($report['format'] ?? '') === 'dashboard' && !empty($report['kpis'])) {
            $html .= '<table class="kpi-row"><tr>';
            foreach ($report['kpis'] as $kpi) {
                $html .= '<td class="kpi-box"><div class="kpi-val">' . htmlspecialchars((string)$kpi['value']) . '</div>';
                $html .= '<div class="kpi-lbl">' . htmlspecialchars($kpi['label']) . '</div></td>';
            }
            $html .= '</tr></table>';

            if (!empty($report['summary']['outstanding_advances'])) {
                $html .= '<div class="summary-box"><strong>Outstanding Advances:</strong> ' . $currency
                    . number_format((float)$report['summary']['outstanding_advances'], 2) . '</div>';
            }
        } elseif (!empty($report['columns']) && !empty($report['rows'])) {
            $html .= '<table class="data"><thead><tr>';
            foreach ($report['columns'] as $col) {
                $align = ($col['align'] ?? '') === 'right' ? ' class="right"' : '';
                $html .= '<th' . $align . '>' . htmlspecialchars($col['label']) . '</th>';
            }
            $html .= '</tr></thead><tbody>';

            $maxBar = 0;
            if (($report['type'] ?? '') === 'headcount') {
                $maxBar = max(array_column($report['rows'], 'headcount') ?: [1]);
            }

            foreach ($report['rows'] as $row) {
                $html .= '<tr>';
                foreach ($report['columns'] as $col) {
                    $key = $col['key'];
                    $val = self::formatCell($row[$key] ?? '', $col['format'] ?? null);
                    $align = ($col['align'] ?? '') === 'right' ? ' class="right"' : '';
                    $cell = htmlspecialchars($val);
                    if ($key === 'headcount' && $maxBar > 0) {
                        $pct = round(((float)$row[$key] / $maxBar) * 100);
                        $cell .= '<div class="bar-wrap"><div class="bar-fill" style="width:' . $pct . '%"></div></div>';
                    }
                    $html .= '<td' . $align . '>' . $cell . '</td>';
                }
                $html .= '</tr>';
            }

            if (!empty($report['summary'])) {
                $html .= '<tr class="totals-row"><td colspan="' . count($report['columns']) . '">';
                $parts = [];
                foreach ($report['summary'] as $k => $v) {
                    if ($v === null || $v === '') {
                        continue;
                    }
                    $label = ucwords(str_replace('_', ' ', $k));
                    if (is_numeric($v) && strpos($k, 'total') !== false && strpos($k, 'count') === false) {
                        $parts[] = "$label: $currency" . number_format((float)$v, 2);
                    } else {
                        $parts[] = "$label: $v";
                    }
                }
                $html .= implode(' &nbsp;|&nbsp; ', $parts);
                $html .= '</td></tr>';
            }

            $html .= '</tbody></table>';
        } else {
            $html .= '<p style="color:#64748b;padding:20px 0">No data available for the selected filters.</p>';
        }

        $html .= '<div class="footer-note">';
        $html .= 'This document is generated electronically by ' . $appName . ' and is valid without signature. ';
        $html .= 'Distribution is restricted to authorized HR and finance personnel.';
        $html .= '</div>';
        $html .= '<div class="confidential">CONFIDENTIAL — ' . strtoupper($appName) . ' HR DEPARTMENT</div>';

        $html .= '</body></html>';
        return $html;
    }

    public static function ensureMpdf(): bool
    {
        if (class_exists('Mpdf\Mpdf')) {
            return true;
        }
        $autoload = ABSPATH . 'vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }
        return class_exists('Mpdf\Mpdf');
    }

    public static function outputPdf(array $report, array $meta = []): void
    {
        if (!self::ensureMpdf()) {
            throw new RuntimeException('PDF engine (mPDF) is not installed. Run: composer require mpdf/mpdf');
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 18,
            'margin_footer' => 8,
        ]);

        $mpdf->SetTitle($report['title'] ?? 'HR Report');
        $mpdf->SetAuthor(APP_NAME);
        $mpdf->SetHTMLFooter(
            '<div style="font-size:7pt;color:#94a3b8;text-align:center;border-top:1px solid #e2e8f0;padding-top:4px">'
            . htmlspecialchars(APP_NAME) . ' — Page {PAGENO} of {nbpg}</div>'
        );

        $mpdf->WriteHTML(self::renderPdfHtml($report, $meta));

        $filename = 'HR_' . ($report['type'] ?? 'report') . '_' . date('Y-m-d') . '.pdf';
        $mpdf->Output($filename, 'D');
    }

    public static function outputCsv(array $report): void
    {
        $filename = 'HR_' . ($report['type'] ?? 'report') . '_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if (($report['format'] ?? '') === 'dashboard' && !empty($report['kpis'])) {
            fputcsv($out, ['Metric', 'Value']);
            foreach ($report['kpis'] as $kpi) {
                fputcsv($out, [$kpi['label'], $kpi['value']]);
            }
        } elseif (!empty($report['columns'])) {
            fputcsv($out, array_column($report['columns'], 'label'));
            foreach ($report['rows'] as $row) {
                $line = [];
                foreach ($report['columns'] as $col) {
                    $line[] = self::formatCell($row[$col['key']] ?? '', $col['format'] ?? null);
                }
                fputcsv($out, $line);
            }
        }

        fclose($out);
    }
}
