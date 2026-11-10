<?php
/**
 * Citizen User Registration Page
 * Project: E-Waste to Best
 */

$pageTitle = "Register Account - E-Waste to Best";
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/user/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $error = 'Security validation failed (Invalid CSRF Token). Please refresh and try again.';
    } else {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation rules
        if (empty($fullname) || empty($email) || empty($phone) || empty($city) || empty($pincode) || empty($address) || empty($password)) {
            $error = 'All fields are required to register your citizen recycling profile.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match. Please verify both password entries.';
        } elseif (!$pdo) {
            $error = 'Database connection is unavailable. Please check your MySQL setup.';
        } else {
            try {
                // Check if email already registered
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $checkStmt->execute([$email]);
                if ($checkStmt->fetch()) {
                    $error = 'An account with this email address already exists. Please sign in instead.';
                } else {
                    // Hash password securely using standard algorithm
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $insertStmt = $pdo->prepare("
                        INSERT INTO users (fullname, email, password, phone, address, city, pincode, role)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'user')
                    ");
                    $insertStmt->execute([
                        $fullname,
                        $email,
                        $hashedPassword,
                        $phone,
                        $address,
                        $city,
                        $pincode
                    ]);

                    setFlash('success', 'Registration successful! You may now sign in with your credentials.');
                    header('Location: ' . BASE_URL . '/login.php');
                    exit;
                }
            } catch (PDOException $e) {
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">
            <div class="card card-modern p-4 p-md-5 shadow-lg bg-white border-0">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-emerald text-white rounded-4 p-3 mb-3 shadow-sm">
                        <i class="bi bi-person-plus-fill fs-2"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Create Citizen Account</h3>
                    <p class="text-secondary small">Join our smart recycling network and track your environmental contribution</p>
                </div>

                <?php renderFlash(); ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div><?= sanitize($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/register.php">
                    <?= csrfField() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="fullname" class="form-label small fw-semibold">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?= sanitize($_POST['fullname'] ?? '') ?>" placeholder="e.g. Rahul Sharma" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="rahul@example.com" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label small fw-semibold">Contact Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= sanitize($_POST['phone'] ?? '') ?>" placeholder="+91 98765 43210" required>
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label small fw-semibold">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?= sanitize($_POST['city'] ?? '') ?>" placeholder="e.g. Noida, Delhi, Mumbai" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="address" class="form-label small fw-semibold">Full Residential / Pickup Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?= sanitize($_POST['address'] ?? '') ?>" placeholder="Apartment, Street, Landmark" required>
                        </div>
                        <div class="col-md-4">
                            <label for="pincode" class="form-label small fw-semibold">PIN Code</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" value="<?= sanitize($_POST['pincode'] ?? '') ?>" placeholder="201301" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label small fw-semibold">Password (min. 6 characters)</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label small fw-semibold">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="form-check mb-4 small text-secondary">
                        <input class="form-check-input" type="checkbox" id="termsCheck" required checked>
                        <label class="form-check-label" for="termsCheck">
                            I agree to dispose of electrical devices via authorized channels and understand metal estimates are subject to physical laboratory assays.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-emerald w-100 py-2 fw-bold mb-3">
                        <i class="bi bi-check2-circle me-1"></i> Complete Registration
                    </button>
                </form>

                <div class="text-center small text-secondary">
                    Already registered? <a href="<?= BASE_URL ?>/login.php" class="text-success fw-semibold">Sign In here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
