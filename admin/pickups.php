<?php
/**
 * Administrator Pickup Management & Workflow Dispatch
 * Project: E-Waste to Best
 */

$pageTitle = "Manage Pickup Requests - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../includes/csrf.php';

$error = '';
$statusFilter = $_GET['status'] ?? 'all';

// Handle Quick Status / Center Assignment Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        $requestId = (int)($_POST['request_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        $centerId = !empty($_POST['center_id']) ? (int)$_POST['center_id'] : null;
        $adminRemarks = trim($_POST['admin_remarks'] ?? '');

        if ($requestId > 0 && !empty($newStatus)) {
            try {
                $upStmt = $pdo->prepare("
                    UPDATE pickup_requests
                    SET status = ?, center_id = ?, admin_remarks = ?
                    WHERE id = ?
                ");
                $upStmt->execute([$newStatus, $centerId, $adminRemarks, $requestId]);

                setFlash('success', 'Pickup request #' . $requestId . ' updated to status "' . sanitize($newStatus) . '".');
                header('Location: ' . BASE_URL . '/admin/pickups.php?status=' . urlencode($statusFilter));
                exit;
            } catch (PDOException $e) {
                $error = 'Status update failed: ' . $e->getMessage();
            }
        }
    }
}

// Fetch all collection centers for assignment dropdown
$centers = [];
if ($pdo) {
    try {
        $cStmt = $pdo->query("SELECT id, center_name, city FROM collection_centers WHERE is_active = 1 ORDER BY city ASC, center_name ASC");
        $centers = $cStmt->fetchAll();
    } catch (PDOException $e) {
        $centers = [];
    }
}

