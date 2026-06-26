/**
 * Permission Management JavaScript
 * 
 * Client-side permission checking and UI control
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

(function($) {
    'use strict';
    
    // Cache user permissions
    let userPermissions = null;
    
    /**
     * Initialize permissions system
     */
    function initPermissions() {
        // Get permissions from data attribute if available
        const permissionsData = $('body').data('permissions');
        if (permissionsData) {
            userPermissions = typeof permissionsData === 'string' ? JSON.parse(permissionsData) : permissionsData;
        }
        
        // Apply permission-based visibility
        applyPermissionVisibility();
    }
    
    /**
     * Check if user can perform action (client-side)
     * 
     * @param {string} moduleKey Module key
     * @param {string} actionKey Action key
     * @returns {boolean} True if user has permission
     */
    function canPerform(moduleKey, actionKey) {
        if (!userPermissions) {
            return false;
        }
        
        // Super Admin check (if available)
        if (window.currentUser && window.currentUser.role_name === 'Super Admin') {
            return true;
        }
        
        return userPermissions[moduleKey] && userPermissions[moduleKey][actionKey] === true;
    }
    
    /**
     * Apply permission-based visibility to elements
     */
    function applyPermissionVisibility() {
        // Hide elements with data-permission attributes
        $('[data-permission]').each(function() {
            const $el = $(this);
            const permission = $el.data('permission');
            
            if (typeof permission === 'string') {
                try {
                    permission = JSON.parse(permission);
                } catch (e) {
                    console.error('Invalid permission data:', permission);
                    return;
                }
            }
            
            const moduleKey = permission.module;
            const actionKey = permission.action;
            
            if (!canPerform(moduleKey, actionKey)) {
                // Check if element should be hidden or disabled
                const action = $el.data('permission-action') || 'hide';
                
                if (action === 'disable') {
                    $el.prop('disabled', true);
                    $el.addClass('disabled');
                    $el.attr('title', 'You do not have permission to perform this action');
                } else {
                    $el.hide();
                }
            }
        });
    }
    
    /**
     * Show/hide element based on permission
     * 
     * @param {string} moduleKey Module key
     * @param {string} actionKey Action key
     * @param {jQuery} $element Element to show/hide
     * @param {string} action 'hide' or 'disable'
     */
    function togglePermissionElement(moduleKey, actionKey, $element, action = 'hide') {
        if (canPerform(moduleKey, actionKey)) {
            if (action === 'disable') {
                $element.prop('disabled', false);
                $element.removeClass('disabled');
            } else {
                $element.show();
            }
        } else {
            if (action === 'disable') {
                $element.prop('disabled', true);
                $element.addClass('disabled');
            } else {
                $element.hide();
            }
        }
    }
    
    /**
     * Create permission-aware button
     * 
     * @param {string} moduleKey Module key
     * @param {string} actionKey Action key
     * @param {string} text Button text
     * @param {string} url Button URL
     * @param {string} className CSS class
     * @param {string} icon Icon class
     * @returns {jQuery} Button element
     */
    function createPermissionButton(moduleKey, actionKey, text, url = '#', className = 'btn btn-primary', icon = '') {
        const hasPermission = canPerform(moduleKey, actionKey);
        const iconHtml = icon ? `<i class="${icon}"></i> ` : '';
        const disabledClass = hasPermission ? '' : ' disabled';
        const disabledAttr = hasPermission ? '' : ' disabled';
        
        if (url === '#') {
            return $(`<button type="button" class="${className}${disabledClass}" ${disabledAttr} data-permission='{"module":"${moduleKey}","action":"${actionKey}"}'>${iconHtml}${text}</button>`);
        } else {
            return $(`<a href="${url}" class="${className}${disabledClass}" ${disabledAttr} data-permission='{"module":"${moduleKey}","action":"${actionKey}"}'>${iconHtml}${text}</a>`);
        }
    }
    
    // Expose functions globally
    window.PermissionManager = {
        canPerform: canPerform,
        toggleElement: togglePermissionElement,
        createButton: createPermissionButton,
        init: initPermissions
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        initPermissions();
    });
    
})(jQuery);

