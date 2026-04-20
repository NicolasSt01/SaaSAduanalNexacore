@extends('layouts.app')

@section('title', 'Configuración de Plantillas')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.adminconfig') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-pink-600 transition-colors">
                            <i class="fas fa-cog mr-2"></i> Configuración
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">Plantillas de Correo</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Plantillas de <span class="text-pink-600">Correo</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Personaliza cómo se ven los correos de actualización de estado para tus clientes.</p>
        </div>
    </div>

    @include('partials.alerts')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 bg-pink-600">
            <h3 class="text-lg leading-6 font-black text-white flex items-center">
                <i class="fas fa-paint-roller mr-2"></i> Selecciona tu Estilo
            </h3>
        </div>
        
        <form action="{{ route('admin.config.guardar-plantillas') }}" method="POST">
            @csrf
            <div class="p-6 md:p-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Opción Básica Azul -->
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="plantilla_correo_modulacion" value="basica_azul" class="peer sr-only" {{ $plantilla_seleccionada == 'basica_azul' ? 'checked' : '' }}>
                        <div class="h-full rounded-2xl border-2 border-gray-200 p-6 hover:border-blue-300 peer-checked:border-blue-500 peer-checked:bg-blue-50/50 transition-all text-center flex flex-col">
                            <div class="absolute top-4 right-4 text-blue-500 opacity-0 peer-checked:opacity-100 transition-opacity">
                                <i class="fas fa-check-circle text-2xl drop-shadow-sm"></i>
                            </div>
                            <div class="h-16 w-16 mx-auto bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-3xl mb-4 shadow-inner">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900">Básica Azul</h4>
                            <p class="text-sm text-gray-500 mt-2 flex-grow">Un diseño limpio, corporativo y directo. Ideal para mantener un perfil formal. Utiliza tonos azules trust-building.</p>
                            
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="w-4 h-4 rounded-full bg-blue-600 shadow-sm border border-black/10"></span>
                                    <span class="w-4 h-4 rounded-full bg-slate-100 shadow-sm border border-black/10"></span>
                                    <span class="w-4 h-4 rounded-full bg-slate-800 shadow-sm border border-black/10"></span>
                                </div>
                                <button type="button" onclick="openPreview('basica_azul')" class="z-20 relative text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg transition-colors border border-gray-200 font-bold">
                                    <i class="fas fa-eye"></i> Vista Previa
                                </button>
                            </div>
                        </div>
                    </label>

                    <!-- Opción Moderna Verde -->
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="plantilla_correo_modulacion" value="moderna_verde" class="peer sr-only" {{ $plantilla_seleccionada == 'moderna_verde' ? 'checked' : '' }}>
                        <div class="h-full rounded-2xl border-2 border-gray-200 p-6 hover:border-emerald-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 transition-all text-center flex flex-col">
                            <div class="absolute top-4 right-4 text-emerald-500 opacity-0 peer-checked:opacity-100 transition-opacity">
                                <i class="fas fa-check-circle text-2xl drop-shadow-sm"></i>
                            </div>
                            <div class="h-16 w-16 mx-auto bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-3xl mb-4 shadow-inner">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900">Moderna Verde</h4>
                            <p class="text-sm text-gray-500 mt-2 flex-grow">Alineada con el estatus de las operaciones (Verde = Desaduanamiento Libre). Comunica éxito rápido y es altamente amigable.</p>
                            
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="w-4 h-4 rounded-full bg-emerald-600 shadow-sm border border-black/10"></span>
                                    <span class="w-4 h-4 rounded-full bg-teal-50 shadow-sm border border-black/10"></span>
                                    <span class="w-4 h-4 rounded-full bg-gray-900 shadow-sm border border-black/10"></span>
                                </div>
                                <button type="button" onclick="openPreview('moderna_verde')" class="z-20 relative text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg transition-colors border border-gray-200 font-bold">
                                    <i class="fas fa-eye"></i> Vista Previa
                                </button>
                            </div>
                        </div>
                    </label>

                    <!-- Opción Elegante Oscura -->
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="plantilla_correo_modulacion" value="elegante_oscura" class="peer sr-only" {{ $plantilla_seleccionada == 'elegante_oscura' ? 'checked' : '' }}>
                        <div class="h-full rounded-2xl border-2 border-gray-200 p-6 hover:border-indigo-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 transition-all text-center flex flex-col">
                            <div class="absolute top-4 right-4 text-indigo-500 opacity-0 peer-checked:opacity-100 transition-opacity">
                                <i class="fas fa-check-circle text-2xl drop-shadow-sm"></i>
                            </div>
                            <div class="h-16 w-16 mx-auto bg-gray-800 text-indigo-400 rounded-full flex items-center justify-center text-3xl mb-4 shadow-inner border border-gray-700">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900">Elegante (Dark)</h4>
                            <p class="text-sm text-gray-500 mt-2 flex-grow">Un diseño premium con contrastes oscuros. Altamente distintiva y atractiva para agencias boutique o de tecnología.</p>
                            
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="w-4 h-4 rounded-full bg-gray-900 shadow-sm border border-black/10"></span>
                                    <span class="w-4 h-4 rounded-full bg-indigo-500 shadow-sm border border-black/10"></span>
                                    <span class="w-4 h-4 rounded-full bg-gray-100 shadow-sm border border-black/10"></span>
                                </div>
                                <button type="button" onclick="openPreview('elegante_oscura')" class="z-20 relative text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg transition-colors border border-gray-200 font-bold">
                                    <i class="fas fa-eye"></i> Vista Previa
                                </button>
                            </div>
                        </div>
                    </label>

                    <!-- Plantilla Personalizada Venta -->
                    @if($plantilla_personalizada)
                    <label class="relative cursor-pointer group lg:col-span-full xl:col-span-1 xl:col-start-2">
                        <input type="radio" name="plantilla_correo_modulacion" value="personalizada" class="peer sr-only" {{ $plantilla_seleccionada == 'personalizada' ? 'checked' : '' }}>
                        <div class="h-full rounded-2xl border-2 border-fuchsia-200 p-6 hover:border-fuchsia-400 peer-checked:border-fuchsia-500 peer-checked:bg-fuchsia-50 transition-all text-center flex items-center flex-col shadow-sm relative overflow-hidden bg-gradient-to-br from-white to-fuchsia-50/30">
                            <div class="absolute -right-6 -top-6 text-fuchsia-100 opacity-50 group-hover:scale-110 transition-transform duration-500 pointer-events-none">
                                <i class="fas fa-star text-9xl"></i>
                            </div>
                            <div class="absolute top-4 right-4 text-fuchsia-500 opacity-0 peer-checked:opacity-100 transition-opacity z-10">
                                <i class="fas fa-check-circle text-2xl drop-shadow-sm"></i>
                            </div>
                            <div class="h-16 w-16 mx-auto bg-gradient-to-tr from-fuchsia-600 to-pink-500 text-white rounded-full flex items-center justify-center text-3xl mb-4 shadow-lg z-10 border-2 border-white">
                                <i class="fas fa-magic"></i>
                            </div>
                            <h4 class="text-xl font-black text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-600 to-pink-600 z-10">Plantilla Especial</h4>
                            <p class="text-sm font-bold text-fuchsia-800 bg-fuchsia-100 px-3 py-1 rounded-full inline-block mt-2 z-10 flex-grow mb-4">
                                {{ $plantilla_personalizada }}
                            </p>
                            <div class="z-10 mt-2 flex justify-center w-full">
                                <button type="button" onclick="openPreview('{{ $plantilla_personalizada }}')" class="relative text-xs bg-white text-fuchsia-700 hover:bg-fuchsia-100 px-4 py-2 rounded-lg transition-colors border border-fuchsia-200 font-bold shadow-sm">
                                    <i class="fas fa-eye"></i> Ver Vista Previa
                                </button>
                            </div>
                        </div>
                    </label>
                    @endif

                </div>

            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                <button type="submit" class="inline-flex justify-center items-center rounded-xl border border-transparent bg-pink-600 px-8 py-3 text-sm font-bold text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition-all hover:scale-105">
                    <i class="fas fa-save mr-2"></i> Guardar Plantilla
                </button>
            </div>
        </form>
    </div>

    @if(!$plantilla_personalizada)
    <div class="mt-8 bg-gradient-to-r from-indigo-900 to-purple-800 rounded-2xl shadow-lg p-6 md:p-8 flex flex-col md:flex-row items-center justify-between text-white overflow-hidden relative">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full transform translate-x-1/3 -translate-y-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full transform -translate-x-1/2 translate-y-1/2"></div>
        
        <div class="relative z-10 md:w-2/3 mb-6 md:mb-0">
            <h3 class="text-2xl font-black mb-2 flex items-center text-amber-300">
                <i class="fas fa-crown mr-3"></i> ¿Quieres una identidad 100% tuya?
            </h3>
            <p class="text-indigo-100 text-sm md:text-base pr-4 leading-relaxed">
                Podemos diseñar y programar una plantilla de correos hiper-personalizada para tu agencia aduanal, usando tu imagen de marca, logotipos, colores exactos y firmas de directivos.
            </p>
        </div>
        <div class="relative z-10 md:w-1/3 text-center md:text-right">
            <a href="mailto:soporte@nexacore.mx?subject=Quiero mi plantilla de correo personalizada" class="inline-flex justify-center items-center rounded-xl bg-amber-400 px-6 py-3 text-sm font-black text-indigo-900 shadow-lg hover:bg-amber-300 transition-colors">
                <i class="fas fa-gem mr-2"></i> Solicitar Cotización
            </a>
        </div>
    </div>
    @endif

