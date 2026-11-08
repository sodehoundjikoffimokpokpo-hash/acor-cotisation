@extends('layouts.app')

@section('title', 'Comparatif des Tarifs Journaliers')

@section('content')
<div class="space-y-8">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <a href="{{ route('admin.finance') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-brand-600 mb-2 transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Retour à la comptabilité
            </a>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Comparatif des Tarifs Journaliers</h1>
            <p class="text-slate-500 text-sm mt-1">Analyse comparative des performances entre le tarif de 100 FCFA et celui de 150 FCFA par jour.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.reports.tarifs', ['export' => 'csv']) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center space-x-2 transition shadow-sm text-xs">
                <i data-lucide="download" class="w-4 h-4 text-white"></i>
                <span>Exporter en CSV</span>
            </a>
            <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-950 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center space-x-2 transition shadow-sm text-xs">
                <i data-lucide="printer" class="w-4 h-4 text-white"></i>
                <span>Imprimer / PDF</span>
            </button>
        </div>
    </div>

    <!-- COMPARATIF CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tarif 100 FCFA -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900 flex items-center">
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-xl mr-2.5">
                        <i data-lucide="percent" class="w-5 h-5"></i>
                    </span>
                    <span>Tarif 100 FCFA / jour</span>
                </h3>
                <span class="text-xs bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full font-bold">Standard</span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Clients Actifs</p>
                    <p class="text-2xl font-black text-slate-800">{{ $report['rate100']['count'] }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Objectif Cumulé</p>
                    <p class="text-2xl font-black text-slate-800">{{ number_format($report['rate100']['target'], 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">F</span></p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Validé Encaissé</p>
                    <p class="text-2xl font-black text-blue-600">{{ number_format($report['rate100']['paid'], 0, ',', ' ') }} <span class="text-xs font-normal text-blue-400">F</span></p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Taux Réalisation</p>
                    <p class="text-2xl font-black text-emerald-600">{{ round($report['rate100']['pct'], 1) }}%</p>
                </div>
            </div>

            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mt-4">
                <div class="h-full bg-blue-500 rounded-full" style="width: {{ min(100, $report['rate100']['pct']) }}%"></div>
            </div>
        </div>

        <!-- Tarif 150 FCFA -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900 flex items-center">
                    <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl mr-2.5">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </span>
                    <span>Tarif 150 FCFA / jour</span>
                </h3>
                <span class="text-xs bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full font-bold">Premium</span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Clients Actifs</p>
                    <p class="text-2xl font-black text-slate-800">{{ $report['rate150']['count'] }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Objectif Cumulé</p>
                    <p class="text-2xl font-black text-slate-800">{{ number_format($report['rate150']['target'], 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">F</span></p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Validé Encaissé</p>
                    <p class="text-2xl font-black text-indigo-600">{{ number_format($report['rate150']['paid'], 0, ',', ' ') }} <span class="text-xs font-normal text-indigo-400">F</span></p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Taux Réalisation</p>
                    <p class="text-2xl font-black text-emerald-600">{{ round($report['rate150']['pct'], 1) }}%</p>
                </div>
            </div>

            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mt-4">
                <div class="h-full bg-indigo-500 rounded-full" style="width: {{ min(100, $report['rate150']['pct']) }}%"></div>
            </div>
        </div>
    </div>

    <!-- TABLE COMPARATIVE -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-6">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Synthèse Globale Comparée</h2>
            <p class="text-slate-400 text-xs">Comparatif des volumes financiers et taux d'engagement.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr class="text-left text-slate-400 text-xs uppercase font-semibold">
                        <th class="pb-3">Tarif</th>
                        <th class="pb-3 text-center">Clients inscrits</th>
                        <th class="pb-3 text-right">Cible Financière Cumulée</th>
                        <th class="pb-3 text-right">Montant Encaissé (Validé)</th>
                        <th class="pb-3 text-center">Taux d'Avancement moyen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-4 font-bold text-slate-900">100 FCFA / Jour</td>
                        <td class="py-4 text-center font-semibold">{{ $report['rate100']['count'] }}</td>
                        <td class="py-4 text-right font-mono font-bold">{{ number_format($report['rate100']['target'], 0, ',', ' ') }} FCFA</td>
                        <td class="py-4 text-right font-mono font-bold text-blue-600">{{ number_format($report['rate100']['paid'], 0, ',', ' ') }} FCFA</td>
                        <td class="py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800">
                                {{ round($report['rate100']['pct'], 1) }}%
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-4 font-bold text-slate-900">150 FCFA / Jour</td>
                        <td class="py-4 text-center font-semibold">{{ $report['rate150']['count'] }}</td>
                        <td class="py-4 text-right font-mono font-bold">{{ number_format($report['rate150']['target'], 0, ',', ' ') }} FCFA</td>
                        <td class="py-4 text-right font-mono font-bold text-indigo-600">{{ number_format($report['rate150']['paid'], 0, ',', ' ') }} FCFA</td>
                        <td class="py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800">
                                {{ round($report['rate150']['pct'], 1) }}%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
