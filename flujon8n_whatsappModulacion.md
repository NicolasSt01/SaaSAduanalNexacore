# Flujo n8n — Notificación de Modulación por WhatsApp

> **Referencia:** INC-025 — Documento de diseño y arquitectura

---

## 1. Objetivo

Notificar cambios de modulación por WhatsApp usando **n8n + Evolution API** como sistema complementario al correo electrónico (ya cubierto por Laravel en INC-023/024).

- **WhatsApp**: n8n + Evolution API (1 sola instalación, múltiples instancias por tenant)
- **Email**: Laravel (INC-023/024 ya corregido y funcionando)
- **Un solo workflow n8n** para todos los tenants → credenciales viajan en el payload

---

## 2. Arquitectura General

```
┌──────────────────────────────────────────────────────────────────┐
│ 1 SOLA INSTALACIÓN DE EVOLUTION API  │  multi-instancia          │
│                                      │                           │
│  ┌──────────┐  ┌──────────────┐      ┌──────────────────────┐   │
│  │ Tenant 1 │  │ Tenant 2     │ ...  │ Tenant N             │   │
│  │ crosspt1 │  │ agencia_abc  │      │ agencia_xyz          │   │
│  │ +52 899..│  │ +52 555..    │      │ +52 818..            │   │
│  └──────────┘  └──────────────┘      └──────────────────────┘   │
│                                                                  │
│  POST /instance/crosspt1/message/sendText?apikey=<global_key>    │
│  POST /instance/agencia_abc/message/sendText?apikey=<global_key> │
└──────────────────────────────────────────────────────────────────┘
                                    ▲
                                    │ HTTP (payload trae instance + base_url)
                                    │
┌───────────────────────────────────┴──────────────────────────────┐
│                        N8N (1 solo workflow)                      │
│                                                                    │
│  Webhook ← POST /modulacion-whatsapp  (Laravel → n8n)            │
│  Code → Validar payload                                            │
│  Code → Formatear mensaje según template del tenant               │
│  HTTP Request → Evolution API (sendText dinámico por instance)    │
│  Respond to Webhook → { success, enviados, errores }              │
└───────────────────────────────────┬──────────────────────────────┘
                                    │
                                    │ HTTP POST con payload
                                    │
┌───────────────────────────────────┴──────────────────────────────┐
│                        LARAVEL (NexaCore)                         │
│                                                                    │
│  DodaConsultaService → detecta modulación                        │
│  NotificacionModulacionService → email (local, INC-024)          │
│  NotificacionWhatsAppService → HTTP → n8n webhook (INC-025)      │
│                                                                    │
│  Config por tenant (en tenants.configuracion):                    │
│  ┌─────────────────────────────────────────────────────────┐     │
│  │ evolution_api:                                           │     │
│  │   instance: "crosspoint_main"    ← nombre de instancia   │     │
│  │   connected: true                ← ¿sesión activa?       │     │
│  │   connected_at: "2026-05-23"                             │     │
│  │ n8n_webhook_url: "https://n8n.../modulacion-whatsapp"    │     │
│  │ n8n_webhook_token: "tok_abc123"                          │     │
│  └─────────────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────────────┘
```

**Claves de arquitectura:**
- `base_url` y `api_key` de Evolution API son **globales** (`.env` de Laravel) — mismas para todos los tenants
- `instance` es lo **único que varía por tenant**
- El payload de Laravel a n8n incluye `instance` + `base_url` para que el HTTP Request sea dinámico

---

## 3. Onboarding WhatsApp por Tenant

### 3.1 Flujo de activación

