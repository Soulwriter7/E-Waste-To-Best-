<?php
/**
 * E-Waste Information & Awareness Page
 * Project: E-Waste to Best
 */

$pageTitle = "E-Waste Hazards & Environmental Awareness - E-Waste to Best";
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-dark text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-3 py-1 rounded-pill small mb-2">
                    <i class="bi bi-radioactive me-1"></i> ENVIRONMENTAL & HEALTH CRISIS
                </span>
                <h1 class="display-5 fw-bold mb-3">Understanding the E-Waste Dilemma</h1>
                <p class="text-light text-opacity-75 lead mb-0">
                    Electronic waste constitutes only 2% of solid waste in landfills, yet it accounts for over <strong>70% of toxic heavy metals</strong> polluting our global ecosystems and groundwater tables.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="<?= BASE_URL ?>/estimator.php" class="btn btn-emerald btn-lg">
                    <i class="bi bi-calculator me-1"></i> Check Your Device Value
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Key Statistics -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card card-modern p-4 text-center border-top border-4 border-danger h-100">
                <div class="display-6 fw-bold text-danger mb-2">62 Million</div>
                <h6 class="fw-bold mb-1">Metric Tons Generated Annually</h6>
                <p class="text-secondary small mb-0">Global e-waste output is increasing 5x faster than documented recycling capacity according to UN reports.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern p-4 text-center border-top border-4 border-warning h-100">
                <div class="display-6 fw-bold text-warning mb-2">&lt; 22.3%</div>
                <h6 class="fw-bold mb-1">Officially Documented Recovery</h6>
                <p class="text-secondary small mb-0">More than 77% of electronics end up dumped in open landfills, incinerated, or handled informally without safety gear.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern p-4 text-center border-top border-4 border-success h-100">
                <div class="display-6 fw-bold text-success mb-2">$62.5 Billion</div>
                <h6 class="fw-bold mb-1">Unclaimed Annual Metal Worth</h6>
                <p class="text-secondary small mb-0">Precious gold, copper, and platinum discarded every year—surpassing the GDP of many individual nations.</p>
            </div>
        </div>
    </div>

    <!-- Toxic Chemical Breakdown -->
    <div class="mb-5">
        <div class="text-center mb-4">
            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill small mb-2">CHEMICAL TOXICOLOGY</span>
            <h2 class="fw-bold">Dangerous Toxins Lurking in Obsolete Electronics</h2>
            <p class="text-secondary max-w-600 mx-auto">Improper open-air burning and acid baths release severe carcinogens into the atmosphere and drinking water.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card card-modern p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-danger mb-0">Lead (Pb)</h5>
                        <span class="badge bg-danger bg-opacity-10 text-danger">Neurotoxic</span>
                    </div>
                    <div class="small text-secondary mb-2"><strong>Found in:</strong> CRT monitors, solder on printed circuit boards (PCBs), lead-acid batteries.</div>
                    <p class="text-secondary small mb-0">
                        Causes permanent damage to the central nervous system, cognitive impairment in children, kidney failure, and cardiovascular disorders.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card card-modern p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-warning mb-0">Mercury (Hg)</h5>
                        <span class="badge bg-warning bg-opacity-10 text-warning">Bioaccumulative</span>
                    </div>
                    <div class="small text-secondary mb-2"><strong>Found in:</strong> Cold cathode fluorescent lamps (CCFL) in LCD backlights, switches, thermostats.</div>
                    <p class="text-secondary small mb-0">
                        Vaporizes into ambient air when crushed. Bioaccumulates in fish and water systems, causing irreversible brain damage and birth defects.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card card-modern p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-danger mb-0">Cadmium (Cd)</h5>
                        <span class="badge bg-danger bg-opacity-10 text-danger">Carcinogenic</span>
                    </div>
                    <div class="small text-secondary mb-2"><strong>Found in:</strong> Rechargeable NiCd batteries, chip resistors, infrared detectors, older CRT phosphors.</div>
                    <p class="text-secondary small mb-0">
                        Severe environmental persistence. Damages bone density (Itai-itai disease), produces irreversible pulmonary damage, and induces renal cancer.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card card-modern p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-primary mb-0">Brominated Flame Retardants (BFRs)</h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary">Endocrine Disruptor</span>
                    </div>
                    <div class="small text-secondary mb-2"><strong>Found in:</strong> Plastic casings, printed circuit boards, wiring insulation to suppress fire.</div>
                    <p class="text-secondary small mb-0">
                        When smoldered in informal dumps, forms polybrominated dioxins and furans—some of the most toxic carcinogens known to biomedical science.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card card-modern p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-info mb-0">Beryllium (Be)</h5>
                        <span class="badge bg-info bg-opacity-10 text-info">Lung Carcinogen</span>
                    </div>
                    <div class="small text-secondary mb-2"><strong>Found in:</strong> Copper-beryllium alloy connectors, motherboard spring clips, transistor heat sinks.</div>
                    <p class="text-secondary small mb-0">
                        Inhaling microscopic dust produces chronic berylliosis (an incurable lung disease) and elevates cancer risks for workers and nearby residents.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card card-modern p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-success mb-0">Lithium (Li)</h5>
                        <span class="badge bg-success bg-opacity-10 text-success">Fire & Soil Hazard</span>
                    </div>
                    <div class="small text-secondary mb-2"><strong>Found in:</strong> Smartphone, tablet, and laptop rechargeable lithium-ion battery packs.</div>
                    <p class="text-secondary small mb-0">
                        Crushed or punctured cells explode and trigger uncontrollable thermal runaway fires in waste trucks while leaking cobalt and caustic electrolytes.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Do's and Don'ts Table -->
    <div class="card card-modern p-4 p-lg-5 mb-5">
        <h3 class="fw-bold text-center mb-4">Best Practices: What Every Student & Household Must Do</h3>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="p-4 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-25 h-100">
                    <h5 class="fw-bold text-success mb-3 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-4"></i> The DO's of E-Waste
                    </h5>
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-0 small">
                        <li class="d-flex gap-2">
                            <i class="bi bi-check2 text-success fw-bold fs-5"></i>
                            <span><strong>Backup & Wipe Data:</strong> Perform factory resets and remove memory cards before submitting devices.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-check2 text-success fw-bold fs-5"></i>
                            <span><strong>Store in a Dry Place:</strong> Keep discarded devices away from rain and humidity to prevent battery swelling and chemical leaching.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-check2 text-success fw-bold fs-5"></i>
                            <span><strong>Separate Batteries:</strong> When feasible, detach removable batteries and wrap terminals in non-conductive tape.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-check2 text-success fw-bold fs-5"></i>
                            <span><strong>Use Certified Portals:</strong> Book collections exclusively through authorized channels like <em>E-Waste to Best</em>.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-4 rounded-4 bg-danger bg-opacity-10 border border-danger border-opacity-25 h-100">
                    <h5 class="fw-bold text-danger mb-3 d-flex align-items-center">
                        <i class="bi bi-x-circle-fill me-2 fs-4"></i> The DON'Ts of E-Waste
                    </h5>
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-0 small">
                        <li class="d-flex gap-2">
                            <i class="bi bi-x text-danger fw-bold fs-5"></i>
                            <span><strong>Never Throw in Household Bins:</strong> Electronics must never mix with organic, wet, or municipal trash.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-x text-danger fw-bold fs-5"></i>
                            <span><strong>Never Dismantle CRT Displays:</strong> CRT tubes contain high vacuum pressure and up to 2 kg of hazardous lead.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-x text-danger fw-bold fs-5"></i>
                            <span><strong>Never Burn Plastic Cables:</strong> Stripping copper wire by burning releases lethal dioxin fumes into the local air.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-x text-danger fw-bold fs-5"></i>
                            <span><strong>Avoid Unlicensed Scrap Dealers:</strong> Informal scrap yards perform acid bathing without safety ventilation or effluent filters.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="text-center py-4 bg-light rounded-4 border">
        <h4 class="fw-bold mb-2">Make a Direct Ecological Difference</h4>
        <p class="text-secondary small mb-4">Schedule a doorstep pickup or drop off your electronics at an authorized city center.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>/user/pickup-request.php" class="btn btn-emerald">
                <i class="bi bi-truck me-1"></i> Book a Certified Pickup
            </a>
            <a href="<?= BASE_URL ?>/centers.php" class="btn btn-outline-dark">
                <i class="bi bi-geo-alt me-1"></i> Locate Nearest Center
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
