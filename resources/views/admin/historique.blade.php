@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6 px-4">
    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Retour au Dashboard
    </a>
</div>
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-slate-900">Journal des opérations</h1>
            <p class="mt-2 text-sm text-slate-700">Historique complet de toutes les actions effectuées sur la plateforme.</p>
        </div>
    </div>

    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <table class="min-w-full divide-y divide-slate-300">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Date</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Utilisateur</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Action</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Modifications</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 align-top">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-slate-900 align-top">
                                    {{ $log->user->firstname ?? 'Système' }} {{ $log->user->name ?? '' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm align-top">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 text-xs font-semibold leading-5 text-slate-800">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-sm text-slate-600 align-top">
                                    @php
                                        $changes = \App\Helpers\AuditLogFormatter::diff($log->old_values, $log->new_values);
                                    @endphp

                                    @if(count($changes) > 0)
                                        <ul class="space-y-1">
                                            @foreach($changes as $change)
                                            <li>
                                                <span class="font-medium text-slate-800">{{ $change['label'] }}</span> :
                                                <span class="text-slate-400">{{ $change['old'] }}</span>
                                                <span class="mx-1">→</span>
                                                <span class="text-emerald-600 font-semibold">{{ $change['new'] }}</span>
                                            </li>
                                            @endforeach
                                        </ul>
                                    @elseif($log->description)
                                        <span class="text-slate-600 italic">{{ $log->description }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-10 text-slate-400">Aucun historique trouvé.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="px-4 py-3 border-t border-slate-200">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
