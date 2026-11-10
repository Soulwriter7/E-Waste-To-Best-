<?php
/**
 * Administrator Overview & Command Center
 * Project: E-Waste to Best
 */

$pageTitle = "Admin Dashboard - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

// Fetch Global System Metrics
$totalUsers = 0;
$totalPickups = 0;
$pendingCount = 0;
$completedCount = 0;
$totalGoldRecovered = 0;
$totalPayoutValue = 0;
$totalCo2Saved = 0;

$recentRequests = [];
$categoryDistribution = [];

if ($pdo) {
    try {
        // Users Count
        $uStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
        $totalUsers = (int)$uStmt->fetchColumn();

        // Requests Overview
        $rStmt = $pdo->query("
            SELECT 
                COUNT(*) as total_pickups,
                COALESCE(SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END), 0) as pending_count,
                COALESCE(SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END), 0) as completed_count,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_gold_g ELSE est_gold_g END), 0) as total_gold,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_payout_value ELSE est_total_value END), 0) as total_payout,
                COALESCE(SUM(est_co2_saved_kg), 0) as total_co2
            FROM pickup_requests
            WHERE status != 'Cancelled'
        ");
        $kpi = $rStmt->fetch();

        $totalPickups = (int)($kpi['total_pickups'] ?? 0);
        $pendingCount = (int)($kpi['pending_count'] ?? 0);
        $completedCount = (int)($kpi['completed_count'] ?? 0);
        $totalGoldRecovered = (float)($kpi['total_gold'] ?? 0);
        $totalPayoutValue = (float)($kpi['total_payout'] ?? 0);
        $totalCo2Saved = (float)($kpi['total_co2'] ?? 0);

        // Recent Requests Queue
        $recentStmt = $pdo->query("
            SELECT r.*, c.category_name, c.unit_type, cc.center_name, u.fullname as citizen_name
            FROM pickup_requests r
            JOIN device_categories c ON r.device_category_id = c.id
            LEFT JOIN collection_centers cc ON r.center_id = cc.id
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
            LIMIT 6
        ");
        $recentRequests = $recentStmt->fetchAll();

        // Category breakdown for chart
        $catStmt = $pdo->query("
            SELECT c.category_name, COUNT(r.id) as count
            FROM device_categories c
            LEFT JOIN pickup_requests r ON c.id = r.device_category_id
            GROUP BY c.id, c.category_name
            ORDER BY count DESC
            LIMIT 5
        ");
        $categoryDistribution = $catStmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback defaults
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-dark text-white py-4 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-danger small mb-1">CENTRAL ADMINISTRATION</span>
                <h2 class="fw-bold mb-1">System Command Center</h2>
                <p class="text-secondary small mb-0">Platform overview, pickup dispatch queues, and verified metallurgical assay operations.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/admin/pickups.php" class="btn btn-warning text-dark d-inline-flex align-items-center">
                    <i class="bi bi-truck me-2"></i> Manage Requests
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-danger ms-2"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/admin/centers.php" class="btn btn-outline-light d-inline-flex align-items-center">
                    <i class="bi bi-building me-2"></i> Centers
                </a>
                <a href="<?= BASE_URL ?>/admin/metal-rates.php" class="btn btn-outline-light d-inline-flex align-items-center">
                    <i class="bi bi-currency-exchange me-2"></i> Metal Rates
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <?php renderFlash(); ?>

    <!-- Admin KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card shadow-sm h-100 border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-semibold">Registered Citizens</span>
                    <i class="bi bi-people fs-4 text-primary"></i>
                </div>
                <div class="fs-3 fw-bold mb-1"><?= number_format($totalUsers) ?></div>
                <div class="small text-secondary">Active user profiles</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="stat-card shadow-sm h-100 border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-semibold">Pending Intake</span>
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <div class="fs-3 fw-bold mb-1 text-warning"><?= $pendingCount ?></div>
                <div class="small text-secondary"><?= $completedCount ?> pickups completed</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="stat-card shadow-sm h-100 border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-semibold">Total Scrap Valuation</span>
                    <i class="bi bi-currency-rupee fs-4 text-success"></i>
                </div>
                <div class="fs-3 fw-bold mb-1 text-success">₹ <?= number_format($totalPayoutValue, 2) ?></div>
                <div class="small text-secondary">Cumulative recovered value</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="stat-card shadow-sm h-100 border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-semibold">Gold (Au) Reclaimed</span>
                    <i class="bi bi-gem fs-4 text-info"></i>
                </div>
                <div class="fs-3 fw-bold mb-1"><?= number_format($totalGoldRecovered, 3) ?> g</div>
                <div class="small text-secondary"><?= number_format($totalCo2Saved, 1) ?> kg CO₂ offset</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <!-- Incoming Requests Queue -->
            <div class="card card-modern p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Incoming Pickup Queue</h5>
                    <a href="<?= BASE_URL ?>/admin/pickups.php" class="btn btn-outline-secondary btn-sm">
                        View All (<?= $totalPickups ?>) <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <?php if (!empty($recentRequests)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Citizen</th>
                                    <th>Device</th>
                                    <th>Center</th>
                                    <th>Status</th>
                                    <th class="text-end">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRequests as $req): ?>
                                    <?php $statusSlug = strtolower(str_replace(' ', '-', $req['status'])); ?>
                                    <tr>
                                        <td class="fw-bold"><?= sanitize($req['request_code']) ?></td>
                                        <td><?= sanitize($req['citizen_name']) ?></td>
                                        <td>
                                            <?= sanitize($req['category_name']) ?> (<?= (float)$req['quantity'] ?> <?= sanitize($req['unit_type']) ?>)
                                        </td>
                                        <td>
                                            <?= $req['center_name'] ? sanitize($req['center_name']) : '<span class="text-danger small">Unassigned</span>' ?>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-status-<?= $statusSlug ?>">
                                                <?= sanitize($req['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/admin/verify-pickup.php?id=<?= $req['id'] ?>" class="btn btn-sm btn-outline-primary" title="Record Lab Verification">
                                                <i class="bi bi-sliders"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/user/view-request.php?code=<?= urlencode($req['request_code']) ?>" class="btn btn-sm btn-outline-dark" title="View Full Voucher">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-secondary">
                        <i class="bi bi-check2-circle fs-2 mb-2 d-block text-success"></i>
                        No requests currently in queue.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Category Distribution Chart -->
            <div class="card card-modern p-4 h-100">
                <h5 class="fw-bold mb-3">Top Scrapped Devices</h5>
                <div style="height: 240px; position: relative;">
                    <canvas id="categoryChart"></canvas>
                </div>
                <div class="mt-3 text-secondary text-xs text-center">
                    Distribution by consignment volume across all collection requests.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'polarArea',
            data: {
                labels: <?= json_encode(array_column($categoryDistribution, 'category_name')) ?>,
                datasets: [{
                    data: <?= json_encode(array_map('intval', array_column($categoryDistribution, 'count'))) ?>,
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
