# 🚀 Implementación: Límites del SOIA-Bot + Sistema de Notificaciones

## 📋 Resumen del Problema

**Problema Principal:** El SOIA-Bot no respeta los límites de consultas configurados por tenant.
- Si un tenant tiene `bot_consultas_limite_mes = 4`, el bot debería procesar máximo 4 operaciones ese mes
- Actualmente el bot procesa TODAS las operaciones pendientes sin verificar límites

**Problema Secundario:** No hay sistema de notificaciones para alertar a los tenants cuando alcanzan sus límites.

---

## 🎯 Objetivos de la Implementación

1. ✅ **Respetar límites del bot** por tenant al ejecutar consultas masivas
2. ✅ **Sistema de notificaciones** en navbar para alertas de límites
3. ✅ **Notificaciones inteligentes** según el modo de ejecución del bot
4. ✅ **Botón de upgrade** que redirija a página de planes (temporalmente `#`)

---

## 🔄 Modos de Ejecución del Bot

El SOIA-Bot se puede ejecutar de **2 maneras**:

| Modo | Cómo se ejecuta | Notificaciones al tenant |
|------|----------------|-------------------------|
| **🔧 Manual** | Admin da click en botón "Ejecutar Bot ahora" desde `/admin/bot-doda` | ✅ **Se muestran notificaciones en el navbar** del tenant cuando alcanza límites |
| **🤖 Automático (API)** | Endpoint `/api/bot/doda/ejecutar` llamado por cron job externo | ⚠️ **Solo se notifica UNA vez** cuando alcanza el límite (no spam de notificaciones) |

### 📌 Comportamiento de Notificaciones

#### Modo Manual (Click del Admin)
```
Admin ejecuta bot manualmente
  → Bot procesa operaciones
  → Si tenant alcanza 80% → Notificación "Estás cerca del límite"
  → Si tenant alcanza 90% → Notificación "Muy cerca del límite"
  → Si tenant alcanza 100% → Notificación "Límite alcanzado, actualiza tu plan"
  → Tenant ve 🔔 en navbar con badge rojo
  → Click en "Actualizar Plan" → Redirige a #
```

#### Modo Automático (API/Cron)
```
Cron job ejecuta endpoint API
  → Bot procesa operaciones
  → Si tenant alcanza 100% → NOTIFICACIÓN ÚNICA (una sola vez)
  → Se marca flag 'notificado_limite' en tenant
  → NO se envían más notificaciones hasta el siguiente mes
  → Cuando el tenant entra al sistema, ve 🔔 con la notificación
```

**Importante:** En modo automático, solo se notifica **UNA SOLA VEZ** cuando el tenant alcanza el 100% de su límite. No queremos spam de notificaciones cada vez que el cron job se ejecuta.

---

## 📝 Plan de Implementación (Paso a Paso)

### PASO 1: Modificar `DodaConsultaService` para respetar límites

**Archivo:** `app/Services/DodaConsultaService.php`

**Cambios necesarios:**

