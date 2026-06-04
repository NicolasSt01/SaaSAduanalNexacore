<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ============================================================================
// IMPORTACIÓN DE CONTROLADORES
// ============================================================================
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\FinanzasController;
use App\Http\Controllers\AduanaController;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\BorderStatusController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConceptoAdicionalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectorioController;
use App\Http\Controllers\DocumentadorController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\DodaBotController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\FacturaXMLController;
use App\Http\Controllers\FinanzasController;
use App\Http\Controllers\ImportadorController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\NotificacionesSistemaController;
use App\Http\Controllers\OperacionController;
use App\Http\Controllers\OperacionImportController;
use App\Http\Controllers\PatenteController;
use App\Http\Controllers\RecorridoController;
use App\Http\Controllers\ReporteClienteMailController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\PublicRegisterController;

// ============================================================================
// RUTAS PÚBLICAS
// ============================================================================

// Landing page
Route::get('/', function () {
    return view('landing');
})->name('home');

// Página de prueba para alerts
// Route::get('/test-alerts', function () {
//     return view('test-alerts')->with('success', 'Este es un mensaje de prueba');
// });

// Reporte público (sin autenticación)
Route::get('/reporte/{token}', [ReporteClienteMailController::class, 'verReportePublico'])
    ->name('reporte.publico');

// ============================================================================
// REGISTRO PÚBLICO DE TENANTS (TRIAL)
// ============================================================================

Route::prefix('registro')->name('public.')->group(function () {
    // Formulario de registro
    Route::get('/', [PublicRegisterController::class, 'showRegister'])->name('form');

    // Procesar registro
    Route::post('/', [PublicRegisterController::class, 'register'])->name('register');

    // Página de éxito
    Route::get('/exito', [PublicRegisterController::class, 'registerSuccess'])->name('register-success');

    // Verificar correo electrónico
    Route::get('/verificar/{token}', [PublicRegisterController::class, 'verifyEmail'])->name('verify-email');
});

// Cambio de contraseña (primer login)
Route::middleware(['auth'])->group(function () {
    Route::get('/cambiar-contrasena', [PublicRegisterController::class, 'showChangePassword'])
        ->name('change-first-password');
    Route::post('/cambiar-contrasena', [PublicRegisterController::class, 'changeFirstPassword'])
        ->name('change-first-password.store');

    // Trial expirado
    Route::get('/trial/expirado', function () {
        return view('auth.trial-expired');
    })->name('trial.expired');
});

// ============================================================================
// AUTENTICACIÓN
// ============================================================================

Auth::routes(['verify' => true]);

