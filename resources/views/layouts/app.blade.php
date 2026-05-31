<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'NexaCore SaaS'))</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Alpine Config if needed later, pero por ahora solo Tailwind
        // Dejamos preflight encendido porque así lo pidió el cliente
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" disabled>
    <!-- Lo dejamos comentado o deshabilitado si ya trabajaremos 100% con tailwind -->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- 🔔 ESTILOS DE NOTIFICACIONES --}}
    <style>
        /* Campana de notificaciones */
        .notification-bell {
            position: relative;
            cursor: pointer;
            font-size: 1.25rem;
            color: #6c757d;
            transition: color 0.2s;
        }

        .notification-bell:hover {
            color: #0d6efd;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.65rem;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        /* Dropdown de notificaciones */
        .notification-dropdown {
            width: 380px;
            max-height: 500px;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 8px;
        }

        .notification-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 8px 8px 0 0;
            font-weight: 600;
        }

        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s;
            cursor: pointer;
            display: flex;
            gap: 12px;
            align-items: start;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item.unread {
            background-color: #e7f3ff;
        }

        .notification-item.unread:hover {
            background-color: #d0e9ff;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .notification-icon.success {
            background-color: #d1f4e0;
            color: #28a745;
        }

        .notification-icon.info {
            background-color: #cfe2ff;
            color: #0d6efd;
        }

        .notification-icon.warning {
            background-color: #fff3cd;
            color: #ffc107;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 4px;
            color: #212529;
        }

        .notification-message {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #adb5bd;
        }

        .notification-footer {
            padding: 10px 16px;
            background-color: #f8f9fa;
            border-radius: 0 0 8px 8px;
            text-align: center;
        }

        .notification-footer button {
            font-size: 0.85rem;
            padding: 4px 12px;
        }

        .empty-notifications {
            padding: 40px 20px;
            text-align: center;
            color: #6c757d;
        }

        .empty-notifications i {
            font-size: 3rem;
            margin-bottom: 12px;
            opacity: 0.3;
        }

        /* Toasts personalizados */
        .toast-container-custom {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }

        .toast-custom {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 12px;
            padding: 16px;
            display: flex;
            gap: 12px;
            align-items: start;
            animation: slideIn 0.3s ease-out;
            cursor: pointer;
            border-left: 4px solid #0d6efd;
        }

        .toast-custom.success {
            border-left-color: #28a745;
        }

        .toast-custom.info {
            border-left-color: #0d6efd;
        }

        .toast-custom.warning {
            border-left-color: #ffc107;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .toast-custom.hiding {
            animation: slideOut 0.3s ease-out forwards;
        }

        .toast-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .toast-icon.success {
            background-color: #d1f4e0;
            color: #28a745;
        }

        .toast-icon.info {
            background-color: #cfe2ff;
            color: #0d6efd;
        }

        .toast-icon.warning {
            background-color: #fff3cd;
            color: #ffc107;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }

        .toast-message {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .toast-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .toast-actions button {
            padding: 4px 12px;
            font-size: 0.8rem;
            border-radius: 4px;
        }

        .toast-close {
            cursor: pointer;
            color: #6c757d;
            font-size: 1.2rem;
            line-height: 1;
            opacity: 0.5;
            transition: opacity 0.2s;
        }

        .toast-close:hover {
            opacity: 1;
        }
    </style>

    @yield('customcss')

</head>

<body>
    <div id="app">
        <nav class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ url('/') }}" class="text-2xl font-black tracking-tighter">
                                <span class="text-indigo-600">Nexa</span><span class="text-gray-500">Core</span>
                            </a>
                        </div>

                        <!-- Navigation Links (Desktop) -->
                        <div
                            class="hidden sm:-my-px sm:ml-8 sm:flex sm:space-x-4 overflow-x-auto no-scrollbar items-center">
                            @auth
                                <!--@if(in_array(auth()->user()->role, ['admin']) && in_array(auth()->user()->name, ['Admin CrossPoint', 'Ricardo Rodriguez', 'Alejandro']))
                                                <a href="{{ route('admin.config') }}"
                                                    class="inline-flex items-center px-2 pt-1 border-b-2 {{ request()->is('admin/config*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition">Configuración</a>
                                            @endif-->
                                @if(in_array(auth()->user()->role, ['admin']))
                                    <a href="{{ route('admin.admindashboard') }}"
                                        class="inline-flex items-center px-2 pt-1 border-b-2 {{ request()->is('admin.admindashboard*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition">Gerencia</a>
                                @endif

                                @if(in_array(strtolower(auth()->user()->role), ['admin', 'documentador']))
                                    <a href="{{ route('documentador.dashboard') }}"
                                        class="inline-flex items-center px-2 pt-1 border-b-2 {{ request()->is('documentador*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition">Gestión
                                        Aduanal</a>
                                @endif

                                @if(in_array(auth()->user()->role, ['admin']))
                                    <a href="{{ route('reportes.index') }}"
                                        class="inline-flex items-center px-2 pt-1 border-b-2 {{ request()->is('reportes*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition">Reportes</a>

                                    <a href="{{ route('expedientes.index') }}"
                                        class="inline-flex items-center px-2 pt-1 border-b-2 {{ request()->is('expedientes*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition">Firmas</a>
                                @endif

                                @if(in_array(auth()->user()->role, ['Cliente', 'ClienteAdmin']))
                                    <a href="{{ route('cliente.admindashboard') }}"
                                        class="inline-flex items-center px-2 pt-1 border-b-2 {{ request()->is('cliente/dashboard*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition">Cliente</a>
                                    <a href="{{ route('expedientes.indexcliente') }}"
                                        class="inline-flex items-center px-2 pt-1 border-b-2 {{ request()->is('cliente/expedientes*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition">Expedientes</a>
                                @endif

                                @if(in_array(auth()->user()->role, ['admin', 'finanzas']))
                                    <a href="{{ route('finanzas.index') }}"
                                        class="inline-flex items-center px-2 pt-1 border-b-2 {{ request()->is('finanzas*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition">Finanzas</a>
                                @endif

                                @if(auth()->user()->hasAnyConfigPermiso())
                                    <a href="{{ route('admin.config') }}"
                                        class="inline-flex items-center px-2 pt-1 border-b-2 {{ request()->is('admin/config*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition">Configuraciones</a>
                                @endif

                                @if(auth()->check() && auth()->user()->isSuperAdmin())
                                    <a href="{{ route('admin.tenants.index') }}"
                                        class="inline-flex items-center px-2 pt-1 border-b-2 {{ request()->is('superadmin*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition text-purple-600 pb-1"><i
                                            class="fas fa-crown mr-1"></i> Panel NexaCore</a>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6 gap-4">
                        @guest
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}"
                                    class="text-gray-500 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-bold transition">Ingresar</a>
                            @endif
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">Registro</a>
                            @endif
                        @else
                            {{-- Notificaciones --}}
                            @if(in_array(auth()->user()->role, ['Trafico', 'Documentador', 'finanzas']))
                                <div class="relative">
                                    <button type="button"
                                        onclick="document.getElementById('notificationMenu').classList.toggle('hidden')"
                                        class="p-2 rounded-full text-gray-400 hover:text-indigo-600 focus:outline-none transition relative">
                                        <i class="fas fa-bell text-xl"></i>
                                        <span
                                            class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full animate-pulse"
                                            id="notificationBadge" style="display: none;">0</span>
                                    </button>
                                    <!-- Notification Dropdown -->
                                    <div id="notificationMenu"
                                        class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-t-xl px-4 py-3">
                                            <p class="text-sm font-bold text-white"><i
                                                    class="fas fa-bell mr-2"></i>Notificaciones</p>
                                        </div>
                                        <div id="notificationList" class="max-h-80 overflow-y-auto w-full">
                                            <div class="p-6 text-center text-gray-400">
                                                <i class="fas fa-bell-slash text-3xl mb-2"></i>
                                                <p class="text-sm">No hay notificaciones</p>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-2 border-t border-gray-100 rounded-b-xl text-center">
                                            <button id="markAllAsRead"
                                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800"><i
                                                    class="fas fa-check-double mr-1"></i>Marcar todas como leídas</button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- 🔔 Notificaciones del Sistema --}}
                            @auth
                                <div class="relative ml-2" id="notificacionesContainer">
                                    <button onclick="toggleNotificaciones()"
                                        class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none">
                                        <i class="fas fa-bell text-xl"></i>
                                        <span id="notificacionesBadge"
                                            class="absolute -top-1 -right-1 block h-5 w-5 rounded-full ring-2 ring-white bg-red-500 text-white text-xs font-bold text-center hidden">
                                            0
                                        </span>
                                    </button>

                                    <!-- Dropdown de Notificaciones -->
                                    <div id="notificacionesDropdown"
                                        class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 ring-1 ring-black ring-opacity-5 z-50">
                                        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                                            <h3 class="text-sm font-bold text-gray-800">Notificaciones</h3>
                                            <button onclick="marcarTodasLeidas()"
                                                class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">
                                                Marcar todas
                                            </button>
                                        </div>
                                        <div id="notificacionesList" class="max-h-96 overflow-y-auto">
                                            <!-- Se llena dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            @endauth

                            {{-- User Dropdown --}}
                            <div class="relative ml-2">
                                <button type="button"
                                    onclick="document.getElementById('userMenu').classList.toggle('hidden')"
                                    class="flex items-center gap-2 text-sm font-bold text-gray-700 hover:text-indigo-600 focus:outline-none transition">
                                    <div
                                        class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span>{{ Auth::user()->name }}</span>
                                    <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                                </button>
                                <!-- Profile Dropdown -->
                                <div id="userMenu"
                                    class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 ring-1 ring-black ring-opacity-5 focus:outline-none z-50 py-1">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->email }}</p>
                                        <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">
                                            {{ Auth::user()->role }}
                                        </p>
                                    </div>
                                    <a href="#"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition"><i
                                            class="fas fa-user-circle mr-2"></i>Mi Perfil</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="block w-full text-left px-4 py-2 text-sm text-red-600 font-bold hover:bg-red-50 transition"><i
                                                class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión</button>
                                    </form>
                                </div>
                            </div>
                        @endguest
                    </div>

                    <!-- Hamburger button -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button type="button" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none transition">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div class="hidden sm:hidden bg-white border-t border-gray-100" id="mobileMenu">
                <div class="pt-2 pb-3 space-y-1 px-2">
                    @auth
                        @if(in_array(auth()->user()->role, ['admin']) && in_array(auth()->user()->name, ['Admin CrossPoint', 'Ricardo Rodriguez', 'Alejandro']))
                            <a href="{{ route('admin.config') }}"
                                class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">Configuración</a>
                        @endif
                        @if(in_array(strtolower(auth()->user()->role), ['admin', 'documentador']))
                            <a href="{{ route('documentador.dashboard') }}"
                                class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">Gestión
                                Aduanal</a>
                        @endif
                        @if(in_array(auth()->user()->role, ['admin']))
                            <a href="{{ route('reportes.index') }}"
                                class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">Reportes</a>
                            <a href="{{ route('expedientes.index') }}"
                                class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">Firmas</a>
                        @endif
                        @if(in_array(auth()->user()->role, ['Cliente', 'ClienteAdmin']))
                            <a href="{{ route('cliente.admindashboard') }}"
                                class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">Cliente</a>
                            <a href="{{ route('expedientes.indexcliente') }}"
                                class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">Expedientes</a>
                        @endif
                        @if(in_array(auth()->user()->role, ['admin', 'finanzas']))
                            <a href="{{ route('finanzas.index') }}"
                                class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">Finanzas</a>
                        @endif
                        @if(auth()->user()->hasAnyConfigPermiso())
                            <a href="{{ route('admin.config') }}"
                                class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">Configuraciones</a>
                        @endif
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.tenants.index') }}"
                                class="block px-3 py-2 rounded-md text-base font-bold text-purple-600 hover:bg-purple-50">Panel
                                NexaCore</a>
                        @endif
                    @endauth
                </div>
                @auth
                    <div class="pt-4 pb-3 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div class="shrink-0">
                                <div
                                    class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-base font-bold text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                        <div class="mt-3 space-y-1 px-2">
                            <a href="#"
                                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">Mi
                                Perfil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-red-600 hover:bg-red-50">Cerrar
                                    Sesión</button>
                            </form>
                        </div>
                    </div>
                @endauth
                @guest
                    <div class="pt-4 pb-3 border-t border-gray-200 px-2 space-y-1">
                        <a href="{{ route('login') }}"
                            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50">Ingresar</a>
                    </div>
                @endguest
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    {{-- 🔔 CONTENEDOR DE TOASTS --}}
    <div class="toast-container-custom" id="toastContainer"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- 🔔 SCRIPT BÁSICO DE NOTIFICACIONES (SOLO CARGA INICIAL) --}}
    @if(auth()->check() && in_array(auth()->user()->role, ['Trafico', 'Documentador', 'finanzas']))
        <script>
            // Solo cargar el contador inicial al cargar la página
            document.addEventListener('DOMContentLoaded', function () {
                cargarContadorNotificaciones();
            });

            // Función global para cargar el contador
            function cargarContadorNotificaciones() {
                fetch('{{ route('notificaciones.noLeidas') }}')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Datos recibidos:', data); // Para debugging
                        actualizarBadgeGlobal(data.count);
                        if (data.notificaciones && data.notificaciones.length > 0) {
                            mostrarNotificaciones(data.notificaciones);
                        } else {
                            mostrarNotificacionesVacio();
                        }
                    })
                    .catch(error => console.error('Error al cargar notificaciones:', error));
            }

            // Función global para actualizar el badge
            function actualizarBadgeGlobal(count) {
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }

            // Función para mostrar notificaciones vacías
            function mostrarNotificacionesVacio() {
                const notificationList = document.getElementById('notificationList');
                notificationList.innerHTML = `
                                                        <div class="empty-notifications">
                                                            <i class="fas fa-bell-slash"></i>
                                                            <p class="mb-0">No hay notificaciones</p>
                                                        </div>
                                                    `;
            }

            // Función para mostrar las notificaciones en el dropdown
            function mostrarNotificaciones(notificaciones) {
                const notificationList = document.getElementById('notificationList');

                if (!notificaciones || notificaciones.length === 0) {
                    mostrarNotificacionesVacio();
                    return;
                }

                let html = '';
                notificaciones.forEach(notificacion => {
                    const isUnread = !notificacion.leida; // Cambiado de read_at a leida
                    const timeAgo = getTimeAgo(notificacion.fecha_creacion || notificacion.created_at);
                    const iconClass = getIconClass(notificacion.tipo || 'info');
                    const titulo = notificacion.titulo || 'Nueva notificación';
                    const mensaje = notificacion.mensaje || notificacion.descripcion || 'Sin mensaje';

                    html += `
                                                            <div class="notification-item ${isUnread ? 'unread' : ''}" 
                                                                 data-id="${notificacion.id}" 
                                                                 onclick="marcarComoLeida('${notificacion.id}', this)">
                                                                <div class="notification-icon ${iconClass}">
                                                                    <i class="${getIcon(notificacion.tipo || 'info')}"></i>
                                                                </div>
                                                                <div class="notification-content">
                                                                    <div class="notification-title">${titulo}</div>
                                                                    <div class="notification-message">${mensaje}</div>
                                                                    <div class="notification-time">${timeAgo}</div>
                                                                </div>
                                                            </div>
                                                        `;
                });

                notificationList.innerHTML = html;
            }

            // Función auxiliar para obtener el tiempo transcurrido
            function getTimeAgo(dateString) {
                if (!dateString) return 'Reciente';

                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'Reciente';

                const now = new Date();
                const diffMs = now - date;
                const diffMins = Math.floor(diffMs / 60000);
                const diffHours = Math.floor(diffMs / 3600000);
                const diffDays = Math.floor(diffMs / 86400000);

                if (diffMins < 1) return 'Ahora mismo';
                if (diffMins < 60) return `Hace ${diffMins} minuto${diffMins > 1 ? 's' : ''}`;
                if (diffHours < 24) return `Hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
                if (diffDays < 7) return `Hace ${diffDays} día${diffDays > 1 ? 's' : ''}`;

                return date.toLocaleDateString('es-ES', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
            }

            // Función para obtener la clase del icono según el tipo
            function getIconClass(tipo) {
                const tipos = {
                    'success': 'success',
                    'exito': 'success',
                    'warning': 'warning',
                    'alerta': 'warning',
                    'error': 'warning',
                    'info': 'info',
                    'informacion': 'info'
                };
                return tipos[tipo] || 'info';
            }

            // Función para obtener el icono según el tipo
            function getIcon(tipo) {
                const iconos = {
                    'success': 'fas fa-check-circle',
                    'exito': 'fas fa-check-circle',
                    'warning': 'fas fa-exclamation-triangle',
                    'alerta': 'fas fa-exclamation-triangle',
                    'error': 'fas fa-exclamation-circle',
                    'info': 'fas fa-info-circle',
                    'informacion': 'fas fa-info-circle'
                };
                return iconos[tipo] || 'fas fa-bell';
            }

            // Función para marcar una notificación como leída
            function marcarComoLeida(notificacionId, element) {
                fetch(`/notificaciones/${notificacionId}/marcar-leida`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remover la clase unread
                            element.classList.remove('unread');

                            // Actualizar el contador
                            actualizarBadgeGlobal(data.count || 0);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Marcar todas como leídas (desde cualquier página)
            document.getElementById('markAllAsRead')?.addEventListener('click', function () {
                fetch('{{ route('notificaciones.marcarTodasLeidas') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            actualizarBadgeGlobal(0);
                            cargarContadorNotificaciones(); // Recargar para limpiar la lista
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        </script>
    @endif

    @stack('scripts')

    {{-- 🔔 Sistema de Notificaciones --}}
    @auth
        <script>
            // Configuración de notificaciones según rol
            const USER_ROLE = '{{ auth()->user()->role }}';
            const ES_ADMIN = ['admin', 'super_admin'].includes(USER_ROLE);

            // Sistema de Notificaciones
            let notificacionesAbierto = false;

            function toggleNotificaciones() {
                notificacionesAbierto = !notificacionesAbierto;
                const dropdown = document.getElementById('notificacionesDropdown');

                if (notificacionesAbierto) {
                    dropdown.classList.remove('hidden');
                    cargarNotificaciones();
                } else {
                    dropdown.classList.add('hidden');
                }
            }

            function cargarNotificaciones() {
                if (ES_ADMIN) {
                    // Admin: cargar AMBAS fuentes de notificaciones
                    Promise.all([
                        fetch('{{ route("notificaciones.noLeidas") }}').then(r => r.json()),
                        fetch('{{ route("notificaciones.sistema.no-leidas") }}').then(r => r.json())
                    ]).then(([notifsUsuario, notifsSistema]) => {
                        // Combinar notificaciones de ambas fuentes
                        const notifsUser = notifsUsuario.notificaciones || [];
                        const notifsSys = notifsSistema.notificaciones || [];

                        // Normalizar formato de notificaciones de usuario
                        const notifsUserNormalizadas = notifsUser.map(n => ({
                            id: n.id,
                            tipo: n.tipo || 'notificacion_usuario',
                            titulo: n.titulo || n.mensaje?.substring(0, 50) || 'Notificación',
                            mensaje: n.mensaje || '',
                            nivel: n.nivel || 'info',
                            accion_url: n.accion_url || n.url || null,
                            accion_texto: n.accion_texto || 'Ver',
                            icono: n.icono || 'fa-bell',
                            color: n.color || 'blue',
                            created_at: n.created_at || 'Reciente',
                            fuente: 'usuario'
                        }));

                        // Normalizar formato de notificaciones del sistema
                        const notifsSistemaNormalizadas = notifsSys.map(n => ({
                            ...n,
                            fuente: 'sistema'
                        }));

                        // Combinar y ordenar por fecha (más recientes primero)
                        const todas = [...notifsSistemaNormalizadas, ...notifsUserNormalizadas];
                        const total = (notifsUsuario.count || 0) + (notifsSistema.no_leidas || 0);

                        actualizarBadge(total);
                        renderizarNotificaciones(todas);
                    }).catch(err => console.error('Error cargando notificaciones:', err));
                } else {
                    // No admin: cargar SOLO notificaciones de usuario
                    fetch('{{ route("notificaciones.noLeidas") }}')
                        .then(response => response.json())
                        .then(data => {
                            const notifs = (data.notificaciones || []).map(n => ({
                                id: n.id,
                                tipo: n.tipo || 'notificacion_usuario',
                                titulo: n.titulo || n.mensaje?.substring(0, 50) || 'Notificación',
                                mensaje: n.mensaje || '',
                                nivel: n.nivel || 'info',
                                accion_url: n.accion_url || n.url || null,
                                accion_texto: n.accion_texto || 'Ver',
                                icono: n.icono || 'fa-bell',
                                color: n.color || 'blue',
                                created_at: n.created_at || 'Reciente',
                                fuente: 'usuario'
                            }));

                            actualizarBadge(data.count || 0);
                            renderizarNotificaciones(notifs);
                        })
                        .catch(err => console.error('Error cargando notificaciones:', err));
                }
            }

            function actualizarBadge(count) {
                const badge = document.getElementById('notificacionesBadge');
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }

            function renderizarNotificaciones(notificaciones) {
                const list = document.getElementById('notificacionesList');

                if (!notificaciones || notificaciones.length === 0) {
                    list.innerHTML = `
                                    <div class="p-6 text-center text-gray-400">
                                        <i class="fas fa-bell-slash text-3xl mb-2"></i>
                                        <p class="text-sm">No hay notificaciones</p>
                                    </div>
                                `;
                    return;
                }

                const colores = {
                    'info': 'bg-blue-50 border-blue-200',
                    'warning': 'bg-amber-50 border-amber-200',
                    'error': 'bg-red-50 border-red-200',
                    'success': 'bg-emerald-50 border-emerald-200',
                };

                const iconos = {
                    'info': 'fa-info-circle text-blue-500',
                    'warning': 'fa-exclamation-triangle text-amber-500',
                    'error': 'fa-times-circle text-red-500',
                    'success': 'fa-check-circle text-emerald-500',
                };

                list.innerHTML = notificaciones.map(notif => `
                                <div class="p-4 border-b ${colores[notif.nivel] || colores['info']} hover:opacity-80 cursor-pointer"
                                     onclick="marcarLeida(${notif.id}, '${notif.fuente}')">
                                    <div class="flex items-start gap-3">
                                        <i class="fas ${notif.icono || iconos[notif.nivel] || iconos['info']} mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-gray-800">${notif.titulo}</p>
                                            <p class="text-xs text-gray-600 mt-1">${notif.mensaje}</p>
                                            ${notif.accion_url ? `
                                                <a href="${notif.accion_url}" class="inline-block mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                                    ${notif.accion_texto || 'Ver más'} →
                                                </a>
                                            ` : ''}
                                            <p class="text-xs text-gray-400 mt-1">${notif.created_at}</p>
                                        </div>
                                    </div>
                                </div>
                            `).join('');
            }

            function marcarLeida(id, fuente = 'sistema') {
                if (fuente === 'usuario') {
                    // Marcar notificación de usuario
                    fetch(`/notificaciones/${id}/marcar-leida`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        }
                    }).then(() => {
                        cargarNotificaciones();
                    }).catch(err => console.error('Error marcando como leída:', err));
                } else {
                    // Marcar notificación del sistema
                    fetch(`/api/notificaciones-sistema/${id}/marcar-leida`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        }
                    }).then(() => {
                        cargarNotificaciones();
                    }).catch(err => console.error('Error marcando como leída:', err));
                }
            }

            function marcarTodasLeidas() {
                if (ES_ADMIN) {
                    // Admin: marcar ambas fuentes como leídas
                    Promise.all([
                        fetch('/notificaciones/marcar-todas-leidas', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                            }
                        }),
                        fetch('/api/notificaciones-sistema/marcar-todas', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                            }
                        })
                    ]).then(() => {
                        cargarNotificaciones();
                    }).catch(err => console.error('Error marcando todas como leídas:', err));
                } else {
                    // No admin: marcar solo notificaciones de usuario
                    fetch('/notificaciones/marcar-todas-leidas', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        }
                    }).then(() => {
                        cargarNotificaciones();
                    }).catch(err => console.error('Error marcando todas como leídas:', err));
                }
            }

            // Cargar notificaciones cada 30 segundos
            setInterval(() => {
                if (!notificacionesAbierto) {
                    if (ES_ADMIN) {
                        Promise.all([
                            fetch('{{ route("notificaciones.noLeidas") }}').then(r => r.json()),
                            fetch('{{ route("notificaciones.sistema.no-leidas") }}').then(r => r.json())
                        ]).then(([notifsUsuario, notifsSistema]) => {
                            const total = (notifsUsuario.count || 0) + (notifsSistema.no_leidas || 0);
                            actualizarBadge(total);
                        }).catch(err => console.error('Error actualizando badge:', err));
                    } else {
                        fetch('{{ route("notificaciones.noLeidas") }}')
                            .then(response => response.json())
                            .then(data => {
                                actualizarBadge(data.count || 0);
                            })
                            .catch(err => console.error('Error actualizando badge:', err));
                    }
                }
            }, 30000);

            // Cargar badge al iniciar
            document.addEventListener('DOMContentLoaded', function () {
                if (ES_ADMIN) {
                    Promise.all([
                        fetch('{{ route("notificaciones.noLeidas") }}').then(r => r.json()),
                        fetch('{{ route("notificaciones.sistema.no-leidas") }}').then(r => r.json())
                    ]).then(([notifsUsuario, notifsSistema]) => {
                        const total = (notifsUsuario.count || 0) + (notifsSistema.no_leidas || 0);
                        actualizarBadge(total);
                    }).catch(err => console.error('Error cargando badge inicial:', err));
                } else {
                    fetch('{{ route("notificaciones.noLeidas") }}')
                        .then(response => response.json())
                        .then(data => {
                            actualizarBadge(data.count || 0);
                        })
                        .catch(err => console.error('Error cargando badge inicial:', err));
                }
            });

            // Cerrar dropdown al hacer click fuera
            document.addEventListener('click', function (event) {
                const container = document.getElementById('notificacionesContainer');
                if (container && !container.contains(event.target) && notificacionesAbierto) {
                    notificacionesAbierto = false;
                    document.getElementById('notificacionesDropdown').classList.add('hidden');
                }
            });
        </script>
    @endauth
</body>

</html>