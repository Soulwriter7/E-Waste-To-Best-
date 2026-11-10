<?php
/**
 * Administrator User & Citizen Directory
 * Project: E-Waste to Best
 */

$pageTitle = "Registered Users - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$search = trim($_GET['search'] ?? '');

$users = [];
if ($pdo) {
    try {
        $sql = "
            SELECT u.*, 
                   COUNT(r.id) as total_pickups,
                   COALESCE(SUM(r.est_total_value), 0) as total_scrap_volume
            FROM users u
            LEFT JOIN pickup_requests r ON u.id = r.user_id
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE u.fullname LIKE ? OR u.email LIKE ? OR u.city LIKE ?";
            $term = "%{$search}%";
            $params = [$term, $term, $term];
        }

        $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
    } catch (PDOException $e) {
        $users = [];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-dark text-white py-4 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-danger small mb-1">USER AUDIT</span>
                <h2 class="fw-bold mb-1">Registered Citizens & Officers</h2>
                <p class="text-secondary small mb-0">Overview of student and citizen profiles participating in e-waste recovery.</p>
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

    <!-- Search Bar -->
    <div class="card card-modern p-3 mb-4 bg-white shadow-sm">
        <form method="GET" action="<?= BASE_URL ?>/admin/users.php" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="search" value="<?= sanitize($search) ?>" placeholder="Search citizens by name, email, or city...">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-emerald flex-grow-1">Filter Users</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card card-modern p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>User Name & Email</th>
                        <th>Phone</th>
                        <th>City / Location</th>
                        <th>Role</th>
                        <th>Pickups Booked</th>
                        <th>Joined On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= sanitize($u['fullname']) ?></div>
                                    <div class="text-secondary text-xs"><?= sanitize($u['email']) ?></div>
                                </td>
                                <td><?= sanitize($u['phone']) ?></td>
                                <td>
                                    <div><?= sanitize($u['city']) ?></div>
                                    <div class="text-muted text-xs"><?= sanitize($u['pincode']) ?></div>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">ADMIN</span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Citizen</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold"><?= (int)$u['total_pickups'] ?></span> requests
                                </td>
                                <td class="text-secondary">
                                    <?= date('d M, Y', strtotime($u['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary">No registered users matched the query.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
