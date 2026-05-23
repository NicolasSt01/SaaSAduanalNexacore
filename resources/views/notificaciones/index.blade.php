@extends('layouts.app')

@section('title', 'Notificaciones')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-bell"></i>
                    </div>
                    Notificaciones
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Historial de tus notificaciones operativas.</p>
            </div>
        </div>

        @if($notificaciones->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 border border-gray-100 dark:border-gray-700 shadow-sm text-center">
                <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bell-slash text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-black text-gray-800 dark:text-white">Sin notificaciones</h3>
                <p class="text-sm text-gray-500 mt-1">No tienes notificaciones pendientes por el momento.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($notificaciones as $notif)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border {{ $notif->leida ? 'border-gray-100 dark:border-gray-700' : 'border-indigo-200 dark:border-indigo-800 bg-indigo-50/30 dark:bg-indigo-900/10' }} shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl {{ $notif->leida ? 'bg-gray-100 dark:bg-gray-700 text-gray-400' : 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400' }} flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $notif->tipo === 'modulacion_actualizada' ? 'fa-sync' : ($notif->tipo === 'documento_subido' ? 'fa-file-upload' : 'fa-bell') }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-sm font-black text-gray-800 dark:text-white">{{ $notif->titulo }}</h4>
                                @if(!$notif->leida)
                                    <span class="w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">{{ $notif->mensaje }}</p>
                            @if($notif->operacion)
                                <a href="{{ route('documentador.dashboard', ['op' => $notif->operacion_id]) }}" class="inline-block mt-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                    Ver operación <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                                </a>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $notificaciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
