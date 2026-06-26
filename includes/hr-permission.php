<?php
if (!defined('ABSPATH')) exit;

/**
 * HR permission helper for AJAX endpoints.
 * Super Admin always passes; otherwise checks granular permission with Admin role fallback.
 */
function hrAjaxRequire($moduleKey, $actionKey = 'view')
{
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    if (hasRole(['Super Admin'])) {
        return getCurrentUser();
    }
    if (function_exists('canPerform') && canPerform($moduleKey, $actionKey)) {
        return getCurrentUser();
    }
    if (hasRole(['Admin'])) {
        return getCurrentUser();
    }
    jsonResponse(false, 'Permission denied');
}

function hrRequirePage($moduleKey, $actionKey = 'view', $extraRoles = [])
{
    requireLogin();
    if (hasRole(['Super Admin'])) {
        return;
    }
    if (function_exists('canPerform') && canPerform($moduleKey, $actionKey)) {
        return;
    }
    $fallbackRoles = array_merge(['Admin'], $extraRoles);
    if (hasRole($fallbackRoles)) {
        return;
    }
    $_SESSION['error'] = 'You do not have permission to access this page.';
    redirect(APP_URL . 'dashboard.php');
}

/**
 * Page access for HR modules that staff can also use (leaves, grievances, etc.)
 */
function hrRequireAccess($moduleKey, $actionKey = 'view', $extraRoles = ['Teacher', 'Staff'])
{
    hrRequirePage($moduleKey, $actionKey, $extraRoles);
}

function hrAjaxRequireAccess($moduleKey, $actionKey = 'view', $extraRoles = ['Teacher', 'Staff'])
{
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    if (hasRole(['Super Admin'])) {
        return getCurrentUser();
    }
    if (function_exists('canPerform') && canPerform($moduleKey, $actionKey)) {
        return getCurrentUser();
    }
    $fallback = array_merge(['Admin'], $extraRoles);
    if (hasRole($fallback)) {
        return getCurrentUser();
    }
    jsonResponse(false, 'Permission denied');
}