```
┌─────────────────────────────────────────────────────────────────────┐
│            FLUJO DE ONBOARDING WHATSAPP POR TENANT                   │
│                                                                      │
│  1. Admin del tenant va a /configuracion-whatsapp                    │
│                                                                      │
│  2. NexaCore → Evolution API:  POST /instance/create                │
│     { instanceName: "tenant_" + tenant.id,                          │
│       token: "<api_key_global>" }                                   │
│                                                                      │
│  3. NexaCore → Evolution API:  GET /instance/connect/tenant_X       │
│     ← { qr: "data:image/png;base64,iVBORw0..." }                   │
│                                                                      │
│  4. Se muestra QR en pantalla. Admin escanea con WhatsApp.          │
│                                                                      │
│  5. NexaCore → Evolution API:  GET /instance/connectionState/tenant_X│
│     ← { state: "open" }                                            │
│                                                                      │
│  6. Guardar en tenant.configuracion:                                 │
│     evolution_api.instance = "tenant_1"                             │
│     evolution_api.connected = true                                  │
│                                                                      │
│  7. Admin ya puede usar el botón "Sincronizar Grupos"               │
│     para registrar grupos en el directorio.                         │
└─────────────────────────────────────────────────────────────────────┘
```

### 3.2 Panel `/configuracion-whatsapp`

Vista Blade Laravel con 3 estados:

| Estado | UI |
|--------|-----|
| **No configurado** | Botón "Conectar WhatsApp" → dispara creación de instancia + QR |
| **Esperando escaneo** | Muestra QR + botón "Verificar conexión" + spinner |
| **Conectado** | Muestra número conectado, botón "Desconectar", botón "Sincronizar grupos" |

**Endpoints Laravel nuevos:**

```php
// Crear instancia y obtener QR
POST /admin/whatsapp/conectar
→ Evolution API: POST /instance/create + GET /instance/connect/{instance}
← { qr: "base64...", instance: "tenant_3" }

// Verificar estado de conexión
POST /admin/whatsapp/estado
→ Evolution API: GET /instance/connectionState/{instance}
← { connected: true, instance: "tenant_3" }

// Desconectar (logout de WhatsApp)
POST /admin/whatsapp/desconectar
→ Evolution API: DELETE /instance/logout/{instance}
← { success: true }

// Sincronizar grupos
POST /admin/whatsapp/grupos
→ Evolution API: GET /group/fetchAll/{instance}?getParticipants=true
← { grupos: [{id, subject, participants}, ...] }
```

### 3.3 Service Layer en Laravel

```php
class EvolutionApiService
{
    // Config global desde .env
    protected string $baseUrl;   // EVOLUTION_API_BASE_URL
    protected string $apiKey;    // EVOLUTION_API_KEY

    // --- Operaciones por tenant ---
    public function createInstance(string $instanceName): array;
    public function getQr(string $instance): array;
    public function getConnectionState(string $instance): array;
    public function logout(string $instance): array;
    public function fetchGroups(string $instance): array;
    public function deleteInstance(string $instance): array;
}
```

---

## 4. Payload del Webhook (Laravel → n8n)

Payload simplificado — sin SMTP (email lo maneja Laravel), solo WhatsApp:

```json
{
  "evolution_api": {
    "base_url": "https://evo.nexacore.com.mx",
    "api_key": "B6D711FCDEF4E...",
    "instance": "crosspoint_main"
  },
  "tenant": {
    "id": 1,
    "nombre_empresa": "Crosspoint",
    "plantilla_modulacion": "basica_azul"
  },
  "operacion": {
    "id": 28,
    "referencia": "2605190",
    "num_factura": "685",
    "nombre_producto": "AGUACATE",
    "num_thermo": "CTR74",
    "codigo_alpha": "ABC123",
    "num_doda": "142601766",
    "modulacion": "DESADUANAMIENTO LIBRE",
    "fecha_modulacion": "2026-05-23T15:42:00"
  },
  "destinatarios_whatsapp": [
    {
      "nombre": "Nicolas Salas",
      "numero": "528991610219"
    },
    {
      "nombre": "Operaciones Frutival",
      "numero": "521234567890@g.us"
    }
  ]
}
```

