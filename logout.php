<?php
/**
 * Logout Page
 * 
 * Handles user logout
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Define constants
defined('ABSPATH') or define('ABSPATH', dirname(__FILE__) . '/');

// Include configuration
require_once ABSPATH . 'config/config.php';

// Call logout function
logoutUser();


