/**
 * Global JavaScript Helpers
 * Project: E-Waste to Best
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Bootstrap Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // 2. Auto-dismiss alerts after 6 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });

    // 3. Demo Login Helper (for college presentation / viva convenience)
    window.fillDemoCredentials = (role) => {
        const emailInput = document.getElementById('email');
        const passInput = document.getElementById('password');
        if (!emailInput || !passInput) return;

        if (role === 'admin') {
            emailInput.value = 'admin@ewaste.org';
            passInput.value = 'admin123';
        } else if (role === 'user') {
            emailInput.value = 'rahul@gmail.com';
            passInput.value = 'user123';
        }
    };

    // 4. City Filter in Collection Centers page
    const cityFilter = document.getElementById('cityFilterSelect');
    const searchInput = document.getElementById('centerSearchInput');
    const centerCards = document.querySelectorAll('.center-card-item');

    function filterCenters() {
        if (!centerCards.length) return;
        const selectedCity = (cityFilter ? cityFilter.value.toLowerCase().trim() : 'all');
        const searchTerm = (searchInput ? searchInput.value.toLowerCase().trim() : '');

        let visibleCount = 0;
        centerCards.forEach(card => {
            const cardCity = (card.getAttribute('data-city') || '').toLowerCase();
            const cardText = card.textContent.toLowerCase();

            const matchesCity = (selectedCity === 'all' || selectedCity === '' || cardCity.includes(selectedCity));
            const matchesSearch = (searchTerm === '' || cardText.includes(searchTerm));

            if (matchesCity && matchesSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noResults = document.getElementById('noCentersFound');
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (cityFilter) cityFilter.addEventListener('change', filterCenters);
    if (searchInput) searchInput.addEventListener('input', filterCenters);
});
