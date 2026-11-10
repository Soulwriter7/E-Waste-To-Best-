<?php
/**
 * Recycling & Reuse Suggestions Page
 * Project: E-Waste to Best
 */

$pageTitle = "Circular Economy & Reuse Suggestions - E-Waste to Best";
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-dark text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-1 rounded-pill small mb-2">
                    <i class="bi bi-arrow-repeat me-1"></i> CIRCULAR ECONOMY PRINCIPLES
                </span>
                <h1 class="display-5 fw-bold mb-3">Refurbish, Upcycle & Reuse</h1>
                <p class="text-light text-opacity-75 lead mb-0">
                    The greenest device is the one that stays in use. Discover how to extend the lifespan of your gadgets before committing them to metallurgical recovery.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="#decisionTree" class="btn btn-emerald btn-lg">
                    <i class="bi bi-diagram-3 me-1"></i> Repair vs Recycle Guide
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Hierarchy of Waste Management banner -->
    <div class="card card-modern p-4 mb-5 bg-emerald bg-opacity-10 border border-success border-opacity-25">
        <div class="row align-items-center g-4">
            <div class="col-md-8">
                <h4 class="fw-bold text-success mb-2">The Sustainable 4R Hierarchy</h4>
                <p class="text-secondary small mb-0">
                    Always prioritize <strong>Refuse</strong> single-use tech &rarr; <strong>Reduce</strong> frequent upgrades &rarr; <strong>Reuse & Repair</strong> components &rarr; and finally <strong>Recycle</strong> through certified metallurgical assay.
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-success p-2 px-3 fs-6">Reduce &gt; Reuse &gt; Recycle</span>
            </div>
        </div>
    </div>

    <!-- Categorized Reuse Guides -->
    <h3 class="fw-bold mb-4">Smart Ways to Give Old Electronics a Second Life</h3>
    <div class="row g-4 mb-5">
        <!-- Old Laptops -->
        <div class="col-lg-4 col-md-6">
            <div class="card card-modern p-4 h-100 border-top border-4 border-primary">
                <div class="d-flex align-items-center gap-2 mb-3 text-primary fs-3">
                    <i class="bi bi-laptop"></i>
                    <h5 class="fw-bold mb-0 text-dark">Old Laptops</h5>
                </div>
                <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-4">
                    <li><i class="bi bi-check2-circle text-primary me-2"></i><strong>Home Media Server:</strong> Install Linux / Plex / Jellyfin to stream movies across your home network.</li>
                    <li><i class="bi bi-check2-circle text-primary me-2"></i><strong>Lightweight OS:</strong> Install ChromeOS Flex or Lubuntu to give sluggish laptops lightning-fast speed for basic browsing.</li>
                    <li><i class="bi bi-check2-circle text-primary me-2"></i><strong>Secondary Monitor:</strong> Use free software like <em>Spacedesk</em> to convert it into a dual-screen extension for your main workstation.</li>
                </ul>
                <div class="mt-auto">
                    <span class="badge bg-light text-dark border">Estimated life extension: +2 to 4 years</span>
                </div>
            </div>
        </div>

        <!-- Old Smartphones -->
        <div class="col-lg-4 col-md-6">
            <div class="card card-modern p-4 h-100 border-top border-4 border-success">
                <div class="d-flex align-items-center gap-2 mb-3 text-success fs-3">
                    <i class="bi bi-phone"></i>
                    <h5 class="fw-bold mb-0 text-dark">Old Smartphones</h5>
                </div>
                <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-4">
                    <li><i class="bi bi-check2-circle text-success me-2"></i><strong>Smart Security Camera:</strong> Run apps like <em>AlfredCamera</em> to turn an old phone with a working camera into a home security monitor.</li>
                    <li><i class="bi bi-check2-circle text-success me-2"></i><strong>Dedicated Car GPS:</strong> Keep it mounted in your car with offline Google Maps or OsmAnd downloaded.</li>
                    <li><i class="bi bi-check2-circle text-success me-2"></i><strong>High-Quality PC Webcam:</strong> Use <em>DroidCam</em> to turn an old phone's rear camera into a 1080p stream for Zoom & Teams.</li>
                </ul>
                <div class="mt-auto">
                    <span class="badge bg-light text-dark border">Estimated life extension: +2 years</span>
                </div>
            </div>
        </div>

        <!-- Hard Drives -->
        <div class="col-lg-4 col-md-6">
            <div class="card card-modern p-4 h-100 border-top border-4 border-warning">
                <div class="d-flex align-items-center gap-2 mb-3 text-warning fs-3">
                    <i class="bi bi-hdd"></i>
                    <h5 class="fw-bold mb-0 text-dark">Old Hard Disk Drives</h5>
                </div>
                <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-4">
                    <li><i class="bi bi-check2-circle text-warning me-2"></i><strong>DIY External Backup Drive:</strong> Buy an inexpensive 2.5" SATA to USB enclosure (₹300 - ₹500) to create a rugged external drive.</li>
                    <li><i class="bi bi-check2-circle text-warning me-2"></i><strong>Cold Storage Archive:</strong> Store non-critical photos and offline data backups safely in a static-free sleeve.</li>
                    <li><i class="bi bi-check2-circle text-warning me-2"></i><strong>Router Storage (NAS):</strong> Plug into your home Wi-Fi router's USB port for centralized household storage.</li>
                </ul>
                <div class="mt-auto">
                    <span class="badge bg-light text-dark border">Cost saving: ~₹2,500 vs buying new</span>
                </div>
            </div>
        </div>

        <!-- Computer Monitors -->
        <div class="col-lg-4 col-md-6">
            <div class="card card-modern p-4 h-100 border-top border-4 border-info">
                <div class="d-flex align-items-center gap-2 mb-3 text-info fs-3">
                    <i class="bi bi-display"></i>
                    <h5 class="fw-bold mb-0 text-dark">Old Monitors</h5>
                </div>
                <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-4">
                    <li><i class="bi bi-check2-circle text-info me-2"></i><strong>Smart Dashboard:</strong> Connect a Raspberry Pi to display weather, family calendar, and college timetables.</li>
                    <li><i class="bi bi-check2-circle text-info me-2"></i><strong>Secondary Coding Screen:</strong> Rotate it into portrait mode for reading code and PDF research papers.</li>
                    <li><i class="bi bi-check2-circle text-info me-2"></i><strong>Gaming Console Display:</strong> Dedicate it to vintage consoles (PS3, Xbox 360, Wii) or retro emulation.</li>
                </ul>
                <div class="mt-auto">
                    <span class="badge bg-light text-dark border">Productivity boost: +30%</span>
                </div>
            </div>
        </div>

        <!-- Cables & Chargers -->
        <div class="col-lg-4 col-md-6">
            <div class="card card-modern p-4 h-100 border-top border-4 border-danger">
                <div class="d-flex align-items-center gap-2 mb-3 text-danger fs-3">
                    <i class="bi bi-plugin"></i>
                    <h5 class="fw-bold mb-0 text-dark">Cables & Chargers</h5>
                </div>
                <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-4">
                    <li><i class="bi bi-check2-circle text-danger me-2"></i><strong>Campus Lab Donation:</strong> College robotics clubs and maker labs constantly need 5V/12V adapters and USB cables.</li>
                    <li><i class="bi bi-check2-circle text-danger me-2"></i><strong>Color Coding & Storage:</strong> Use toilet paper rolls or velcro ties to organize spare cords cleanly.</li>
                    <li><i class="bi bi-check2-circle text-danger me-2"></i><strong>Strip for Pure Copper:</strong> High copper yield per kg when sent to our recovery centers.</li>
                </ul>
                <div class="mt-auto">
                    <span class="badge bg-light text-dark border">High metal reclaim rate</span>
                </div>
            </div>
        </div>

        <!-- RAM & Motherboards -->
        <div class="col-lg-4 col-md-6">
            <div class="card card-modern p-4 h-100 border-top border-4 border-dark">
                <div class="d-flex align-items-center gap-2 mb-3 text-dark fs-3">
                    <i class="bi bi-cpu"></i>
                    <h5 class="fw-bold mb-0 text-dark">PCBs & RAM Sticks</h5>
                </div>
                <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-4">
                    <li><i class="bi bi-check2-circle text-dark me-2"></i><strong>Robotics Breadboard Parts:</strong> Desolder capacitors, LEDs, buttons, and heat sinks for student lab projects.</li>
                    <li><i class="bi bi-check2-circle text-dark me-2"></i><strong>Direct Scrap to Metal:</strong> If boards are burnt or trace-damaged, they have the highest gold and silver content for recovery.</li>
                    <li><i class="bi bi-check2-circle text-dark me-2"></i><strong>Educational Props:</strong> Excellent hardware anatomy models for introductory computer architecture classes.</li>
                </ul>
                <div class="mt-auto">
                    <span class="badge bg-light text-dark border">Gold-fingered components</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Decision Assistant: Should I Repair or Recycle? -->
    <div id="decisionTree" class="card card-modern p-4 p-lg-5 mb-5 bg-white">
        <div class="text-center mb-4">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill small mb-2">DECISION ASSISTANT</span>
            <h3 class="fw-bold">Should You Repair or Recycle Your Device?</h3>
            <p class="text-secondary max-w-600 mx-auto">Use the rule-of-thumb guide below to determine whether your device belongs on a repair bench or in a collection center.</p>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">Criterion</th>
                        <th style="width: 37.5%;" class="text-success"><i class="bi bi-tools me-1"></i> Consider REPAIRING If:</th>
                        <th style="width: 37.5%;" class="text-danger"><i class="bi bi-recycle me-1"></i> Ready for RECYCLING If:</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <tr>
                        <td class="fw-bold">Device Age</td>
                        <td>Device is under 4 to 5 years old and still receives critical security updates.</td>
                        <td>Device is over 7+ years old with obsolete architecture (e.g. 32-bit CPU, 3G cellular modem).</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Repair Cost Ratio</td>
                        <td>Repair cost is <strong>under 50%</strong> of the device's replacement market value.</td>
                        <td>Repair estimate exceeds 50% of the cost of a modern refurbished replacement.</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Battery Condition</td>
                        <td>Battery is easily replaceable and genuine replacement packs are available.</td>
                        <td>Battery is severely swollen, punctured, or no OEM batteries exist anymore.</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Physical Integrity</td>
                        <td>Screen or port is broken, but motherboard logic board boots up normally.</td>
                        <td>Motherboard is cracked, suffered water corrosion, or experienced severe IC burnout.</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Software Performance</td>
                        <td>Device responds well after clean OS reinstallation or lightweight OS swap.</td>
                        <td>Cannot open modern web pages, apps crash due to insufficient hardware memory.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Direct CTA -->
    <div class="card card-modern p-4 bg-dark text-white text-center">
        <h4 class="fw-bold mb-2">Decided to Recycle Your Device?</h4>
        <p class="text-light text-opacity-75 small mb-3">Estimate the recovered precious metal value and schedule a certified pickup in seconds.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>/estimator.php" class="btn btn-emerald">
                <i class="bi bi-calculator me-1"></i> Open Metal Estimator
            </a>
            <a href="<?= BASE_URL ?>/user/pickup-request.php" class="btn btn-outline-light">
                <i class="bi bi-calendar2-plus me-1"></i> Book Pickup Request
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
