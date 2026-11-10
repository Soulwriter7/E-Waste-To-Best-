<?php
/**
 * User Profile & Settings
 * Project: E-Waste to Best
 */

$pageTitle = "My Profile - E-Waste to Best";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/csrf.php';

$userId = (int)$_SESSION['user_id'];
$error = '';
$success = '';

// Fetch current user details
$user = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $user = null;
    }
}

if (!$user) {
    die("User profile could not be loaded.");
}

// Handle Profile Information Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_info') {
            $fullname = trim($_POST['fullname'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $pincode = trim($_POST['pincode'] ?? '');

            if (empty($fullname) || empty($phone) || empty($address) || empty($city) || empty($pincode)) {
                $error = 'All profile fields are required.';
            } else {
                try {
                    $updateStmt = $pdo->prepare("
                        UPDATE users 
                        SET fullname = ?, phone = ?, address = ?, city = ?, pincode = ?
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$fullname, $phone, $address, $city, $pincode, $userId]);

                    $_SESSION['user_name'] = $fullname;
                    setFlash('success', 'Profile information updated successfully.');
                    header('Location: ' . BASE_URL . '/user/profile.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Failed to update profile: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $error = 'Please fill out all password fields.';
            } elseif (!password_verify($currentPassword, $user['password'])) {
                $error = 'Current password is incorrect.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            } else {
                try {
                    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                    $pwdStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $pwdStmt->execute([$hashed, $userId]);

                    setFlash('success', 'Your password has been securely updated.');
                    header('Location: ' . BASE_URL . '/user/profile.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Password update failed: ' . $e->getMessage();
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-dark text-white py-4 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <span class="badge bg-secondary text-light small mb-1">ACCOUNT SETTINGS</span>
        <h2 class="fw-bold mb-1">Profile & Preferences</h2>
        <p class="text-secondary small mb-0">Manage personal contact information, default pickup address, and credentials.</p>
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
        <!-- Contact Information Form -->
        <div class="col-lg-7">
            <div class="card card-modern p-4 p-md-5 bg-white">
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-person-gear text-emerald"></i> Personal & Address Details
                </h4>

                <form method="POST" action="<?= BASE_URL ?>/user/profile.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_info">

                    <div class="mb-3">
                        <label for="fullname" class="form-label small fw-semibold">Full Name</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?= sanitize($user['fullname']) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address (Read-only)</label>
                            <input type="email" class="form-control bg-light" value="<?= sanitize($user['email']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label small fw-semibold">Contact Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= sanitize($user['phone']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label small fw-semibold">Default Pickup Address</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?= sanitize($user['address']) ?>" required>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="city" class="form-label small fw-semibold">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?= sanitize($user['city']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="pincode" class="form-label small fw-semibold">Postal PIN Code</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" value="<?= sanitize($user['pincode']) ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-emerald py-2 fw-semibold">
                        <i class="bi bi-check2 me-1"></i> Save Profile Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Security & Password Change -->
        <div class="col-lg-5">
            <div class="card card-modern p-4 p-md-5 bg-white mb-4">
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock text-primary"></i> Change Password
                </h4>

                <form method="POST" action="<?= BASE_URL ?>/user/profile.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label for="current_password" class="form-label small fw-semibold">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="••••••••" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label small fw-semibold">New Password (min 6 characters)</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label small fw-semibold">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-outline-dark py-2 fw-semibold w-100">
                        <i class="bi bi-key me-1"></i> Update Password
                    </button>
                </form>
            </div>

            <!-- Role badge card -->
            <div class="p-3 rounded-3 bg-light border text-secondary small">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold text-dark">Account Level:</span>
                    <span class="badge bg-success text-uppercase"><?= sanitize($user['role']) ?></span>
                </div>
                <div>Member since: <?= date('d M, Y', strtotime($user['created_at'])) ?></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
