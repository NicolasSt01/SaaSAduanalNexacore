@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="max-w-lg mx-auto">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-10 text-center">
            <div class="h-20 w-20 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-3xl mx-auto mb-6 shadow-inner">
                <i class="fas fa-user-circle"></i>
            </div>

            <h2 class="text-2xl font-black text-gray-800 mb-3">¡Bienvenido, {{ Auth::user()->name }}!</h2>

            <p class="text-sm text-gray-500 font-medium mb-6">
                Nos alegra tenerte en la plataforma de seguimiento y control de operaciones.
                Aquí podrás consultar el estado de tus expedientes, documentos y notificaciones
                de manera rápida y segura.
            </p>

            @if(Auth::user()->isCliente() && Auth::user()->cliente)
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl mb-6">
                    <i class="fas fa-building text-emerald-500"></i>
                    <span class="text-sm font-bold text-emerald-700">{{ Auth::user()->cliente->nombre }}</span>
                </div>
            @endif

            <hr class="my-6 border-gray-100">

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('cliente.admindashboard') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all shadow-sm w-full sm:w-auto">
                    <i class="fas fa-tachometer-alt"></i> Panel Principal
                </a>
                <a href="{{ route('expedientes.indexcliente') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 border border-gray-300 bg-white text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm w-full sm:w-auto">
                    <i class="fas fa-folder-open"></i> Ver Expedientes
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
