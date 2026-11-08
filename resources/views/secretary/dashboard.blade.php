@extends('layouts.app')

@section('title', 'Espace Secrétariat')

@section('content')
<div class="space-y-8">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Espace Secrétariat</h1>
            <p class="text-slate-500 text-sm mt-1">Supervision administrative, attribution des carnets et validation des cotisations.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center space-x-2 shadow-sm">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center space-x-2 shadow-sm">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- STATISTIQUES RAPIDES (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-brand-50 text-brand-600 rounded-2xl">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Clients Actifs</p>
                <p class="text-xl font-bold text-slate-900">{{ $totalClientsActifs }}</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Encaissé</p>
                <p class="text-xl font-bold text-slate-900">
                    {{ number_format($totalEncaisse, 0, ',', ' ') }} FCFA
                </p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex items-center space-x-4">
            <div class="p-3 {{ $clientsALivrer > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400' }} rounded-2xl">
                <i data-lucide="package" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">À Livrer</p>
                <p class="text-xl font-bold {{ $clientsALivrer > 0 ? 'text-emerald-600 font-black animate-pulse' : 'text-slate-900' }}">
                    {{ $clientsALivrer }}
                </p>
            </div>
        </div>
    </div>

    <!-- Zone principale : 2 colonnes -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Colonne Gauche : Liste des dossiers (2 colonnes) -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 lg:col-span-2 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Dossiers Clients (Société)</h2>
                    <p class="text-slate-400 text-xs">Suivi global de l'ensemble des portefeuilles</p>
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <div class="relative flex-1 md:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" id="table-search" oninput="filterClients()" placeholder="Rechercher un client ou carnet..." class="w-full pl-9 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-brand-500 text-slate-700" value="{{ $search ?? '' }}">
                    </div>
                    <select id="status-filter" onchange="filterClients()" class="bg-slate-50 border border-slate-200 rounded-xl py-1.5 px-3 text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="all">Tous les statuts</option>
                        <option value="active">Actif</option>
                        <option value="goal_reached">Objectif Atteint</option>
                        <option value="delivered">Livré</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead>
                        <tr class="text-left text-slate-400 text-xs uppercase font-semibold">
                            <th class="pb-3">Client / Contact</th>
                            <th class="pb-3">Carnet</th>
                            <th class="pb-3">Progression / Cible</th>
                            <th class="pb-3">Collecteur</th>
                            <th class="pb-3">Statut</th>
                            <th class="pb-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="clients-table-body" class="divide-y divide-slate-100 text-slate-700">
                        @forelse($clients as $client)
                            <tr class="client-row hover:bg-slate-50/50 transition-colors"
                                data-search-text="{{ strtolower($client->firstname . ' ' . $client->name . ' ' . ($client->activeCarnet->number ?? '')) }}"
                                data-status="{{ $client->status }}">
                                <td class="py-4">
                                    <div class="font-bold text-slate-900">{{ $client->firstname }} {{ $client->name }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 flex items-center">
                                        <i data-lucide="phone" class="w-3 h-3 mr-1"></i>
                                        {{ $client->phone ?? 'Aucun téléphone' }}
                                    </div>
                                </td>
                                <td class="py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-slate-100 text-slate-800 font-mono text-xs font-bold">
                                        {{ $client->activeCarnet->number ?? 'Aucun' }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    <div class="font-bold text-slate-800">{{ number_format($client->totalPaid(), 0, ',', ' ') }} / {{ number_format($client->target_amount, 0, ',', ' ') }} FCFA</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Article: <span class="font-semibold text-slate-600">{{ $client->desired_article }}</span></div>
                                </td>
                                <td class="py-4 text-xs font-medium text-slate-600">
                                    {{ $client->collector->firstname ?? '-' }} {{ $client->collector->name ?? '' }}
                                </td>
                                <td class="py-4">
                                    @if($client->status === 'goal_reached')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            Objectif Atteint
                                        </span>
                                    @elseif($client->status === 'delivered')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                            Livré
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-800 border border-brand-200">
                                            Actif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <a href="{{ route('secretary.clients.show', $client->id) }}" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-all" title="Voir la fiche">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>

                                        @if($client->status === 'goal_reached')
                                            <form method="POST" action="{{ route('secretary.clients.deliver', $client->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center space-x-1 py-1.5 px-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition-all text-xs shadow-sm">
                                                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                                    <span>Livrer</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400 px-2">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400">Aucun dossier client trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Colonne Droite : Attribution de carnets et Validation des cotisations -->
        <div class="space-y-6 lg:col-span-1 self-start">

           <!-- BLOC 1 : Affectation de stock / Carnets à un Collecteur -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center">
                        <i data-lucide="book-plus" class="w-4 h-4 text-brand-600 mr-2"></i>
                        <span>Affecter des carnets</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Transférer des carnets vierges à un collecteur terrain.</p>
                </div>

                <form action="{{ route('secretary.carnets.assign') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Sélectionner le collecteur</label>
                        <select name="collector_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:ring-2 focus:ring-brand-500">
                            <option value="">-- Choisir un agent sur le terrain --</option>
                            @foreach($collectors as $collector)
                                <option value="{{ $collector->id }}">{{ $collector->firstname }} {{ $collector->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Type de carnet (Tarif)</label>
                        <select name="daily_rate" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:ring-2 focus:ring-brand-500">
                            <option value="100">Tarif 100 FCFA</option>
                            <option value="150" selected>Tarif 150 FCFA</option>
                            <option value="200">Tarif 200 FCFA</option>
                            <option value="500">Tarif 500 FCFA</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Nombre de carnets à lui transférer</label>
                        <input type="number" name="quantity" required min="1" placeholder="Ex: 50" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-brand-500">
                        <p class="text-[10px] text-slate-400 mt-1">Les carnets vierges seront automatiquement transférés et ajoutés à son stock.</p>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Valider le transfert</span>
                    </button>
                </form>
            </div>

            <!-- BLOC 2 : Validation des Cotisations de la Journée -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-indigo-600 mr-2"></i>
                        <span>Validation des Cotisations</span>
                    </h2>
                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded-full">
                        {{ isset($pendingContributions) ? $pendingContributions->count() : 0 }} en attente
                    </span>
                </div>
                <p class="text-xs text-slate-400">Valider les encaissements effectués par les agents sur le terrain.</p>

                <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                    @forelse($pendingContributions ?? [] as $contrib)
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-2xl space-y-1.5">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-800">{{ $contrib->client->firstname ?? '' }} {{ $contrib->client->name ?? '' }}</span>
                                <span class="text-xs font-bold text-brand-600">{{ number_format($contrib->amount, 0, ',', ' ') }} F</span>
                            </div>
                            <div class="text-[10px] text-slate-400 flex justify-between">
                                <span>Carnet : {{ $contrib->client->activeCarnet->number ?? '-' }}</span>
                                <span>Par : {{ $contrib->collector->firstname ?? 'Agent' }}</span>
                            </div>
                            <form action="{{ route('secretary.contributions.validate', $contrib->id) }}" method="POST" class="pt-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center justify-center space-x-1">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    <span>Valider ce versement</span>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">
                            <i data-lucide="shield-check" class="w-6 h-6 mx-auto text-indigo-300 mb-1"></i>
                            Toutes les cotisations sont validées.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    function filterClients() {
        const searchQuery = document.getElementById('table-search').value.toLowerCase().trim();
        const selectedStatus = document.getElementById('status-filter').value;
        const rows = document.querySelectorAll('.client-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchText = row.getAttribute('data-search-text');
            const status = row.getAttribute('data-status');

            const matchesSearch = searchText.includes(searchQuery);
            const matchesStatus = (selectedStatus === 'all') || (status === selectedStatus);

            if (matchesSearch && matchesStatus) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });
    }
</script>
@endsection
