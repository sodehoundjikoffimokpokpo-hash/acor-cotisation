@extends('layouts.app')

@section('title', 'Validation Financière de Fin de Journée')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Validation Financière</h1>
            <p class="text-slate-500 text-sm mt-1">Vérification et validation définitive en fin de journée de l'ensemble des cotisations.</p>
        </div>
        <div class="flex space-x-2">
            @if($pendingContributions->count() > 0)
                <form method="POST" action="{{ route('admin.validation.approve-all') }}" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center space-x-2 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition-all text-sm shadow-md">
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                        <span>Tout valider ({{ number_format($totalPendingAmount, 0, ',', ' ') }} FCFA)</span>
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2 py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition-all text-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Retour au Dashboard</span>
            </a>
        </div>
    </div>

    <!-- 1. Cotisations Individuelles -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-bold text-slate-900">Cotisations en Attente</h2>
            <p class="text-slate-400 text-xs">{{ $pendingContributions->count() }} versements nécessitant votre validation.</p>
        </div>
        <div class="overflow-x-auto">
            <!-- Ton tableau de cotisations reste identique -->
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr class="text-left text-slate-400 text-xs uppercase font-semibold">
                        <th class="pb-3">Client / Carnet</th>
                        <th class="pb-3">Collecteur / Zone</th>
                        <th class="pb-3">Date du versement</th>
                        <th class="pb-3 text-right">Montant</th>
                        <th class="pb-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($pendingContributions as $contribution)
                        <tr>
                            <td class="py-4 font-bold text-slate-900">
                                <div>{{ $contribution->client->firstname }} {{ $contribution->client->name }}</div>
                                <div class="text-[10px] text-slate-400 font-medium mt-0.5">Carnet N° {{ $contribution->client->activeCarnet->number ?? 'N/A' }}</div>
                            </td>
                            <td class="py-4">
                                <div class="font-semibold text-slate-800">{{ $contribution->collector->firstname ?? 'N/A' }} {{ $contribution->collector->name ?? '' }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $contribution->collector->activity_zone ?? 'N/A' }}</div>
                            </td>
                            <td class="py-4 text-slate-500">
                                {{ \Carbon\Carbon::parse($contribution->payment_date)->format('d/m/Y') }}
                            </td>
                            <td class="py-4 text-right font-bold text-slate-900">
                                {{ number_format($contribution->amount, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-4 text-right">
                                <form method="POST" action="{{ route('admin.validation.approve', $contribution->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="py-1.5 px-3 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-xl font-semibold transition-all text-xs">
                                        Valider
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 text-xs">Aucune cotisation en attente de validation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. NOUVEAU : Caisses à Clôturer (Synthèse des collecteurs) -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-bold text-slate-900">Caisses à Valider (Clôtures journalières)</h2>
            <p class="text-slate-400 text-xs">Synthèse des journées clôturées par les collecteurs.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr class="text-left text-slate-400 text-xs uppercase font-semibold">
                        <th class="pb-3">Collecteur</th>
                        <th class="pb-3">Date de clôture</th>
                        <th class="pb-3">Nb Cotisations</th>
                        <th class="pb-3">Montant Total</th>
                        <th class="pb-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($caissesEnAttente as $caisse)
                    <tr>
                        <td class="py-4 font-semibold text-slate-900">{{ $caisse->collector->firstname }} {{ $caisse->collector->name }}</td>
                        <td class="py-4 text-slate-500">{{ \Carbon\Carbon::parse($caisse->closure_date)->format('d/m/Y') }}</td>
                        <td class="py-4 text-slate-500">{{ $caisse->nb_cotisations }}</td>
                        <td class="py-4 font-bold text-indigo-700">{{ number_format($caisse->montant_total, 0, ',', ' ') }} FCFA</td>
                        <td class="py-4 text-right">
                            <form method="POST" action="{{ route('admin.valider.caisse', $caisse->id) }}">
                                @csrf
                                <button type="submit" class="py-1.5 px-3 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-800 rounded-xl font-semibold transition-all text-xs">
                                    Valider la caisse
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400 text-xs">Aucune caisse en attente de clôture.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
