@extends('layouts.app')

@section('title', 'Rapport de Clôture Journalier')

@section('content')
<div class="space-y-6 max-w-lg mx-auto">
    <!-- Header/Back Navigation -->
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold text-slate-900">Clôture Journalière</h1>
        <a href="{{ route('collector.dashboard') }}" class="flex items-center space-x-1.5 py-1.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition-all text-xs">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Retour</span>
        </a>
    </div>

    <!-- Summary Card -->
    <div class="bg-gradient-to-tr from-slate-900 to-slate-800 text-white rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center space-x-3">
            <div class="p-2.5 rounded-xl bg-brand-500/20 text-brand-400">
                <i data-lucide="calculator" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Total Encaissé Aujourd'hui</span>
                <p class="text-xs text-slate-400 font-medium">{{ date('d/m/Y') }}</p>
            </div>
        </div>

        <div class="border-t border-slate-700/60 pt-4">
            <p class="text-3xl font-extrabold tracking-tight">
                {{ number_format($totalCollectedToday, 0, ',', ' ') }} FCFA
            </p>
            <p class="text-xs text-slate-400 mt-1">
                Collecté auprès de <span class="text-brand-400 font-bold">{{ $clientsCountToday }}</span> client(s) unique(s).
            </p>
        </div>

        <!-- BOUTON DE CLÔTURE AJOUTÉ -->
        @php
            $aDejaCloture = \App\Models\CaisseClosure::where('collector_id', auth()->id())->whereDate('closure_date', now())->exists();
        @endphp

        @if(!$aDejaCloture && $totalCollectedToday > 0)
            <form action="{{ route('collector.cloturer-caisse') }}" method="POST" onsubmit="return confirm('Confirmer la clôture de caisse ? Cette action est irréversible.');">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center space-x-2 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition-all text-sm shadow-lg">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Clôturer ma caisse</span>
                </button>
            </form>
        @elseif($aDejaCloture)
            <div class="text-center py-2 bg-emerald-900/50 border border-emerald-700 rounded-xl text-emerald-400 text-xs font-bold">
                Caisse déjà clôturée pour aujourd'hui.
            </div>
        @endif
        <!-- FIN DU BOUTON -->

        <div class="bg-slate-700/30 border border-slate-700/40 rounded-2xl p-3.5 text-xs text-slate-300 flex items-start space-x-2.5">
            <i data-lucide="info" class="w-4 h-4 text-brand-400 flex-shrink-0 mt-0.5"></i>
            <p>Veuillez présenter ce rapport de caisse à l'administrateur avec le versement physique correspondant pour validation définitive.</p>
        </div>
    </div>

    <!-- List of Today's Contributions -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
        <!-- ... (le reste de ton code ne change pas) ... -->
        <div>
            <h3 class="text-sm font-bold text-slate-900">Détails des Encaissements</h3>
            <p class="text-slate-400 text-[10px] mt-0.5">Liste chronologique des saisies du jour.</p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($contributionsToday as $contribution)
                <div class="py-3 flex justify-between items-center text-xs">
                    <div>
                        <p class="font-bold text-slate-800">{{ $contribution->client->firstname }} {{ $contribution->client->name }}</p>
                        <p class="text-[10px] text-slate-400 flex items-center mt-0.5">
                            <i data-lucide="book-open" class="w-3 h-3 mr-0.5"></i>
                            {{ $contribution->client->activeCarnet->number ?? 'Aucun' }}
                            <span class="mx-1">&bull;</span>
                            {{ $contribution->created_at->format('H:i') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-slate-900">{{ number_format($contribution->amount, 0, ',', ' ') }} FCFA</span>
                        <div class="mt-0.5">
                            @if($contribution->validation_status === 'validated')
                                <span class="inline-flex items-center text-[9px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.2 rounded">Validé</span>
                            @else
                                <span class="inline-flex items-center text-[9px] font-semibold text-amber-600 bg-amber-50 px-1.5 py-0.2 rounded">En attente</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-slate-400 text-xs flex flex-col items-center justify-center space-y-2">
                    <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                    <span>Aucun versement enregistré aujourd'hui.</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
