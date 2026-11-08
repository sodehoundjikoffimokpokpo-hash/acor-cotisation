@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-10 px-4">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">Journal d'activité</h1>
        <p class="text-slate-500 mt-2">Suivi détaillé des actions effectuées dans le système.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="py-4 px-6 font-semibold text-slate-600">Action</th>
                    <th class="py-4 px-6 font-semibold text-slate-600">Modèle</th>
                    <th class="py-4 px-6 font-semibold text-slate-600">Utilisateur</th>
                    <th class="py-4 px-6 font-semibold text-slate-600">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($activities as $activity)
                <tr class="hover:bg-slate-50 transition-colors">
                    <!-- Colonne Action avec détails des modifications -->
                    <td class="py-4 px-6">
                        <div class="flex flex-col">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activity->description == 'created' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($activity->description) }}
                            </span>

                            @if($activity->description == 'updated' && isset($activity->properties['old']))
                                <div class="mt-2 text-xs text-slate-500">
                                    @foreach($activity->properties['old'] as $key => $oldValue)
                                        @if(isset($activity->properties['attributes'][$key]) && $activity->properties['attributes'][$key] != $oldValue)
                                            <p class="truncate max-w-xs">
                                                <span class="font-semibold text-slate-700">{{ ucfirst($key) }}</span>:
                                                <span class="line-through text-red-400">{{ $oldValue }}</span> →
                                                <span class="text-indigo-600 font-bold">{{ $activity->properties['attributes'][$key] }}</span>
                                            </p>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </td>

                    <td class="py-4 px-6 text-slate-700 font-medium">{{ class_basename($activity->subject_type) }}</td>

                    <td class="py-4 px-6 text-slate-700">
                        <span class="inline-flex items-center text-sm">
                            {{ $activity->causer ? $activity->causer->name : 'Système' }}
                        </span>
                    </td>

                    <td class="py-4 px-6 text-slate-500 text-sm">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">
        {{ $activities->links() }}
    </div>
</div>
@endsection
