@extends('layouts.app')

@section('title', 'Tableau de bord Collaborateur')

@section('content')
<div class="space-y-8">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Espace Collaborateur</h1>
            <p class="text-slate-500 text-sm mt-1">Bienvenue, {{ $user->firstname }} {{ $user->name }}. Titre/Fonction : <span class="font-bold text-slate-700">{{ $user->activity_zone ?? 'Collaborateur' }}</span></p>
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

    <!-- ACCÈS AUTORISÉS -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-900">Vos modules d'accès autorisés</h2>
        <p class="text-slate-400 text-xs">Vous tombez uniquement sur les accès configurés par l'administrateur principal d'ACOR.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php $hasAnyAccess = false; @endphp

            <!-- Module 1: Validation Financière -->
            @if($user->can_validate_contributions)
                @php $hasAnyAccess = true; @endphp
                <div class="bg-white border border-slate-200 hover:border-blue-300 rounded-3xl p-6 shadow-sm transition hover:shadow-md flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl w-fit">
                            <i data-lucide="check-square" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Validation Financière</h3>
                        <p class="text-xs text-slate-500">Valider les cotisations journalières enregistrées par les collecteurs sur le terrain.</p>
                    </div>
                    <a href="{{ route('admin.validation') }}" class="w-full text-center py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition block">
                        Accéder au module
                    </a>
                </div>
            @endif

            <!-- Module 2: Gestion des Collecteurs -->
            @if($user->can_manage_collectors)
                @php $hasAnyAccess = true; @endphp
                <div class="bg-white border border-slate-200 hover:border-emerald-300 rounded-3xl p-6 shadow-sm transition hover:shadow-md flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl w-fit">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Gestion des Collecteurs</h3>
                        <p class="text-xs text-slate-500">Suivre les dossiers, ajouter ou modifier les agents collecteurs sur le terrain.</p>
                    </div>
                    <a href="{{ route('admin.collectors') }}" class="w-full text-center py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition block">
                        Accéder au module
                    </a>
                </div>
            @endif

            <!-- Module 3: Consultation de l'Historique / Audit Logs -->
            @if($user->can_view_audit_logs)
                @php $hasAnyAccess = true; @endphp
                <div class="bg-white border border-slate-200 hover:border-indigo-300 rounded-3xl p-6 shadow-sm transition hover:shadow-md flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl w-fit">
                            <i data-lucide="activity" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Historique & Traçabilité</h3>
                        <p class="text-xs text-slate-500">Consulter l'historique complet des actions effectuées par l'ensemble des utilisateurs.</p>
                    </div>
                    <a href="{{ route('admin.historique') }}" class="w-full text-center py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition block">
                        Accéder au module
                    </a>
                </div>
            @endif

            @if(!$hasAnyAccess)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 p-6 rounded-3xl col-span-3 text-center space-y-2">
                    <i data-lucide="shield-alert" class="w-10 h-10 text-amber-500 mx-auto"></i>
                    <h3 class="text-sm font-bold">Aucun accès configuré</h3>
                    <p class="text-xs text-amber-700 max-w-md mx-auto">Votre compte a été activé, mais aucun droit d'accès ne vous a encore été attribué. Veuillez contacter l'administrateur principal d'ACOR pour obtenir des habilitations.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
