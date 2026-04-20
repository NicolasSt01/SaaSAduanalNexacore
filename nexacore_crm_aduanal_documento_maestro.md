# 🚀 NexaCore — CRM Aduanal Inteligente (SaaS) · Documento Maestro

> **Versión:** 3.0  
> **Empresa operadora:** NexaCore  
> **Mercado objetivo inicial:** Agencias aduanales en Reynosa, Tamaulipas, México  
> **Modelo de negocio:** SaaS multi-tenant — renta mensual por agencia

---

## 📌 VISIÓN DEL PROYECTO

NexaCore es una plataforma SaaS multi-tenant diseñada para agencias aduanales en México. Su propósito es centralizar el control operativo de cada agencia, automatizar la consulta de estatus de modulación vía SOIA, y comunicar en tiempo real a clientes, usuarios y administradores mediante WhatsApp y correo electrónico.

> El sistema no es solo un CRM.  
> Es un **sistema integral de gestión operativa aduanal + automatización + comunicación + analítica avanzada**.

NexaCore actúa como la capa tecnológica entre las agencias y sus clientes, gestionando toda la infraestructura de envíos, facturación, cobros y soporte desde una sola plataforma que el usuario final nunca ve.

---

## 🎯 OBJETIVO PRINCIPAL

Centralizar el flujo operativo de las agencias aduanales, eliminar procesos manuales repetitivos y reducir errores humanos, mientras se genera inteligencia de negocio a partir de los datos operativos.

**Valor agregado clave:**
- Control total de la operación en un solo lugar
- Centralización de documentos e información
- Reportes automáticos a clientes sin intervención manual
- Reportes a gerencias y administradores en tiempo real
- Interfaces de monitoreo de operación en tiempo real
- Bot de alertas de modulación SOIA en tiempo real
- Bot de mensajería vía WhatsApp a clientes y contactos

---

## 🏢 ROL DE NEXACORE (SUPER ADMIN / PROPIETARIO DE LA PLATAFORMA)

NexaCore es la empresa que opera la plataforma. Sus responsabilidades son:

- **Almacenar y aislar** la data de todas las agencias (cada agencia ve únicamente su propia información)
- **Gestionar los envíos** de correos electrónicos y WhatsApp de todas las agencias contratantes
- **Facturar a las agencias** por concepto de renta fija mensual + cargos adicionales según uso o plan
- **Cobrar en línea** directamente desde la plataforma (tarjeta de crédito/débito u otros métodos), sin necesidad de cobranza presencial
- **Monitorear métricas globales** del sistema: agencias activas, operaciones procesadas, mensajes enviados, MRR
- **Gestionar altas, bajas y soporte** de las agencias contratantes
- Administrar actualizaciones, disponibilidad (SLA) y seguridad de la plataforma

---

## 🔁 FLUJO OPERATIVO REAL (BASE DEL SISTEMA)

### Paso 1 — Recepción de información del cliente

El cliente de la agencia aduanal envía sus documentos e información a la agencia aduanal por los medios habituales:

- WhatsApp
- Correo electrónico
- Otros medios externos

Los documentos que el cliente envía incluyen:
- Facturas comerciales
- Documentos de transporte (BL, carta porte, etc.)
- Permisos y certificados cuando aplique
- Cualquier documento necesario para el trámite

---

### Paso 2 — Captura de la operación (usuario documentador)

El usuario/documentador de la agencia crea una nueva operación dentro del CRM con la información recibida.

#### Campos de captura:

**Identificación (automáticos)**
- ID de operación (autogenerado por el sistema)
- Fecha de captura (automática)

**Identificación (editables)**
- Referencia interna de la agencia

**Cliente y partes**
- Cliente → empresa que contrata a la agencia aduanal
- Importador → cliente del cliente (quien importa la mercancía)

**Mercancía**
- Producto (descripción en texto libre)
- Número de factura comercial

**Logística**
- Aduana de cruce *(obligatorio)*
- Fecha de cruce estimada *(obligatorio)*
- Bodega de destino *(opcional)*
- Número económico — unidad de transporte *(opcional)*
- Código Alpha *(opcional)*

> 👉 En este punto se crea la operación base. El expediente queda abierto y los documentos del cliente quedan adjuntos o referenciados.

---

### Paso 3 — Proceso externo (fuera del CRM)

Los usuarios de la agencia trabajan el trámite aduanal en sus sistemas propios:

- CAAAREM3
- Aduanet
- Otros sistemas internos de cada agencia

Este proceso es externo al CRM y NexaCore no interfiere en él.

---

### Paso 4 — Actualización del pedimento y DODA ⭐

Una vez concluido el trámite en los sistemas externos, el usuario regresa al CRM y actualiza la operación con:

- **Número de pedimento**
- **Número de DODA**

> 👉 Este paso es crítico: al registrar el DODA, se activa automáticamente el bot de monitoreo de modulación.

---

### Paso 5 — Bot de monitoreo SOIA (automatización DODA)

El sistema ejecuta automáticamente un proceso en segundo plano:

- Consulta periódica a la página de **SOIA** (Sistema de Operación de Inspección Aduanera)
- Obtención del estatus de modulación del DODA registrado
- Guardado del resultado completo en formato JSON
- Registro del historial de cambios con timestamps

**Estatus posibles (ejemplos):**
- Desaduanamiento libre (verde) ✅
- Reconocimiento aduanero (rojo) 🔴
- Reconocimiento concluido 🟡
- Otros estatus del sistema SOIA

