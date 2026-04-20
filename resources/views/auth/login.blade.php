@extends('layouts.app')

@section('title', 'Iniciar Sesión | Control Operaciones Agencias Aduanales')

@section('customcss')
<style>
    /* Ocultar el navbar de la app en la página de login para un look centrado y enfocado */
    nav { display: none !important; }
    main { padding: 0 !important; }
    
    .login-bg {
        background-image: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .form-input {
        @apply block w-full px-4 py-3 text-gray-700 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200;
    }
</style>
@endsection

@section('content')
<div class="login-bg px-4">
    <div class="w-full max-w-md">
        {{-- Alertas de error --}}
        @if (session('error'))
            <div class="mb-6 animate-bounce">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-bold text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Card Principal --}}
        <div class="glass-card shadow-2xl rounded-3xl overflow-hidden animate-fade-in">
            <div class="p-8 sm:p-10">
                {{-- Header / Logo --}}
                <div class="flex flex-col items-center mb-8">
                    <div class="w-24 h-24 mb-6 relative group">
                        <div class="absolute inset-0 bg-indigo-500 rounded-2xl rotate-6 group-hover:rotate-12 transition duration-300 opacity-20"></div>
                        <img src="/login-logo.png" 
                             alt="Logo" 
                             class="w-full h-full object-cover rounded-2xl shadow-lg relative z-10">
                    </div>
                    <div class="text-center">
                        <h1 class="text-2xl font-black text-gray-900 tracking-tight leading-tight">
                            Control Operaciones
                        </h1>
                        <p class="text-sm font-bold text-indigo-600 uppercase tracking-widest mt-1">Agencias Aduanales</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-6">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-2 px-1">Correo electrónico</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition duration-200">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input type="email" name="email" id="email" 
                                   class="block w-full pl-10 pr-4 py-3 text-gray-700 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                                   placeholder="usuario@agencia.com" 
                                   required autofocus>
                        </div>
                    </div>

                    {{-- Contraseña --}}
                    <div>
                        <div class="flex items-center justify-between mb-2 px-1">
                            <label for="password" class="block text-sm font-bold text-gray-700">Contraseña</label>
                            {{-- Opcional: Link Recuperar --}}
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">¿Olvidaste tu contraseña?</a>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition duration-200">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" name="password" id="password" 
                                   class="block w-full pl-10 pr-4 py-3 text-gray-700 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                                   placeholder="••••••••" 
                                   required>
                        </div>
                    </div>

                    {{-- Remember me / Options --}}
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-600 font-medium">Recordarme siempre</label>
                    </div>

                    {{-- Botón Iniciar Sesión --}}
                    <div>
                        <button type="submit" class="w-full flex justify-center items-center py-4 px-6 text-white text-sm font-black bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-lg hover:shadow-indigo-500/30 transform hover:-translate-y-0.5 transition duration-200 group">
                            INICIAR SESIÓN
                            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            {{-- Footer del Card --}}
            <div class="p-6 bg-gray-50 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400 font-medium uppercase tracking-tighter">
                    © {{ date('Y') }} Sistema de Control Aduanal • v2.0
                </p>
            </div>
        </div>
        
        {{-- Ayuda / Footer externo --}}
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">¿Necesitas ayuda? <a href="#" class="text-indigo-600 font-bold hover:underline">Soporte Técnico</a></p>
        </div>
    </div>
</div>

@push('scripts')
<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.6s ease-out forwards;
    }
</style>
@endpush
@endsection
