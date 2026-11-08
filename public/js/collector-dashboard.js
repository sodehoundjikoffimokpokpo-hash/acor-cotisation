function switchTab(tab) {
    const tP = document.getElementById('tab-payment');
    const tC = document.getElementById('tab-client');
    const bP = document.getElementById('tab-btn-payment');
    const bC = document.getElementById('tab-btn-client');

    if (tab === 'payment') {
        tP.classList.remove('hidden');
        tC.classList.add('hidden');
        bP.className = "py-2.5 px-4 rounded-xl text-xs font-bold transition-all bg-white text-brand-700 shadow-sm";
        bC.className = "py-2.5 px-4 rounded-xl text-xs font-bold transition-all text-slate-600 hover:text-slate-900";
    } else {
        tP.classList.add('hidden');
        tC.classList.remove('hidden');
        bP.className = "py-2.5 px-4 rounded-xl text-xs font-bold transition-all text-slate-600 hover:text-slate-900";
        bC.className = "py-2.5 px-4 rounded-xl text-xs font-bold transition-all bg-white text-brand-700 shadow-sm";
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const carnetSelect = document.getElementById('carnet_number');
    const dailyInput = document.getElementById('new_daily_contribution');
    const targetInput = document.getElementById('new_target_amount');
    const articleInput = document.getElementById('new_desired_article');
    const searchInput = document.getElementById('search_carnet');

    if (carnetSelect && dailyInput && targetInput && articleInput) {
        function updateClientFieldsFromCarnet() {
            const selectedOpt = carnetSelect.options[carnetSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value !== "") {
                const rate = selectedOpt.getAttribute('data-rate') || 0;
                const target = selectedOpt.getAttribute('data-target') || 0;
                const article = selectedOpt.getAttribute('data-article') || '';

                dailyInput.value = rate;
                targetInput.value = target;
                articleInput.value = article;
            } else {
                dailyInput.value = '';
                targetInput.value = '';
                articleInput.value = '';
            }
        }

        carnetSelect.addEventListener('change', updateClientFieldsFromCarnet);
        updateClientFieldsFromCarnet();

        if (searchInput) {
            const originalOptions = Array.from(carnetSelect.options);
            
            searchInput.addEventListener('input', function() {
                const query = searchInput.value.toLowerCase().trim();
                const currentValue = carnetSelect.value;
                
                carnetSelect.innerHTML = '';
                
                const filteredOptions = originalOptions.filter(option => {
                    if (option.value === "" || option.disabled) return true;
                    const text = option.text.toLowerCase();
                    return text.includes(query);
                });
                
                filteredOptions.forEach(option => {
                    carnetSelect.add(option);
                });

                carnetSelect.value = currentValue;
                updateClientFieldsFromCarnet();
            });
        }
    }

    // Handle requests dispatched from calendar actions
    window.addEventListener('load-client-calendar-requested', function(e) {
        if (typeof window.clientsData !== 'undefined') {
            loadClientCalendar(e.detail.clientId, window.clientsData);
        }
    });

    window.addEventListener('switch-tab-requested', function(e) {
        switchTab(e.detail.tab);
        if (e.detail.focus) {
            const el = document.getElementById(e.detail.focus);
            if (el) {
                setTimeout(() => {
                    el.scrollIntoView({ behavior: 'smooth' });
                    el.focus();
                }, 100);
            }
        }
    });
});