---

### Paso 6 — Detección de cambio de estatus

Cuando el estatus del DODA cambia respecto al último registro:

```
→ Se dispara el evento: doda.status_changed
```

---

### Paso 7 — Notificación automática

El sistema envía notificaciones de forma automática a los destinatarios configurados por el administrador de la agencia.

**Canales disponibles:**
- 📱 WhatsApp
- 📧 Correo electrónico

**Destinatarios configurables:**
- Clientes finales (importadores)
- Grupos de contacto
- Usuarios internos de la agencia (documentadores, operadores)
- Administrador / Gerencia de la agencia

> El administrador de cada agencia define previamente qué usuarios reciben notificaciones y por qué canal.

---

### Paso 8 — Registro de actividad

El sistema guarda automáticamente:
- Fecha y hora de la notificación
- Canal utilizado (WhatsApp / Email)
- Destinatario
- Resultado del envío (enviado / fallido / pendiente)
- Historial de reintentos si aplica

---

## 🧩 MÓDULOS DEL SISTEMA

### 🔐 Autenticación y Acceso
- Registro de agencias (alta desde NexaCore)
- Login por tenant (dominio o subdominio propio por agencia)
- Roles y permisos configurables por el administrador de cada agencia
- Multi-tenant estricto: ningún usuario puede ver datos de otra agencia
- Recuperación de contraseña

---

### 🏢 Gestión de Agencia (por tenant)
- Configuración del perfil de la agencia
- Gestión de usuarios internos y asignación de roles
- Configuración de notificaciones: canales, destinatarios, mensajes plantilla
- API Keys de integración
- Webhooks para integraciones externas (n8n en fases futuras)

---

### 👥 Clientes e Importadores
- Catálogo de clientes de la agencia (empresas contratantes)
- Catálogo de importadores (clientes de los clientes)
- Contactos por cliente: nombre, teléfono WhatsApp, correo
- Historial de operaciones por cliente

---

### 📁 Documentos
- Adjuntar documentos a cada operación (facturas, permisos, BL, etc.)
- Clasificación por tipo de documento
- Almacenamiento seguro por tenant

---

### 📦 Operaciones (CORE)

Entidad central del sistema. Contiene:

- Toda la información operativa y logística
- Estado actual de la operación
- Documentos adjuntos
- Relación con cliente e importador
- Número de pedimento y DODA
- Historial completo de estatus SOIA
- Log de notificaciones enviadas

**Estados de la operación:**
- Capturada
- En proceso (trámite externo)
- DODA asignado (monitoreo activo)
- Desaduanada / Libre
- En reconocimiento
- Reconocimiento concluido
- Cerrada

---

### 📜 Historial de Estatus DODA
- Registro cronológico de cada cambio de estatus
- Timestamp de cada consulta
- JSON completo de respuesta SOIA
- Diferencial entre estatus anterior y nuevo

---

### 📲 Notificaciones
- Log completo de todos los envíos realizados
- Detalle por canal (WhatsApp / Email)
- Estado de cada envío (éxito / fallo / reintento)
- Plantillas de mensajes configurables por agencia

---

### 📊 Analítica e Inteligencia de Negocio

#### Vista Agencia (Administrador de la agencia)
- Total de operaciones del período
- Operaciones por estatus
- Tiempo promedio de desaduanamiento
- Porcentaje de reconocimientos (rojo vs libre)
- Operaciones por usuario/documentador
- Volumen de notificaciones enviadas

#### Vista Usuario (documentador/operador)
- Mis operaciones capturadas
- Operaciones pendientes de DODA
- Rendimiento individual

#### Vista NexaCore (Super Admin)
- Total de agencias activas
- Operaciones procesadas en toda la plataforma
- Mensajes enviados (WhatsApp + Email)
- MRR y facturación
- Agencias con pagos pendientes
- Uso de recursos por agencia

---

### 💳 Facturación y Cobros (NexaCore → Agencias)

- Generación automática de facturas mensuales por agencia
- Concepto: renta fija + cargos adicionales (por operaciones, mensajes, etc.)
- Cobro en línea directamente desde la plataforma (tarjeta, SPEI u otros métodos)
- Historial de pagos y facturas por agencia
- Notificaciones automáticas de vencimiento y pago confirmado
- Suspensión automática de servicio en caso de impago (configurable)

---

## ⚙️ ARQUITECTURA EVENT-DRIVEN

El sistema opera basado en eventos para garantizar reactividad y escalabilidad:

| Evento | Descripción |
|---|---|
| `operation.created` | Se captura una nueva operación |
| `operation.updated` | Se actualizan datos (incluye asignación de DODA) |
| `doda.assigned` | Se registra por primera vez el número de DODA → activa el bot |
| `doda.status_changed` ⭐ | El estatus de SOIA cambia → dispara notificaciones |
| `notification.requested` | Se solicita enviar una notificación |
| `notification.sent` | Notificación enviada exitosamente |
| `notification.failed` | Fallo en el envío → reintento |
| `payment.received` | Pago de renta confirmado |
| `payment.overdue` | Pago vencido → alerta y flujo de cobranza |

---

## 🔔 NOTIFICATION SERVICE

Servicio central que encapsula toda la lógica de notificaciones.

**Modos de operación:**