```php
// Agregar al inicio del archivo
use App\Services\SistemaNotificacionesService;
use App\Models\NotificacionSistema;

// Agregar propiedad
protected SistemaNotificacionesService $notificacionesService;
protected bool $esEjecucionManual = false;

// Modificar constructor para recibir el servicio
public function __construct(
    NotificacionModulacionService $notificacionService,
    SistemaNotificacionesService $notificacionesService
) {
    $this->notificacionService = $notificacionService;
    $this->notificacionesService = $notificacionesService;
    $this->executionId = uniqid('doda_', true);
}

// Método para marcar como ejecución manual
public function setEjecucionManual(bool $manual = true): void
{
    $this->esEjecucionManual = $manual;
}

// En el método obtenerOperacionesPendientes()
protected function obtenerOperacionesPendientes()
{
    $tenants = Tenant::where('estado', 'activo')
        ->where('bot_mode', '!=', 'deshabilitado')
        ->get();

    $operacionesPendientes = collect();

    foreach ($tenants as $tenant) {
        // Verificar límite de consultas del bot
        $consultasUsadas = $tenant->getBotConsultasUsadas();
        $limite = $tenant->getBotConsultasLimite();

        // Si tiene límite y ya lo alcanzó, saltar este tenant
        if ($limite && $consultasUsadas >= $limite) {
            $this->log('warning', "⚠️ Tenant {$tenant->nombre_empresa} alcanzó límite de consultas", [
                'limite' => $limite,
                'usadas' => $consultasUsadas,
            ]);
            
            // NOTIFICACIÓN SEGÚN MODO DE EJECUCIÓN:
            if ($this->esEjecucionManual) {
                // Modo MANUAL: Crear notificación completa para el tenant
                $this->notificacionesService->crearNotificacion(
                    $tenant->id,
                    'bot_limit_reached',
                    '🚫 Límite de SOIA-Bot alcanzado',
                    "Has alcanzado tu límite de {$limite} consultas al SOIA-Bot este mes. Ya se procesaron {$consultasUsadas} operaciones. Actualiza tu plan para continuar usando el bot.",
                    'error',
                    '#',
                    'Actualizar Plan Ahora',
                    ['consultas_usadas' => $consultasUsadas, 'limite' => $limite]
                );
            } else {
                // Modo AUTOMÁTICO: Solo notificar UNA vez
                $notificacionExistente = NotificacionSistema::where('tenant_id', $tenant->id)
                    ->where('tipo', 'bot_limit_reached')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->first();
                
                if (!$notificacionExistente) {
                    // Primera vez que alcanza el límite este mes → Notificar
                    $this->notificacionesService->crearNotificacion(
                        $tenant->id,
                        'bot_limit_reached',
                        '🚫 Límite de SOIA-Bot alcanzado',
                        "Has alcanzado tu límite de {$limite} consultas al SOIA-Bot este mes. Se omitieron operaciones pendientes. Actualiza tu plan para continuar usando el bot.",
                        'error',
                        '#',
                        'Actualizar Plan Ahora',
                        ['consultas_usadas' => $consultasUsadas, 'limite' => $limite, 'modo' => 'automatico']
                    );
                    
                    $this->log('info', "📧 Notificación única enviada a {$tenant->nombre_empresa} por límite alcanzado (modo automático)");
                } else {
                    $this->log('info', "🔕 Notificación omitida para {$tenant->nombre_empresa} (ya fue notificado este mes)");
                }
            }
            
            continue;
        }

        // Calcular cuántas consultas restantes tiene
        $consultasRestantes = $limite ? ($limite - $consultasUsadas) : null;

        // Obtener operaciones pendientes de este tenant
        $opsTenant = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('num_doda')
            ->where('num_doda', '!=', '')
            ->where(function ($query) {
                $query->whereNull('modulacion')
                    ->orWhere('modulacion', '')
                    ->orWhere('modulacion', '0')
                    ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                    ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado')
                    ->orWhere('modulacion', 'ERROR DODA NO COINCIDE');
            })
            ->with(['cliente', 'aduana', 'patente', 'tenant'])
            ->get();

        // Si hay límite, limitar las operaciones a procesar
        if ($consultasRestantes !== null && $opsTenant->count() > $consultasRestantes) {
            $opsTenant = $opsTenant->take($consultasRestantes);
            
            $this->log('info', "📊 Limitando operaciones del tenant {$tenant->nombre_empresa}", [
                'disponibles' => $opsTenant->count(),
                'limite_restante' => $consultasRestantes,
            ]);
            
            // Notificar que se están limitando operaciones (solo modo manual)
            if ($this->esEjecucionManual && $consultasRestantes <= ($limite * 0.2)) {
                $this->notificacionesService->crearNotificacion(
                    $tenant->id,
                    'bot_near_limit',
                    '⚠️ Últimas consultas disponibles',
                    "Solo te quedan {$consultasRestantes} consultas al SOIA-Bot este mes. Se procesarán {$opsTenant->count()} operaciones ahora.",
                    'warning',
                    '#',
                    'Ver mi Plan'
                );
            }
        }

        $operacionesPendientes = $operacionesPendientes->merge($opsTenant);
    }

    return $operacionesPendientes;
}
```

