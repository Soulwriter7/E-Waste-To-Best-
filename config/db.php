<?php
/**
 * Database Connection using PDO
 * Project: E-Waste to Best
 */

require_once __DIR__ . '/constants.php';

$db_host = 'localhost';
$db_name = 'ewaste_db';
$db_user = 'root';
$db_pass = ''; // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // If running in development and DB doesn't exist yet, show friendly setup instructions
    $errorMsg = $e->getMessage();
    
    // Check if error is unknown database
    if (strpos($errorMsg, 'Unknown database') !== false) {
        $dbSetupNotice = true;
    }
    
    // Provide a helper function to check DB availability
    function isDbConnected() {
        return false;
    }
    
    $pdo = null;
}

if (!function_exists('isDbConnected')) {
    function isDbConnected() {
        global $pdo;
        return $pdo !== null;
    }
}