### direct
- El backend envía directamente vía proveedor de WhatsApp (ej. Twilio, Meta Business API) y correo (ej. Resend, Mailgun)

### n8n (fase futura)
- El backend envía un webhook a n8n
- n8n ejecuta la orquestación, reintentos e integraciones externas

---

## 🔄 INTEGRACIÓN CON n8n (Fase Futura)

n8n se utilizará para:
- Orquestación avanzada de envíos
- Lógica de reintentos con backoff
- Integraciones con sistemas externos de las agencias
- Automatizaciones personalizadas por agencia

n8n **NO** se utilizará para:
- Lógica de negocio core
- Consultas al sistema SOIA
- Validaciones de datos

---

## 🏗️ ARQUITECTURA TÉCNICA

| Capa | Tecnología |
|---|---|
| Backend | Laravel (PHP) |
| Base de datos | MySQL |
| Colas y jobs | Redis + Laravel Queues |
| Bot SOIA | Servicio dedicado (scraping / HTTP) en Laravel Jobs |
| Frontend | Blade (MVP) → React (fases futuras) |
| Notificaciones WhatsApp | Meta Business API / Twilio |
| Notificaciones Email | Resend / Mailgun / SES |
| Pagos | Stripe / Conekta / OpenPay |
| Almacenamiento docs | S3 / Cloudflare R2 |
| Multi-tenancy | Dominio/subdominio por agencia + scope en queries |

---

## 🔐 SEGURIDAD Y AISLAMIENTO DE DATOS

- Cada agencia opera en su propio tenant con scope estricto a nivel base de datos
- Ningún usuario puede acceder, ver ni modificar datos de otra agencia
- El administrador de cada agencia gestiona los roles y permisos de sus propios usuarios
- NexaCore (Super Admin) tiene acceso de solo lectura a métricas agregadas, nunca a datos operativos individuales de las agencias sin autorización
- Autenticación con tokens seguros (Sanctum / Passport)
- Comunicaciones cifradas (HTTPS / TLS)
- Logs de auditoría por acción de usuario

---

## 🤖 ARQUITECTURA DE DESARROLLO MULTI-AGENTE

Para el desarrollo del sistema se utilizará una arquitectura de agentes especializados:

| Agente | Responsabilidad |
|---|---|
| Arquitecto | Estructura general, decisiones técnicas |
| Base de Datos | Diseño del esquema, migraciones, índices |
| Backend | Lógica de negocio, APIs, jobs, eventos |
| Frontend | Interfaces, componentes, UX |
| Integraciones | WhatsApp, Email, SOIA, pagos |
| Analítica | KPIs, dashboards, reportes |
| Seguridad | Multi-tenancy, permisos, auditoría |
| DevOps | Deploy, CI/CD, monitoreo |

---

## 🧭 ROADMAP

### Fase 1 — Core operativo
- Autenticación y multi-tenancy
- Gestión de clientes e importadores
- Captura de operaciones (flujo completo)
- Adjunto de documentos

### Fase 2 — Automatización DODA
- Bot de consulta periódica a SOIA
- Historial de estatus con JSON completo
- Detección de cambios de estatus

### Fase 3 — Notificaciones
- Envío automático por WhatsApp y Email
- Configuración de destinatarios por agencia
- Logs de notificaciones

### Fase 4 — Facturación y Cobros
- Generación de facturas a agencias
- Cobro en línea desde la plataforma
- Panel financiero NexaCore

### Fase 5 — Analítica e Inteligencia
- Dashboards por agencia, usuario y NexaCore
- Reportes automáticos a gerencias
- Interfaces de monitoreo en tiempo real

### Fase 6 — IA y Automatización Avanzada
- Predicción de reconocimientos
- Sugerencias operativas
- Integración con n8n para flujos personalizados
- Chatbot de soporte

---

## ⚠️ RIESGOS Y MITIGACIONES

| Riesgo | Mitigación |
|---|---|
| Cambios en la estructura de SOIA | Diseñar el bot con adaptadores intercambiables, alertas de fallo |
| Bloqueo de scraping por aduana | Explorar API oficial si existe; fallback manual con aviso |
| Políticas de WhatsApp Business API | Usar proveedores certificados (Meta BSP); preparar fallback a Email |
| Agencias que no pagan | Suspensión automática + notificaciones previas; política de gracia |
| Fuga de datos entre tenants | Tests de aislamiento como parte del CI/CD |
| Dependencia de proveedor de pagos | Arquitectura de pagos con proveedor intercambiable |

---

## 📈 MÉTRICAS DE ÉXITO

### Operativas
- Operaciones procesadas por mes en toda la plataforma
- Tiempo promedio de respuesta del bot SOIA
- Tasa de éxito de notificaciones enviadas

### Negocio
- MRR (Monthly Recurring Revenue)
- Agencias activas
- Churn de agencias
- NPS de agencias contratantes

### Técnicas
- Uptime de la plataforma (objetivo: 99.9%)
- Latencia del bot de monitoreo
- Tiempo de onboarding de nueva agencia

---

## 🗄️ DISEÑO DE BASE DE DATOS — ESQUEMA MULTI-TENANT

> **Estado:** Definición inicial v1.0 (actualizado 2026-03-23)

Esta sección define la estructura relacional completa de la base de datos de NexaCore, incluyendo la decisión de arquitectura multi-tenant, las tablas principales, sus campos, relaciones y consideraciones de aislamiento de datos.

