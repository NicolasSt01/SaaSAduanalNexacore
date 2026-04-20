@extends('layouts.app')

@section('title', 'Detalles de Exportación')

@section('customcss')
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #34495e;
        --accent-color: #3498db;
        --success-color: #27ae60;
        --danger-color: #e74c3c;
        --warning-color: #f39c12;
        --light-bg: #f8f9fa;
        --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        --card-hover-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        --border-radius: 12px;
    }

    body {
        background-color: #f5f6fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--primary-color);
    }

    .main-container {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        margin: 2rem 0;
        overflow: hidden;
        animation: slideInUp 0.6s ease-out;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .header-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 2rem;
        position: relative;
    }

    .header-section::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--accent-color), var(--success-color), var(--accent-color));
    }

    .info-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        border: 1px solid #e9ecef;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 1.5rem;
    }

    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-hover-shadow);
        border-color: var(--accent-color);
    }

    .card-header-custom {
        background: var(--light-bg);
        border: none;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .card-header-custom h5 {
        color: var(--primary-color);
        font-weight: 600;
        margin: 0;
    }

    .info-item {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.2s ease;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-item:hover {
        background-color: #f8f9fa;
    }

    .info-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: var(--primary-color);
    }

    .status-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modulacion-container {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        padding: 2rem;
        text-align: center;
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
    }

    .modulacion-indicator {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1rem;
        position: relative;
        margin: 0 auto 1rem;
        transition: all 0.3s ease;
    }

    .modulacion-verde {
        background: var(--success-color);
        box-shadow: 0 0 0 0 rgba(39, 174, 96, 0.4);
        animation: pulse-green 2s infinite;
    }

    .modulacion-rojo {
        background: var(--danger-color);
        box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.4);
        animation: pulse-red 2s infinite;
    }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(39, 174, 96, 0.4); }
        70% { box-shadow: 0 0 0 15px rgba(39, 174, 96, 0); }
        100% { box-shadow: 0 0 0 0 rgba(39, 174, 96, 0); }
    }

    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.4); }
        70% { box-shadow: 0 0 0 15px rgba(231, 76, 60, 0); }
        100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); }
    }

    .sobrepeso-alert {
        background: linear-gradient(135deg, #ff6b6b, #ffa500);
        color: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        border: none;
        margin-bottom: 1.5rem;
        animation: slideInDown 0.5s ease-out;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .btn-custom {
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.875rem;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
    }

    .btn-primary-custom {
        background: var(--accent-color);
        color: white;
    }

    .btn-primary-custom:hover {
        background: #2980b9;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .btn-warning-custom {
        background: var(--warning-color);
        color: white;
    }

    .btn-warning-custom:hover {
        background: #e67e22;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
    }

    .btn-danger-custom {
        background: var(--danger-color);
        color: white;
    }

    .btn-danger-custom:hover {
        background: #c0392b;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    .btn-secondary-custom {
        background: #6c757d;
        color: white;
    }

    .btn-secondary-custom:hover {
        background: #5a6268;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .expediente-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--accent-color);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .expediente-link:hover {
        color: #2980b9;
        text-decoration: none;
    }

    .expediente-link .btn {
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--accent-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .user-avatar.assigned {
        background: var(--success-color);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0;
    }

    .expedition-number {
        font-size: 1.125rem;
        color: #6c757d;
        font-weight: 400;
    }

    .no-data {
        color: #6c757d;
        font-style: italic;
    }
</style>
@endsection

@section('content')
@php
    $modulacion = strtolower($operacion->modulacion ?? '');
@endphp

<div class="container-fluid px-4">
    <div class="main-container">
        <!-- Header -->
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="section-title text-white">Detalles de Operacion</h1>
                </div>
                <div class="btn-group">
                    <a href="{{ route('operaciones.edit', $operacion) }}" class="btn btn-warning-custom">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <form action="{{ route('operaciones.destroy', $operacion) }}" method="POST" onsubmit="return confirm('¿Eliminar esta exportación?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger-custom">
                            <i class="fas fa-trash-alt me-2"></i>Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="container-fluid p-4">

            {{-- Alerta Sobrepeso --}}
            @if($operacion->sobrepeso ?? false)
                <div class="sobrepeso-alert alert">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Atención: Sobrepeso Detectado</h5>
                            <p class="mb-0">Esta exportación presenta sobrepeso. Se requiere documentación adicional.</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <!-- Información Principal -->
                <div class="col-xl-8 col-lg-7">

                    <!-- Información Básica -->
                    <div class="info-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-info-circle me-2 text-primary"></i>Información Básica</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Fecha de Operacion</div>
                                        <div class="info-value">{{ optional($operacion->fecha)->format('d/m/Y') ?? 'No disponible' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Cliente</div>
                                        <div class="info-value">{{ optional($operacion->cliente)->nombre_empresa ?? 'No disponible' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Producto</div>
                                        <div class="info-value">{{ $operacion->nombre_producto ?? 'No disponible' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Importador</div>
                                        <div class="info-value">{{ optional($operacion->importador)->nombre ?? 'No disponible' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Número de Factura</div>
                                        <div class="info-value">{{ $operacion->num_factura ?? '---' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Número Thermo</div>
                                        <div class="info-value">{{ $operacion->num_thermo ?? '---' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Logística -->
                    <div class="info-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-shipping-fast me-2 text-success"></i>Información Logística</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Bodega</div>
                                        <div class="info-value">{{ optional($operacion->bodega)->nombre_bodega ?? 'No disponible' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Aduana</div>
                                        <div class="info-value">{{ optional($operacion->aduana)->nombre_aduana ?? 'No disponible' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Patente</div>
                                        <div class="info-value">
                                            @if($operacion->patente)
                                                <span class="status-badge bg-info text-white">{{ $operacion->patente->numero_patente }}</span>
                                            @else
                                                <span class="no-data">---</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Pedimento Asociado</div>
                                        <div class="info-value">
                                            @if($operacion->expediente)
                                                <a href="{{ route('expedientes.show', $operacion->pedimento_id) }}"
                                                    class="expediente-link">
                                                    <span>{{ $operacion->expediente->numero_pedimento }}</span>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </button>
                                                </a>
                                            @else
                                                <span class="no-data">No disponible</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Código Alpha</div>
                                        <div class="info-value">{{ $operacion->codigo_alpha ?? '---' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Número DODA</div>
                                        <div class="info-value">{{ $operacion->num_doda ?? '---' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Asignado -->
                    <div class="info-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-users me-2 text-info"></i>Personal Asignado</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="info-item">
                                <div class="info-label">Usuario que Registró</div>
                                <div class="info-value">
                                    <div class="user-info">
                                        @if($operacion->documentador)
                                            <div class="user-avatar">{{ strtoupper(substr($operacion->documentador->name,0,2)) }}</div>
                                            <div>
                                                <div class="fw-semibold">{{ $operacion->documentador->name }}</div>
                                                <small class="text-muted">Registrado el {{ $operacion->created_at->format('d/m/Y H:i') }}</small>
                                            </div>
                                        @else
                                            <span class="no-data">No disponible</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Asignado para Completar</div>
                                <div class="info-value">
                                    <div class="user-info">
                                        @if($operacion->asignado)
                                            <div class="user-avatar assigned">{{ strtoupper(substr($operacion->asignado->name,0,2)) }}</div>
                                            <div>
                                                <div class="fw-semibold">{{ $operacion->asignado->name }}</div>
                                                @if($operacion->asignado_desde)
                                                    <small class="text-muted">Asignado desde {{ $operacion->asignado_desde }}</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="no-data">No asignado</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Lateral -->
                <div class="col-xl-4 col-lg-5">
                    <!-- Estado de Modulación -->
                    <div class="modulacion-container">
                        <h5 class="mb-4">
                            <i class="fas fa-traffic-light me-2 text-warning"></i>
                            Estado de Modulación
                        </h5>
                        
                        <div class="modulacion-indicator modulacion-{{ $modulacion === 'desaduanamiento libre' ? 'verde' : 'rojo' }}">
                            <div class="text-center">
                                @if($modulacion === 'desaduanamiento libre')
                                    <i class="fas fa-check fa-2x mb-1"></i>
                                    <div style="font-size: 0.8rem; font-weight: 700;">VERDE</div>
                                @else
                                    <i class="fas fa-times fa-2x mb-1"></i>
                                    <div style="font-size: 0.8rem; font-weight: 700;">ROJO</div>
                                @endif
                            </div>
                        </div>
                        <p class="text-muted mb-0">
                            {{ $operacion->modulacion ?? 'No definido' }}
                        </p>
                    </div>

                    <!-- Información Adicional -->
                    <div class="info-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-clipboard-list me-2 text-secondary"></i>Información Adicional</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="info-item">
                                <div class="info-label">Estado del Trámite</div>
                                <div class="info-value">
                                    <span class="status-badge bg-success text-white">{{ $operacion->estado ?? 'No definido' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Progreso</div>
                                <div class="info-value">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $operacion->porcentaje_progreso ?? 0 }}%" 
                                             aria-valuenow="{{ $operacion->porcentaje_progreso ?? 0 }}" 
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-success mt-1 d-block">{{ $operacion->porcentaje_progreso ?? 0 }}% Completado</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="text-center mt-4 pt-3 border-top">
                <a href="{{ route('operaciones.index') }}" class="btn btn-secondary-custom me-3">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                </a>
                <a href="#{{-- route('operaciones.pdf', $operacion) --}}" class="btn btn-primary-custom">
                    <i class="fas fa-file-pdf me-2"></i>Generar Reporte PDF
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animación de entrada escalonada para las tarjetas
        const cards = document.querySelectorAll('.info-card, .modulacion-container');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.style.opacity = '0';
            card.style.animation = 'slideInUp 0.6s ease-out forwards';
        });
    });
</script>
@endsection