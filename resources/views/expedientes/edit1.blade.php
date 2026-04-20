@extends('layouts.app')

@section('title', 'Editar Expediente')

@section('content')
    @include('expedientes.form', [
        'expediente' => $expediente,
        'clientes' => $clientes,
        'patentes' => $patentes,
        'aduanas' => $aduanas,
        'documentadores' => $documentadores
    ])
@endsection