Route::get('login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Página de usuario inactivo
Route::get('/inactive', function () {
    return view('auth.inactive');
})->name('inactive.user');

// ============================================================================
// DASHBOARD GENERAL (Todos los roles)
// ============================================================================

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// ============================================================================
// SUPER ADMIN - NEXACORE (Acceso exclusivo rol: super_admin)
// ============================================================================

Route::prefix('nexacore-admin')->name('admin.')->middleware(['auth', 'super_admin'])->group(function () {

    // --- Dashboard Super Admin ---
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('super_dashboard');
    Route::get('/dashboard/data', [AdminDashboardController::class, 'liveData']);

    // --- Gestión de Tenants (CRUD) ---
    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
    Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
    Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');

    // Configuración de facturación y permisos del tenant
    Route::post('/tenants/{tenant}/config', [TenantController::class, 'updateConfig'])->name('tenants.config.update');

    // Gestión de usuarios del tenant
    Route::patch('/tenants/{tenant}/users/{user}/toggle', [TenantController::class, 'toggleUserStatus'])->name('tenants.user.toggle');

    // Suspender / Reactivar tenant
    Route::patch('/tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');

    // Crear usuario para tenant
    Route::post('/tenants/{tenant}/users', [TenantController::class, 'createUser'])->name('tenants.users.store');

    // --- Capacidades y Límites del Tenant ---
    Route::get('/tenants/{tenant}/capabilities', [TenantController::class, 'capabilities'])->name('tenants.capabilities');
    Route::put('/tenants/{tenant}/capabilities', [TenantController::class, 'updateCapabilities'])->name('tenants.capabilities.update');
    Route::post('/tenants/{tenant}/capabilities/apply-defaults', [TenantController::class, 'applyPlanDefaults'])->name('tenants.capabilities.apply-defaults');
    Route::get('/tenants/{tenant}/usage', [TenantController::class, 'getUsage'])->name('tenants.usage');

    // --- Finanzas: Facturación, Planes, Pagos ---
    Route::get('/finanzas', [FinanzasController::class, 'dashboard'])->name('finanzas.dashboard');

    Route::get('/finanzas/planes', [FinanzasController::class, 'planes'])->name('finanzas.planes');
    Route::post('/finanzas/planes', [FinanzasController::class, 'storePlan'])->name('finanzas.planes.store');
    Route::put('/finanzas/planes/{plan}', [FinanzasController::class, 'updatePlan'])->name('finanzas.planes.update');
    Route::delete('/finanzas/planes/{plan}', [FinanzasController::class, 'destroyPlan'])->name('finanzas.planes.destroy');

    Route::get('/finanzas/pagos', [FinanzasController::class, 'pagos'])->name('finanzas.pagos');
    Route::post('/finanzas/pagos', [FinanzasController::class, 'storePago'])->name('finanzas.pagos.store');

    Route::get('/finanzas/facturas', [FinanzasController::class, 'facturas'])->name('finanzas.facturas');
    Route::get('/finanzas/facturas/{factura}/descargar', [FinanzasController::class, 'descargarFactura'])->name('finanzas.facturas.descargar');
});

// ============================================================================
// PANEL DE ADMINISTRACIÓN (Solo roles: admin, super_admin)
// ============================================================================

Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {

    // --- Dashboard Admin ---
    Route::get('/admin', [UserController::class, 'dashboardadmin'])->name('admin.admindashboard');
    Route::get('/configuracionportal', [UserController::class, 'configuracionportal'])->name('admin.adminconfig');

    // --- Configuración del Tenant (Accesible para admin del tenant) ---
    Route::get('/admin/config', [ConfigController::class, 'index'])->name('admin.config');

    // Configuración de referencias
    Route::get('/configuracion-referencias', [ConfigController::class, 'referencia'])->name('admin.config.referencia');
    Route::post('/configuracion-referencias', [ConfigController::class, 'guardarReferencia'])->name('admin.guardar-referencia');

    // Configuración de analíticas y metas
    Route::get('/configuracion-analiticas', [ConfigController::class, 'analiticas'])->name('admin.config.analiticas');
    Route::post('/configuracion-analiticas', [ConfigController::class, 'guardarAnaliticas'])->name('admin.config.guardar-analiticas');

    // Configuración de plantillas de correo
    Route::get('/configuracion-plantillas', [ConfigController::class, 'plantillas'])->name('admin.config.plantillas');
    Route::post('/configuracion-plantillas', [ConfigController::class, 'guardarPlantillas'])->name('admin.config.guardar-plantillas');
    Route::get('/configuracion-plantillas/preview/{tipo}', [ConfigController::class, 'previewPlantilla'])->name('admin.config.plantillas.preview');

    // Configuración SMTP (Servidor de correo del tenant)
    Route::get('/configuracion-smtp', [ConfigController::class, 'smtp'])->name('admin.config.smtp');
    Route::post('/configuracion-smtp', [ConfigController::class, 'guardarSmtp'])->name('admin.config.guardar-smtp');
    Route::post('/configuracion-smtp/probar', [ConfigController::class, 'probarSmtp'])->name('admin.config.smtp.probar');

    // --- Configuración WhatsApp (Evolution API) ---
    Route::get('/configuracion-whatsapp', [WhatsAppController::class, 'index'])->name('admin.config.whatsapp');
    Route::post('/admin/whatsapp/conectar', [WhatsAppController::class, 'conectar'])->name('admin.whatsapp.conectar');
    Route::post('/admin/whatsapp/estado', [WhatsAppController::class, 'estado'])->name('admin.whatsapp.estado');
    Route::post('/admin/whatsapp/desconectar', [WhatsAppController::class, 'desconectar'])->name('admin.whatsapp.desconectar');
    Route::post('/admin/whatsapp/grupos', [WhatsAppController::class, 'grupos'])->name('admin.whatsapp.grupos');
    Route::post('/admin/whatsapp/guardar-plantilla', [WhatsAppController::class, 'guardarPlantilla'])->name('admin.whatsapp.guardarPlantilla');
    Route::get('/admin/whatsapp/pendientes', [WhatsAppController::class, 'pendientes'])->name('admin.whatsapp.pendientes');
    Route::post('/admin/whatsapp/reenviar-pendiente', [WhatsAppController::class, 'reenviarPendiente'])->name('admin.whatsapp.reenviarPendiente');
    Route::post('/admin/whatsapp/descartar-pendiente', [WhatsAppController::class, 'descartarPendiente'])->name('admin.whatsapp.descartarPendiente');
    Route::post('/admin/whatsapp/descartar-todas-pendientes', [WhatsAppController::class, 'descartarTodasPendientes'])->name('admin.whatsapp.descartarTodasPendientes');
    Route::post('/admin/whatsapp/contactos', [WhatsAppController::class, 'contactos'])->name('admin.whatsapp.contactos');
    Route::get('/admin/whatsapp/debug/instancias', [WhatsAppController::class, 'debugInstancias'])->name('admin.whatsapp.debug.instancias');
    Route::get('/admin/whatsapp/debug/contactos', [WhatsAppController::class, 'debugContactos'])->name('admin.whatsapp.debug.contactos');

    // --- Panel de Control SOIA-Bot ---
    Route::get('/admin/bot-doda', [DodaBotController::class, 'showTestPanel'])->name('admin.bot-doda.panel');
    Route::post('/admin/bot-doda/run', [DodaBotController::class, 'runLocal'])->name('admin.bot-doda.run');
    Route::get('/admin/bot-doda/logs', [DodaBotController::class, 'getLogs'])->name('admin.bot-doda.logs');
    Route::get('/admin/bot-doda/status', [DodaBotController::class, 'statusUi'])->name('admin.bot-doda.status');
});

