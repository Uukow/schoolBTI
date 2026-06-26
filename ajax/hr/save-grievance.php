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

    $id = (int)($data['id'] ?? 0);

    $action = sanitize($data['action'] ?? '');



    $canManage = hasRole(['Super Admin', 'Admin'])

        || (function_exists('canPerform') && canPerform('hr_grievances', 'manage'));



    $staffRow = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));

    $myStaffId = (int)($staffRow['id'] ?? 0);



    if ($id > 0 && $action === 'add_note') {

        if (!$canManage) {

            jsonResponse(false, 'Permission denied');

        }

        $comment = sanitize($data['comment'] ?? '');

        if ($comment === '') {

            jsonResponse(false, 'Comment is required');

        }

        $isInternal = (int)($data['is_internal'] ?? 0);

        executeQuery(

            "INSERT INTO hr_grievance_actions (grievance_id, action_by, action_type, comment, is_internal)

             VALUES (?, ?, 'Note', ?, ?)",

            'iisi',

            [$id, $currentUser['id'], $comment, $isInternal]

        );

        jsonResponse(true, 'Note added');

    }



    if ($id > 0 && $canManage) {

        $allowedStatus = ['Submitted', 'Under_Review', 'Investigating', 'Resolved', 'Closed', 'Escalated'];

        $status = sanitize($data['status'] ?? '');

        $resolution = sanitize($data['resolution'] ?? '');

        $assignedTo = isset($data['assigned_to']) && $data['assigned_to'] !== '' ? (int)$data['assigned_to'] : null;



        if ($status && !in_array($status, $allowedStatus, true)) {

            jsonResponse(false, 'Invalid status');

        }



        if ($status) {

            executeQuery(

                "UPDATE hr_grievances SET status = ?, resolution = COALESCE(NULLIF(?, ''), resolution),

                 assigned_to = COALESCE(?, assigned_to),

                 resolved_at = IF(? IN ('Resolved','Closed'), NOW(), resolved_at) WHERE id = ?",

                'ssisi',

                [$status, $resolution, $assignedTo, $status, $id]

            );

            executeQuery(

                "INSERT INTO hr_grievance_actions (grievance_id, action_by, action_type, comment, is_internal)

                 VALUES (?, ?, ?, ?, 0)",

                'iiss',

                [$id, $currentUser['id'], $status, $resolution ?: 'Status updated to ' . str_replace('_', ' ', $status)]

            );

            if (in_array($status, ['Resolved', 'Closed'], true)) {

                $g = fetchOne(executeQuery("SELECT staff_id, is_anonymous, grievance_no FROM hr_grievances WHERE id = ?", 'i', [$id]));

                if ($g && !empty($g['staff_id']) && empty($g['is_anonymous']) && class_exists('NotificationService')) {

                    $staffUser = fetchOne(executeQuery("SELECT user_id FROM staff WHERE id = ?", 'i', [$g['staff_id']]));

                    if (!empty($staffUser['user_id'])) {

                        NotificationService::send([

                            'user_id' => (int)$staffUser['user_id'],

                            'title' => 'Grievance Update',

                            'message' => "Your grievance {$g['grievance_no']} has been marked as " . str_replace('_', ' ', $status),

                            'type' => 'hr_grievances',

                        ]);

                    }

                }

            }

            logActivity($currentUser['id'], 'Update Grievance', 'HR', "Grievance ID $id → $status");

            jsonResponse(true, 'Grievance updated successfully');

        }



        if ($assignedTo !== null) {

            executeQuery("UPDATE hr_grievances SET assigned_to = ? WHERE id = ?", 'ii', [$assignedTo, $id]);

            executeQuery(

                "INSERT INTO hr_grievance_actions (grievance_id, action_by, action_type, comment) VALUES (?, ?, 'Assigned', ?)",

                'iis',

                [$id, $currentUser['id'], 'Case assigned to officer']

            );

            jsonResponse(true, 'Officer assigned');

        }

    }



    if ($id > 0) {

        jsonResponse(false, 'Invalid update request');

    }



    $isAnonymous = (int)($data['is_anonymous'] ?? 0);

    $category = sanitize($data['category'] ?? 'Other');

    $subject = sanitize($data['subject'] ?? '');

    $description = sanitize($data['description'] ?? '');

    $priority = sanitize($data['priority'] ?? 'Medium');



    if (empty($subject) || empty($description)) {

        jsonResponse(false, 'Subject and description are required');

    }



    $allowedCat = ['Harassment', 'Discrimination', 'Working_Conditions', 'Payroll', 'Other'];

    $allowedPri = ['Low', 'Medium', 'High', 'Critical'];

    if (!in_array($category, $allowedCat, true)) {

        $category = 'Other';

    }

    if (!in_array($priority, $allowedPri, true)) {

        $priority = 'Medium';

    }



    $staffId = $myStaffId > 0 ? $myStaffId : null;

    if (!$staffId && !$canManage) {

        jsonResponse(false, 'Your account is not linked to a staff record');

    }



    $grievanceNo = HrNumberService::next('GRV-', 'hr_grievances', 'grievance_no');

    $branchId = $currentUser['branch_id'] ?? null;



    $stmt = executeQuery(

        "INSERT INTO hr_grievances (grievance_no, staff_id, is_anonymous, category, subject, description, priority, branch_id, status)

         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Submitted')",

        'siissssi',

        [$grievanceNo, $staffId, $isAnonymous, $category, $subject, $description, $priority, $branchId]

    );



    if ($stmt) {

        $newId = (int)getDBConnection()->insert_id;

        executeQuery(

            "INSERT INTO hr_grievance_actions (grievance_id, action_by, action_type, comment) VALUES (?, ?, 'Submitted', ?)",

            'iis',

            [$newId, $currentUser['id'], 'Grievance submitted' . ($isAnonymous ? ' (anonymous to HR)' : '')]

        );

        if (class_exists('NotificationService')) {

            NotificationService::notifyHrAdmins($branchId, 'New Grievance', "Grievance $grievanceNo submitted ($priority)", 'hr_grievances');

        }

        logActivity($currentUser['id'], 'Submit Grievance', 'HR', "Grievance: $grievanceNo");

        jsonResponse(true, 'Grievance submitted successfully', ['grievance_no' => $grievanceNo]);

    }



    global $conn;

    jsonResponse(false, 'Failed to submit grievance: ' . ($conn->error ?? 'Unknown error'));

} catch (Throwable $e) {

    error_log('save-grievance.php: ' . $e->getMessage());

    jsonResponse(false, 'Server error: ' . $e->getMessage());

}

