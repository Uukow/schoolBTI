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



    $canManage = hasRole(['Super Admin', 'Admin'])

        || (function_exists('canPerform') && canPerform('hr_ppdp', 'manage'));



    // Quick status-only update

    if ($id > 0 && !empty($data['status']) && empty($data['program_name'])) {

        if (!$canManage) {

            jsonResponse(false, 'Permission denied');

        }

        $allowed = ['Planned', 'Open', 'In_Progress', 'Completed', 'Cancelled'];

        $newStatus = sanitize($data['status']);

        if (!in_array($newStatus, $allowed, true)) {

            jsonResponse(false, 'Invalid status');

        }

        executeQuery("UPDATE hr_ppdp_programs SET status = ? WHERE id = ?", 'si', [$newStatus, $id]);

        logActivity($currentUser['id'], 'Update PPDP Status', 'HR', "Program ID $id → $newStatus");

        jsonResponse(true, 'Program status updated');

    }



    // Staff self-registration

    if ($id > 0 && !empty($data['register_staff_id'])) {

        $staffId = (int)$data['register_staff_id'];

        $program = fetchOne(executeQuery("SELECT * FROM hr_ppdp_programs WHERE id = ?", 'i', [$id]));

        if (!$program) {

            jsonResponse(false, 'Program not found');

        }

        if ($program['status'] !== 'Open') {

            jsonResponse(false, 'Registration is only open for programs with Open status');

        }

        $count = (int)(fetchOne(executeQuery(

            "SELECT COUNT(*) AS c FROM hr_ppdp_participants WHERE program_id = ?",

            'i', [$id]

        ))['c'] ?? 0);

        if ($count >= (int)$program['capacity']) {

            jsonResponse(false, 'Program has reached maximum capacity');

        }

        $existing = fetchOne(executeQuery(

            "SELECT id FROM hr_ppdp_participants WHERE program_id = ? AND staff_id = ?",

            'ii', [$id, $staffId]

        ));

        if ($existing) {

            jsonResponse(false, 'You are already registered for this program');

        }

        if (!$canManage && $staffId !== (int)(fetchOne(executeQuery(

            "SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]

        ))['id'] ?? 0)) {

            jsonResponse(false, 'Permission denied');

        }

        executeQuery(

            "INSERT INTO hr_ppdp_participants (program_id, staff_id, registration_date, status)

             VALUES (?, ?, CURDATE(), 'Registered')",

            'ii', [$id, $staffId]

        );

        logActivity($currentUser['id'], 'PPDP Registration', 'HR', "Staff $staffId registered for program $id");

        jsonResponse(true, 'Successfully registered for the program');

    }



    if ($id > 0 && $canManage) {

        $name = sanitize($data['program_name'] ?? '');

        $start = sanitize($data['start_date'] ?? '');

        $end = sanitize($data['end_date'] ?? '');

        if (empty($name) || empty($start) || empty($end)) {

            jsonResponse(false, 'Program name and dates are required');

        }

        if ($end < $start) {

            jsonResponse(false, 'End date cannot be before start date');

        }

        $branchId = isset($data['branch_id']) && $data['branch_id'] !== '' ? (int)$data['branch_id'] : null;

        $facilitatorId = isset($data['facilitator_id']) && $data['facilitator_id'] !== '' ? (int)$data['facilitator_id'] : null;

        $capacity = max(1, (int)($data['capacity'] ?? 30));

        $status = sanitize($data['status'] ?? 'Planned');

        $stmt = executeQuery(

            "UPDATE hr_ppdp_programs SET program_name=?, description=?, start_date=?, end_date=?,

             capacity=?, branch_id=?, facilitator_id=?, status=? WHERE id=?",

            'ssssiiisi',

            [

                $name,

                sanitize($data['description'] ?? ''),

                $start,

                $end,

                $capacity,

                $branchId,

                $facilitatorId,

                $status,

                $id,

            ]

        );

        if ($stmt) {

            logActivity($currentUser['id'], 'Update PPDP Program', 'HR', "Program ID: $id");

            jsonResponse(true, 'Program updated successfully');

        }

        jsonResponse(false, 'Failed to update program');

    }



    if (!$canManage) {

        jsonResponse(false, 'Permission denied');

    }



    $name = sanitize($data['program_name'] ?? '');

    $start = sanitize($data['start_date'] ?? '');

    $end = sanitize($data['end_date'] ?? '');

    if (empty($name) || empty($start) || empty($end)) {

        jsonResponse(false, 'Program name and dates are required');

    }

    if ($end < $start) {

        jsonResponse(false, 'End date cannot be before start date');

    }



    $branchId = isset($data['branch_id']) && $data['branch_id'] !== ''

        ? (int)$data['branch_id']

        : ($currentUser['branch_id'] ?? null);

    $facilitatorId = isset($data['facilitator_id']) && $data['facilitator_id'] !== '' ? (int)$data['facilitator_id'] : null;

    $capacity = max(1, (int)($data['capacity'] ?? 30));

    $status = sanitize($data['status'] ?? 'Planned');



    $code = HrNumberService::next('PPDP-', 'hr_ppdp_programs', 'program_code');

    $stmt = executeQuery(

        "INSERT INTO hr_ppdp_programs (program_code, program_name, description, start_date, end_date,

         capacity, branch_id, facilitator_id, status)

         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",

        'sssssiiis',

        [

            $code,

            $name,

            sanitize($data['description'] ?? ''),

            $start,

            $end,

            $capacity,

            $branchId,

            $facilitatorId,

            $status,

        ]

    );



    if ($stmt) {

        logActivity($currentUser['id'], 'Create PPDP Program', 'HR', "Program: $code");

        jsonResponse(true, 'Program created successfully', ['program_code' => $code]);

    }



    global $conn;

    jsonResponse(false, 'Failed to create program: ' . ($conn->error ?? 'Unknown error'));

} catch (Throwable $e) {

    error_log('save-ppdp-program.php: ' . $e->getMessage());

    jsonResponse(false, 'Server error: ' . $e->getMessage());

}