// ============================================================================
// GESTIÓN DE USUARIOS (Solo roles: admin, super_admin)
// ============================================================================

Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    Route::get('usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::get('usuarios/create', [UserController::class, 'create'])->name('usuarios.create');
    Route::post('usuarios', [UserController::class, 'store'])->name('usuarios.store');
    Route::get('usuarios/{usuario}/edit', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('usuarios/{usuario}', [UserController::class, 'update'])->name('usuarios.update');
    Route::put('usuarios/{usuario}/desactivar', [UserController::class, 'desactivar'])->name('usuarios.desactivar');
});

// ============================================================================
// CATÁLOGOS GENERALES (Accesibles por múltiples roles)
// ============================================================================

Route::middleware(['auth'])->group(function () {

    // --- Clientes ---
    Route::resource('clientes', ClienteController::class);

    // Documentos de cliente (Art. 36-A)
    Route::post('/clientes/{cliente}/documentos', [ClienteController::class, 'subirDocumento'])->name('clientes.subirDocumento');
    Route::delete('/clientes/{cliente}/documentos/{documento}', [ClienteController::class, 'eliminarDocumento'])->name('clientes.eliminarDocumento');

    // --- Directorio de Notificaciones ---
    Route::resource('directorio', DirectorioController::class);
    Route::get('/api/directorio/cliente/{clienteId}', [DirectorioController::class, 'getContactosByCliente'])->name('api.directorio.cliente');

    // --- Patentes Aduanales ---
    Route::resource('patentes', PatenteController::class);
    Route::get('/patentes/{id}/aduanas', [PatenteController::class, 'getAduanas']);

    // --- Aduanas (Recurso global) ---
    Route::resource('aduanas', AduanaController::class);

    // --- Importadores ---
    Route::resource('importadores', ImportadorController::class);

    // --- Bodegas ---
    Route::resource('bodegas', BodegaController::class);
});

// ============================================================================
// EXPEDIENTES / PEDIMENTOS
// ============================================================================

Route::middleware(['auth'])->group(function () {
    Route::resource('expedientes', ExpedienteController::class);

    // Descarga masiva de documentos
    Route::get('expedientes/{expediente}/download', [ExpedienteController::class, 'downloadAllDocuments'])
        ->name('expedientes.downloadAll');

    // Vista para clientes
    Route::get('expedientes/{expediente}/showclient', [ExpedienteController::class, 'showclient'])
        ->name('expedientes.showclient');

    // Cerrar firma de expediente
    Route::post('/expedientes/{expediente}/cerrar-firma', [ExpedienteController::class, 'cerrarFirma'])
        ->name('expedientes.cerrarFirma');

    // Actualizar checklist
    Route::post('/expedientes/{expediente}/update-checklist', [ExpedienteController::class, 'updateChecklist'])
        ->name('expedientes.updateChecklist');

    // Documentos pendientes (JSON)
    Route::get('/expedientes/{expediente}/documentos-pendientes', [ExpedienteController::class, 'documentosPendientes'])
        ->name('expedientes.documentos-pendientes');
});

