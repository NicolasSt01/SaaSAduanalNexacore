@extends('layouts.app')

@section('title', 'Configuración del Tenant')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Centro de <span
                    class="text-indigo-600">Configuración</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Administra los catálogos y accesos operativos de tu
                agencia.</p>
        </div>
        <div class="flex items-center gap-3">
            <div
                class="bg-indigo-50 border border-indigo-100 px-4 py-2 rounded-xl flex items-center justify-center shadow-sm">
                <i class="fas fa-building text-indigo-500 mr-2"></i>
                <span class="text-sm font-bold text-indigo-700">{{ auth()->user()->tenant->nombre_empresa ?? 'Mi
                    Agencia' }}</span>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700 font-bold">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- EQUIPO DE TRABAJO -->
        @if(auth()->user()->hasPermiso('gestionar_usuarios'))
        <a href="{{ route('usuarios.index') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-indigo-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-indigo-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-users text-9xl"></i>
            </div>
            <div
                class="bg-indigo-100 text-indigo-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-user-shield"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-indigo-600 transition-colors">
                Gestión de Usuarios</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Administra cuentas, roles y accesos de tu
                equipo operativo (Máximo {{ auth()->user()->tenant->max_usuarios ?? 'N/A' }} usuarios).</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-indigo-600 text-xs mt-auto relative z-10 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif

        <!-- PATENTES Y ADUANAS -->
        @if(auth()->user()->hasPermiso('gestionar_patentes'))
        <a href="{{ route('patentes.index') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-blue-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-blue-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-stamp text-9xl"></i>
            </div>
            <div
                class="bg-blue-100 text-blue-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-id-badge"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-blue-600 transition-colors">Mis
                Patentes</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Registra y administra los números de
                patente aduanal con los que operas.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-blue-600 text-xs mt-auto relative z-10 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif

        @if(auth()->user()->hasPermiso('gestionar_aduanas'))
        <a href="{{ route('aduanas.index') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-sky-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-sky-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-building text-9xl"></i>
            </div>
            <div
                class="bg-sky-100 text-sky-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-sky-600 transition-colors">Aduanas
                Operativas</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Configura los puertos y aduanas por donde
                despachas mercancía (Secciones, claves).</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-sky-600 text-xs mt-auto relative z-10 group-hover:bg-sky-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif

        <!-- CLIENTES Y COMERCIAL -->
        @if(auth()->user()->hasPermiso('gestionar_clientes'))
        <a href="{{ route('clientes.index') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-emerald-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-emerald-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-handshake text-9xl"></i>
            </div>
            <div
                class="bg-emerald-100 text-emerald-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-briefcase"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-emerald-600 transition-colors">
                Directorio de Clientes</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Lista de empresas, RFCs y correos de tus
                clientes para asignarles operaciones.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-emerald-600 text-xs mt-auto relative z-10 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif

        @if(auth()->user()->hasPermiso('gestionar_clientes'))
        <a href="{{ route('directorio.index') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-fuchsia-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-fuchsia-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-address-book text-9xl"></i>
            </div>
            <div
                class="bg-fuchsia-100 text-fuchsia-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-bell"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-fuchsia-600 transition-colors">
                Directorio de Notificaciones</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Gestiona los contactos de tus clientes y sus preferencias de notificaciones automáticas.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-fuchsia-600 text-xs mt-auto relative z-10 group-hover:bg-fuchsia-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif

        @if(auth()->user()->hasPermiso('gestionar_importadores'))
        <a href="{{ route('importadores.index') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-teal-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-teal-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-truck-loading text-9xl"></i>
            </div>
            <div
                class="bg-teal-100 text-teal-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-globe-americas"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-teal-600 transition-colors">
                Importadores</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Catálogo de importadores registrados con
                su información fiscal.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-teal-600 text-xs mt-auto relative z-10 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif

        @if(auth()->user()->hasPermiso('gestionar_bodegas'))
        <a href="{{ route('bodegas.index') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-amber-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-amber-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-warehouse text-9xl"></i>
            </div>
            <div
                class="bg-amber-100 text-amber-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-boxes"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-amber-600 transition-colors">
                Bodegas</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Almacenes donde se guardan las mercancías
                posteriores al flujo aduanal.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-amber-600 text-xs mt-auto relative z-10 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif

        <!-- EXPEDIENTES / PEDIMENTOS -->
        @if(auth()->user()->hasPermiso('gestionar_pedimentos'))
        <a href="{{ route('expedientes.index') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-purple-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-purple-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-passport text-9xl"></i>
            </div>
            <div
                class="bg-purple-100 text-purple-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-purple-600 transition-colors">
                Catálogo de Pedimentos</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Base de expedientes / pedimentos
                asignados a clientes antes de la operación.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-purple-600 text-xs mt-auto relative z-10 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif

        <!-- FOLIOS Y REFERENCIAS -->
        @if(auth()->user()->hasPermiso('gestionar_referencias'))
        <a href="{{ route('admin.config.referencia') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-violet-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-violet-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-hashtag text-9xl"></i>
            </div>
            <div
                class="bg-violet-100 text-violet-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-signature"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-violet-600 transition-colors">
                Ajustes de Folio</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Personaliza el prefijo de tu empresa y el
                correlativo automático.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-violet-600 text-xs mt-auto relative z-10 group-hover:bg-violet-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif

        <!-- ANALÍTICAS Y METAS -->
        @if(auth()->user()->hasPermiso('gestionar_analiticas'))
        <a href="{{ route('admin.config.analiticas') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-rose-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-rose-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-chart-line text-9xl"></i>
            </div>
            <div
                class="bg-rose-100 text-rose-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-bullseye"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-rose-600 transition-colors">
                Analíticas y Metas</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Configura las metas operacionales
                diarias y mensuales para el dashboard de gerencia.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-rose-600 text-xs mt-auto relative z-10 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif
        
        <!-- PLANTILLAS DE CORREO -->
        <a href="{{ route('admin.config.plantillas') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-pink-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-pink-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-envelope-open-text text-9xl"></i>
            </div>
            <div
                class="bg-pink-100 text-pink-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-paint-roller"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-pink-600 transition-colors">
                Plantillas de Correo</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Personaliza el diseño de los correos automáticos que el SOIA-Bot envía a tus clientes.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-pink-600 text-xs mt-auto relative z-10 group-hover:bg-pink-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>

        <!-- SERVIDOR DE CORREOS SMTP -->
        <a href="{{ route('admin.config.smtp') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-orange-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-orange-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-server text-9xl"></i>
            </div>
            <div
                class="bg-orange-100 text-orange-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fas fa-mail-bulk"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-orange-600 transition-colors">
                Servidor de Correos (SMTP)</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Conecta tu propia cuenta de correo (Gmail, Outlook, HostGator) para que las notificaciones aduanales se envíen directamente desde tu buzón corporativo.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-orange-600 text-xs mt-auto relative z-10 group-hover:bg-orange-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>

        <!-- WHATSAPP -->
        @if(optional(auth()->user()->tenant)->hasFeature('whatsapp_notifications'))
        <a href="{{ route('admin.config.whatsapp') }}"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-green-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
            <div
                class="absolute -right-6 -top-6 text-green-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                <i class="fab fa-whatsapp text-9xl"></i>
            </div>
            <div
                class="bg-green-100 text-green-600 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
                <i class="fab fa-whatsapp"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 relative z-10 group-hover:text-green-600 transition-colors">
                WhatsApp</h3>
            <p class="text-sm text-gray-500 mt-2 relative z-10 mb-4 flex-grow">Conecta tu número de WhatsApp para que el SOIA-Bot notifique automáticamente a tus clientes cuando sus operaciones cambien de estatus.</p>
            <div
                class="w-full bg-gray-50 p-2 rounded-lg border border-gray-100 text-center font-bold text-green-600 text-xs mt-auto relative z-10 group-hover:bg-green-600 group-hover:text-white transition-colors">
                Ingresar <i class="fas fa-arrow-right ml-1"></i>
            </div>
        </a>
        @endif

    </div>
</div>
<!-- Tailwind CDN (si es necesario para recarga directa, omitelo si app ya lo incluye de build) -->
<script src="https://cdn.tailwindcss.com"></script>
@endsection