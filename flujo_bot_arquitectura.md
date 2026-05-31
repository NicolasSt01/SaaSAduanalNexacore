# Flujo de Trabajo — SOIA-Bot Multi-Tenant

> Última actualización: 2026-05-26

## Arquitectura General

```
┌──────────────────────────────────────────────────────────────────┐
│                      EJECUCIÓN DEL BOT                           │
│                                                                  │
│  ┌─────────────┐    ┌──────────────────┐    ┌───────────────┐   │
│  │ n8n Auto    │    │ Panel UI Manual  │    │ API Externa   │   │
│  │ (Schedule)  │    │ /admin/bot-doda  │    │ (Legacy)      │   │
│  └──────┬──────┘    └────────┬─────────┘    └───────┬───────┘   │
│         │                    │                      │           │
│         ▼                    ▼                      ▼           │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │              Laravel: DodaConsultaService                │   │
│  │                                                          │   │
│  │  ejecutarConsultaMasiva()                                │   │
│  │    → obtenerOperacionesPendientes()  (filtrar tenants)   │   │
│  │    → prepararConsultas()                                 │   │
│  │    → ejecutarConsultasConcurrentes()  (Guzzle Pool)      │   │
│  │    → crearNotificacionesPostEjecucion()                  │   │
│  │         → Incrementar contadores (JSON_SET atómico)      │   │
│  │         → Validar límites                                │   │
│  │         → Notificar (email + WhatsApp)                   │   │
│  └──────────────────────────────────────────────────────────┘   │
│                              │                                   │
│         ┌────────────────────┼────────────────────┐              │
│         ▼                    ▼                     ▼              │
│  ┌──────────┐    ┌───────────────────┐    ┌──────────────┐      │
│  │ PECEM    │    │ Email (Laravel)   │    │ WhatsApp     │      │
│  │ SOIA SAT │    │ INC-024           │    │ n8n + Evo    │      │
│  │ (HTTP)   │    │ dispatch_sync     │    │ INC-025/027  │      │
│  └──────────┘    └───────────────────┘    └──────────────┘      │
└──────────────────────────────────────────────────────────────────┘
```

## 3 Formas de Ejecutar el Bot

| Método | ¿Quién? | Endpoint | `esEjecucionManual` | Notificaciones |
|--------|---------|----------|---------------------|---------------|
| **n8n Schedule** | Automático | `POST /api/bot/doda/ejecutar-tenant/{id}` | `false` | Compactas |
| **Panel UI** | Admin del tenant | `POST /admin/bot-doda/run` | `true` | Completas |
| **API externa** | Windows Task Scheduler | `GET /api/bot/doda/ejecutar` | `false` | Compactas |

## Flujo n8n (SOIA-Bot Automatico)

```
Cada 10 minutos:
  1. GET /api/bot/doda/tenants-automaticos
     → Lista tenants con bot.mode = 'automatico'
     → Incluye créditos (usados/límite/disponibles)

  2. SplitInBatches: 1 tenant a la vez

  3. POST /api/bot/doda/ejecutar-tenant/{id}
     → Validación secuencial:
       ├─ bot.mode !== 'automatico' → skip
       ├─ canMakeBotConsulta() = false → skip (sin créditos)
       ├─ doda_bot_running lock → skip (429, ocupado)
       └─ OK → ejecutarConsultaMasiva()

  4. Wait 60s entre tenants (anti-sobresaturación)

  5. Loop al siguiente tenant
```

## Modos del Bot por Tenant

| Modo | ¿n8n lo procesa? | ¿Panel UI visible? | ¿API externa? |
|------|-----------------|-------------------|---------------|
| `automatico` | ✅ Sí | ❌ Oculto | ✅ Sí |
| `manual` | ❌ No | ✅ Visible | ✅ Sí |
| `deshabilitado` | ❌ No | ❌ Oculto | ❌ No |

## Sistema de Notificaciones (post-ejecución)

```
Bot detecta modulación
  │
  ├─ Email (Laravel INC-024)
  │   → canSendCorreo()? Sí → dispatch_sync → incrementar
  │                      No → pending_notifications cola JSON
  │
  └─ WhatsApp (n8n INC-025/027)
      → canSendWhatsapp()? Sí → HTTP POST n8n → Evolution API
                           No → pending_notifications cola JSON
```

## Contadores (atómicos, sin race conditions)

```
incrementarBotConsultas()      → JSON_SET(configuracion, '$.bot.consultas_mes', ...)
incrementarConsumoCorreos()    → JSON_SET(configuracion, '$.limites.funcionalidades.correos_dia_count', ...)
incrementarConsumoWhatsapp()   → JSON_SET(configuracion, '$.limites.funcionalidades.whatsapp_mes_count', ...)
```

Auto-reset: día (correos) / mes (bot, whatsapp)

## Workflows n8n

| Workflow | ID | Función |
|----------|-----|---------|
| SOIA-Bot Automatico | `wlhih0KjwmKol2mm` | Schedule → ejecuta bot por tenant automático |
| Modulacion WhatsApp | `U6YiCcmlZiQAIjlp` | Webhook → formatea + envía WhatsApp vía Evolution API |