**Nota:** Solo se envían destinatarios que tengan `whatsapp` informado Y `canal_preferido` compatible con WhatsApp.

---

## 5. Workflow n8n (1 solo flujo para todos los tenants)

### 5.1 Nodos necesarios

| # | Nodo | Tipo | Descripción |
|---|------|------|-------------|
| 1 | **Webhook** | `n8n-nodes-base.webhook` | POST /modulacion-whatsapp |
| 2 | **Code: Validar** | `n8n-nodes-base.code` | Valida payload, enriquece con template/emoji |
| 3 | **SplitInBatches** | `n8n-nodes-base.splitInBatches` | Itera destinatarios (batchSize=1) |
| 4 | **Code: Mensaje** | `n8n-nodes-base.code` | Construye texto WhatsApp según plantilla |
| 5 | **HTTP: Evolution** | `n8n-nodes-base.httpRequest` | POST a Evolution API sendText |
| 6 | **Code: Acumular** | `n8n-nodes-base.code` | Acumula resultados en StaticData |
| 7 | **Respond** | `n8n-nodes-base.respondToWebhook` | Responde JSON con resumen |

### 5.2 Diagrama

```
Webhook (POST)       Code: Validar       SplitInBatches (loop)
┌──────────┐         ┌──────────┐        ┌──────────────────────┐
│ POST     │────────▶│ Valida   │───────▶│ main[1] (por contacto)│──┐
│ /modul...│         │ payload  │        │                      │  │
└──────────┘         └──────────┘        │ main[0] (al final)   │  │
                                         └──────────┬───────────┘  │
                                                    │              │
                    ┌───────────────────────────────┘              │
                    ▼                                              ▼
              Respond to Webhook                          ┌──────────────┐
              {success,enviados}                          │ Code: Mensaje│
                                                          │ (template WA)│
                                                          └──────┬───────┘
                                                                 │
                                                                 ▼
                                                          ┌──────────────┐
                                                          │ HTTP Request │
                                                          │ Evolution API│
                                                          │ POST sendText│
                                                          └──────┬───────┘
                                                                 │
                                                                 ▼
                                                          ┌──────────────┐
                                                          │ Code: Acumul.│
                                                          │ StaticData   │
                                                          └──────────────┘
                                                                 │
                                                                 └──► loop back
```

### 5.3 Código de los nodos

#### Nodo 2: Code "Validar y Preparar"

```javascript
const body = $input.first().json;

if (!body.destinatarios_whatsapp?.length) {
  throw new Error('Sin destinatarios WhatsApp');
}

const op = body.operacion;
const esVerde = op.modulacion.toUpperCase().includes('LIBRE');
const template = body.tenant.plantilla_modulacion || 'basica_azul';

const fecha = new Date(op.fecha_modulacion).toLocaleString('es-MX', {
  day: '2-digit', month: 'short', year: 'numeric',
  hour: '2-digit', minute: '2-digit'
});

return body.destinatarios_whatsapp.map(d => ({
  json: {
    evolution_api: body.evolution_api,
    tenant: body.tenant,
    operacion: op,
    destinatario: d,
    template,
    esVerde,
    emoji: esVerde ? '🟢' : '🔴',
    fecha_formateada: fecha
  }
}));
```

#### Nodo 4: Code "Mensaje WhatsApp"

