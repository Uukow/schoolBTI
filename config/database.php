<?php
/**
 * Database Connection Handler
 * 
 * Provides MySQLi connection with proper error handling
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * Get database connection
 * 
 * @return mysqli|false Database connection object or false on failure
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        // Create connection with error suppression for initial attempt
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            $errorMsg = "Database Connection Failed: " . $conn->connect_error;
            error_log($errorMsg);
            
            // Return false instead of dying - let calling code handle it
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                // In debug mode, show error but don't die during config load
                error_log($errorMsg);
                return false;
            } else {
                // In production, log and return false
                error_log($errorMsg);
                return false;
            }
        }
        
        // Set charset
        if (!$conn->set_charset(DB_CHARSET)) {
            error_log("Error setting charset: " . $conn->error);
        }
        
        // Set timezone
        @$conn->query("SET time_zone = '+03:00'"); // East Africa Time
    }
    
    return $conn;
}

/**
 * Execute a prepared statement query
 * 
 * @param string $sql SQL query with placeholders
 * @param string $types Parameter types (i, d, s, b)
 * @param array $params Parameters to bind
 * @return mysqli_stmt|false Prepared statement or false on failure
 */
function executeQuery($sql, $types = '', $params = []) {
    global $conn;
    
    // Ensure connection exists
    if ($conn === null || $conn === false) {
        $conn = getDBConnection();
        if ($conn === false) {
            error_log("executeQuery: Database connection not available");
            return false;
        }
    }
    
    // Close any previous statement that might still be open (fixes "Commands out of sync" error)
    // This is especially important when using bind_result() without mysqlnd
    while ($conn->more_results()) {
        $conn->next_result();
        if ($result = $conn->store_result()) {
            $result->free();
        }
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Query Preparation Failed: " . $conn->error);
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Query Execution Failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    return $stmt;
}

/**
 * Fetch single row from query result
 * 
 * @param mysqli_stmt $stmt Prepared statement
 * @return array|null Associative array or null
 */
function fetchOne($stmt) {
    if (!$stmt || $stmt === false) {
        return null;
    }
    
    // Check if mysqlnd is available (get_result method exists)
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        if ($result) {
            $data = $result->fetch_assoc();
            $result->free();
            $stmt->close();
            return $data;
        }
        $stmt->close();
        return null;
    } else {
        // Fallback for systems without mysqlnd - use bind_result
        try {
            $meta = $stmt->result_metadata();
            if (!$meta) {
                $stmt->close();
                return null;
            }
            
            $fields = [];
            $row = [];
            $params = [];
            while ($field = $meta->fetch_field()) {
                $fields[] = $field->name;
                $row[$field->name] = null;
                $params[] = &$row[$field->name];
            }
            
            if (empty($fields)) {
                $meta->free();
                $stmt->close();
                return null;
            }
            
            call_user_func_array([$stmt, 'bind_result'], $params);
            
            if ($stmt->fetch()) {
                $result = [];
                foreach ($fields as $field) {
                    $result[$field] = $row[$field];
                }
                $meta->free();
                // IMPORTANT: Close statement to prevent "Commands out of sync" error
                $stmt->close();
                return $result;
            }
            
            $meta->free();
            // Close statement even if no results
            $stmt->close();
            return null;
        } catch (Exception $e) {
            error_log("fetchOne error: " . $e->getMessage());
            if (isset($stmt) && $stmt) {
                @$stmt->close();
            }
            return null;
        } catch (Error $e) {
            error_log("fetchOne fatal error: " . $e->getMessage());
            if (isset($stmt) && $stmt) {
                @$stmt->close();
            }
            return null;
        }
    }
}

/**
 * Fetch all rows from query result
 * 
 * @param mysqli_stmt $stmt Prepared statement
 * @return array Array of associative arrays
 */
function fetchAll($stmt) {
    if (!$stmt || $stmt === false) {
        return [];
    }
    
    // Check if mysqlnd is available (get_result method exists)
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        $stmt->close();
        return $data;
    } else {
        // Fallback for systems without mysqlnd - use bind_result
        try {
            $meta = $stmt->result_metadata();
            if (!$meta) {
                $stmt->close();
                return [];
            }
            
            $fields = [];
            $row = [];
            $params = [];
            while ($field = $meta->fetch_field()) {
                $fields[] = $field->name;
                $row[$field->name] = null;
                $params[] = &$row[$field->name];
            }
            
            call_user_func_array([$stmt, 'bind_result'], $params);
            
            $results = [];
            while ($stmt->fetch()) {
                $result = [];
                foreach ($fields as $field) {
                    $result[$field] = $row[$field];
                }
                $results[] = $result;
            }
            
            $meta->free();
            // IMPORTANT: Close statement to prevent "Commands out of sync" error
            $stmt->close();
            return $results;
        } catch (Exception $e) {
            error_log("fetchAll error: " . $e->getMessage());
            if (isset($stmt) && $stmt) {
                @$stmt->close();
            }
            return [];
        } catch (Error $e) {
            error_log("fetchAll fatal error: " . $e->getMessage());
            if (isset($stmt) && $stmt) {
                @$stmt->close();
            }
            return [];
        }
    }
}

/**
 * Get last inserted ID
 * 
 * @return int Last insert ID
 */
function getLastInsertId() {
    global $conn;
    return $conn->insert_id;
}

/**
 * Begin database transaction
 */
function beginTransaction() {
    global $conn;
    $conn->begin_transaction();
}

/**
 * Commit database transaction
 */
function commitTransaction() {
    global $conn;
    $conn->commit();
}

/**
 * Rollback database transaction
 */
function rollbackTransaction() {
    global $conn;
    $conn->rollback();
}

/**
 * Escape string for SQL query (Use prepared statements instead when possible)
 * 
 * @param string $str String to escape
 * @return string Escaped string
 */
function escapeString($str) {
    global $conn;
    return $conn->real_escape_string($str);
}

/**
 * Get database connection (alias for getDBConnection)
 * 
 * @return mysqli|false Database connection object or false on failure
 */
function getDB() {
    return getDBConnection();
}

/**
 * Close database connection
 */
function closeConnection() {
    global $conn;
    if ($conn && $conn !== false) {
        @$conn->close();
    }
}

// Register shutdown function to close connection
register_shutdown_function('closeConnection');


