/**
 * Interactive Metal Recovery & Value Estimator
 * Project: E-Waste to Best
 */

document.addEventListener('DOMContentLoaded', () => {
    const categorySelect = document.getElementById('deviceCategory');
    const quantityInput = document.getElementById('deviceQuantity');
    const unitLabel = document.getElementById('unitLabel');

    // Value Display Elements
    const copperYieldDisplay = document.getElementById('copperYield');
    const aluminiumYieldDisplay = document.getElementById('aluminiumYield');
    const silverYieldDisplay = document.getElementById('silverYield');
    const goldYieldDisplay = document.getElementById('goldYield');

    const copperValueDisplay = document.getElementById('copperValue');
    const aluminiumValueDisplay = document.getElementById('aluminiumValue');
    const silverValueDisplay = document.getElementById('silverValue');
    const goldValueDisplay = document.getElementById('goldValue');

    const totalValueDisplay = document.getElementById('totalEstimatedValue');
    const co2SavingDisplay = document.getElementById('co2Saving');
    const toxicDivertedDisplay = document.getElementById('toxicDiverted');

    if (!categorySelect || !quantityInput) return;

    function formatVal(val, decimals = 3) {
        return Number(val).toLocaleString('en-IN', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    function calculateLive() {
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const qty = parseFloat(quantityInput.value) || 0;
        if (qty <= 0) return;

        // Extract dataset values from option element
        const copperYieldPerUnit = parseFloat(selectedOption.getAttribute('data-copper') || 0);
        const aluminiumYieldPerUnit = parseFloat(selectedOption.getAttribute('data-aluminium') || 0);
        const silverYieldPerUnit = parseFloat(selectedOption.getAttribute('data-silver') || 0);
        const goldYieldPerUnit = parseFloat(selectedOption.getAttribute('data-gold') || 0);
        const co2PerUnit = parseFloat(selectedOption.getAttribute('data-co2') || 0);
        const toxicPerUnit = parseFloat(selectedOption.getAttribute('data-toxic') || 0);
        const unitType = selectedOption.getAttribute('data-unit') || 'unit(s)';

        if (unitLabel) unitLabel.textContent = unitType;

        // Fetch market rates from window global or fallback
        const rates = window.METAL_RATES || {
            Gold: 7150.00,
            Silver: 88.50,
            Copper: 0.82,
            Aluminium: 0.24
        };

        // Compute grams
        const totalCopperG = copperYieldPerUnit * qty;
        const totalAluminiumG = aluminiumYieldPerUnit * qty;
        const totalSilverG = silverYieldPerUnit * qty;
        const totalGoldG = goldYieldPerUnit * qty;

        // Compute monetary worth
        const valCopper = totalCopperG * rates.Copper;
        const valAluminium = totalAluminiumG * rates.Aluminium;
        const valSilver = totalSilverG * rates.Silver;
        const valGold = totalGoldG * rates.Gold;
        const totalVal = valCopper + valAluminium + valSilver + valGold;

        // Environmental offsets
        const totalCo2 = co2PerUnit * qty;
        const totalToxic = toxicPerUnit * qty;

        // Update DOM
        if (copperYieldDisplay) copperYieldDisplay.textContent = formatVal(totalCopperG, 2) + ' g';
        if (aluminiumYieldDisplay) aluminiumYieldDisplay.textContent = formatVal(totalAluminiumG, 2) + ' g';
        if (silverYieldDisplay) silverYieldDisplay.textContent = formatVal(totalSilverG, 3) + ' g';
        if (goldYieldDisplay) goldYieldDisplay.textContent = formatVal(totalGoldG, 4) + ' g';

        if (copperValueDisplay) copperValueDisplay.textContent = '₹ ' + formatVal(valCopper, 2);
        if (aluminiumValueDisplay) aluminiumValueDisplay.textContent = '₹ ' + formatVal(valAluminium, 2);
        if (silverValueDisplay) silverValueDisplay.textContent = '₹ ' + formatVal(valSilver, 2);
        if (goldValueDisplay) goldValueDisplay.textContent = '₹ ' + formatVal(valGold, 2);

        if (totalValueDisplay) totalValueDisplay.textContent = '₹ ' + formatVal(totalVal, 2);
        if (co2SavingDisplay) co2SavingDisplay.textContent = formatVal(totalCo2, 1) + ' kg';
        if (toxicDivertedDisplay) toxicDivertedDisplay.textContent = formatVal(totalToxic, 2) + ' kg';
    }

    categorySelect.addEventListener('change', calculateLive);
    quantityInput.addEventListener('input', calculateLive);

    // Initial trigger
    calculateLive();
});
