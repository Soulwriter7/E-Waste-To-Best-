<?php
/**
 * Modern Homepage
 * Project: E-Waste to Best - Smart E-Waste Recycling & Metal Recovery System
 */

$pageTitle = "E-Waste to Best - Smart E-Waste Recycling & Metal Recovery System";
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Fetch metal rates & global statistics
$metalRates = getMetalRates($pdo);
$stats = getGlobalImpactStats($pdo);

// Fetch device categories for the quick calculator widget
$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM device_categories WHERE is_active = 1 ORDER BY id ASC");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        $categories = [];
    }
}
?>

<!-- Hero Section -->
<section class="hero-gradient">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-success bg-opacity-25 border border-success border-opacity-50 text-white mb-3">
                    <i class="bi bi-recycle text-success"></i>
                    <span class="small fw-semibold">Next-Gen Circular Tech Platform</span>
                </div>
                <h1 class="display-4 fw-extrabold text-white mb-3 lh-sm">
                    Transform Electronic Scrap Into <span class="eco-highlight">Precious Resources</span>
                </h1>
                <p class="hero-tagline mb-4 text-light text-opacity-75">
                    Harness smart metallurgical estimation to evaluate recoverable <strong>Gold, Silver, Copper, and Aluminium</strong> inside discarded electronics. Schedule authorized doorstep collections and track verified environmental savings.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>/estimator.php" class="btn btn-emerald btn-lg px-4 d-inline-flex align-items-center">
                        <i class="bi bi-calculator me-2"></i> Estimate Metal Value
                    </a>
                    <a href="<?= BASE_URL ?>/user/pickup-request.php" class="btn btn-outline-light btn-lg px-4 d-inline-flex align-items-center">
                        <i class="bi bi-truck me-2"></i> Schedule Pickup
                    </a>
                    <a href="<?= BASE_URL ?>/centers.php" class="btn btn-dark btn-lg border border-secondary px-3 d-inline-flex align-items-center">
                        <i class="bi bi-geo-alt me-1 text-emerald"></i> Find Centers
                    </a>
                </div>
            </div>

            <div class="col-lg-5">
                <!-- Interactive Quick Calculator Preview Card -->
                <div class="card card-modern shadow-lg p-4 bg-white text-dark">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Metal Estimator
                        </h5>
                        <span class="badge bg-success bg-opacity-10 text-success fw-semibold">Live Benchmark</span>
                    </div>
                    <p class="text-secondary small mb-3">
                        Select an item to see theoretical recoverable precious metals:
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Electronic Device</label>
                        <select class="form-select" id="deviceCategory">
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        data-copper="<?= $cat['copper_yield_g'] ?>"
                                        data-aluminium="<?= $cat['aluminium_yield_g'] ?>"
                                        data-silver="<?= $cat['silver_yield_g'] ?>"
                                        data-gold="<?= $cat['gold_yield_g'] ?>"
                                        data-co2="<?= $cat['co2_saving_kg'] ?>"
                                        data-toxic="<?= $cat['toxic_diverted_kg'] ?>"
                                        data-unit="<?= $cat['unit_type'] ?>">
                                        <?= sanitize($cat['category_name']) ?> (per <?= $cat['unit_type'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1" data-copper="15.2" data-aluminium="3.5" data-silver="0.25" data-gold="0.024" data-co2="18.5" data-toxic="0.18" data-unit="unit">
                                    Smartphone / Mobile Phone (per unit)
                                </option>
                                <option value="2" data-copper="185.0" data-aluminium="310.0" data-silver="1.85" data-gold="0.195" data-co2="42.0" data-toxic="1.45" data-unit="unit">
                                    Laptop Computer (per unit)
                                </option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Quantity (<span id="unitLabel">unit</span>)</label>
                        <input type="number" class="form-control" id="deviceQuantity" value="2" min="1" max="1000">
                    </div>

                    <!-- Quick Estimated Metals Preview -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 rounded-3 bg-light border text-center">
                                <span class="metal-pill metal-pill-gold mb-1">Au Gold</span>
                                <div class="fw-bold small" id="goldYield">0.0480 g</div>
                                <div class="text-secondary text-xs" id="goldValue">₹ 343.20</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 bg-light border text-center">
                                <span class="metal-pill metal-pill-copper mb-1">Cu Copper</span>
                                <div class="fw-bold small" id="copperYield">30.40 g</div>
                                <div class="text-secondary text-xs" id="copperValue">₹ 24.93</div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-secondary small d-block">Est. Scrap Value</span>
                            <span class="fs-5 fw-bold text-success" id="totalEstimatedValue">₹ 368.13</span>
                        </div>
                        <div class="text-end">
                            <span class="text-secondary small d-block">Est. CO₂ Offset</span>
                            <span class="fw-bold text-dark" id="co2Saving">37.0 kg</span>
                        </div>
                    </div>

                    <a href="<?= BASE_URL ?>/estimator.php" class="btn btn-outline-success btn-sm w-100">
                        View Comprehensive 4-Metal Analysis <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Metal Commodity Rate Ticker -->
<div class="bg-dark py-2 border-top border-bottom border-secondary border-opacity-25 text-white">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 text-secondary small">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark fw-bold">MARKET RATES</span>
                <span>Active Benchmark Scrap Value (INR per Gram):</span>
            </div>
            <div class="d-flex flex-wrap gap-4">
                <span><strong class="text-warning">Gold (Au):</strong> ₹ <?= number_format($metalRates['Gold']['rate'] ?? 7150, 2) ?>/g</span>
                <span><strong class="text-light">Silver (Ag):</strong> ₹ <?= number_format($metalRates['Silver']['rate'] ?? 88.5, 2) ?>/g</span>
                <span><strong class="text-danger">Copper (Cu):</strong> ₹ <?= number_format($metalRates['Copper']['rate'] ?? 0.82, 2) ?>/g</span>
                <span><strong class="text-info">Aluminium (Al):</strong> ₹ <?= number_format($metalRates['Aluminium']['rate'] ?? 0.24, 2) ?>/g</span>
            </div>
        </div>
    </div>
</div>

<!-- Global Impact Live Statistics -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-emerald text-white px-3 py-2 rounded-pill small mb-2">COLLECTIVE CONTRIBUTION</span>
            <h2 class="fw-bold">Platform Environmental & Metal Footprint</h2>
            <p class="text-secondary max-w-600 mx-auto">
                Real-time cumulative data from certified collection centers and university recycling drives.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-sm-6">
                <div class="stat-card shadow-sm h-100 border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success">Verified</span>
                    </div>
                    <div class="fs-3 fw-bold mb-1"><?= number_format($stats['total_pickups'] ?? 128) ?></div>
                    <div class="text-secondary small">E-Waste Batches Collected</div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="stat-card shadow-sm h-100 border-start border-4 border-primary">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-cloud-arrow-down"></i>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary">Carbon Offsets</span>
                    </div>
                    <div class="fs-3 fw-bold mb-1"><?= number_format($stats['total_co2_kg'] ?? 4820, 1) ?> kg</div>
                    <div class="text-secondary small">Greenhouse Gas CO₂ Avoided</div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="stat-card shadow-sm h-100 border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-gem"></i>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning">Precious Metal</span>
                    </div>
                    <div class="fs-3 fw-bold mb-1"><?= number_format($stats['total_gold_g'] ?? 48.65, 3) ?> g</div>
                    <div class="text-secondary small">Pure Gold (Au) Reclaimed</div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="stat-card shadow-sm h-100 border-start border-4 border-danger">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-lightning"></i>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger">Base Metals</span>
                    </div>
                    <div class="fs-3 fw-bold mb-1"><?= number_format($stats['total_copper_kg'] ?? 312.4, 1) ?> kg</div>
                    <div class="text-secondary small">Refined Copper (Cu) Saved</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- The 4-Step E-Waste to Best Workflow -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-dark text-emerald px-3 py-2 rounded-pill small mb-2">CIRCULAR PROCESS</span>
            <h2 class="fw-bold">How the Smart Recovery System Works</h2>
            <p class="text-secondary">From discarded electronics in your dorm or home to high-grade industrial re-entry.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card card-modern h-100 p-4 text-center">
                    <div class="step-circle mx-auto mb-3">1</div>
                    <h5 class="fw-bold mb-2">Inventory & Estimate</h5>
                    <p class="text-secondary small">
                        Input your unused laptops, motherboards, or phones to calculate estimated grams of Gold, Silver, Copper, and Aluminium.
                    </p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-modern h-100 p-4 text-center">
                    <div class="step-circle mx-auto mb-3">2</div>
                    <h5 class="fw-bold mb-2">Schedule Pickup</h5>
                    <p class="text-secondary small">
                        Choose your nearest authorized collection depot, select preferred doorstep pickup time slots, and submit your request.
                    </p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-modern h-100 p-4 text-center">
                    <div class="step-circle mx-auto mb-3">3</div>
                    <h5 class="fw-bold mb-2">Laboratory Assay</h5>
                    <p class="text-secondary small">
                        Certified recycling facilities dismantle devices, safely isolate hazardous lead and mercury, and assay actual recoverable weights.
                    </p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-modern h-100 p-4 text-center">
                    <div class="step-circle mx-auto mb-3">4</div>
                    <h5 class="fw-bold mb-2">Verified Best</h5>
                    <p class="text-secondary small">
                        Receive a verified environmental impact certificate, track lifecycle status, and claim recovered scrap payouts.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Platform Feature Highlights -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill small mb-2">CORE CAPABILITIES</span>
                <h2 class="fw-bold mb-3">Engineering Clean Tech for a Zero-Waste Future</h2>
                <p class="text-secondary mb-4">
                    Electronic waste contains over 50 times more gold and copper per ton than natural ores mined from the ground. Our system prevents toxic dumping into landfills and gives students and institutions a clear, transparent window into the value of e-scrap.
                </p>

                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3">
                        <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success fs-4 h-fit">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Scientific Transparency</h6>
                            <p class="text-secondary small mb-0">Calculated baseline recovery coefficients are derived from empirical USGS & EPA studies, clearly marked until laboratory verification.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary fs-4 h-fit">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Authenticated Collection Depots</h6>
                            <p class="text-secondary small mb-0">Connect directly with licensed recycling facilities across major cities with operating hours and accepted material filters.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="p-2 rounded-3 bg-warning bg-opacity-10 text-warning fs-4 h-fit">
                            <i class="bi bi-tree"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Environmental Dashboard</h6>
                            <p class="text-secondary small mb-0">Track real-time greenhouse gas diversion, toxic chemicals prevented from groundwater, and precious metals conserved.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-modern p-4 bg-light border-0 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Resource Richness Comparison</h5>
                        <span class="badge bg-danger">Mining vs E-Waste</span>
                    </div>
                    <p class="text-secondary small mb-4">
                        Did you know? Gold yield per ton of e-waste motherboards dramatically outperforms traditional ore mining:
                    </p>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span>1 Ton of Mobile Phone PCBs</span>
                            <span class="text-success fw-bold">~250 to 350 grams Gold</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: 90%;"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span>1 Ton of Gold Ore from Mine</span>
                            <span class="text-secondary fw-bold">~1 to 5 grams Gold</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-secondary" style="width: 6%;"></div>
                        </div>
                    </div>

                    <div class="disclaimer-badge">
                        <i class="bi bi-info-circle me-1"></i> Source: United Nations University (UNU) Global E-waste Monitor Report. Urban mining e-waste is up to 50x richer in precious metals than primary geological extraction.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 bg-dark text-white">
    <div class="container text-center py-4">
        <h2 class="display-6 fw-bold mb-3">Ready to Convert Your E-Waste Into Best?</h2>
        <p class="text-light text-opacity-75 max-w-600 mx-auto mb-4">
            Join conscious citizens and college campuses creating a pollution-free future. Register your account and book your first collection today.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-emerald btn-lg px-4">
                <i class="bi bi-person-plus me-2"></i> Join the Initiative
            </a>
            <a href="<?= BASE_URL ?>/awareness.php" class="btn btn-outline-light btn-lg px-4">
                <i class="bi bi-book me-2"></i> Learn E-Waste Risks
            </a>
        </div>
    </div>
</section>

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
