<?php
/**
 * Permission Manager
 * 
 * Centralized permission management system for granular action-based permissions
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

class PermissionManager {
    
    private static $permissionCache = [];
    private static $userPermissionsCache = [];
    
    /**
     * Check if user can perform an action on a module
     * 
     * Priority order:
     * 1. User-specific overrides (highest priority)
     * 2. Role-based permissions
     * 3. Super Admin always has access
     * 
     * @param int|null $userId User ID (null = current user)
     * @param string $moduleKey Module key (e.g., 'students', 'fees')
     * @param string $actionKey Action key (e.g., 'create', 'view', 'update')
     * @return bool True if user has permission, false otherwise
     */
    public static function canPerform($userId, $moduleKey, $actionKey) {
        // Get current user if not specified
        if ($userId === null) {
            $user = getCurrentUser();
            if (!$user) {
                return false;
            }
            $userId = $user['id'];
        }
        
        // Check cache first
        $cacheKey = "{$userId}_{$moduleKey}_{$actionKey}";
        if (isset(self::$permissionCache[$cacheKey])) {
            return self::$permissionCache[$cacheKey];
        }
        
        // Super Admin always has access
        $user = getCurrentUser();
        if ($user && isset($user['role_name']) && $user['role_name'] === 'Super Admin') {
            self::$permissionCache[$cacheKey] = true;
            return true;
        }
        
        // Get module and action IDs
        $moduleId = self::getModuleId($moduleKey);
        $actionId = self::getActionId($actionKey);
        
        if (!$moduleId || !$actionId) {
            self::$permissionCache[$cacheKey] = false;
            return false;
        }
        
        // Check user-specific overrides first (highest priority)
        $override = self::getUserOverride($userId, $moduleId, $actionId);
        if ($override !== null) {
            $result = (bool)$override;
            self::$permissionCache[$cacheKey] = $result;
            return $result;
        }
        
        // Check role-based permissions
        $hasPermission = self::hasRolePermission($userId, $moduleId, $actionId);
        self::$permissionCache[$cacheKey] = $hasPermission;
        
        return $hasPermission;
    }
    
    /**
     * Get user-specific override for a module-action combination
     * 
     * @param int $userId User ID
     * @param int $moduleId Module ID
     * @param int $actionId Action ID
     * @return int|null 1 if granted, 0 if denied, null if no override
     */
    private static function getUserOverride($userId, $moduleId, $actionId) {
        $sql = "SELECT granted FROM user_action_overrides 
                WHERE user_id = ? AND module_id = ? AND action_id = ? 
                LIMIT 1";
        
        $stmt = executeQuery($sql, 'iii', [$userId, $moduleId, $actionId]);
        $result = fetchOne($stmt);
        
        return $result ? (int)$result['granted'] : null;
    }
    
    /**
     * Check if user's role has permission for a module-action
     * 
     * @param int $userId User ID
     * @param int $moduleId Module ID
     * @param int $actionId Action ID
     * @return bool True if role has permission
     */
    private static function hasRolePermission($userId, $moduleId, $actionId) {
        $sql = "SELECT COUNT(*) as count 
                FROM role_action_permissions rap
                INNER JOIN users u ON u.role_id = rap.role_id
                WHERE u.id = ? AND rap.module_id = ? AND rap.action_id = ? AND rap.granted = 1";
        
        $stmt = executeQuery($sql, 'iii', [$userId, $moduleId, $actionId]);
        $result = fetchOne($stmt);
        
        return $result && $result['count'] > 0;
    }
    
    /**
     * Get module ID by key
     * 
     * @param string $moduleKey Module key
     * @return int|null Module ID or null if not found
     */
    public static function getModuleId($moduleKey) {
        static $moduleCache = [];
        
        if (isset($moduleCache[$moduleKey])) {
            return $moduleCache[$moduleKey];
        }
        
        $sql = "SELECT id FROM modules WHERE module_key = ? AND is_active = 1 LIMIT 1";
        $stmt = executeQuery($sql, 's', [$moduleKey]);
        $result = fetchOne($stmt);
        
        $moduleId = $result ? (int)$result['id'] : null;
        $moduleCache[$moduleKey] = $moduleId;
        
        return $moduleId;
    }
    
    /**
     * Get action ID by key
     * 
     * @param string $actionKey Action key
     * @return int|null Action ID or null if not found
     */
    public static function getActionId($actionKey) {
        static $actionCache = [];
        
        if (isset($actionCache[$actionKey])) {
            return $actionCache[$actionKey];
        }
        
        $sql = "SELECT id FROM actions WHERE action_key = ? AND is_active = 1 LIMIT 1";
        $stmt = executeQuery($sql, 's', [$actionKey]);
        $result = fetchOne($stmt);
        
        $actionId = $result ? (int)$result['id'] : null;
        $actionCache[$actionKey] = $actionId;
        
        return $actionId;
    }
    
    /**
     * Get all permissions for a role
     * 
     * @param int $roleId Role ID
     * @return array Array of permissions [module_key => [action_key => granted]]
     */
    public static function getRolePermissions($roleId) {
        $sql = "SELECT m.module_key, a.action_key, rap.granted
                FROM role_action_permissions rap
                INNER JOIN modules m ON rap.module_id = m.id
                INNER JOIN actions a ON rap.action_id = a.id
                WHERE rap.role_id = ?
                ORDER BY m.display_order, a.display_order";
        
        $stmt = executeQuery($sql, 'i', [$roleId]);
        $permissions = fetchAll($stmt);
        
        $result = [];
        foreach ($permissions as $perm) {
            $moduleKey = $perm['module_key'];
            $actionKey = $perm['action_key'];
            
            if (!isset($result[$moduleKey])) {
                $result[$moduleKey] = [];
            }
            
            $result[$moduleKey][$actionKey] = (bool)$perm['granted'];
        }
        
        return $result;
    }
    
    /**
     * Get all permissions for a user (role + overrides)
     * 
     * @param int $userId User ID
     * @return array Array of permissions [module_key => [action_key => granted]]
     */
    public static function getUserPermissions($userId) {
        // Check cache
        if (isset(self::$userPermissionsCache[$userId])) {
            return self::$userPermissionsCache[$userId];
        }
        
        // Get user's role
        $user = self::getUserData($userId);
        if (!$user) {
            return [];
        }
        
        // Start with role permissions
        $permissions = self::getRolePermissions($user['role_id']);
        
        // Apply user-specific overrides
        $sql = "SELECT m.module_key, a.action_key, uao.granted
                FROM user_action_overrides uao
                INNER JOIN modules m ON uao.module_id = m.id
                INNER JOIN actions a ON uao.action_id = a.id
                WHERE uao.user_id = ?";
        
        $stmt = executeQuery($sql, 'i', [$userId]);
        $overrides = fetchAll($stmt);
        
        foreach ($overrides as $override) {
            $moduleKey = $override['module_key'];
            $actionKey = $override['action_key'];
            
            if (!isset($permissions[$moduleKey])) {
                $permissions[$moduleKey] = [];
            }
            
            $permissions[$moduleKey][$actionKey] = (bool)$override['granted'];
        }
        
        self::$userPermissionsCache[$userId] = $permissions;
        
        return $permissions;
    }
    
    /**
     * Get user data by ID
     * 
     * @param int $userId User ID
     * @return array|null User data
     */
    private static function getUserData($userId) {
        // If checking current user, use getCurrentUser for efficiency
        $currentUser = getCurrentUser();
        if ($currentUser && $currentUser['id'] == $userId) {
            return [
                'id' => $currentUser['id'],
                'role_id' => $currentUser['role_id'],
                'role_name' => $currentUser['role_name'] ?? null
            ];
        }
        
        // Otherwise query database
        $sql = "SELECT u.id, u.role_id, r.role_name FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ? AND u.is_active = 1
                LIMIT 1";
        
        $stmt = executeQuery($sql, 'i', [$userId]);
        return fetchOne($stmt);
    }
    
    /**
     * Save role permissions
     * 
     * @param int $roleId Role ID
     * @param array $permissions Array of [module_key => [action_key => granted]]
     * @param int $changedBy User ID who made the change
     * @return bool Success status
     */
    public static function saveRolePermissions($roleId, $permissions, $changedBy = null) {
        beginTransaction();
        
        try {
            // Get old permissions for audit log
            $oldPermissions = self::getRolePermissions($roleId);
            
            // Delete existing permissions
            $deleteSql = "DELETE FROM role_action_permissions WHERE role_id = ?";
            executeQuery($deleteSql, 'i', [$roleId]);
            
            // Insert new permissions
            $insertSql = "INSERT INTO role_action_permissions (role_id, module_id, action_id, granted) 
                         VALUES (?, ?, ?, ?)";
            
            foreach ($permissions as $moduleKey => $actions) {
                $moduleId = self::getModuleId($moduleKey);
                if (!$moduleId) continue;
                
                foreach ($actions as $actionKey => $granted) {
                    $actionId = self::getActionId($actionKey);
                    if (!$actionId) continue;
                    
                    executeQuery($insertSql, 'iiii', [
                        $roleId,
                        $moduleId,
                        $actionId,
                        $granted ? 1 : 0
                    ]);
                }
            }
            
            // Log audit trail
            if ($changedBy) {
                self::logPermissionChange(
                    $changedBy,
                    'role',
                    $roleId,
                    null,
                    null,
                    'grant',
                    json_encode($oldPermissions),
                    json_encode($permissions),
                    "Updated permissions for role ID: {$roleId}"
                );
            }
            
            // Clear cache
            self::clearCache();
            
            commitTransaction();
            return true;
            
        } catch (Exception $e) {
            rollbackTransaction();
            error_log("PermissionManager::saveRolePermissions Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set user-specific permission override
     * 
     * @param int $userId User ID
     * @param string $moduleKey Module key
     * @param string $actionKey Action key
     * @param bool $granted Whether to grant or deny
     * @param int $changedBy User ID who made the change
     * @return bool Success status
     */
    public static function setUserOverride($userId, $moduleKey, $actionKey, $granted, $changedBy = null) {
        $moduleId = self::getModuleId($moduleKey);
        $actionId = self::getActionId($actionKey);
        
        if (!$moduleId || !$actionId) {
            return false;
        }
        
        $sql = "INSERT INTO user_action_overrides (user_id, module_id, action_id, granted, created_by)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE granted = VALUES(granted), updated_at = NOW()";
        
        $result = executeQuery($sql, 'iiiii', [
            $userId,
            $moduleId,
            $actionId,
            $granted ? 1 : 0,
            $changedBy
        ]);
        
        if ($result) {
            // Log audit trail
            if ($changedBy) {
                self::logPermissionChange(
                    $changedBy,
                    'user',
                    $userId,
                    $moduleId,
                    $actionId,
                    $granted ? 'override_grant' : 'override_revoke',
                    null,
                    json_encode(['module' => $moduleKey, 'action' => $actionKey, 'granted' => $granted]),
                    "Set override for user ID: {$userId}, {$moduleKey}.{$actionKey} = " . ($granted ? 'granted' : 'denied')
                );
            }
            
            // Clear cache
            self::clearCache();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Remove user-specific permission override
     * 
     * @param int $userId User ID
     * @param string $moduleKey Module key
     * @param string $actionKey Action key
     * @param int $changedBy User ID who made the change
     * @return bool Success status
     */
    public static function removeUserOverride($userId, $moduleKey, $actionKey, $changedBy = null) {
        $moduleId = self::getModuleId($moduleKey);
        $actionId = self::getActionId($actionKey);
        
        if (!$moduleId || !$actionId) {
            return false;
        }
        
        $sql = "DELETE FROM user_action_overrides 
                WHERE user_id = ? AND module_id = ? AND action_id = ?";
        
        $result = executeQuery($sql, 'iii', [$userId, $moduleId, $actionId]);
        
        if ($result) {
            // Log audit trail
            if ($changedBy) {
                self::logPermissionChange(
                    $changedBy,
                    'user',
                    $userId,
                    $moduleId,
                    $actionId,
                    'override_remove',
                    json_encode(['module' => $moduleKey, 'action' => $actionKey]),
                    null,
                    "Removed override for user ID: {$userId}, {$moduleKey}.{$actionKey}"
                );
            }
            
            // Clear cache
            self::clearCache();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get all modules with their actions
     * 
     * @return array Array of modules with actions
     */
    public static function getAllModulesWithActions() {
        $sql = "SELECT m.id, m.module_key, m.module_name, m.module_description, m.display_order,
                       a.id as action_id, a.action_key, a.action_name, a.action_description, a.display_order as action_order
                FROM modules m
                CROSS JOIN actions a
                WHERE m.is_active = 1 AND a.is_active = 1
                ORDER BY m.display_order, a.display_order";
        
        $stmt = executeQuery($sql);
        $results = fetchAll($stmt);
        
        $modules = [];
        foreach ($results as $row) {
            $moduleKey = $row['module_key'];
            
            if (!isset($modules[$moduleKey])) {
                $modules[$moduleKey] = [
                    'id' => $row['id'],
                    'module_key' => $moduleKey,
                    'module_name' => $row['module_name'],
                    'module_description' => $row['module_description'],
                    'display_order' => $row['display_order'],
                    'actions' => []
                ];
            }
            
            $modules[$moduleKey]['actions'][] = [
                'id' => $row['action_id'],
                'action_key' => $row['action_key'],
                'action_name' => $row['action_name'],
                'action_description' => $row['action_description'],
                'display_order' => $row['action_order']
            ];
        }
        
        return $modules;
    }
    
    /**
     * Log permission change to audit log
     * 
     * @param int $userId User who made the change
     * @param string $targetType 'role' or 'user'
     * @param int $targetId Target role or user ID
     * @param int|null $moduleId Module ID
     * @param int|null $actionId Action ID
     * @param string $changeType Type of change
     * @param string|null $oldValue Old value (JSON)
     * @param string|null $newValue New value (JSON)
     * @param string $description Description
     */
    private static function logPermissionChange($userId, $targetType, $targetId, $moduleId, $actionId, 
                                                $changeType, $oldValue, $newValue, $description) {
        $sql = "INSERT INTO permission_audit_log 
                (user_id, target_type, target_id, module_id, action_id, change_type, 
                 old_value, new_value, description, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        executeQuery($sql, 'isiiissssss', [
            $userId,
            $targetType,
            $targetId,
            $moduleId,
            $actionId,
            $changeType,
            $oldValue,
            $newValue,
            $description,
            $ipAddress,
            $userAgent
        ]);
    }
    
    /**
     * Get audit log entries
     * 
     * @param array $filters Filters (target_type, target_id, user_id, module_id, action_id, date_from, date_to)
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Audit log entries
     */
    public static function getAuditLog($filters = [], $limit = 100, $offset = 0) {
        $where = ['1=1'];
        $params = [];
        $types = '';
        
        if (!empty($filters['target_type'])) {
            $where[] = "target_type = ?";
            $params[] = $filters['target_type'];
            $types .= 's';
        }
        
        if (!empty($filters['target_id'])) {
            $where[] = "target_id = ?";
            $params[] = $filters['target_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['module_id'])) {
            $where[] = "module_id = ?";
            $params[] = $filters['module_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['action_id'])) {
            $where[] = "action_id = ?";
            $params[] = $filters['action_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }
        
        $sql = "SELECT pal.*, 
                       u.username, u.email,
                       m.module_key, m.module_name,
                       a.action_key, a.action_name
                FROM permission_audit_log pal
                LEFT JOIN users u ON pal.user_id = u.id
                LEFT JOIN modules m ON pal.module_id = m.id
                LEFT JOIN actions a ON pal.action_id = a.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY pal.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = executeQuery($sql, $types, $params);
        return fetchAll($stmt);
    }
    
    /**
     * Clear permission cache
     */
    public static function clearCache() {
        self::$permissionCache = [];
        self::$userPermissionsCache = [];
    }
}

