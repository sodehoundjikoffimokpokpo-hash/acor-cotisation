@extends('layouts.app')

@section('title', 'Tableau de Bord Administrateur')

@section('content')
<div class="space-y-6 sm:space-y-8 px-4 sm:px-0">

    <!-- ========================================== -->
    <!-- SECTION ALERTES OBJECTIFS ATTEINTS & RETARDS-->
    <!-- ========================================== -->
    @php
        $clientsAlertes = $clients->where('status', 'goal_reached');
    @endphp

    @if($clientsAlertes->count() > 0 || ($retardsCount ?? 0) > 0)
        <div class="space-y-3">
            <!-- Alertes Objectifs Atteints -->
            @foreach($clientsAlertes as $alerteClient)
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl p-4 flex items-start sm:items-center justify-between shadow-sm animate-pulse">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-emerald-500 text-white rounded-xl shadow-sm">
                            <i data-lucide="party-popper" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Objectif Atteint !</h4>
                            <p class="text-xs text-slate-600">
                                Le client <span class="font-bold text-emerald-700">{{ $alerteClient->firstname }} {{ $alerteClient->name }}</span> (Carnet N° {{ $alerteClient->activeCarnet->number ?? 'N/A' }}) a totalisé ses <span class="font-bold">{{ number_format($alerteClient->target_amount, 0, ',', ' ') }} FCFA</span>.
                            </p>
                        </div>
                    </div>
                    <button onclick="openClientModal({{ json_encode($alerteClient) }}, {{ $alerteClient->totalPaid() }}, {{ $alerteClient->daysCovered() }}, '{{ $alerteClient->contributionTimeFormatted() }}')" class="ml-4 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                        Gérer la livraison
                    </button>
                </div>
            @endforeach

            <!-- Alerte Retards de Cotisation -->
            @if(($retardsCount ?? 0) > 0)
                <div class="bg-gradient-to-r from-rose-50 to-amber-50 border border-rose-200 rounded-2xl p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-rose-500 text-white rounded-xl shadow-sm">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Alerte sur les retards</h4>
                            <p class="text-xs text-slate-600">
                                Il y a actuellement <span class="font-bold text-rose-700">{{ $retardsCount }}</span> cotisation(s) en retard ou client(s) inactifs par rapport aux prévisions.
                            </p>
                        </div>
                    </div>
                    <a href="#" class="ml-4 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                        Voir les détails
                    </a>
                </div>
            @endif
        </div>
    @endif

   <!-- Actions Principales -->
<div class="flex flex-wrap items-center gap-2 sm:gap-3">


    <!-- NOUVEAU : Sauvegardes -->
    <a href="{{ route('admin.backups') }}" class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition-all duration-200 space-x-1.5 sm:space-x-2">
        <i data-lucide="database" class="w-4 h-4"></i>
        <span>Sauvegardes</span>
    </a>

    <!-- NOUVEAU : Multi-Pays -->
    <a href="{{ route('admin.locations') }}" class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition-all duration-200 space-x-1.5 sm:space-x-2">
        <i data-lucide="globe" class="w-4 h-4"></i>
        <span>Multi-Pays / Zones</span>
    </a>

    <!-- Lien Historique existant -->
    <a href="{{ route('admin.historique') }}" class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition-all duration-200 space-x-1.5 sm:space-x-2">
        <i data-lucide="history" class="w-4 h-4"></i>
        <span>Historique</span>
    </a>

    <a href="{{ route('admin.validation') }}" class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition-all duration-200 space-x-1.5 sm:space-x-2">
        <i data-lucide="shield-alert" class="w-4 h-4"></i>
        <span>Validation ({{ $pendingValidationCount }})</span>
    </a>

    <a href="{{ route('admin.collectors') }}" class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition-all duration-200 space-x-1.5 sm:space-x-2">
        <i data-lucide="users" class="w-4 h-4"></i>
        <span>Collecteurs ({{ $activeCollectorsCount ?? 0 }})</span>
    </a>

    <a href="{{ route('admin.rights') }}" class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition-all duration-200 space-x-1.5 sm:space-x-2">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        <span>Droits & Employés</span>
    </a>

    <a href="{{ route('admin.carnets.index') }}" class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition-all duration-200 space-x-1.5 sm:space-x-2">
        <i data-lucide="book-open" class="w-4 h-4"></i>
        <span>Voir Carnets</span>
    </a>
