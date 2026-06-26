<?php
/**
 * HR API auth helper — resolves user from user_id query param
 */
function hrApiAuth()
{
    $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
    if (!$userId) {
        sendApiResponse(false, 'User ID is required', null, 400);
    }
    $user = fetchOne(executeQuery(
        "SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?",
        'i', [(int)$userId]
    ));
    if (!$user) {
        sendApiResponse(false, 'User not found', null, 404);
    }
    $isSuperAdmin = ($user['role_name'] ?? '') === 'Super Admin';
    $branchId = $isSuperAdmin
        ? (isset($_GET['branch_id']) && $_GET['branch_id'] !== '' && $_GET['branch_id'] !== 'null' ? (int)$_GET['branch_id'] : null)
        : ($user['branch_id'] ?? null);
    $staff = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$user['id']]));
    return [
        'user' => $user,
        'role' => $user['role_name'] ?? '',
        'is_super_admin' => $isSuperAdmin,
        'branch_id' => $branchId,
        'staff_id' => $staff['id'] ?? null,
    ];
}

function hrApiBranchFilter($auth, $alias = 's')
{
    if ($auth['is_super_admin'] || !$auth['branch_id']) {
        return ['sql' => '', 'params' => [], 'types' => ''];
    }
    return ['sql' => " AND {$alias}.branch_id = ?", 'params' => [$auth['branch_id']], 'types' => 'i'];
}
