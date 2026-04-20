@extends('layouts.app')

@section('title', 'Registro Exitoso - NexaCore Aduanal')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-50 via-white to-indigo-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Icono de Éxito -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-check-circle text-emerald-600 text-4xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                ¡Registro Exitoso!
            </h2>
        </div>

        <!-- Mensaje Principal -->
        <div class="bg-white rounded-lg shadow-lg p-6 space-y-4">
            <div class="text-center">
                <p class="text-gray-700">
                    Hemos enviado un correo electrónico a tu dirección con:
                </p>
            </div>

            <ul class="space-y-3">
                <li class="flex items-start">
                    <i class="fas fa-key text-indigo-500 mt-1 mr-3"></i>
                    <div>
                        <p class="font-medium text-gray-900">Tu contraseña temporal</p>
                        <p class="text-sm text-gray-600">Contraseña aleatoria segura para tu primer acceso</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-link text-indigo-500 mt-1 mr-3"></i>
                    <div>
                        <p class="font-medium text-gray-900">Link de verificación</p>
                        <p class="text-sm text-gray-600">Para verificar tu correo electrónico</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-info-circle text-indigo-500 mt-1 mr-3"></i>
                    <div>
                        <p class="font-medium text-gray-900">Instrucciones de acceso</p>
                        <p class="text-sm text-gray-600">Guía paso a paso para comenzar</p>
                    </div>
                </li>
            </ul>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-4">
                <p class="text-sm text-amber-800">
                    <i class="fas fa-clock mr-1"></i>
                    <strong>Importante:</strong> El link de verificación expira en 24 horas.
                </p>
            </div>
        </div>

        <!-- Próximos Pasos -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">📋 Próximos Pasos:</h3>
            <ol class="space-y-3">
                <li class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold mr-3">1</span>
                    <p class="text-gray-700">Revisa tu bandeja de entrada (y spam)</p>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold mr-3">2</span>
                    <p class="text-gray-700">Haz click en el link de verificación</p>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold mr-3">3</span>
                    <p class="text-gray-700">Inicia sesión con tu contraseña temporal</p>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold mr-3">4</span>
                    <p class="text-gray-700">Cambia tu contraseña por una personalizada</p>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm font-bold mr-3">5</span>
                    <p class="text-gray-700 font-bold">¡Comienza a explorar NexaCore Aduanal!</p>
                </li>
            </ol>
        </div>

        <!-- Link a Login -->
        <div class="text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Ir al Login
            </a>
        </div>
    </div>
</div>
@endsection