// ============================================================================
// DOCUMENTOS
// ============================================================================

Route::middleware(['auth'])->group(function () {

    // Documentos de expedientes
    Route::prefix('expedientes/{expediente}')->group(function () {
        Route::post('documentos', [DocumentoController::class, 'store'])->name('documentos.store');
    });

    Route::delete('documentos/{documento}', [DocumentoController::class, 'destroy'])->name('documentos.destroy');
    Route::get('/documentos/{documento}/preview', [DocumentoController::class, 'preview'])->name('documentos.preview');
    Route::get('/documentos/{documento}/download', [DocumentoController::class, 'download'])->name('documentos.download');

    // Guardar documentos desde tráfico
    Route::post('/documentos/savedoc', [DocumentoController::class, 'store2'])->name('documentos_operacion.store');
    Route::post('/documentos/savedoctrafico', [DocumentoController::class, 'store3'])->name('documentos_operacion.store2');

    // Documentos de conceptos adicionales
    Route::post('/documentos/concepto-adicional', [DocumentoController::class, 'storeConceptoAdicional'])
        ->name('documentos.storeConceptoAdicional');
});

// ============================================================================
// OPERACIONES (Tráfico y Documentación)
// ============================================================================

Route::middleware(['auth'])->group(function () {

    // --- CRUD de Operaciones ---
    Route::resource('operaciones', OperacionController::class);

    // --- Prioridad de operaciones ---
    Route::post('operaciones/{operacion}/update-priority', [OperacionController::class, 'updatePriority'])
        ->name('operaciones.updatePriority');

    // --- Asignación de operaciones ---
    Route::get('/operaciones/{operacion}/asignar', [OperacionController::class, 'showAsignarForm'])
        ->name('operaciones.showAsignarForm');
    Route::post('/operaciones/{operacion}/asignar', [OperacionController::class, 'asignar'])
        ->name('operaciones.asignar');

    // --- Actualización de modulación ---
    Route::put('/operaciones/{num_thermo}/modulacion', [OperacionController::class, 'updateModulacion'])
        ->name('operaciones.updatemodulacion');

    Route::post('/operaciones/actualizar-modulaciones-masivo', [OperacionController::class, 'actualizarModulacionesMasivo'])
        ->name('operaciones.actualizar-modulaciones-masivo');

    // --- Actualización de campos ---
    Route::post('/operaciones/actualizar-campo', [OperacionController::class, 'actualizarCampo'])
        ->name('operaciones.actualizarCampo');

    Route::post('/operaciones/{id}/update-alpha', [OperacionController::class, 'updateAlpha'])
        ->name('trafico.operaciones.updateAlpha');

    // --- Sobrepeso ---
    Route::post('/operaciones/{id}/sobrepeso', [OperacionController::class, 'updateSobrepeso'])
        ->name('operaciones.updateSobrepeso');

    // --- Asignar bodega ---
    Route::post('/operaciones/{operacion}/asignar-bodega', [OperacionController::class, 'asignarBodega'])
        ->name('operaciones.asignarBodega');

    // --- Formulario administrativo (cuadrar registros) ---
    Route::get('/operaciones/admin/create', [OperacionController::class, 'createAdmin'])
        ->name('operaciones.admin.create');
    Route::post('/operaciones/admin/store', [OperacionController::class, 'storeAdmin'])
        ->name('operaciones.admin.store');
});

// ============================================================================
// DEPARTAMENTO DE TRÁFICO
// ============================================================================