```javascript
const item = $input.first().json;
const op = item.operacion;
const t = item.tenant;

let mensaje;

switch (item.template) {
  case 'moderna_verde':
    mensaje = `${item.emoji} MODULACIÓN FINALIZADA ${item.emoji}\n\n` +
      `✓ Estatus: ${op.modulacion}\n` +
      `✓ Factura: ${op.num_factura}\n` +
      `✓ Producto: ${op.nombre_producto}\n` +
      `✓ Referencia: ${op.referencia}\n` +
      `✓ Fecha: ${item.fecha_formateada}\n\n` +
      `${t.nombre_empresa}`;
    break;

  case 'elegante_oscura':
    mensaje = `┌─────────────────────────┐\n` +
      `│  AVISO OPERATIVO        │\n` +
      `│  ${t.nombre_empresa.padEnd(21)}│\n` +
      `└─────────────────────────┘\n\n` +
      `  RESOLUCIÓN ADUANAL\n` +
      `  ${op.modulacion} ${item.emoji}\n\n` +
      `  Estimado/a ${item.destinatario.nombre},\n\n` +
      `  Factura: ${op.num_factura}\n` +
      `  Producto: ${op.nombre_producto}\n` +
      `  Referencia: ${op.referencia}\n` +
      `  Fecha: ${item.fecha_formateada}\n\n` +
      `  Cordialmente,\n` +
      `  ${t.nombre_empresa}`;
    break;

  default: // basica_azul
    mensaje = `${item.emoji} *Actualización de Trámite - ${t.nombre_empresa}*\n\n` +
      `*Estado:* ${op.modulacion} ${item.emoji}\n` +
      `*Su trámite ha sido ${item.esVerde ? 'completado exitosamente' : 'procesado'}*\n\n` +
      `📋 *Detalles del trámite:*\n` +
      `━━━━━━━━━━━━━━━━━━━━\n` +
      `📄 *Factura:* ${op.num_factura}\n` +
      `🔑 *Referencia:* ${op.referencia}\n` +
      `📦 *Producto:* ${op.nombre_producto}\n` +
      `🚛 *No. Económico:* ${op.num_thermo}\n` +
      `━━━━━━━━━━━━━━━━━━━━\n\n` +
      `${t.nombre_empresa} - Agencia Aduanal`;
}

return [{ json: { ...item, mensaje_whatsapp: mensaje } }];
```

#### Nodo 5: HTTP Request → Evolution API

```
Method: POST
URL: {{ $json.evolution_api.base_url }}/message/sendText/{{ $json.evolution_api.instance }}
Authentication: None
Headers:
  apikey: {{ $json.evolution_api.api_key }}
  Content-Type: application/json
Body (JSON):
{
  "number": "{{ $json.destinatario.numero }}",
  "text": "{{ $json.mensaje_whatsapp }}",
  "delay": 1200
}
```

#### Nodo 6: Code "Acumular Resultados"

```javascript
const item = $input.first().json;
const static = $getWorkflowStaticData('global');

if (!static.resultados) {
  static.resultados = { enviados: 0, errores: 0, detalles: [] };
}
static.resultados.enviados++;
static.resultados.detalles.push({
  nombre: item.destinatario.nombre,
  numero: item.destinatario.numero
});

return [$input.first()];
```

#### Nodo 7: Respond to Webhook

```javascript
const static = $getWorkflowStaticData('global');

return [{
  json: {
    success: true,
    enviados: static.resultados?.enviados || 0,
    errores: static.resultados?.errores || 0,
    detalles: static.resultados?.detalles || []
  }
}];
```

---

## 6. Sincronización de Grupos de WhatsApp

### 6.1 Workflow n8n: whatsapp-grupos-sync

```
Webhook (POST /whatsapp-grupos-sync)
  → HTTP Request: Evolution API  GET /group/fetchAll/{instance}?getParticipants=true
  → Code: Formatear respuesta  [{id, subject, participants}]
  → Respond to Webhook
```

### 6.2 Integración en Laravel

1. Admin va a `/directorio`, modal de crear/editar contacto
2. Botón "Sincronizar Grupos WhatsApp"
3. Laravel → `EvolutionApiService::fetchGroups($tenant->configInstance())`
4. Respuesta: `[{id: "521234567890@g.us", subject: "Operaciones Frutival", participants: 15}, ...]`
5. Se muestra select en el modal para elegir grupo o número individual
6. Se guarda en `directorio.whatsapp`

---

## 7. Modificaciones necesarias en Laravel

