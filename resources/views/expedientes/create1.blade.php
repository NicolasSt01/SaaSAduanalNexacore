@extends('layouts.app')

@section('title', 'Crear Nuevo Expediente')

@section('content')
    @include('expedientes.form', [
        'clientes' => $clientes,
        'patentes' => $patentes,
        'aduanas' => $aduanas,
        'documentadores' => $documentadores
    ])
@endsection