<?php
/**
 * Permission Helper Functions
 * 
 * UI helper functions for showing/hiding elements based on permissions
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * Check if current user can perform action and return button HTML
 * 
 * @param string $moduleKey Module key
 * @param string $actionKey Action key
 * @param string $buttonHtml Button HTML to show if permitted
 * @param string $alternativeHtml Alternative HTML to show if not permitted (optional)
 * @return string Button HTML or empty string
 */
function permissionButton($moduleKey, $actionKey, $buttonHtml, $alternativeHtml = '') {
    if (canPerform($moduleKey, $actionKey)) {
        return $buttonHtml;
    }
    return $alternativeHtml;
}

/**
 * Check if current user can perform action and return link HTML
 * 
 * @param string $moduleKey Module key
 * @param string $actionKey Action key
 * @param string $linkHtml Link HTML to show if permitted
 * @param string $alternativeHtml Alternative HTML to show if not permitted (optional)
 * @return string Link HTML or empty string
 */
function permissionLink($moduleKey, $actionKey, $linkHtml, $alternativeHtml = '') {
    return permissionButton($moduleKey, $actionKey, $linkHtml, $alternativeHtml);
}

/**
 * Get permission data attribute for JavaScript
 * 
 * @param string $moduleKey Module key
 * @param string $actionKey Action key
 * @return string Data attribute string
 */
function permissionDataAttr($moduleKey, $actionKey) {
    $hasPermission = canPerform($moduleKey, $actionKey) ? 'true' : 'false';
    return "data-permission='{\"module\":\"{$moduleKey}\",\"action\":\"{$actionKey}\",\"granted\":{$hasPermission}}'";
}

/**
 * Get permission class for styling
 * 
 * @param string $moduleKey Module key
 * @param string $actionKey Action key
 * @param string $grantedClass Class to add if granted
 * @param string $deniedClass Class to add if denied
 * @return string Class string
 */
function permissionClass($moduleKey, $actionKey, $grantedClass = '', $deniedClass = 'disabled') {
    if (canPerform($moduleKey, $actionKey)) {
        return $grantedClass;
    }
    return $deniedClass;
}

/**
 * Check if user can perform action and return boolean for inline conditions
 * 
 * @param string $moduleKey Module key
 * @param string $actionKey Action key
 * @return bool True if permitted
 */
function can($moduleKey, $actionKey) {
    return canPerform($moduleKey, $actionKey);
}

/**
 * Generate permission-aware button
 * 
 * @param string $moduleKey Module key
 * @param string $actionKey Action key
 * @param string $text Button text
 * @param string $url Button URL (for links) or '#' for buttons
 * @param string $class Additional CSS classes
 * @param string $icon Icon class (optional)
 * @param array $attributes Additional HTML attributes
 * @return string Button HTML
 */
function permissionAwareButton($moduleKey, $actionKey, $text, $url = '#', $class = 'btn btn-primary', $icon = '', $attributes = []) {
    if (!canPerform($moduleKey, $actionKey)) {
        // Return disabled button
        $disabledClass = $class . ' disabled';
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= " {$key}=\"" . htmlspecialchars($value) . "\"";
        }
        $iconHtml = $icon ? "<i class=\"{$icon}\"></i> " : '';
        return "<a href=\"#\" class=\"{$disabledClass}\" disabled {$attrString} title=\"You do not have permission to perform this action\">{$iconHtml}{$text}</a>";
    }
    
    // Return enabled button
    $attrString = '';
    foreach ($attributes as $key => $value) {
        $attrString .= " {$key}=\"" . htmlspecialchars($value) . "\"";
    }
    $iconHtml = $icon ? "<i class=\"{$icon}\"></i> " : '';
    
    if ($url === '#') {
        return "<button type=\"button\" class=\"{$class}\" {$attrString}>{$iconHtml}{$text}</button>";
    } else {
        return "<a href=\"{$url}\" class=\"{$class}\" {$attrString}>{$iconHtml}{$text}</a>";
    }
}

/**
 * Generate permission-aware action buttons (View, Edit, Delete, etc.)
 * 
 * @param array $actions Array of actions ['view' => ['url' => '...', 'text' => '...'], ...]
 * @param string $moduleKey Module key
 * @return string Buttons HTML
 */
function permissionActionButtons($actions, $moduleKey) {
    $html = '<div class="btn-group" role="group">';
    
    foreach ($actions as $actionKey => $actionData) {
        $url = $actionData['url'] ?? '#';
        $text = $actionData['text'] ?? ucfirst($actionKey);
        $class = $actionData['class'] ?? 'btn btn-sm btn-outline-primary';
        $icon = $actionData['icon'] ?? '';
        
        if (canPerform($moduleKey, $actionKey)) {
            $iconHtml = $icon ? "<i class=\"{$icon}\"></i> " : '';
            $html .= "<a href=\"{$url}\" class=\"{$class}\">{$iconHtml}{$text}</a>";
        }
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Get all user permissions as JSON for JavaScript
 * 
 * @return string JSON string of user permissions
 */
function getUserPermissionsJson() {
    $permissions = getUserPermissions();
    return json_encode($permissions);
}

