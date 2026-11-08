@extends('layouts.app')

@section('title', 'Gestion des Droits & Habilitations')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Gestion des Droits & Habilitations</h1>
            <p class="text-slate-500 text-sm mt-1">Octroyez ou restreignez l'accès des collaborateurs aux fonctionnalités stratégiques.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2 py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition-all text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Retour au Dashboard</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center space-x-2 shadow-sm mb-6">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Colonne Gauche : Matrice des Droits (2/3) -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 lg:col-span-2 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900">Matrice des Droits des Collaborateurs</h2>
                <p class="text-slate-400 text-xs">Les administrateurs possèdent par défaut tous les droits d'accès.</p>
            </div>

            @php
                $usersGrouped = $users->groupBy(function($u) {
                    return $u->country->name ?? 'Non Assigné';
                });
            @endphp

            <div class="space-y-6">
                @forelse($usersGrouped as $countryName => $countryUsers)
                    <div class="border border-slate-150 rounded-2xl p-4 bg-slate-50/50 space-y-3">
                        <h3 class="text-xs font-bold text-slate-800 flex items-center bg-white px-3 py-1.5 border border-slate-250 rounded-xl shadow-xs self-start w-fit">
                            <i data-lucide="globe" class="w-3.5 h-3.5 text-brand-600 mr-1.5"></i>
                            {{ $countryName }}
                        </h3>
                        <div class="overflow-x-auto bg-white border border-slate-200 rounded-xl p-3 shadow-xs">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead>
                                    <tr class="text-left text-slate-400 text-xs uppercase font-semibold">
                                        <th class="pb-3">Utilisateur / Rôle</th>
                                        <th class="pb-3 text-center">Validation Financière</th>
                                        <th class="pb-3 text-center">Gestion des Collecteurs</th>
                                        <th class="pb-3 text-center">Consultation Traçabilité (Logs)</th>
                                        <th class="pb-3 text-center">Gestion Lots & Tarifs</th>
                                        <th class="pb-3 text-center">Gestion Géographique</th>
                                        <th class="pb-3 text-center">Gestion Sauvegardes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @foreach($countryUsers as $user)
                                        <tr>
                                            <!-- User Details -->
                                            <td class="py-4">
                                                <div class="font-bold text-slate-900">{{ $user->firstname }} {{ $user->name }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono mt-0.5 flex items-center flex-wrap gap-1.5">
                                                    <span class="flex items-center">
                                                        <i data-lucide="user" class="w-3.5 h-3.5 mr-1"></i>
                                                        {{ $user->username }}
                                                    </span>
                                                    <span class="text-slate-300">&bull;</span>
                                                    <span class="capitalize px-1.5 py-0.2 bg-indigo-50 text-indigo-700 rounded border border-indigo-100 font-bold text-[9px]">
                                                        @if($user->role === 'secretary')
                                                            Secrétaire
                                                        @elseif($user->role === 'employee')
                                                            Employé ({{ $user->activity_zone ?? 'Assistant' }})
                                                        @elseif($user->role === 'admin')
                                                            Admin Local
                                                        @else
                                                            Collecteur
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Permission 1: Validation Financière -->
                                            <td class="py-4 text-center">
                                                <form method="POST" action="{{ route('admin.rights.toggle', [$user->id, 'can_validate_contributions']) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl transition-all border
                                                        @if($user->can_validate_contributions) bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100
                                                        @else bg-slate-50 border-slate-200 text-slate-400 hover:bg-slate-100
                                                        @endif">
                                                        <i data-lucide="@if($user->can_validate_contributions) shield-check @else shield-alert @endif" class="w-4 h-4 mr-1.5"></i>
                                                        <span class="text-xs font-bold">
                                                            @if($user->can_validate_contributions) Actif @else Inactif @endif
                                                        </span>
                                                     </button>
                                                </form>
                                            </td>

                                            <!-- Permission 2: Gestion des Collecteurs -->
                                            <td class="py-4 text-center">
                                                <form method="POST" action="{{ route('admin.rights.toggle', [$user->id, 'can_manage_collectors']) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl transition-all border
                                                        @if($user->can_manage_collectors) bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100
                                                        @else bg-slate-50 border-slate-200 text-slate-400 hover:bg-slate-100
                                                        @endif">
                                                        <i data-lucide="@if($user->can_manage_collectors) shield-check @else shield-alert @endif" class="w-4 h-4 mr-1.5"></i>
                                                        <span class="text-xs font-bold">
                                                            @if($user->can_manage_collectors) Actif @else Inactif @endif
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>

                                            <!-- Permission 3: Consultation des Logs -->
                                            <td class="py-4 text-center">
                                                <form method="POST" action="{{ route('admin.rights.toggle', [$user->id, 'can_view_audit_logs']) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl transition-all border
                                                        @if($user->can_view_audit_logs) bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100
                                                        @else bg-slate-50 border-slate-200 text-slate-400 hover:bg-slate-100
                                                        @endif">
                                                        <i data-lucide="@if($user->can_view_audit_logs) shield-check @else shield-alert @endif" class="w-4 h-4 mr-1.5"></i>
                                                        <span class="text-xs font-bold">
                                                            @if($user->can_view_audit_logs) Actif @else Inactif @endif
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>

                                            <!-- Permission 4: Gestion des Lots & Tarifs -->
                                            <td class="py-4 text-center">
                                                <form method="POST" action="{{ route('admin.rights.toggle', [$user->id, 'can_manage_lots']) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl transition-all border
                                                        @if($user->can_manage_lots) bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100
                                                        @else bg-slate-50 border-slate-200 text-slate-400 hover:bg-slate-100
                                                        @endif">
                                                        <i data-lucide="@if($user->can_manage_lots) shield-check @else shield-alert @endif" class="w-4 h-4 mr-1.5"></i>
                                                        <span class="text-xs font-bold">
                                                            @if($user->can_manage_lots) Actif @else Inactif @endif
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>

                                            <!-- Permission 5: Gestion Géographique -->
                                            <td class="py-4 text-center">
                                                <form method="POST" action="{{ route('admin.rights.toggle', [$user->id, 'can_manage_locations']) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl transition-all border
                                                        @if($user->can_manage_locations) bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100
                                                        @else bg-slate-50 border-slate-200 text-slate-400 hover:bg-slate-100
                                                        @endif">
                                                        <i data-lucide="@if($user->can_manage_locations) shield-check @else shield-alert @endif" class="w-4 h-4 mr-1.5"></i>
                                                        <span class="text-xs font-bold">
                                                            @if($user->can_manage_locations) Actif @else Inactif @endif
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>

                                            <!-- Permission 6: Gestion des Sauvegardes -->
                                            <td class="py-4 text-center">
                                                <form method="POST" action="{{ route('admin.rights.toggle', [$user->id, 'can_manage_backups']) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl transition-all border
                                                        @if($user->can_manage_backups) bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100
                                                        @else bg-slate-50 border-slate-200 text-slate-400 hover:bg-slate-100
                                                        @endif">
                                                        <i data-lucide="@if($user->can_manage_backups) shield-check @else shield-alert @endif" class="w-4 h-4 mr-1.5"></i>
                                                        <span class="text-xs font-bold">
                                                            @if($user->can_manage_backups) Actif @else Inactif @endif
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-xs bg-slate-50 border border-slate-100 rounded-2xl italic">Aucun collaborateur trouvé pour la gestion des droits.</div>
                @endforelse
            </div>
        </div>

        <!-- Colonne Droite : Créer un Collaborateur (1/3) -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-4 self-start">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 flex items-center">
                    <i data-lucide="user-plus" class="w-4 h-4 text-brand-600 mr-2"></i>
                    <span>Créer un Collaborateur</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">Ajouter un secrétaire ou un employé avec accès configurés.</p>
            </div>

            <form action="{{ route('admin.rights.store-collaborator') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Prénom</label>
                        <input type="text" name="firstname" required placeholder="Julie" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nom</label>
                        <input type="text" name="name" required placeholder="DUPONT" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Login (Identifiant)</label>
                    <input type="text" name="username" required placeholder="Ex: julie.dupont" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Mot de passe</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Rôle</label>
                    <select name="role" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="employee">Employé (Accès personnalisés)</option>
                        <option value="secretary">Secrétaire</option>
                        <option value="admin">Administrateur Local</option>
                    </select>
                </div>

                @if(!Auth::user()->country_id)
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pays d'affectation</label>
                        <select name="country_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="" disabled selected>-- Choisir le Pays --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Titre / Fonction (ex: Assistant, Adjoint)</label>
                    <input type="text" name="title" placeholder="Ex: Adjoint de Direction" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Habilitations d'accès</label>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="can_validate_contributions" id="chk_validate" class="rounded text-brand-600 focus:ring-brand-500">
                        <label for="chk_validate" class="text-xs text-slate-600">Validation Financière</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="can_manage_collectors" id="chk_manage" class="rounded text-brand-600 focus:ring-brand-500">
                        <label for="chk_manage" class="text-xs text-slate-600">Gestion des Collecteurs</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="can_view_audit_logs" id="chk_logs" class="rounded text-brand-600 focus:ring-brand-500">
                        <label for="chk_logs" class="text-xs text-slate-600">Consultation Traçabilité (Logs)</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="can_manage_lots" id="chk_lots" class="rounded text-brand-600 focus:ring-brand-500">
                        <label for="chk_lots" class="text-xs text-slate-600">Gestion des Lots & Tarifs</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="can_manage_locations" id="chk_locations" class="rounded text-brand-600 focus:ring-brand-500">
                        <label for="chk_locations" class="text-xs text-slate-600">Gestion Géographique</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="can_manage_backups" id="chk_backups" class="rounded text-brand-600 focus:ring-brand-500">
                        <label for="chk_backups" class="text-xs text-slate-600">Gestion des Sauvegardes</label>
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center space-x-1.5 mt-4">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Créer le Collaborateur</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
