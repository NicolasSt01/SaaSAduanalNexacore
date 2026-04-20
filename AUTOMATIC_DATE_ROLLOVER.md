# 🔄 Automatic Date Rollover - Operaciones Rezagadas

## 📋 Descripción

Sistema automático que actualiza la `fecha_cruce_estimada` de las operaciones que no han logrado modulacion y cuya fecha estimada ya pasó. Esto evita que las operaciones se queden rezagadas en fechas anteriores sin actualización de DODA.

## 🎯 Objetivo

- **Ejecución diaria**: 23:50 PM (configurable)
- **Lógica**: Si una operación no tiene modulacion y su `fecha_cruce_estimada` es menor o igual a hoy, se actualiza al día siguiente
- **Automático**: Se ejecuta via cron job en el VPS o Laravel Task Scheduler

## 🚀 Implementación

### 1. Endpoint API

**Ruta**: `POST /api/bot/doda/rollover-dates?token={token}`

**Autenticación**: Mismo token que el bot de consulta (`CHECK_TRAFICO_TOKEN`)

**Método**: `actualizarFechasRezagadas()` en `DodaBotController`

### 2. Lógica del Proceso

```
1. Buscar operaciones donde:
   - fecha_cruce_estimada <= hoy
   - NO tienen modulacion (o está vacía, o es "0", etc.)
   - estado != 'cerrada' (opcional)

2. Para cada operación encontrada:
   - fecha_cruce_estimada = fecha_cruce_estimada + 1 día

3. Retornar estadísticas:
   - total_actualizadas
   - operaciones_actualizadas (IDs)
   - errores (si los hay)
```

### 3. Configuración del Cron Job

#### Opción A: Laravel Task Scheduler (Recomendado)

En `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Actualizar fechas de operaciones rezagadas - 23:50 diario
    $schedule->call(function () {
        Http::post(config('app.url') . '/api/bot/doda/rollover-dates', [
            'token' => env('CHECK_TRAFICO_TOKEN'),
        ]);
    })->dailyAt('23:50');
}
```

Y en el VPS, agregar al crontab:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

#### Opción B: Cron Job Directo en VPS

```bash
50 23 * * * curl -X POST "https://tudominio.com/api/bot/doda/rollover-dates?token=TU_TOKEN" >> /var/log/date-rollover.log 2>&1
```

#### Opción C: Windows Task Scheduler (si usas el bot externo)

Configurar una tarea que ejecute:
```powershell
Invoke-RestMethod -Uri "https://tudominio.com/api/bot/doda/rollover-dates?token=TU_TOKEN" -Method POST
```

### 4. Ejemplo de Uso

#### Request
```http
POST /api/bot/doda/rollover-dates?token=9d12f90d...
```

#### Response Exitosa
```json
{
  "success": true,
  "execution_id": "rollover_6605b...",
  "fecha_ejecucion": "2026-04-04T23:50:00.000000Z",
  "total_actualizadas": 15,
  "operaciones_actualizadas": [
    {
      "id": 123,
      "referencia": "45678",
      "fecha_anterior": "2026-04-03",
      "fecha_nueva": "2026-04-04"
    }
  ],
  "errores": []
}
```

#### Response con Error
```json
{
  "success": false,
  "error": "Unauthorized",
  "message": "Token inválido o no proporcionado"
}
```

## 🔒 Seguridad

- ✅ Autenticación por token (mismo que el bot de consulta)
- ✅ Rate limiting aplicado
- ✅ Logging dedicado en `storage/logs/doda_bot.log`
- ✅ Anti-concurrencia (evita ejecuciones simultáneas)

## 📊 Logging

Cada ejecución genera logs en `storage/logs/doda_bot.log`:

```
[2026-04-04 23:50:00] local.INFO: [API] 🔄 Rollover de fechas iniciado {"ip":"127.0.0.1"}
[2026-04-04 23:50:01] local.INFO: [API] ✅ Operación #123 actualizada: 2026-04-03 -> 2026-04-04
[2026-04-04 23:50:01] local.INFO: [API] ✅ Rollover completado: 15 operaciones actualizadas
```

## 🧪 Testing

Puedes probar el endpoint manualmente:

```bash
curl -X POST "http://localhost:8000/api/bot/doda/rollover-dates?token=TU_TOKEN"
```

O desde el panel UI en `/admin/bot-doda` (si agregas el botón de prueba)

## 📝 Notas

- Las operaciones se actualizan **1 día** a la vez
- Se ejecuta **todos los días** a la hora configurada
- Si una operación ya tiene modulacion, **NO** se actualiza su fecha
- El proceso es **multi-tenant** (funciona con el scope global)
- Se puede ejecutar manualmente desde la UI o via API

## 🔄 Flujo Completo Sugerido

```
23:50 - Rollover de fechas (este nuevo endpoint)
  ↓
00:00 - Consulta masiva de modulacion (bot existente)
  ↓
06:00 - Envío de reportes (si aplica)
```

De esta forma, primero se actualizan las fechas y luego el bot consulta la modulacion con las fechas correctas.
