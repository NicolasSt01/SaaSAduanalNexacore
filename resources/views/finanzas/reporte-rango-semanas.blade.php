@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2>📊 Reporte por Rango de Semanas</h2>
                <p class="text-muted">
                    Del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}
                    al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    (Semana {{ $semanaInicio }}/{{ $anioInicio }} - Semana {{ $semanaFin }}/{{ $anioFin }})
                </p>
            </div>
        </div>

        {{-- FORMULARIO DE FILTROS --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('finanzas.reporte-rango-semanas') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Año Inicio</label>
                            <input type="number" name="anio_inicio" class="form-control" value="{{ $anioInicio }}"
                                min="2020" max="2030">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Semana Inicio</label>
                            <input type="number" name="semana_inicio" class="form-control" value="{{ $semanaInicio }}"
                                min="1" max="53">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Año Fin</label>
                            <input type="number" name="anio_fin" class="form-control" value="{{ $anioFin }}" min="2020"
                                max="2030">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Semana Fin</label>
                            <input type="number" name="semana_fin" class="form-control" value="{{ $semanaFin }}" min="1"
                                max="53">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cliente (Opcional)</label>
                            <select name="cliente_id" class="form-select">
                                <option value="">Todos los clientes</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ $clienteId == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre_empresa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <a href="{{ route('finanzas.reporte-rango-semanas') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- TOTALES GENERALES --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary">{{ $totalesGenerales['pedimentos'] }}</h3>
                        <p class="mb-0">Pedimentos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success">{{ $totalesGenerales['remesas'] }}</h3>
                        <p class="mb-0">Remesas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning">{{ $totalesGenerales['clientes'] }}</h3>
                        <p class="mb-0">Clientes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-danger">{{ $totalesGenerales['rojos'] }}</h3>
                        <p class="mb-0">Rojos</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- RESUMEN POR CLIENTE --}}
        @if($resumenClientes->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📋 Resumen por Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th class="text-center">Pedimentos</th>
                                    <th class="text-center">Remesas</th>
                                    <th class="text-center">Patentes</th>
                                    <th class="text-center">Rojos</th>
                                    <th class="text-center">Sobrepesos</th>
                                    <th class="text-center">Taras</th>
                                    <th class="text-center">Adicionales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resumenClientes as $cliente)
                                    <tr>
                                        <td><strong>{{ $cliente['nombre_empresa'] }}</strong></td>
                                        <td class="text-center">{{ $cliente['totales']['pedimentos'] }}</td>
                                        <td class="text-center">{{ $cliente['totales']['remesas'] }}</td>
                                        <td class="text-center">{{ $cliente['totales']['patentes'] }}</td>
                                        <td class="text-center">{{ $cliente['totales']['rojos'] }}</td>
                                        <td class="text-center">{{ $cliente['totales']['sobrepesos'] }}</td>
                                        <td class="text-center">{{ $cliente['totales']['taras'] }}</td>
                                        <td class="text-center">{{ $cliente['totales']['adicionales'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- DETALLE POR PATENTE --}}
        @if($detallesPorPatente->isNotEmpty())
            @foreach($detallesPorPatente as $detallePatente)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <h5 class="mb-0">🏢 Patente: {{ $detallePatente['patente'] }}</h5>
                            </div>
                            <div class="col-md-10">
                                <div class="row text-center">
                                    <div class="col">
                                        <small>Pedimentos:</small>
                                        <strong class="d-block">{{ $detallePatente['totales']['pedimentos'] }}</strong>
                                    </div>
                                    <div class="col">
                                        <small>Remesas:</small>
                                        <strong class="d-block">{{ $detallePatente['totales']['remesas'] }}</strong>
                                    </div>
                                    <div class="col">
                                        <small>Rojos:</small>
                                        <strong class="d-block">{{ $detallePatente['totales']['rojos'] }}</strong>
                                    </div>
                                    <div class="col">
                                        <small>Sobrepesos:</small>
                                        <strong class="d-block">{{ $detallePatente['totales']['sobrepesos'] }}</strong>
                                    </div>
                                    <div class="col">
                                        <small>Taras:</small>
                                        <strong class="d-block">{{ $detallePatente['totales']['taras'] }}</strong>
                                    </div>
                                    <div class="col">
                                        <small>Adicionales:</small>
                                        <strong class="d-block">{{ $detallePatente['totales']['adicionales'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Semana</th>
                                        <th>Aduana</th>
                                        <th>Pedimento</th>
                                        <th>Fecha Apertura</th>
                                        <th class="text-center">Remesas</th>
                                        <th class="text-center">Rojos</th>
                                        <th class="text-center">Sobrepesos</th>
                                        <th class="text-center">Taras</th>
                                        <th class="text-center">Adicionales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detallePatente['pedimentos'] as $pedimento)
                                        <tr>
                                            <td>{{ $pedimento['cliente'] }}</td>
                                            <td class="text-center">{{ $pedimento['semana'] }}</td>
                                            <td>{{ $pedimento['aduana'] }}</td>
                                            <td>{{ $pedimento['pedimento'] }}</td>
                                            <td>{{ \Carbon\Carbon::parse($pedimento['fecha_apertura'])->format('d/m/Y') }}</td>
                                            <td class="text-center">{{ $pedimento['remesas'] }}</td>
                                            <td class="text-center">{{ $pedimento['rojos'] }}</td>
                                            <td class="text-center">{{ $pedimento['sobrepesos'] }}</td>
                                            <td class="text-center">{{ $pedimento['taras'] }}</td>
                                            <td class="text-center">{{ $pedimento['adicionales'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-info">
                No se encontraron pedimentos en el rango de semanas seleccionado.
            </div>
        @endif
    </div>
@endsection