Route::middleware(['auth'])->prefix('trafico')->name('trafico.')->group(function () {

    // --- Dashboard Tráfico ---
    Route::get('/', [OperacionController::class, 'dashboardTrafico'])->name('index');
    Route::get('/dashboard/ajax', [OperacionController::class, 'dashboardTraficoAjax'])->name('dashboard.ajax');

    // --- Acciones rápidas ---
    Route::post('/acknowledge/{id}', [OperacionController::class, 'acknowledgeOp'])->name('acknowledge');
    Route::get('/detalle/{id}', [OperacionController::class, 'obtenerDetalle'])->name('detalle');

    // --- Nueva operación de exportación ---
    Route::get('/nuevaexpo', [OperacionController::class, 'nuevaoperacion'])->name('nuevaexpo');
    Route::post('/nuevaexpo/store', [OperacionController::class, 'storetrafico'])->name('operaciones.storetrafico');

    // --- Detalle de operación ---
    Route::get('/operaciones/{id}', [OperacionController::class, 'showDataExpo'])->name('operaciones.show');

    // --- Modales de información ---
    Route::get('/modal-detalle/{thermo}/{alpha}', [OperacionController::class, 'modalDetalle'])->name('modal.detalle');
    Route::get('/modal-ubicacion/{thermo}/{alpha}', [OperacionController::class, 'modalUbicacion'])->name('modal.ubicacion');
    Route::get('/modal-modulacion/{thermo}/{alpha}', [OperacionController::class, 'modalModulacion'])->name('modal.modulacion');
    Route::get('/modal-modulacion/{thermo}/{alpha}/print', [OperacionController::class, 'printModulacion'])->name('modal.modulacion.print');
    Route::get('/modal-conceptos/{thermo}/{alpha}', [OperacionController::class, 'modalConceptos'])->name('modal.conceptos');
});

// Rutas de verificación de modulación (Bot)
// Route::get('/check', [OperacionController::class, 'check'])->name('operaciones.actualizarmodulacion');
// Route::get('/checktrafico', [OperacionController::class, 'checktrafico'])->name('operaciones.actualizarmodulacion2');
Route::get('/checktraficobot', [OperacionController::class, 'checkTraficoBot']);

// ============================================================================
// DEPARTAMENTO DE DOCUMENTACIÓN
// ============================================================================

Route::middleware(['auth'])->prefix('documentador')->name('documentador.')->group(function () {

    // --- Dashboard Documentador ---
    Route::get('/dashboard', [DocumentadorController::class, 'index'])->name('dashboard');
    Route::get('/live-data', [DocumentadorController::class, 'liveData'])->name('liveData');

    // --- Trabajar en operación ---
    Route::get('/trabajar/{id}', [DocumentadorController::class, 'trabajarOperacion2'])->name('trabajar');
    Route::get('/operacion/{id}/editar', [DocumentadorController::class, 'editarOperacion'])->name('documentador.operacion.editar');
    Route::post('/operacion/{id}/campo', [DocumentadorController::class, 'actualizarCampoOperacion'])->name('documentador.operacion.campo');

    // --- Acciones de trámite ---
    Route::post('/store', [DocumentadorController::class, 'storeOperacion'])->name('storeOperacion');
    Route::post('/actualizar-estado/{id}', [DocumentadorController::class, 'updateEstado'])->name('updateEstado');
    Route::post('/actualiza/{id}', [DocumentadorController::class, 'actualizardatosoperacion'])->name('actualizardata');
    Route::post('/completar/{id}', [DocumentadorController::class, 'completarOperacion'])->name('completar');
    Route::post('/cancelar/{id}', [DocumentadorController::class, 'cancelarOperacion'])->name('cancelarOperacion');

    // --- Tomar/Soltar trámite (No usado en este flujo de proyecto) ---
    // Route::post('/tomar-tramite', [DocumentadorController::class, 'tomarTramite'])->name('tomarTramite');
    // Route::post('/tramite/{id}/tomar', [DocumentadorController::class, 'tomarTramite2'])->name('tomar_tramite2');
    // Route::post('/take', [DocumentadorController::class, 'takeAssignment'])->name('tomar_tramite');
    // Route::post('/soltar-tramite/{id}', [DocumentadorController::class, 'soltarTramite'])->name('soltar_tramite');

    // --- Actualización de DODA/Pedimento ---
    Route::post('/update-doda/{id}', [DocumentadorController::class, 'updateDodaPedimento'])->name('updateDodaPedimento');

    // --- API: Documentos de una operación (para modal details) ---
    Route::get('/api/operaciones/{id}/documentos', [DocumentadorController::class, 'getDocumentosOperacion'])
        ->name('api.operacion.documentos');
});

// ============================================================================
// FINANZAS
// ============================================================================

