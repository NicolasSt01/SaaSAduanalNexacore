@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm border-0">
                <div class="card-body p-5 text-center">

                    <div class="mb-4">
                        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                             alt="User Icon" 
                             width="90"
                             class="rounded-circle shadow-sm">
                    </div>

                    <h2 class="fw-bold mb-3">¡Bienvenido, {{ Auth::user()->name }}!</h2>

                    <p class="text-muted fs-5 mb-4">
                        Nos alegra tenerte en la plataforma de seguimiento y control de operaciones.
                        Aquí podrás consultar el estado de tus expedientes, documentos y notificaciones
                        de manera rápida y segura.
                    </p>

                    @if(Auth::user()->isCliente() && Auth::user()->cliente)
                        <p class="fw-semibold fs-6 mt-3">
                            <i class="bi bi-building me-1"></i>
                            Empresa: {{ Auth::user()->cliente->nombre_empresa }}
                        </p>
                    @endif

                    <hr class="my-4">

                    <div class="d-flex justify-content-center gap-3">
                        <!--<a href="{{ route('expedientes.indexcliente') }}" class="btn btn-primary px-4">
                            Ver Expedientes
                        </a>-->
                        <a href="{{ route('cliente.admindashboard') }}" class="btn btn-primary px-4">
                            Panel Principal
                        </a>
                        <a href="{{ route('expedientes.indexcliente') }}" class="btn btn-outline-secondary px-4">
                            Ver Expedientes
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
