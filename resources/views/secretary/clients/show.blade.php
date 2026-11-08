@extends('layouts.app')

@section('title', 'Fiche Client - ' . $client->firstname . ' ' . $client->name)

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('secretary.dashboard') }}" class="p-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $client->firstname }} {{ $client->name }}</h1>
            <p class="text-slate-500 text-xs mt-0.5">Fiche individuelle et historique des cotisations</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Infos Client -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-6 self-start">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Informations Générales</h2>

            <div class="space-y-4 text-sm">
                <div>
                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Numéro de Carnet</span>
                    <span class="inline-flex items-center mt-1 px-2.5 py-1 rounded bg-slate-100 text-slate-800 font-mono text-xs font-bold">
                        {{ $client->activeCarnet->number ?? 'Aucun carnet actif' }}
                    </span>
                </div>
                <div>
                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Téléphone</span>
                    <p class="text-slate-800 font-medium mt-0.5">{{ $client->phone ?? 'Non renseigné' }}</p>
                </div>
                <div>
                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Adresse</span>
                    <p class="text-slate-800 font-medium mt-0.5">{{ $client->address ?? 'Non renseignée' }}</p>
                </div>
                <div>
                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Article Souhaité</span>
                    <p class="text-brand-600 font-bold mt-0.5">{{ $client->desired_article }}</p>
                </div>
                <div>
                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Mise Journalière</span>
                    <p class="text-slate-800 font-semibold mt-0.5">{{ number_format($client->daily_contribution, 0, ',', ' ') }} FCFA / jour</p>
                </div>
                
                @if(count($client->other_active_plans) > 0)
                    <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-indigo-700 flex items-center">
                            <i data-lucide="layers" class="w-3.5 h-3.5 mr-1"></i>
                            <span>Autres souscriptions actives</span>
                        </span>
                        <div class="space-y-2">
                            @foreach($client->otherPlans() as $otherPlan)
                                <div class="bg-indigo-50/40 border border-indigo-100/50 rounded-xl p-2.5 text-xs flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $otherPlan->desired_article }}</p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">Mise: {{ number_format($otherPlan->daily_contribution, 0, ',', ' ') }} F &bull; Carnet: {{ $otherPlan->activeCarnet->number ?? '-' }}</p>
                                    </div>
                                    <a href="{{ route('secretary.clients.show', $otherPlan->id) }}" class="p-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-650 rounded-lg transition" title="Voir la fiche">
                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Détails Financiers & Prix de Revient -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-4 self-start">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Prix de Revient du Carnet</h2>

            <div class="space-y-3.5 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400 font-semibold uppercase">Coût Total Carnet</span>
                    <span class="font-bold text-slate-800">{{ number_format($client->cardCost(), 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 font-semibold uppercase">Coût Journalier Carnet</span>
                    <span class="font-bold text-slate-800">{{ number_format($client->cardCostDaily(), 2, ',', ' ') }} FCFA / jour</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 font-semibold uppercase">Jours couverts prévus</span>
                    <span class="font-bold text-slate-800">{{ $client->daysCoveredTarget() }} jours</span>
                </div>
                <div class="flex justify-between border-t border-slate-100 pt-2.5">
                    <span class="text-slate-400 font-semibold uppercase">Montant Total à payer</span>
                    <span class="font-bold text-slate-900">{{ number_format($client->totalTargetAmount(), 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 font-semibold uppercase">Marge Réalisée</span>
                    <span class="font-bold text-emerald-600">+ {{ number_format($client->margeRealisee(), 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between border-t border-slate-100 pt-2.5">
                    <span class="text-slate-400 font-semibold uppercase">Montant déjà payé</span>
                    <span class="font-bold text-blue-600">{{ number_format($client->totalValidatedPaid(), 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 font-semibold uppercase">Solde restant</span>
                    <span class="font-bold text-rose-600">{{ number_format($client->soldeRestant(), 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        <!-- Historique des Cotisations -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-4">
                <div class="space-y-2 w-full">
                    <div class="flex justify-between items-baseline">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Progression de l'objectif</span>
                        <span class="text-lg font-black text-slate-950">{{ number_format($client->totalPaid(), 0, ',', ' ') }} / {{ number_format($client->target_amount, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                        @php
                            $percent = min(100, $client->target_amount > 0 ? ($client->totalPaid() / $client->target_amount) * 100 : 0);
                        @endphp
                        <div class="h-full bg-gradient-to-r from-brand-500 to-indigo-600 rounded-full" style="width: {{ $percent }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium">Objectif rempli à {{ round($percent, 1) }}% — {{ $client->daysCovered() }} jours cotisés au total ({{ $client->contributionTimeFormatted() }}).</p>
                </div>

                <!-- Visuel du Carnet Réel (Grille de Cotisations) -->
                <div class="border-t border-slate-100 pt-4 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 mr-1 text-slate-400"></i>
                        <span>Visuel du Carnet (Calendrier)</span>
                    </h4>

                    <!-- Légende -->
                    <div class="flex items-center space-x-4 text-[10px] font-semibold text-slate-500 bg-slate-50 p-2 rounded-xl border border-slate-100">
                        <div class="flex items-center space-x-1.5">
                            <span class="w-2.5 h-2.5 rounded-md bg-emerald-500 border border-emerald-600 inline-block shadow-xs"></span>
                            <span class="text-slate-700">Validé</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-2.5 h-2.5 rounded-md bg-amber-500 border border-amber-600 inline-block shadow-xs"></span>
                            <span class="text-slate-700">En attente</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-2.5 h-2.5 rounded-md bg-white border border-slate-250 inline-block shadow-xs"></span>
                            <span class="text-slate-600">Non payé</span>
                        </div>
                    </div>

                    <!-- Résumé des cotisations -->
                    <div class="grid grid-cols-3 gap-2 text-center text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100 font-bold">
                        <div class="bg-white p-1.5 rounded-lg border border-slate-100 shadow-2xs">
                            <span class="block text-[10px] text-slate-400 uppercase">Validés</span>
                            <span class="text-emerald-600 text-sm font-black" id="modal-client-validated-count">0</span>
                        </div>
                        <div class="bg-white p-1.5 rounded-lg border border-slate-100 shadow-2xs">
                            <span class="block text-[10px] text-slate-400 uppercase">En attente</span>
                            <span class="text-amber-600 text-sm font-black" id="modal-client-pending-count">0</span>
                        </div>
                        <div class="bg-white p-1.5 rounded-lg border border-slate-100 shadow-2xs">
                            <span class="block text-[10px] text-slate-400 uppercase">Total payé</span>
                            <span class="text-indigo-650 text-sm font-black" id="modal-client-total-days-count">0 / 0 j</span>
                        </div>
                    </div>

                    <!-- En-tête du Calendrier du Carnet -->
                    <div class="flex justify-between items-center bg-slate-900 text-white py-1.5 px-3 rounded-xl">
                        <div class="flex items-center space-x-1">
                            <button type="button" onclick="prevCarnetCalendarMonth()" class="p-1 text-slate-400 hover:text-white rounded transition hover:bg-slate-800">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <span class="text-xs font-bold uppercase tracking-wider min-w-[120px] text-center" id="modal-client-carnet-month-name">Mois</span>
                            <button type="button" onclick="nextCarnetCalendarMonth()" class="p-1 text-slate-400 hover:text-white rounded transition hover:bg-slate-800">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <span class="text-[10px] bg-slate-800 text-slate-300 font-semibold px-2 py-0.5 rounded-md">Mise : {{ number_format($client->daily_contribution, 0, ',', ' ') }} F</span>
                    </div>

                    <!-- Grille -->
                    <div id="modal-client-carnet-grid" class="grid grid-cols-7 gap-1.5 p-3 bg-slate-50 border border-slate-150 rounded-2xl max-h-64 overflow-y-auto dynamic-scrollbar text-center text-[10px] font-bold">
                        <!-- Les cases seront générées dynamiquement en JS -->
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-4">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Historique des Cotisations</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 text-xs uppercase font-semibold">
                                <th class="pb-2">Date & Heure</th>
                                <th class="pb-2">Montant Apporté</th>
                                <th class="pb-2 text-right">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <!-- Adaptation ici vers $client->contributions -->
                            @forelse($client->contributions->sortByDesc('created_at') as $contribution)
                                <tr>
                                    <td class="py-3 font-medium text-slate-600">
                                        {{ $contribution->created_at->format('d/m/Y à H:i') }}
                                    </td>
                                    <td class="py-3 font-bold text-slate-900">
                                        {{ number_format($contribution->amount, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            {{ ucfirst($contribution->validation_status ?? 'Encaissé') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-400">Aucune cotisation enregistrée pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let carnetCalendarYear = new Date().getFullYear();
    let carnetCalendarMonth = new Date().getMonth();
    const currentClient = @json($client);

    document.addEventListener('DOMContentLoaded', function() {
        // Déterminer le mois de la dernière cotisation pour initialiser le calendrier dessus
        let initialYear = new Date().getFullYear();
        let initialMonth = new Date().getMonth();

        const contributions = currentClient.contributions || [];
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
    });

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
        if (!currentClient) return;

        // Mettre à jour le résumé des cotisations
        const contributions = currentClient.contributions || [];
        const validatedDays = contributions.filter(c => c.validation_status === 'validated').length;
        const pendingDays = contributions.filter(c => c.validation_status === 'pending').length;
        const dailyRate = parseFloat(currentClient.daily_contribution) || 0;
        
        // Calculer les jours cibles du carnet (target_amount / daily_contribution)
        const target = parseFloat(currentClient.target_amount) || 0;
        const targetDays = dailyRate > 0 ? Math.round(target / dailyRate) : 100;

        document.getElementById('modal-client-validated-count').innerText = validatedDays;
        document.getElementById('modal-client-pending-count').innerText = pendingDays;
        document.getElementById('modal-client-total-days-count').innerText = `${validatedDays + pendingDays} / ${targetDays} j`;

        const gridContainer = document.getElementById('modal-client-carnet-grid');
        if (!gridContainer) return;
        gridContainer.innerHTML = ''; // Nettoyer

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
        if (currentClient.contributions) {
            currentClient.contributions.forEach(c => {
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
</script>
@endsection