</div>
    <!-- ========================================== -->
    <!-- GRILLE DE TABLEAU DE BORD EN TEMPS RÉEL    -->
    <!-- ========================================== -->
    <div class="space-y-6">
        <!-- Carte Spéciale : Accès direct Module Comptabilité -->
        <a href="{{ route('admin.finance') }}" class="group bg-white border border-slate-200 p-5 rounded-2xl sm:rounded-3xl shadow-sm hover:border-brand-500 hover:shadow-md transition-all flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="p-3.5 bg-indigo-50 text-indigo-600 group-hover:bg-brand-50 group-hover:text-brand-600 rounded-xl sm:rounded-2xl transition-colors">
                    <i data-lucide="line-chart" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 group-hover:text-brand-600 transition-colors">Module Comptabilité</h3>
                    <p class="text-slate-400 text-xs mt-0.5">Consulter les états financiers et performances des collecteurs en temps réel.</p>
                </div>
            </div>
            <div class="text-slate-300 group-hover:text-brand-500 transition-colors">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </div>
        </a>

        <!-- Grille Principale des Indicateurs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

            <!-- 1. Montant Total Encaissé -->
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm flex items-center space-x-4">
                <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-brand-50 text-brand-600 flex-shrink-0">
                    <i data-lucide="banknote" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Montant Total Encaissé</p>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5 sm:mt-1 truncate">{{ number_format($totalCollected, 0, ',', ' ') }} FCFA</h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">{{ number_format($totalValidated, 0, ',', ' ') }} F validés à la caisse</p>
                </div>
            </div>

            <!-- 2. Nombre de Cotisations Enregistrées -->
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm flex items-center space-x-4">
                <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-indigo-50 text-indigo-600 flex-shrink-0">
                    <i data-lucide="history" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Cotisations Enregistrées</p>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5 sm:mt-1 truncate">{{ $totalContributionsCount ?? 0 }}</h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">Flux global des versements</p>
                </div>
            </div>

            <!-- 3. Clients Actifs / Total -->
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm flex items-center space-x-4">
                <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50 text-slate-700 flex-shrink-0">
                    <i data-lucide="users-round" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Total Clients</p>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5 sm:mt-1 truncate">{{ $totalClientsCount }}</h3>
                    <p class="text-[10px] text-brand-600 font-medium mt-0.5 truncate">{{ $activeClientsCount }} clients actifs suivis</p>
                </div>
            </div>

            <!-- 4. Nouveaux Clients -->
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm flex items-center space-x-4">
                <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-cyan-50 text-cyan-600 flex-shrink-0">
                    <i data-lucide="user-plus" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Nouveaux Clients</p>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5 sm:mt-1 truncate">{{ $newClientsCount ?? 0 }}</h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">Inscrits ce mois-ci</p>
                </div>
            </div>

            <!-- 5. Collecteurs Actifs -->
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm flex items-center space-x-4">
                <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-emerald-50 text-emerald-600 flex-shrink-0">
                    <i data-lucide="user-check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Collecteurs Actifs</p>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5 sm:mt-1 truncate">{{ $activeCollectorsCount ?? 0 }}</h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">Présents sur le terrain</p>
                </div>
            </div>

            <!-- 6. Carnets en Stock -->
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm flex items-center space-x-4">
                <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-amber-50 text-amber-600 flex-shrink-0">
                    <i data-lucide="archive" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Carnets en Stock</p>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5 sm:mt-1 truncate">{{ $carnetsInStockCount ?? 0 }}</h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">Vierges et prêts à attribuer</p>
                </div>
            </div>

            <!-- 7. Carnets Vondus / Attribués -->
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm flex items-center space-x-4">
                <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-orange-50 text-orange-600 flex-shrink-0">
                    <i data-lucide="book-check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Carnets Vendus</p>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5 sm:mt-1 truncate">{{ $carnetsSoldCount ?? 0 }}</h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">Associés à un client</p>
                </div>
            </div>

            <!-- 8. Articles Prêts pour la Livraison -->
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm flex items-center space-x-4">
                <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-teal-50 text-teal-600 flex-shrink-0">
                    <i data-lucide="package" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Prêts pour Livraison</p>
                    <h3 class="text-xl sm:text-2xl font-bold text-teal-950 mt-0.5 sm:mt-1 truncate">{{ $goalsReachedCount }}</h3>
                    <p class="text-[10px] text-emerald-600 font-semibold mt-0.5 flex items-center truncate">
                        <i data-lucide="check-circle" class="w-3 h-3 mr-0.5 flex-shrink-0"></i> Objectif final validé
                    </p>
                </div>
            </div>

            <!-- 9. Articles Déjà Livrés -->
            <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm flex items-center space-x-4 sm:col-span-2 lg:col-span-4">
                <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-blue-50 text-blue-600 flex-shrink-0">
                    <i data-lucide="package-check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Articles Déjà Livrés</p>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-950 mt-0.5 sm:mt-1 truncate">{{ $deliveredArticlesCount ?? 0 }}</h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">Cycles de cotisations clôturés avec succès</p>
                </div>
            </div>

    </div>

    <!-- ========================================== -->
    <!-- SECTION DES GRAPHES / BUSINESS INTELLIGENCE-->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        <!-- 1. Évolution Mensuelle -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-5 space-y-4">
            <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Évolution Mensuelle</h2>
                    <p class="text-slate-400 text-[10px]">Tendance des encaissements validés (FCFA).</p>
                </div>
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="h-56 w-full relative">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        <!-- 2. Top 5 Collecteurs -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-5 space-y-4">
            <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Top 5 Collecteurs</h2>
                    <p class="text-slate-400 text-[10px]">Performances des 5 meilleurs agents (FCFA).</p>
                </div>
                <div class="p-2 bg-brand-50 text-brand-600 rounded-xl">
                    <i data-lucide="crown" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="h-56 w-full relative">
                <canvas id="collectorChart"></canvas>
            </div>
        </div>

        <!-- 3. Articles Demandés -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-5 space-y-4">
            <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Articles Demandés</h2>
                    <p class="text-slate-400 text-[10px]">Répartition des objectifs d'acquisition.</p>
                </div>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="h-56 w-full relative flex items-center justify-center">
                <canvas id="articlesShareChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SECTION PRINCIPALE (TABLEAU ET LOGS)       -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        <!-- Client Contribution Follow-up (Large) -->
        <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl shadow-sm p-4 sm:p-6 lg:col-span-2 space-y-6 overflow-hidden">
            <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Suivi des Clients & Cotisations</h2>
                    <p class="text-slate-400 text-[11px] sm:text-xs">Liste globale et indicateurs de progression.</p>
                </div>
            </div>

            <div class="overflow-x-auto -mx-4 sm:-mx-6 dynamic-scrollbar">
                <div class="inline-block min-w-full align-middle px-4 sm:px-6">
                    <table class="min-w-[800px] lg:min-w-full divide-y divide-slate-100 text-xs sm:text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 text-[11px] uppercase font-semibold bg-slate-50/70">
                                <th class="px-4 py-3 rounded-l-xl">Client / Carnet</th>
                                <th class="px-4 py-3">Durée Cumulée</th>
                                <th class="px-4 py-3">Montant Reçu / Objectif</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3">Collecteur / Zone</th>
                                <th class="px-4 py-3 text-right rounded-r-xl">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($clients as $client)
                                @php
                                    $percentage = min(100, $client->target_amount > 0 ? ($client->totalPaid() / $client->target_amount) * 100 : 0);
                                    $isGoalReached = $client->status === 'goal_reached';
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-slate-950">{{ $client->firstname }} {{ $client->name }}</div>
                                        <div class="text-[10px] text-slate-400 inline-flex items-center mt-1 font-medium bg-slate-100 px-2 py-0.5 rounded-md">
                                            <i data-lucide="book-open" class="w-3 h-3 mr-1 text-slate-500"></i>
                                            {{ $client->activeCarnet->number ?? 'Aucun carnet' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-indigo-700 bg-indigo-50/60 px-2 py-0.5 rounded-lg w-max text-xs">{{ $client->daysCovered() }} jours</div>
                                        <div class="text-[10px] text-slate-400 mt-1 font-medium">({{ $client->contributionTimeFormatted() }})</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-slate-900">{{ number_format($client->totalPaid(), 0, ',', ' ') }} FCFA</div>
                                        <div class="w-32 sm:w-36 bg-slate-100 h-2 rounded-full mt-2 overflow-hidden border border-slate-50">
                                            <div class="h-full rounded-full transition-all duration-500 {{ $isGoalReached ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : 'bg-gradient-to-r from-brand-500 to-indigo-600' }}" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <div class="text-[10px] font-semibold mt-1 flex justify-between w-32 sm:w-36 text-slate-500">
                                            <span class="{{ $isGoalReached ? 'text-emerald-600' : 'text-brand-600' }}">{{ round($percentage) }}%</span>
                                            <span>Cible: {{ number_format($client->target_amount, 0, ',', ' ') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($client->status === 'goal_reached')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                                Atteint
                                            </span>
                                        @elseif($client->status === 'delivered')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i data-lucide="package-check" class="w-3 h-3 mr-1"></i>
                                                Livré
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 mr-1.5"></span>
                                                Actif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-xs font-semibold text-slate-800">{{ $client->collector->firstname ?? 'Système' }} {{ $client->collector->name ?? '' }}</div>
                                        <div class="text-[10px] text-slate-400 capitalize mt-1 flex items-center font-medium">
                                            <i data-lucide="map-pin" class="w-3 h-3 mr-0.5 text-slate-400"></i>
                                            {{ $client->collector->activity_zone ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <button onclick="openClientModal({{ json_encode($client) }}, {{ $client->totalPaid() }}, {{ $client->daysCovered() }}, '{{ $client->contributionTimeFormatted() }}')" class="inline-flex items-center justify-center p-2 bg-slate-50 border border-slate-200 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 rounded-xl transition-all duration-200 text-slate-500" title="Afficher toutes les informations">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                    </td>


                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 font-medium">Aucun client enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

       <!-- Side Section: Audit Logs -->
<div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl shadow-sm p-4 sm:p-6 space-y-6">
    <div class="border-b border-slate-100 pb-4">
        <h2 class="text-base sm:text-lg font-bold text-slate-900">Audit Logs (Traçabilité)</h2>
        <p class="text-slate-400 text-[11px] sm:text-xs">Historique en temps réel des actions.</p>
    </div>

    <div class="flow-root">
        <ul class="-mb-8">
            @forelse($auditLogs as $log)
                <li>
                    <div class="relative pb-8">
                        @if(!$loop->last)
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                        @endif
                        <div class="relative flex space-x-3">
                            <div>
                                <span class="h-8 w-8 rounded-lg flex items-center justify-center text-xs font-bold ring-8 ring-white
                                    @if($log->action === 'LOGIN') bg-blue-50 text-blue-600
                                    @elseif($log->action === 'CREATE' || $log->action === 'UPDATE') bg-violet-50 text-violet-600
                                    @elseif($log->action === 'GOAL_REACHED') bg-emerald-50 text-emerald-600
                                    @elseif($log->action === 'VALIDATE_DAILY_FUNDS') bg-amber-50 text-amber-600
                                    @elseif($log->action === 'RECORD_CONTRIBUTION') bg-brand-50 text-brand-600
                                    @else bg-slate-100 text-slate-600 @endif">
                                    <i data-lucide="{{ $log->action === 'UPDATE' ? 'edit' : ($log->action === 'CREATE' ? 'plus' : 'activity') }}" class="w-4 h-4"></i>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0 pt-1.5">
                                <!-- Description principale -->
                                <p class="text-xs text-slate-800 break-words font-semibold">{{ $log->description ?? $log->action }}</p>

                                <!-- Affichage dynamique des changements (Si existants) -->
                                @php
                                    $changes = \App\Helpers\AuditLogFormatter::diff($log->old_values, $log->new_values);
                                @endphp

                                @if(count($changes) > 0)
                                    <div class="mt-2 p-2 bg-slate-50 rounded-lg border border-slate-100 text-[10px] space-y-1">
                                        @foreach($changes as $change)
                                            <p class="text-slate-600">
                                                <span class="font-semibold text-slate-700">{{ $change['label'] }}</span> :
                                                <span class="text-slate-400">{{ $change['old'] }}</span>
                                                <span class="mx-1">→</span>
                                                <span class="text-emerald-600 font-bold">{{ $change['new'] }}</span>
                                            </p>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="text-[10px] text-slate-400 mt-2 flex flex-wrap items-center gap-1.5 font-medium">
                                    <span class="text-slate-500">{{ $log->user->firstname ?? 'Système' }} {{ $log->user->name ?? '' }}</span>
                                    @if($log->user && $log->user->country)
                                        <span class="text-[9px] bg-slate-100 text-slate-650 px-1.5 py-0.5 rounded font-bold">{{ $log->user->country->name }}</span>
                                    @endif
                                    <span>&bull;</span>
                                    <span>{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="text-center text-slate-400 text-xs py-4">Aucune action enregistrée.</li>
            @endforelse
        </ul>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: CRÉATION DE CARNET                -->
<!-- ========================================== -->
<div id="modal-carnet" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900 bg-opacity-40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 max-w-md w-full overflow-hidden mx-auto">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-950">Générer un carnet</h3>
            <button onclick="document.getElementById('modal-carnet').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-50 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('admin.carnets.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <p class="text-sm text-slate-500 leading-relaxed">
                Voulez-vous générer automatiquement le prochain numéro de carnet disponible ? Il sera initialisé avec le statut <span class="font-semibold text-brand-600">actif</span>.
            </p>
            <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-carnet').classList.add('hidden')" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition">Confirmer</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: FICHE COMPLÈTE DU CLIENT          -->
<!-- ========================================== -->
<div id="modal-client-details" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 transition-all">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-2xl w-full overflow-hidden mx-auto animate-fade-in">
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="p-1.5 bg-brand-100 text-brand-700 rounded-lg">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900" id="modal-client-title">Fiche Complète du Client</h3>
            </div>
            <button onclick="closeClientModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-200/50 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto dynamic-scrollbar">
            <!-- État Principal et Progression -->
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Progression Financière</span>
                    <span id="modal-client-status-badge"></span>
                </div>
                <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden shadow-inner">
                    <div id="modal-client-progress-bar" class="h-full rounded-full transition-all duration-500"></div>
                </div>
                <div class="flex justify-between text-xs font-bold text-slate-700">
                    <div>Encaissé : <span id="modal-client-paid" class="text-slate-950"></span> FCFA</div>
                    <div>Objectif Cible : <span id="modal-client-target" class="text-brand-600"></span> FCFA</div>
                </div>
            </div>

            <!-- Grille Informations Générales -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="border border-slate-100 p-3.5 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nom complet</span>
                    <div class="text-sm font-bold text-slate-900" id="modal-client-fullname"></div>
                </div>
                <div class="border border-slate-100 p-3.5 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Numéro de Téléphone</span>
                    <div class="text-sm font-bold text-slate-900 flex items-center" id="modal-client-phone"></div>
                </div>
                <div class="border border-slate-100 p-3.5 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Carnet Rattaché</span>
                    <div class="text-sm font-bold text-indigo-700 flex items-center" id="modal-client-carnet"></div>
                </div>
                <div class="border border-slate-100 p-3.5 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Durée de Cotisation</span>
                    <div class="text-sm font-bold text-slate-900" id="modal-client-duration"></div>
                </div>
            </div>

            <!-- Visuel du Carnet Réel (Calendrier de Cotisations) -->
            <div class="border border-slate-100 rounded-xl p-4 bg-white shadow-xs space-y-3">
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
                        <span class="text-slate-650">Non payé</span>
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
                    <span class="text-[10px] bg-slate-800 text-slate-300 font-semibold px-2 py-0.5 rounded-md">Mise : <span id="modal-client-daily-rate-badge">0</span> F</span>
                </div>

                <!-- Grille -->
                <div id="modal-client-carnet-grid" class="grid grid-cols-7 gap-1.5 p-3 bg-slate-50 border border-slate-150 rounded-2xl max-h-64 overflow-y-auto dynamic-scrollbar text-center text-[10px] font-bold">
                    <!-- Les cases seront générées dynamiquement en JS -->
                </div>
            </div>

            <!-- Section Collecteur Responsable -->
            <div class="border border-slate-100 rounded-xl p-4 bg-slate-50/30 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center">
                    <i data-lucide="truck" class="w-3.5 h-3.5 mr-1 text-slate-400"></i> Collecteur Assigné
                </h4>
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm font-bold text-slate-900" id="modal-client-collector-name"></div>
                        <div class="text-xs text-slate-500 mt-0.5 flex items-center">
                            <i data-lucide="map-pin" class="w-3 h-3 mr-1 text-slate-400"></i> Zone : <span id="modal-client-collector-zone" class="font-semibold text-slate-700 ml-1"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex justify-end">
            <button onclick="closeClientModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-sm rounded-xl transition shadow-sm">
                Fermer
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- SCRIPTS & STYLES APPLICATIFS               -->
<!-- ========================================== -->
<style>
    .dynamic-scrollbar::-webkit-scrollbar { height: 5px; width: 5px; }
    .dynamic-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .dynamic-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .animate-fade-in { animation: fadeIn 0.15s ease-out forwards; }
</style>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }

        // 1. Chart.js Monthly Trend (Line Chart)
        const monthlyTrendLabels = @json($monthlyTrendLabels);
        const monthlyTrendAmounts = @json($monthlyTrendAmounts);

        const ctxTrend = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: monthlyTrendLabels,
                datasets: [{
                    label: 'Versements (FCFA)',
                    data: monthlyTrendAmounts,
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { color: '#64748b', font: { size: 9 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 9 } }
                    }
                }
            }
        });

        // 2. Chart.js Top 5 Collectors (Bar Chart)
        const collectorData = @json($contributionsByCollector);
        const labelsCollector = collectorData.map(item => item.name);
        const dataCollector = collectorData.map(item => item.total);

        const ctxCollector = document.getElementById('collectorChart').getContext('2d');
        new Chart(ctxCollector, {
            type: 'bar',
            data: {
                labels: labelsCollector,
                datasets: [{
                    label: 'Validé Collecté (FCFA)',
                    data: dataCollector,
                    backgroundColor: 'rgba(79, 70, 229, 0.75)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(79, 70, 229, 0.95)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { color: '#64748b', font: { size: 9 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 9 } }
                    }
                }
            }
        });

        // 3. Chart.js Articles Distribution (Doughnut Chart)
        const articlesLabels = @json($articlesShareLabels);
        const articlesCounts = @json($articlesShareCounts);

        const ctxArticles = document.getElementById('articlesShareChart').getContext('2d');
        new Chart(ctxArticles, {
            type: 'doughnut',
            data: {
                labels: articlesLabels,
                datasets: [{
                    data: articlesCounts,
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderWidth: 1,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            color: '#475569',
                            boxWidth: 10,
                            font: { size: 10, weight: 'bold' }
                        }
                    }
                }
            }
        });
    });

    let carnetCalendarYear = new Date().getFullYear();
    let carnetCalendarMonth = new Date().getMonth();

    // Fonction d'ouverture dynamique du modal de détails
    function openClientModal(client, totalPaid, daysCovered, timeFormatted) {
        document.getElementById('modal-client-fullname').innerText = `${client.firstname} ${client.name}`;
        document.getElementById('modal-client-phone').innerText = client.phone || 'Non renseigné';
        document.getElementById('modal-client-carnet').innerText = client.active_carnet ? `N° ${client.active_carnet.number}` : 'Aucun carnet actif';
        document.getElementById('modal-client-duration').innerText = `${daysCovered} jours (${timeFormatted})`;

        // Remplissage Collecteur
        if (client.collector) {
            document.getElementById('modal-client-collector-name').innerText = `${client.collector.firstname} ${client.collector.name}`;
            document.getElementById('modal-client-collector-zone').innerText = client.collector.activity_zone || 'N/A';
        } else {
            document.getElementById('modal-client-collector-name').innerText = 'Système / Aucun';
            document.getElementById('modal-client-collector-zone').innerText = 'N/A';
        }

        // Gestion financière
        const target = parseFloat(client.target_amount) || 0;
        document.getElementById('modal-client-paid').innerText = new Intl.NumberFormat('fr-FR').format(totalPaid);
        document.getElementById('modal-client-target').innerText = new Intl.NumberFormat('fr-FR').format(target);

        // Barre de progression dynamique
        const percentage = target > 0 ? Math.min(100, (totalPaid / target) * 100) : 0;
        const progressBar = document.getElementById('modal-client-progress-bar');
        progressBar.style.width = `${percentage}%`;

        // Style du badge et de la barre selon le statut
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

        // Remplissage du visuel du Carnet (Calendrier)
        const dailyRate = parseFloat(client.daily_contribution) || 0;
        document.getElementById('modal-client-daily-rate-badge').innerText = new Intl.NumberFormat('fr-FR').format(dailyRate);

        // NOUVEAU: Remplir le résumé des cotisations
        const contributions = client.contributions || [];
        const validatedDays = contributions.filter(c => c.validation_status === 'validated').length;
        const pendingDays = contributions.filter(c => c.validation_status === 'pending').length;
        const targetDays = dailyRate > 0 ? Math.round(target / dailyRate) : 100;

        document.getElementById('modal-client-validated-count').innerText = validatedDays;
        document.getElementById('modal-client-pending-count').innerText = pendingDays;
        document.getElementById('modal-client-total-days-count').innerText = `${validatedDays + pendingDays} / ${targetDays} j`;

        // Stocker le client actif en variable globale pour nouvelle souscription et calendrier
        window.activeModalClient = client;

        // Ouvrir le calendrier du carnet sur le mois de la dernière cotisation
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

        // Affichage du modal
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

    function closeClientModal() {
        document.getElementById('modal-client-details').classList.add('hidden');
    }
</script>
@endsection
