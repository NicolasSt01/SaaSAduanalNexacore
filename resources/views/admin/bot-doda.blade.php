@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                SOIA-Bot Panel de Control
            </h1>
            <p class="mt-2 text-sm text-gray-600">Monitoreo y ejecución manual de las consultas DODA/PECEM</p>
        </div>
        <div class="flex space-x-3">
            <button id="btnHealth" class="px-4 py-2 bg-white text-gray-700 rounded-lg shadow border border-gray-200 hover:bg-gray-50 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Health Check
            </button>
            <button id="btnRunBot" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg shadow hover:opacity-90 transition-opacity flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span id="btnRunText">Ejecutar Bot Ahora</span>
            </button>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Estado del Bot</p>
                    <p class="text-2xl font-bold mt-1" id="botStatus">Comprobando...</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Operaciones Pendientes</p>
                    <p class="text-2xl font-bold mt-1" id="operacionesPendientes">-</p>
                </div>
                <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Última Ejecución</p>
                    <p class="text-sm font-bold mt-1 text-gray-800" id="ultimaEjecucion">-</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs and Results -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Live Terminal -->
        <div class="lg:col-span-2 bg-gray-900 rounded-xl shadow border border-gray-800 overflow-hidden flex flex-col h-[500px]">
            <div class="flex items-center justify-between px-4 py-3 bg-gray-800 border-b border-gray-700">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <span class="ml-4 text-xs text-gray-400 font-mono tracking-wider">doda_bot.log</span>
                </div>
                <button id="btnRefreshLogs" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
            </div>
            <div class="p-4 flex-1 overflow-y-auto">
                <pre id="terminalLogs" class="text-xs font-mono text-green-400 whitespace-pre-wrap leading-relaxed">Cargando logs...</pre>
            </div>
        </div>

        <!-- Latest Results -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-[500px]">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-800">Resultado de la Última Ejecución</h3>
            </div>
            <div class="p-5 flex-1 overflow-y-auto" id="resultsContainer">
                <div class="text-center text-gray-500 py-10">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="text-sm">Ejecuta el bot para ver los resultados en formato JSON aquí.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnRunBot = document.getElementById('btnRunBot');
    const btnRunText = document.getElementById('btnRunText');
    const btnHealth = document.getElementById('btnHealth');
    const btnRefreshLogs = document.getElementById('btnRefreshLogs');
    const terminalLogs = document.getElementById('terminalLogs');
    const resultsContainer = document.getElementById('resultsContainer');
    
    // Status elements
    const botStatus = document.getElementById('botStatus');
    const opPendientes = document.getElementById('operacionesPendientes');
    const ultimaEjecucion = document.getElementById('ultimaEjecucion');

    let isRunning = false;

    // Fetch CSRF token for POST requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const fetchStatus = () => {
        fetch('{{ route("admin.bot-doda.status") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.bot_activo) {
                botStatus.innerHTML = '<span class="text-yellow-600">Ejecutando...</span>';
                if(!isRunning) setRunningState(true);
            } else {
                botStatus.innerHTML = '<span class="text-green-600">En Espera</span>';
                if(isRunning) setRunningState(false);
            }
            opPendientes.textContent = data.operaciones_pendientes ?? '-';
            if(data.ultima_ejecucion) {
                const date = new Date(data.ultima_ejecucion.consultado_at);
                ultimaEjecucion.textContent = date.toLocaleString();
            }
        });
    };

    const fetchLogs = () => {
        fetch('{{ route("admin.bot-doda.logs") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            terminalLogs.textContent = data.logs;
            terminalLogs.parentElement.scrollTop = terminalLogs.parentElement.scrollHeight;
        });
    };

    const setRunningState = (running) => {
        isRunning = running;
        if(running) {
            btnRunBot.disabled = true;
            btnRunBot.classList.add('opacity-50', 'cursor-not-allowed');
            btnRunText.textContent = 'En Ejecución...';
        } else {
            btnRunBot.disabled = false;
            btnRunBot.classList.remove('opacity-50', 'cursor-not-allowed');
            btnRunText.textContent = 'Ejecutar Bot Ahora';
        }
    };

    btnRunBot.addEventListener('click', () => {
        if(isRunning) return;
        setRunningState(true);
        resultsContainer.innerHTML = '<div class="flex justify-center items-center h-full"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';

        fetch('{{ route("admin.bot-doda.run") }}', {
            method: 'POST',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken 
            }
        })
        .then(res => res.json())
        .then(data => {
            setRunningState(false);
            fetchStatus();
            fetchLogs();
            
            // Format JSON result nicely
            resultsContainer.innerHTML = `<pre class="text-xs text-gray-700 bg-gray-50 p-3 rounded border border-gray-200 overflow-x-auto">${JSON.stringify(data, null, 2)}</pre>`;
            
            if(data.success) {
                // Show simple alert
                const msg = `Completado: ${data.total_consultadas} consultadas, ${data.total_cambios} cambios.`
                terminalLogs.textContent += `\n[UI] ${msg}\n`;
            }
        })
        .catch(err => {
            setRunningState(false);
            resultsContainer.innerHTML = `<div class="text-red-500 text-sm p-4 text-center">Error ejecutando el bot. Revisa la consola o los logs.</div>`;
        });
    });

    btnHealth.addEventListener('click', () => {
        fetch('{{ route("admin.bot-doda.status") }}').then(() => {
            terminalLogs.textContent += "\n[UI] API status OK\n";
            terminalLogs.parentElement.scrollTop = terminalLogs.parentElement.scrollHeight;
        });
    });

    btnRefreshLogs.addEventListener('click', fetchLogs);

    // Initial fetch
    fetchStatus();
    fetchLogs();
    
    // Auto refresh status every 10 seconds
    setInterval(fetchStatus, 10000);
});
</script>
@endsection
