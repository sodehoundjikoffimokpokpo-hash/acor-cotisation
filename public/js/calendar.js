// State management variables for calendar interaction
let selectedMode = 'single';
let selectedDates = [];
let currentClient = null;
let calendarYear = new Date().getFullYear();
let calendarMonth = new Date().getMonth();
let carnetCalendarYear = new Date().getFullYear();
let carnetCalendarMonth = new Date().getMonth();

function toggleSelectionMode(mode) {
    selectedMode = mode;
    selectedDates = [];
    updateSelectionSummary();
    renderCalendarGrid();
}

function prevCalendarMonth() {
    calendarMonth--;
    if (calendarMonth < 0) {
        calendarMonth = 11;
        calendarYear--;
    }
    renderCalendarGrid();
}

function nextCalendarMonth() {
    calendarMonth++;
    if (calendarMonth > 11) {
        calendarMonth = 0;
        calendarYear++;
    }
    renderCalendarGrid();
}

function loadClientCalendar(clientId, clients) {
    currentClient = clients.find(c => c.id == clientId);
    if (!currentClient) return;

    let targetYear = new Date().getFullYear();
    let targetMonth = new Date().getMonth();

    const contributionsMap = {};
    if (currentClient.contributions) {
        currentClient.contributions.forEach(c => {
            if (c.payment_date) {
                const pDate = c.payment_date.split(' ')[0].split('T')[0];
                contributionsMap[pDate] = true;
            }
        });
    }

    let checkDate = new Date(currentClient.created_at || new Date());
    if (isNaN(checkDate.getTime())) {
        checkDate = new Date();
    }
    checkDate.setDate(1); // Commencer au début du mois de création
    
    const today = new Date();
    let limit = 0;
    while (checkDate <= today && limit < 365) {
        const dateStr = `${checkDate.getFullYear()}-${String(checkDate.getMonth() + 1).padStart(2, '0')}-${String(checkDate.getDate()).padStart(2, '0')}`;
        if (!contributionsMap[dateStr]) {
            targetYear = checkDate.getFullYear();
            targetMonth = checkDate.getMonth();
            break;
        }
        checkDate.setDate(checkDate.getDate() + 1);
        limit++;
    }

    calendarYear = targetYear;
    calendarMonth = targetMonth;

    document.getElementById('calendar-wrapper').classList.remove('hidden');
    document.getElementById('calendar-daily-rate').innerText = `Taux : ${currentClient.daily_contribution} F/jour`;

    selectedDates = [];
    updateSelectionSummary();
    renderCalendarGrid();
}

function renderCalendarGrid() {
    if (!currentClient) return;

    const grid = document.getElementById('calendar-grid');
    grid.innerHTML = '';

    const weekdays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    weekdays.forEach(day => {
        const el = document.createElement('div');
        el.className = 'font-bold text-slate-400 text-[10px] uppercase py-1';
        el.innerText = day;
        grid.appendChild(el);
    });

    const year = calendarYear;
    const month = calendarMonth;

    const monthNames = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
    document.getElementById('calendar-month-name').innerText = `${monthNames[month]} ${year}`;

    const firstDayIndex = new Date(year, month, 1).getDay();
    const startOffset = firstDayIndex === 0 ? 6 : firstDayIndex - 1;
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    for (let i = 0; i < startOffset; i++) {
        const el = document.createElement('div');
        el.className = 'py-3';
        grid.appendChild(el);
    }

    const contributionsMap = {};
    if (currentClient.contributions) {
        currentClient.contributions.forEach(c => {
            if (c.payment_date) {
                const pDate = c.payment_date.split(' ')[0].split('T')[0];
                contributionsMap[pDate] = c.validation_status;
            }
        });
    }

    const unpaidDatesInOrder = [];
    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        if (!contributionsMap[dateStr]) {
            unpaidDatesInOrder.push(dateStr);
        }
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const status = contributionsMap[dateStr];

        const cell = document.createElement('div');
        cell.className = 'relative py-2.5 rounded-xl border transition-all flex flex-col items-center justify-center cursor-pointer select-none';

        const dayNum = document.createElement('span');
        dayNum.className = 'font-bold text-xs';
        dayNum.innerText = d;
        cell.appendChild(dayNum);

        if (status === 'validated') {
            cell.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white', 'cursor-not-allowed');
            const badge = document.createElement('span');
            badge.className = 'text-[7px] font-bold uppercase tracking-wider mt-0.5';
            badge.innerText = 'Payé';
            cell.appendChild(badge);
        } else if (status === 'pending') {
            cell.classList.add('bg-amber-400', 'border-amber-400', 'text-slate-900', 'cursor-not-allowed');
            const badge = document.createElement('span');
            badge.className = 'text-[7px] font-bold uppercase tracking-wider mt-0.5';
            badge.innerText = 'Attente';
            cell.appendChild(badge);
        } else {
            const index = unpaidDatesInOrder.indexOf(dateStr);
            const numSelected = selectedDates.length;
            let isSelectable = false;

            if (numSelected === 0) {
                isSelectable = (index === 0);
            } else {
                const lastSelectedDate = selectedDates[selectedDates.length - 1];
                if (dateStr === lastSelectedDate) {
                    isSelectable = true;
                } else if (selectedMode === 'multiple') {
                    isSelectable = (index === numSelected);
                }
            }

            const isSelected = selectedDates.includes(dateStr);
            if (isSelected) {
                cell.className = 'relative py-2.5 rounded-xl border border-brand-500 bg-brand-600 text-white flex flex-col items-center justify-center cursor-pointer select-none shadow-sm';
                const badge = document.createElement('span');
                badge.className = 'text-[7px] font-bold uppercase tracking-wider mt-0.5 text-brand-100';
                badge.innerText = 'Sélection';
                cell.appendChild(badge);
            } else if (isSelectable) {
                cell.className = 'relative py-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 flex flex-col items-center justify-center cursor-pointer select-none';
            } else {
                cell.className = 'relative py-2.5 rounded-xl border border-slate-100 bg-slate-100 text-slate-400 opacity-40 flex flex-col items-center justify-center cursor-not-allowed select-none';
            }

            if (isSelectable) {
                cell.onclick = function() {
                    handleDateSelection(dateStr);
                };
            }
        }

        grid.appendChild(cell);
    }
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
}

