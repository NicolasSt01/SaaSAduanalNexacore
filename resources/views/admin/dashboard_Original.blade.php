@extends('layouts.app')

@section('title', 'Panel de Configuración')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 h-full flex flex-col bg-gray-50/50">
    
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Lado Izquierdo: Perfil y Título -->
        <div class="lg:w-1/3 flex flex-col gap-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex flex-col items-center text-center relative overflow-hidden">
                <!-- Decorative background elements -->
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

                <div class="relative z-10 w-24 h-24 mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4F46E5&color=fff&size=120"
                         class="rounded-2xl shadow-lg border-4 border-white object-cover w-full h-full transform transition hover:scale-105" alt="Avatar">
                    <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-green-500 rounded-full border-4 border-white flex items-center justify-center shadow-sm"></div>
                </div>
                
                <h2 class="text-2xl font-black text-gray-800 relative z-10">{{ Auth::user()->name }}</h2>
                <p class="text-gray-500 text-sm font-medium mb-4 relative z-10 block truncate max-w-full"><i class="fas fa-envelope mr-1 text-gray-400"></i> {{ Auth::user()->email }}</p>
                
                <span class="bg-indigo-50 text-indigo-700 font-bold px-4 py-1.5 rounded-full text-xs tracking-wider border border-indigo-100 shadow-sm relative z-10">
                    {{ strtoupper(Auth::user()->role) }}
                </span>
            </div>

            <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-3xl shadow-lg p-8 relative overflow-hidden">
                <div class="absolute -right-6 -top-6 text-white opacity-10">
                    <i class="fas fa-cog text-9xl"></i>
                </div>
                <div class="relative z-10">
                    <h1 class="text-3xl font-black text-white leading-tight mb-2">Centro de Configuraciones</h1>
                    <p class="text-indigo-100 text-sm font-medium">Administra los catálogos y accesos operativos de tu agencia desde aquí.</p>
                </div>
            </div>
        </div>

        <!-- Lado Derecho: Botones CRM -->
        <div class="lg:w-2/3">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-50">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-th-large text-indigo-500 mr-3"></i> Opciones de Configuración
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @php
                    $cards = [
                        ['route'=>'usuarios.index', 'icon'=>'fa-user-shield', 'label'=>'Usuarios', 'color'=>'text-indigo-600', 'bg'=>'bg-indigo-50'],
                        ['route'=>'patentes.index', 'icon'=>'fa-stamp', 'label'=>'Patentes', 'color'=>'text-blue-600', 'bg'=>'bg-blue-50'],
                        ['route'=>'aduanas.index', 'icon'=>'fa-building', 'label'=>'Aduanas', 'color'=>'text-sky-600', 'bg'=>'bg-sky-50'],
                        ['route'=>'clientes.index', 'icon'=>'fa-handshake', 'label'=>'Clientes', 'color'=>'text-emerald-600', 'bg'=>'bg-emerald-50'],
                        ['route'=>'expedientes.index', 'icon'=>'fa-folder-open', 'label'=>'Expedientes', 'color'=>'text-purple-600', 'bg'=>'bg-purple-50'],
                        ['route'=>'importadores.index', 'icon'=>'fa-globe-americas', 'label'=>'Importadores', 'color'=>'text-teal-600', 'bg'=>'bg-teal-50'],
                        ['route'=>'bodegas.index', 'icon'=>'fa-warehouse', 'label'=>'Bodegas', 'color'=>'text-amber-600', 'bg'=>'bg-amber-50'],
                        ['route'=>'operaciones.index', 'icon'=>'fa-chart-pie', 'label'=>'Op. Diaria', 'color'=>'text-rose-600', 'bg'=>'bg-rose-50'],
                        ['route'=>'operaciones.import.index', 'icon'=>'fa-file-excel', 'label'=>'Cuadrar (Excel)', 'color'=>'text-green-600', 'bg'=>'bg-green-50'],
                    ];
                    @endphp

                    @foreach($cards as $c)
                    <a href="{{ route($c['route']) }}" class="group block bg-white rounded-2xl border border-gray-100 hover:border-{{ explode('-', $c['bg'])[1] }}-300 p-5 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg relative overflow-hidden">
                        
                        <div class="flex flex-col items-center text-center relative z-10">
                            <div class="w-14 h-14 rounded-xl {{ $c['bg'] }} flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300 shadow-inner">
                                <i class="fas {{ $c['icon'] }} {{ $c['color'] }} text-2xl"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-700 group-hover:{{ $c['color'] }} transition-colors">{{ $c['label'] }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        
    </div>
</div>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
