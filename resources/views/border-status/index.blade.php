@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Consulta de Estatus PECEM</h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('border.status.check') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="petition_integration_number" class="form-label">Número de Integración</label>
                        <input type="text" name="petition_integration_number" id="petition_integration_number"
                            class="form-control" placeholder="Ej: 123456789" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        Consultar
                    </button>
                </form>

                @isset($status_code)
                    <hr>
                    @if($status_code === 0)
                        <!-- Estatus 0 (Verde - Check) -->
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i> {{ $status_txt ?? 'Desaduanamiento Libre' }}
                        </div>
                    @elseif($status_code === 1)
                        <!-- Estatus 1 (Rojo - Times) -->
                        <div class="alert alert-danger d-flex align-items-center">
                            <i class="fas fa-times-circle me-2"></i> {{ $status_txt }}
                        </div>
                    @elseif($status_code === 2)
                        <!-- Estatus 2 (Azul - Info) -->
                        <div class="alert alert-primary d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i> {{ $status_txt }}
                        </div>
                    @elseif($status_code === 3)
                        <!-- Estatus 3 (Amarillo - Exclamation) -->
                        <div class="alert alert-warning d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i> {{ 'DODA no presentado al Mecanismo de Selección Automatizado' }}
                        </div>
                    @else
                        <!-- Caso por defecto (Gris - Question) -->
                        <div class="alert alert-secondary d-flex align-items-center">
                            <i class="fas fa-question-circle me-2"></i> No se encontró un estatus válido
                        </div>
                    @endif
                @endisset
            </div>
        </div>
    </div>
@endsection