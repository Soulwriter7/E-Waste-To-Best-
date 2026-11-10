<?php
/**
 * CSRF Protection Helpers
 * Project: E-Waste to Best
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF token if not already existing
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render hidden HTML input field with CSRF token
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate CSRF token on POST requests
 */
function validateCsrfToken($token = null) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submittedToken = $token ?? ($_POST['csrf_token'] ?? '');
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            return false;
        }
    }
    return true;
}
