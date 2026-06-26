<?php
/**
 * Shared helpers for the Laboratory module
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * Branch filter for lab queries. Includes global records (branch_id IS NULL)
 * so seeded defaults are visible to all branches.
 *
 * @param string $alias Optional table alias (e.g. "s" for lab_sections s)
 * @param array|null $currentUser Optional user row; defaults to getCurrentUser()
 * @param bool $prepared When true, returns sql/params/types for prepared statements
 * @return array|string Prepared array or raw SQL fragment
 */
function labBranchWhere($alias = '', $currentUser = null, $prepared = true)
{
    if (hasRole(['Super Admin'])) {
        return $prepared ? ['sql' => '', 'params' => [], 'types' => ''] : '';
    }

    $user = $currentUser ?? getCurrentUser();
    $col = $alias !== '' ? "{$alias}.branch_id" : 'branch_id';

    if ($prepared) {
        return [
            'sql'    => " AND ({$col} = ? OR {$col} IS NULL)",
            'params' => [$user['branch_id']],
            'types'  => 'i',
        ];
    }

    $bid = (int)$user['branch_id'];
    return " AND ({$col} = {$bid} OR {$col} IS NULL)";
}

/**
 * Requester options for material requests (students, staff, users).
 *
 * @return array<int, array{value:string,label:string,name:string,user_id:?int}>
 */
function getLabRequesterOptions($currentUser = null)
{
    $currentUser = $currentUser ?? getCurrentUser();
    $branchCond  = labBranchWhere('', $currentUser, false);
    $userBranch  = labBranchWhere('u', $currentUser, false);
    $options     = [];
    $seenUsers   = [];

    $students = fetchAll(executeQuery(
        "SELECT id, user_id, first_name, last_name, admission_no
         FROM students WHERE status = 'Active'" . $branchCond . "
         ORDER BY first_name, last_name"
    ));
    foreach ($students as $s) {
        $name  = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
        $extra = $s['admission_no'] ? ' - ' . $s['admission_no'] : '';
        $uid   = !empty($s['user_id']) ? (int)$s['user_id'] : null;
        $value = $uid ? 'u:' . $uid : 's:' . (int)$s['id'];
        $options[] = [
            'value'   => $value,
            'label'   => $name . ' (Student' . $extra . ')',
            'name'    => $name,
            'user_id' => $uid,
        ];
        if ($uid) {
            $seenUsers[$uid] = true;
        }
    }

    $staffRows = fetchAll(executeQuery(
        "SELECT id, user_id, first_name, last_name, designation
         FROM staff WHERE status = 'Active'" . $branchCond . "
         ORDER BY first_name, last_name"
    ));
    foreach ($staffRows as $st) {
        $uid = !empty($st['user_id']) ? (int)$st['user_id'] : null;
        if ($uid && isset($seenUsers[$uid])) {
            continue;
        }
        $name  = trim(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? ''));
        $extra = $st['designation'] ? ' - ' . $st['designation'] : '';
        $value = $uid ? 'u:' . $uid : 'st:' . (int)$st['id'];
        $options[] = [
            'value'   => $value,
            'label'   => $name . ' (Staff' . $extra . ')',
            'name'    => $name,
            'user_id' => $uid,
        ];
        if ($uid) {
            $seenUsers[$uid] = true;
        }
    }

    $users = fetchAll(executeQuery(
        "SELECT u.id, u.username FROM users u WHERE u.is_active = 1" . $userBranch . " ORDER BY u.username"
    ));
    foreach ($users as $u) {
        $uid = (int)$u['id'];
        if (isset($seenUsers[$uid])) {
            continue;
        }
        $options[] = [
            'value'   => 'u:' . $uid,
            'label'   => $u['username'] . ' (User)',
            'name'    => $u['username'],
            'user_id' => $uid,
        ];
    }

    usort($options, fn($a, $b) => strcasecmp($a['label'], $b['label']));
    return $options;
}

/**
 * Resolve a requester dropdown value to stored requester_id and requester_name.
 *
 * @return array{requester_id:?int,requester_name:string}|null
 */
function resolveLabRequester($requesterKey)
{
    $requesterKey = trim((string)$requesterKey);
    if ($requesterKey === '') {
        return null;
    }

    if (preg_match('/^u:(\d+)$/', $requesterKey, $m)) {
        $uid = (int)$m[1];
        $row = fetchOne(executeQuery(
            "SELECT id, username FROM users WHERE id = ? AND is_active = 1",
            'i',
            [$uid]
        ));
        if (!$row) {
            return null;
        }
        return ['requester_id' => $uid, 'requester_name' => $row['username']];
    }

    if (preg_match('/^s:(\d+)$/', $requesterKey, $m)) {
        $row = fetchOne(executeQuery(
            "SELECT user_id, first_name, last_name FROM students WHERE id = ? AND status = 'Active'",
            'i',
            [(int)$m[1]]
        ));
        if (!$row) {
            return null;
        }
        $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        return [
            'requester_id'   => !empty($row['user_id']) ? (int)$row['user_id'] : null,
            'requester_name' => $name,
        ];
    }

    if (preg_match('/^st:(\d+)$/', $requesterKey, $m)) {
        $row = fetchOne(executeQuery(
            "SELECT user_id, first_name, last_name FROM staff WHERE id = ? AND status = 'Active'",
            'i',
            [(int)$m[1]]
        ));
        if (!$row) {
            return null;
        }
        $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        return [
            'requester_id'   => !empty($row['user_id']) ? (int)$row['user_id'] : null,
            'requester_name' => $name,
        ];
    }

    return null;
}