---

### 🏛️ DECISIÓN ARQUITECTÓNICA: ESTRATEGIA MULTI-TENANT

#### Opción A — Base de datos compartida con columna `tenant_id` (Shared DB)

Todas las agencias comparten una misma base de datos. Cada tabla incluye una columna `tenant_id` que referencia al tenant (agencia) propietario del registro.

**Ventajas:**
- Infraestructura más simple y económica
- Mantenimiento centralizado (una sola migración para todos)
- Búsquedas cruzadas entre tenants para reportes de NexaCore
- Onboarding instantáneo de nuevas agencias (sin provisionar BD)

**Desventajas:**
- Riesgo de fuga de datos si hay un bug en los scopes (mitigado con Global Scopes en Laravel)
- Bases de datos más grandes con el tiempo
- Backups de una agencia implican exportar de una BD compartida

---

#### Opción B — Base de datos independiente por agencia (Isolated DB)

Cada agencia tiene su propia base de datos con un prefijo de subdominio identificador.

**Ejemplo de subdominos e instancias:**
```
crosspoint.nexacore.com.mx     → DB: nexacore_crosspoint
logisticsmasters.nexacore.com.mx → DB: nexacore_logisticsmasters
agencia3.nexacore.com.mx       → DB: nexacore_agencia3
```

**Ventajas:**
- Aislamiento total de datos (imposible filtrar datos entre agencias)
- Backups y restauraciones independientes por agencia
- Posibilidad de personalización del esquema por agencia en el futuro
- Cumplimiento más fácil de regulaciones de privacidad de datos

**Desventajas:**
- Mayor complejidad operativa (migraciones deben correr en cada BD)
- Más conexiones de base de datos activas
- Onboarding requiere provisionamiento de BD

---

#### ✅ DECISIÓN: Estrategia Híbrida Recomendada

**Para el MVP: Base de datos compartida con `tenant_id` + Global Scopes en Laravel.**

Razones:
1. Permite lanzar más rápido sin infraestructura compleja
2. Laravel Tenancy (paquete `stancl/tenancy`) maneja el aislamiento automáticamente mediante scopes globales
3. Cuando se escale, es posible migrar a BD aisladas por agencia con el mismo paquete
4. El subdominio por agencia (`agencia.nexacore.com.mx`) se implementa desde el inicio sin importar la estrategia de BD

**La tabla `tenants` es el eje central que identifica a cada agencia cliente de NexaCore.**

> ⚠️ **Regla de oro:** NUNCA se ejecuta una query sobre tablas operativas sin filtrar por `tenant_id`. Esto se garantiza mediante Eloquent Global Scopes (el Model base incluye el scope automáticamente).

---

### 📐 ESQUEMA RELACIONAL COMPLETO

#### Diagrama de Relaciones (Simplificado)

```
tenants
  └── usuarios
  └── clientes
        └── directorio (contactos)
        └── operaciones
  └── importadores
        └── operaciones
  └── bodegas
        └── operaciones
  └── aduanas
        └── bodegas
        └── patentes
        └── pedimentos
        └── operaciones
  └── patentes
        └── pedimentos
        └── operaciones
  └── pedimentos
        └── operaciones
  └── operaciones
        └── operacion_documentos
        └── operacion_historial_doda
        └── notificaciones
```

---

### 📋 TABLAS DETALLADAS

---

#### 🏢 Tabla: `tenants` *(Agencias aduanales — clientes de NexaCore)*

> Tabla maestra del sistema multi-tenant. Cada registro representa una agencia aduanal contratante.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | Identificador único |
| `slug` | VARCHAR(100) UNIQUE | Identificador de subdominio: `crosspoint`, `logisticsmasters` |
| `nombre_empresa` | VARCHAR(255) | Razón social de la agencia |
| `rfc` | VARCHAR(20) | RFC de la agencia |
| `correo_admin` | VARCHAR(255) | Correo del administrador principal |
| `telefono` | VARCHAR(20) | Teléfono de contacto |
| `logo_url` | VARCHAR(500) NULL | URL del logotipo (almacenado en S3/R2) |
| `plan` | ENUM('basico','profesional','enterprise') | Plan contratado |
| `estado` | ENUM('activo','suspendido','cancelado') | Estado de la suscripción |
| `fecha_inicio` | DATE | Fecha de alta en la plataforma |
| `fecha_vencimiento` | DATE NULL | Fecha de vencimiento del ciclo de pago |
| `max_usuarios` | INT | Límite de usuarios según plan |
| `max_operaciones_mes` | INT NULL | Límite de operaciones mensuales (NULL = ilimitado) |
| `configuracion` | JSON NULL | Configuraciones específicas del tenant (webhooks, preferencias) |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Última actualización |

---

#### 👤 Tabla: `usuarios`

> Usuarios internos de cada agencia aduanal. Un usuario pertenece a un solo tenant.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | Identificador único |
| `tenant_id` | BIGINT UNSIGNED FK | Referencia a `tenants.id` |
| `nombre` | VARCHAR(150) | Nombre completo del usuario |
| `correo` | VARCHAR(255) UNIQUE | Correo electrónico (usado para login) |
| `password` | VARCHAR(255) | Contraseña hasheada (bcrypt) |
| `carrera` | VARCHAR(150) NULL | Carrera o especialidad del usuario |
| `rol` | ENUM('admin','supervisor','documentador','operador','consulta') | Rol dentro de la agencia |
| `permisos` | JSON NULL | Permisos granulares adicionales al rol |
| `telefono_whatsapp` | VARCHAR(20) NULL | Número para recibir notificaciones WhatsApp |
| `activo` | BOOLEAN | `1` = activo, `0` = inactivo/bloqueado |
| `ultimo_login` | TIMESTAMP NULL | Última fecha de acceso |
| `remember_token` | VARCHAR(100) NULL | Token Laravel Remember Me |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Índices:** `tenant_id`, `correo`, `rol`