// Fetch filtered pickup requests
$requests = [];
if ($pdo) {
    try {
        $sql = "
            SELECT r.*, c.category_name, c.unit_type, cc.center_name, cc.city as center_city,
                   u.fullname as citizen_name, u.phone as citizen_phone
            FROM pickup_requests r
            JOIN device_categories c ON r.device_category_id = c.id
            LEFT JOIN collection_centers cc ON r.center_id = cc.id
            JOIN users u ON r.user_id = u.id
        ";
        $params = [];

        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $sql .= " WHERE r.status = ?";
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
                <span class="badge bg-danger small mb-1">LOGISTICS DISPATCH</span>
                <h2 class="fw-bold mb-1">Pickup Requests Management</h2>
                <p class="text-secondary small mb-0">Route requests to collection centers, update field status, and trigger laboratory verification.</p>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <?php renderFlash(); ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?= sanitize($error) ?></div>
        </div>
    <?php endif; ?>

    <!-- Status Filter Pills -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="<?= BASE_URL ?>/admin/pickups.php?status=all" class="btn btn-sm <?= ($statusFilter === 'all') ? 'btn-dark' : 'btn-outline-secondary' ?>">
            All (<?= count($requests) ?>)
        </a>
        <a href="<?= BASE_URL ?>/admin/pickups.php?status=Pending" class="btn btn-sm <?= ($statusFilter === 'Pending') ? 'btn-warning text-dark' : 'btn-outline-secondary' ?>">
            Pending
        </a>
        <a href="<?= BASE_URL ?>/admin/pickups.php?status=Assigned" class="btn btn-sm <?= ($statusFilter === 'Assigned') ? 'btn-primary' : 'btn-outline-secondary' ?>">
            Assigned
        </a>
        <a href="<?= BASE_URL ?>/admin/pickups.php?status=Out for Pickup" class="btn btn-sm <?= ($statusFilter === 'Out for Pickup') ? 'btn-info text-dark' : 'btn-outline-secondary' ?>">
            Out for Pickup
        </a>
        <a href="<?= BASE_URL ?>/admin/pickups.php?status=Collected" class="btn btn-sm <?= ($statusFilter === 'Collected') ? 'btn-secondary' : 'btn-outline-secondary' ?>">
            Collected / In Lab
        </a>
        <a href="<?= BASE_URL ?>/admin/pickups.php?status=Completed" class="btn btn-sm <?= ($statusFilter === 'Completed') ? 'btn-success' : 'btn-outline-secondary' ?>">
            Completed & Verified
        </a>
        <a href="<?= BASE_URL ?>/admin/pickups.php?status=Cancelled" class="btn btn-sm <?= ($statusFilter === 'Cancelled') ? 'btn-danger' : 'btn-outline-secondary' ?>">
            Cancelled
        </a>
    </div>

    <!-- Master Table -->
    <div class="card card-modern p-4">
        <?php if (!empty($requests)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Request Code</th>
                            <th>Citizen</th>
                            <th>Consignment Details</th>
                            <th>Pickup Address</th>
                            <th>Assigned Center</th>
                            <th>Lifecycle Status</th>
                            <th>Assay</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                            <?php $statusSlug = strtolower(str_replace(' ', '-', $req['status'])); ?>
                            <tr>
                                <td class="fw-bold">
                                    <a href="<?= BASE_URL ?>/user/view-request.php?code=<?= urlencode($req['request_code']) ?>" class="text-dark text-decoration-none fw-bold">
                                        <?= sanitize($req['request_code']) ?>
                                    </a>
                                    <div class="text-muted text-xs"><?= date('d M Y, h:i A', strtotime($req['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= sanitize($req['citizen_name']) ?></div>
                                    <div class="text-secondary text-xs"><?= sanitize($req['citizen_phone']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= sanitize($req['category_name']) ?></div>
                                    <div class="text-secondary text-xs"><?= (float)$req['quantity'] ?> <?= sanitize($req['unit_type']) ?> &bull; <?= sanitize($req['item_condition']) ?></div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 170px;" title="<?= sanitize($req['pickup_address']) ?>">
                                        <?= sanitize($req['pickup_address']) ?>
                                    </div>
                                    <div class="text-muted text-xs"><?= sanitize($req['pickup_city']) ?> (<?= sanitize($req['pickup_pincode']) ?>)</div>
                                </td>
                                <td>
                                    <?= !empty($req['center_name']) ? sanitize($req['center_name']) : '<span class="text-danger small">Unassigned</span>' ?>
                                </td>
                                <td>
                                    <span class="badge-status badge-status-<?= $statusSlug ?>">
                                        <?= sanitize($req['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($req['is_verified']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="bi bi-patch-check-fill me-1"></i> Verified
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                            Est.
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updateModal<?= $req['id'] ?>" title="Quick Update Status">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="<?= BASE_URL ?>/admin/verify-pickup.php?id=<?= $req['id'] ?>" class="btn btn-outline-primary" title="Record Lab Verified Assay">
                                            <i class="bi bi-sliders"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/user/view-request.php?code=<?= urlencode($req['request_code']) ?>" class="btn btn-outline-dark" title="View Voucher">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>

                                    <!-- Quick Update Status Modal -->
                                    <div class="modal fade" id="updateModal<?= $req['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Update Request #<?= sanitize($req['request_code']) ?></h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/pickups.php">
                                                    <div class="modal-body">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Pickup Status</label>
                                                            <select class="form-select form-select-sm" name="status" required>
                                                                <option value="Pending" <?= ($req['status'] === 'Pending') ? 'selected' : '' ?>>Pending Verification</option>
                                                                <option value="Assigned" <?= ($req['status'] === 'Assigned') ? 'selected' : '' ?>>Assigned to Center</option>
                                                                <option value="Out for Pickup" <?= ($req['status'] === 'Out for Pickup') ? 'selected' : '' ?>>Out for Pickup</option>
                                                                <option value="Collected" <?= ($req['status'] === 'Collected') ? 'selected' : '' ?>>Collected / At Lab</option>
                                                                <option value="Completed" <?= ($req['status'] === 'Completed') ? 'selected' : '' ?>>Completed</option>
                                                                <option value="Cancelled" <?= ($req['status'] === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Assign Collection Center</label>
                                                            <select class="form-select form-select-sm" name="center_id">
                                                                <option value="">-- Select Center --</option>
                                                                <?php foreach ($centers as $center): ?>
                                                                    <option value="<?= $center['id'] ?>" <?= ($req['center_id'] == $center['id']) ? 'selected' : '' ?>>
                                                                        <?= sanitize($center['center_name']) ?> (<?= sanitize($center['city']) ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Admin / Dispatch Remarks</label>
                                                            <textarea class="form-control form-control-sm" name="admin_remarks" rows="2" placeholder="e.g. Assigned to driver Vikram; pickup scheduled for 10 AM."><?= sanitize($req['admin_remarks'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-emerald btn-sm">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-secondary">
                <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
                <h5 class="fw-bold">No Requests Found</h5>
                <p class="small">No pickup requests match your selected status criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
