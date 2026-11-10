<?php
/**
 * Administrator Collection Center Management (Full CRUD)
 * Project: E-Waste to Best
 */

$pageTitle = "Manage Collection Centers - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../includes/csrf.php';

$error = '';

// Handle Center Actions: Add, Edit, Toggle Status, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $centerName = trim($_POST['center_name'] ?? '');
            $contactPerson = trim($_POST['contact_person'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $pincode = trim($_POST['pincode'] ?? '');
            $operatingHours = trim($_POST['operating_hours'] ?? '9:00 AM - 6:00 PM (Mon-Sat)');
            $acceptedItems = trim($_POST['accepted_items'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($centerName) || empty($contactPerson) || empty($phone) || empty($email) || empty($address) || empty($city)) {
                $error = 'Please fill out all required facility details.';
            } else {
                try {
                    if ($action === 'create') {
                        $insStmt = $pdo->prepare("
                            INSERT INTO collection_centers 
                            (center_name, contact_person, phone, email, address, city, state, pincode, operating_hours, accepted_items, is_active)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $insStmt->execute([$centerName, $contactPerson, $phone, $email, $address, $city, $state, $pincode, $operatingHours, $acceptedItems, $isActive]);
                        setFlash('success', 'Collection facility "' . sanitize($centerName) . '" registered successfully.');
                    } else {
                        $centerId = (int)$_POST['center_id'];
                        $upStmt = $pdo->prepare("
                            UPDATE collection_centers
                            SET center_name = ?, contact_person = ?, phone = ?, email = ?, address = ?, city = ?, state = ?, pincode = ?, operating_hours = ?, accepted_items = ?, is_active = ?
                            WHERE id = ?
                        ");
                        $upStmt->execute([$centerName, $contactPerson, $phone, $email, $address, $city, $state, $pincode, $operatingHours, $acceptedItems, $isActive, $centerId]);
                        setFlash('success', 'Facility "' . sanitize($centerName) . '" updated successfully.');
                    }
                    header('Location: ' . BASE_URL . '/admin/centers.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Database operation failed: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $centerId = (int)$_POST['center_id'];
            try {
                $delStmt = $pdo->prepare("DELETE FROM collection_centers WHERE id = ?");
                $delStmt->execute([$centerId]);
                setFlash('info', 'Collection center removed successfully.');
                header('Location: ' . BASE_URL . '/admin/centers.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Cannot delete facility if existing pickups are assigned to it. Toggle status to Inactive instead.';
            }
        }
    }
}

// Fetch all centers
$centers = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM collection_centers ORDER BY is_active DESC, city ASC, center_name ASC");
        $centers = $stmt->fetchAll();
    } catch (PDOException $e) {
        $centers = [];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-dark text-white py-4 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-danger small mb-1">FACILITY DIRECTORY</span>
                <h2 class="fw-bold mb-1">Collection Centers Management</h2>
                <p class="text-secondary small mb-0">Add, edit, and maintain authorized physical drop-off and metallurgical recovery facilities.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-emerald" data-bs-toggle="modal" data-bs-target="#addCenterModal">
                    <i class="bi bi-plus-circle me-1"></i> Add New Center
                </button>
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

    <!-- Centers Table -->
    <div class="card card-modern p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Center Name & Location</th>
                        <th>Contact In-Charge</th>
                        <th>Operating Hours</th>
                        <th>Accepted Electronics</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($centers)): ?>
                        <?php foreach ($centers as $c): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= sanitize($c['center_name']) ?></div>
                                    <div class="text-secondary text-xs">
                                        <?= sanitize($c['address']) ?>, <?= sanitize($c['city']) ?> (<?= sanitize($c['pincode']) ?>)
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= sanitize($c['contact_person']) ?></div>
                                    <div class="text-muted text-xs"><?= sanitize($c['phone']) ?> &bull; <?= sanitize($c['email']) ?></div>
                                </td>
                                <td><?= sanitize($c['operating_hours']) ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?= sanitize($c['accepted_items']) ?>">
                                        <?= sanitize($c['accepted_items']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($c['is_active']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCenterModal<?= $c['id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/centers.php" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this facility?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="center_id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Edit Center Modal -->
                                    <div class="modal fade" id="editCenterModal<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered text-start">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Edit Facility: <?= sanitize($c['center_name']) ?></h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/centers.php">
                                                    <div class="modal-body">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="center_id" value="<?= $c['id'] ?>">

                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-8">
                                                                <label class="form-label small fw-semibold">Facility Name</label>
                                                                <input type="text" class="form-control form-control-sm" name="center_name" value="<?= sanitize($c['center_name']) ?>" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-semibold">Contact Person</label>
                                                                <input type="text" class="form-control form-control-sm" name="contact_person" value="<?= sanitize($c['contact_person']) ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-semibold">Phone Number</label>
                                                                <input type="tel" class="form-control form-control-sm" name="phone" value="<?= sanitize($c['phone']) ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-semibold">Email Address</label>
                                                                <input type="email" class="form-control form-control-sm" name="email" value="<?= sanitize($c['email']) ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Street Address</label>
                                                            <input type="text" class="form-control form-control-sm" name="address" value="<?= sanitize($c['address']) ?>" required>
                                                        </div>

                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-semibold">City</label>
                                                                <input type="text" class="form-control form-control-sm" name="city" value="<?= sanitize($c['city']) ?>" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-semibold">State</label>
                                                                <input type="text" class="form-control form-control-sm" name="state" value="<?= sanitize($c['state']) ?>" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-semibold">PIN Code</label>
                                                                <input type="text" class="form-control form-control-sm" name="pincode" value="<?= sanitize($c['pincode']) ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-semibold">Operating Hours</label>
                                                                <input type="text" class="form-control form-control-sm" name="operating_hours" value="<?= sanitize($c['operating_hours']) ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-semibold">Accepted Items</label>
                                                                <input type="text" class="form-control form-control-sm" name="accepted_items" value="<?= sanitize($c['accepted_items']) ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_active" id="activeCheck<?= $c['id'] ?>" <?= $c['is_active'] ? 'checked' : '' ?>>
                                                            <label class="form-check-label small" for="activeCheck<?= $c['id'] ?>">Active & Accepting Drop-offs</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-emerald btn-sm">Update Facility</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary">No collection facilities found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Center Modal -->
<div class="modal fade" id="addCenterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Register New Collection Facility</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/centers.php">
                <div class="modal-body">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="create">

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Facility Name</label>
                            <input type="text" class="form-control form-control-sm" name="center_name" placeholder="e.g. Northern Green E-Waste Recovery Depot" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Contact Person</label>
                            <input type="text" class="form-control form-control-sm" name="contact_person" placeholder="Manager Name" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control form-control-sm" name="phone" placeholder="+91 ..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" class="form-control form-control-sm" name="email" placeholder="facility@ewaste.org" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Street Address</label>
                        <input type="text" class="form-control form-control-sm" name="address" placeholder="Industrial Plot, Street, Landmark" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" class="form-control form-control-sm" name="city" placeholder="City" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">State</label>
                            <input type="text" class="form-control form-control-sm" name="state" placeholder="State" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">PIN Code</label>
                            <input type="text" class="form-control form-control-sm" name="pincode" placeholder="PIN" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Operating Hours</label>
                            <input type="text" class="form-control form-control-sm" name="operating_hours" value="9:00 AM - 6:00 PM (Mon-Sat)" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Accepted Device Categories</label>
                            <input type="text" class="form-control form-control-sm" name="accepted_items" value="Smartphones, Laptops, Motherboards, Monitors, Cables, Hard Drives" required>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="newActiveCheck" checked>
                        <label class="form-check-label small" for="newActiveCheck">Active & Accepting Drop-offs Immediately</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-emerald btn-sm">Add Center</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