function handleDateSelection(dateStr) {
    if (selectedMode === 'single') {
        selectedDates = [dateStr];
    } else {
        const idx = selectedDates.indexOf(dateStr);
        if (idx > -1) {
            selectedDates.splice(idx, 1);
        } else {
            selectedDates.push(dateStr);
        }
    }
    updateSelectionSummary();
    renderCalendarGrid();
}

function updateSelectionSummary() {
    const count = selectedDates.length;
    const rate = currentClient ? parseFloat(currentClient.daily_contribution) : 0;
    const total = count * rate;

    document.getElementById('selected-days-count').innerText = `${count} jour(s) sélectionné(s)`;
    document.getElementById('selected-total-amount').innerText = `${new Intl.NumberFormat('fr-FR').format(total)} FCFA`;

    const btn = document.getElementById('btn-submit-contributions');
    btn.disabled = count === 0;

    const inputsContainer = document.getElementById('hidden-dates-inputs');
    inputsContainer.innerHTML = '';
    selectedDates.forEach(date => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'dates[]';
        input.value = date;
        inputsContainer.appendChild(input);
    });
}

function formatContributionTime(totalDays) {
    const months = Math.floor(totalDays / 30);
    const days = totalDays % 30;
    let parts = [];
    if (months > 0) parts.push(`${months} mois`);
    if (days > 0 || parts.length === 0) parts.push(`${days} jour${days > 1 ? 's' : ''}`);
    return parts.join(' et ');
}

function switchModalToClient(planId, clients) {
    const nextClient = clients.find(c => c.id == planId);
    if (nextClient) {
        closeClientModal();
        setTimeout(() => {
            openClientModal(nextClient, undefined, undefined, undefined, clients);
        }, 200);
    }
}

