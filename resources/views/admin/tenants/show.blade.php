@extends('layouts.admin')

@section('header_title', 'Administración de Facturación y Consumo: ' . $tenant->nombre_empresa)

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.tenants.index') }}"
            class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Volver a Agencias
        </a>
        <div class="flex gap-2">
            <a href="{{ route('admin.tenants.capabilities', $tenant->id) }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow-sm transition">
                <i class="fas fa-sliders-h"></i> Configurar Capacidades
            </a>
            <a href="{{ route('admin.tenants.edit', $tenant->id) }}"
                class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded shadow-sm transition">
                <i class="fas fa-edit"></i> Editar Datos Básicos
            </a>
            <form method="POST" action="{{ route('admin.tenants.toggle-status', $tenant->id) }}" class="inline">
                @csrf
                @method('PATCH')
                @if($tenant->isActive())
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow-sm transition"
                    onclick="return confirm('¿Suspender esta agencia? Todos los usuarios perderán acceso inmediatamente.')">
                    <i class="fas fa-ban"></i> Suspender Agencia
                </button>
                @else
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm transition">
                    <i class="fas fa-check-circle"></i> Reactivar Agencia
                </button>
                @endif
            </form>
        </div>
    </div>

    <!-- KPIs del Mes Actual -->
    <div class="mb-6">
        <h4 class="text-gray-700 font-bold mb-4 uppercase tracking-wide text-sm border-b pb-2"><i
                class="fas fa-chart-pie text-indigo-500 mr-2"></i> Consumo de este Mes (Cobro)</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Operaciones / Pedimentos -->
            <div
                class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow p-5 text-white flex justify-between items-center transform hover:scale-105 transition duration-300">
                <div>
                    <p class="text-indigo-100 text-xs font-bold uppercase tracking-wider mb-1">Pedimentos Modulados</p>
                    <span class="text-3xl font-black">{{ number_format($opsMes) }}</span>
                </div>
                <div class="text-indigo-200 opacity-75 text-4xl">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>

            <!-- Documentos -->
            <div
                class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow p-5 text-white flex justify-between items-center transform hover:scale-105 transition duration-300">
                <div>
                    <p class="text-blue-100 text-xs font-bold uppercase tracking-wider mb-1">Docs Subidos</p>
                    <span class="text-3xl font-black">{{ number_format($docsMes) }}</span>
                </div>
                <div class="text-blue-200 opacity-75 text-4xl">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
            </div>

            <!-- Emails -->
            <div
                class="bg-gradient-to-br from-orange-400 to-red-500 rounded-xl shadow p-5 text-white flex justify-between items-center transform hover:scale-105 transition duration-300">
                <div>
                    <p class="text-orange-100 text-xs font-bold uppercase tracking-wider mb-1">Correos Enviados</p>
                    <span class="text-3xl font-black">{{ number_format($emailsMes) }}</span>
                </div>
                <div class="text-orange-200 opacity-75 text-4xl">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
            </div>

            <!-- WhatsApp -->
            <div
                class="bg-gradient-to-br from-emerald-400 to-green-600 rounded-xl shadow p-5 text-white flex justify-between items-center transform hover:scale-105 transition duration-300">
                <div>
                    <p class="text-emerald-100 text-xs font-bold uppercase tracking-wider mb-1">WhatsApp Enviados</p>
                    <span class="text-3xl font-black">{{ number_format($whatsappMes) }}</span>
                </div>
                <div class="text-emerald-200 opacity-75 text-4xl">
                    <i class="fab fa-whatsapp"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Suscripción Activa (INC-059) -->
    @php
        $suscripcionActiva = \App\Models\Suscripcion::where('tenant_id', $tenant->id)->where('estado', 'activa')->latest()->first();
        $addonsActivos = \App\Models\AddonContratado::with('addon')->where('tenant_id', $tenant->id)->where('estado', 'activo')->get();
        $planesDisponibles = \App\Models\PlanCustom::where('activo', true)->orderBy('nombre')->get();
        $addonsDisponibles = \App\Models\Addon::where('activo', true)->orderBy('tipo')->orderBy('nombre')->get();
    @endphp

    <div class="mt-6 mb-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Suscripción Activa -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h4 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center">
                <i class="fas fa-credit-card text-indigo-500 mr-2"></i> Suscripción
            </h4>
            @if($suscripcionActiva)
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-black text-emerald-600 uppercase tracking-widest">Plan Activo</p>
                            <p class="text-xl font-black text-gray-800">{{ $suscripcionActiva->plan->nombre }}</p>
                        </div>
                        <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200">ACTIVA</span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 mt-3 text-center">
                        <div>
                            <p class="text-xs text-gray-500">Inicio</p>
                            <p class="font-bold text-sm">{{ $suscripcionActiva->fecha_inicio->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Vence</p>
                            <p class="font-bold text-sm">{{ $suscripcionActiva->fecha_fin->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Restan</p>
                            <p class="font-black text-lg {{ $suscripcionActiva->diasRestantes() <= 7 ? 'text-red-600' : 'text-emerald-600' }}">{{ $suscripcionActiva->diasRestantes() }}d</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-4 text-center">
                    <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-500">Sin suscripción activa</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.suscripciones.crear', $tenant->id) }}" class="flex gap-2">
                @csrf
                <select name="plan_custom_id" required class="flex-1 rounded-lg border-gray-300 text-sm px-3 py-2">
                    <option value="">Seleccionar plan...</option>
                    @foreach($planesDisponibles as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }} — ${{ number_format($p->precio_base, 2) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg text-sm whitespace-nowrap" onclick="return confirm('¿Crear suscripción y enviar email de pago?')">
                    <i class="fas fa-plus mr-1"></i> Crear
                </button>
            </form>
        </div>

        <!-- Add-ons Contratados -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h4 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center">
                <i class="fas fa-puzzle-piece text-purple-500 mr-2"></i> Add-ons
            </h4>
            @if($addonsActivos->count() > 0)
                <div class="space-y-2 mb-4">
                    @foreach($addonsActivos as $ac)
                    <div class="flex justify-between items-center bg-purple-50 border border-purple-200 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-2">
                            <i class="fas {{ $ac->addon->tipo_icon }} text-{{ $ac->addon->tipo_color }}-500"></i>
                            <span class="text-sm font-bold">{{ $ac->addon->nombre }}</span>
                        </div>
                        <span class="text-xs font-bold text-gray-500">{{ $ac->diasRestantes() }}d</span>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 mb-4">Sin add-ons activos</p>
            @endif

            <form method="POST" action="{{ route('admin.suscripciones.addons.contratar', $tenant->id) }}" class="flex gap-2">
                @csrf
                <select name="addon_id" required class="flex-1 rounded-lg border-gray-300 text-sm px-3 py-2">
                    <option value="">Agregar add-on...</option>
                    @foreach($addonsDisponibles as $a)
                        <option value="{{ $a->id }}">{{ $a->nombre }} (${{ number_format($a->precio_mensual, 2) }}/mes)</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-4 py-2 rounded-lg text-sm whitespace-nowrap" onclick="return confirm('¿Contratar add-on y enviar email de pago?')">
                    <i class="fas fa-plus mr-1"></i> Contratar
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Formulario de Configuración de Cobro -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-1">
            <h4 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center">
                <i class="fas fa-file-invoice-dollar text-green-500 mr-2"></i> Configuración de Renta
            </h4>

            <form action="{{ route('admin.tenants.config.update', $tenant->id) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Límite de Usuarios (Paquete)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-users text-gray-400"></i>
                        </div>
                        <input type="number" name="max_usuarios"
                            value="{{ old('max_usuarios', $tenant->max_usuarios ?? 5) }}" required
                            class="pl-10 w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Cantidad máxima de cuentas de usuario permitidas.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Renta Fija Mensual ($)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-dollar-sign text-gray-400"></i>
                        </div>
                        <input type="number" step="0.01" name="renta_mensual"
                            value="{{ old('renta_mensual', $tenant->configuracion['renta_mensual'] ?? 0) }}" required
                            class="pl-10 w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Periodo de Gracia (Días)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-times text-gray-400"></i>
                        </div>
                        <input type="number" name="dias_gracia"
                            value="{{ old('dias_gracia', $tenant->configuracion['dias_gracia'] ?? 3) }}" required
                            class="pl-10 w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Días tolerados tras el vencimiento antes de suspender el sistema.
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Plan de Suscripción</label>
                    <select name="plan_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                        <option value="">Sin plan asignado</option>
                        @foreach($planes as $plan)
                        <option value="{{ $plan->id }}" {{ $tenant->plan_id == $plan->id ? 'selected' : '' }}>
                            {{ $plan->nombre }} — ${{ number_format($plan->precio_mensual, 2) }}/mes
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha de Corte</label>
                    <input type="date" name="fecha_corte" value="{{ old('fecha_corte', $tenant->fecha_corte?->format('Y-m-d')) }}"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                    <p class="text-xs text-gray-500 mt-1">Fecha base para calcular vencimientos y recordatorios.</p>
                </div>

                <div class="mb-6 border-t pt-4">
                    <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Permisos Habilitados para
                        Tenant</h5>
                    <div class="grid grid-cols-1 gap-2">
                        @php $tenantPermisos = $tenant->configuracion['permisos'] ?? array_keys($allPermisos); @endphp
                        @foreach($allPermisos as $key => $label)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="permisos[]" value="{{ $key }}" {{ in_array($key, $tenantPermisos) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span
                                    class="text-xs font-bold text-gray-700 group-hover:text-indigo-600 transition-colors uppercase tracking-tight">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition flex justify-center items-center gap-2">
                    <i class="fas fa-save"></i> Guardar Ajustes
                </button>
            </form>
        </div>

        <!-- Lista de Usuarios Per Tenant -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2 flex flex-col">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h4 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-users-cog text-indigo-500 mr-2"></i> Usuarios del Tenant
                </h4>
                <span class="bg-gray-100 text-gray-600 font-bold px-3 py-1 rounded-full text-xs">
                    {{ $tenant->users->count() }} / {{ $tenant->max_usuarios ?? 'Sin límite' }}
                </span>
            </div>

            <div class="mb-4">
                <button type="button" onclick="document.getElementById('createUserForm').classList.toggle('hidden')"
                    class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold transition">
                    <i class="fas fa-user-plus mr-1"></i> Crear Usuario
                </button>

                <div id="createUserForm" class="hidden mt-4 bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <form method="POST" action="{{ route('admin.tenants.users.store', $tenant->id) }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nombre</label>
                                <input type="text" name="name" required placeholder="Nombre completo"
                                    class="w-full rounded-lg border-gray-300 text-sm px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Email</label>
                                <input type="email" name="email" required placeholder="usuario@ejemplo.com"
                                    class="w-full rounded-lg border-gray-300 text-sm px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Rol</label>
                                <select name="role" required class="w-full rounded-lg border-gray-300 text-sm px-3 py-2">
                                    <option value="admin">Admin</option>
                                    <option value="admin_n2">Admin N2</option>
                                    <option value="documentador">Documentador</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-2 rounded-lg text-sm transition">
                                    <i class="fas fa-check mr-1"></i> Crear y Enviar Email
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Se generará una contraseña aleatoria y se enviará por email.</p>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full whitespace-nowrap">
                    <thead
                        class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500 font-semibold border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3">Usuario y Correo</th>
                            <th class="px-4 py-3">Rol</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($tenant->users as $user)
                            <tr class="hover:bg-indigo-50 transition duration-150">
                                <td class="px-4 py-3">
                                    <p class="font-bold text-gray-800">{{ $user->name }}</p>
                                    <p class="text-gray-500 text-xs">{{ $user->email }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="bg-indigo-50 text-indigo-700 font-bold px-2 py-1 rounded-md text-[10px] border border-indigo-100 uppercase tracking-wide">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($user->active)
                                        <span
                                            class="inline-flex items-center gap-1 text-green-700 px-2 py-1 bg-green-50 rounded-full text-xs font-bold border border-green-200">
                                            <i class="fas fa-check-circle"></i> ACTIVO
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 text-red-700 px-2 py-1 bg-red-50 rounded-full text-xs font-bold border border-red-200">
                                            <i class="fas fa-times-circle"></i> CORTADO
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center flex justify-center items-center gap-2">
                                    <form action="{{ route('admin.tenants.user.toggle', [$tenant->id, $user->id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('¿Confirma que desea cambiar el estado de acceso de este usuario?')">
                                        @csrf
                                        @method('PATCH')
                                        @if($user->active)
                                            <button type="submit"
                                                class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition"
                                                title="Cortar Acceso a {{ $user->name }}">
                                                <i class="fas fa-ban"></i> Suspender
                                            </button>
                                        @else
                                            <button type="submit"
                                                class="text-green-500 hover:text-green-700 hover:bg-green-50 p-2 rounded-lg transition"
                                                title="Reactivar a {{ $user->name }}">
                                                <i class="fas fa-check"></i> Activar
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center text-gray-500">
                                    <i class="fas fa-ghost text-4xl text-gray-300 mb-3 block"></i>
                                    <p>Esta agencia aún no ha registrado usuarios.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection