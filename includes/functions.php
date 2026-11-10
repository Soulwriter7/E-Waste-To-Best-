<?php
/**
 * Core Helper Functions
 * Project: E-Waste to Best
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';

/**
 * Sanitize string for HTML output
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message to session
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render Bootstrap Alert for Flash message if present
 */
function renderFlash() {
    $flash = getFlash();
    if ($flash) {
        $alertType = $flash['type'] === 'error' ? 'danger' : $flash['type'];
        $icon = match($alertType) {
            'success' => 'bi-check-circle-fill',
            'danger' => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-circle-fill',
            default => 'bi-info-circle-fill'
        };
        echo '
        <div class="alert alert-' . sanitize($alertType) . ' alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="bi ' . $icon . ' me-2 fs-5"></i>
            <div>' . $flash['message'] . '</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

/**
 * Format currency with configured symbol
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format((float)$amount, 2);
}

/**
 * Format gram values gracefully
 */
function formatGrams($grams, $decimals = 3) {
    $g = (float)$grams;
    if ($g >= 1000) {
        return number_format($g / 1000, 2) . ' kg';
    }
    return number_format($g, $decimals) . ' g';
}

/**
 * Generate human-friendly Unique Request Code (e.g. EWB-2026-4819)
 */
function generateRequestCode() {
    return 'EWB-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
}

/**
 * Get all current benchmark metal rates
 */
function getMetalRates($pdo) {
    if (!$pdo) {
        return [
            'Gold' => ['symbol' => 'Au', 'rate' => 7150.00],
            'Silver' => ['symbol' => 'Ag', 'rate' => 88.50],
            'Copper' => ['symbol' => 'Cu', 'rate' => 0.82],
            'Aluminium' => ['symbol' => 'Al', 'rate' => 0.24]
        ];
    }
    try {
        $stmt = $pdo->query("SELECT metal_name, symbol, rate_per_gram FROM metal_rates");
        $rates = [];
        while ($row = $stmt->fetch()) {
            $rates[$row['metal_name']] = [
                'symbol' => $row['symbol'],
                'rate' => (float)$row['rate_per_gram']
            ];
        }
        return $rates;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Theoretical recovery calculation based on category and quantity
 */
function calculateEstimatedRecovery($pdo, $categoryId, $quantity) {
    $qty = (float)$quantity;
    if ($qty <= 0) {
        return null;
    }

    if (!$pdo) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM device_categories WHERE id = ? AND is_active = 1");
        $stmt->execute([$categoryId]);
        $cat = $stmt->fetch();
        if (!$cat) return null;

        $copper_g = (float)$cat['copper_yield_g'] * $qty;
        $aluminium_g = (float)$cat['aluminium_yield_g'] * $qty;
        $silver_g = (float)$cat['silver_yield_g'] * $qty;
        $gold_g = (float)$cat['gold_yield_g'] * $qty;
        $co2_kg = (float)$cat['co2_saving_kg'] * $qty;
        $toxic_kg = (float)$cat['toxic_diverted_kg'] * $qty;

        $rates = getMetalRates($pdo);
        $goldRate = $rates['Gold']['rate'] ?? 7150.00;
        $silverRate = $rates['Silver']['rate'] ?? 88.50;
        $copperRate = $rates['Copper']['rate'] ?? 0.82;
        $aluminiumRate = $rates['Aluminium']['rate'] ?? 0.24;

        $goldValue = $gold_g * $goldRate;
        $silverValue = $silver_g * $silverRate;
        $copperValue = $copper_g * $copperRate;
        $aluminiumValue = $aluminium_g * $aluminiumRate;
        $totalValue = $goldValue + $silverValue + $copperValue + $aluminiumValue;

        return [
            'category_name' => $cat['category_name'],
            'unit_type' => $cat['unit_type'],
            'quantity' => $qty,
            'copper_g' => $copper_g,
            'aluminium_g' => $aluminium_g,
            'silver_g' => $silver_g,
            'gold_g' => $gold_g,
            'gold_value' => $goldValue,
            'silver_value' => $silverValue,
            'copper_value' => $copperValue,
            'aluminium_value' => $aluminiumValue,
            'total_value' => $totalValue,
            'co2_saved_kg' => $co2_kg,
            'toxic_diverted_kg' => $toxic_kg,
            'is_theoretical' => true
        ];
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get cumulative platform impact stats
 */
function getGlobalImpactStats($pdo) {
    if (!$pdo) {
        return [
            'total_pickups' => 128,
            'total_co2_kg' => 4820,
            'total_gold_g' => 48.65,
            'total_copper_kg' => 312.4,
            'total_toxic_kg' => 580.0
        ];
    }

    try {
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_pickups,
                COALESCE(SUM(est_co2_saved_kg), 0) as total_co2_kg,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_gold_g ELSE est_gold_g END), 0) as total_gold_g,
                COALESCE(SUM(CASE WHEN is_verified = 1 THEN actual_copper_g ELSE est_copper_g END), 0) / 1000 as total_copper_kg,
                COALESCE(SUM(est_total_value), 0) as total_value
            FROM pickup_requests
            WHERE status != 'Cancelled'
        ");
        return $stmt->fetch();
    } catch (PDOException $e) {
        return [
            'total_pickups' => 0,
            'total_co2_kg' => 0,
            'total_gold_g' => 0,
            'total_copper_kg' => 0,
            'total_value' => 0
        ];
    }
}