Route::middleware(['auth'])->prefix('finanzas')->name('finanzas.')->group(function () {

    // --- Dashboard Finanzas ---
    Route::get('/', [FinanzasController::class, 'indexNew'])->name('index');

    // --- Vistas de detalle ---
    Route::get('/detalle/{clienteId}/{patenteId}', [FinanzasController::class, 'detalleClientePatente'])
        ->name('detalle.cliente.patente');
    Route::get('/expediente/{expedienteId}', [FinanzasController::class, 'detalleExpediente'])
        ->name('detalle.expediente');

    // --- Gestión de Facturas ---
    Route::post('/factura/guardar', [FinanzasController::class, 'guardarFactura'])->name('factura.guardar');
    Route::delete('/factura/{facturaId}', [FinanzasController::class, 'eliminarFactura'])->name('factura.eliminar');
    Route::get('/factura/{facturaId}', [FinanzasController::class, 'obtenerFactura'])->name('factura.obtener');
    Route::post('/factura/{facturaId}/cambiar-estado', [FinanzasController::class, 'cambiarEstadoFactura'])
        ->name('factura.cambiar.estado');

    // --- Documentos de Facturas ---
    Route::post('/factura/documento/subir', [FinanzasController::class, 'subirDocumentoFactura'])
        ->name('factura.documento.subir');
    Route::delete('/factura/documento/{documentoId}', [FinanzasController::class, 'eliminarDocumentoFactura'])
        ->name('factura.documento.eliminar');
    Route::get('/documento/{documentoId}/descargar', [FinanzasController::class, 'descargarDocumento'])
        ->name('documento.descargar');

    // --- Exportar PDFs ---
    Route::get('/exportar/pdf', [FinanzasController::class, 'exportarPDF'])->name('exportar.pdf');
    Route::get('/exportar/detalle/{clienteId}/{patenteId}/pdf', [FinanzasController::class, 'exportarDetalleClientePatentePDF'])
        ->name('exportar.detalle.pdf');

    // --- Modal de modulación ---
    Route::get('/modal-modulacion/{id}', [FinanzasController::class, 'modalModulacion'])->name('modal.modulacion');
    Route::get('/modal-modulacion/{id}/print', [FinanzasController::class, 'printModulacion'])->name('modal.modulacion.print');

    // --- Documentos almacenados ---
    Route::get('/documentos/{filename}', function ($filename) {
        $path = storage_path('app/documentos/' . $filename);
        if (!\Illuminate\Support\Facades\File::exists($path)) {
            abort(404);
        }
        $file = \Illuminate\Support\Facades\File::get($path);
        $type = \Illuminate\Support\Facades\File::mimeType($path);
        return response($file, 200)->header("Content-Type", $type);
    })->name('documento.show');
});

// ============================================================================
// CONCEPTOS ADICIONALES
// ============================================================================

Route::middleware(['auth'])->group(function () {
    Route::post('/conceptos-adicionales', [ConceptoAdicionalController::class, 'store'])->name('conceptos.store');
    Route::put('/conceptos-adicionales/{concepto}', [ConceptoAdicionalController::class, 'update'])->name('conceptos.update');
    Route::delete('/conceptos-adicionales/{concepto}', [ConceptoAdicionalController::class, 'destroy'])->name('conceptos.destroy');
    Route::get('/conceptos-adicionales/camion', [ConceptoAdicionalController::class, 'getConceptosCamion'])->name('conceptos.camion');
    Route::get('/finanzas/resumen/{expediente}', [ConceptoAdicionalController::class, 'resumenFinanzas'])->name('finanzas.resumen');
});

// ============================================================================
// FACTURAS XML
// ============================================================================

Route::middleware(['auth'])->group(function () {
    Route::get('/facturas/upload', [FacturaXMLController::class, 'showUploadForm'])->name('facturas.upload');
    Route::post('/facturas/process', [FacturaXMLController::class, 'processXML'])->name('facturas.process');
    Route::post('/facturas/store', [FacturaXMLController::class, 'store'])->name('facturas.store');
});

// ============================================================================
// REPORTES
// ============================================================================

