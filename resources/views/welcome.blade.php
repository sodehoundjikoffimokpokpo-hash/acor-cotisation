<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACOR-Cotisation - Portail Interne</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc8fc',
                            400: '#38aef9',
                            500: '#0e94ea',
                            600: '#0276c7',
                            700: '#035ea1',
                            800: '#075084',
                            900: '#0c436d',
                            950: '#082b49',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-image: radial-gradient(circle at 10% 20%, rgba(15, 23, 42, 1) 0%, rgba(8, 47, 73, 1) 90%);
        }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between px-4 sm:px-6 lg:px-8 py-6">

    <!-- Top Header -->
    <header class="max-w-7xl w-full mx-auto flex justify-between items-center py-4 border-b border-slate-800/60">
        <div class="flex items-center space-x-2.5">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-brand-500/20">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-brand-400 to-indigo-400 bg-clip-text text-transparent">ACOR-Cotisation</span>
        </div>
        
        <div>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/60 rounded-xl text-xs font-bold transition-all">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Mon Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Se connecter</span>
                </a>
            @endauth
        </div>
    </header>

    <!-- Main Section -->
    <main class="max-w-4xl w-full mx-auto my-12 space-y-12">
        <div class="text-center space-y-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-500/10 border border-brand-500/20 text-brand-400">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 mr-2 animate-pulse"></span>
                Système Interne Hautement Sécurisé
            </span>
            <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-white leading-tight">
                Gestion des Cotisations Journalières &amp; Suivi Multi-Pays
            </h1>
            <p class="text-slate-400 text-sm sm:text-base max-w-2xl mx-auto">
                Portail officiel unifié d'ACOR. Accédez à vos portefeuilles clients, gérez les attributions de carnets et contrôlez la performance comptable globale par pays.
            </p>
        </div>

        <!-- Portals Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Admin Card -->
            <div class="bg-slate-900/40 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-xl flex flex-col justify-between hover:border-brand-500/30 transition-all duration-300 transform hover:scale-[1.02] group">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-400 flex items-center justify-center border border-brand-500/20">
                        <i data-lucide="shield" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white group-hover:text-brand-400 transition-colors">Espace Admin</h2>
                        <p class="text-slate-400 text-xs mt-2 leading-relaxed">
                            Contrôle général des pays, configuration des lots, audit complet de la traçabilité des actions et tableau de bord de Business Intelligence.
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="w-full mt-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/60 rounded-xl text-xs font-bold flex items-center justify-center space-x-1.5 transition-all">
                    <span>Accéder à l'Admin</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Secretary Card -->
            <div class="bg-slate-900/40 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-xl flex flex-col justify-between hover:border-indigo-500/30 transition-all duration-300 transform hover:scale-[1.02] group">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20">
                        <i data-lucide="users-round" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white group-hover:text-indigo-400 transition-colors">Espace Secrétariat</h2>
                        <p class="text-slate-400 text-xs mt-2 leading-relaxed">
                            Création de dossiers clients, attribution de carnets vierges aux collecteurs et gestion logistique de livraison des articles physiques.
                        </p>
                    </div>
                </div>
                <a href="{{ route('secretary.dashboard') }}" class="w-full mt-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/60 rounded-xl text-xs font-bold flex items-center justify-center space-x-1.5 transition-all">
                    <span>Accéder au Secrétariat</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Collector Card -->
            <div class="bg-slate-900/40 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-xl flex flex-col justify-between hover:border-emerald-500/30 transition-all duration-300 transform hover:scale-[1.02] group">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                        <i data-lucide="smartphone" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white group-hover:text-emerald-400 transition-colors">Espace Collecteur</h2>
                        <p class="text-slate-400 text-xs mt-2 leading-relaxed">
                            Calendrier interactif d'encaissement, suivi mobile du portefeuille client de terrain, relance des inactifs et clôture journalière de caisse.
                        </p>
                    </div>
                </div>
                <a href="{{ route('collector.dashboard') }}" class="w-full mt-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/60 rounded-xl text-xs font-bold flex items-center justify-center space-x-1.5 transition-all">
                    <span>Accéder au Collecteur</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="max-w-7xl w-full mx-auto text-center py-6 border-t border-slate-800/40 text-xs text-slate-500 space-y-1">
        <p>&copy; {{ date('Y') }} ACOR-Cotisation. Tous droits réservés.</p>
        <p>L'inscription en ligne est désactivée. Pour obtenir des accès, contactez votre administrateur pays.</p>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