---

### PASO 2: Crear tabla de notificaciones

**Crear migración:**
```bash
php artisan make:migration create_notificaciones_sistema_table
```

**Contenido de la migración:**
```php
Schema::create('notificaciones_sistema', function (Blueprint $table) {
    $table->id();
    $table->integer('tenant_id')->nullable(); // Null = notificación global
    $table->string('tipo'); // 'bot_limit_reached', 'bot_near_limit', 'resource_limit', etc.
    $table->string('titulo');
    $table->text('mensaje');
    $table->string('accion_url')->nullable(); // URL del botón de acción
    $table->string('accion_texto')->nullable(); // Texto del botón (ej: "Actualizar Plan")
    $table->enum('nivel', ['info', 'warning', 'error', 'success'])->default('info');
    $table->boolean('leida')->default(false);
    $table->json('metadata')->nullable(); // Datos adicionales
    $table->timestamp('leida_en')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['tenant_id', 'leida']);
    $table->index('tipo');
});
```

**Crear modelo:**
```bash
php artisan make:model NotificacionSistema
```

**Modelo `app/Models/NotificacionSistema.php`:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificacionSistema extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'tipo',
        'titulo',
        'mensaje',
        'accion_url',
        'accion_texto',
        'nivel',
        'leida',
        'leida_en',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'leida_en' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Scope para no leídas
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    // Scope por tenant
    public function scopeParaTenant($query, $tenantId)
    {
        return $query->where(function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
              ->orWhereNull('tenant_id'); // Notificaciones globales
        });
    }

    // Scope por nivel
    public function scopeNivel($query, $nivel)
    {
        return $query->where('nivel', $nivel);
    }
}
```

---

### PASO 3: Crear servicio de notificaciones

**Crear archivo:** `app/Services/SistemaNotificacionesService.php`

```php
<?php

namespace App\Services;

