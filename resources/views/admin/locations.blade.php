@extends('layouts.app')

@section('title', 'Gestion Multi-Pays, Régions & Zones')

@section('content')
<div class="space-y-8">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Découpage Géographique</h1>
            <p class="text-slate-500 text-sm mt-1">Configurez les pays, régions administratifs, et zones de terrain de l'application ACOR.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2 py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition-all text-sm shadow-sm">
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
        <!-- Colonne Droite : Formulaires de configuration (1/3) -->
        <div class="space-y-6 lg:col-span-1">
            @if(!Auth::user()->country_id)
                <!-- 1. Ajouter un Pays -->
                <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center">
                            <i data-lucide="globe" class="w-4 h-4 text-brand-600 mr-2"></i>
                            <span>Créer un Pays</span>
                        </h3>
                    </div>
                    <form action="{{ route('admin.countries.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nom du Pays</label>
                            <input type="text" name="name" required placeholder="Ex: Togo" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Devise (Code)</label>
                            <input type="text" name="currency_code" required value="FCFA" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <button type="submit" class="w-full py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                            Ajouter le Pays
                        </button>
                    </form>
                </div>
            @endif

            <!-- 2. Ajouter une Région -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center">
                        <i data-lucide="map" class="w-4 h-4 text-indigo-600 mr-2"></i>
                        <span>Créer une Région</span>
                    </h3>
                </div>
                <form action="{{ route('admin.regions.store') }}" method="POST" class="space-y-3">
                    @csrf
                    @if(Auth::user()->country_id)
                        <input type="hidden" name="country_id" value="{{ Auth::user()->country_id }}">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 flex justify-between items-center shadow-xs">
                            <span class="text-slate-400 font-bold">Rattaché à :</span>
                            <span class="text-slate-900 font-extrabold">{{ Auth::user()->country->name ?? 'Mon Pays' }}</span>
                        </div>
                    @else
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Rattacher au Pays</label>
                            <select name="country_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="" disabled selected>-- Sélectionner le pays --</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nom de la Région</label>
                        <input type="text" name="name" required placeholder="Ex: Région Maritime" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                        Ajouter la Région
                    </button>
                </form>
            </div>

            <!-- 3. Ajouter une Zone -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center">
                        <i data-lucide="navigation" class="w-4 h-4 text-emerald-600 mr-2"></i>
                        <span>Créer une Zone de terrain</span>
                    </h3>
                </div>
                <form action="{{ route('admin.zones.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Rattacher à la Région</label>
                        <select name="region_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="" disabled selected>-- Sélectionner la région --</option>
                            @foreach($countries as $country)
                                @if(!Auth::user()->country_id || Auth::user()->country_id == $country->id)
                                    @if(!Auth::user()->country_id)
                                        <optgroup label="{{ $country->name }}">
                                    @endif
                                    @foreach($country->regions as $region)
                                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                                    @endforeach
                                    @if(!Auth::user()->country_id)
                                        </optgroup>
                                    @endif
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nom de la Zone</label>
                        <input type="text" name="name" required placeholder="Ex: Lomé Adidogomé" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                        Ajouter la Zone
                    </button>
                </form>
            </div>
        </div>

        <!-- Colonne Gauche : Liste hiérarchisée des entités (2/3) -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 lg:col-span-2 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900">Structure Géographique Actuelle</h2>
                <p class="text-slate-400 text-xs">Arborescence hiérarchique Pays > Régions > Zones de terrain.</p>
            </div>

            <div class="space-y-6">
                @forelse($countries as $country)
                    <div class="border border-slate-150 rounded-2xl p-4 bg-slate-50/40">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-sm font-black text-slate-900 flex items-center">
                                <i data-lucide="globe" class="w-4 h-4 text-brand-600 mr-2"></i>
                                <span>{{ $country->name }}</span>
                            </h4>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-brand-50 text-brand-700 px-2.5 py-0.5 rounded-full border border-brand-100">
                                Devise : {{ $country->currency_code }}
                            </span>
                        </div>

                        <div class="ml-6 space-y-4">
                            @forelse($country->regions as $region)
                                <div class="bg-white p-3 border border-slate-200 rounded-xl space-y-2">
                                    <h5 class="text-xs font-bold text-slate-800 flex items-center">
                                        <i data-lucide="map" class="w-3.5 h-3.5 text-indigo-500 mr-1.5"></i>
                                        <span>{{ $region->name }}</span>
                                    </h5>
                                    
                                    <div class="ml-5 flex flex-wrap gap-2">
                                        @forelse($region->zones as $zone)
                                            <span class="inline-flex items-center text-[10px] font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">
                                                <i data-lucide="navigation" class="w-2.5 h-2.5 text-emerald-500 mr-1"></i>
                                                {{ $zone->name }}
                                            </span>
                                        @empty
                                            <span class="text-[10px] text-slate-400 italic">Aucune zone dans cette région</span>
                                        @endforelse
                                    </div>
                                </div>
                            @empty
                                <p class="text-[11px] text-slate-400 italic">Aucune région enregistrée pour ce pays.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="text-center py-8 text-slate-400 text-sm italic">Aucun pays configuré pour le moment.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
