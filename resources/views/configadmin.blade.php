@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <div class="list-group">
                <a href="{{ route('admin.config') }}" class="list-group-item list-group-item-action active">
                    <i class="fas fa-cog mr-2"></i>Configuración General
                </a>
                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-users mr-2"></i>Gestión de Usuarios
                </a>
                <a href="{{ route('clientes.index') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-building mr-2"></i>Gestión de Clientes
                </a>
                <a href="{{ route('patentes.index') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-id-card mr-2"></i>Gestión de Patentes
                </a>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Panel de Configuración</h1>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Usuarios</h5>
                            <p class="card-text">Administra los usuarios del sistema</p>
                            <a href="{{ route('users.index') }}" class="btn btn-light">Gestionar</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Clientes</h5>
                            <p class="card-text">Administra los clientes registrados</p>
                            <a href="{{ route('clientes.index') }}" class="btn btn-light">Gestionar</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Patentes</h5>
                            <p class="card-text">Administra las patentes aduanales</p>
                            <a href="{{ route('patentes.index') }}" class="btn btn-light">Gestionar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .sidebar {
        position: fixed;
        top: 56px; /* Ajusta según tu navbar */
        bottom: 0;
        left: 0;
        z-index: 100;
        padding: 20px 0;
        overflow-x: hidden;
        overflow-y: auto;
        background-color: #f8f9fa;
        border-right: 1px solid #eee;
    }
    
    .main-content {
        margin-left: 250px;
    }
</style>
@endsection