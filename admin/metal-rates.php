<?php
/**
 * Administrator Metal Market Rates Management
 * Project: E-Waste to Best
 */

$pageTitle = "Metal Market Rates - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../includes/csrf.php';

$adminId = (int)$_SESSION['user_id'];
$error = '';

// Handle Rate Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        $rates = $_POST['rates'] ?? [];
        if (!empty($rates) && is_array($rates)) {
            try {
                $upStmt = $pdo->prepare("
                    UPDATE metal_rates 
                    SET rate_per_gram = ?, updated_by = ?
                    WHERE id = ?
                ");

                foreach ($rates as $id => $val) {
                    $rateVal = (float)$val;
                    if ($rateVal > 0) {
                        $upStmt->execute([$rateVal, $adminId, (int)$id]);
                    }
                }

                setFlash('success', 'Benchmark metal market rates updated successfully.');
                header('Location: ' . BASE_URL . '/admin/metal-rates.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Failed to update rates: ' . $e->getMessage();
            }
        }
    }
}

// Fetch current rates
$metalList = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT m.*, u.fullname as updater_name 
            FROM metal_rates m
            LEFT JOIN users u ON m.updated_by = u.id
            ORDER BY m.rate_per_gram DESC
        ");
        $metalList = $stmt->fetchAll();
    } catch (PDOException $e) {
        $metalList = [];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-dark text-white py-4 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-danger small mb-1">COMMODITY PRICING</span>
                <h2 class="fw-bold mb-1">Metal Market Benchmark Rates</h2>
                <p class="text-secondary small mb-0">Configure active recovery rates (₹ INR / gram) used by the public estimation calculator and dispatch valuation.</p>
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

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-modern p-4 p-md-5 bg-white shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-currency-exchange text-warning me-1"></i> Benchmark Scrap Pricing
                    </h4>
                    <span class="badge bg-secondary">Base Currency: INR (₹)</span>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/admin/metal-rates.php">
                    <?= csrfField() ?>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th>Metal Name</th>
                                    <th>Symbol</th>
                                    <th>Rate (₹ per Gram)</th>
                                    <th>Last Updated</th>
                                    <th>Modifier</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($metalList as $m): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?= sanitize($m['metal_name']) ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= sanitize($m['symbol']) ?></span>
                                        </td>
                                        <td style="max-width: 180px;">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" class="form-control fw-bold" name="rates[<?= $m['id'] ?>]" value="<?= (float)$m['rate_per_gram'] ?>" required>
                                            </div>
                                        </td>
                                        <td class="small text-secondary">
                                            <?= date('d M Y, h:i A', strtotime($m['last_updated'])) ?>
                                        </td>
                                        <td class="small text-muted">
                                            <?= sanitize($m['updater_name'] ?? 'System Seeder') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 rounded-3 bg-light border mb-4 small text-secondary">
                        <i class="bi bi-info-circle me-1"></i>
                        Updates made here immediately propagate to the live JavaScript estimator on the homepage, the 4-metal recovery page, and booking calculations.
                    </div>

                    <button type="submit" class="btn btn-emerald py-2 px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> Save Updated Commodity Rates
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
