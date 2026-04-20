@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Panel del Cliente</h4>
                </div>
                
                <div class="card-body">
                    @include('partials.alerts')
                    
                    <!-- Contenido específico para clientes -->
                    @yield('client-content')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection