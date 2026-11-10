<?php
/**
 * User Environmental Impact Dashboard & Green Certificate
 * Project: E-Waste to Best
 */

$pageTitle = "Environmental Impact & Green Certificate - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$userId = (int)$_SESSION['user_id'];

// Default aggregate metrics
$impact = [
    'co2_saved_kg' => 0,
    'gold_g' => 0,
    'silver_g' => 0,
    'copper_g' => 0,
    'aluminium_g' => 0,
    'total_requests' => 0,
    'total_units' => 0,
    'trees_equivalent' => 0,
    'toxic_diverted_kg' => 0
];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_requests,
                COALESCE(SUM(quantity), 0) as total_units,
                COALESCE(SUM(est_co2_saved_kg), 0) as co2_saved_kg,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_gold_g ELSE est_gold_g END), 0) as gold_g,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_silver_g ELSE est_silver_g END), 0) as silver_g,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_copper_g ELSE est_copper_g END), 0) as copper_g,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_aluminium_g ELSE est_aluminium_g END), 0) as aluminium_g
            FROM pickup_requests
            WHERE user_id = ? AND status != 'Cancelled'
        ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if ($row) {
            $impact['total_requests'] = (int)$row['total_requests'];
            $impact['total_units'] = (float)$row['total_units'];
            $impact['co2_saved_kg'] = (float)$row['co2_saved_kg'];
            $impact['gold_g'] = (float)$row['gold_g'];
            $impact['silver_g'] = (float)$row['silver_g'];
            $impact['copper_g'] = (float)$row['copper_g'];
            $impact['aluminium_g'] = (float)$row['aluminium_g'];

            // 1 mature tree absorbs ~21.77 kg of CO2 per year
            $impact['trees_equivalent'] = round($impact['co2_saved_kg'] / 21.77, 1);
            // Toxic material diverted approx calculation
            $impact['toxic_diverted_kg'] = round($impact['total_units'] * 0.45, 2);
        }
    } catch (PDOException $e) {
        // Fallback defaults
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-dark text-white py-4 border-bottom border-secondary border-opacity-25 d-print-none">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 small mb-1">
                    ECOLOGICAL AUDIT
                </span>
                <h2 class="fw-bold mb-1">Your Personal Environmental Impact</h2>
                <p class="text-secondary small mb-0">Visual audit of greenhouse gases offset, landfill toxins averted, and precious metals reclaimed.</p>
            </div>
            <div>
                <button type="button" class="btn btn-emerald d-inline-flex align-items-center" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i> Print Green Certificate
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <?php renderFlash(); ?>

    <!-- KPI Summary Row -->
    <div class="row g-4 mb-5 d-print-none">
        <div class="col-lg-3 col-sm-6">
            <div class="card card-modern p-4 text-center border-start border-4 border-success h-100">
                <i class="bi bi-cloud-arrow-down fs-1 text-success mb-2"></i>
                <div class="fs-2 fw-bold text-dark mb-1"><?= number_format($impact['co2_saved_kg'], 1) ?> kg</div>
                <div class="text-secondary small">Total CO₂ Greenhouse Gas Avoided</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-modern p-4 text-center border-start border-4 border-primary h-100">
                <i class="bi bi-tree fs-1 text-primary mb-2"></i>
                <div class="fs-2 fw-bold text-dark mb-1"><?= $impact['trees_equivalent'] ?></div>
                <div class="text-secondary small">Equivalent Tree-Years Planted</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-modern p-4 text-center border-start border-4 border-warning h-100">
                <i class="bi bi-gem fs-1 text-warning mb-2"></i>
                <div class="fs-2 fw-bold text-dark mb-1"><?= number_format($impact['gold_g'], 3) ?> g</div>
                <div class="text-secondary small">Pure Reclaimed Gold (Au)</div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-modern p-4 text-center border-start border-4 border-danger h-100">
                <i class="bi bi-shield-x fs-1 text-danger mb-2"></i>
                <div class="fs-2 fw-bold text-dark mb-1"><?= number_format($impact['toxic_diverted_kg'], 2) ?> kg</div>
                <div class="text-secondary small">Toxic Heavy Metals Kept Out of Soil</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-5 d-print-none">
        <div class="col-lg-6">
            <div class="card card-modern p-4 h-100">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart text-emerald"></i> Recovered Metal Breakdown (Grams)
                </h5>
                <div style="height: 280px; position: relative;">
                    <canvas id="metalsPieChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-modern p-4 h-100">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart text-primary"></i> Environmental Savings Balance
                </h5>
                <div style="height: 280px; position: relative;">
                    <canvas id="impactBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Printable Official Green Certificate -->
    <div class="card card-modern p-5 bg-white border-4 border-success shadow-sm printable-area">
        <div class="p-4 p-md-5 border border-success border-2 rounded-3 position-relative" style="background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.05), transparent 70%);">
            
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle p-3 mb-3 shadow-sm">
                    <i class="bi bi-award-fill fs-1"></i>
                </div>
                <h6 class="text-uppercase text-success fw-bold tracking-wider mb-1">CERTIFICATE OF SUSTAINABLE CONTRIBUTION</h6>
                <h2 class="display-6 fw-bold brand-font text-dark mb-2">Institutional Green Citizen Award</h2>
                <div class="text-secondary small">Awarded under the Smart E-Waste Recycling & Metal Recovery Initiative</div>
            </div>

            <div class="text-center max-w-600 mx-auto mb-4">
                <p class="lead mb-2">This is officially presented to:</p>
                <h3 class="fw-bold text-dark border-bottom pb-2 mb-3 mx-auto" style="max-width: 400px;">
                    <?= sanitize($_SESSION['user_name'] ?? 'Conscious Citizen') ?>
                </h3>
                <p class="text-secondary small">
                    In recognition of exemplary commitment toward circular electronics management. Through verified collection requests, this participant has diverted obsolete electronics away from municipal landfills, directly returning valuable metallurgical resources back to industry.
                </p>
            </div>

            <div class="row g-3 text-center mb-4">
                <div class="col-4">
                    <div class="p-3 rounded bg-light border">
                        <div class="text-secondary text-xs text-uppercase fw-bold">Carbon Offset</div>
                        <div class="fs-4 fw-bold text-success"><?= number_format($impact['co2_saved_kg'], 1) ?> kg</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-3 rounded bg-light border">
                        <div class="text-secondary text-xs text-uppercase fw-bold">Gold Reclaimed</div>
                        <div class="fs-4 fw-bold text-warning"><?= number_format($impact['gold_g'], 3) ?> g</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-3 rounded bg-light border">
                        <div class="text-secondary text-xs text-uppercase fw-bold">Copper Saved</div>
                        <div class="fs-4 fw-bold text-danger"><?= number_format($impact['copper_g'], 1) ?> g</div>
                    </div>
                </div>
            </div>

            <div class="row pt-4 border-top text-secondary small align-items-end">
                <div class="col-6 text-start">
                    <div class="fw-bold text-dark">Issue Date: <?= date('d F, Y') ?></div>
                    <div class="text-xs">Certificate ID: EWB-CERT-<?= strtoupper(substr(md5($userId . date('Y')), 0, 8)) ?></div>
                </div>
                <div class="col-6 text-end">
                    <div class="fw-bold text-dark">Director of Sustainability</div>
                    <div class="text-xs">E-Waste to Best Academic Initiative</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Metals Pie Chart
    const pieCtx = document.getElementById('metalsPieChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Gold (Au)', 'Silver (Ag)', 'Copper (Cu)', 'Aluminium (Al)'],
                datasets: [{
                    data: [
                        <?= (float)$impact['gold_g'] ?>,
                        <?= (float)$impact['silver_g'] ?>,
                        <?= (float)$impact['copper_g'] ?>,
                        <?= (float)$impact['aluminium_g'] ?>
                    ],
                    backgroundColor: ['#f59e0b', '#94a3b8', '#c2410c', '#64748b'],
                    borderWidth: 2
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

    // 2. Impact Bar Chart
    const barCtx = document.getElementById('impactBarChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['CO₂ Avoided (kg)', 'Trees Equivalent', 'Toxic Diverted (kg)'],
                datasets: [{
                    label: 'Impact Score',
                    data: [
                        <?= (float)$impact['co2_saved_kg'] ?>,
                        <?= (float)$impact['trees_equivalent'] ?>,
                        <?= (float)$impact['toxic_diverted_kg'] ?>
                    ],
                    backgroundColor: ['#10b981', '#0d9488', '#3b82f6'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
