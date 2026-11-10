<?php
/**
 * User Sign Out Script
 * Project: E-Waste to Best
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';

// Unset all session variables
$_SESSION = [];

// Destroy session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start clean session for flash message
session_start();
setFlash('info', 'You have been safely signed out. Thank you for supporting sustainable e-waste recovery!');

header('Location: ' . BASE_URL . '/login.php');
exit;
