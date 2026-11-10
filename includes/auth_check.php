<?php
/**
 * User Authentication Guard
 * Project: E-Waste to Best
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/functions.php';

if (empty($_SESSION['user_id'])) {
    setFlash('warning', 'Please sign in to access your portal.');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
