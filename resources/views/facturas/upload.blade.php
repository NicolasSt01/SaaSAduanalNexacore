@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cargar Factura XML</h2>
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <form action="{{ route('facturas.process') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="xml_file">Seleccionar archivo XML:</label>
            <input type="file" class="form-control-file" id="xml_file" name="xml_file" required>
        </div>
        <button type="submit" class="btn btn-primary">Procesar XML</button>
    </form>
</div>
@endsection