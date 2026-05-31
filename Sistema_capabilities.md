# Sistema de Capacidades y Límites (Capabilities) — NexaCore SaaS

> **Última actualización:** 2026-05-24

---

## 1. Estructura del JSON `configuracion` en la tabla `tenants`

Toda la configuración de capacidades, límites y features de un tenant se almacena en un único campo JSON: `tenants.configuracion`. No se utilizan tablas separadas para esta información.

### 1.1 Esquema completo

```json
{
  "bot": {
    "mode": "manual",
    "consultas_limite_mes": 50,
    "consultas_mes": 0,
    "consultas_mes_periodo": "2026-05"
  },
  "features_enabled": [
    "email_notifications",
    "whatsapp_notifications"
  ],
  "reportes": {
    "enabled": ["clientes", "remesas", "aduanas"],
    "disabled": ["patron_clientes", "clientes_pdf", "operacion_semanal"]
  },
  "limites": {
    "recursos": {
      "clientes": 10,
      "importadores": 5,
      "bodegas": 3,
      "aduanas": 2,
      "patentes": 3,
      "pedimentos_mes": 100,
      "documentos_mes": 200
    },
    "funcionalidades": {
      "reportes_mes": 10,
      "correos_dia": 50,
      "whatsapp_mes": 100
    }
  },
  "evolution_api": {
    "instance": "tenant_1",
    "connected": true,
    "connected_at": "2026-05-24 15:00:00",
    "whatsapp_plantilla": "breve",
    "whatsapp_plantilla_custom": null
  },
  "plantilla_correo_modulacion": "basica_azul"
}
```

### 1.2 Secciones del JSON

| Sección | Descripción | Controlado por |
|---------|-------------|----------------|
| `bot` | Configuración del SOIA-Bot (modo, límite consultas, contadores) | Superadmin |
| `features_enabled` | Array de features habilitadas (flags on/off) | Superadmin |
| `reportes` | Reportes habilitados/deshabilitados | Superadmin |
| `limites.recursos` | Límites de recursos (clientes, importadores, etc.) | Superadmin |
| `limites.funcionalidades` | Límites de funcionalidades (correos, reportes, whatsapp) | Superadmin |
| `evolution_api` | Configuración de WhatsApp (instancia, plantilla) | Admin tenant + Superadmin |
| `plantilla_correo_modulacion` | Template de email para notificaciones | Admin tenant |

---

## 2. Catálogo de Features (`features_enabled`)

### 2.1 Features disponibles

| Clave | Nombre | Planes que la incluyen |
|-------|--------|----------------------|
| `email_notifications` | 📧 Notificaciones por Email | Todos (Trial, Básico, Profesional, Enterprise) |
| `whatsapp_notifications` | 💬 Notificaciones por WhatsApp | Básico, Profesional, Enterprise |

> **Nota (2026-05-24):** Se eliminaron `basic_dashboard`, `basic_reports`, `advanced_reports`, `api_access`, `priority_support`, `white_label`, `custom_integrations`, `dedicated_support` — no tenían implementación real ni definición clara. El control de acceso a reportes se maneja en la sección "Configuración de Reportes".

### 2.2 Estado de enforcement por feature

| Feature | ¿Se respeta en vistas? | ¿Se respeta en controladores? | ¿Se respeta en rutas? | Estado |
|---------|----------------------|---------------------------|---------------------|--------|
| `email_notifications` | ✅ | ✅ (vía `EnviarNotificacionModulacionJob`) | N/A | **OK** |
| `whatsapp_notifications` | ✅ (card oculta en config si no tiene) | ✅ (vía `Tenant::whatsappHabilitado()`) | ❌ **NO** — La ruta no tiene middleware de feature | **PENDIENTE** |

---

## 3. Límites de Recursos (`limites.recursos`)

### 3.1 Recursos disponibles y su enforcement

| Recurso | Método de conteo en `getUso()` | ¿Se bloquea al exceder? | ¿Dónde se verifica? |
|---------|-------------------------------|------------------------|---------------------|
| `clientes` | `$this->clientes()->count()` | ❌ **NO** | `Tenant::canAddResource()` existe pero **nadie lo llama** |
| `importadores` | `$this->importadores()->count()` | ❌ **NO** | Ídem |
| `bodegas` | `$this->bodegas()->count()` | ❌ **NO** | Ídem |
| `aduanas` | `0` (son globales) | ❌ **NO** | N/A |
| `patentes` | `$this->patentes()->count()` | ❌ **NO** | Ídem |
| `pedimentos_mes` | `Expediente::where('tenant_id',...)->count()` | ❌ **NO** | Ídem |
| `documentos_mes` | `Documento::where('tenant_id',...)->count()` | ❌ **NO** | Ídem |