function openClientModal(client, totalPaid, daysCovered, timeFormatted, clients) {
    if (totalPaid === undefined) {
        totalPaid = (client.contributions || []).reduce((sum, c) => sum + parseFloat(c.amount), 0);
    }
    if (daysCovered === undefined) {
        daysCovered = parseFloat(client.daily_contribution) > 0 ? Math.floor(totalPaid / parseFloat(client.daily_contribution)) : 0;
    }
    if (timeFormatted === undefined) {
        timeFormatted = formatContributionTime(daysCovered);
    }

    document.getElementById('modal-client-fullname').innerText = `${client.firstname} ${client.name}`;
    document.getElementById('modal-client-phone').innerText = client.phone || 'Non renseigné';
    document.getElementById('modal-client-carnet').innerText = client.active_carnet ? `N° ${client.active_carnet.number}` : (client.active_carnet_number ? `N° ${client.active_carnet_number}` : (client.active_carnet?.number || 'Aucun carnet actif'));
    
    if (!client.active_carnet && client.carnets && client.carnets.length > 0) {
        const act = client.carnets.find(cr => cr.status === 'active');
        if (act) {
            document.getElementById('modal-client-carnet').innerText = `N° ${act.number}`;
        }
    }
    
    document.getElementById('modal-client-duration').innerText = `${daysCovered} jours (${timeFormatted})`;

    const target = parseFloat(client.target_amount) || 0;
    document.getElementById('modal-client-paid').innerText = new Intl.NumberFormat('fr-FR').format(totalPaid);
    document.getElementById('modal-client-target').innerText = new Intl.NumberFormat('fr-FR').format(target);

    const percentage = target > 0 ? Math.min(100, (totalPaid / target) * 100) : 0;
    const progressBar = document.getElementById('modal-client-progress-bar');
    progressBar.style.width = `${percentage}%`;

    const badgeContainer = document.getElementById('modal-client-status-badge');

    if (client.status === 'goal_reached') {
        badgeContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>Objectif Atteint</span>`;
        progressBar.className = "h-full rounded-full transition-all duration-500 bg-gradient-to-r from-emerald-500 to-teal-500";
    } else if (client.status === 'delivered') {
        badgeContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">Livré</span>`;
        progressBar.className = "h-full rounded-full transition-all duration-500 bg-blue-600";
    } else {
        badgeContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-100 text-brand-800 border border-brand-200">En cours / Actif</span>`;
        progressBar.className = "h-full rounded-full transition-all duration-500 bg-gradient-to-r from-brand-500 to-indigo-600";
    }

    const otherPlansSection = document.getElementById('modal-other-plans-section');
    const otherPlansList = document.getElementById('modal-other-plans-list');
    otherPlansList.innerHTML = '';

    const otherPlans = client.other_active_plans || [];
    if (otherPlans.length > 0) {
        otherPlansSection.classList.remove('hidden');
        otherPlans.forEach(plan => {
            const planDiv = document.createElement('div');
            planDiv.className = 'py-2 flex justify-between items-center text-xs border-b border-indigo-100/30 last:border-0';
            
            const pct = plan.target_amount > 0 ? Math.round((plan.total_paid / plan.target_amount) * 100) : 0;
            
            planDiv.innerHTML = `
                <div>
                    <p class="font-bold text-slate-800">${plan.desired_article} <span class="font-normal text-slate-400 font-mono text-[10px] ml-1">Carnet : ${plan.carnet_number}</span></p>
                    <p class="text-[10px] text-slate-500 mt-0.5">Mise : ${new Intl.NumberFormat('fr-FR').format(plan.daily_contribution)} F/jour &bull; Cotisé : ${new Intl.NumberFormat('fr-FR').format(plan.total_paid)} F</p>
                </div>
                <div class="text-right flex items-center space-x-3">
                    <div class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">${pct}%</div>
                    <button type="button" onclick="switchModalToClient(${plan.id}, ${JSON.stringify(clients).replace(/"/g, '&quot;')})" class="p-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition" title="Basculer vers cette fiche">
                        <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            `;
            otherPlansList.appendChild(planDiv);
        });
    } else {
        otherPlansSection.classList.add('hidden');
    }

    const dailyRate = parseFloat(client.daily_contribution) || 0;
    document.getElementById('modal-client-daily-rate-badge').innerText = new Intl.NumberFormat('fr-FR').format(dailyRate);

    const contributions = client.contributions || [];
    const validatedDays = contributions.filter(c => c.validation_status === 'validated').length;
    const pendingDays = contributions.filter(c => c.validation_status === 'pending').length;
    const targetDays = dailyRate > 0 ? Math.round(target / dailyRate) : 100;

    document.getElementById('modal-client-validated-count').innerText = validatedDays;
    document.getElementById('modal-client-pending-count').innerText = pendingDays;
    document.getElementById('modal-client-total-days-count').innerText = `${validatedDays + pendingDays} / ${targetDays} j`;

    window.activeModalClient = client;

    let initialYear = new Date().getFullYear();
    let initialMonth = new Date().getMonth();

    if (contributions.length > 0) {
        const sortedContributions = [...contributions].sort((a, b) => new Date(b.payment_date) - new Date(a.payment_date));
        if (sortedContributions[0] && sortedContributions[0].payment_date) {
            const latestDate = new Date(sortedContributions[0].payment_date);
            if (!isNaN(latestDate.getTime())) {
                initialYear = latestDate.getFullYear();
                initialMonth = latestDate.getMonth();
            }
        }
    }

    carnetCalendarYear = initialYear;
    carnetCalendarMonth = initialMonth;
    renderCarnetCalendarGrid();

    document.getElementById('modal-client-details').classList.remove('hidden');
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
}

function prevCarnetCalendarMonth() {
    carnetCalendarMonth--;
    if (carnetCalendarMonth < 0) {
        carnetCalendarMonth = 11;
        carnetCalendarYear--;
    }
    renderCarnetCalendarGrid();
}

