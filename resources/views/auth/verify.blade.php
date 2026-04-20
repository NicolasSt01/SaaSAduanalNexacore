@extends('layouts.app')

@section('title', 'Verificar Correo | NexaCore Aduanal')

@section('customcss')
<style>
    nav { display: none !important; }
    main { padding: 0 !important; }
    
    .verify-bg {
        background-color: #ffffff;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .modern-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
        border-radius: 2.5rem;
        width: 100%;
        max-width: 32rem;
        padding: 3rem;
        text-align: center;
    }

    .icon-box {
        width: 5rem;
        height: 5rem;
        background: #f5f7ff;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        border-radius: 1.5rem;
        margin: 0 auto 2rem;
    }
</style>
@endsection

@section('content')
<div class="verify-bg">
    <div class="modern-card">
        <div class="icon-box animate-pulse">
            <i class="fas fa-paper-plane"></i>
        </div>
        
        <h1 class="text-3xl font-black text-gray-900 mb-4">{{ __('Verifica tu correo') }}</h1>
        
        <p class="text-gray-600 font-medium leading-relaxed mb-8">
            {{ __('Antes de continuar, por favor revisa tu bandeja de entrada para encontrar el enlace de verificación.') }}
        </p>

        @if (session('resent'))
            <div class="bg-green-50 border border-green-100 rounded-2xl p-4 mb-8 text-sm font-bold text-green-700">
                {{ __('Se ha enviado un nuevo enlace de verificación a tu dirección de correo.') }}
            </div>
        @endif

        <div class="space-y-4">
            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="w-full py-4 px-6 bg-indigo-600 text-white rounded-2xl font-black text-sm hover:bg-indigo-700 transition shadow-lg shadow-indigo-600/20 translate-y-0 hover:-translate-y-0.5">
                    {{ __('Solicitar otro enlace') }}
                </button>
            </form>

            <a href="{{ route('logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="inline-block text-xs font-black text-gray-400 hover:text-gray-600 uppercase tracking-widest mt-4">
                {{ __('Cerrar Sesión') }}
            </a>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </div>
</div>
@endsection
