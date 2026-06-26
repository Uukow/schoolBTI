<?php
/**
 * Index Page
 * 
 * Redirects to appropriate page based on login status
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Define constants
defined('ABSPATH') or define('ABSPATH', dirname(__FILE__) . '/');

// Include configuration
require_once ABSPATH . 'config/config.php';

// Redirect based on login status
if (isLoggedIn()) {
    redirect(APP_URL . 'dashboard.php');
} else {
    redirect(APP_URL . 'login.php');
}


