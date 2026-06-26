<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }

    $currentUser = getCurrentUser();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $applicationId = (int)($data['application_id'] ?? $data['id'] ?? 0);
    $status = sanitize($data['status'] ?? '');
    $approvalAction = sanitize($data['approval_action'] ?? '');
    $rejectionReason = sanitize($data['rejection_reason'] ?? '');

    if (!$applicationId) {
        jsonResponse(false, 'Application ID is required');
    }

    $canApprove = hasRole(['Super Admin', 'Admin'])
        || (function_exists('canPerform') && canPerform('hr_leave', 'approve'));

    $staffRow = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));
    $myStaffId = (int)($staffRow['id'] ?? 0);

    $application = fetchOne(executeQuery(
        "SELECT la.*, s.branch_id, s.user_id AS staff_user_id
         FROM leave_applications la
         INNER JOIN staff s ON la.staff_id = s.id WHERE la.id = ?",
        'i',
        [$applicationId]
    ));

    if (!$application) {
        jsonResponse(false, 'Leave application not found');
    }

    $currentStage = $application['approval_stage'] ?? $application['status'];

    if ($approvalAction === 'manager_approve') {
        if (!$canApprove) {
            jsonResponse(false, 'Permission denied');
        }
        if ($currentStage !== 'Pending') {
            jsonResponse(false, 'Leave is not pending manager approval');
        }

        $policy = LeaveBalanceService::getPolicy($application['leave_type_id'], $application['branch_id']);
        $needsHrApproval = $policy ? (bool)$policy['requires_hr_approval'] : true;

        if ($needsHrApproval) {
            executeQuery(
                "UPDATE leave_applications SET approval_stage='Manager_Approved', status='Pending',
                 manager_approved_by=?, manager_approval_date=NOW() WHERE id=?",
                'ii',
                [$currentUser['id'], $applicationId]
            );
            logActivity($currentUser['id'], 'Manager Approve Leave', 'HR', "Leave ID: $applicationId");
            jsonResponse(true, 'Approved at manager level — pending HR final approval');
        }
    }

    if ($status === 'Approved' || $approvalAction === 'hr_approve' || $approvalAction === 'manager_approve') {
        if (!$canApprove) {
            jsonResponse(false, 'Permission denied');
        }
        if (!in_array($currentStage, ['Pending', 'Manager_Approved'], true)) {
            jsonResponse(false, 'Leave cannot be approved in current stage');
        }

        $balanceCheck = LeaveBalanceService::canApplyLeave(
            (int)$application['staff_id'],
            (int)$application['leave_type_id'],
            (float)$application['total_days'],
            (int)date('Y', strtotime($application['start_date']))
        );

        if (!$balanceCheck['allowed']) {
            jsonResponse(false, $balanceCheck['message']);
        }

        executeQuery(
            "UPDATE leave_applications SET status='Approved', approval_stage='Approved',
             approved_by=?, approval_date=NOW() WHERE id=?",
            'ii',
            [$currentUser['id'], $applicationId]
        );

        LeaveBalanceService::deductLeave(
            (int)$application['staff_id'],
            (int)$application['leave_type_id'],
            (float)$application['total_days'],
            (int)date('Y', strtotime($application['start_date']))
        );

        if (!empty($application['staff_user_id']) && class_exists('NotificationService')) {
            NotificationService::send([
                'user_id' => $application['staff_user_id'],
                'title' => 'Leave Approved',
                'message' => 'Your leave application has been approved.',
                'type' => 'hr_leave',
            ]);
        }

        logActivity($currentUser['id'], 'Approve Leave', 'HR', "Approved leave ID: $applicationId");
        jsonResponse(true, 'Leave approved successfully');
    }

    if ($status === 'Rejected') {
        if (!$canApprove) {
            jsonResponse(false, 'Permission denied');
        }
        if ($rejectionReason === '') {
            jsonResponse(false, 'Rejection reason is required');
        }

        executeQuery(
            "UPDATE leave_applications SET status='Rejected', approval_stage='Rejected',
             approved_by=?, approval_date=NOW(), rejection_reason=? WHERE id=?",
            'isi',
            [$currentUser['id'], $rejectionReason, $applicationId]
        );

        if (!empty($application['staff_user_id']) && class_exists('NotificationService')) {
            NotificationService::send([
                'user_id' => $application['staff_user_id'],
                'title' => 'Leave Rejected',
                'message' => 'Your leave application was rejected. Reason: ' . $rejectionReason,
                'type' => 'hr_leave',
            ]);
        }

        logActivity($currentUser['id'], 'Reject Leave', 'HR', "Rejected leave ID: $applicationId");
        jsonResponse(true, 'Leave rejected');
    }

    if ($status === 'Cancelled') {
        $isOwner = $myStaffId > 0 && (int)$application['staff_id'] === $myStaffId;
        if (!$canApprove && !$isOwner) {
            jsonResponse(false, 'Permission denied');
        }
        if (!in_array($currentStage, ['Pending', 'Manager_Approved', 'Approved'], true)) {
            jsonResponse(false, 'This leave cannot be cancelled');
        }

        if ($currentStage === 'Approved') {
            LeaveBalanceService::restoreLeave(
                (int)$application['staff_id'],
                (int)$application['leave_type_id'],
                (float)$application['total_days'],
                (int)date('Y', strtotime($application['start_date']))
            );
        }

        executeQuery(
            "UPDATE leave_applications SET status='Cancelled', approval_stage='Cancelled' WHERE id=?",
            'i',
            [$applicationId]
        );
        logActivity($currentUser['id'], 'Cancel Leave', 'HR', "Cancelled leave ID: $applicationId");
        jsonResponse(true, 'Leave cancelled');
    }

    jsonResponse(false, 'No action performed');
} catch (Throwable $e) {
    error_log('update-leave-status.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
