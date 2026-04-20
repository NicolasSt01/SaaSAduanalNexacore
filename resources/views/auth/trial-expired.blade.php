@extends('layouts.app')

@section('title', 'Trial Expirado - NexaCore Aduanal')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 via-white to-orange-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Icono de Expiración -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fas fa-clock text-red-600 text-4xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Tu Período de Prueba Ha Expirado
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Los 7 días de prueba han terminado
            </p>
        </div>

        <!-- Mensaje Principal -->
        <div class="bg-white rounded-lg shadow-lg p-6 space-y-4">
            <div class="text-center">
                <p class="text-gray-700">
                    Esperamos que hayas disfrutado tu experiencia con <strong>NexaCore Aduanal</strong>.
                </p>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-sm text-red-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Tu cuenta y datos siguen disponibles. Solo necesitas actualizar tu plan para continuar usando la plataforma.
                </p>
            </div>
        </div>

        <!-- Planes Disponibles -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">💎 Planes Disponibles:</h3>
            
            <div class="space-y-4">
                <!-- Plan Básico -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-gray-900">Básico</h4>
                            <p class="text-sm text-gray-600">50 consultas/mes, 10 clientes</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-indigo-600">$499</p>
                            <p class="text-xs text-gray-500">MXN/mes</p>
                        </div>
                    </div>
                </div>

                <!-- Plan Profesional -->
                <div class="border-2 border-indigo-500 rounded-lg p-4 bg-indigo-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-gray-900">Profesional ⭐</h4>
                            <p class="text-sm text-gray-600">200 consultas/mes, 50 clientes</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-indigo-600">$999</p>
                            <p class="text-xs text-gray-500">MXN/mes</p>
                        </div>
                    </div>
                </div>

                <!-- Plan Enterprise -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-gray-900">Enterprise</h4>
                            <p class="text-sm text-gray-600">Ilimitado, todo incluido</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-indigo-600">$1,999</p>
                            <p class="text-xs text-gray-500">MXN/mes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="space-y-3">
            <a href="#" class="block w-full text-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition">
                <i class="fas fa-credit-card mr-2"></i>
                Actualizar Mi Plan
            </a>
            
            <a href="mailto:soporte@nexacore.com" class="block w-full text-center px-6 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                <i class="fas fa-envelope mr-2"></i>
                Contactar a Ventas
            </a>
        </div>

        <!-- Link a Login -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                ¿Necesitas más información?
                <a href="mailto:soporte@nexacore.com" class="font-medium text-indigo-600 hover:text-indigo-500">
                    soporte@nexacore.com
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
