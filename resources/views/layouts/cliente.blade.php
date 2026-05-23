@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-12 w-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-800">Panel del <span class="text-indigo-600">Cliente</span></h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Bienvenido, {{ auth()->user()->name }}</p>
            </div>
        </div>

        @include('partials.alerts')

        @yield('client-content')
    </div>
</div>
@endsection