**Roles y sus capacidades:**

| Rol | Descripción |
|---|---|
| `admin` | Acceso total: configuración, usuarios, reportes, operaciones |
| `supervisor` | Ve todas las operaciones, puede editar y cerrar |
| `documentador` | Captura operaciones, actualiza DODA, adjunta documentos |
| `operador` | Ve y actualiza el estado de sus operaciones asignadas |
| `consulta` | Solo lectura, sin modificaciones |

---

#### 🏭 Tabla: `clientes`

> Empresas que contratan a la agencia aduanal. Son los clientes directos de la agencia.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | Identificador único |
| `tenant_id` | BIGINT UNSIGNED FK | Referencia a `tenants.id` |
| `nombre_empresa` | VARCHAR(255) | Razón social del cliente |
| `rfc` | VARCHAR(20) NULL | RFC del cliente (México) |
| `tax_id` | VARCHAR(50) NULL | Tax ID para clientes extranjeros |
| `pais` | VARCHAR(100) DEFAULT 'México' | País de origen |
| `direccion` | TEXT NULL | Dirección fiscal completa |
| `correo_principal` | VARCHAR(255) NULL | Correo de contacto principal |
| `telefono_principal` | VARCHAR(20) NULL | Teléfono de contacto principal |
| `activo` | BOOLEAN DEFAULT 1 | Estado del cliente |
| `observaciones` | TEXT NULL | Notas internas sobre el cliente |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Índices:** `tenant_id`, `rfc`, `nombre_empresa`

---

#### 📒 Tabla: `directorio` *(Contactos del cliente)*

> Personas de contacto asociadas a cada cliente. Estos son los destinatarios de notificaciones WhatsApp/Email.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | Identificador único |
| `tenant_id` | BIGINT UNSIGNED FK | Referencia a `tenants.id` |
| `cliente_id` | BIGINT UNSIGNED FK | Referencia a `clientes.id` |
| `nombre` | VARCHAR(150) | Nombre del contacto |
| `puesto` | VARCHAR(100) NULL | Cargo o puesto del contacto |
| `correo` | VARCHAR(255) NULL | Correo electrónico |
| `telefono` | VARCHAR(20) NULL | Teléfono directo |
| `whatsapp` | VARCHAR(20) NULL | Número WhatsApp (con código de país: +521...) |
| `recibe_notificaciones` | BOOLEAN DEFAULT 1 | Si recibe notificaciones automáticas |
| `canal_preferido` | ENUM('whatsapp','email','ambos') DEFAULT 'ambos' | Canal preferido de notificación |
| `activo` | BOOLEAN DEFAULT 1 | Estado del contacto |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Índices:** `tenant_id`, `cliente_id`

---

#### 🚢 Tabla: `importadores`

> Empresas que importan la mercancía. Son los clientes del cliente de la agencia. Pueden ser nacionales o extranjeros.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | Identificador único |
| `tenant_id` | BIGINT UNSIGNED FK | Referencia a `tenants.id` |
| `nombre_empresa` | VARCHAR(255) | Razón social del importador |
| `tax_id` | VARCHAR(50) NULL | Tax ID (para importadores extranjeros) |
| `rfc` | VARCHAR(20) NULL | RFC (si es importador mexicano) |
| `pais` | VARCHAR(100) NULL | País del importador |
| `direccion` | TEXT NULL | Dirección completa |
| `activo` | BOOLEAN DEFAULT 1 | Estado |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Índices:** `tenant_id`, `tax_id`

---

#### 🏛️ Tabla: `aduanas`

> Catálogo de aduanas de México. Puede ser un catálogo global (sin `tenant_id`) o por tenant. Recomendado como catálogo global compartido para evitar duplicados.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | Identificador único |
| `clave` | VARCHAR(10) UNIQUE | Clave oficial de aduana (ej. `810` = Nuevo Laredo) |
| `nombre` | VARCHAR(200) | Nombre oficial de la aduana |
| `ciudad` | VARCHAR(100) NULL | Ciudad donde se ubica |
| `estado` | VARCHAR(100) NULL | Estado de la república |
| `activa` | BOOLEAN DEFAULT 1 | Si la aduana está operativa |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

> 📌 Esta tabla NO lleva `tenant_id` — es un catálogo global compartido.

---

#### 🏪 Tabla: `bodegas`

> Bodegas o recintos fiscalizados donde se almacena la mercancía. Son específicas por tenant.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | Identificador único |
| `tenant_id` | BIGINT UNSIGNED FK | Referencia a `tenants.id` |
| `nombre` | VARCHAR(200) | Nombre de la bodega |
| `aduana_id` | BIGINT UNSIGNED FK | Referencia a `aduanas.id` (aduana donde se ubica) |
| `clave_aduana` | VARCHAR(20) NULL | Clave de referencia rápida |
| `direccion` | TEXT NULL | Dirección física |
| `activa` | BOOLEAN DEFAULT 1 | Estado |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Índices:** `tenant_id`, `aduana_id`

