@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-12 text-center max-w-lg mx-auto">
        <div class="h-16 w-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mx-auto mb-6 shadow-inner">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1 class="text-2xl font-black text-gray-800">¡Bienvenido a <span class="text-indigo-600">NexaCore</span>!</h1>
        <p class="text-sm text-gray-500 mt-3 font-medium">Has iniciado sesión correctamente.</p>
        @if (session('status'))
            <div class="mt-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm text-left">
                <div class="flex">
                    <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                    <div class="ml-3">
                        <p class="text-sm text-emerald-700 font-bold">{{ session('status') }}</p>
                    </div>
                </div>
            </div>
        @endif
        <p class="text-sm text-gray-400 mt-4">Usa la barra de navegación superior para acceder a los módulos.</p>
    </div>
</div>
@endsection
