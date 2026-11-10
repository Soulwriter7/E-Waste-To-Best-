<?php
/**
 * Schedule E-Waste Pickup Request
 * Project: E-Waste to Best
 */

$pageTitle = "Schedule E-Waste Pickup - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/csrf.php';

$userId = (int)$_SESSION['user_id'];
$error = '';
$preselectedCenterId = isset($_GET['center_id']) ? (int)$_GET['center_id'] : null;

// Fetch user profile details for autofill
$currentUser = [];
if ($pdo) {
    try {
        $uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $uStmt->execute([$userId]);
        $currentUser = $uStmt->fetch() ?: [];
    } catch (PDOException $e) {
        $currentUser = [];
    }
}

// Fetch active device categories
$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM device_categories WHERE is_active = 1 ORDER BY id ASC");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        $categories = [];
    }
}

// Fetch active collection centers
$centers = [];
if ($pdo) {
    try {
        $cStmt = $pdo->query("SELECT * FROM collection_centers WHERE is_active = 1 ORDER BY city ASC, center_name ASC");
        $centers = $cStmt->fetchAll();
    } catch (PDOException $e) {
        $centers = [];
    }
}

// Fetch metal rates for client calculation
$metalRates = getMetalRates($pdo);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $error = 'Security check failed (CSRF token invalid). Please refresh and submit again.';
    } else {
        $categoryId = (int)($_POST['device_category_id'] ?? 0);
        $quantity = (float)($_POST['quantity'] ?? 0);
        $itemCondition = $_POST['item_condition'] ?? 'Dead / Scrap';
        $centerId = !empty($_POST['center_id']) ? (int)$_POST['center_id'] : null;
        $pickupAddress = trim($_POST['pickup_address'] ?? '');
        $pickupCity = trim($_POST['pickup_city'] ?? '');
        $pickupPincode = trim($_POST['pickup_pincode'] ?? '');
        $preferredDate = $_POST['preferred_date'] ?? '';
        $preferredSlot = $_POST['preferred_slot'] ?? '';
        $specialNotes = trim($_POST['special_notes'] ?? '');

        // Validation
        if ($categoryId <= 0 || $quantity <= 0) {
            $error = 'Please select a valid device category and enter a quantity greater than zero.';
        } elseif (empty($pickupAddress) || empty($pickupCity) || empty($pickupPincode)) {
            $error = 'Please provide complete pickup address details.';
        } elseif (empty($preferredDate) || empty($preferredSlot)) {
            $error = 'Please select your preferred collection date and time slot.';
        } elseif (strtotime($preferredDate) < strtotime(date('Y-m-d'))) {
            $error = 'Preferred collection date must be scheduled for a future date.';
        } elseif (!$pdo) {
            $error = 'Database is offline. Unable to complete booking.';
        } else {
            try {
                // Compute theoretical recovery estimate
                $est = calculateEstimatedRecovery($pdo, $categoryId, $quantity);
                if (!$est) {
                    $error = 'Unable to compute metallurgical yield. Category may be inactive.';
                } else {
                    $requestCode = generateRequestCode();

                    $insertStmt = $pdo->prepare("
                        INSERT INTO pickup_requests (
                            request_code, user_id, center_id, device_category_id, quantity,
                            pickup_address, pickup_city, pickup_pincode, preferred_date, preferred_slot,
                            item_condition, special_notes,
                            est_copper_g, est_aluminium_g, est_silver_g, est_gold_g, est_total_value, est_co2_saved_kg,
                            is_verified, status
                        ) VALUES (
                            ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?,
                            ?, ?,
                            ?, ?, ?, ?, ?, ?,
                            0, 'Pending'
                        )
                    ");

                    $insertStmt->execute([
                        $requestCode,
                        $userId,
                        $centerId,
                        $categoryId,
                        $quantity,
                        $pickupAddress,
                        $pickupCity,
                        $pickupPincode,
                        $preferredDate,
                        $preferredSlot,
                        $itemCondition,
                        $specialNotes,
                        $est['copper_g'],
                        $est['aluminium_g'],
                        $est['silver_g'],
                        $est['gold_g'],
                        $est['total_value'],
                        $est['co2_saved_kg']
                    ]);

                    setFlash('success', 'Pickup request successfully booked! Your booking code is <strong>' . $requestCode . '</strong>.');
                    header('Location: ' . BASE_URL . '/user/view-request.php?code=' . urlencode($requestCode));
                    exit;
                }
            } catch (PDOException $e) {
                $error = 'Failed to submit pickup request: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-dark text-white py-4 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <span class="badge bg-emerald text-white small mb-1">SECURE BOOKING</span>
        <h2 class="fw-bold mb-1">Schedule Certified Doorstep E-Waste Pickup</h2>
        <p class="text-secondary small mb-0">Book an authorized collector to safely intake and assay your discarded electronics.</p>
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
        <!-- Booking Form -->
        <div class="col-lg-7">
            <div class="card card-modern p-4 p-md-5 bg-white">
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-calendar2-plus text-emerald"></i> Pickup Details
                </h4>

                <form method="POST" action="<?= BASE_URL ?>/user/pickup-request.php">
                    <?= csrfField() ?>

                    <!-- Step 1: Device Configuration -->
                    <h6 class="text-uppercase text-secondary fw-bold small mb-3">1. Electronic Scrap Details</h6>
                    
                    <div class="mb-3">
                        <label for="deviceCategory" class="form-label small fw-semibold">Device Category</label>
                        <select class="form-select form-select-lg" id="deviceCategory" name="device_category_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    data-copper="<?= $cat['copper_yield_g'] ?>"
                                    data-aluminium="<?= $cat['aluminium_yield_g'] ?>"
                                    data-silver="<?= $cat['silver_yield_g'] ?>"
                                    data-gold="<?= $cat['gold_yield_g'] ?>"
                                    data-co2="<?= $cat['co2_saving_kg'] ?>"
                                    data-toxic="<?= $cat['toxic_diverted_kg'] ?>"
                                    data-unit="<?= $cat['unit_type'] ?>">
                                    <?= sanitize($cat['category_name']) ?> (<?= $cat['unit_type'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="deviceQuantity" class="form-label small fw-semibold">Quantity / Bulk Weight</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="deviceQuantity" name="quantity" value="1" min="0.1" step="any" required>
                                <span class="input-group-text bg-light text-secondary" id="unitLabel">unit</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="itemCondition" class="form-label small fw-semibold">Physical Device Condition</label>
                            <select class="form-select" id="itemCondition" name="item_condition">
                                <option value="Dead / Scrap" selected>Dead / Scrap / Burnt</option>
                                <option value="Partially Working">Partially Working / Damaged Screen</option>
                                <option value="Working">Working / Obsolete Spec</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Step 2: Collection Center & Slot -->
                    <h6 class="text-uppercase text-secondary fw-bold small mb-3">2. Processing Depot & Time Slot</h6>

                    <div class="mb-3">
                        <label for="centerSelect" class="form-label small fw-semibold">Designated Recovery Center (Optional)</label>
                        <select class="form-select" id="centerSelect" name="center_id">
                            <option value="">Auto-Assign to Nearest Available Center</option>
                            <?php foreach ($centers as $center): ?>
                                <option value="<?= $center['id'] ?>" <?= ($center['id'] === $preselectedCenterId) ? 'selected' : '' ?>>
                                    <?= sanitize($center['center_name']) ?> (<?= sanitize($center['city']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">Our logistics dispatcher will route your collection to the optimal facility.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="preferredDate" class="form-label small fw-semibold">Preferred Pickup Date</label>
                            <input type="date" class="form-control" id="preferredDate" name="preferred_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+2 days')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="preferredSlot" class="form-label small fw-semibold">Preferred Time Window</label>
                            <select class="form-select" id="preferredSlot" name="preferred_slot" required>
                                <option value="Morning (9AM - 12PM)">Morning (9AM - 12PM)</option>
                                <option value="Afternoon (12PM - 3PM)">Afternoon (12PM - 3PM)</option>
                                <option value="Evening (3PM - 6PM)">Evening (3PM - 6PM)</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Step 3: Address & Instructions -->
                    <h6 class="text-uppercase text-secondary fw-bold small mb-3">3. Pickup Location & Instructions</h6>

                    <div class="mb-3">
                        <label for="pickupAddress" class="form-label small fw-semibold">Doorstep Address</label>
                        <input type="text" class="form-control" id="pickupAddress" name="pickup_address" value="<?= sanitize($currentUser['address'] ?? '') ?>" placeholder="House/Flat No, Apartment, Street, Landmark" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="pickupCity" class="form-label small fw-semibold">City</label>
                            <input type="text" class="form-control" id="pickupCity" name="pickup_city" value="<?= sanitize($currentUser['city'] ?? '') ?>" placeholder="e.g. Noida, Delhi" required>
                        </div>
                        <div class="col-md-6">
                            <label for="pickupPincode" class="form-label small fw-semibold">Postal PIN Code</label>
                            <input type="text" class="form-control" id="pickupPincode" name="pickup_pincode" value="<?= sanitize($currentUser['pincode'] ?? '') ?>" placeholder="201301" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="specialNotes" class="form-label small fw-semibold">Special Instructions for Collection Agent</label>
                        <textarea class="form-control" id="specialNotes" name="special_notes" rows="2" placeholder="e.g. Call before arrival, gates close at 6 PM, battery is detached..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-emerald btn-lg w-100 py-3 fw-bold shadow-sm">
                        <i class="bi bi-shield-check me-2"></i> Confirm & Book E-Waste Pickup
                    </button>
                </form>
            </div>
        </div>

        <!-- Real-Time Recovery Calculation Sidebar -->
        <div class="col-lg-5">
            <div class="card card-modern p-4 sticky-top" style="top: 100px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-calculator text-warning me-1"></i> Live Yield Estimate
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 small">Theoretical</span>
                </div>

                <p class="text-secondary small mb-3">
                    Projected precious metals reclaimed from this batch based on baseline EPA assay coefficients:
                </p>

                <!-- Metal Cards -->
                <div class="d-flex flex-column gap-2 mb-3">
                    <div class="p-2 px-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                        <span class="metal-pill metal-pill-gold">Au &bull; Gold</span>
                        <div class="text-end">
                            <strong class="d-block text-dark small" id="goldYield">0.000 g</strong>
                            <span class="text-muted text-xs" id="goldValue">₹ 0.00</span>
                        </div>
                    </div>

                    <div class="p-2 px-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                        <span class="metal-pill metal-pill-silver">Ag &bull; Silver</span>
                        <div class="text-end">
                            <strong class="d-block text-dark small" id="silverYield">0.000 g</strong>
                            <span class="text-muted text-xs" id="silverValue">₹ 0.00</span>
                        </div>
                    </div>

                    <div class="p-2 px-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                        <span class="metal-pill metal-pill-copper">Cu &bull; Copper</span>
                        <div class="text-end">
                            <strong class="d-block text-dark small" id="copperYield">0.00 g</strong>
                            <span class="text-muted text-xs" id="copperValue">₹ 0.00</span>
                        </div>
                    </div>

                    <div class="p-2 px-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                        <span class="metal-pill metal-pill-aluminium">Al &bull; Aluminium</span>
                        <div class="text-end">
                            <strong class="d-block text-dark small" id="aluminiumYield">0.00 g</strong>
                            <span class="text-muted text-xs" id="aluminiumValue">₹ 0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Total Valuation Box -->
                <div class="p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small">Total Theoretical Value:</span>
                        <span class="fs-4 fw-bold text-success" id="totalEstimatedValue">₹ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small text-secondary">
                        <span>Projected CO₂ Avoided:</span>
                        <strong class="text-dark" id="co2Saving">0.0 kg</strong>
                    </div>
                </div>

                <div class="disclaimer-badge warning small">
                    <i class="bi bi-shield-exclamation me-1"></i>
                    <strong>System Assurance:</strong> A pickup is only officially booked once submitted and assigned a unique reference code. Final verified recovery is recorded after certified lab assay.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.METAL_RATES = {
    Gold: <?= (float)($metalRates['Gold']['rate'] ?? 7150.00) ?>,
    Silver: <?= (float)($metalRates['Silver']['rate'] ?? 88.50) ?>,
    Copper: <?= (float)($metalRates['Copper']['rate'] ?? 0.82) ?>,
    Aluminium: <?= (float)($metalRates['Aluminium']['rate'] ?? 0.24) ?>
};
</script>
<script src="<?= BASE_URL ?>/assets/js/estimator.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