---

#### 📜 Tabla: `patentes`

> Patentes aduanales registradas por el tenant. Una patente identifica a un agente aduanal autorizado.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | Identificador único |
| `tenant_id` | BIGINT UNSIGNED FK | Referencia a `tenants.id` |
| `numero_patente` | VARCHAR(50) | Número oficial de la patente aduanal |
| `nombre_agente` | VARCHAR(200) | Nombre completo del agente aduanal |
| `rfc_agente` | VARCHAR(20) NULL | RFC del agente aduanal |
| `activa` | BOOLEAN DEFAULT 1 | Estado de la patente |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Índices:** `tenant_id`, `numero_patente`

---

#### 📄 Tabla: `pedimentos`

> Pedimentos aduanales. Un pedimento está vinculado a un cliente, una patente y una aduana. Puede estar asociado a múltiples operaciones (en casos de rectificaciones o complementos).

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | Identificador único |
| `tenant_id` | BIGINT UNSIGNED FK | Referencia a `tenants.id` |
| `cliente_id` | BIGINT UNSIGNED FK | Referencia a `clientes.id` |
| `patente_id` | BIGINT UNSIGNED FK | Referencia a `patentes.id` |
| `aduana_id` | BIGINT UNSIGNED FK | Referencia a `aduanas.id` |
| `numero_pedimento` | VARCHAR(50) | Número oficial del pedimento |
| `clave_pedimento` | VARCHAR(20) NULL | Clave del tipo de pedimento (ej. A1, G1, etc.) |
| `tipo_pedimento` | VARCHAR(100) NULL | Descripción del tipo (importación, exportación, etc.) |
| `fecha_apertura` | DATE NULL | Fecha en que se abrió el pedimento |
| `fecha_pago` | DATE NULL | Fecha de pago de impuestos |
| `fecha_cierre` | DATE NULL | Fecha en que se cerró oficialmente |
| `categoria` | VARCHAR(100) NULL | Categoría del pedimento |
| `estado` | ENUM('abierto','cerrado','cancelado') DEFAULT 'abierto' | Estado actual |
| `observaciones` | TEXT NULL | Notas internas |
| `usuario_apertura_id` | BIGINT UNSIGNED FK NULL | Usuario que abrió el pedimento |
| `usuario_cierre_id` | BIGINT UNSIGNED FK NULL | Usuario que cerró el pedimento |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Índices:** `tenant_id`, `cliente_id`, `patente_id`, `aduana_id`, `numero_pedimento`, `estado`

---

#### ⭐ Tabla: `operaciones` *(Tabla Central del Sistema)*

> La tabla más importante del CRM. Cada registro representa una operación aduanal completa desde su captura hasta su cierre. Concentra todas las relaciones del sistema.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | ID interno único |
| `tenant_id` | BIGINT UNSIGNED FK | 🔑 Referencia a `tenants.id` — **clave de aislamiento multi-tenant** |
| `referencia` | VARCHAR(100) | Número de referencia interna de la agencia |
| `fecha_registro` | TIMESTAMP | Fecha y hora de captura de la operación |
| `fecha_cruce` | DATE NULL | Fecha estimada o real de cruce de frontera |
| `cliente_id` | BIGINT UNSIGNED FK | Referencia a `clientes.id` |
| `importador_id` | BIGINT UNSIGNED FK NULL | Referencia a `importadores.id` |
| `producto` | TEXT | Descripción del producto / mercancía |
| `bodega_id` | BIGINT UNSIGNED FK NULL | Referencia a `bodegas.id` |
| `numero_factura` | VARCHAR(100) NULL | Número de factura comercial del cliente |
| `aduana_id` | BIGINT UNSIGNED FK | Referencia a `aduanas.id` |
| `patente_id` | BIGINT UNSIGNED FK NULL | Referencia a `patentes.id` |
| `pedimento_id` | BIGINT UNSIGNED FK NULL | Referencia a `pedimentos.id` (se asigna posterior a la captura) |
| `numero_economico` | VARCHAR(50) NULL | Número económico de la unidad de transporte |
| `codigo_alpha` | VARCHAR(50) NULL | Código Alpha de la unidad de transporte |
| `doda` | VARCHAR(100) NULL | Número DODA — **activa el bot de monitoreo al asignarse** |
| `modulacion` | VARCHAR(100) NULL | Resultado de modulación (ej. Verde, Rojo, etc.) |
| `fecha_modulacion` | TIMESTAMP NULL | Fecha y hora del último cambio de modulación |
| `modulacion_json` | JSON NULL | Respuesta JSON completa de SOIA |
| `prioridad` | ENUM('baja','normal','alta','urgente') DEFAULT 'normal' | Prioridad de atención de la operación |
| `estado` | ENUM('capturada','en_proceso','doda_asignado','libre','reconocimiento','reconocimiento_concluido','cerrada','cancelada') DEFAULT 'capturada' | Estado actual de la operación |
| `observaciones` | TEXT NULL | Notas internas y observaciones generales |
| `usuario_capturo_id` | BIGINT UNSIGNED FK | Usuario que creó / capturó la operación |
| `usuario_doda_id` | BIGINT UNSIGNED FK NULL | Usuario que capturó / actualizó el DODA |
| `usuario_cerro_id` | BIGINT UNSIGNED FK NULL | Usuario que cerró la operación |
| `fecha_cierre` | TIMESTAMP NULL | Fecha en que se cerró la operación |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Índices:** `tenant_id`, `cliente_id`, `importador_id`, `pedimento_id`, `aduana_id`, `patente_id`, `estado`, `doda`, `fecha_registro`, `referencia`

