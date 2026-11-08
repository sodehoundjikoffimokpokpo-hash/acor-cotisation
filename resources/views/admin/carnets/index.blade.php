@extends('layouts.app')

@section('content')
<div class="container mx-auto px-6 py-8">

    <!-- En-tête de la page -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Gestion du Stock & Lots de Carnets</h2>
            <p class="text-sm text-gray-500 mt-1">Suivi du stock général, affectation aux collecteurs et gestion automatique des pénalités des lots.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <!-- Bouton Nouvel Approvisionnement par Lot -->
            <button onclick="document.getElementById('modal-lot-approvisionner').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center space-x-2 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Nouvel Approvisionnement (Lot)</span>
            </button>

            <!-- Bouton Affectation en masse -->
            <button onclick="document.getElementById('modal-affecter-stock').classList.remove('hidden')" class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center space-x-2 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Affecter à un collecteur</span>
            </button>
        </div>
    </div>

    <!-- Alertes de Notification -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center space-x-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center space-x-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Cartes de Statistiques Rapides -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center space-x-4">
            <div class="p-3 rounded-lg bg-gray-50 text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase">Stock Central Dispo</p>
                <p class="text-xl font-bold text-gray-800">{{ $statistics['disponible_siege'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center space-x-4">
            <div class="p-3 rounded-lg bg-yellow-50 text-yellow-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase">Total en Circulation</p>
                <p class="text-xl font-bold text-gray-800">{{ $statistics['en_circulation'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center space-x-4">
            <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase">Total Clients Actifs</p>
                <p class="text-xl font-bold text-gray-800">{{ $statistics['actif'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center space-x-4">
            <div class="p-3 rounded-lg bg-red-50 text-red-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase">Lots Expirés</p>
                <p class="text-xl font-bold text-gray-800">{{ $statistics['lots_expires'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- SECTION : SUIVI DES LOTS DE STOCK DE CARNETS -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Suivi et Validité des Lots
        </h3>
        @php
            $lotsGrouped = $lots->groupBy(function($l) {
                return $l->country->name ?? 'Non Assigné';
            });
        @endphp

        <div class="space-y-6">
            @forelse($lotsGrouped as $countryName => $countryLots)
                <div class="border border-slate-150 rounded-2xl p-4 bg-slate-50/50 space-y-3">
                    <h4 class="text-xs font-bold text-slate-800 flex items-center bg-white px-3 py-1.5 border border-slate-250 rounded-xl shadow-xs self-start w-fit">
                        <i data-lucide="globe" class="w-3.5 h-3.5 text-blue-600 mr-1.5"></i>
                        {{ $countryName }}
                    </h4>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-150 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-400 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                                        <th class="px-6 py-4">N° Lot / Article</th>
                                        <th class="px-6 py-4">Date Achat</th>
                                        <th class="px-6 py-4">Quantité Initiale</th>
                                        <th class="px-6 py-4">Disponibles</th>
                                        <th class="px-6 py-4">Date Limite Expiration</th>
                                        <th class="px-6 py-4">Pénalité Actuelle</th>
                                        <th class="px-6 py-4">Statut</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                                    @foreach($countryLots as $lot)
                                        <tr class="hover:bg-gray-50/50 transition">
                                            <td class="px-6 py-4 font-bold text-gray-900">
                                                <div>{{ $lot->lot_number }}</div>
                                                <div class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ $lot->article_name ?? 'Aucun Article' }}</div>
                                            </td>
                                            <td class="px-6 py-4">{{ $lot->created_at->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4">{{ $lot->initial_quantity }} pcs</td>
                                            <td class="px-6 py-4 font-semibold text-blue-600">
                                                {{ $lot->carnets()->whereNull('collector_id')->whereNull('client_id')->count() }} pcs
                                            </td>
                                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($lot->expiration_date)->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4 font-medium text-gray-800">{{ number_format($lot->penalty_amount, 0, ',', ' ') }} FCFA</td>
                                            <td class="px-6 py-4">
                                                @if(\Carbon\Carbon::parse($lot->expiration_date)->isPast())
                                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-50 text-red-700 border border-red-200">
                                                        Expiré (Pénalité active)
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-50 text-green-700 border border-green-200">
                                                        Valide
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <button onclick="openEditPenaltyModal('{{ $lot->id }}', '{{ $lot->lot_number }}', '{{ $lot->penalty_amount }}')" class="text-blue-600 hover:text-blue-900 font-medium text-xs bg-blue-50 hover:bg-blue-100 px-2.5 py-1.5 rounded-lg transition">
                                                    Modifier Pénalité
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-gray-400 italic bg-white rounded-xl border border-gray-150">Aucun lot enregistré. Cliquez sur "Nouvel Approvisionnement" pour commencer.</div>
            @endforelse
        </div>
    </div>

    <!-- ZONE DE FILTRES ET RECHERCHE -->
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6">
        <form action="{{ route('admin.carnets.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="N° Carnet, Nom client..." class="w-full text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Statut</label>
                <select name="status" class="w-full text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="">Tous les états</option>
                    <option value="disponible" {{ request('status') == 'disponible' ? 'selected' : '' }}>Disponible (Vierge au siège)</option>
                    <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Détenu par un collecteur</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Consommé (Client Actif)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Collecteur Attribué</label>
                <select name="collector_id" class="w-full text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="">Tous les collecteurs</option>
                    @foreach($collectors ?? [] as $collector)
                        <option value="{{ $collector->id }}" {{ request('collector_id') == $collector->id ? 'selected' : '' }}>
                            {{ $collector->name }} (Stock dispo: {{ $collector->carnets_stock ?? 0 }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-gray-800 hover:bg-gray-900 text-white font-medium text-sm px-4 py-2 rounded-lg transition">Filtrer</button>
                <a href="{{ route('admin.carnets.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm p-2 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.253 8H18"/></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- SEPARATION PAR TARIF (TABS) -->
    <div class="flex border-b border-gray-200 mb-6 space-x-2">
        <a href="{{ route('admin.carnets.index', array_merge(request()->query(), ['daily_rate' => ''])) }}" 
           class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider transition border-b-2 {{ !request()->filled('daily_rate') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Tous les tarifs
        </a>
        <a href="{{ route('admin.carnets.index', array_merge(request()->query(), ['daily_rate' => '100'])) }}" 
           class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider transition border-b-2 {{ request('daily_rate') == '100' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Carnets de 100 FCFA
        </a>
        <a href="{{ route('admin.carnets.index', array_merge(request()->query(), ['daily_rate' => '150'])) }}" 
           class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider transition border-b-2 {{ request('daily_rate') == '150' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Carnets de 150 FCFA
        </a>
    </div>

    <!-- TABLEAU DES CARNETS INDIVIDUELS -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-400 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4">Numéro de Carnet</th>
                        <th class="px-6 py-4">Tarif</th>
                        <th class="px-6 py-4">Rattaché au Lot</th>
                        <th class="px-6 py-4">Pays</th>
                        <th class="px-6 py-4">Collecteur / Gestionnaire</th>
                        <th class="px-6 py-4">Client Associé</th>
                        <th class="px-6 py-4">Statut / État</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($carnets ?? [] as $carnet)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-900">N° {{ $carnet->number }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-100">
                                    {{ number_format($carnet->lot->daily_rate ?? 0, 0) }} FCFA
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-gray-500">{{ $carnet->lot->lot_number ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @if($carnet->lot && $carnet->lot->country)
                                    <span class="inline-flex items-center text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-xl">
                                        <i data-lucide="globe" class="w-3.5 h-3.5 mr-1 text-slate-400"></i>
                                        {{ $carnet->lot->country->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Non Assigné</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($carnet->collector)
                                    <span class="font-medium text-gray-800">{{ $carnet->collector->name }}</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">Au Siège (Stock Central)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $carnet->client->name ?? 'Aucun — Carnet vierge' }}
                            </td>
                            <td class="px-6 py-4">
                                @if(!$carnet->collector_id && !$carnet->client_id)
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200">Disponible au Siège</span>
                                @elseif($carnet->collector_id && !$carnet->client_id)
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-purple-50 text-purple-700 border border-purple-200">Dans le stock de l'agent</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-50 text-green-700 border border-green-200">Actif (Client)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.carnets.show', $carnet->id) }}" class="text-gray-600 hover:text-gray-900 font-medium text-xs bg-gray-100 hover:bg-gray-200 px-2.5 py-1.5 rounded-lg transition">Historique</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">Aucun carnet trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($carnets) && method_exists($carnets, 'hasPages') && $carnets->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $carnets->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL 1 : NOUVEL APPROVISIONNEMENT EN MASSE VIA UN LOT -->
<div id="modal-lot-approvisionner" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl border border-gray-100 max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center sticky top-0 z-10">
            <h3 class="text-lg font-bold text-gray-800">Ajouter un Lot de Carnets (Article)</h3>
            <button onclick="document.getElementById('modal-lot-approvisionner').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.carnets.store-bulk') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Numéro unique du Lot</label>
                <input type="text" name="batch_number" required placeholder="Ex: LOT-2026-A" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantité à générer</label>
                    <input type="number" name="quantity" min="1" max="1000" required placeholder="Ex: 100" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Validité (en mois)</label>
                    <input type="number" name="validity_months" min="1" max="24" value="6" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant de la pénalité si expiré (FCFA)</label>
                <input type="number" name="penalty_amount" min="0" value="5000" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
            </div>
            @if(auth()->user()->country_id)
                <input type="hidden" name="country_id" value="{{ auth()->user()->country_id }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pays d'affectation</label>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 font-bold flex items-center justify-between shadow-sm">
                        <span class="text-gray-500 font-medium">Affecté à :</span>
                        <span class="text-indigo-700 font-extrabold">{{ auth()->user()->country->name ?? 'Mon Pays' }}</span>
                    </div>
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pays d'affectation</label>
                    <select name="country_id" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                        <option value="" disabled selected>-- Choisir le Pays --</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }} ({{ $country->currency_code }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- NOUVEAU: CHAMPS DE L'ARTICLE ASSOCIÉ -->
            <div class="border-t border-gray-150 pt-3 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Détails de l'Article représenté</h4>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nom de l'Article (ex: Moto TVS, Frigo)</label>
                    <input type="text" name="article_name" required placeholder="Ex: Moto TVS 125cc" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Prix d'Achat Estimé</label>
                        <input type="number" id="input-purchase-price" name="purchase_price" min="0" value="0" required oninput="calculateBulkPricing()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Frais de Transport</label>
                        <input type="number" id="input-transport-fees" name="transport_fees" min="0" value="0" required oninput="calculateBulkPricing()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Frais de Recherche</label>
                        <input type="number" id="input-search-fees" name="search_fees" min="0" value="0" required oninput="calculateBulkPricing()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Commission Collecteur</label>
                        <input type="number" id="input-collector-commission" name="collector_commission" min="0" value="0" required oninput="calculateBulkPricing()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Frais Administratifs</label>
                        <input type="number" id="input-admin-fees" name="admin_fees" min="0" value="0" required oninput="calculateBulkPricing()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Provision sur Prix</label>
                        <input type="number" id="input-price-provision" name="price_provision" min="0" value="0" required oninput="calculateBulkPricing()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div class="col-span-2">
                        <label class="block font-medium text-gray-700 mb-1">Marge ACOR</label>
                        <input type="number" id="input-acor-margin" name="acor_margin" min="0" value="0" required oninput="calculateBulkPricing()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700">Prix Final de l'Article</span>
                    <input type="number" id="input-final-price" readonly value="0" class="w-28 text-right bg-transparent font-black text-slate-900 border-none p-0 focus:ring-0 text-sm">
                </div>
            </div>

            <!-- CONFIGURATION CARNET -->
            <div class="border-t border-gray-150 pt-3 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Configuration du Carnet</h4>
                <div class="grid grid-cols-3 gap-3 text-xs">
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Coût Carnet</label>
                        <input type="number" name="card_cost" min="0" value="2000" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Jours Couverts</label>
                        <input type="number" id="input-days-covered" name="days_covered" min="1" value="100" required oninput="calculateBulkPricing()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Taux Jour (FCFA)</label>
                        <input type="number" id="input-daily-rate" name="daily_rate" min="0" value="0" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modal-lot-approvisionner').classList.add('hidden')" class="px-4 py-2 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">Entrer en stock</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2 : AFFECTATION DE VOLUME DE STOCK À UN COLLECTEUR -->
<div id="modal-affecter-stock" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl border border-gray-100 max-w-md w-full overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Affecter du stock à un collecteur</h3>
            <button onclick="document.getElementById('modal-affecter-stock').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.carnets.assign') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sélectionner le collecteur</label>
                <select name="collector_id" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    <option value="">-- Choisir un agent sur le terrain --</option>
                    @foreach($collectors ?? [] as $collector)
                        <option value="{{ $collector->id }}">{{ $collector->name }} (Stock actuel: {{ $collector->carnets_stock ?? 0 }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type de carnet (Tarif)</label>
                <select name="daily_rate" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                    @forelse($availableRates ?? [] as $rate)
                        <option value="{{ $rate }}" {{ $loop->first ? 'selected' : '' }}>Tarif {{ number_format($rate, 0, ',', ' ') }} FCFA</option>
                    @empty
                        <option value="100">Tarif 100 FCFA</option>
                        <option value="150" selected>Tarif 150 FCFA</option>
                        <option value="200">Tarif 200 FCFA</option>
                        <option value="500">Tarif 500 FCFA</option>
                    @endforelse
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de carnets à lui transférer</label>
                <input type="number" name="quantity" min="1" required placeholder="Ex: 50" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
                <p class="text-xs text-gray-400 mt-1">Les carnets vierges disponibles au siège seront automatiquement prélevés et ajoutés à son stock.</p>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-affecter-stock').classList.add('hidden')" class="px-4 py-2 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">Valider le transfert</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3 : MODIFIER LE MONTANT DE LA PÉNALITÉ D'UN LOT -->
<div id="modal-modifier-penalite" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl border border-gray-100 max-w-md w-full overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Modifier la pénalité du Lot <span id="span-batch-number" class="text-blue-600"></span></h3>
            <button onclick="document.getElementById('modal-modifier-penalite').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="form-modifier-penalite" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau montant de la pénalité (FCFA)</label>
                <input type="number" name="penalty_amount" id="input-penalty-amount" min="0" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 transition">
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-modifier-penalite').classList.add('hidden')" class="px-4 py-2 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
    function calculateBulkPricing() {
        const pPrice = parseFloat(document.getElementById('input-purchase-price').value) || 0;
        const tFees = parseFloat(document.getElementById('input-transport-fees').value) || 0;
        const sFees = parseFloat(document.getElementById('input-search-fees').value) || 0;
        const cComm = parseFloat(document.getElementById('input-collector-commission').value) || 0;
        const aFees = parseFloat(document.getElementById('input-admin-fees').value) || 0;
        const pProv = parseFloat(document.getElementById('input-price-provision').value) || 0;
        const aMarg = parseFloat(document.getElementById('input-acor-margin').value) || 0;

        const finalPrice = pPrice + tFees + sFees + cComm + aFees + pProv + aMarg;
        document.getElementById('input-final-price').value = finalPrice;

        const days = parseInt(document.getElementById('input-days-covered').value) || 1;
        const dailyRate = Math.round(finalPrice / days);
        document.getElementById('input-daily-rate').value = dailyRate;
    }

    function openEditPenaltyModal(id, batchNumber, currentPenalty) {
        document.getElementById('span-batch-number').innerText = batchNumber;
        document.getElementById('input-penalty-amount').value = currentPenalty;
        // Met à jour dynamiquement l'action vers la route de mise à jour du lot
        document.getElementById('form-modifier-penalite').action = "/admin/lots/" + id + "/update-penalty";
        document.getElementById('modal-modifier-penalite').classList.remove('hidden');
    }
</script>
@endsection
