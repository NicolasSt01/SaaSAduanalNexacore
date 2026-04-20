# 🤖 SOIA-Bot - Modo Manual vs Automático

## 📋 Descripción

El sistema SOIA-Bot ahora soporta dos modos de operación configurables por tenant:

1. **Modo Manual**: El usuario puede consultar la modulación manualmente desde su dashboard
2. **Modo Automático**: El bot se ejecuta automáticamente via cron job (sin intervención del usuario)

## 🎯 Configuración por Tenant

Como **Super Admin**, puedes configurar el modo del bot para cada tenant en la sección de configuración del SOIA-Bot.

### Ubicación de Configuración

En el panel de administración de tenants, busca la sección **"Configuración del SOIA-Bot"**.

### Opciones Disponibles

```php
// En la configuración del tenant (campo JSON 'configuracion')
[
    'bot' => [
        'mode' => 'manual', // 'manual', 'automatico', 'deshabilitado'
        'consultas_limite_mes' => 100, // Límite de consultas por mes
        'consultas_mes' => 0, // Contador actual
        'consultas_mes_periodo' => '2026-04', // Periodo actual
    ],
]
```

### Modos del Bot

| Modo | Descripción | Cuándo Usar |
|------|-------------|-------------|
| `manual` | El usuario debe consultar manualmente desde el dashboard | Planes básicos, trial, o cuando el cliente quiere control |
| `automatico` | El bot se ejecuta automáticamente via cron job | Planes premium, o cuando tú como empresa gestionas el bot |
| `deshabilitado` | El bot está desactivado | Tenants sin acceso al bot |

## 🖥️ Interfaz del Usuario

### Modo Manual

Cuando un tenant tiene el modo `manual`, los usuarios verán:

**En el Dashboard del Documentador:**
```
┌─────────────────────────────────────────────────────┐
│ Panel Operativo 🛰️ EN VIVO                          │
├─────────────────────────────────────────────────────┤
│ Operaciones del Día                                 │
│ [🤖 Consultar Modulación] [+ Nueva Operación]       │
└─────────────────────────────────────────────────────┘
```

El botón **"Consultar Modulación"** está visible y permite:
- Ejecutar la consulta masiva de modulaciones
- Ver el progreso en tiempo real
- Recibir un resumen de cambios detectados

### Modo Automático

Cuando un tenant tiene el modo `automatico`, los usuarios **NO** verán el botón:
```
┌─────────────────────────────────────────────────────┐
│ Panel Operativo 🛰️ EN VIVO                          │
├─────────────────────────────────────────────────────┤
│ Operaciones del Día                                 │
│ [+ Nueva Operación]                                 │
└─────────────────────────────────────────────────────┘
```

La modulación se actualiza automáticamente via cron job.

## 🔧 Implementación Técnica

### Controller (DocumentadorController.php)

```php
public function dashboardDocumentador()
{
    $user = auth()->user();
    $tenant = $user->tenant;
    
    // Obtener configuración del SOIA-Bot
    $botMode = $tenant ? $tenant->getBotMode() : 'deshabilitado';
    $botEnabled = $tenant ? $tenant->isBotEnabled() : false;
    $botAutomatic = $tenant ? $tenant->isBotAutomatic() : false;

    return view('documentador.dashboard', compact('botMode', 'botEnabled', 'botAutomatic'));
}
```

### Vista (dashboard.blade.php)

```blade
@if($botEnabled && !$botAutomatic)
<!-- Botón de consulta manual de modulación -->
<button onclick="consultarModulacionManual()" id="btn_consulta_modulacion"
    class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 ...">
    <i class="fas fa-robot"></i> 
    <span>Consultar Modulación</span>
</button>
@endif
```

### JavaScript

La función `consultarModulacionManual()`:
1. Muestra un modal de carga con animación
2. Llama al endpoint `/api/bot/doda/ejecutar`
3. Muestra resultados (éxito o error)
4. Recarga automáticamente los datos de la página

## 📊 Flujo de Consulta Manual

```
Usuario hace clic en "Consultar Modulación"
  ↓
Confirmación (¿Deseas consultar...?)
  ↓
Modal de carga: "El SOIA-Bot está consultando..."
  ↓
Petición a: GET /api/bot/doda/ejecutar?token=xxx
  ↓
Respuesta del servidor
  ↓
Modal de éxito: 
  - Operaciones consultadas: 45
  - Cambios detectados: 3
  - Errores: 0
  ↓
Recarga automática de la página (2 segundos)
```

## 🔒 Seguridad

- ✅ El endpoint requiere autenticación por token (`CHECK_TRAFICO_TOKEN`)
- ✅ Anti-concurrencia (evita ejecuciones simultáneas)
- ✅ Rate limiting por IP
- ✅ Logging dedicado en `storage/logs/doda_bot.log`

## 📝 Métodos Disponibles en el Modelo Tenant

```php
// Obtener el modo del bot
$tenant->getBotMode(); // 'manual', 'automatico', 'deshabilitado'

// Verificar si está habilitado
$tenant->isBotEnabled(); // true/false

// Verificar si es automático
$tenant->isBotAutomatic(); // true/false

// Verificar límites
$tenant->canMakeBotConsulta(); // true/false
```

## 🎨 Personalización del Botón

El botón tiene las siguientes características visuales:

- **Color**: Gradiente de púrpura a índigo
- **Icono**: Robot (fas fa-robot)
- **Ubicación**: Header de operaciones, junto al botón "Nueva Operación"
- **Estado**: Se deshabilita y muestra spinner durante la consulta

## 🧪 Testing

### Probar el botón manualmente

1. Inicia sesión como usuario de un tenant con modo `manual`
2. Ve al dashboard del documentador (`/documentador/dashboard`)
3. Deberías ver el botón "Consultar Modulación"
4. Haz clic y verifica que:
   - Aparece el modal de carga
   - La consulta se ejecuta correctamente
   - Aparece el modal de éxito con estadísticas
   - La página se recarga automáticamente

### Probar con tenant en modo automático

1. Cambia el tenant a modo `automatico`
2. Ve al dashboard del documentador
3. El botón **NO** debe aparecer

## 📌 Notas Importantes

1. **Token del Bot**: Asegúrate de que `CHECK_TRAFICO_TOKEN` esté configurado en `.env`
2. **Límites**: El tenant puede tener un límite de consultas por mes
3. **Logs**: Todas las consultas se registran en `storage/logs/doda_bot.log`
4. **Permisos**: Solo usuarios autenticados pueden ejecutar la consulta manual

## 🚀 Configuración Recomendada por Plan

| Plan | Modo | Consultas/Mes | Descripción |
|------|------|---------------|-------------|
| Trial | Manual | 20 | El usuario consulta manualmente |
| Básico | Manual | 50 | Control del usuario |
| Profesional | Automático | 200 | Gestión automática por ti |
| Enterprise | Automático | Ilimitado | Máximo rendimiento |

## 🔄 Cambiar Modo del Bot

Como Super Admin, puedes cambiar el modo:

### Desde la UI de Administración

1. Ve a Configuración del Tenant
2. Busca "Configuración del SOIA-Bot"
3. Cambia el dropdown "Modo del Bot"
4. Guarda los cambios

### Programáticamente

```php
$tenant->updateConfig('bot.mode', 'automatico');
```

## 💡 Tips

- **Modo Manual**: Ideal para clientes que quieren control total
- **Modo Automático**: Mejor para clientes que prefieren automatización
- **Cron Job**: Configúralo a las 23:50 para rollover de fechas y 00:00 para consulta masiva
- **Monitoreo**: Revisa los logs para verificar ejecuciones exitosas
