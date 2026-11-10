<?php
/**
 * User & Admin Login Page
 * Project: E-Waste to Best
 */

$pageTitle = "Sign In - E-Waste to Best";
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

// If already logged in, redirect to respective dashboard
if (!empty($_SESSION['user_id'])) {
    if (($_SESSION['user_role'] ?? 'user') === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/user/dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $error = 'Security validation failed (Invalid CSRF Token). Please refresh and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both your email address and password.';
        } elseif (!$pdo) {
            $error = 'Database connection is unavailable. Please check your MySQL setup in XAMPP.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, fullname, email, password, role FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Password matches! Protect against session fixation
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['fullname'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    setFlash('success', 'Welcome back, ' . sanitize($user['fullname']) . '!');

                    if ($user['role'] === 'admin') {
                        header('Location: ' . BASE_URL . '/admin/dashboard.php');
                    } else {
                        header('Location: ' . BASE_URL . '/user/dashboard.php');
                    }
                    exit;
                } else {
                    $error = 'Invalid email address or password. Please verify your credentials.';
                }
            } catch (PDOException $e) {
                $error = 'A system error occurred during authentication: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-modern p-4 p-md-5 shadow-lg bg-white border-0">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-4 p-3 mb-3 shadow-sm">
                        <i class="bi bi-shield-lock-fill fs-2"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Access Portal</h3>
                    <p class="text-secondary small">Sign in to manage pickups and track your environmental recovery</p>
                </div>

                <?php renderFlash(); ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div><?= sanitize($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/login.php" autocomplete="on">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label small fw-semibold mb-0">Password</label>
                            <a href="#" class="small text-secondary text-decoration-none" onclick="alert('For academic evaluation, use the demo credentials below.'); return false;">Forgot?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-emerald w-100 py-2 fw-bold mb-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Account
                    </button>
                </form>

                <!-- Academic Evaluation / Demo Credentials Helper -->
                <div class="p-3 rounded-3 bg-light border text-center mb-3">
                    <div class="small fw-bold text-secondary mb-2">
                        <i class="bi bi-mortarboard-fill text-primary me-1"></i> Academic Presentation Quick Login
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="fillDemoCredentials('admin')">
                            <i class="bi bi-shield-check me-1"></i> Admin Demo
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="fillDemoCredentials('user')">
                            <i class="bi bi-person me-1"></i> Citizen Demo
                        </button>
                    </div>
                    <div class="text-muted text-xs mt-2" style="font-size: 0.75rem;">
                        Admin: <code>admin@ewaste.org / admin123</code> | User: <code>rahul@gmail.com / user123</code>
                    </div>
                </div>

                <div class="text-center small text-secondary">
                    Don't have an account yet? <a href="<?= BASE_URL ?>/register.php" class="text-success fw-semibold">Register as a Citizen</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
