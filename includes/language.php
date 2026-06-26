<?php
/**
 * Language and Translation Functions
 * 
 * Handles multi-language support for the application
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// Set default language if not set
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en';
}

// Handle language switch via URL
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Load language file
$lang = $_SESSION['lang'];
$langFile = ABSPATH . 'languages/' . $lang . '.php';

if (file_exists($langFile)) {
    $translations = require $langFile;
} else {
    // Fallback to English if selected language file doesn't exist
    $langFile = ABSPATH . 'languages/en.php';
    if (file_exists($langFile)) {
        $translations = require $langFile;
    } else {
        $translations = [];
    }
}

/**
 * Get translated string
 * 
 * @param string $key Translation key
 * @return string Translated string or key if not found
 */
function __($key) {
    global $translations;
    
    if (isset($translations[$key])) {
        return $translations[$key];
    }
    
    return $key;
}

/**
 * Get current language direction (LTR/RTL)
 * 
 * @return string 'ltr' or 'rtl'
 */
function getDir() {
    return $_SESSION['lang'] == 'ar' ? 'rtl' : 'ltr';
}

/**
 * Get current language code
 * 
 * @return string 'en' or 'ar'
 */
function getLang() {
    return $_SESSION['lang'];
}