Route::middleware(['auth'])->group(function () {

    // --- Índice de Reportes ---
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/upgrade/{reporte?}', [ReporteController::class, 'upgrade'])->name('reportes.upgrade');

    // --- Reportes de Trámites ---
    Route::get('/reportes/tramites-anuales', [ReporteController::class, 'tramitesAnuales'])
        ->name('reportes.tramites-anuales');
    Route::get('/reportes/tramites-comparativos', [ReporteController::class, 'tramitesComparativos'])
        ->name('reportes.tramites-comparativos');

    // --- Reportes de Operaciones ---
    Route::get('/reportes/cliente', [ReporteController::class, 'reporteCliente'])
        ->middleware('report.access:clientes')
        ->name('reportes.cliente');
    Route::get('/reportes/cliente/pdf', [ReporteController::class, 'reporteClientePdf'])
        ->middleware('report.access:clientes')
        ->name('reportes.cliente.pdf');
    Route::get('/reportes/operaciones_semanas', [ReporteController::class, 'operacionesPorSemanas'])
        ->name('reportes.operaciones_semanas');
// Route::get('/reportes/demo', [ReporteController::class, 'operacionesPorSemanas2'])->name('reportes.demo');
    Route::get('/reportes/operaciones-diarias', [ReporteController::class, 'operacionesDiarias'])
        ->name('reportes.operaciones-diarias');
    Route::get('/api/reportes/operaciones-diarias', [ReporteController::class, 'operacionesDiariasApi'])
        ->name('api.reportes.operaciones-diarias');
    Route::get('/reportes/operaciones-diarias/pdf', [ReporteController::class, 'exportarPDF'])
        ->name('reportes.operaciones-diarias.pdf');
    Route::get('/reportes/operaciones-diarias/excel', [ReporteController::class, 'exportarExcel'])
        ->name('reportes.operaciones-diarias.excel');
    Route::get('/reportes/operacion-semanal', [ReporteController::class, 'operacionSemanal'])
        ->middleware('report.access:operacion_semanal')
        ->name('reportes.operacion_semanal');

    // --- Reportes de Remesas ---
    Route::get('/reportes/remesas', [ReporteController::class, 'reporteRemesas'])
        ->middleware('report.access:remesas')
        ->name('reportes.remesas');
    Route::post('/reportes/exportar/excel', [ReporteController::class, 'exportarExcel'])->name('reportes.exportar.excel');
    Route::post('/reportes/exportar/pdf', [ReporteController::class, 'exportarPDF'])->name('reportes.exportar.pdf');
    Route::post('/reportes/estadisticas', [ReporteController::class, 'obtenerEstadisticas'])->name('reportes.estadisticas');

    // --- Reportes de Aduanas ---
    Route::get('/reportes/aduanas', [ReporteController::class, 'reporteAduanas'])
        ->middleware('report.access:aduanas')
        ->name('reportes.aduanas');

    // --- Reportes de Gerencia ---
    Route::get('/reportes/gerencia', [ReporteController::class, 'reporteGerencia'])->name('reportes.gerencia');
    Route::get('/reportes/patrones-cliente', [ReporteController::class, 'reportePatronesCliente'])
        ->middleware('report.access:patron_clientes')
        ->name('reportes.patrones-cliente');

    // --- Reporte de Pedimentos ---
    Route::get('/reportes/pedimentos', [ReporteController::class, 'reportePedimentos'])
        ->middleware('report.access:pedimentos')
        ->name('reportes.pedimentos');
    Route::get('/reportes/pedimentos/pdf', [ReporteController::class, 'reportePedimentosPdf'])
        ->middleware('report.access:pedimentos')
        ->name('reportes.pedimentos.pdf');

    // --- Reportes Especiales ---
// Route::get('/reportessemanas', [ReporteController::class, 'expsem']);
    Route::get('/reportes/calendario-primeras-operaciones', [ReporteController::class, 'calendarioPrimerasOperaciones'])
        ->name('reportes.calendario-primeras-operaciones');
});

// --- Envío de Reportes por Correo ---
Route::prefix('reportes')->middleware('auth')->group(function () {
    Route::get('cliente-mail', [ReporteClienteMailController::class, 'index'])
        ->middleware('report.access:clientes_pdf')
        ->name('reportes.cliente.mail');
    Route::post('cliente-mail/enviar', [ReporteClienteMailController::class, 'enviar'])
        ->middleware('report.access:clientes_pdf')
        ->name('reportes.cliente.mail.enviar');
    Route::post('cliente-mail/procesar', [ReporteClienteMailController::class, 'procesar'])
        ->middleware('report.access:clientes_pdf')
        ->name('reportes.cliente.mail.procesar');
    Route::post('cliente-mail/enviar-masivo', [ReporteClienteMailController::class, 'enviarMasivo'])
        ->middleware('report.access:clientes_pdf')
        ->name('reportes.cliente.mail.enviar-masivo');
    Route::post('cliente-mail/preview', [ReporteClienteMailController::class, 'preview'])
        ->middleware('report.access:clientes_pdf')
        ->name('reportes.cliente.mail.preview');
    Route::post('cliente-mail/pdf', [ReporteClienteMailController::class, 'generarPDF'])
        ->middleware('report.access:clientes_pdf')
        ->name('reportes.cliente.mail.pdf');
});