</div>

<!-- Modal Vista Previa -->
<div id="previewModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Fondo oscuro -->
    <div class="absolute inset-0 transition-opacity bg-black/70 backdrop-blur-sm" aria-hidden="true" onclick="closePreview()"></div>
    
    <!-- Contenedor principal -->
    <div class="relative z-10 bg-white rounded-2xl overflow-hidden shadow-2xl transition-all w-full max-w-4xl h-[85vh] flex flex-col mx-auto">
        <div class="bg-gray-900 px-6 py-4 flex justify-between items-center shrink-0">
            <h3 class="text-lg leading-6 font-bold text-white flex items-center">
                <i class="fas fa-desktop mr-2 text-indigo-400"></i> Vista Previa del Correo
            </h3>
            <button type="button" class="text-gray-400 hover:text-white transition" onclick="closePreview()">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>
        <div class="flex-grow p-0 relative w-full bg-gray-100 flex">
            <div id="loadingPreview" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100 z-10">
                <i class="fas fa-circle-notch fa-spin text-4xl text-indigo-500 mb-4"></i>
                <p class="text-gray-500 font-bold">Cargando previa...</p>
            </div>
            <iframe id="previewIframe" src="" class="w-full h-full border-0 absolute inset-0 z-20 opacity-0 transition-opacity duration-500" onload="this.style.opacity=1; document.getElementById('loadingPreview').style.display='none';"></iframe>
        </div>
    </div>
</div>

<script>
    function openPreview(tipo) {
        event.preventDefault(); // Prevenir tick del radio
        event.stopPropagation();
        
        let iframe = document.getElementById('previewIframe');
        let loader = document.getElementById('loadingPreview');
        
        iframe.style.opacity = 0;
        loader.style.display = 'flex';
        
        // Cargar iframe usando la vista generada al momento
        let baseUrl = "{{ route('admin.config.plantillas.preview', ['tipo' => 'PLACEHOLDER']) }}";
        iframe.src = baseUrl.replace('PLACEHOLDER', tipo);
        
        // Mostrar modal
        document.getElementById('previewModal').classList.remove('hidden');
    }

    function closePreview() {
        document.getElementById('previewModal').classList.add('hidden');
        document.getElementById('previewIframe').src = '';
    }
</script>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
