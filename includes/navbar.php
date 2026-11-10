<?php
/**
 * Global Navigation Bar Component
 * Project: E-Waste to Best
 */

$currentScript = basename($_SERVER['PHP_SELF']);
$isLoggedIn = !empty($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? 'guest';
$userName = $_SESSION['user_name'] ?? 'User';
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <span class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-3 p-1 px-2 shadow-sm">
                <i class="bi bi-cpu-fill fs-5"></i>
            </span>
            <span>E-Waste <span class="eco-highlight">to Best</span></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent" aria-controls="navContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'index.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'awareness.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/awareness.php">
                        <i class="bi bi-shield-exclamation me-1"></i> Awareness
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'reuse-tips.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/reuse-tips.php">
                        <i class="bi bi-arrow-repeat me-1"></i> Reuse Tips
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'estimator.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/estimator.php">
                        <i class="bi bi-calculator me-1"></i> Metal Estimator
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'centers.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/centers.php">
                        <i class="bi bi-geo-alt me-1"></i> Centers
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if ($isLoggedIn): ?>
                    <?php if ($userRole === 'admin'): ?>
                        <!-- Admin Dropdown Menu -->
                        <div class="dropdown">
                            <button class="btn btn-dark border border-secondary dropdown-toggle text-light d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="badge bg-danger">ADMIN</span>
                                <span><?= sanitize($userName) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow">
                                <li><h6 class="dropdown-header text-uppercase text-secondary">Administration</h6></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Admin Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/pickups.php"><i class="bi bi-truck me-2"></i> Pickup Requests</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/centers.php"><i class="bi bi-building me-2"></i> Collection Centers</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/metal-rates.php"><i class="bi bi-currency-exchange me-2"></i> Metal Market Rates</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/users.php"><i class="bi bi-people me-2"></i> Registered Citizens</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- User Dropdown Menu -->
                        <a href="<?= BASE_URL ?>/user/pickup-request.php" class="btn btn-sm btn-emerald d-none d-sm-inline-flex align-items-center">
                            <i class="bi bi-plus-circle me-1"></i> Book Pickup
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-dark border border-secondary dropdown-toggle text-light d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle text-emerald"></i>
                                <span><?= sanitize($userName) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow">
                                <li><h6 class="dropdown-header text-uppercase text-secondary">Citizen Portal</h6></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/dashboard.php"><i class="bi bi-grid me-2"></i> My Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/pickup-request.php"><i class="bi bi-calendar2-plus me-2"></i> Schedule Pickup</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/history.php"><i class="bi bi-clock-history me-2"></i> Request History</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/impact.php"><i class="bi bi-tree me-2"></i> Environmental Impact</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/profile.php"><i class="bi bi-gear me-2"></i> Profile & Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-light btn-sm px-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </a>
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-emerald btn-sm px-3">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
