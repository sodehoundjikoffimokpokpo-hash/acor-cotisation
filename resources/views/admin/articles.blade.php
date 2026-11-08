@extends('layouts.app')

@section('title', 'Gestion des Articles')

@section('content')
<div class="space-y-8">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Gestion des Articles & Services</h1>
            <p class="text-slate-500 text-sm mt-1">Configurez et encadrez les prix de vente des articles proposés en épargne.</p>
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
        <!-- Colonne Gauche : Table des Articles (2/3) -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 lg:col-span-2 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900">Catalogue des Articles</h2>
                <p class="text-slate-400 text-xs">Consultez les articles et le détail de leur décomposition tarifaire.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead>
                        <tr class="text-left text-slate-400 text-xs uppercase font-semibold">
                            <th class="pb-3">Article</th>
                            <th class="pb-3 text-right">Détails de tarification (FCFA)</th>
                            <th class="pb-3 text-center">Stock</th>
                            <th class="pb-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($articles as $article)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4">
                                    <div class="font-bold text-slate-900">{{ $article->name }}</div>
                                    <div class="text-[10px] text-brand-600 font-extrabold mt-0.5">
                                        Objectif Client : {{ number_format($article->price, 0, ',', ' ') }} FCFA
                                    </div>
                                </td>
                                <td class="py-4 text-right text-xs space-y-0.5">
                                    <div class="text-[10px] text-slate-400 font-medium">
                                        Achat: <span class="font-semibold text-slate-700">{{ number_format($article->purchase_price, 0, ',', ' ') }} F</span> | 
                                        Trans: <span class="font-semibold text-slate-700">{{ number_format($article->transport_fees, 0, ',', ' ') }} F</span> | 
                                        Rech: <span class="font-semibold text-slate-700">{{ number_format($article->search_fees, 0, ',', ' ') }} F</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-medium">
                                        Comm Col: <span class="font-semibold text-slate-700">{{ number_format($article->collector_commission, 0, ',', ' ') }} F</span> | 
                                        Admin: <span class="font-semibold text-slate-700">{{ number_format($article->admin_fees, 0, ',', ' ') }} F</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-medium">
                                        Prov: <span class="font-semibold text-slate-700">{{ number_format($article->price_provision, 0, ',', ' ') }} F</span> | 
                                        Marge: <span class="font-semibold text-emerald-600">{{ number_format($article->acor_margin, 0, ',', ' ') }} F</span>
                                    </div>
                                </td>
                                <td class="py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black @if($article->stock_quantity > 0) bg-emerald-50 text-emerald-800 border border-emerald-100 @else bg-rose-50 text-rose-800 border border-rose-100 @endif">
                                        {{ $article->stock_quantity }}
                                    </span>
                                </td>
                                <td class="py-4 text-right">
                                    <form action="{{ route('admin.articles.updateStock', $article->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        <input type="number" name="quantity" class="w-16 px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-brand-500" placeholder="+ Qté" required min="1">
                                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-[10px] px-2.5 py-1.5 rounded-lg transition shadow-sm">
                                            Ajouter
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Aucun article enregistré dans le catalogue.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Colonne Droite : Formulaire d'ajout (1/3) -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 space-y-4 self-start">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 flex items-center">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-brand-600 mr-2"></i>
                    <span>Ajouter un Article</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">Ajouter un produit avec encadrement des prix obligatoires.</p>
            </div>

            <form action="{{ route('admin.articles.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nom de l'article</label>
                    <input type="text" name="name" required placeholder="Ex: Moto TVS 125cc" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Stock Initial</label>
                    <input type="number" name="stock_quantity" required min="1" value="10" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Composants de Prix -->
                <div class="space-y-2.5 pt-2.5 border-t border-slate-100">
                    <h3 class="text-xs font-bold text-slate-800">Décomposition de Prix (Formule ACOR)</h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Prix Achat Estimé</label>
                            <input type="number" id="purchase_price" name="purchase_price" min="0" value="0" oninput="calculateTotalPrice()" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Frais Transport</label>
                            <input type="number" id="transport_fees" name="transport_fees" min="0" value="0" oninput="calculateTotalPrice()" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Frais Recherche</label>
                            <input type="number" id="search_fees" name="search_fees" min="0" value="0" oninput="calculateTotalPrice()" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Comm. Collecteur</label>
                            <input type="number" id="collector_commission" name="collector_commission" min="0" value="0" oninput="calculateTotalPrice()" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Frais Admin</label>
                            <input type="number" id="admin_fees" name="admin_fees" min="0" value="0" oninput="calculateTotalPrice()" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Prov. hausse</label>
                            <input type="number" id="price_provision" name="price_provision" min="0" value="0" oninput="calculateTotalPrice()" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Marge ACOR</label>
                            <input type="number" id="acor_margin" name="acor_margin" min="0" value="0" oninput="calculateTotalPrice()" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Objectif Client (Calculé Automatiquement)</label>
                    <input type="number" id="price_final" name="price" readonly value="0" class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs text-brand-600 font-extrabold focus:outline-none cursor-not-allowed">
                </div>

                <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center space-x-1.5 mt-4">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Ajouter au Catalogue</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function calculateTotalPrice() {
        const p = parseFloat(document.getElementById('purchase_price').value) || 0;
        const t = parseFloat(document.getElementById('transport_fees').value) || 0;
        const s = parseFloat(document.getElementById('search_fees').value) || 0;
        const c = parseFloat(document.getElementById('collector_commission').value) || 0;
        const a = parseFloat(document.getElementById('admin_fees').value) || 0;
        const pr = parseFloat(document.getElementById('price_provision').value) || 0;
        const m = parseFloat(document.getElementById('acor_margin').value) || 0;

        document.getElementById('price_final').value = p + t + s + c + a + pr + m;
    }
</script>
@endsection
