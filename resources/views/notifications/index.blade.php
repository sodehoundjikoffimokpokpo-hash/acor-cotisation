@extends('layouts.app')

@section('title', 'Centre de Notifications')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Centre de Notifications</h1>
            <p class="text-slate-500 text-sm mt-1">
                Retrouvez l'historique complet de vos alertes internes. 
                @if(auth()->user()->unreadNotifications->count() > 0)
                    Vous avez <span class="font-bold text-brand-600">{{ auth()->user()->unreadNotifications->count() }} nouvelle(s) notification(s)</span>.
                @endif
            </p>
        </div>
        
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.markAsRead') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center space-x-2 py-2 px-4 bg-brand-50 hover:bg-brand-100 text-brand-700 rounded-xl font-semibold transition text-xs shadow-sm">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                    <span>Tout marquer comme lu</span>
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center space-x-2 shadow-sm">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Liste des Notifications -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <ul class="divide-y divide-slate-100">
            @forelse($notifications as $notification)
                @php
                    $type = $notification->data['type'] ?? 'info';
                    $isUnread = is_null($notification->read_at);
                @endphp
                <li class="p-5 flex items-start space-x-4 transition hover:bg-slate-50/50 @if($isUnread) bg-brand-50/20 @endif">
                    <!-- Badge Icone Dynamique -->
                    <div class="p-2.5 rounded-xl shrink-0 
                        @if($type === 'danger' || $type === 'error') bg-rose-50 text-rose-600 border border-rose-100
                        @elseif($type === 'warning') bg-amber-50 text-amber-600 border border-amber-100
                        @elseif($type === 'success') bg-emerald-50 text-emerald-600 border border-emerald-100
                        @else bg-blue-50 text-blue-600 border border-blue-100 @endif">
                        <i data-lucide="
                            @if($type === 'danger' || $type === 'error') alert-octagon
                            @elseif($type === 'warning') alert-triangle
                            @elseif($type === 'success') check-circle2
                            @else info @endif" class="w-5 h-5"></i>
                    </div>

                    <!-- Corps de la Notification -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <h3 class="text-sm font-bold text-slate-900 truncate flex items-center">
                                <span>{{ $notification->data['title'] ?? 'Notification' }}</span>
                                @if($isUnread)
                                    <span class="ml-2 w-1.5 h-1.5 rounded-full bg-brand-600" title="Non lue"></span>
                                @endif
                            </h3>
                            <span class="text-[10px] text-slate-400 font-medium ml-4 shrink-0">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
                    </div>
                </li>
            @empty
                <li class="p-10 text-center text-slate-400 space-y-2">
                    <i data-lucide="bell-off" class="w-10 h-10 text-slate-300 mx-auto"></i>
                    <p class="text-sm font-medium">Aucune alerte enregistrée pour le moment.</p>
                </li>
            @endforelse
        </ul>
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="pt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
