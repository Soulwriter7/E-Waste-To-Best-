<?php
/**
 * Collection Centre Locator Page
 * Project: E-Waste to Best
 */

$pageTitle = "Authorized E-Waste Collection Centers - E-Waste to Best";
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Fetch all active collection centers
$centers = [];
$cities = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM collection_centers WHERE is_active = 1 ORDER BY city ASC, center_name ASC");
        $centers = $stmt->fetchAll();

        // Extract unique cities for filter dropdown
        $cityStmt = $pdo->query("SELECT DISTINCT city FROM collection_centers WHERE is_active = 1 ORDER BY city ASC");
        $cities = $cityStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $centers = [];
    }
}
?>

<div class="bg-dark text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-1 rounded-pill small mb-2">
                    <i class="bi bi-geo-alt-fill me-1"></i> VERIFIED INFRASTRUCTURE DIRECTORY
                </span>
                <h1 class="display-5 fw-bold mb-3">Authorized E-Waste Collection Centers</h1>
                <p class="text-light text-opacity-75 lead mb-0">
                    Locate state-certified e-waste drop-off depots equipped with licensed toxic component handlers, hydrometallurgical recovery links, and strict pollution controls.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="<?= BASE_URL ?>/user/pickup-request.php" class="btn btn-emerald btn-lg">
                    <i class="bi bi-truck me-1"></i> Prefer Doorstep Pickup?
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Filter and Search Bar -->
    <div class="card card-modern p-4 mb-5 bg-white shadow-sm">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <label for="centerSearchInput" class="form-label small fw-bold text-secondary">Search by Name or Keyword</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="centerSearchInput" placeholder="e.g. Okhla, MIDC, Gomti Nagar...">
                </div>
            </div>

            <div class="col-md-4">
                <label for="cityFilterSelect" class="form-label small fw-bold text-secondary">Filter by City</label>
                <select class="form-select" id="cityFilterSelect">
                    <option value="all">All Cities (<?= count($centers) ?> Centers)</option>
                    <?php foreach ($cities as $cityName): ?>
                        <option value="<?= strtolower(sanitize($cityName)) ?>"><?= sanitize($cityName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <div class="p-2 rounded-3 bg-light border text-center w-100 small text-secondary">
                    <i class="bi bi-patch-check-fill text-success me-1"></i> CPCB / State PCB Certified
                </div>
            </div>
        </div>
    </div>

    <!-- Center Cards Grid -->
    <div class="row g-4" id="centersContainer">
        <?php if (!empty($centers)): ?>
            <?php foreach ($centers as $center): ?>
                <div class="col-lg-4 col-md-6 center-card-item" data-city="<?= strtolower(sanitize($center['city'])) ?>">
                    <div class="card card-modern h-100 p-4 border-top border-4 border-success d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-success bg-opacity-10 text-success fw-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Authorized Depot
                            </span>
                            <span class="badge bg-light text-dark border">
                                <?= sanitize($center['city']) ?>, <?= sanitize($center['state']) ?>
                            </span>
                        </div>

                        <h5 class="fw-bold mb-2 text-dark"><?= sanitize($center['center_name']) ?></h5>
                        
                        <div class="text-secondary small mb-3">
                            <i class="bi bi-geo-alt text-danger me-1"></i>
                            <?= sanitize($center['address']) ?>, <?= sanitize($center['city']) ?> - <?= sanitize($center['pincode']) ?>
                        </div>

                        <hr class="my-2 border-light">

                        <div class="small mb-3">
                            <div class="text-secondary mb-1">
                                <i class="bi bi-clock text-primary me-2"></i><strong>Hours:</strong> <?= sanitize($center['operating_hours']) ?>
                            </div>
                            <div class="text-secondary mb-1">
                                <i class="bi bi-person text-secondary me-2"></i><strong>Contact:</strong> <?= sanitize($center['contact_person']) ?>
                            </div>
                            <div class="text-secondary mb-1">
                                <i class="bi bi-telephone text-success me-2"></i><a href="tel:<?= sanitize($center['phone']) ?>" class="text-decoration-none text-secondary"><?= sanitize($center['phone']) ?></a>
                            </div>
                            <div class="text-secondary">
                                <i class="bi bi-envelope text-info me-2"></i><a href="mailto:<?= sanitize($center['email']) ?>" class="text-decoration-none text-secondary"><?= sanitize($center['email']) ?></a>
                            </div>
                        </div>

                        <div class="p-2 rounded-2 bg-light border small text-secondary mb-4">
                            <div class="fw-semibold text-dark mb-1">Accepted Devices:</div>
                            <?= sanitize($center['accepted_items']) ?>
                        </div>

                        <div class="mt-auto pt-2 d-flex gap-2">
                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($center['center_name'] . ' ' . $center['address'] . ' ' . $center['city']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm flex-grow-1">
                                <i class="bi bi-map me-1"></i> Open Maps
                            </a>
                            <a href="<?= BASE_URL ?>/user/pickup-request.php?center_id=<?= $center['id'] ?>" class="btn btn-emerald btn-sm flex-grow-1">
                                <i class="bi bi-calendar2-check me-1"></i> Select Center
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-building-exclamation fs-1 text-secondary mb-3 d-block"></i>
                <h5 class="fw-bold">No Collection Centers Found</h5>
                <p class="text-secondary small">Please ensure the MySQL database `ewaste_db` has been imported using `config/seed_data.sql`.</p>
            </div>
        <?php endif; ?>

        <!-- Zero Results Message for JS filter -->
        <div class="col-12 text-center py-5" id="noCentersFound" style="display: none;">
            <i class="bi bi-search fs-1 text-secondary mb-2 d-block"></i>
            <h5 class="fw-bold">No Matching Centers Found</h5>
            <p class="text-secondary small">Try selecting "All Cities" or refining your search term.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
