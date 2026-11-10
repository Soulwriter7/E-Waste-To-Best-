<?php
/**
 * Metal Recovery & Value Estimator Tool
 * Project: E-Waste to Best
 */

$pageTitle = "Metal Recovery & Value Calculator - E-Waste to Best";
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

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

// Fetch benchmark metal rates
$metalRates = getMetalRates($pdo);

// Handle optional server-side calculation for noscript / direct forms
$selectedCatId = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : ($categories[0]['id'] ?? 1);
$inputQty = isset($_GET['qty']) ? (float)$_GET['qty'] : 1;
if ($inputQty <= 0) $inputQty = 1;

$calculatedResult = null;
if (!empty($categories)) {
    $calculatedResult = calculateEstimatedRecovery($pdo, $selectedCatId, $inputQty);
}
?>

<div class="bg-dark text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-3 py-1 rounded-pill small mb-2">
                    <i class="bi bi-cpu-fill me-1"></i> METALLURGICAL ESTIMATION ENGINE
                </span>
                <h1 class="display-5 fw-bold mb-3">Precious & Base Metal Recovery Calculator</h1>
                <p class="text-light text-opacity-75 lead mb-0">
                    Calculate theoretical recovery yields for <strong>Gold (Au), Silver (Ag), Copper (Cu), and Aluminium (Al)</strong> based on peer-reviewed EPA and USGS e-waste characterization benchmarks.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="p-3 rounded-3 bg-secondary bg-opacity-25 border border-secondary border-opacity-50 text-start small">
                    <div class="fw-bold text-warning mb-1"><i class="bi bi-info-circle me-1"></i> Scientific Rigor Notice</div>
                    <span class="text-light text-opacity-75">
                        These figures represent <em>mathematical benchmark projections</em> prior to destructive chemical dissolution and certified lab assays.
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Scientific Disclaimer Banner -->
    <div class="disclaimer-badge warning mb-4 shadow-sm">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
            <div>
                <strong>IMPORTANT SCIENTIFIC DISCLAIMER:</strong>
                All metal weights and currency values displayed below are <strong>theoretical approximations</strong> calculated from average component density in published recycling literature (USGS Circular 1400 / EPA WEEE Characterization). Actual recovered yields vary based on manufacturer, model generation, board thickness, and condition. Final verified payouts are only issued after physical laboratory assays at certified collection centers.
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Input Form Column -->
        <div class="col-lg-5">
            <div class="card card-modern p-4 sticky-top" style="top: 100px;">
                <h4 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-emerald"></i> Configure Device Input
                </h4>
                <p class="text-secondary small mb-4">Select the device category and enter the batch size to see live metallurgical estimates.</p>

                <div class="mb-3">
                    <label for="deviceCategory" class="form-label fw-semibold">Electronic Device Category</label>
                    <select class="form-select form-select-lg" id="deviceCategory">
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= ($cat['id'] == $selectedCatId) ? 'selected' : '' ?>
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
                        <?php else: ?>
                            <option value="1" data-copper="15.2" data-aluminium="3.5" data-silver="0.25" data-gold="0.024" data-co2="18.5" data-toxic="0.18" data-unit="unit">
                                Smartphone / Mobile Phone
                            </option>
                            <option value="2" data-copper="185.0" data-aluminium="310.0" data-silver="1.85" data-gold="0.195" data-co2="42.0" data-toxic="1.45" data-unit="unit">
                                Laptop Computer
                            </option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="deviceQuantity" class="form-label fw-semibold">
                        Quantity / Weight (<span id="unitLabel" class="text-primary fw-bold">unit</span>)
                    </label>
                    <div class="input-group input-group-lg">
                        <input type="number" class="form-control" id="deviceQuantity" value="<?= $inputQty ?>" min="0.1" step="any">
                        <span class="input-group-text bg-light text-secondary"><i class="bi bi-hash"></i></span>
                    </div>
                    <div class="form-text small">Enter whole units or decimal weight for bulk scrap.</div>
                </div>

                <!-- Active Market Rates Snapshot -->
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-dark">Active Benchmark Scrap Rates</span>
                        <span class="badge bg-secondary">Live</span>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary">
                        <span>Gold (Au): <strong>₹ <?= number_format($metalRates['Gold']['rate'] ?? 7150, 2) ?>/g</strong></span>
                        <span>Silver (Ag): <strong>₹ <?= number_format($metalRates['Silver']['rate'] ?? 88.5, 2) ?>/g</strong></span>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary mt-1">
                        <span>Copper (Cu): <strong>₹ <?= number_format($metalRates['Copper']['rate'] ?? 0.82, 2) ?>/g</strong></span>
                        <span>Aluminium (Al): <strong>₹ <?= number_format($metalRates['Aluminium']['rate'] ?? 0.24, 2) ?>/g</strong></span>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/user/pickup-request.php" class="btn btn-emerald btn-lg w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-truck"></i> Schedule Pickup for this Device
                </a>
            </div>
        </div>

        <!-- Live Estimate Output Column -->
        <div class="col-lg-7">
            <!-- Total Monetary Valuation Card -->
            <div class="card card-modern p-4 mb-4 bg-emerald bg-opacity-10 border border-success border-opacity-30">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <div>
                        <span class="badge bg-success mb-1">THEORETICAL ESTIMATE</span>
                        <h5 class="text-dark fw-bold mb-0">Total Estimated Scrap Recovery Value</h5>
                        <small class="text-secondary">Sum total of extracted precious & industrial metals</small>
                    </div>
                    <div class="text-sm-end">
                        <div class="display-6 fw-extrabold text-success" id="totalEstimatedValue">
                            ₹ <?= number_format($calculatedResult['total_value'] ?? 368.13, 2) ?>
                        </div>
                        <small class="text-secondary">(Indicative Valuation)</small>
                    </div>
                </div>
            </div>

            <!-- 4 Metals Detailed Grid -->
            <h5 class="fw-bold mb-3">Extracted Metal Yield Breakdown</h5>
            <div class="row g-3 mb-4">
                <!-- Gold -->
                <div class="col-sm-6">
                    <div class="card card-modern p-3 h-100 border-start border-4 border-warning">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metal-pill metal-pill-gold">Au &bull; Gold</span>
                                <h6 class="fw-bold mt-2 mb-0">Recoverable Pure Gold</h6>
                            </div>
                            <i class="bi bi-gem text-warning fs-4"></i>
                        </div>
                        <div class="fs-4 fw-bold text-dark mb-1" id="goldYield">
                            <?= number_format($calculatedResult['gold_g'] ?? 0.048, 4) ?> g
                        </div>
                        <div class="d-flex justify-content-between small text-secondary">
                            <span>Benchmark Value:</span>
                            <strong class="text-dark" id="goldValue">₹ <?= number_format($calculatedResult['gold_value'] ?? 343.20, 2) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Silver -->
                <div class="col-sm-6">
                    <div class="card card-modern p-3 h-100 border-start border-4 border-secondary">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metal-pill metal-pill-silver">Ag &bull; Silver</span>
                                <h6 class="fw-bold mt-2 mb-0">Recoverable Pure Silver</h6>
                            </div>
                            <i class="bi bi-disc text-secondary fs-4"></i>
                        </div>
                        <div class="fs-4 fw-bold text-dark mb-1" id="silverYield">
                            <?= number_format($calculatedResult['silver_g'] ?? 0.50, 3) ?> g
                        </div>
                        <div class="d-flex justify-content-between small text-secondary">
                            <span>Benchmark Value:</span>
                            <strong class="text-dark" id="silverValue">₹ <?= number_format($calculatedResult['silver_value'] ?? 44.25, 2) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Copper -->
                <div class="col-sm-6">
                    <div class="card card-modern p-3 h-100 border-start border-4 border-danger">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metal-pill metal-pill-copper">Cu &bull; Copper</span>
                                <h6 class="fw-bold mt-2 mb-0">Refined Copper</h6>
                            </div>
                            <i class="bi bi-lightning text-danger fs-4"></i>
                        </div>
                        <div class="fs-4 fw-bold text-dark mb-1" id="copperYield">
                            <?= number_format($calculatedResult['copper_g'] ?? 30.4, 2) ?> g
                        </div>
                        <div class="d-flex justify-content-between small text-secondary">
                            <span>Benchmark Value:</span>
                            <strong class="text-dark" id="copperValue">₹ <?= number_format($calculatedResult['copper_value'] ?? 24.93, 2) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Aluminium -->
                <div class="col-sm-6">
                    <div class="card card-modern p-3 h-100 border-start border-4 border-info">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="metal-pill metal-pill-aluminium">Al &bull; Aluminium</span>
                                <h6 class="fw-bold mt-2 mb-0">Shielding Aluminium</h6>
                            </div>
                            <i class="bi bi-shield text-info fs-4"></i>
                        </div>
                        <div class="fs-4 fw-bold text-dark mb-1" id="aluminiumYield">
                            <?= number_format($calculatedResult['aluminium_g'] ?? 7.0, 2) ?> g
                        </div>
                        <div class="d-flex justify-content-between small text-secondary">
                            <span>Benchmark Value:</span>
                            <strong class="text-dark" id="aluminiumValue">₹ <?= number_format($calculatedResult['aluminium_value'] ?? 1.68, 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Environmental Protection Score Card -->
            <div class="card card-modern p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-tree text-success"></i> Projected Ecological Savings
                </h5>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light border text-center">
                            <span class="text-secondary small d-block mb-1">Carbon (CO₂) Offset</span>
                            <span class="fs-4 fw-bold text-success" id="co2Saving">
                                <?= number_format($calculatedResult['co2_saved_kg'] ?? 37.0, 1) ?> kg
                            </span>
                            <span class="text-muted small d-block mt-1">Equivalent to ~2 mature trees/yr</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light border text-center">
                            <span class="text-secondary small d-block mb-1">Toxic Landfill Diverted</span>
                            <span class="fs-4 fw-bold text-primary" id="toxicDiverted">
                                <?= number_format($calculatedResult['toxic_diverted_kg'] ?? 0.36, 2) ?> kg
                            </span>
                            <span class="text-muted small d-block mt-1">Lead & Mercury sealed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scientific Reference Table -->
            <div class="card card-modern p-4 bg-light border-0">
                <h6 class="fw-bold mb-2"><i class="bi bi-journal-text me-1"></i> Scientific Baseline References</h6>
                <p class="text-secondary small mb-0">
                    Yield formulas derived from: <em>U.S. Geological Survey (USGS) Circular 1400: Obsolete Computers, Gold Availability, and Environmental Perspectives</em> and <em>United Nations Environment Programme (UNEP) Basel Convention Technical Guidelines on E-Waste</em>. Recovery efficiency assumed at 92-95% hydrometallurgical extraction standard.
                </p>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
