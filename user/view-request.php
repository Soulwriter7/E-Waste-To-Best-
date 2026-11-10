<?php
/**
 * Detailed Pickup Receipt & Status Voucher View
 * Project: E-Waste to Best
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$userId = (int)$_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'user';
$code = $_GET['code'] ?? '';

if (empty($code)) {
    setFlash('danger', 'Invalid request code specified.');
    header('Location: ' . BASE_URL . '/user/history.php');
    exit;
}

$request = null;
if ($pdo) {
    try {
        $sql = "
            SELECT r.*, c.category_name, c.unit_type, c.toxic_diverted_kg,
                   cc.center_name, cc.address as center_address, cc.city as center_city, cc.phone as center_phone, cc.email as center_email, cc.operating_hours,
                   u.fullname as citizen_name, u.email as citizen_email, u.phone as citizen_phone
            FROM pickup_requests r
            JOIN device_categories c ON r.device_category_id = c.id
            LEFT JOIN collection_centers cc ON r.center_id = cc.id
            JOIN users u ON r.user_id = u.id
            WHERE r.request_code = ?
        ";

        // If not admin, restrict to owner's request
        if ($userRole !== 'admin') {
            $sql .= " AND r.user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code, $userId]);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code]);
        }

        $request = $stmt->fetch();
    } catch (PDOException $e) {
        $request = null;
    }
}

if (!$request) {
    setFlash('danger', 'Pickup request not found or access denied.');
    header('Location: ' . BASE_URL . '/user/history.php');
    exit;
}

$pageTitle = "Voucher #" . $request['request_code'] . " - E-Waste to Best";
require_once __DIR__ . '/../includes/header.php';

$statusSlug = strtolower(str_replace(' ', '-', $request['status']));
?>

<div class="bg-dark text-white py-4 d-print-none">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 small mb-1">
                    OFFICIAL VOUCHER
                </span>
                <h2 class="fw-bold mb-0">Collection Voucher: <?= sanitize($request['request_code']) ?></h2>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-light" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print Voucher
                </button>
                <?php if ($userRole === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/admin/verify-pickup.php?id=<?= $request['id'] ?>" class="btn btn-warning text-dark">
                        <i class="bi bi-sliders me-1"></i> Lab Verification Interface
                    </a>
                    <a href="<?= BASE_URL ?>/admin/pickups.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Queue
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/user/history.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to History
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <?php renderFlash(); ?>

    <!-- Printable Receipt Container -->
    <div class="card card-modern p-4 p-md-5 bg-white shadow-sm printable-area">
        <!-- Voucher Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start border-bottom pb-4 mb-4 gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-3 p-1 px-2">
                        <i class="bi bi-cpu-fill fs-5"></i>
                    </span>
                    <h4 class="fw-bold mb-0 brand-font text-dark">E-Waste <span class="text-success">to Best</span></h4>
                </div>
                <div class="text-secondary small">Smart E-Waste Recycling & Metal Recovery Network</div>
                <div class="text-secondary text-xs">Certified Environmental Management Framework</div>
            </div>

            <div class="text-sm-end">
                <span class="badge-status badge-status-<?= $statusSlug ?> fs-6 mb-2 d-inline-block">
                    Status: <?= sanitize($request['status']) ?>
                </span>
                <div class="fw-bold text-dark fs-5"># <?= sanitize($request['request_code']) ?></div>
                <div class="text-muted small">Generated on <?= date('d M, Y - h:i A', strtotime($request['created_at'])) ?></div>
            </div>
        </div>

        <!-- Participant & Logistics Details -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <h6 class="text-uppercase text-secondary fw-bold small mb-2">Citizen Information</h6>
                <div class="fw-bold text-dark"><?= sanitize($request['citizen_name']) ?></div>
                <div class="text-secondary small"><?= sanitize($request['citizen_email']) ?></div>
                <div class="text-secondary small"><?= sanitize($request['citizen_phone']) ?></div>
            </div>

            <div class="col-md-4">
                <h6 class="text-uppercase text-secondary fw-bold small mb-2">Doorstep Pickup Logistics</h6>
                <div class="text-secondary small">
                    <strong>Address:</strong> <?= sanitize($request['pickup_address']) ?><br>
                    <strong>City/PIN:</strong> <?= sanitize($request['pickup_city']) ?> - <?= sanitize($request['pickup_pincode']) ?><br>
                    <strong>Preferred Slot:</strong> <?= date('d M, Y', strtotime($request['preferred_date'])) ?> (<?= sanitize($request['preferred_slot']) ?>)
                </div>
            </div>

            <div class="col-md-4">
                <h6 class="text-uppercase text-secondary fw-bold small mb-2">Assigned Recovery Depot</h6>
                <?php if (!empty($request['center_name'])): ?>
                    <div class="fw-bold text-dark"><?= sanitize($request['center_name']) ?></div>
                    <div class="text-secondary small"><?= sanitize($request['center_address']) ?>, <?= sanitize($request['center_city']) ?></div>
                    <div class="text-secondary small"><i class="bi bi-telephone me-1"></i><?= sanitize($request['center_phone']) ?></div>
                <?php else: ?>
                    <div class="p-2 rounded bg-light text-secondary small border">
                        Central Dispatch routing in progress.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submitted Device Specifications -->
        <div class="p-3 rounded-3 bg-light border mb-4">
            <h6 class="fw-bold text-dark mb-2">Item Consignment Details</h6>
            <div class="row g-2 small">
                <div class="col-sm-4">
                    <span class="text-secondary">Device Classification:</span>
                    <strong class="d-block text-dark"><?= sanitize($request['category_name']) ?></strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-secondary">Quantity / Batch Weight:</span>
                    <strong class="d-block text-dark"><?= (float)$request['quantity'] ?> <?= sanitize($request['unit_type']) ?></strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-secondary">Declared Condition:</span>
                    <strong class="d-block text-dark"><?= sanitize($request['item_condition']) ?></strong>
                </div>
            </div>
            <?php if (!empty($request['special_notes'])): ?>
                <div class="mt-2 pt-2 border-top text-secondary small">
                    <strong>User Notes:</strong> <?= sanitize($request['special_notes']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Side-by-Side Comparison: Theoretical Model vs Verified Laboratory Assay -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-intersect text-emerald me-1"></i> Metallurgical Recovery Audit
                </h5>
                <?php if ($request['is_verified']): ?>
                    <span class="badge bg-success px-3 py-2">
                        <i class="bi bi-patch-check-fill me-1"></i> Physical Lab Assay Completed
                    </span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark px-3 py-2">
                        <i class="bi bi-hourglass-split me-1"></i> Awaiting Lab Intake & Assay
                    </span>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Recoverable Element</th>
                            <th class="bg-light">Theoretical Estimate (At Booking)</th>
                            <th class="<?= $request['is_verified'] ? 'table-success text-success fw-bold' : 'table-light text-muted' ?>">
                                Verified Physical Assay (Center Lab)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <tr>
                            <td><span class="metal-pill metal-pill-gold">Au &bull; Gold</span></td>
                            <td><?= number_format($request['est_gold_g'], 4) ?> g</td>
                            <td>
                                <?php if ($request['is_verified']): ?>
                                    <strong class="text-dark"><?= number_format($request['actual_gold_g'], 4) ?> g</strong>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Pending physical assay</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="metal-pill metal-pill-silver">Ag &bull; Silver</span></td>
                            <td><?= number_format($request['est_silver_g'], 4) ?> g</td>
                            <td>
                                <?php if ($request['is_verified']): ?>
                                    <strong class="text-dark"><?= number_format($request['actual_silver_g'], 4) ?> g</strong>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Pending physical assay</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="metal-pill metal-pill-copper">Cu &bull; Copper</span></td>
                            <td><?= number_format($request['est_copper_g'], 2) ?> g</td>
                            <td>
                                <?php if ($request['is_verified']): ?>
                                    <strong class="text-dark"><?= number_format($request['actual_copper_g'], 2) ?> g</strong>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Pending physical assay</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="metal-pill metal-pill-aluminium">Al &bull; Aluminium</span></td>
                            <td><?= number_format($request['est_aluminium_g'], 2) ?> g</td>
                            <td>
                                <?php if ($request['is_verified']): ?>
                                    <strong class="text-dark"><?= number_format($request['actual_aluminium_g'], 2) ?> g</strong>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Pending physical assay</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr class="table-active fw-bold">
                            <td>Total Scrap Valuation</td>
                            <td class="text-secondary">₹ <?= number_format($request['est_total_value'], 2) ?></td>
                            <td>
                                <?php if ($request['is_verified']): ?>
                                    <span class="text-success fs-6">₹ <?= number_format($request['actual_payout_value'], 2) ?></span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Payable upon verification</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($request['verification_notes'])): ?>
            <div class="p-3 rounded-3 bg-light border mb-4 small text-secondary">
                <strong class="text-dark d-block mb-1"><i class="bi bi-file-earmark-medical me-1"></i> Laboratory Verification Remarks:</strong>
                <?= sanitize($request['verification_notes']) ?>
            </div>
        <?php endif; ?>

        <!-- Environmental Savings Banner -->
        <div class="p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="text-success fw-bold d-block"><i class="bi bi-tree-fill me-1"></i> Verified Environmental Savings</span>
                <span class="text-secondary small">Greenhouse gas offset: <strong><?= number_format($request['est_co2_saved_kg'], 1) ?> kg CO₂e</strong></span>
            </div>
            <div class="text-end">
                <span class="badge bg-success p-2 px-3">Zero Landfill Certified</span>
            </div>
        </div>

        <!-- Official Signatures for Printed Voucher -->
        <div class="row pt-4 mt-4 border-top text-secondary small text-center">
            <div class="col-6">
                <div class="border-bottom pb-4 mb-2 mx-auto" style="max-width: 200px;"></div>
                <div>Citizen / Depositor Signature</div>
            </div>
            <div class="col-6">
                <div class="border-bottom pb-4 mb-2 mx-auto" style="max-width: 200px;"></div>
                <div>Authorized Center In-Charge</div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
