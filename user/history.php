<?php
/**
 * User Pickup History & Status Tracking
 * Project: E-Waste to Best
 */

$pageTitle = "Pickup Request History - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$userId = (int)$_SESSION['user_id'];
$statusFilter = $_GET['status'] ?? 'all';

$requests = [];
if ($pdo) {
    try {
        $sql = "
            SELECT r.*, c.category_name, c.unit_type, cc.center_name, cc.city as center_city, cc.phone as center_phone
            FROM pickup_requests r
            JOIN device_categories c ON r.device_category_id = c.id
            LEFT JOIN collection_centers cc ON r.center_id = cc.id
            WHERE r.user_id = ?
        ";
        $params = [$userId];

        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $sql .= " AND r.status = ?";
            $params[] = $statusFilter;
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $requests = $stmt->fetchAll();
    } catch (PDOException $e) {
        $requests = [];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-dark text-white py-4 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-secondary text-light small mb-1">AUDIT & TRACKING</span>
                <h2 class="fw-bold mb-1">Pickup Request History</h2>
                <p class="text-secondary small mb-0">Review status updates, collection vouchers, and certified metal assay logs.</p>
            </div>
            <a href="<?= BASE_URL ?>/user/pickup-request.php" class="btn btn-emerald">
                <i class="bi bi-calendar2-plus me-1"></i> New Request
            </a>
        </div>
    </div>
</div>

<div class="container py-5">
    <?php renderFlash(); ?>

    <!-- Filter Pills -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="<?= BASE_URL ?>/user/history.php?status=all" class="btn btn-sm <?= ($statusFilter === 'all') ? 'btn-dark' : 'btn-outline-secondary' ?>">
            All Requests
        </a>
        <a href="<?= BASE_URL ?>/user/history.php?status=Pending" class="btn btn-sm <?= ($statusFilter === 'Pending') ? 'btn-warning text-dark' : 'btn-outline-secondary' ?>">
            Pending Verification
        </a>
        <a href="<?= BASE_URL ?>/user/history.php?status=Assigned" class="btn btn-sm <?= ($statusFilter === 'Assigned') ? 'btn-primary' : 'btn-outline-secondary' ?>">
            Assigned to Center
        </a>
        <a href="<?= BASE_URL ?>/user/history.php?status=Collected" class="btn btn-sm <?= ($statusFilter === 'Collected') ? 'btn-info text-dark' : 'btn-outline-secondary' ?>">
            Collected / In Transit
        </a>
        <a href="<?= BASE_URL ?>/user/history.php?status=Completed" class="btn btn-sm <?= ($statusFilter === 'Completed') ? 'btn-success' : 'btn-outline-secondary' ?>">
            Completed & Verified
        </a>
    </div>

    <!-- Requests Table Card -->
    <div class="card card-modern p-4">
        <?php if (!empty($requests)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Request ID</th>
                            <th>Date Booked</th>
                            <th>Device & Quantity</th>
                            <th>Assigned Center</th>
                            <th>Status Lifecycle</th>
                            <th>Recovery Type</th>
                            <th>Valuation</th>
                            <th class="text-end">Voucher / Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php foreach ($requests as $req): ?>
                            <?php 
                                $statusSlug = strtolower(str_replace(' ', '-', $req['status']));
                            ?>
                            <tr>
                                <td class="fw-bold">
                                    <a href="<?= BASE_URL ?>/user/view-request.php?code=<?= urlencode($req['request_code']) ?>" class="text-dark text-decoration-none fw-bold">
                                        <?= sanitize($req['request_code']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div><?= date('d M, Y', strtotime($req['created_at'])) ?></div>
                                    <div class="text-muted text-xs"><?= date('h:i A', strtotime($req['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= sanitize($req['category_name']) ?></div>
                                    <div class="text-secondary text-xs">
                                        <?= (float)$req['quantity'] ?> <?= sanitize($req['unit_type']) ?> &bull; Condition: <?= sanitize($req['item_condition']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($req['center_name'])): ?>
                                        <div class="fw-semibold text-truncate" style="max-width: 170px;" title="<?= sanitize($req['center_name']) ?>">
                                            <?= sanitize($req['center_name']) ?>
                                        </div>
                                        <div class="text-muted text-xs"><?= sanitize($req['center_city']) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">Awaiting Center Routing</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-status badge-status-<?= $statusSlug ?>">
                                        <?= sanitize($req['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($req['is_verified']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="bi bi-patch-check-fill me-1"></i> Lab Assay Verified
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                            Theoretical Model
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-success">
                                    ₹ <?= number_format($req['is_verified'] ? $req['actual_payout_value'] : $req['est_total_value'], 2) ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/user/view-request.php?code=<?= urlencode($req['request_code']) ?>" class="btn btn-outline-dark btn-sm">
                                        <i class="bi bi-receipt me-1"></i> View Receipt
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-folder2-open fs-1 text-secondary mb-2 d-block"></i>
                <h5 class="fw-bold">No Records Found</h5>
                <p class="text-secondary small mb-3">No collection requests match your selected status filter.</p>
                <a href="<?= BASE_URL ?>/user/pickup-request.php" class="btn btn-emerald btn-sm">
                    Book New Pickup Request
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
