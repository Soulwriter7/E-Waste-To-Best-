<?php
/**
 * Admin Access Guard
 * Project: E-Waste to Best
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/functions.php';

if (empty($_SESSION['user_id'])) {
    setFlash('warning', 'Administrative access requires authentication.');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    setFlash('danger', 'Access denied. You do not possess administrator credentials.');
    header('Location: ' . BASE_URL . '/user/dashboard.php');
    exit;
}
