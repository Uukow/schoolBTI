<?php
/**
 * Attendance Calculation Service
 * Applies attendance rules to compute late, early departure, and overtime
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

class AttendanceCalculationService
{
    /**
     * Get applicable attendance rule for a staff member
     */
    public static function getRuleForStaff($staffId)
    {
        $staffSql = "SELECT branch_id FROM staff WHERE id = ?";
        $staff = fetchOne(executeQuery($staffSql, 'i', [$staffId]));
        if (!$staff) {
            return null;
        }

        $branchId = $staff['branch_id'];

        $sql = "SELECT * FROM hr_attendance_rules
                WHERE is_active = 1 AND (branch_id = ? OR branch_id IS NULL)
                ORDER BY branch_id DESC LIMIT 1";
        return fetchOne(executeQuery($sql, 'i', [$branchId]));
    }

    /**
     * Check if date is a holiday for staff branch
     */
    public static function isHoliday($date, $branchId)
    {
        $sql = "SELECT id FROM hr_holidays
                WHERE ? BETWEEN holiday_date AND COALESCE(end_date, holiday_date)
                AND (branch_id IS NULL OR branch_id = ?)
                LIMIT 1";
        $row = fetchOne(executeQuery($sql, 'si', [$date, $branchId]));
        return !empty($row);
    }

    /**
     * Check if date is weekend per rule
     */
    public static function isWeekend($date, $rule)
    {
        if (!$rule || empty($rule['weekend_days'])) {
            return false;
        }
        $dayOfWeek = (int) date('w', strtotime($date));
        $weekendDays = array_map('intval', explode(',', $rule['weekend_days']));
        return in_array($dayOfWeek, $weekendDays, true);
    }

    /**
     * Calculate late minutes, early departure, overtime from check times
     */
    public static function calculateMetrics($checkIn, $checkOut, $rule)
    {
        $result = [
            'late_minutes' => 0,
            'early_departure_minutes' => 0,
            'overtime_minutes' => 0,
            'status' => 'Present',
        ];

        if (!$rule) {
            return $result;
        }

        $grace = (int) ($rule['grace_period_minutes'] ?? 15);
        $workStart = strtotime($rule['work_start_time']);
        $workEnd = strtotime($rule['work_end_time']);
        $otThreshold = (int) ($rule['overtime_threshold_minutes'] ?? 30);
        $halfDayHours = (float) ($rule['half_day_threshold_hours'] ?? 4);
        $breakMinutes = (int) ($rule['break_minutes'] ?? 60);

        if ($checkIn) {
            $checkInTs = strtotime($checkIn);
            $allowedStart = $workStart + ($grace * 60);
            if ($checkInTs > $allowedStart) {
                $result['late_minutes'] = (int) round(($checkInTs - $workStart) / 60);
                $result['status'] = 'Late';
            }
        }

        if ($checkOut) {
            $checkOutTs = strtotime($checkOut);
            if ($checkOutTs < $workEnd) {
                $result['early_departure_minutes'] = (int) round(($workEnd - $checkOutTs) / 60);
            }
        }

        if ($checkIn && $checkOut) {
            $workedMinutes = (strtotime($checkOut) - strtotime($checkIn)) / 60 - $breakMinutes;
            $standardMinutes = ($workEnd - $workStart) / 60 - $breakMinutes;
            if ($workedMinutes > $standardMinutes + $otThreshold) {
                $result['overtime_minutes'] = (int) round($workedMinutes - $standardMinutes);
            }
            if ($workedMinutes / 60 < $halfDayHours) {
                $result['status'] = 'Half Day';
            }
        }

        return $result;
    }

    /**
     * Apply calculations and update staff_attendance record
     */
    public static function applyToRecord($attendanceId, $staffId, $attendanceDate, $checkIn, $checkOut, $status)
    {
        $staff = fetchOne(executeQuery("SELECT branch_id FROM staff WHERE id = ?", 'i', [$staffId]));
        if (!$staff) {
            return false;
        }

        if (self::isHoliday($attendanceDate, $staff['branch_id'])) {
            return true;
        }

        $rule = self::getRuleForStaff($staffId);
        $metrics = self::calculateMetrics($checkIn, $checkOut, $rule);

        $finalStatus = $status;
        if ($status === 'Present' && $metrics['status'] !== 'Present') {
            $finalStatus = $metrics['status'];
        }

        $sql = "UPDATE staff_attendance SET
                late_minutes = ?, early_departure_minutes = ?, overtime_minutes = ?,
                status = ?
                WHERE id = ?";
        return executeQuery($sql, 'iiisi', [
            $metrics['late_minutes'],
            $metrics['early_departure_minutes'],
            $metrics['overtime_minutes'],
            $finalStatus,
            $attendanceId,
        ]);
    }
}
