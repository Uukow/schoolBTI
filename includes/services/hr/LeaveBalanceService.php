<?php
/**
 * Leave Balance Service
 * Manages leave allocations, usage, and balance checks
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

class LeaveBalanceService
{
    /**
     * Get or create leave balance for staff/year/type
     */
    public static function getBalance($staffId, $leaveTypeId, $year = null)
    {
        $year = $year ?? (int) date('Y');

        $sql = "SELECT * FROM hr_leave_balances
                WHERE staff_id = ? AND leave_type_id = ? AND year = ?";
        $balance = fetchOne(executeQuery($sql, 'iii', [$staffId, $leaveTypeId, $year]));

        if ($balance) {
            return $balance;
        }

        return self::initializeBalance($staffId, $leaveTypeId, $year);
    }

    /**
     * Initialize balance from leave policy
     */
    public static function initializeBalance($staffId, $leaveTypeId, $year)
    {
        $staff = fetchOne(executeQuery("SELECT branch_id FROM staff WHERE id = ?", 'i', [$staffId]));
        $branchId = $staff['branch_id'] ?? null;

        $policySql = "SELECT days_per_year FROM hr_leave_policies
                      WHERE leave_type_id = ? AND is_active = 1
                      AND (branch_id = ? OR branch_id IS NULL)
                      ORDER BY branch_id DESC LIMIT 1";
        $policy = fetchOne(executeQuery($policySql, 'ii', [$leaveTypeId, $branchId]));

        if (!$policy) {
            $typeRow = fetchOne(executeQuery("SELECT days_allowed FROM leave_types WHERE id = ?", 'i', [$leaveTypeId]));
            $allocated = (float) ($typeRow['days_allowed'] ?? 0);
        } else {
            $allocated = (float) $policy['days_per_year'];
        }

        $insertSql = "INSERT INTO hr_leave_balances (staff_id, leave_type_id, year, allocated_days, used_days, carried_forward)
                      VALUES (?, ?, ?, ?, 0, 0)
                      ON DUPLICATE KEY UPDATE allocated_days = VALUES(allocated_days)";
        executeQuery($insertSql, 'iiid', [$staffId, $leaveTypeId, $year, $allocated]);

        return self::getBalanceRaw($staffId, $leaveTypeId, $year);
    }

    private static function getBalanceRaw($staffId, $leaveTypeId, $year)
    {
        return fetchOne(executeQuery(
            "SELECT * FROM hr_leave_balances WHERE staff_id = ? AND leave_type_id = ? AND year = ?",
            'iii',
            [$staffId, $leaveTypeId, $year]
        ));
    }

    /**
     * Remaining leave days
     */
    public static function getRemainingDays($staffId, $leaveTypeId, $year = null)
    {
        $balance = self::getBalance($staffId, $leaveTypeId, $year);
        if (!$balance) {
            return 0;
        }
        return (float) $balance['allocated_days'] + (float) $balance['carried_forward'] - (float) $balance['used_days'];
    }

    /**
     * Validate leave request against balance
     */
    public static function canApplyLeave($staffId, $leaveTypeId, $totalDays, $year = null)
    {
        $remaining = self::getRemainingDays($staffId, $leaveTypeId, $year);
        if ($totalDays > $remaining) {
            return [
                'allowed' => false,
                'message' => "Insufficient leave balance. Remaining: {$remaining} days, Requested: {$totalDays} days.",
                'remaining' => $remaining,
            ];
        }
        return ['allowed' => true, 'remaining' => $remaining, 'message' => 'OK'];
    }

    /**
     * Deduct leave days on final approval
     */
    public static function deductLeave($staffId, $leaveTypeId, $totalDays, $year = null)
    {
        $year = $year ?? (int) date('Y');
        self::getBalance($staffId, $leaveTypeId, $year);

        $sql = "UPDATE hr_leave_balances SET used_days = used_days + ?
                WHERE staff_id = ? AND leave_type_id = ? AND year = ?";
        return executeQuery($sql, 'diii', [$totalDays, $staffId, $leaveTypeId, $year]);
    }

    /**
     * Restore leave days on cancellation/rejection after approval
     */
    public static function restoreLeave($staffId, $leaveTypeId, $totalDays, $year = null)
    {
        $year = $year ?? (int) date('Y');
        $sql = "UPDATE hr_leave_balances SET used_days = GREATEST(0, used_days - ?)
                WHERE staff_id = ? AND leave_type_id = ? AND year = ?";
        return executeQuery($sql, 'diii', [$totalDays, $staffId, $leaveTypeId, $year]);
    }

    /**
     * Get all balances for a staff member
     */
    public static function getAllBalancesForStaff($staffId, $year = null)
    {
        $year = $year ?? (int) date('Y');

        $types = fetchAll(executeQuery("SELECT id, leave_name, leave_code, days_allowed FROM leave_types ORDER BY leave_name"));
        $result = [];

        foreach ($types as $type) {
            $balance = self::getBalance($staffId, $type['id'], $year);
            $remaining = (float) $balance['allocated_days'] + (float) $balance['carried_forward'] - (float) $balance['used_days'];
            $result[] = [
                'leave_type_id' => $type['id'],
                'leave_name' => $type['leave_name'],
                'leave_code' => $type['leave_code'],
                'allocated_days' => (float) $balance['allocated_days'],
                'used_days' => (float) $balance['used_days'],
                'carried_forward' => (float) $balance['carried_forward'],
                'remaining_days' => $remaining,
                'year' => $year,
            ];
        }

        return $result;
    }

    /**
     * Get leave policy for staff
     */
    public static function getPolicy($leaveTypeId, $branchId)
    {
        $sql = "SELECT * FROM hr_leave_policies
                WHERE leave_type_id = ? AND is_active = 1
                AND (branch_id = ? OR branch_id IS NULL)
                ORDER BY branch_id DESC LIMIT 1";
        return fetchOne(executeQuery($sql, 'ii', [$leaveTypeId, $branchId]));
    }
}
