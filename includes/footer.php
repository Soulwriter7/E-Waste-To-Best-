<?php
/**
 * Global Footer Component
 * Project: E-Waste to Best
 */
?>
</main>

<footer class="footer-custom">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-3 p-1 px-2 shadow-sm">
                        <i class="bi bi-cpu-fill fs-5"></i>
                    </span>
                    <h5 class="text-white mb-0 brand-font">E-Waste <span class="text-success">to Best</span></h5>
                </div>
                <p class="text-secondary small leading-relaxed">
                    A smart college project initiative engineering circular economy solutions for electrical and electronic scrap. We bridge citizen recycling with certified metallurgical recovery facilities to reclaim gold, silver, copper, and aluminium while safeguarding groundwater.
                </p>
                <div class="d-flex gap-3 text-secondary fs-5">
                    <a href="#" class="text-secondary hover-emerald"><i class="bi bi-github"></i></a>
                    <a href="#" class="text-secondary hover-emerald"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="text-secondary hover-emerald"><i class="bi bi-globe"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="text-white text-uppercase fw-bold mb-3 small tracking-wider">Quick Links</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small">
                    <li><a href="<?= BASE_URL ?>/index.php">Home Platform</a></li>
                    <li><a href="<?= BASE_URL ?>/estimator.php">Metal Calculator</a></li>
                    <li><a href="<?= BASE_URL ?>/centers.php">Collection Centers</a></li>
                    <li><a href="<?= BASE_URL ?>/awareness.php">E-Waste Hazards</a></li>
                    <li><a href="<?= BASE_URL ?>/reuse-tips.php">Refurbish & Reuse</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="text-white text-uppercase fw-bold mb-3 small tracking-wider">Citizen Services</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small">
                    <li><a href="<?= BASE_URL ?>/user/pickup-request.php">Doorstep E-Waste Pickup</a></li>
                    <li><a href="<?= BASE_URL ?>/user/history.php">Track Collection Status</a></li>
                    <li><a href="<?= BASE_URL ?>/user/impact.php">Personal Green Certificate</a></li>
                    <li><a href="<?= BASE_URL ?>/login.php">Citizen Sign In</a></li>
                    <li><a href="<?= BASE_URL ?>/register.php">Create Free Account</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="text-white text-uppercase fw-bold mb-3 small tracking-wider">System Standards</h6>
                <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 small text-secondary">
                    <div class="d-flex align-items-center gap-2 text-warning mb-2 fw-semibold">
                        <i class="bi bi-shield-check"></i> Scientific Transparency
                    </div>
                    Metal recoveries before physical laboratory assay are calculated using standardized empirical recovery coefficients from USGS & EPA WEEE benchmark literature.
                </div>
            </div>
        </div>

        <hr class="border-secondary border-opacity-25 my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small text-secondary">
            <div>
                &copy; <?= date('Y') ?> <strong><?= sanitize(APP_NAME) ?></strong>. Academic Major Capstone Project.
            </div>
            <div>
                Developed with <span class="text-danger">&hearts;</span> using PHP, MySQL, Bootstrap 5 & JavaScript.
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle JS with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Global App Interactivity Script -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
