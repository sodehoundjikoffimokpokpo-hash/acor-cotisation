@extends('layouts.app') {{-- Ajuste 'layouts.admin' si ton layout s'appelle différemment --}}

@section('content')

<div class="max-w-6xl mx-auto py-6 px-4">
    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Retour au Dashboard
    </a>
</div>
<div class="max-w-6xl mx-auto py-10 px-4">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">Historique des Sauvegardes</h1>
        <p class="text-slate-500 mt-2">Accès rapide aux fichiers de sécurité du système.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="py-4 px-6 font-semibold text-slate-600">Nom du fichier</th>
                    <th class="py-4 px-6 font-semibold text-slate-600">Taille</th>
                    <th class="py-4 px-6 font-semibold text-slate-600 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($files as $file)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-6 text-slate-700 font-medium">{{ basename($file) }}</td>
                    <td class="py-4 px-6 text-slate-500">{{ round(Storage::disk('local')->size($file) / 1024, 2) }} KB</td>
                    <td class="py-4 px-6 text-right">
                        <a href="{{ route('admin.backups.download', ['file' => $file]) }}"
                           class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-all shadow-sm">
                           Télécharger
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-12 text-center text-slate-400">Aucune sauvegarde trouvée pour le moment.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