function nextCarnetCalendarMonth() {
    carnetCalendarMonth++;
    if (carnetCalendarMonth > 11) {
        carnetCalendarMonth = 0;
        carnetCalendarYear++;
    }
    renderCarnetCalendarGrid();
}

function renderCarnetCalendarGrid() {
    if (!window.activeModalClient) return;
    const client = window.activeModalClient;

    const gridContainer = document.getElementById('modal-client-carnet-grid');
    gridContainer.innerHTML = '';

    const weekdays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    weekdays.forEach(day => {
        const el = document.createElement('div');
        el.className = 'font-bold text-slate-400 text-[10px] uppercase py-1';
        el.innerText = day;
        gridContainer.appendChild(el);
    });

    const year = carnetCalendarYear;
    const month = carnetCalendarMonth;

    const monthNames = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
    document.getElementById('modal-client-carnet-month-name').innerText = `${monthNames[month]} ${year}`;

    const firstDayIndex = new Date(year, month, 1).getDay();
    const startOffset = firstDayIndex === 0 ? 6 : firstDayIndex - 1;
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    for (let i = 0; i < startOffset; i++) {
        const el = document.createElement('div');
        el.className = 'py-3';
        gridContainer.appendChild(el);
    }

    const contributionsMap = {};
    if (client.contributions) {
        client.contributions.forEach(c => {
            if (c.payment_date) {
                const pDate = c.payment_date.split(' ')[0].split('T')[0];
                contributionsMap[pDate] = c.validation_status;
            }
        });
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const status = contributionsMap[dateStr];

        const box = document.createElement('div');
        box.className = 'aspect-square flex flex-col items-center justify-center rounded-lg font-bold text-[10px] shadow-sm relative group cursor-help';
        
        const dayNum = document.createElement('span');
        dayNum.innerText = d;
        box.appendChild(dayNum);

        if (status === 'validated') {
            box.className += ' bg-emerald-500 text-white border border-emerald-600';
            box.title = `Date : ${d} ${monthNames[month]} ${year} : Validé`;
            box.innerHTML += `<span class="absolute top-0.5 right-0.5 flex h-1 w-1"><span class="relative inline-flex rounded-full h-1 w-1 bg-white"></span></span>`;
        } else if (status === 'pending') {
            box.className += ' bg-amber-500 text-white border border-amber-600';
            box.title = `Date : ${d} ${monthNames[month]} ${year} : En attente`;
            box.innerHTML += `<span class="absolute top-0.5 right-0.5 flex h-1 w-1"><span class="relative inline-flex rounded-full h-1 w-1 bg-white animate-ping"></span></span>`;
        } else {
            box.className += ' bg-white border border-slate-200 text-slate-400 hover:border-slate-300 transition-colors';
            box.title = `Date : ${d} ${monthNames[month]} ${year} : Non payé`;
        }

        gridContainer.appendChild(box);
    }
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
}

function startPaymentForModalClient() {
    if (!window.activeModalClient) return;

    closeClientModal();

    const select = document.getElementById('client_id');
    select.value = window.activeModalClient.id;

    // Dispatch custom event or callback to load calendar in collector dashboard context
    const event = new CustomEvent('load-client-calendar-requested', {
        detail: { clientId: window.activeModalClient.id }
    });
    window.dispatchEvent(event);
}

function closeClientModal() {
    document.getElementById('modal-client-details').classList.add('hidden');
}

function startNewSubscriptionForModalClient() {
    if (!window.activeModalClient) return;

    closeClientModal();

    document.getElementById('new_name').value = window.activeModalClient.name;
    document.getElementById('new_firstname').value = window.activeModalClient.firstname;
    document.getElementById('new_phone').value = window.activeModalClient.phone;

    // Dispatch event to switch tab to client subscription
    const event = new CustomEvent('switch-tab-requested', {
        detail: { tab: 'client', focus: 'carnet_number' }
    });
    window.dispatchEvent(event);
}

function openEditClientModal(client) {
    document.getElementById('edit_firstname').value = client.firstname;
    document.getElementById('edit_name').value = client.name;
    document.getElementById('edit_phone').value = client.phone;
    document.getElementById('edit_address').value = client.address || '';
    document.getElementById('edit_daily_contribution').value = client.daily_contribution;
    document.getElementById('edit_target_amount').value = client.target_amount;
    document.getElementById('edit_desired_article').value = client.desired_article;

    const form = document.getElementById('edit-client-form');
    form.action = `/collector/clients/${client.id}/update`;

    document.getElementById('modal-edit-client').classList.remove('hidden');
}
