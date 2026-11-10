<?php
/**
 * Citizen User Dashboard
 * Project: E-Waste to Best
 */

$pageTitle = "My Dashboard - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$userId = (int)$_SESSION['user_id'];

// Fetch user statistics
$totalRequests = 0;
$totalCo2Saved = 0;
$totalEstGold = 0;
$totalEstCopper = 0;
$totalEstValue = 0;
$verifiedCompletedCount = 0;

if ($pdo) {
    try {
        $statsStmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_requests,
                COALESCE(SUM(est_co2_saved_kg), 0) as total_co2,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_gold_g ELSE est_gold_g END), 0) as total_gold,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_copper_g ELSE est_copper_g END), 0) as total_copper,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_payout_value ELSE est_total_value END), 0) as total_value,
                COALESCE(SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END), 0) as completed_count
            FROM pickup_requests
            WHERE user_id = ? AND status != 'Cancelled'
        ");
        $statsStmt->execute([$userId]);
        $userStats = $statsStmt->fetch();

        $totalRequests = (int)($userStats['total_requests'] ?? 0);
        $totalCo2Saved = (float)($userStats['total_co2'] ?? 0);
        $totalEstGold = (float)($userStats['total_gold'] ?? 0);
        $totalEstCopper = (float)($userStats['total_copper'] ?? 0);
        $totalEstValue = (float)($userStats['total_value'] ?? 0);
        $verifiedCompletedCount = (int)($userStats['completed_count'] ?? 0);

        // Fetch recent pickup requests
        $recentStmt = $pdo->prepare("
            SELECT r.*, c.category_name, c.unit_type, cc.center_name, cc.city as center_city
            FROM pickup_requests r
            JOIN device_categories c ON r.device_category_id = c.id
            LEFT JOIN collection_centers cc ON r.center_id = cc.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
            LIMIT 5
        ");
        $recentStmt->execute([$userId]);
        $recentRequests = $recentStmt->fetchAll();
    } catch (PDOException $e) {
        $recentRequests = [];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-dark text-white py-4 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 small mb-1">
                    CITIZEN DASHBOARD
                </span>
                <h2 class="fw-bold mb-1">Welcome, <?= sanitize($_SESSION['user_name'] ?? 'Citizen') ?></h2>
                <p class="text-secondary small mb-0">Track your e-waste collections, estimated recovered metals, and verified green score.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/user/pickup-request.php" class="btn btn-emerald d-inline-flex align-items-center">
                    <i class="bi bi-calendar2-plus me-2"></i> Schedule New Pickup
                </a>
                <a href="<?= BASE_URL ?>/user/impact.php" class="btn btn-outline-light d-inline-flex align-items-center">
                    <i class="bi bi-tree me-2"></i> Green Impact
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <?php renderFlash(); ?>

    <!-- User KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card shadow-sm h-100 border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-semibold">Pickup Requests</span>
                    <i class="bi bi-truck fs-4 text-primary"></i>
                </div>
                <div class="fs-3 fw-bold mb-1"><?= $totalRequests ?></div>
                <div class="small text-secondary"><?= $verifiedCompletedCount ?> processed & verified</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="stat-card shadow-sm h-100 border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-semibold">Est. Total Valuation</span>
                    <i class="bi bi-cash-stack fs-4 text-success"></i>
                </div>
                <div class="fs-3 fw-bold mb-1 text-success">₹ <?= number_format($totalEstValue, 2) ?></div>
                <div class="small text-secondary">Based on benchmark rates</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="stat-card shadow-sm h-100 border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-semibold">Recovered Gold (Au)</span>
                    <i class="bi bi-gem fs-4 text-warning"></i>
                </div>
                <div class="fs-3 fw-bold mb-1"><?= number_format($totalEstGold, 4) ?> g</div>
                <div class="small text-secondary">Pure reclaimed gold</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="stat-card shadow-sm h-100 border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-semibold">CO₂ Offset</span>
                    <i class="bi bi-cloud-arrow-down fs-4 text-info"></i>
                </div>
                <div class="fs-3 fw-bold mb-1"><?= number_format($totalCo2Saved, 1) ?> kg</div>
                <div class="small text-secondary">Greenhouse emissions saved</div>
            </div>
        </div>
    </div>

    <!-- Recent Pickup Activity Table -->
    <div class="card card-modern p-4 mb-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
            <div>
                <h4 class="fw-bold mb-0">Recent Pickup Requests</h4>
                <p class="text-secondary small mb-0">Track live progress from booking to laboratory assay</p>
            </div>
            <a href="<?= BASE_URL ?>/user/history.php" class="btn btn-outline-secondary btn-sm">
                View Complete History <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <?php if (!empty($recentRequests)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Request Code</th>
                            <th>Device & Quantity</th>
                            <th>Preferred Slot</th>
                            <th>Assigned Center</th>
                            <th>Recovery Status</th>
                            <th>Est. Value</th>
                            <th>Verification</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php foreach ($recentRequests as $req): ?>
                            <?php 
                                $statusSlug = strtolower(str_replace(' ', '-', $req['status']));
                            ?>
                            <tr>
                                <td class="fw-bold text-dark">
                                    <a href="<?= BASE_URL ?>/user/view-request.php?code=<?= urlencode($req['request_code']) ?>" class="text-decoration-none text-dark fw-bold">
                                        <?= sanitize($req['request_code']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= sanitize($req['category_name']) ?></div>
                                    <div class="text-muted text-xs"><?= (float)$req['quantity'] ?> <?= sanitize($req['unit_type']) ?> &bull; <?= sanitize($req['item_condition']) ?></div>
                                </td>
                                <td>
                                    <div><?= date('d M, Y', strtotime($req['preferred_date'])) ?></div>
                                    <div class="text-muted text-xs"><?= sanitize($req['preferred_slot']) ?></div>
                                </td>
                                <td>
                                    <?php if ($req['center_name']): ?>
                                        <div class="text-truncate" style="max-width: 180px;" title="<?= sanitize($req['center_name']) ?>">
                                            <?= sanitize($req['center_name']) ?>
                                        </div>
                                        <div class="text-muted text-xs"><?= sanitize($req['center_city']) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">Auto Assigning...</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-status badge-status-<?= $statusSlug ?>">
                                        <?= sanitize($req['status']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-success">
                                    ₹ <?= number_format($req['is_verified'] ? $req['actual_payout_value'] : $req['est_total_value'], 2) ?>
                                </td>
                                <td>
                                    <?php if ($req['is_verified']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="bi bi-patch-check-fill me-1"></i> Lab Assay Verified
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                            Theoretical Estimate
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/user/view-request.php?code=<?= urlencode($req['request_code']) ?>" class="btn btn-outline-dark btn-sm">
                                        <i class="bi bi-receipt"></i> Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-secondary mb-2 d-block"></i>
                <h5 class="fw-bold">No E-Waste Requests Booked Yet</h5>
                <p class="text-secondary small mb-4">Start your green journey by submitting your first obsolete electronic item.</p>
                <a href="<?= BASE_URL ?>/user/pickup-request.php" class="btn btn-emerald">
                    <i class="bi bi-calendar2-plus me-1"></i> Schedule Your First Pickup
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Action Banner -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card card-modern p-4 h-100 bg-light border-0">
                <h5 class="fw-bold mb-2"><i class="bi bi-tree text-success me-2"></i>Download Green Certificate</h5>
                <p class="text-secondary small mb-3">
                    Obtain an authenticated institutional certificate confirming your electronic diversion from open dumps and landfills.
                </p>
                <a href="<?= BASE_URL ?>/user/impact.php" class="btn btn-outline-success btn-sm w-fit mt-auto">
                    View Environmental Audit & Certificate
                </a>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-modern p-4 h-100 bg-light border-0">
                <h5 class="fw-bold mb-2"><i class="bi bi-question-circle text-primary me-2"></i>Need Help Preparing Electronics?</h5>
                <p class="text-secondary small mb-3">
                    Learn how to securely wipe your data from SSDs and hard drives, and review what components are accepted at our centers.
                </p>
                <a href="<?= BASE_URL ?>/awareness.php" class="btn btn-outline-primary btn-sm w-fit mt-auto">
                    Read Preparation Guidelines
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