**Estados de la operación y sus transiciones:**

```
capturada
    └→ en_proceso        (usuario inicia el trámite externo)
          └→ doda_asignado   (se registra el DODA → activa bot SOIA)
                └→ libre                (SOIA: Desaduanamiento libre)
                └→ reconocimiento       (SOIA: Reconocimiento aduanero)
                      └→ reconocimiento_concluido
                            └→ cerrada
    └→ cancelada         (en cualquier estado)
```

---

#### 📁 Tabla: `operacion_documentos`

> Documentos adjuntos a cada operación (facturas, BL, permisos, certificados, etc.).

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | |
| `tenant_id` | BIGINT UNSIGNED FK | Referencia a `tenants.id` |
| `operacion_id` | BIGINT UNSIGNED FK | Referencia a `operaciones.id` |
| `tipo_documento` | ENUM('factura','bl','permiso','certificado','otro') | Tipo de documento |
| `nombre_archivo` | VARCHAR(255) | Nombre original del archivo |
| `url_archivo` | VARCHAR(500) | URL del archivo en S3/R2 |
| `subido_por_id` | BIGINT UNSIGNED FK | Usuario que subió el documento |
| `created_at` | TIMESTAMP | |

---

#### 📡 Tabla: `operacion_historial_doda`

> Registro cronológico de cada consulta al sistema SOIA para el DODA de una operación. Permite auditoría completa del historial de modulación.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | |
| `tenant_id` | BIGINT UNSIGNED FK | |
| `operacion_id` | BIGINT UNSIGNED FK | Referencia a `operaciones.id` |
| `doda` | VARCHAR(100) | Número DODA consultado |
| `estatus_anterior` | VARCHAR(100) NULL | Estatus previo al cambio |
| `estatus_nuevo` | VARCHAR(100) | Estatus obtenido en esta consulta |
| `hubo_cambio` | BOOLEAN | `1` si el estatus cambió respecto al anterior |
| `respuesta_json` | JSON | JSON completo de la respuesta de SOIA |
| `consultado_at` | TIMESTAMP | Fecha y hora exacta de la consulta |

**Índices:** `operacion_id`, `consultado_at`, `hubo_cambio`

---

#### 🔔 Tabla: `notificaciones`

> Log completo de todas las notificaciones enviadas o intentadas (WhatsApp / Email).

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | |
| `tenant_id` | BIGINT UNSIGNED FK | |
| `operacion_id` | BIGINT UNSIGNED FK NULL | Operación relacionada (si aplica) |
| `destinatario_tipo` | ENUM('contacto','usuario','externo') | Tipo de destinatario |
| `destinatario_id` | BIGINT UNSIGNED NULL | ID del contacto o usuario |
| `canal` | ENUM('whatsapp','email') | Canal de envío |
| `destinatario_valor` | VARCHAR(255) | Número de WhatsApp o correo real al que se envió |
| `asunto` | VARCHAR(255) NULL | Asunto (solo para email) |
| `mensaje` | TEXT | Contenido del mensaje enviado |
| `plantilla_id` | BIGINT UNSIGNED NULL | Plantilla usada (si aplica) |
| `estado` | ENUM('pendiente','enviado','fallido','reintentando') DEFAULT 'pendiente' | Estado del envío |
| `intentos` | TINYINT DEFAULT 0 | Número de intentos de envío |
| `error_detalle` | TEXT NULL | Detalle del error si falló |
| `enviado_at` | TIMESTAMP NULL | Timestamp de envío exitoso |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

---

#### 💳 Tabla: `facturacion` *(NexaCore → Agencias)*

> Registro de facturas y cobros de NexaCore a sus clientes (las agencias).

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | |
| `tenant_id` | BIGINT UNSIGNED FK | Agencia a la que se factura |
| `periodo` | VARCHAR(7) | Período de facturación: `2026-03` |
| `monto_renta` | DECIMAL(10,2) | Cargo fijo mensual por plan |
| `monto_extras` | DECIMAL(10,2) DEFAULT 0 | Cargos adicionales (sobrelímite de operaciones, mensajes, etc.) |
| `monto_total` | DECIMAL(10,2) | Total a cobrar |
| `estado` | ENUM('pendiente','pagada','vencida','cancelada') DEFAULT 'pendiente' | Estado del pago |
| `fecha_emision` | DATE | Fecha de emisión de la factura |
| `fecha_vencimiento` | DATE | Fecha límite de pago |
| `fecha_pago` | DATE NULL | Fecha real de pago |
| `metodo_pago` | VARCHAR(50) NULL | Método de pago usado |
| `referencia_pago` | VARCHAR(255) NULL | Referencia o ID de transacción del procesador de pagos |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

---

### 🔗 RESUMEN DE RELACIONES ENTRE TABLAS

