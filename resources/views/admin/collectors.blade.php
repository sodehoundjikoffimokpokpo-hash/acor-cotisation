@extends('layouts.app')

@section('title', 'Gestion des Collecteurs')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Comptes Collecteurs</h1>
            <p class="text-slate-500 text-sm mt-1">Créez, modifiez et configurez les accès des agents de terrain.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2 py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition-all text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Retour au Dashboard</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- List of Collectors (Left / Large) -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 lg:col-span-2 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900">Liste des Collecteurs</h2>
                <p class="text-slate-400 text-xs">Total de {{ $collectors->count() }} agents enregistrés</p>
            </div>

            @php
                $collectorsGrouped = $collectors->groupBy(function($c) {
                    return $c->country->name ?? 'Non Assigné';
                });
            @endphp

            <div class="space-y-6">
                @forelse($collectorsGrouped as $countryName => $countryCollectors)
                    <div class="border border-slate-150 rounded-2xl p-4 bg-slate-50/50 space-y-3">
                        <h3 class="text-xs font-bold text-slate-800 flex items-center bg-white px-3 py-1.5 border border-slate-250 rounded-xl shadow-xs self-start w-fit">
                            <i data-lucide="globe" class="w-3.5 h-3.5 text-brand-600 mr-1.5"></i>
                            {{ $countryName }}
                        </h3>
                        <div class="overflow-x-auto bg-white border border-slate-200 rounded-xl p-3 shadow-xs">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead>
                                    <tr class="text-left text-slate-400 text-xs uppercase font-semibold">
                                        <th class="pb-3">Agent</th>
                                        <th class="pb-3">Identifiant unique</th>
                                        <th class="pb-3">Zone / Pays</th>
                                        <th class="pb-3">Statut</th>
                                        <th class="pb-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @foreach($countryCollectors as $collector)
                                        <tr>
                                            <td class="py-4">
                                                <div class="font-semibold text-slate-900">{{ $collector->firstname }} {{ $collector->name }}</div>
                                                <div class="text-[10px] text-slate-400 mt-0.5">Dernière connexion: {{ $collector->last_login_at ? $collector->last_login_at->diffForHumans() : 'Jamais connecté' }}</div>
                                            </td>
                                            <td class="py-4">
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-slate-100 text-slate-800 font-mono text-xs">
                                                    {{ $collector->username }}
                                                </span>
                                            </td>
                                            <td class="py-4">
                                                <div class="font-semibold text-slate-800">{{ $collector->activity_zone }}</div>
                                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $collector->activity_country }}</div>
                                            </td>
                                            <td class="py-4">
                                                @if($collector->is_active)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                        Actif
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                                        Suspendu
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-right space-x-2">
                                                <!-- Toggle Status Button -->
                                                <form method="POST" action="{{ route('admin.collectors.toggle', $collector->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all" 
                                                        title="{{ $collector->is_active ? 'Suspendre' : 'Réactiver' }}">
                                                        @if($collector->is_active)
                                                            <i data-lucide="user-x" class="w-4 h-4 text-red-500"></i>
                                                        @else
                                                            <i data-lucide="user-check" class="w-4 h-4 text-emerald-500"></i>
                                                        @endif
                                                    </button>
                                                </form>

                                                <!-- Edit Button (Loads data to form via JS) -->
                                                <button onclick="editCollector({{ json_encode($collector) }})" class="inline-flex items-center p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all" title="Modifier">
                                                    <i data-lucide="edit-3" class="w-4 h-4 text-brand-600"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-xs bg-slate-50 border border-slate-100 rounded-2xl italic">Aucun collecteur enregistré.</div>
                @endforelse
            </div>
        </div>

        <!-- Form (Right / Side) -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-6 self-start">
            <div class="border-b border-slate-100 pb-4">
                <h2 id="form-title" class="text-lg font-bold text-slate-900">Nouveau Collecteur</h2>
                <p id="form-subtitle" class="text-slate-400 text-xs">Créez un compte pour un nouvel agent.</p>
            </div>

            <form id="collector-form" method="POST" action="{{ route('admin.collectors.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" id="collector-id" name="collector_id">

                <!-- Nom -->
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Nom de famille</label>
                    <input type="text" id="name" name="name" required class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <!-- Prénom -->
                <div>
                    <label for="firstname" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Prénom</label>
                    <input type="text" id="firstname" name="firstname" required class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <!-- Username (Unique number) -->
                <div>
                    <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Identifiant Unique (Numéro)</label>
                    <input type="text" id="username" name="username" required class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Mot de passe</label>
                    <input type="password" id="password" name="password" class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    <p id="password-hint" class="text-[10px] text-slate-400 mt-1">Laissez vide si vous ne souhaitez pas modifier le mot de passe actuel.</p>
                </div>

                <!-- Activity Zone -->
                <div>
                    <label for="activity_zone" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Zone d'Activité</label>
                    <input type="text" id="activity_zone" name="activity_zone" required class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm" placeholder="Ex: Zone Nord / Commune 2">
                </div>

                <!-- Activity Country -->
                @if(Auth::user()->country_id)
                    <input type="hidden" id="activity_country" name="activity_country" value="{{ Auth::user()->activity_country ?? Auth::user()->country->name }}">
                @else
                    <div>
                        <label for="activity_country" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Pays d'Activité</label>
                        <input type="text" id="activity_country" name="activity_country" required class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm" placeholder="Ex: Togo / Bénin">
                    </div>
                @endif

                <!-- Submit & Cancel Buttons -->
                <div class="pt-2 flex space-x-2">
                    <button type="button" id="cancel-btn" onclick="resetForm()" class="hidden flex-1 py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition-all">
                        Annuler
                    </button>
                    <button type="submit" id="submit-btn" class="flex-1 py-2 px-4 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold text-xs transition-all shadow-md">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function editCollector(collector) {
        document.getElementById('form-title').innerText = "Modifier l'Agent";
        document.getElementById('form-subtitle').innerText = "Modifiez les informations de " + collector.firstname + ".";
        
        document.getElementById('collector-id').value = collector.id;
        document.getElementById('name').value = collector.name;
        document.getElementById('firstname').value = collector.firstname;
        
        // Disable editing of username to prevent mismatches, but let it stay visible
        const usernameInput = document.getElementById('username');
        usernameInput.value = collector.username;
        
        document.getElementById('activity_zone').value = collector.activity_zone;
        document.getElementById('activity_country').value = collector.activity_country;
        
        // Password is not required on edit
        document.getElementById('password').required = false;
        document.getElementById('password-hint').classList.remove('hidden');
        
        document.getElementById('cancel-btn').classList.remove('hidden');
        
        // Update form action for update route
        const form = document.getElementById('collector-form');
        form.action = "{{ url('admin/collectors') }}/" + collector.id;
    }

    function resetForm() {
        document.getElementById('form-title').innerText = "Nouveau Collecteur";
        document.getElementById('form-subtitle').innerText = "Créez un compte pour un nouvel agent.";
        
        document.getElementById('collector-id').value = "";
        document.getElementById('name').value = "";
        document.getElementById('firstname').value = "";
        
        const usernameInput = document.getElementById('username');
        usernameInput.value = "";
        
        document.getElementById('activity_zone').value = "";
        document.getElementById('activity_country').value = "";
        
        document.getElementById('password').required = true;
        document.getElementById('password').value = "";
        document.getElementById('password-hint').classList.add('hidden');
        
        document.getElementById('cancel-btn').classList.add('hidden');
        
        const form = document.getElementById('collector-form');
        form.action = "{{ route('admin.collectors.store') }}";
    }

    // Default form reset state
    window.onload = function() {
        resetForm();
    };
</script>
@endsection