// ============================================================================
// IMPORTACIÓN DE OPERACIONES (Excel)
// ============================================================================

Route::middleware(['auth'])->group(function () {
    Route::get('importar_excel', [OperacionImportController::class, 'index'])->name('operaciones.import.index');
    Route::post('importar_excel', [OperacionImportController::class, 'import'])->name('operaciones.import.store');
    Route::get('/importar_excel/log/{nombreArchivo}', [OperacionImportController::class, 'descargarLog'])
        ->name('operaciones.import.log');
});

// ============================================================================
// NOTIFICACIONES
// ============================================================================

Route::middleware(['auth'])->prefix('notificaciones')->name('notificaciones.')->group(function () {
    // API para AJAX
    Route::get('/no-leidas', [NotificacionController::class, 'noLeidas'])->name('noLeidas');
    Route::get('/nuevas', [NotificacionController::class, 'nuevas'])->name('nuevas');
    Route::post('/{id}/marcar-leida', [NotificacionController::class, 'marcarLeida'])->name('marcarLeida');
    Route::post('/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasLeidas'])->name('marcarTodasLeidas');

    // Vista completa
    Route::get('/', [NotificacionController::class, 'index'])->name('index');
});

// ============================================================================
// BORDER STATUS (Estado de cruce)
// ============================================================================

Route::middleware(['auth'])->group(function () {
    Route::get('/border-status', [BorderStatusController::class, 'index'])->name('border.status');
    Route::post('/border-status/check', [BorderStatusController::class, 'check'])->name('border.status.check');
});

// ============================================================================
// RECORRIDOS
// ============================================================================

Route::middleware(['auth'])->group(function () {
    Route::post('/recorridos', [RecorridoController::class, 'store'])->name('recorridos.store');
});

// ============================================================================
// GRÁFICOS Y ANALÍTICAS
// ============================================================================

Route::middleware(['auth'])->group(function () {
    Route::get('/graficos', [OperacionController::class, 'grafico'])->name('graficos.index');
});

// ============================================================================
// DASHBOARDS DE CLIENTES
// ============================================================================

Route::middleware(['auth'])->group(function () {
    Route::get('dashcliente', [DashboardController::class, 'indexcliente'])->name('cliente.admindashboard2');
    Route::get('dashcliente2', [DashboardController::class, 'admincliente'])->name('cliente.admindashboard');
    Route::get('dashcliente/operaciones', [DashboardController::class, 'operacionescliente'])->name('cliente.operacionescliente');
});

// ============================================================================
// NOTIFICACIONES DEL SISTEMA
// ============================================================================

Route::middleware(['auth'])->prefix('api/notificaciones-sistema')->name('notificaciones.sistema.')->group(function () {
    Route::get('/no-leidas', [NotificacionesSistemaController::class, 'obtenerNoLeidas'])->name('no-leidas');
    Route::post('/{id}/marcar-leida', [NotificacionesSistemaController::class, 'marcarLeida'])->name('marcar-leida');
    Route::post('/marcar-todas', [NotificacionesSistemaController::class, 'marcarTodasLeidas'])->name('marcar-todas');
});

// ============================================================================
// API BOT DODA v2 (Multi-Tenant)
// ============================================================================

Route::prefix('api/bot/doda')->group(function () {
    Route::get('/ejecutar', [DodaBotController::class, 'ejecutar']);
    Route::get('/status', [DodaBotController::class, 'status']);
    Route::get('/health', [DodaBotController::class, 'health']);
    Route::get('/tenants-automaticos', [DodaBotController::class, 'tenantsAutomaticos']);
    Route::post('/ejecutar-tenant/{tenantId}', [DodaBotController::class, 'ejecutarTenant']);
    Route::post('/rollover-dates', [DodaBotController::class, 'actualizarFechasRezagadas']);
    Route::post('/rollover-pendientes', [DodaBotController::class, 'rolloverOperacionesPendientes']);
});