| Tabla | Relación | Tabla Relacionada | Tipo |
|---|---|---|---|
| `tenants` | tiene muchos | `usuarios` | 1:N |
| `tenants` | tiene muchos | `clientes` | 1:N |
| `tenants` | tiene muchos | `importadores` | 1:N |
| `tenants` | tiene muchos | `bodegas` | 1:N |
| `tenants` | tiene muchos | `patentes` | 1:N |
| `tenants` | tiene muchos | `pedimentos` | 1:N |
| `tenants` | tiene muchos | `operaciones` | 1:N |
| `clientes` | tiene muchos | `directorio` | 1:N |
| `clientes` | tiene muchos | `pedimentos` | 1:N |
| `clientes` | tiene muchos | `operaciones` | 1:N |
| `aduanas` | tiene muchas | `bodegas` | 1:N |
| `aduanas` | tiene muchos | `pedimentos` | 1:N |
| `aduanas` | tiene muchas | `operaciones` | 1:N |
| `patentes` | tiene muchos | `pedimentos` | 1:N |
| `patentes` | tiene muchas | `operaciones` | 1:N |
| `pedimentos` | tiene muchas | `operaciones` | 1:N |
| `importadores` | tiene muchas | `operaciones` | 1:N |
| `bodegas` | tiene muchas | `operaciones` | 1:N |
| `operaciones` | tiene muchos | `operacion_documentos` | 1:N |
| `operaciones` | tiene muchos | `operacion_historial_doda` | 1:N |
| `operaciones` | tiene muchas | `notificaciones` | 1:N |
| `usuarios` | captura muchas | `operaciones` | 1:N |
| `usuarios` | cierra muchas | `operaciones` | 1:N |

---

### 🔐 REGLAS DE AISLAMIENTO MULTI-TENANT (Implementación Laravel)

1. **Global Scope automático**: El modelo base `TenantModel` incluye un scope global que siempre filtra por `tenant_id` del usuario autenticado.
2. **Middleware de tenant**: Al recibir cada request, el middleware detecta el subdominio y establece el `tenant_id` activo en la sesión.
3. **Validaciones en formularios**: Siempre validar que el `cliente_id`, `patente_id`, etc. pertenezcan al mismo `tenant_id` del usuario.
4. **Tests de aislamiento**: Suite de tests automatizados que verifica que ningún tenant puede leer datos de otro.
5. **Logs de auditoría**: Toda acción de create/update/delete queda registrada con `user_id`, `tenant_id`, `ip`, timestamp y modelo afectado.

```php
// Ejemplo de Global Scope en el modelo base
class TenantModel extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $builder->where('tenant_id', auth()->user()?->tenant_id);
        });
    }
}
```

---

### 📊 ÍNDICES Y PERFORMANCE

**Índices críticos para la operación diaria:**

```sql
-- Búsqueda de operaciones por tenant (el más frecuente)
INDEX idx_operaciones_tenant (tenant_id)

-- Búsqueda por estado (dashboard y monitoreo)
INDEX idx_operaciones_estado (tenant_id, estado)

-- Búsqueda por DODA (bot de monitoreo)
INDEX idx_operaciones_doda (doda)

-- Historial DODA por operación 
INDEX idx_historial_doda_operacion (operacion_id, consultado_at)

-- Notificaciones pendientes de envío (cola de jobs)
INDEX idx_notificaciones_estado (estado, created_at)

-- Pedimentos por cliente y aduana
INDEX idx_pedimentos_cliente_aduana (tenant_id, cliente_id, aduana_id)
```

---

### 🚀 ORDEN DE IMPLEMENTACIÓN DE MIGRACIONES

> Las migraciones deben ejecutarse en este orden para respetar las claves foráneas:

1. `create_tenants_table`
2. `create_aduanas_table` *(catálogo global, sin tenant)*
3. `create_usuarios_table`
4. `create_clientes_table`
5. `create_importadores_table`
6. `create_bodegas_table`
7. `create_patentes_table`
8. `create_directorio_table`
9. `create_pedimentos_table`
10. `create_operaciones_table`
11. `create_operacion_documentos_table`
12. `create_operacion_historial_doda_table`
13. `create_notificaciones_table`
14. `create_facturacion_table`

---

## 🔥 PRÓXIMOS PASOS

> Los siguientes ítems son las acciones concretas a ejecutar en el desarrollo:

1. **Crear el proyecto Laravel 12** con configuración base
2. **Instalar `stancl/tenancy`** y configurar el middleware de tenant por subdominio
3. **Ejecutar las migraciones** en el orden definido arriba
4. **Crear los modelos Eloquent** con sus relaciones y Global Scopes
5. **Crear los Seeders** de catálogo (`aduanas` principalmente)
6. **Crear el módulo de autenticación** con roles y permisos
7. **Construir el CRUD de Clientes, Importadores, Bodegas, Patentes**
8. **Construir el módulo de Operaciones** (captura, edición, cambio de estado)
9. **Integrar el Bot SOIA** como Laravel Job que corre en queue
10. **Integrar notificaciones** WhatsApp y Email

---

## 🧠 NOTAS FINALES

Este documento es la fuente de verdad del proyecto NexaCore. Toda decisión técnica, de diseño de base de datos y de desarrollo deberá estar alineada con el flujo operativo real descrito aquí.

El sistema debe ser construido pensando en escalar desde las agencias de Reynosa hacia cualquier plaza aduanal de México.

---

*NexaCore — Tecnología para aduanas. Construido en Reynosa, para el mundo.*

---

**FIN DOCUMENTO MAESTRO v3.0**
