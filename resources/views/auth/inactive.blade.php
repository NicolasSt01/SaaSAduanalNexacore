@extends('layouts.app')

@section('title', 'Usuario Inactivo')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-lg rounded-4 p-4 text-center" style="max-width: 500px;">
        <h3 class="mb-3 text-danger">Cuenta Inactiva</h3>
        <p class="mb-4">Tu cuenta ha sido desactivada.  
        Por favor comunícate con <strong>Soporte Crosspoint</strong> para más información.</p>
        
        <a href="{{ route('login') }}" class="btn btn-primary">Volver al Login</a>
    </div>
</div>
@endsection
