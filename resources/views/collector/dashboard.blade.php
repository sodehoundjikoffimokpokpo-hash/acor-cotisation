@extends('layouts.app')

@section('title', 'Espace Terrain - Collecteur')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 lg:space-y-0 lg:grid lg:grid-cols-3 lg:gap-6 lg:max-w-none">

    <!-- COLONNE GAUCHE : Formulaires et Actions -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center bg-slate-900 text-white p-6 -mx-4 sm:mx-0 rounded-b-3xl sm:rounded-3xl shadow-lg shadow-slate-900/10">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Agent de terrain</span>
                <h1 class="text-xl font-bold mt-0.5">{{ Auth::user()->firstname }} {{ Auth::user()->name }}</h1>
                <p class="text-[10px] text-slate-400 capitalize mt-0.5 flex items-center">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 mr-1 text-brand-400"></i>
                    {{ Auth::user()->activity_zone ?? 'Zone non définie' }} ({{ Auth::user()->activity_country ?? 'Togo' }})
                </p>
            </div>
            <a href="{{ route('collector.closure') }}" class="flex flex-col items-center justify-center p-3 bg-brand-600 hover:bg-brand-500 rounded-2xl transition-all shadow-md">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-white"></i>
                <span class="text-[9px] font-bold mt-1 text-white uppercase tracking-wider">Clôture</span>
            </a>
        </div>

        <!-- Modules Administratifs Autorisés -->
        @if(Auth::user()->can_validate_contributions || Auth::user()->can_manage_collectors || Auth::user()->can_view_audit_logs)
            <div class="bg-gradient-to-br from-violet-50 to-indigo-50 border border-violet-200 rounded-3xl p-5 shadow-sm space-y-4">
                <div>
                    <p class="text-[10px] font-bold text-violet-500 uppercase tracking-wider">Accès Administratifs</p>
                    <h3 class="text-sm font-bold text-slate-900 mt-0.5">Modules Autorisés</h3>
                </div>
                <div class="grid grid-cols-1 gap-2.5">
                    @if(Auth::user()->can_manage_collectors)
                        <a href="{{ route('admin.collectors') }}" class="flex items-center justify-between p-3 bg-white hover:bg-violet-100/50 border border-violet-150 rounded-2xl transition-all shadow-xs text-xs font-bold text-slate-800">
                            <span class="flex items-center space-x-2">
                                <i data-lucide="users" class="w-4 h-4 text-violet-600"></i>
                                <span>Gestion des Collecteurs</span>
                            </span>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-violet-400"></i>
                        </a>
                    @endif

                    @if(Auth::user()->can_validate_contributions)
                        <a href="{{ route('admin.validation') }}" class="flex items-center justify-between p-3 bg-white hover:bg-violet-100/50 border border-violet-150 rounded-2xl transition-all shadow-xs text-xs font-bold text-slate-800">
                            <span class="flex items-center space-x-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-violet-600"></i>
                                <span>Validation Financière</span>
                            </span>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-violet-400"></i>
                        </a>
                    @endif

                    @if(Auth::user()->can_view_audit_logs)
                        <a href="{{ route('admin.logs') }}" class="flex items-center justify-between p-3 bg-white hover:bg-violet-100/50 border border-violet-150 rounded-2xl transition-all shadow-xs text-xs font-bold text-slate-800">
                            <span class="flex items-center space-x-2">
                                <i data-lucide="history" class="w-4 h-4 text-violet-600"></i>
                                <span>Consultation des Logs</span>
                            </span>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-violet-400"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- Indicateur Stock -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Mon Stock Disponible</p>
                    <h3 class="text-xl font-bold text-slate-800 mt-0.5">{{ $stockDisponibleCount }} carnet(s)</h3>
                </div>
                <div class="p-2.5 rounded-xl {{ $stockDisponibleCount > 0 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100 animate-pulse' }}">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
            </div>

            @if($stockDisponibleCount > 0)
                <div class="space-y-1.5">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Détail par Tarif :</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        @foreach($stockByRate as $rate => $count)
                            <div class="bg-slate-50 border border-slate-100 p-2 rounded-xl text-center">
                                <span class="block font-bold text-slate-800">{{ $count }} carnet(s)</span>
                                <span class="text-[9px] font-semibold text-slate-400 uppercase">{{ number_format($rate, 0) }} FCFA</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-xs font-semibold flex items-start space-x-2 shadow-sm">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->has('stock_error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs font-semibold flex items-start space-x-2 shadow-sm">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 mt-0.5 shrink-0"></i>
                <span>{{ $errors->first('stock_error') }}</span>
            </div>
        @endif

        <!-- Switch Tab -->
        <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1.5 rounded-2xl">
            <button type="button" onclick="switchTab('payment')" id="tab-btn-payment" class="py-2.5 px-4 rounded-xl text-xs font-bold transition-all bg-white text-brand-700 shadow-sm">
                Enregistrer Versement
            </button>
            <button type="button" onclick="switchTab('client')" id="tab-btn-client" class="py-2.5 px-4 rounded-xl text-xs font-bold transition-all text-slate-600 hover:text-slate-900">
                Nouveau Client
            </button>
        </div>

        <!-- Tab 1: Payment -->
        <div id="tab-payment" class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-brand-600 mr-2"></i>
                    <span>Enregistrer un Versement</span>
                </h2>
                <p class="text-slate-400 text-xs mt-0.5">Saisie immédiate d'une cotisation encaissée.</p>
            </div>

            <form id="contribution-calendar-form" method="POST" action="{{ route('collector.contributions.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="client_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Sélectionner le Client</label>
                    <select id="client_id" name="client_id" required class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm" onchange="loadClientCalendar(this.value)">
                        <option value="" disabled selected>Choisir un client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">
                                {{ $client->firstname }} {{ $client->name }} ({{ $client->activeCarnet ? $client->activeCarnet->number : 'Pas de carnet' }}) - {{ $client->desired_article }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Conteneur Calendrier Dynamique -->
                <div id="calendar-wrapper" class="hidden space-y-4 border-t border-slate-100 pt-4">
                    <!-- Sélecteur de Mode (Unique / Multiple) -->
                    <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-150">
                        <span class="text-xs font-bold text-slate-700">Mode de Sélection</span>
                        <div class="flex items-center space-x-3 text-xs">
                            <label class="inline-flex items-center font-semibold text-slate-600 cursor-pointer">
                                <input type="radio" name="selection_mode" value="single" checked class="text-brand-600 focus:ring-brand-500 mr-1.5" onchange="toggleSelectionMode(this.value)">
                                Cotisation Unique
                            </label>
                            <label class="inline-flex items-center font-semibold text-slate-600 cursor-pointer">
                                <input type="radio" name="selection_mode" value="multiple" class="text-brand-600 focus:ring-brand-500 mr-1.5" onchange="toggleSelectionMode(this.value)">
                                Cotisations Multiples
                            </label>
                        </div>
                    </div>

                    <!-- En-tête du Calendrier (Mois en cours) -->
                    <div class="flex justify-between items-center bg-slate-900 text-white py-1.5 px-3 rounded-xl">
                        <div class="flex items-center space-x-1">
                            <button type="button" onclick="prevCalendarMonth()" class="p-1 text-slate-400 hover:text-white rounded transition hover:bg-slate-800">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <span class="text-xs font-bold uppercase tracking-wider min-w-[120px] text-center" id="calendar-month-name">Mois En Cours</span>
                            <button type="button" onclick="nextCalendarMonth()" class="p-1 text-slate-400 hover:text-white rounded transition hover:bg-slate-800">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <span class="text-[10px] bg-slate-800 text-slate-300 font-semibold px-2 py-0.5 rounded-md" id="calendar-daily-rate">Taux : 0 F/jour</span>
                    </div>

                    <!-- Grille du Calendrier -->
                    <div class="grid grid-cols-7 gap-1.5 text-center text-xs" id="calendar-grid">
                        <!-- Les en-têtes des jours (Lun, Mar, etc.) et les jours du mois seront injectés en JS -->
                    </div>

                    <!-- Résumé des cotisations sélectionnées -->
                    <div class="bg-indigo-50 border border-indigo-150 p-3 rounded-xl flex items-center justify-between text-xs">
                        <div>
                            <span class="block font-bold text-indigo-900" id="selected-days-count">0 jour(s) sélectionné(s)</span>
                            <span class="text-[10px] text-indigo-500">Mise totale correspondante</span>
                        </div>
                        <span class="text-sm font-black text-indigo-900" id="selected-total-amount">0 FCFA</span>
                    </div>

                    <!-- Liste des inputs cachés générés dynamiquement pour soumettre les dates -->
                    <div id="hidden-dates-inputs"></div>

                    <div class="pt-2">
                        <button type="submit" id="btn-submit-contributions" disabled class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold text-sm transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            Confirmer l'Encaissement
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab 2: Create Client -->
        <div id="tab-client" class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6 hidden">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center">
                    <i data-lucide="user-plus" class="w-5 h-5 text-brand-600 mr-2"></i>
                    <span>Enregistrer un Client</span>
                </h2>
            </div>

            <form method="POST" action="{{ route('collector.clients.store') }}" class="space-y-4">
                @csrf
                <!-- Nom de famille -->
                <div>
                    <label for="new_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Nom de famille</label>
                    <input type="text" id="new_name" name="name" required value="{{ old('name') }}" {{ $stockDisponibleCount <= 0 ? 'disabled' : '' }} class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                </div>

                <!-- Prénom -->
                <div>
                    <label for="new_firstname" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Prénom</label>
                    <input type="text" id="new_firstname" name="firstname" required value="{{ old('firstname') }}" {{ $stockDisponibleCount <= 0 ? 'disabled' : '' }} class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                </div>

                <!-- Téléphone -->
                <div>
                    <label for="new_phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Téléphone</label>
                    <input type="text" id="new_phone" name="phone" required value="{{ old('phone') }}" {{ $stockDisponibleCount <= 0 ? 'disabled' : '' }} class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                </div>

                <!-- Sélection Carnet (Numéro) -->
                <div>
                    <label for="carnet_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Attribution Carnet</label>
                    <div class="space-y-1.5">
                        <input type="text" id="search_carnet" placeholder="🔍 Rechercher un article ou numéro..." class="block w-full px-3 py-2 bg-white border border-slate-350 rounded-xl text-slate-950 font-bold focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs shadow-xs">
                        <select id="carnet_number" name="carnet_number" required class="block w-full px-3 py-2.5 bg-white border border-slate-350 rounded-xl text-slate-950 font-bold focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm shadow-xs">
                            <option value="" disabled {{ (isset($suggestedCarnet) || old('carnet_number')) ? '' : 'selected' }}>-- Choisir un carnet --</option>
                            @isset($carnetsDisponibles)
                                @foreach($carnetsDisponibles as $carnet)
                                    <option value="{{ $carnet->number }}" 
                                        data-rate="{{ $carnet->lot->daily_rate ?? 0 }}"
                                        data-target="{{ $carnet->lot->final_price ?? 0 }}"
                                        data-article="{{ $carnet->lot->article_name ?? '' }}"
                                        {{ old('carnet_number') == $carnet->number || (!old('carnet_number') && isset($suggestedCarnet) && $suggestedCarnet->id === $carnet->id) ? 'selected' : '' }}>
                                        {{ $carnet->number }} - {{ $carnet->lot->article_name ?? 'Sans Article' }} ({{ number_format($carnet->lot->daily_rate ?? 0, 0, ',', ' ') }} F/jour, Jours: {{ $carnet->lot->days_covered ?? 100 }}, Objectif: {{ number_format($carnet->lot->final_price ?? 0, 0, ',', ' ') }} F)
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    @if(isset($carnetsDisponibles) && $carnetsDisponibles->isEmpty())
                        <p class="text-rose-500 text-[10px] mt-1">Aucun carnet disponible dans votre stock.</p>
                    @endif
                </div>

                <!-- Champs déduits automatiquement (En lecture seule) -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-150 space-y-3">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-500 flex items-center">
                        <i data-lucide="info" class="w-3.5 h-3.5 mr-1 text-slate-400"></i>
                        <span>Informations déduites du carnet choisi</span>
                    </h4>
                    
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Article Souhaité</label>
                            <input type="text" id="new_desired_article" name="desired_article" readonly required class="w-full bg-transparent font-bold text-slate-800 border-none p-0 focus:ring-0">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Mise journalière</label>
                            <div class="flex items-center">
                                <input type="number" id="new_daily_contribution" name="daily_contribution" readonly required class="w-16 bg-transparent font-bold text-slate-800 border-none p-0 focus:ring-0">
                                <span class="font-bold text-slate-700 ml-1">FCFA</span>
                            </div>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Montant total visé (Objectif)</label>
                            <div class="flex items-center">
                                <input type="number" id="new_target_amount" name="target_amount" readonly required class="w-24 bg-transparent font-bold text-slate-800 border-none p-0 focus:ring-0">
                                <span class="font-bold text-slate-700 ml-1">FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" {{ $stockDisponibleCount <= 0 ? 'disabled' : '' }} class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold text-sm transition-all shadow-lg disabled:bg-slate-200">
                        Créer le Client
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- COLONNE DROITE : Suivi -->
    <div class="lg:col-span-2 space-y-4">
        
        <!-- Relance Clients Inactifs -->
        @if($inactiveClients->count() > 0)
            <div class="bg-rose-50/60 border border-rose-200 rounded-3xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-rose-100 pb-2">
                    <h4 class="text-xs font-bold text-rose-800 flex items-center">
                        <i data-lucide="alert-triangle" class="w-4 h-4 mr-1.5 text-rose-600 animate-bounce"></i>
                        <span>Relance Clients Inactifs (Plus de 7 jours sans versement)</span>
                    </h4>
                    <span class="text-[10px] bg-rose-600 text-white font-bold px-2 py-0.5 rounded-full">{{ $inactiveClients->count() }} client(s)</span>
                </div>
                <div class="divide-y divide-rose-100 max-h-48 overflow-y-auto pr-1 dynamic-scrollbar">
                    @foreach($inactiveClients as $inactiveClient)
                        @php
                            $last = $inactiveClient->contributions->sortByDesc('payment_date')->first();
                            $rawDays = $last ? Carbon\Carbon::parse($last->payment_date)->diffInDays() : $inactiveClient->created_at->diffInDays();
                            $days = max(1, round($rawDays));
                        @endphp
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <p class="font-bold text-slate-800">{{ $inactiveClient->firstname }} {{ $inactiveClient->name }}</p>
                                <p class="text-[10px] text-slate-500">
                                    @if($last)
                                        Dernière cotisation : il y a {{ $days }} jour(s) ({{ Carbon\Carbon::parse($last->payment_date)->format('d/m/Y') }})
                                    @else
                                        Aucune cotisation enregistrée depuis l'inscription (il y a {{ $days }} jour(s))
                                    @endif
                                </p>
                            </div>
                            <a href="tel:{{ $inactiveClient->phone }}" class="flex items-center space-x-1 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold rounded-xl shadow-xs transition-colors">
                                <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                <span>Appeler</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">

            <!-- En-tête et Barre de recherche -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center">
                    <i data-lucide="users" class="w-5 h-5 text-indigo-600 mr-2"></i>
                    <span>Suivi de mon Portefeuille Clients</span>
                </h2>

                <!-- Formulaire de recherche -->
                <form action="{{ route('collector.dashboard') }}" method="GET" class="flex items-center gap-2">
                    <div class="relative flex-1 sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un client..."
                            class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 transition">

                        @if(request('search'))
                            <a href="{{ route('collector.dashboard') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-xs text-slate-400 hover:text-slate-600" title="Effacer">
                                ✕
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="px-3 py-2 text-xs bg-brand-600 hover:bg-brand-700 text-white rounded-xl transition font-bold shadow-sm">
                        Filtrer
                    </button>
                </form>
            </div>

            <div class="space-y-2">
                @foreach($clients as $client)
                    @if($client->target_amount > 0 && $client->totalPaid() >= $client->target_amount && $client->status !== 'delivered')
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between shadow-sm animate-pulse">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-emerald-100 text-emerald-700 rounded-xl"><i data-lucide="party-popper" class="w-5 h-5"></i></div>
                                <div>
                                    <p class="text-xs font-bold text-emerald-900">Objectif Atteint !</p>
                                    <p class="text-[11px] text-emerald-700">{{ $client->firstname }} {{ $client->name }} a fini son programme.</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-emerald-600 text-white px-2.5 py-1 rounded-lg font-bold">Prêt</span>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="space-y-3">
                @forelse($clients as $client)
                    @php
                        $total = $client->totalPaid();
                        $remaining = max(0, $client->target_amount - $total);
                        $pct = min(100, $client->target_amount > 0 ? ($total / $client->target_amount) * 100 : 0);
                    @endphp
                    <div class="bg-slate-50 hover:bg-slate-100/70 border border-slate-200/60 rounded-2xl p-4 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4 group">
                        
                        <!-- Client Avatar & Info -->
                        <div class="flex items-center space-x-3.5 cursor-pointer flex-1" onclick="openClientModal({{ json_encode($client) }}, {{ $client->totalPaid() }}, {{ $client->daysCovered() }}, '{{ $client->contributionTimeFormatted() }}')">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xs shadow-xs text-white shrink-0 {{ $pct >= 100 ? 'bg-gradient-to-tr from-emerald-500 to-teal-500' : 'bg-gradient-to-tr from-indigo-500 to-brand-600' }}">
                                {{ strtoupper(substr($client->firstname, 0, 1)) }}{{ strtoupper(substr($client->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 text-sm flex flex-wrap items-center gap-1.5">
                                    <span class="truncate">{{ $client->firstname }} {{ $client->name }}</span>
                                    @if(count($client->other_active_plans) > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-indigo-50 border border-indigo-200 text-indigo-700 shrink-0" title="Ce client a d'autres plans de cotisation actifs">
                                            <i data-lucide="layers" class="w-2.5 h-2.5 mr-0.5"></i> Multi
                                        </span>
                                    @endif
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400 opacity-60 group-hover:opacity-100 transition-opacity shrink-0"></i>
                                </div>
                                <div class="text-[10px] text-slate-500 font-semibold mt-0.5 truncate">
                                    {{ $client->desired_article }} <span class="text-slate-300 mx-1">|</span> Carnet {{ $client->activeCarnet ? $client->activeCarnet->number : 'N°?' }}
                                </div>
                            </div>
                        </div>

                        <!-- Stats & Progress Info -->
                        <div class="flex items-center justify-between sm:justify-end gap-6 sm:gap-8 border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0">
                            <!-- Metrics -->
                            <div class="flex items-center space-x-6 text-xs text-slate-700 shrink-0">
                                <div>
                                    <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">Cotisé</span>
                                    <span class="font-black text-slate-950">{{ number_format($total, 0, ',', ' ') }} F</span>
                                </div>
                                <div>
                                    <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">Reste</span>
                                    <span class="font-bold {{ $remaining > 0 ? 'text-slate-600' : 'text-emerald-600 font-extrabold' }}">{{ number_format($remaining, 0, ',', ' ') }} F</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-28 flex flex-col items-end shrink-0">
                                <div class="flex justify-between w-full text-[9px] font-bold text-slate-500 mb-1">
                                    <span>Objectif</span>
                                    <span class="{{ $pct >= 100 ? 'text-emerald-600' : 'text-brand-600' }}">{{ round($pct) }}%</span>
                                </div>
                                <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden shadow-inner">
                                    <div class="h-full rounded-full transition-all duration-300 {{ $pct >= 100 ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : 'bg-gradient-to-r from-brand-500 to-indigo-600' }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>

                            <!-- Actions Buttons -->
                            <div class="flex items-center space-x-1 shrink-0">
                                <button type="button" onclick="openClientModal({{ json_encode($client) }}, {{ $client->totalPaid() }}, {{ $client->daysCovered() }}, '{{ $client->contributionTimeFormatted() }}')" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition" title="Voir le carnet">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="openEditClientModal({{ json_encode($client) }})" class="p-2 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-xl transition" title="Modifier le client">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-xs italic bg-slate-50 rounded-2xl border border-slate-100">Aucun client trouvé.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- DETAILED CLIENT BOOKLET MODAL (COLLECTOR VIEW) -->
<div id="modal-client-details" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 transition-all">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-2xl w-full overflow-hidden mx-auto">
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="p-1.5 bg-brand-100 text-brand-700 rounded-lg">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Visuel du Carnet Client</h3>
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
                    <div id="modal-client-progress-bar" class="h-full rounded-full transition-all duration-500 bg-brand-500"></div>
                </div>
                <div class="flex justify-between text-xs font-bold text-slate-700">
                    <div>Encaissé : <span id="modal-client-paid" class="text-slate-950"></span> FCFA</div>
                    <div>Objectif Cible : <span id="modal-client-target" class="text-brand-600"></span> FCFA</div>
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

            <!-- Grille Informations Générales -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="border border-slate-100 p-3.5 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nom complet</span>
                    <div class="text-sm font-bold text-slate-900" id="modal-client-fullname"></div>
                </div>
                <div class="border border-slate-100 p-3.5 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Numéro de Téléphone</span>
                    <div class="text-sm font-bold text-slate-900" id="modal-client-phone"></div>
                </div>
                <div class="border border-slate-100 p-3.5 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Carnet Rattaché</span>
                    <div class="text-sm font-bold text-indigo-700" id="modal-client-carnet"></div>
                </div>
                <div class="border border-slate-100 p-3.5 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Durée de Cotisation</span>
                    <div class="text-sm font-bold text-slate-900" id="modal-client-duration"></div>
                </div>
            </div>

            <!-- NOUVEAU: Autres souscriptions associées (Multi-cotisations) -->
            <div id="modal-other-plans-section" class="hidden border border-indigo-150 rounded-xl p-4 bg-indigo-50/30 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 flex items-center">
                    <i data-lucide="layers" class="w-3.5 h-3.5 mr-1"></i>
                    <span>Autres cotisations actives du client</span>
                </h4>
                <div class="divide-y divide-indigo-100/50 text-xs" id="modal-other-plans-list">
                    <!-- Rempli en JS -->
                </div>
            </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
            <div>
                @if($stockDisponibleCount > 0)
                    <button type="button" onclick="startNewSubscriptionForModalClient()" class="inline-flex items-center space-x-1.5 px-3 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Nouvelle Cotisation / Carnet</span>
                    </button>
                @endif
            </div>
            <div class="flex space-x-2">
                <button type="button" onclick="startPaymentForModalClient()" class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    <span>Enregistrer Cotisation</span>
                </button>
                <button type="button" onclick="closeClientModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-xl transition shadow-sm">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .dynamic-scrollbar::-webkit-scrollbar { height: 5px; width: 5px; }
    .dynamic-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .dynamic-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
</style>
@endsection

@section('scripts')
<script>
    window.clientsData = @json($clients);
</script>
<script src="{{ asset('js/calendar.js') }}"></script>
<script src="{{ asset('js/collector-dashboard.js') }}"></script>
@endsection

<!-- MODAL: MODIFICATION DE CLIENT -->
<div id="modal-edit-client" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900 bg-opacity-40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 max-w-md w-full overflow-hidden mx-auto">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-base font-bold text-slate-950">Modifier les données du client</h3>
            <button onclick="document.getElementById('modal-edit-client').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-50 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-client-form" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Prénom</label>
                    <input type="text" name="firstname" id="edit_firstname" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nom</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Téléphone</label>
                <input type="text" name="phone" id="edit_phone" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Adresse Géographique</label>
                <input type="text" name="address" id="edit_address" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Mise Quotidienne (FCFA)</label>
                    <input type="number" name="daily_contribution" id="edit_daily_contribution" required min="1" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Objectif Épargne (FCFA)</label>
                    <input type="number" name="target_amount" id="edit_target_amount" required min="1" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Article souhaité</label>
                <input type="text" name="desired_article" id="edit_desired_article" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs">
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-edit-client').classList.add('hidden')" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-50 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-semibold hover:bg-indigo-700 transition">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