### 3.2 Estado general de límites de recursos

**Ningún límite de recurso se aplica realmente.** El modelo `Tenant` tiene toda la infraestructura (`getLimite()`, `getUso()`, `canAddResource()`, `enforceResourceLimit()` en `TenantCapabilityService`), pero **ningún controlador** llama a estos métodos antes de crear/actualizar recursos. Los límites solo se muestran en el panel de capabilities del superadmin como referencia visual.

---

## 4. Límites de Funcionalidades (`limites.funcionalidades`)

| Funcionalidad | ¿Se cuenta? | ¿Se bloquea? | Estado |
|--------------|------------|-------------|--------|
| `reportes_mes` | ❌ (`getUso()` retorna 0) | ❌ | **No implementado** |
| `correos_dia` | ❌ (`getUso()` retorna 0) | ❌ | **No implementado** |
| `whatsapp_mes` | ❌ (`getUso()` retorna 0) | ❌ | **No implementado** |

---

## 5. Reportes

### 5.1 Enforcement

| Mecanismo | ¿Funciona? | Detalle |
|-----------|-----------|--------|
| Middleware `report.access` | ✅ **SÍ** | Las rutas de reportes usan `->middleware('report.access:...')`. El middleware `CheckReportAccess` verifica `Tenant::hasReportAccess()`. |
| Vista de reportes (`reportes/index.blade.php`) | ❓ | Muestra todos los reportes sin filtrar por `features_enabled`. Verifica acceso vía `canGenerateReport()` al hacer clic. |
| Sidebar de reportes | ❓ | Depende de la implementación de la vista. No verificado en este análisis. |

---

## 6. Planes y sus defaults

Definidos en `TenantCapabilityService::getDefaultConfigForPlan()`:

| Plan | Bot | Features | Recursos |
|------|-----|----------|----------|
| **Trial** | Manual, 15 consultas | `email_notifications` | 2 clientes, 1 importador, 1 bodega, 10 pedimentos/mes |
| **Básico** | Manual, 50 consultas | email, whatsapp | 10 clientes, 5 importadores, 3 bodegas, 100 pedimentos/mes |
| **Profesional** | Automático, 200 | email, whatsapp | 50 clientes, 20 importadores, 10 bodegas, 500 pedimentos/mes |
| **Enterprise** | Automático, ilimitado | email, whatsapp | Todo ilimitado (null) |

---

## 7. Hallazgos y Deuda Técnica

### 7.1 Crítico: WhatsApp se muestra aunque esté deshabilitado

- **Archivo:** `resources/views/admin/config/index.blade.php:306`
- **Problema:** La card de WhatsApp se renderiza sin verificar `Tenant::hasFeature('whatsapp_notifications')`
- **Impacto:** Un tenant trial (que NO tiene `whatsapp_notifications` en `features_enabled`) ve la opción "WhatsApp" en el panel de configuración y puede acceder a `/configuracion-whatsapp`. Aunque `Tenant::whatsappHabilitado()` rechaza el envío de notificaciones, el tenant puede conectar su WhatsApp y ver contactos/grupos, lo cual no debería estar disponible en su plan.
- **Fix:** Agregar `@if(auth()->user()->tenant->hasFeature('whatsapp_notifications'))` alrededor de la card de WhatsApp.

### 7.2 Alto: Límites de recursos no se aplican

- **Archivos:** Todos los controladores CRUD (`ClienteController`, `ImportadorController`, `BodegaController`, `PatenteController`, `DocumentoController`, `ExpedienteController`)
- **Problema:** El modelo `Tenant` tiene `canAddResource()` y `enforceResourceLimit()`, pero **ningún controlador** los invoca antes de `store()` o `update()`.
- **Impacto:** Un tenant puede exceder su límite de clientes/importadores/documentos sin restricción. El superadmin configura límites que no se respetan.
- **Fix:** Agregar validación `canAddResource()` en cada `store()` y `TenantCapabilityService::enforceResourceLimit()` en cada acción de creación.

