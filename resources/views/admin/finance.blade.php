@extends('layouts.app')

@section('title', 'Gestion Financière & Comptabilité')

@section('content')
<div class="space-y-8">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <!-- Lien de retour au Dashboard principal -->
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-brand-600 mb-2 transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Retour au Tableau de bord
            </a>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Module Comptabilité</h1>
            <p class="text-slate-500 text-sm mt-1">Suivi financier en temps réel et contrôle des performances des collecteurs.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.reports.tarifs') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center space-x-2 transition shadow-sm text-xs">
                <i data-lucide="bar-chart-2" class="w-4 h-4 text-white"></i>
                <span>Comparatif des Tarifs</span>
            </a>
            <div class="flex items-center space-x-2 bg-emerald-50 border border-emerald-100 px-3 py-1.5 rounded-full shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Flux Temps Réel actif</span>
            </div>
        </div>
    </div>

    <!-- KPIs FINANCIERS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Encaissé Aujourd'hui</p>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($statsCompta['today'] ?? 0, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span></p>
            </div>
        </div>
        <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Encaissé cette Semaine</p>
                <p class="text-2xl font-black text-brand-600 mt-1">{{ number_format($statsCompta['week'] ?? 0, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span></p>
            </div>
        </div>
        <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Encaissé ce Mois</p>
                <p class="text-2xl font-black text-indigo-600 mt-1">{{ number_format($statsCompta['month'] ?? 0, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span></p>
            </div>
        </div>
        <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Encaissé cette Année</p>
                <p class="text-2xl font-black text-slate-800 mt-1">{{ number_format($statsCompta['year'] ?? 0, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span></p>
            </div>
        </div>
    </div>

    <!-- REVERSEMENTS GLOBAUX -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 p-6 rounded-3xl text-white shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-200">Total Reversé par les Collecteurs</p>
                <p class="text-3xl font-black">{{ number_format($statsCompta['total_reversed'] ?? 0, 0, ',', ' ') }} <span class="text-sm font-normal text-emerald-200">FCFA</span></p>
            </div>
            <div class="p-4 bg-white/10 rounded-2xl"><i data-lucide="shield-check" class="w-7 h-7"></i></div>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-amber-600 p-6 rounded-3xl text-white shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-100">Montant Total Restant à Reverser</p>
                <p class="text-3xl font-black">{{ number_format($statsCompta['total_due'] ?? 0, 0, ',', ' ') }} <span class="text-sm font-normal text-amber-100">FCFA</span></p>
            </div>
            <div class="p-4 bg-white/10 rounded-2xl"><i data-lucide="wallet" class="w-7 h-7"></i></div>
        </div>
    </div>

    <!-- TABLEAU COLLECTEURS -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-4 gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Performance Individuelle des Collecteurs</h2>
                <p class="text-slate-400 text-xs">Analyse de l'activité commerciale et état comptable par agent de terrain.</p>
            </div>
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i data-lucide="search" class="w-4 h-4"></i></span>
                <input type="text" id="search-collector" oninput="filterCollectors()" placeholder="Rechercher un collecteur..." class="w-full pl-9 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 text-slate-700 focus:outline-none">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr class="text-left text-slate-400 text-xs uppercase font-semibold">
                        <th class="pb-3">Collecteur / Matricule</th>
                        <th class="pb-3 text-center">Clients</th>
                        <th class="pb-3 text-center">Carnets Vendus</th>
                        <th class="pb-3 text-center">Cotisations</th>
                        <th class="pb-3 text-right">Total Encaissé</th>
                        <th class="pb-3 text-right">Total Reversé</th>
                        <th class="pb-3 text-right">Solde Restant</th>
                    </tr>
                </thead>
                <tbody id="collectors-table-body" class="divide-y divide-slate-100 text-slate-700">
                    @forelse($collectors as $collector)
                        <tr class="collector-row hover:bg-slate-50/60 transition-colors" data-name="{{ strtolower($collector->name) }}">
                            <td class="py-4">
                                <div class="font-bold text-slate-900">{{ $collector->name }}</div>
                                <div class="text-[10px] font-mono text-slate-400 mt-0.5">ID: #{{ str_pad($collector->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="py-4 text-center">
                                <span class="inline-flex px-2.5 py-1 bg-slate-100 text-slate-800 rounded-lg font-semibold text-xs">{{ $collector->clients_count ?? 0 }}</span>
                            </td>
                            <td class="py-4 text-center font-mono text-xs font-bold text-slate-600">{{ $collector->carnets_sold_count ?? 0 }}</td>
                            <td class="py-4 text-center text-slate-600 font-medium">{{ $collector->contributions_count ?? 0 }}</td>
                            <td class="py-4 text-right font-bold text-slate-900">{{ number_format($collector->total_collected ?? 0, 0, ',', ' ') }} F</td>
                            <td class="py-4 text-right font-bold text-emerald-600">{{ number_format($collector->total_reversed ?? 0, 0, ',', ' ') }} F</td>
                            <td class="py-4 text-right whitespace-nowrap">
                                @php $due = ($collector->total_collected ?? 0) - ($collector->total_reversed ?? 0); @endphp
                                <span class="inline-flex font-black px-3 py-1 rounded-xl text-xs {{ $due > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ number_format($due, 0, ',', ' ') }} FCFA
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">Aucun collecteur trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function filterCollectors() {
        const query = document.getElementById('search-collector').value.toLowerCase().trim();
        document.querySelectorAll('.collector-row').forEach(row => {
            row.classList.toggle('hidden', !row.getAttribute('data-name').includes(query));
        });
    }
</script>
@endsection