### 7.1 Nuevos archivos

| Archivo | Descripción |
|---------|-------------|
| `app/Services/EvolutionApiService.php` | Wrapper para Evolution API (createInstance, getQr, sendText, fetchGroups, etc.) |
| `app/Services/NotificacionWhatsAppService.php` | Envía notificación WhatsApp → HTTP POST a n8n |
| `app/Http/Controllers/Admin/WhatsAppController.php` | Endpoints para el panel `/configuracion-whatsapp` |
| `resources/views/admin/config/whatsapp.blade.php` | Panel de configuración WhatsApp por tenant |

### 7.2 Variables .env

```env
# Evolution API (1 sola instalación para todos los tenants)
EVOLUTION_API_BASE_URL=https://evo.nexacore.com.mx
EVOLUTION_API_KEY=B6D711FCDEF4E...

# n8n webhook global (mismo workflow para todos los tenants)
N8N_MODULACION_WHATSAPP_URL=https://n8n.nexacore.com.mx/webhook/modulacion-whatsapp
N8N_MODULACION_WHATSAPP_TOKEN=tok_abc123
```

### 7.3 Configuración JSON del tenant

```json
{
  "evolution_api": {
    "instance": "tenant_1",
    "connected": true,
    "connected_at": "2026-05-23 15:00:00"
  },
  "notificaciones": {
    "whatsapp_habilitado": true
  }
}
```

### 7.4 Modificar NotificacionModulacionService

Agregar despacho a WhatsApp después del email:

```php
// Email (sistema actual, INC-024 ya corregido)
$this->despacharNotificacionExterna(...);

// WhatsApp (nuevo, INC-025)
if ($tenant->whatsappHabilitado()) {
    app(NotificacionWhatsAppService::class)->notificar(
        $operacion,
        $nuevoEstatus,
        $destinatarios
    );
}
```

### 7.5 Nuevas rutas

```php
// Panel de configuración WhatsApp
Route::get('/configuracion-whatsapp', [WhatsAppController::class, 'index'])
    ->name('admin.config.whatsapp');
Route::post('/admin/whatsapp/conectar', [WhatsAppController::class, 'conectar']);
Route::post('/admin/whatsapp/estado', [WhatsAppController::class, 'estado']);
Route::post('/admin/whatsapp/desconectar', [WhatsAppController::class, 'desconectar']);
Route::post('/admin/whatsapp/grupos', [WhatsAppController::class, 'grupos']);
```

### 7.6 Directorio: soporte para grupos

Agregar al modal de directorio:
- Select con opción "Número individual" o "Grupo de WhatsApp"
- Al seleccionar "Grupo", mostrar grupos sincronizados
- El campo `whatsapp` guarda el ID del grupo (con `@g.us`)

---

## 8. Resumen

### Lo que NO cambia

- El sistema de email ya funciona correctamente (INC-023/024)
- El flujo del bot DODA (detección de modulación)
- El directorio de contactos (solo se agrega soporte para grupos)

### Lo que se agrega

| Componente | Tipo | Descripción |
|------------|------|-------------|
| Evolution API | Infraestructura | 1 servidor, N instancias (1 por tenant) |
| n8n workflow | Automatización | 1 solo workflow, payload incluye credenciales dinámicas |
| WhatsAppController | Laravel | Panel `/configuracion-whatsapp` por tenant |
| EvolutionApiService | Laravel | Cliente PHP para Evolution API |
| NotificacionWhatsAppService | Laravel | Despacho a n8n con fallback |

### Ventajas del diseño

1. **1 solo workflow n8n** para todos los tenants
2. **1 sola instalación Evolution API** con multi-instancia
3. **Sin credenciales duplicadas** — api_key global, instance por tenant
4. **Email sigue en Laravel** — ya funciona y no se toca
5. **Tenant auto-gestiona** su WhatsApp (QR, conexión, grupos)
6. **Fallback local** si n8n no está disponible
