<?php
/**
 * Physical Laboratory Assay Verification Interface
 * Project: E-Waste to Best
 */

$pageTitle = "Lab Assay Verification - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../includes/csrf.php';

$requestId = (int)($_GET['id'] ?? 0);
$error = '';

if ($requestId <= 0) {
    setFlash('danger', 'Invalid pickup request ID specified.');
    header('Location: ' . BASE_URL . '/admin/pickups.php');
    exit;
}

// Fetch request details
$req = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, c.category_name, c.unit_type, c.toxic_diverted_kg,
                   cc.center_name, cc.city as center_city,
                   u.fullname as citizen_name, u.email as citizen_email
            FROM pickup_requests r
            JOIN device_categories c ON r.device_category_id = c.id
            LEFT JOIN collection_centers cc ON r.center_id = cc.id
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$requestId]);
        $req = $stmt->fetch();
    } catch (PDOException $e) {
        $req = null;
    }
}

if (!$req) {
    setFlash('danger', 'Pickup request not found.');
    header('Location: ' . BASE_URL . '/admin/pickups.php');
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        $actualWeight = (float)($_POST['actual_weight_kg'] ?? 0);
        $actualGold = (float)($_POST['actual_gold_g'] ?? 0);
        $actualSilver = (float)($_POST['actual_silver_g'] ?? 0);
        $actualCopper = (float)($_POST['actual_copper_g'] ?? 0);
        $actualAluminium = (float)($_POST['actual_aluminium_g'] ?? 0);
        $actualPayout = (float)($_POST['actual_payout_value'] ?? 0);
        $verificationNotes = trim($_POST['verification_notes'] ?? '');

        if ($actualPayout < 0) {
            $error = 'Scrap payout value cannot be negative.';
        } else {
            try {
                // Update pickup request with verified numbers
                $upStmt = $pdo->prepare("
                    UPDATE pickup_requests
                    SET is_verified = 1,
                        status = 'Completed',
                        actual_weight_kg = ?,
                        actual_gold_g = ?,
                        actual_silver_g = ?,
                        actual_copper_g = ?,
                        actual_aluminium_g = ?,
                        actual_payout_value = ?,
                        verification_notes = ?
                    WHERE id = ?
                ");
                $upStmt->execute([
                    $actualWeight,
                    $actualGold,
                    $actualSilver,
                    $actualCopper,
                    $actualAluminium,
                    $actualPayout,
                    $verificationNotes,
                    $requestId
                ]);

                // Record or update Impact Log entry
                $co2Kg = (float)$req['est_co2_saved_kg'];
                $toxicKg = (float)$req['toxic_diverted_kg'] * (float)$req['quantity'];
                $preciousG = $actualGold + $actualSilver;

                $logStmt = $pdo->prepare("
                    INSERT INTO impact_logs (pickup_id, co2_saved_kg, toxic_waste_diverted_kg, precious_metals_reclaimed_g)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        co2_saved_kg = VALUES(co2_saved_kg),
                        toxic_waste_diverted_kg = VALUES(toxic_waste_diverted_kg),
                        precious_metals_reclaimed_g = VALUES(precious_metals_reclaimed_g)
                ");
                $logStmt->execute([$requestId, $co2Kg, $toxicKg, $preciousG]);

                setFlash('success', 'Physical laboratory verification recorded successfully! Request #' . $req['request_code'] . ' is now marked as Completed.');
                header('Location: ' . BASE_URL . '/user/view-request.php?code=' . urlencode($req['request_code']));
                exit;
            } catch (PDOException $e) {
                $error = 'Failed to save verification record: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-dark text-white py-4 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-warning text-dark small mb-1">METALLURGICAL ASSAY WORKSTATION</span>
                <h2 class="fw-bold mb-1">Laboratory Verification: #<?= sanitize($req['request_code']) ?></h2>
                <p class="text-secondary small mb-0">Record actual chemical dissolution weights and certified payout to distinguish from theoretical projections.</p>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/admin/pickups.php" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> Back to Queue
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

    <div class="row g-4">
        <!-- Theoretical Baseline Reference (Left) -->
        <div class="col-lg-5">
            <div class="card card-modern p-4 bg-light border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">Original Theoretical Model</h5>
                    <span class="badge bg-secondary">Pre-Assay</span>
                </div>
                <p class="text-secondary small mb-3">Calculated at citizen booking time via USGS/EPA empirical baseline coefficients:</p>

                <div class="small mb-3">
                    <div><strong>Citizen:</strong> <?= sanitize($req['citizen_name']) ?> (<?= sanitize($req['citizen_email']) ?>)</div>
                    <div><strong>Device:</strong> <?= sanitize($req['category_name']) ?></div>
                    <div><strong>Batch Size:</strong> <?= (float)$req['quantity'] ?> <?= sanitize($req['unit_type']) ?></div>
                    <div><strong>Condition:</strong> <?= sanitize($req['item_condition']) ?></div>
                </div>

                <div class="p-3 rounded-3 bg-white border mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Est. Gold (Au):</span>
                        <strong class="text-warning"><?= number_format($req['est_gold_g'], 4) ?> g</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Est. Silver (Ag):</span>
                        <strong class="text-secondary"><?= number_format($req['est_silver_g'], 4) ?> g</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Est. Copper (Cu):</span>
                        <strong class="text-danger"><?= number_format($req['est_copper_g'], 2) ?> g</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Est. Aluminium (Al):</span>
                        <strong class="text-info"><?= number_format($req['est_aluminium_g'], 2) ?> g</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between small fw-bold">
                        <span>Total Theoretical Value:</span>
                        <span class="text-success">₹ <?= number_format($req['est_total_value'], 2) ?></span>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="copyTheoreticalValues()">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Copy Estimates as Baseline Inputs
                </button>
            </div>

            <div class="disclaimer-badge">
                <i class="bi bi-shield-check me-1"></i>
                Saving this form changes the status to <strong>Completed</strong> and marks the consignment as <strong>Lab Verified</strong> on the public and user vouchers.
            </div>
        </div>

        <!-- Verified Assay Form (Right) -->
        <div class="col-lg-7">
            <div class="card card-modern p-4 p-md-5 bg-white shadow-sm border-start border-4 border-success">
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-patch-check-fill text-success"></i> Record Physical Assay & Weights
                </h4>

                <form method="POST" action="<?= BASE_URL ?>/admin/verify-pickup.php?id=<?= $requestId ?>">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label for="actual_weight_kg" class="form-label small fw-semibold">Physical Weighed Batch Gross Weight (kg)</label>
                        <input type="number" step="any" class="form-control form-control-lg" id="actual_weight_kg" name="actual_weight_kg" value="<?= (float)($req['actual_weight_kg'] ?? 0) ?>" required>
                        <div class="form-text small">Calibrated digital scale measurement at facility intake.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="actual_gold_g" class="form-label small fw-semibold">
                                <span class="metal-pill metal-pill-gold">Au</span> Verified Gold (g)
                            </label>
                            <input type="number" step="any" class="form-control" id="actual_gold_g" name="actual_gold_g" value="<?= (float)($req['actual_gold_g'] ?? $req['est_gold_g']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="actual_silver_g" class="form-label small fw-semibold">
                                <span class="metal-pill metal-pill-silver">Ag</span> Verified Silver (g)
                            </label>
                            <input type="number" step="any" class="form-control" id="actual_silver_g" name="actual_silver_g" value="<?= (float)($req['actual_silver_g'] ?? $req['est_silver_g']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="actual_copper_g" class="form-label small fw-semibold">
                                <span class="metal-pill metal-pill-copper">Cu</span> Verified Copper (g)
                            </label>
                            <input type="number" step="any" class="form-control" id="actual_copper_g" name="actual_copper_g" value="<?= (float)($req['actual_copper_g'] ?? $req['est_copper_g']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="actual_aluminium_g" class="form-label small fw-semibold">
                                <span class="metal-pill metal-pill-aluminium">Al</span> Verified Aluminium (g)
                            </label>
                            <input type="number" step="any" class="form-control" id="actual_aluminium_g" name="actual_aluminium_g" value="<?= (float)($req['actual_aluminium_g'] ?? $req['est_aluminium_g']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="actual_payout_value" class="form-label small fw-semibold">Final Certified Scrap Payout Value (₹ INR)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light text-secondary">₹</span>
                            <input type="number" step="any" class="form-control" id="actual_payout_value" name="actual_payout_value" value="<?= (float)($req['actual_payout_value'] ?? $req['est_total_value']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="verification_notes" class="form-label small fw-semibold">Laboratory Assay Notes & Remarks</label>
                        <textarea class="form-control" id="verification_notes" name="verification_notes" rows="3" placeholder="e.g. Acid digestion and hydrometallurgical recovery executed at facility. Gold pins stripped cleanly without contamination."><?= sanitize($req['verification_notes'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-emerald btn-lg w-100 py-3 fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-2"></i> Save Verified Assay & Issue Certificate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function copyTheoreticalValues() {
    document.getElementById('actual_gold_g').value = '<?= (float)$req['est_gold_g'] ?>';
    document.getElementById('actual_silver_g').value = '<?= (float)$req['est_silver_g'] ?>';
    document.getElementById('actual_copper_g').value = '<?= (float)$req['est_copper_g'] ?>';
    document.getElementById('actual_aluminium_g').value = '<?= (float)$req['est_aluminium_g'] ?>';
    document.getElementById('actual_payout_value').value = '<?= (float)$req['est_total_value'] ?>';
    if (!document.getElementById('actual_weight_kg').value || document.getElementById('actual_weight_kg').value == '0') {
        document.getElementById('actual_weight_kg').value = '1.85';
    }
    document.getElementById('verification_notes').value = 'Batch processed at facility. Component yields assayed in full accordance with WEEE hydrometallurgical protocol.';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
