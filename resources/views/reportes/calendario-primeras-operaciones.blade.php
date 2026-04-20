{{-- resources/views/reportes/calendario-primeras-operaciones.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Calendario de Primeras Operaciones</h2>
            <p class="text-muted">Clientes que iniciaron operaciones por mes y año</p>
        </div>
    </div>

    @foreach($calendarioClientes as $año => $meses)
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Año {{ $año }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @for($mes = 1; $mes <= 12; $mes++)
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100 {{ isset($meses[$mes]) ? 'border-success' : 'border-light' }}">
                        <div class="card-header {{ isset($meses[$mes]) ? 'bg-success text-white' : 'bg-light' }}">
                            <strong>{{ \Carbon\Carbon::create()->month($mes)->translatedFormat('F') }}</strong>
                            @if(isset($meses[$mes]))
                                <span class="badge bg-white text-success float-end">
                                    {{ count($meses[$mes]) }}
                                </span>
                            @endif
                        </div>
                        <div class="card-body p-2">
                            @if(isset($meses[$mes]))
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($meses[$mes] as $operacion)
                                    <li class="mb-2 pb-2 border-bottom">
                                        <i class="bi bi-person-check text-success"></i>
                                        <strong>{{ $operacion['cliente'] }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($operacion['fecha'])->format('d/m/Y') }}
                                        </small>
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted text-center small mb-0">Sin nuevos clientes</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>
    @endforeach

    @if(empty($calendarioClientes))
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No hay datos de primeras operaciones registradas.
    </div>
    @endif
</div>

<style>
    .card {
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .border-success {
        border-width: 2px !important;
    }
</style>
@endsection