### 7.3 Alto: Límites de funcionalidades no se contabilizan

- **Archivos:** `Tenant::getUso()` para `reportes_mes`, `correos_dia`, `whatsapp_mes` retorna `0` hardcodeado
- **Problema:** No hay tracking de cuántos correos/reportes/whatsapps ha consumido un tenant
- **Impacto:** Los límites de funcionalidades son puramente decorativos
- **Fix:** Implementar contadores (similar a `bot.consultas_mes`) en `NotificacionModulacionService`, `ReporteController` y `NotificacionWhatsAppService`

### 7.4 Medio: Ruta WhatsApp sin middleware de feature

- Las rutas de WhatsApp (`/configuracion-whatsapp`, `/admin/whatsapp/*`) no tienen middleware que verifique `hasFeature('whatsapp_notifications')`. Si un tenant conoce la URL, puede acceder aunque la feature esté deshabilitada.
- **Fix:** Agregar un middleware `feature:whatsapp_notifications` similar a `report.access`.

### 7.5 Bajo: Columnas legacy en tabla tenants

- Las columnas `max_usuarios` y `max_operaciones_mes` existen en la tabla `tenants` y se actualizan junto con el JSON de configuración. Son redundantes con `limites.recursos.*`.
- **Recomendación:** Consolidar todo en el JSON y eliminar columnas legacy.

---

## 8. Resumen de Enforcement

| Capa | ¿Implementado? | ¿Funciona? | Observaciones |
|------|--------------|-----------|---------------|
| `features_enabled` en vistas | ✅ | ✅ | WhatsApp se oculta correctamente si no tiene feature |
| `features_enabled` en controladores | Parcial | ✅ | `whatsappHabilitado()` verifica antes de enviar |
| `features_enabled` en rutas | ❌ | ❌ | Las rutas de WhatsApp no tienen middleware de feature |
| `reportes` en rutas (middleware) | ✅ | ✅ | `report.access` middleware funciona correctamente |
| `limites.recursos` en controladores | ❌ | ❌ | Infraestructura existe pero no se invoca |
| `limites.funcionalidades` en controladores | ❌ | ❌ | No hay tracking de consumo |
| `bot` límites | ✅ | ✅ | `canMakeBotConsulta()` + contador `consultas_mes` funcionan con incremento atómico |

---

## 9. Modos del SOIA-Bot (`bot.mode`)

El bot tiene 3 modos desde el panel de superadmin:

| Modo | ¿Procesa? | Botón UI | Notificaciones |
|------|----------|----------|---------------|
| `manual` | ✅ | ✅ Visible | Completas (contactos) |
| `automatico` | ✅ | ❌ Oculto | Compactas (resumen) |
| `deshabilitado` | ❌ | ❌ Oculto | Ninguna |

### Cómo se dispara la ejecución

**No hay CRON en Laravel.** El bot se ejecuta externamente:

| Método | Endpoint | Flag |
|--------|----------|------|
| **Windows Task Scheduler** | `GET /api/bot/doda/ejecutar?token=xxx` | `esEjecucionManual=false` |
| **Panel UI** | `POST /admin/bot-doda/run` | `esEjecucionManual=true` |

### Flujo real

```
Task Scheduler → GET /api/bot/doda/ejecutar?token=xxx
  → DodaConsultaService::ejecutarConsultaMasiva()
  → Procesa TODOS los tenants con bot.mode !== 'deshabilitado'
  → Consultas concurrentes al PECEM/SOIA
  → crearNotificacionesPostEjecucion()
    → Incrementa contador atómico (JSON_SET)
    → esEjecucionManual ? notificaciones completas : compactas
```

### Diferencia manual vs automático

- **Manual** (`setEjecucionManual(true)`): Notifica a TODOS los contactos del directorio (email + WhatsApp)
- **Automático** (`false`): Solo notificaciones compactas/resumen

### Anti-concurrencia

Lock `doda_bot_running` (Cache, 10 min TTL). Si ya hay ejecución → HTTP 429.

### Auth API externo

`CHECK_TRAFICO_TOKEN` en `.env` — requerido como `?token=` en el endpoint `/api/bot/doda/ejecutar`.
