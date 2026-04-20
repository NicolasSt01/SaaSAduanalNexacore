@extends('layouts.app')

@section('title', 'Registro - NexaCore Aduanal')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                <i class="fas fa-rocket text-indigo-600 text-2xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Prueba NexaCore Aduanal
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Regístrate para una prueba gratuita de 7 días
            </p>
        </div>

        <!-- Features del Trial -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
            <h3 class="text-sm font-bold text-emerald-800 mb-2">✅ Tu trial incluye:</h3>
            <ul class="text-sm text-emerald-700 space-y-1">
                <li>• 7 días de acceso completo</li>
                <li>• 20 modulaciones disponibles</li>
                <li>• 5 clientes para gestionar</li>
                <li>• SOIA-Bot en modo manual</li>
                <li>• Dashboard básico y notificaciones</li>
            </ul>
        </div>

        <!-- Formulario de Registro -->
        <form class="mt-8 space-y-6" action="{{ route('public.register') }}" method="POST">
            @csrf

            <div class="rounded-md shadow-sm space-y-4">
                <!-- Nombre Completo -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user text-indigo-500 mr-1"></i> Nombre Completo
                    </label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}"
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        placeholder="Tu nombre completo">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nombre de Empresa -->
                <div>
                    <label for="nombre_empresa" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-building text-indigo-500 mr-1"></i> Nombre de tu Empresa
                    </label>
                    <input id="nombre_empresa" name="nombre_empresa" type="text" required value="{{ old('nombre_empresa') }}"
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        placeholder="Ej: Agencia Aduanal del Norte">
                    @error('nombre_empresa')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Correo Electrónico -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-envelope text-indigo-500 mr-1"></i> Correo Electrónico
                    </label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}"
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        placeholder="tu@empresa.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-phone text-indigo-500 mr-1"></i> Teléfono (opcional)
                    </label>
                    <input id="telefono" name="telefono" type="text" value="{{ old('telefono') }}"
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        placeholder="+52 656 123 4567">
                </div>
            </div>

            <!-- Botón de Registro -->
            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    <i class="fas fa-user-plus mr-2"></i>
                    Crear Mi Cuenta de Prueba
                </button>
            </div>

            <!-- Link a Login -->
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Inicia sesión aquí
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
