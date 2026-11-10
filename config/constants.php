<?php
/**
 * Application Constants & Configuration
 * Project: E-Waste to Best - Smart E-Waste Recycling & Metal Recovery System
 */

// Application Info
define('APP_NAME', 'E-Waste to Best');
define('APP_TAGLINE', 'Smart E-Waste Recycling & Metal Recovery System');
define('APP_VERSION', '1.0.0');

// Currency & Units
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');

// Base Paths & URLs
if (!defined('BASE_URL')) {
    // Automatically detect protocol and host
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Determine the base path relative to web root
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $baseFolder = '';
    
    // Find the root folder name 'Waste to best' or similar in script path
    $parts = explode('/', trim($scriptName, '/'));
    if (!empty($parts)) {
        // If the first part is a PHP file (e.g. index.php), the site is running at web root
        if (substr($parts[0], -4) === '.php') {
            $baseFolder = '';
        } else {
            $baseFolder = '/' . $parts[0];
        }
    }
    
    define('BASE_URL', $protocol . $host . $baseFolder);
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
