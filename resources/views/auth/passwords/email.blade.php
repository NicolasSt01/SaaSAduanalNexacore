@extends('layouts.app')

@section('title', 'Recuperar Contraseña - NexaCore Aduanal')

@section('content')
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-key text-indigo-600 text-2xl"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    ¿Olvidaste tu Contraseña?
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    No te preocupes, te enviaremos un link para restablecerla
                </p>
            </div>

            <!-- Mensaje de Éxito -->
            @if (session('status'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-emerald-500 mr-3"></i>
                        <p class="text-sm text-emerald-700 font-bold">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            <!-- Mensaje de Error -->
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-sm text-red-700 font-bold">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            <!-- Formulario de Recuperación -->
            <form class="mt-8 space-y-6" action="{{ route('password.email') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-envelope text-indigo-500 mr-1"></i> Correo Electrónico
                    </label>
                    <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        placeholder="tu@empresa.com">
                </div>

                <!-- Instrucciones -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Ingresa el correo electrónico asociado a tu cuenta y te enviaremos un link para restablecer tu
                        contraseña.
                    </p>
                </div>

                <!-- Botón de Envío -->
                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Link de Recuperación
                    </button>
                </div>

                <!-- Link a Login -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        ¿Recordaste tu contraseña?
                        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                            Inicia sesión aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
@endsection