/**
 * Default requester key for the logged-in user.
 */
function getLabRequesterDefaultKey($currentUser = null)
{
    $currentUser = $currentUser ?? getCurrentUser();
    $uid         = (int)($currentUser['id'] ?? 0);
    if (!$uid) {
        return '';
    }

    foreach (getLabRequesterOptions($currentUser) as $opt) {
        if (!empty($opt['user_id']) && (int)$opt['user_id'] === $uid) {
            return $opt['value'];
        }
    }

    return 'u:' . $uid;
}

/**
 * Active users with the Teacher role for lab instructor dropdowns.
 *
 * @return array<int, array{id:int,username:string,label:string}>
 */
function getLabTeachers($currentUser = null)
{
    $rows = fetchAll(executeQuery(
        "SELECT u.id, u.username,
                TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))) AS full_name
         FROM users u
         INNER JOIN roles r ON u.role_id = r.id
         LEFT JOIN staff s ON s.user_id = u.id AND s.status = 'Active'
         WHERE u.is_active = 1 AND r.role_name = 'Teacher'
         ORDER BY full_name, u.username"
    ));

    $teachers = [];
    foreach ($rows as $row) {
        $name = trim($row['full_name'] ?? '');
        $teachers[] = [
            'id'       => (int)$row['id'],
            'username' => $row['username'],
            'label'    => $name !== '' ? $name . ' (' . $row['username'] . ')' : $row['username'],
        ];
    }

    return $teachers;
}

/**
 * Ensure instructor_id belongs to an active Teacher user.
 */
function validateLabTeacherId($userId)
{
    if (empty($userId)) {
        return true;
    }

    $row = fetchOne(executeQuery(
        "SELECT u.id FROM users u
         INNER JOIN roles r ON u.role_id = r.id
         WHERE u.id = ? AND u.is_active = 1 AND r.role_name = 'Teacher'",
        'i',
        [(int)$userId]
    ));

    return (bool)$row;
}

/**
 * Active Teacher and Admin users for safety inspector dropdowns.
 *
 * @return array<int, array{id:int,username:string,label:string,role_name:string}>
 */
function getLabInspectors($currentUser = null)
{
    $rows = fetchAll(executeQuery(
        "SELECT u.id, u.username, r.role_name,
                TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))) AS full_name
         FROM users u
         INNER JOIN roles r ON u.role_id = r.id
         LEFT JOIN staff s ON s.user_id = u.id AND s.status = 'Active'
         WHERE u.is_active = 1 AND r.role_name IN ('Teacher', 'Admin', 'Super Admin')
         ORDER BY r.role_name, full_name, u.username"
    ));

    $inspectors = [];
    foreach ($rows as $row) {
        $name = trim($row['full_name'] ?? '');
        $role = $row['role_name'] ?? 'User';
        $inspectors[] = [
            'id'        => (int)$row['id'],
            'username'  => $row['username'],
            'role_name' => $role,
            'label'     => ($name !== '' ? $name : $row['username']) . ' (' . $role . ')',
        ];
    }

    return $inspectors;
}

/**
 * Ensure inspector_id is an active Teacher or Admin user.
 */
function validateLabInspectorId($userId)
{
    if (empty($userId)) {
        return true;
    }

    $row = fetchOne(executeQuery(
        "SELECT u.id FROM users u
         INNER JOIN roles r ON u.role_id = r.id
         WHERE u.id = ? AND u.is_active = 1 AND r.role_name IN ('Teacher', 'Admin', 'Super Admin')",
        'i',
        [(int)$userId]
    ));

    return (bool)$row;
}

/**
 * Default safety checklist line items.
 *
 * @return string[]
 */
function getLabDefaultChecklistItems()
{
    return [
        'Fire extinguisher accessible and charged',
        'First aid kit stocked and accessible',
        'Emergency exits unobstructed',
        'Eye wash station functional',
        'Chemical storage properly labeled',
        'PPE available for all users',
        'Electrical panels accessible',
        'Ventilation system operational',
        'Waste disposal containers in place',
        'Safety signage visible and legible',
    ];
}

/** Lab staff roles (full LAB Management menu). */
function labStaffRoles(): array
{
    return ['Super Admin', 'Admin', 'Lab Director', 'Lab Manager', 'Lab Technician', 'Safety Officer', 'Procurement Officer', 'Maintenance Officer'];
}

/** Teacher lab pages (portal + staff oversight). */
function labTeacherRoles(): array
{
    return array_values(array_unique(array_merge(labStaffRoles(), ['Teacher'])));
}

/** Student lab pages (portal + staff oversight). */
function labStudentRoles(): array
{
    return array_values(array_unique(array_merge(labStaffRoles(), ['Student'])));
}

/** Lab pages available to both teachers and students. */
function labParticipantRoles(): array
{
    return array_values(array_unique(array_merge(labTeacherRoles(), ['Student'])));
}

function requireLabRoles(array $roles, ?string $redirectUrl = null): void
{
    requireLogin();
    if (!hasRole($roles)) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        redirect($redirectUrl ?? APP_URL . 'dashboard.php');
    }
}
