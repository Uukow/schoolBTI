<?php
/**
 * Dashboard Helper Functions
 * 
 * Additional helper functions for dashboard display
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * Get time ago string
 */
if (!function_exists('timeAgo')) {
    function timeAgo($dateString) {
        $date = new DateTime($dateString);
        $now = new DateTime();
        $diff = $now->diff($date);
        
        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        }
        if ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        }
        if ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        }
        if ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }
        if ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }
        return 'Just now';
    }
}

/**
 * Get activity icon based on action
 */
if (!function_exists('getActivityIcon')) {
    function getActivityIcon($action) {
        $icons = [
            'Create' => 'add-line',
            'Update' => 'edit-line',
            'Delete' => 'delete-bin-line',
            'Login' => 'login-box-line',
            'Logout' => 'logout-box-line',
            'View' => 'eye-line',
            'Download' => 'download-line',
            'Print' => 'printer-line',
            'Generate' => 'file-list-line',
            'Approve' => 'check-line',
            'Reject' => 'close-line',
            'Send' => 'send-plane-line',
            'Upload' => 'upload-line'
        ];
        
        return $icons[$action] ?? 'file-line';
    }
}

