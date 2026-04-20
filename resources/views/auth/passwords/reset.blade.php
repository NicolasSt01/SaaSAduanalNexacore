@extends('layouts.app')

@section('title', 'Restablecer Contraseña - NexaCore Aduanal')

@section('content')
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-lock text-indigo-600 text-2xl"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Restablecer Contraseña
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Coloca tu nueva contraseña
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

            <!-- Formulario de Restablecimiento -->
            <form class="mt-8 space-y-6" action="{{ route('password.update') }}" method="POST">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="rounded-md shadow-sm space-y-4">
                    <!-- Correo Electrónico -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-envelope text-indigo-500 mr-1"></i> Correo Electrónico
                        </label>
                        <input id="email" name="email" type="email" required value="{{ $email ?? old('email') }}"
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="tu@empresa.com">
                    </div>

                    <!-- Nueva Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-key text-indigo-500 mr-1"></i> Nueva Contraseña
                        </label>
                        <input id="password" name="password" type="password" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Mínimo 8 caracteres">
                        <p class="mt-1 text-xs text-gray-500">
                            Debe tener al menos 8 caracteres
                        </p>
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-check-circle text-indigo-500 mr-1"></i> Confirmar Contraseña
                        </label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Repite tu nueva contraseña">
                    </div>
                </div>

                <!-- Requisitos de Contraseña -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-bold text-blue-800 mb-2">🔒 Tu nueva contraseña debe:</h4>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Tener al menos 8 caracteres</li>
                        <li>• Ser segura y fácil de recordar para ti</li>
                        <li>• Ser diferente a contraseñas anteriores</li>
                    </ul>
                </div>

                <!-- Botón de Restablecimiento -->
                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                        <i class="fas fa-save mr-2"></i>
                        Restablecer Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection