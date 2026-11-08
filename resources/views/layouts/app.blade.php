<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50 text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ACOR-Cotisation') - Gestion Interne</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- AlpineJS & Tailwind CSS -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#f0f7ff', 100: '#e0effe', 200: '#bae0fd', 300: '#7cc8fc', 400: '#38aef9', 500: '#0e94ea', 600: '#0276c7', 700: '#035ea1', 800: '#075084', 900: '#0c436d', 950: '#082b49' }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- PWA settings -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0e94ea">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
</head>
<body class="h-full flex flex-col antialiased">

    <div class="min-h-full flex flex-col">
        <!-- Top Navbar -->
        <nav class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-brand-200">
                                <i data-lucide="wallet" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-brand-700 to-indigo-700 bg-clip-text text-transparent">ACOR-Cotisation</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Dropdown Filtre Pays (Admin uniquement) -->
                        @if(auth()->check() && auth()->user()->isAdmin() && !auth()->user()->country_id)
                            @php
                                $headerCountries = \App\Models\Country::all();
                            @endphp
                            <form action="{{ route('admin.set-country') }}" method="POST" id="global-form-select-country" class="hidden md:flex items-center space-x-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1">
                                @csrf
                                <i data-lucide="globe" class="w-3.5 h-3.5 text-slate-400"></i>
                                <select name="country_id" onchange="document.getElementById('global-form-select-country').submit()" class="text-[11px] bg-transparent border-none font-bold text-slate-700 focus:outline-none focus:ring-0 cursor-pointer p-0 pr-6">
                                    <option value="">Tous les pays</option>
                                    @foreach($headerCountries as $c)
                                        <option value="{{ $c->id }}" {{ session('active_country_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @endif

                        <!-- Notification Bell -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-2 text-slate-400 hover:text-brand-600 relative">
                                <i data-lucide="bell" class="w-5 h-5"></i>
                                @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                                @endif
                            </button>

                            <!-- Dropdown Notifications -->
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-100 py-2 z-50">
                                <div class="px-4 py-2 border-b border-slate-100 font-semibold text-sm flex justify-between items-center">
                                    <span>Notifications</span>
                                    @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                        <form action="{{ route('notifications.markAsRead') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-[10px] text-brand-600 hover:underline font-normal">Tout marquer comme lu</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    @forelse(auth()->user()->unreadNotifications as $notification)
                                        <div class="px-4 py-3 hover:bg-slate-50 border-b border-slate-50">
                                            <p class="text-xs font-bold text-slate-800">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                            <p class="text-[11px] text-slate-600 truncate">{{ $notification->data['message'] ?? '' }}</p>
                                        </div>
                                    @empty
                                        <p class="px-4 py-3 text-xs text-slate-400 text-center">Aucune nouvelle alerte</p>
                                    @endforelse
                                </div>
                                <a href="{{ route('notifications.index') }}" class="block text-center text-[10px] text-brand-600 py-2 hover:underline">Voir tout</a>
                            </div>
                        </div>

                        <!-- User Profile -->
                        <div class="flex items-center space-x-3 bg-slate-50 border border-slate-200 rounded-2xl py-1.5 px-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(Auth::user()->firstname ?? 'U', 0, 1)) }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-xs font-semibold text-slate-700 leading-3">{{ Auth::user()->firstname ?? '' }} {{ Auth::user()->name ?? 'Utilisateur' }}</p>
                                <p class="text-[10px] text-slate-500 capitalize font-medium mt-0.5">
                                    {{ Auth::user()->role === 'collector' ? 'Collecteur' : ucfirst(Auth::user()->role) }}
                                    @if(Auth::user()->country)
                                        <span class="text-brand-600 font-bold ml-0.5">({{ Auth::user()->country->name }})</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Logout Button -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                                <i data-lucide="log-out" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Workspace -->
        <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col">
            <main class="flex-1 flex flex-col">
                @yield('content')
            </main>
        </div>

        <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} ACOR-Cotisation. Tous droits réservés.</p>
            </div>
        </footer>
    </div>

    <script>
        lucide.createIcons();

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registered successfully:', reg.scope))
                    .catch(err => console.error('Service Worker registration failed:', err));
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