use App\Models\NotificacionSistema;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class SistemaNotificacionesService
{
    /**
     * Crear una notificación para un tenant
     */
    public function crearNotificacion(
        int $tenantId,
        string $tipo,
        string $titulo,
        string $mensaje,
        string $nivel = 'info',
        ?string $accionUrl = null,
        ?string $accionTexto = null,
        array $metadata = []
    ): NotificacionSistema {
        $notificacion = NotificacionSistema::create([
            'tenant_id' => $tenantId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'nivel' => $nivel,
            'accion_url' => $accionUrl,
            'accion_texto' => $accionTexto,
            'metadata' => $metadata,
        ]);

        Log::info('Notificación creada', [
            'tenant_id' => $tenantId,
            'tipo' => $tipo,
            'nivel' => $nivel,
        ]);

        return $notificacion;
    }

    /**
     * Verificar y crear alertas de límite del bot para todos los tenants
     */
    public function verificarLimitesBot(): void
    {
        $tenants = Tenant::where('estado', 'activo')
            ->whereNotNull('bot_consultas_limite_mes')
            ->get();

        foreach ($tenants as $tenant) {
            $limite = $tenant->getBotConsultasLimite();
            if (!$limite) continue;

            $usadas = $tenant->getBotConsultasUsadas();
            $porcentaje = ($usadas / $limite) * 100;

            // Alerta al 80%
            if ($porcentaje >= 80 && $porcentaje < 90) {
                $this->crearNotificacion(
                    $tenant->id,
                    'bot_near_limit',
                    '⚠️ Límite de SOIA-Bot cercano',
                    "Has usado {$usadas} de {$limite} consultas este mes ({$porcentaje}%). Te quedan " . ($limite - $usadas) . " consultas disponibles.",
                    'warning',
                    '#',
                    'Actualizar Plan'
                );
            }

            // Alerta al 90%
            if ($porcentaje >= 90 && $porcentaje < 100) {
                $this->crearNotificacion(
                    $tenant->id,
                    'bot_near_limit',
                    '🚨 Límite de SOIA-Bot muy cercano',
                    "Has usado {$usadas} de {$limite} consultas este mes ({$porcentaje}%). Solo te quedan " . ($limite - $usadas) . " consultas.",
                    'error',
                    '#',
                    'Actualizar Plan Ahora'
                );
            }

            // Alerta al 100%
            if ($porcentaje >= 100) {
                $this->crearNotificacion(
                    $tenant->id,
                    'bot_limit_reached',
                    '🚫 Límite de SOIA-Bot alcanzado',
                    "Has alcanzado tu límite de {$limite} consultas al SOIA-Bot este mes. Actualiza tu plan para continuar usando el bot.",
                    'error',
                    '#',
                    'Actualizar Plan Ahora'
                );
            }
        }
    }

    /**
     * Obtener notificaciones no leídas de un tenant
     */
    public function obtenerNoLeidas(int $tenantId)
    {
        return NotificacionSistema::paraTenant($tenantId)
            ->noLeidas()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener todas las notificaciones recientes de un tenant
     */
    public function obtenerRecientes(int $tenantId, int $limite = 10)
    {
        return NotificacionSistema::paraTenant($tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get();
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida(int $notificacionId): bool
    {
        $notificacion = NotificacionSistema::find($notificacionId);
        
        if (!$notificacion) return false;

        $notificacion->update([
            'leida' => true,
            'leida_en' => now(),
        ]);

        return true;
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasLeidas(int $tenantId): int
    {
        return NotificacionSistema::paraTenant($tenantId)
            ->noLeidas()
            ->update([
                'leida' => true,
                'leida_en' => now(),
            ]);
    }

    /**
     * Contar notificaciones no leídas
     */
    public function contarNoLeidas(int $tenantId): int
    {
        return NotificacionSistema::paraTenant($tenantId)
            ->noLeidas()
            ->count();
    }
}
```

---

### PASO 4: Crear controlador de notificaciones

**Crear archivo:** `app/Http/Controllers/NotificacionesSistemaController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\SistemaNotificacionesService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificacionesSistemaController extends Controller
{
    protected SistemaNotificacionesService $notificacionesService;

    public function __construct(SistemaNotificacionesService $notificacionesService)
    {
        $this->notificacionesService = $notificacionesService;
    }

    /**
     * Obtener notificaciones no leídas (AJAX)
     */
    public function obtenerNoLeidas(): JsonResponse
    {
        if (!auth()->check() || !auth()->user()->tenant) {
            return response()->json(['no_leidas' => 0, 'notificaciones' => []]);
        }

        $tenantId = auth()->user()->tenant_id;
        $noLeidas = $this->notificacionesService->obtenerNoLeidas($tenantId);
        $count = $this->notificacionesService->contarNoLeidas($tenantId);

        return response()->json([
            'no_leidas' => $count,
            'notificaciones' => $noLeidas->map(function($notif) {
                return [
                    'id' => $notif->id,
                    'tipo' => $notif->tipo,
                    'titulo' => $notif->titulo,
                    'mensaje' => $notif->mensaje,
                    'nivel' => $notif->nivel,
                    'accion_url' => $notif->accion_url,
                    'accion_texto' => $notif->accion_texto,
                    'created_at' => $notif->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida($id): JsonResponse
    {
        $this->notificacionesService->marcarLeida($id);
        
        return response()->json(['success' => true]);
    }

    /**
     * Marcar todas como leídas
     */
    public function marcarTodasLeidas(): JsonResponse
    {
        if (!auth()->check() || !auth()->user()->tenant) {
            return response()->json(['success' => false, 'message' => 'Sin tenant']);
        }

        $count = $this->notificacionesService->marcarTodasLeidas(auth()->user()->tenant_id);
        
        return response()->json(['success' => true, 'marcadas' => $count]);
    }
}
```

---

### PASO 5: Agregar rutas de notificaciones

**Agregar en `routes/web.php`:**

```php
// Notificaciones del Sistema
Route::middleware(['auth'])->prefix('api/notificaciones-sistema')->name('notificaciones.sistema.')->group(function () {
    Route::get('/no-leidas', [NotificacionesSistemaController::class, 'obtenerNoLeidas'])->name('no-leidas');
    Route::post('/{id}/marcar-leida', [NotificacionesSistemaController::class, 'marcarLeida'])->name('marcar-leida');
    Route::post('/marcar-todas', [NotificacionesSistemaController::class, 'marcarTodasLeidas'])->name('marcar-todas');
});
```

---

### PASO 6: Agregar icono de notificaciones al navbar

**Modificar:** `resources/views/layouts/app.blade.php`

**Agregar después del botón de configuraciones (antes del perfil):**

```blade
<!-- Notificaciones del Sistema -->
@auth
<div class="relative ml-3" id="notificacionesContainer">
    <button onclick="toggleNotificaciones()" class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none">
        <i class="fas fa-bell text-xl"></i>
        <span id="notificacionesBadge" class="absolute top-0 right-0 block h-5 w-5 rounded-full ring-2 ring-white bg-red-500 text-white text-xs font-bold text-center hidden">
            0
        </span>
    </button>

    <!-- Dropdown de Notificaciones -->
    <div id="notificacionesDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 ring-1 ring-black ring-opacity-5 z-50">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-sm font-bold text-gray-800">Notificaciones</h3>
            <button onclick="marcarTodasLeidas()" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">
                Marcar todas como leídas
            </button>
        </div>
        <div id="notificacionesList" class="max-h-96 overflow-y-auto">
            <!-- Se llena dinámicamente -->
        </div>
    </div>
</div>
@endauth
```

**Agregar JavaScript al final del layout:**

```javascript
<script>
// Sistema de Notificaciones
let notificacionesAbierto = false;

function toggleNotificaciones() {
    notificacionesAbierto = !notificacionesAbierto;
    const dropdown = document.getElementById('notificacionesDropdown');
    
    if (notificacionesAbierto) {
        dropdown.classList.remove('hidden');
        cargarNotificaciones();
    } else {
        dropdown.classList.add('hidden');
    }
}

function cargarNotificaciones() {
    fetch('{{ route("notificaciones.sistema.no-leidas") }}')
        .then(response => response.json())
        .then(data => {
            actualizarBadge(data.no_leidas);
            renderizarNotificaciones(data.notificaciones);
        });
}

function actualizarBadge(count) {
    const badge = document.getElementById('notificacionesBadge');
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function renderizarNotificaciones(notificaciones) {
    const list = document.getElementById('notificacionesList');
    
    if (notificaciones.length === 0) {
        list.innerHTML = `
            <div class="p-6 text-center text-gray-400">
                <i class="fas fa-bell-slash text-3xl mb-2"></i>
                <p class="text-sm">No hay notificaciones</p>
            </div>
        `;
        return;
    }

    const colores = {
        'info': 'bg-blue-50 border-blue-200',
        'warning': 'bg-amber-50 border-amber-200',
        'error': 'bg-red-50 border-red-200',
        'success': 'bg-emerald-50 border-emerald-200',
    };

    const iconos = {
        'info': 'fa-info-circle text-blue-500',
        'warning': 'fa-exclamation-triangle text-amber-500',
        'error': 'fa-times-circle text-red-500',
        'success': 'fa-check-circle text-emerald-500',
    };

    list.innerHTML = notificaciones.map(notif => `
        <div class="p-4 border-b ${colores[notif.nivel] || colores['info']} hover:opacity-80 cursor-pointer"
             onclick="marcarLeida(${notif.id})">
            <div class="flex items-start gap-3">
                <i class="fas ${iconos[notif.nivel] || iconos['info']} mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-800">${notif.titulo}</p>
                    <p class="text-xs text-gray-600 mt-1">${notif.mensaje}</p>
                    ${notif.accion_url ? `
                        <a href="${notif.accion_url}" class="inline-block mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-800">
                            ${notif.accion_texto || 'Ver más'} →
                        </a>
                    ` : ''}
                    <p class="text-xs text-gray-400 mt-1">${notif.created_at}</p>
                </div>
            </div>
        </div>
    `).join('');
}

function marcarLeida(id) {
    fetch(`/api/notificaciones-sistema/${id}/marcar-leida`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        }
    }).then(() => {
        cargarNotificaciones();
    });
}

function marcarTodasLeidas() {
    fetch('/api/notificaciones-sistema/marcar-todas', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        }
    }).then(() => {
        cargarNotificaciones();
    });
}

// Cargar notificaciones cada 30 segundos
setInterval(() => {
    if (!notificacionesAbierto) {
        fetch('{{ route("notificaciones.sistema.no-leidas") }}')
            .then(response => response.json())
            .then(data => {
                actualizarBadge(data.no_leidas);
            });
    }
}, 30000);

// Cerrar dropdown al hacer click fuera
document.addEventListener('click', function(event) {
    const container = document.getElementById('notificacionesContainer');
    if (container && !container.contains(event.target) && notificacionesAbierto) {
        notificacionesAbierto = false;
        document.getElementById('notificacionesDropdown').classList.add('hidden');
    }
});
</script>
```

---

### PASO 7: Crear Job para verificar límites periódicamente

**Crear archivo:** `app/Jobs/VerificarLimitesTenants.php`

```php
<?php

namespace App\Jobs;

use App\Services\SistemaNotificacionesService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class VerificarLimitesTenants implements ShouldQueue
{
    use Queueable;

    public function handle(SistemaNotificacionesService $notificacionesService): void
    {
        $notificacionesService->verificarLimitesBot();
    }
}
```

**Agregar al scheduler (`app/Console/Kernel.php`):**

```php
protected function schedule(Schedule $schedule)
{
    // Verificar límites de tenants cada hora
    $schedule->job(new \App\Jobs\VerificarLimitesTenants())
             ->hourly()
             ->withoutOverlapping();
}
```

---

### PASO 8: Llamar verificación al ejecutar el bot

**Modificar:** `app/Http/Controllers/DodaBotController.php` en método `runLocal()`

```php
public function runLocal(Request $request): JsonResponse
{
    $lockKey = 'doda_bot_running';
    if (Cache::has($lockKey)) {
        $lockInfo = Cache::get($lockKey);
        return response()->json([
            'success' => false,
            'error' => 'Bot ya en ejecución',
            'message' => 'Hay una ejecución en curso.',
            'execution_id_actual' => $lockInfo['execution_id'] ?? null,
        ], 429);
    }

    $executionId = uniqid('doda_ui_', true);
    Cache::put($lockKey, [
        'execution_id' => $executionId,
        'started_at' => now()->toIso8601String(),
        'user_id' => auth()->id(),
    ], 600);

    try {
        $this->logBot('info', '🚀 Ejecución manual iniciada desde Panel UI', [
            'user_id' => auth()->id(),
        ]);

        // IMPORTANTE: Marcar como ejecución manual para notificaciones
        $this->consultaService->setEjecucionManual(true);

        $resultado = $this->consultaService->ejecutarConsultaMasiva();

        Cache::forget($lockKey);

        return response()->json(array_merge(['success' => true], $resultado), 200);

    } catch (Exception $e) {
        Cache::forget($lockKey);
        return response()->json([
            'success' => false,
            'error' => 'Error interno del servidor',
            'message' => $e->getMessage(),
        ], 500);
    }
}
```

**NOTA:** El endpoint de API `/api/bot/doda/ejecutar` **NO** debe llamar a `setEjecucionManual()`, por lo que por defecto será `false` (modo automático) y solo notificará una vez.

---

## 📊 Flujo de Funcionamiento

### Flujo Modo Manual (Click del Admin)
```
1. Super Admin configura límite de bot para tenant (ej: 4 consultas/mes)
   ↓
2. Admin entra a /admin/bot-doda y da click "Ejecutar Bot ahora"
   ↓
3. DodaConsultaService.setEjecucionManual(true) se llama
   ↓
4. Bot verifica límites por tenant
   ↓
5. Si tenant usó 3/4 consultas → Procesa 1 operación restante
   ↓
6. Si tenant usó 4/4 consultas → Omite tenant y crea notificación
   ↓
7. Tenant ve 🔔 en navbar con badge rojo
   ↓
8. Click en notificación → Mensaje: "Límite alcanzado"
   ↓
9. Click en "Actualizar Plan" → Redirige a #
```

### Flujo Modo Automático (API/Cron)
```
1. Super Admin configura límite de bot para tenant (ej: 4 consultas/mes)
   ↓
2. Cron job ejecuta GET /api/bot/doda/ejecutar?token=xxx
   ↓
3. DodaConsultaService.ejecutarConsultaMasiva() se ejecuta (sin setEjecucionManual)
   ↓
4. Bot verifica límites por tenant
   ↓
5. Si tenant usó 4/4 consultas → Omite tenant
   ↓
6. Verifica si ya existe notificación este mes
   ↓
7a. Si NO existe → Crea notificación ÚNICA
7b. Si YA existe → Omite notificación (evita spam)
   ↓
8. Tenant entra al sistema y ve 🔔 con notificación
   ↓
9. Click en "Actualizar Plan" → Redirige a #
```

---

## 🧪 Pruebas a Realizar

### 1. Prueba de límite en Modo Manual
```
- Configurar tenant con límite de 4 consultas
- Admin ejecuta bot manualmente desde /admin/bot-doda
- Verificar que solo procese 4 operaciones
- Verificar que se cree notificación de límite alcanzado
- Admin del tenant ve 🔔 con badge rojo
- Click en notificación → Ve mensaje completo
```

### 2. Prueba de límite en Modo Automático
```
- Configurar tenant con límite de 4 consultas
- Ejecutar bot vía API: GET /api/bot/doda/ejecutar?token=xxx
- Verificar que solo procese 4 operaciones
- Verificar que se cree UNA SOLA notificación
- Ejecutar bot nuevamente por API
- Verificar que NO se cree segunda notificación (ya existe una este mes)
- Tenant ve 🔔 con notificación al entrar al sistema
```

### 3. Prueba de notificaciones
```
- Verificar que el badge aparezca con número correcto
- Click en campana → dropdown se abre
- Ver notificaciones renderizadas correctamente
- Click en "Actualizar Plan" → redirige a "#"
- Marcar como leída → badge desaparece
```

### 4. Prueba de polling
```
- Abrir página en 2 tabs
- Crear notificación manualmente en DB
- Verificar que badge aparece en ambos tabs en < 30 seg
```

---

## 🚀 Comandos para Ejecutar

```bash
# 1. Crear migración
php artisan make:migration create_notificaciones_sistema_table

# 2. Crear modelo
php artisan make:model NotificacionSistema

# 3. Ejecutar migraciones
php artisan migrate

# 4. Limpiar caché
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Probar job manualmente
php artisan tinker
>>> dispatch(new \App\Jobs\VerificarLimitesTenants())
```

---

**Documento creado:** 2026-04-02  
**Versión:** 1.0  
**Estado:** Listo para implementación
