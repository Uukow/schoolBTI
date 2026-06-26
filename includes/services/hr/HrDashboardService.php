<?php
/**
 * HR Dashboard Service
 * Aggregates KPIs and analytics for HR command center
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

class HrDashboardService
{
    /**
     * Build branch filter clause for staff queries
     */
    private static function branchFilter($branchId, $isSuperAdmin)
    {
        if ($isSuperAdmin || !$branchId) {
            return ['sql' => '', 'params' => [], 'types' => ''];
        }
        return ['sql' => ' AND branch_id = ?', 'params' => [$branchId], 'types' => 'i'];
    }

    /**
     * Get dashboard KPIs
     */
    public static function getKpis($branchId = null, $isSuperAdmin = false)
    {
        $bf = self::branchFilter($branchId, $isSuperAdmin);
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        $staffSql = "SELECT
            COUNT(*) as total_staff,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_staff,
            SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) as inactive_staff,
            SUM(CASE WHEN designation LIKE '%Teacher%' THEN 1 ELSE 0 END) as teachers
            FROM staff WHERE 1=1" . $bf['sql'];
        $staffStats = fetchOne(executeQuery($staffSql, $bf['types'], $bf['params']));

        $attSql = "SELECT
            COUNT(DISTINCT sa.staff_id) as marked_today,
            SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present_today,
            SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent_today,
            SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as late_today
            FROM staff_attendance sa
            INNER JOIN staff s ON sa.staff_id = s.id
            WHERE sa.attendance_date = ? AND sa.deleted_at IS NULL" . str_replace('branch_id', 's.branch_id', $bf['sql']);
        $attParams = array_merge([$today], $bf['params']);
        $attTypes = 's' . $bf['types'];
        $attStats = fetchOne(executeQuery($attSql, $attTypes, $attParams));

        $activeStaff = (int) ($staffStats['active_staff'] ?? 0);
        $presentToday = (int) ($attStats['present_today'] ?? 0) + (int) ($attStats['late_today'] ?? 0);
        $attendanceRate = $activeStaff > 0 ? round(($presentToday / $activeStaff) * 100, 1) : 0;

        $leaveSql = "SELECT COUNT(*) as pending_leaves FROM leave_applications la
                     INNER JOIN staff s ON la.staff_id = s.id
                     WHERE la.approval_stage IN ('Pending','Manager_Approved')" . str_replace('branch_id', 's.branch_id', $bf['sql']);
        $leaveStats = fetchOne(executeQuery($leaveSql, $bf['types'], $bf['params']));

        $payrollSql = "SELECT
            COUNT(*) as total_payments,
            SUM(CASE WHEN sp.payment_date IS NULL THEN 1 ELSE 0 END) as pending_payments,
            COALESCE(SUM(sp.net_salary), 0) as total_payroll_mtd
            FROM salary_payments sp
            INNER JOIN staff s ON sp.staff_id = s.id
            WHERE sp.payment_month >= ?" . str_replace('branch_id', 's.branch_id', $bf['sql']);
        $payParams = array_merge([$monthStart], $bf['params']);
        $payTypes = 's' . $bf['types'];
        $payStats = fetchOne(executeQuery($payrollSql, $payTypes, $payParams));

        $corrSql = "SELECT COUNT(*) as pending_corrections FROM hr_attendance_corrections ac
                    INNER JOIN staff s ON ac.staff_id = s.id
                    WHERE ac.status IN ('Submitted','Manager_Approved')" . str_replace('branch_id', 's.branch_id', $bf['sql']);
        $corrStats = fetchOne(executeQuery($corrSql, $bf['types'], $bf['params']));

        return [
            'total_staff' => (int) ($staffStats['total_staff'] ?? 0),
            'active_staff' => $activeStaff,
            'inactive_staff' => (int) ($staffStats['inactive_staff'] ?? 0),
            'teachers' => (int) ($staffStats['teachers'] ?? 0),
            'present_today' => $presentToday,
            'absent_today' => (int) ($attStats['absent_today'] ?? 0),
            'late_today' => (int) ($attStats['late_today'] ?? 0),
            'attendance_rate' => $attendanceRate,
            'pending_leaves' => (int) ($leaveStats['pending_leaves'] ?? 0),
            'pending_payments' => (int) ($payStats['pending_payments'] ?? 0),
            'payroll_mtd' => (float) ($payStats['total_payroll_mtd'] ?? 0),
            'pending_corrections' => (int) ($corrStats['pending_corrections'] ?? 0),
        ];
    }

    /**
     * Headcount by department
     */
    public static function getDepartmentBreakdown($branchId = null, $isSuperAdmin = false)
    {
        $bf = self::branchFilter($branchId, $isSuperAdmin);
        $sql = "SELECT COALESCE(department, 'Unassigned') as department, COUNT(*) as count
                FROM staff WHERE status = 'Active'" . $bf['sql'] . "
                GROUP BY department ORDER BY count DESC LIMIT 10";
        return fetchAll(executeQuery($sql, $bf['types'], $bf['params']));
    }

    /**
     * 7-day attendance trend
     */
    public static function getAttendanceTrend($branchId = null, $isSuperAdmin = false)
    {
        $bf = self::branchFilter($branchId, $isSuperAdmin);
        $sql = "SELECT sa.attendance_date,
                SUM(CASE WHEN sa.status IN ('Present','Late') THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent_count
                FROM staff_attendance sa
                INNER JOIN staff s ON sa.staff_id = s.id
                WHERE sa.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                AND sa.deleted_at IS NULL" . str_replace('branch_id', 's.branch_id', $bf['sql']) . "
                GROUP BY sa.attendance_date ORDER BY sa.attendance_date";
        return fetchAll(executeQuery($sql, $bf['types'], $bf['params']));
    }

    /**
     * Recent HR activity from activity_logs
     */
    public static function getRecentActivity($limit = 10)
    {
        $sql = "SELECT al.*, u.username FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.module IN ('HR', 'hr', 'Attendance')
                ORDER BY al.created_at DESC LIMIT ?";
        return fetchAll(executeQuery($sql, 'i', [$limit]));
    }

    /**
     * Leave calendar events (leaves + holidays) for date range
     *
     * @param array $opts branch_id, is_super_admin, department, staff_id, show_leaves, show_holidays
     */
    public static function getLeaveCalendarEvents($startDate, $endDate, array $opts = [])
    {
        $events = [];
        $branchId = $opts['branch_id'] ?? null;
        $isSuperAdmin = !empty($opts['is_super_admin']);
        $department = trim($opts['department'] ?? '');
        $staffId = (int)($opts['staff_id'] ?? 0);
        $showLeaves = !isset($opts['show_leaves']) || $opts['show_leaves'];
        $showHolidays = !isset($opts['show_holidays']) || $opts['show_holidays'];

        if ($showLeaves) {
            $leaveSql = "SELECT la.id, la.staff_id, la.start_date, la.end_date, la.total_days, la.reason,
                         la.status, la.approval_stage,
                         s.first_name, s.last_name, s.staff_id AS employee_code, s.department,
                         b.branch_name,
                         lt.leave_name, lt.leave_code
                         FROM leave_applications la
                         INNER JOIN staff s ON la.staff_id = s.id
                         INNER JOIN leave_types lt ON la.leave_type_id = lt.id
                         LEFT JOIN branches b ON s.branch_id = b.id
                         WHERE la.end_date >= ? AND la.start_date <= ?
                         AND la.approval_stage IN ('Approved','Manager_Approved','Pending')";
            $leaveParams = [$startDate, $endDate];
            $leaveTypes = 'ss';

            if ($branchId) {
                $leaveSql .= " AND s.branch_id = ?";
                $leaveParams[] = $branchId;
                $leaveTypes .= 'i';
            }
            if ($department !== '') {
                $leaveSql .= " AND s.department = ?";
                $leaveParams[] = $department;
                $leaveTypes .= 's';
            }
            if ($staffId > 0) {
                $leaveSql .= " AND la.staff_id = ?";
                $leaveParams[] = $staffId;
                $leaveTypes .= 'i';
            }

            $leaves = fetchAll(executeQuery($leaveSql, $leaveTypes, $leaveParams));
            foreach ($leaves as $leave) {
                $stage = $leave['approval_stage'];
                $color = $stage === 'Approved' ? '#198754' : ($stage === 'Manager_Approved' ? '#0dcaf0' : '#ffc107');
                $events[] = [
                    'id' => 'leave-' . $leave['id'],
                    'title' => $leave['first_name'] . ' ' . $leave['last_name'] . ' · ' . $leave['leave_code'],
                    'start' => $leave['start_date'],
                    'end' => date('Y-m-d', strtotime($leave['end_date'] . ' +1 day')),
                    'type' => 'leave',
                    'status' => $stage,
                    'color' => $color,
                    'leave_id' => (int)$leave['id'],
                    'employee_name' => trim($leave['first_name'] . ' ' . $leave['last_name']),
                    'employee_code' => $leave['employee_code'],
                    'department' => $leave['department'],
                    'branch_name' => $leave['branch_name'],
                    'leave_name' => $leave['leave_name'],
                    'leave_code' => $leave['leave_code'],
                    'total_days' => $leave['total_days'],
                    'reason' => $leave['reason'],
                    'date_start' => $leave['start_date'],
                    'date_end' => $leave['end_date'],
                ];
            }
        }

        if ($showHolidays) {
            $holidaySql = "SELECT h.*, b.branch_name
                           FROM hr_holidays h
                           LEFT JOIN branches b ON h.branch_id = b.id
                           WHERE h.holiday_date <= ? AND COALESCE(h.end_date, h.holiday_date) >= ?";
            $holidayParams = [$endDate, $startDate];
            $holidayTypes = 'ss';

            if ($branchId) {
                $holidaySql .= " AND (h.branch_id IS NULL OR h.branch_id = ?)";
                $holidayParams[] = $branchId;
                $holidayTypes .= 'i';
            }

            $holidays = fetchAll(executeQuery($holidaySql, $holidayTypes, $holidayParams));
            foreach ($holidays as $holiday) {
                $holidayEnd = $holiday['end_date'] ?? $holiday['holiday_date'];
                $days = max(1, (int)((strtotime($holidayEnd) - strtotime($holiday['holiday_date'])) / 86400) + 1);
                $typeColor = [
                    'Public' => '#dc3545',
                    'Institutional' => '#6f42c1',
                    'Optional' => '#fd7e14',
                ];
                $events[] = [
                    'id' => 'holiday-' . $holiday['id'],
                    'title' => $holiday['holiday_name'],
                    'start' => $holiday['holiday_date'],
                    'end' => date('Y-m-d', strtotime($holidayEnd . ' +1 day')),
                    'type' => 'holiday',
                    'status' => $holiday['holiday_type'],
                    'color' => $typeColor[$holiday['holiday_type']] ?? '#dc3545',
                    'holiday_id' => (int)$holiday['id'],
                    'description' => $holiday['description'],
                    'branch_name' => $holiday['branch_name'] ?? 'All Branches',
                    'is_recurring' => (int)$holiday['is_recurring'],
                    'duration_days' => $days,
                    'date_start' => $holiday['holiday_date'],
                    'date_end' => $holidayEnd,
                ];
            }
        }

        return $events;
    }

    public static function getLeaveCalendarStats(array $events, $today = null)
    {
        $today = $today ?? date('Y-m-d');
        $stats = [
            'approved' => 0,
            'pending' => 0,
            'manager_approved' => 0,
            'holidays' => 0,
            'on_leave_today' => 0,
            'total_events' => count($events),
        ];

        foreach ($events as $e) {
            if ($e['type'] === 'holiday') {
                $stats['holidays']++;
                continue;
            }
            $stage = $e['status'] ?? '';
            if ($stage === 'Approved') {
                $stats['approved']++;
            } elseif ($stage === 'Manager_Approved') {
                $stats['manager_approved']++;
            } elseif ($stage === 'Pending') {
                $stats['pending']++;
            }
            if (!empty($e['date_start']) && !empty($e['date_end'])
                && $e['date_start'] <= $today && $e['date_end'] >= $today
                && in_array($stage, ['Approved', 'Manager_Approved'], true)) {
                $stats['on_leave_today']++;
            }
        }

        return $stats;
    }
}
