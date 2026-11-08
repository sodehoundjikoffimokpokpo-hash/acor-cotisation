<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950 text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Introuvable - ACOR</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
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
</head>
<body class="h-full flex items-center justify-center p-6 antialiased bg-radial-gradient">
    <div class="max-w-md w-full text-center space-y-8 bg-slate-900/60 border border-slate-800 p-8 rounded-3xl backdrop-blur-md shadow-2xl">
        <div class="flex justify-center">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-500 to-indigo-600 flex items-center justify-center text-white shadow-xl shadow-brand-500/20">
                <i data-lucide="help-circle" class="w-10 h-10 animate-bounce"></i>
            </div>
        </div>
        
        <div class="space-y-3">
            <h1 class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-indigo-400 font-mono">404</h1>
            <h2 class="text-xl font-bold text-slate-100">Page Introuvable</h2>
            <p class="text-sm text-slate-400 leading-relaxed">
                Le carnet, client ou l'action recherchée semble avoir été déplacé ou n'existe plus. Veuillez vérifier le lien.
            </p>
        </div>

        <div class="pt-4">
            <a href="/" class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/20 transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Retour au Tableau de Bord</span>
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
