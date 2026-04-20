# 🚀 NexaCore — CRM Aduanal Inteligente (SaaS) · Documento Maestro V2

> **Versión:** 4.0  
> **Empresa operadora:** NexaCore  
> **Dominio principal:** nexacore.com.mx (landing page corporativa)  
> **Subdominio SaaS:** agencias.nexacore.com.mx (plataforma operativa)  
> **Mercado objetivo inicial:** Agencias aduanales en Reynosa, Tamaulipas, México  
> **Modelo de negocio:** SaaS multi-tenant — renta mensual por agencia  
> **Stack tecnológico:** Laravel (PHP) + Blade/Livewire + MySQL + Tailwind CSS  
> **Proyecto base existente:** Portal Crosspoint (portalcross)

---

## 📋 CHANGELOG RESPECTO AL DOCUMENTO MAESTRO V1

| Área | V1 (Documento Maestro original) | V2 (Este documento) |
|---|---|---|
| **Acceso** | Subdominio por agencia (`agencia.nexacore.com.mx`) | Subdominio único compartido (`agencias.nexacore.com.mx`) — todos los tenants acceden al mismo punto de entrada |
| **Departamentos** | Tráfico y Documentación separados | Flujo unificado: el usuario captura, edita y procesa la operación completa |
| **Modelo de operación** | Tráfico captura → Documentación procesa | Un solo usuario puede registrar y culminar la operación (adaptado a la realidad de la mayoría de las agencias) |
| **Nomenclatura** | `exportaciones` (tabla actual) | Se mantiene internamente como `operaciones` en el documento, pero la migración mapea desde `exportaciones` existente |
| **Base del código** | Proyecto nuevo desde cero | Se adapta el proyecto existente Crosspoint como base funcional |
| **Base de datos local** | No definida | MySQL local: `root` / `Cb15a33a1c` para pruebas antes de producción |
| **Interfaces** | No especificado | Se mantienen las interfaces de tráfico y documentación existentes adaptadas al flujo unificado |

---

## 📌 VISIÓN DEL PROYECTO

NexaCore es una plataforma SaaS multi-tenant diseñada para agencias aduanales en México. Su propósito es centralizar el control operativo de cada agencia, automatizar la consulta de estatus de modulación vía SOIA, y comunicar en tiempo real a clientes, usuarios y administradores mediante WhatsApp y correo electrónico.

> El sistema no es solo un CRM.  
> Es un **sistema integral de gestión operativa aduanal + automatización + comunicación + analítica avanzada**.

NexaCore actúa como la capa tecnológica entre las agencias y sus clientes, gestionando toda la infraestructura de envíos, facturación, cobros y soporte desde una sola plataforma que el usuario final nunca ve.

### 🏗️ Origen del proyecto

Este proyecto se basa en el sistema **Portal Crosspoint** ya funcional, que incluye:
- Gestión de operaciones (modelo `Exportacion`)
- Gestión de expedientes (modelo `Expediente`)
- Departamentos de tráfico y documentación con interfaces separadas
- Gestión de clientes, importadores, bodegas, patentes, aduanas
- Sistema de facturación y finanzas
- Reportes y notificaciones
- Roles: admin, admin_n2, documentador, cliente

**La adaptación SaaS implica agregar la capa multi-tenant** sobre esta base funcional existente.

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

## 🌐 ARQUITECTURA DE ACCESO Y SUBDOMINIO

### Dominio principal: `nexacore.com.mx`
- Landing page corporativa de NexaCore
- Información institucional, planes, contacto
- **NO contiene la aplicación SaaS**

### Subdominio SaaS: `agencias.nexacore.com.mx`
- Punto de entrada único para TODAS las agencias
- Pantalla de login unificada
- Tras el login, cada usuario ve únicamente la información de su tenant (agencia)
- El `tenant_id` se determina por el usuario autenticado, NO por subdominio

```
nexacore.com.mx                    → Landing page corporativa
agencias.nexacore.com.mx           → Login SaaS (todos los tenants)
agencias.nexacore.com.mx/dashboard → Dashboard del usuario (filtrado por tenant)
```

### ¿Por qué un solo subdominio y no uno por agencia?

| Criterio | Subdominio por agencia | Subdominio único (✅ elegido) |
|---|---|---|
| **Configuración DNS** | Una entrada DNS por cada agencia nueva | Una sola entrada DNS |
| **Certificados SSL** | Wildcard o uno por subdominio | Un solo certificado |
| **Onboarding** | Requiere configurar subdominio | Instantáneo: solo crear tenant + usuario |
| **Mantenimiento** | Complejo | Simple |
| **Aislamiento** | Por URL + scope | Solo por scope de DB (igualmente seguro) |
| **Branding** | Cada agencia con su URL | URL compartida (suficiente para MVP) |

> **Nota:** En fases futuras se puede agregar branding por subdominio (`crosspoint.nexacore.com.mx`) si lo requiere el negocio, sin cambiar la arquitectura de la BD.

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

## 🔁 FLUJO OPERATIVO REAL (BASE DEL SISTEMA) — V2 UNIFICADO

### ⚠️ CAMBIO CLAVE RESPECTO A V1: Flujo Unificado Tráfico + Documentación

En la V1, existían dos departamentos separados:
- **Tráfico**: capturaba la información inicial de la operación
- **Documentación**: tomaba lo capturado por tráfico y la culminaba

**En la V2**, el flujo se unifica porque:
> La mayoría de las agencias aduanales (si no es que todas las conocidas) **no tienen un departamento de tráfico separado**. Los documentadores capturan, trabajan y procesan toda la información ellos mismos.

**Nuevo flujo:**
1. El usuario (documentador/operador) **registra** la operación
2. El mismo usuario (u otro autorizado) puede **editar la referencia** para actualizar la data en cualquier momento
3. El usuario **completa** la operación (pedimento, DODA, etc.)
4. El bot SOIA monitorea automáticamente
5. Las notificaciones se disparan por cambio de estatus

### Interfaces: Se mantiene la estructura visual separada

Aunque el flujo es unificado, **se mantienen las interfaces diferenciadas** del proyecto Crosspoint existente:

| Interfaz | Propósito | Usuarios |
|---|---|---|
| **Vista Operaciones** (antes "tráfico") | Captura inicial, asignaciones, vista panorámica | Cualquier usuario autorizado |
| **Vista Documentador** | Trabajar la operación: editar datos, adjuntar docs, actualizar DODA | Cualquier usuario autorizado |
| **Vista Admin** | Configuración, reportes, gestión de usuarios | Administradores del tenant |
| **Vista Finanzas** | Facturación, expedientes, cobros | Usuarios con permiso de finanzas |

> **Regla:** Un usuario con permisos suficientes puede acceder a TODAS las vistas. No hay restricción por "departamento", Siempre y cuando pertenezca al mismo tenant.

---

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

### Paso 2 — Registro de la operación (cualquier usuario autorizado)

El usuario crea una nueva operación dentro del CRM con la información recibida.

#### Campos de captura (mapeados al modelo actual `Exportacion`):

**Identificación (automáticos)**
- ID de operación (autogenerado por el sistema)
- Fecha de captura (automática) → campo `fecha`
- Referencia interna (autogenerada, consecutiva) → campo `referencia`

**Cliente y partes**
- Cliente → `cliente_id`
- Importador → `importador_id`

**Mercancía**
- Producto (descripción en texto libre) → `nombre_producto`
- Número de factura comercial → `num_factura`

**Logística**
- Aduana de cruce *(obligatorio)* → `aduana_id`
- Fecha de cruce estimada *(opcional)* → campo nuevo 
- Bodega de destino *(opcional)* → `bodega_id`
- Número económico — unidad de transporte *(opcional)* → `num_thermo`
- Código Alpha *(opcional)* → `codigo_alpha`
- Patente *(según configuración)* → `patente_id`

**Asignación**
- Expediente *(opcional, se puede asignar después)* → `expediente_id`
- Prioridad → `prioridad`
- Observaciones → `observaciones`

> 👉 En este punto se crea la operación base. El expediente queda abierto y los documentos del cliente quedan adjuntos o referenciados.

---

### Paso 3 — Edición y actualización de la operación

> **DIFERENCIA CLAVE V2:** El mismo usuario que capturó (u otro autorizado) puede editar la operación en cualquier momento para:
> - Corregir datos iniciales
> - Agregar información faltante
> - Actualizar el estatus
> - Adjuntar documentos adicionales

No se requiere "pasar" la operación a otro departamento. La operación le pertenece al tenant y cualquier usuario autorizado del tenant puede trabajarla.

---

### Paso 4 — Proceso externo (fuera del CRM)

Los usuarios de la agencia trabajan el trámite aduanal en sus sistemas propios:

- CAAAREM3
- Aduanet
- Otros sistemas internos de cada agencia

Este proceso es externo al CRM y NexaCore no interfiere en él.

---

### Paso 5 — Actualización del pedimento y DODA ⭐

Una vez concluido el trámite en los sistemas externos, el usuario regresa al CRM y actualiza la operación con:

- **Número de pedimento** (vinculado al expediente)
- **Número de DODA** → campo `num_doda`

> 👉 Este paso es crítico: al registrar el DODA, se activa automáticamente el bot de monitoreo de modulación.

---

### Paso 6 — Bot de monitoreo SOIA (automatización DODA)

El sistema ejecuta automáticamente un proceso en segundo plano:

- Consulta periódica a la página de **SOIA** (Sistema de Operación de Inspección Aduanera)
- Obtención del estatus de modulación del DODA registrado
- Guardado del resultado completo en formato JSON
- Registro del historial de cambios con timestamps

**Estatus posibles (ejemplos):**
- Desaduanamiento libre (verde) ✅
- Reconocimiento aduanero (rojo) 🔴
- Reconocimiento aduanero concluido (rojo) 🔴
- Otros estatus del sistema SOIA

---

### Paso 7 — Detección de cambio de estatus

Cuando el estatus del DODA cambia respecto al último registro:

```
→ Se dispara el evento: doda.status_changed
```

---

### Paso 8 — Notificación automática

El sistema envía notificaciones de forma automática a los destinatarios configurados por el administrador de la agencia.

**Canales disponibles:**
- 📱 WhatsApp
- 📧 Correo electrónico

**Destinatarios configurables:**
- Clientes finales (importadores)
- Grupos de contacto
- Usuarios internos de la agencia
- Administrador / Gerencia de la agencia

> El administrador de cada agencia define previamente qué usuarios reciben notificaciones y por qué canal.

---

### Paso 9 — Registro de actividad

El sistema guarda automáticamente:
- Fecha y hora de la notificación
- Canal utilizado (WhatsApp / Email)
- Destinatario
- Resultado del envío (enviado / fallido / pendiente)
- Historial de reintentos si aplica

---

## 🧩 MÓDULOS DEL SISTEMA

### 🔐 Autenticación y Acceso

- Login unificado en `agencias.nexacore.com.mx`
- Cada usuario está vinculado a un `tenant_id` (su agencia)
- Tras el login, el sistema filtra automáticamente toda la información por `tenant_id`
- Roles y permisos configurables por el administrador de cada agencia
- Multi-tenant estricto: ningún usuario puede ver datos de otra agencia
- Recuperación de contraseña

**No hay registro público.** Las agencias se dan de alta por NexaCore (super admin). Los usuarios de cada agencia los crea el administrador del tenant.

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

### 📦 Operaciones (CORE) — Flujo Unificado V2

Entidad central del sistema. Contiene:

- Toda la información operativa y logística
- Estado actual de la operación
- Documentos adjuntos
- Relación con cliente e importador
- Número de pedimento y DODA
- Historial completo de estatus SOIA
- Log de notificaciones enviadas

**Tabla actual en BD:** `exportaciones`  
**Modelo Laravel:** `Exportacion`

**Capacidades del flujo unificado:**
- ✅ Cualquier usuario autorizado puede **crear** una operación
- ✅ Cualquier usuario autorizado puede **editar** una operación existente
- ✅ Cualquier usuario autorizado puede **actualizar DODA, pedimento y modulación**
- ✅ Cualquier usuario autorizado puede **adjuntar documentos**
- ✅ Cualquier usuario autorizado puede **cerrar** la operación
- ❌ Nadie puede ver operaciones de otro tenant

**Estados de la operación (mapeados del sistema actual):**

| Estado | Descripción | Rol mínimo |
|---|---|---|
| `capturada` | Se acaba de registrar la operación | documentador |
| `en_proceso` | El trámite externo está en curso | documentador |
| `doda_asignado` | Se registró DODA → bot SOIA activo | documentador |
| `libre` | SOIA: Desaduanamiento libre ✅ | automático (bot) |
| `reconocimiento` | SOIA: Reconocimiento aduanero 🔴 | automático (bot) |
| `reconocimiento_concluido` | Reconocimiento terminado | automático (bot) |
| `cerrada` | Operación finalizada | supervisor+ |
| `cancelada` | Operación cancelada | admin |

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
- Operaciones por usuario
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
- El backend envía directamente vía proveedor de WhatsApp (ej. Twilio, Meta Business API) y correo (ej. Resend, Mailgun, Gmail SMTP)

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
| Backend | Laravel (PHP) — proyecto existente Crosspoint como base |
| Base de datos producción | MySQL (Hostinger / VPS) |
| Base de datos local (dev) | MySQL local → user: `root`, password: `Cb15a33a1c` |
| Colas y jobs | Database Queue (actual) → Redis en fases futuras |
| Bot SOIA | Servicio dedicado (scraping / HTTP) en Laravel Jobs |
| Frontend | Blade (actual, funcional) + Tailwind CSS |
| Notificaciones WhatsApp | Meta Business API / Twilio |
| Notificaciones Email | Gmail SMTP (actual) → Resend / Mailgun (futuro) |
| Pagos | Stripe / Conekta / OpenPay |
| Almacenamiento docs | LOCAL + SFTP (actual) → S3 / Cloudflare R2 (futuro) |
| Multi-tenancy | `tenant_id` en tablas + Global Scopes en Eloquent |
| Acceso SaaS | Subdominio único `agencias.nexacore.com.mx` |

---

## 🔐 SEGURIDAD Y AISLAMIENTO DE DATOS

- Cada agencia opera en su propio tenant con scope estricto a nivel base de datos
- Ningún usuario puede acceder, ver ni modificar datos de otra agencia
- El administrador de cada agencia gestiona los roles y permisos de sus propios usuarios
- NexaCore (Super Admin) tiene acceso de solo lectura a métricas agregadas, nunca a datos operativos individuales de las agencias sin autorización
- Autenticación con Laravel Auth estándar (bcrypt + sessions)
- Comunicaciones cifradas (HTTPS / TLS)
- Logs de auditoría por acción de usuario

---

## 🗄️ DISEÑO DE BASE DE DATOS — ESQUEMA MULTI-TENANT V2

> **Estado:** Definición v2.0 (actualizado 2026-03-25)  
> **Estrategia:** Base de datos compartida con columna `tenant_id` + Global Scopes en Laravel  
> **Base de datos local para desarrollo:** `nexacore_dev` — MySQL `root` / `Cb15a33a1c`

---

### 🏛️ DECISIÓN ARQUITECTÓNICA: Base de datos compartida con `tenant_id`

**Razones:**
1. Permite lanzar rápido sin infraestructura compleja
2. El scope automático en Laravel garantiza el aislamiento
3. Onboarding instantáneo de nuevas agencias (solo INSERT en `tenants`)
4. Un único `agencias.nexacore.com.mx` simplifica operaciones
5. Compatible con el proyecto existente (solo agregar columna `tenant_id`)

> ⚠️ **Regla de oro:** NUNCA se ejecuta una query sobre tablas operativas sin filtrar por `tenant_id`. Esto se garantiza mediante Eloquent Global Scopes.

---

### 📐 ESQUEMA RELACIONAL COMPLETO

#### Diagrama de Relaciones

```
tenants
  └── usuarios (users)
  └── clientes (cliente)
  │     └── directorio (contactos) ← NUEVO
  │     └── operaciones (exportaciones)
  │     └── expedientes
  └── importadores
  │     └── operaciones (exportaciones)
  └── bodegas
  │     └── operaciones (exportaciones)
  └── aduanas (catálogo global, sin tenant_id)
  │     └── bodegas
  │     └── patentes (via aduana_patente)
  │     └── expedientes
  │     └── operaciones (exportaciones)
  └── patentes
  │     └── expedientes
  │     └── operaciones (exportaciones)
  └── expedientes
  │     └── operaciones (exportaciones)
  │     └── documentos
  └── operaciones (exportaciones)  ← TABLA CENTRAL
  │     └── documentos
  │     └── conceptos_adicionales
  │     └── notificaciones
  └── facturas (tabla existente de facturación interna)
  └── facturacion_nexacore ← NUEVO (cobro NexaCore → Agencia)
```

---

### 📋 TABLAS DETALLADAS

---

#### 🏢 Tabla: `tenants` *(NUEVA — Agencias aduanales clientes de NexaCore)*

> Tabla maestra del sistema multi-tenant. Cada registro representa una agencia aduanal contratante.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | Identificador único |
| `slug` | VARCHAR(100) UNIQUE | Identificador interno: `crosspoint`, `logisticsmasters` |
| `nombre_empresa` | VARCHAR(255) | Razón social de la agencia |
| `rfc` | VARCHAR(20) NULL | RFC de la agencia |
| `correo_admin` | VARCHAR(255) | Correo del administrador principal |
| `telefono` | VARCHAR(20) NULL | Teléfono de contacto |
| `logo_url` | VARCHAR(500) NULL | URL del logotipo |
| `plan` | ENUM('basico','profesional','enterprise') DEFAULT 'basico' | Plan contratado |
| `estado` | ENUM('activo','suspendido','cancelado') DEFAULT 'activo' | Estado de la suscripción |
| `fecha_inicio` | DATE | Fecha de alta en la plataforma |
| `fecha_vencimiento` | DATE NULL | Fecha de vencimiento del ciclo de pago |
| `max_usuarios` | INT DEFAULT 10 | Límite de usuarios según plan |
| `max_operaciones_mes` | INT NULL | Límite de operaciones mensuales (NULL = ilimitado) |
| `configuracion` | JSON NULL | Configuraciones específicas del tenant |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

---

#### 👤 Tabla: `users` *(EXISTENTE — se agrega `tenant_id`)*

> Tabla existente del proyecto Crosspoint. Se agrega la columna `tenant_id` para vincular cada usuario a su agencia.

| Campo | Tipo | Estado | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | existente | |
| `tenant_id` | BIGINT UNSIGNED FK | **NUEVO** | Referencia a `tenants.id` |
| `name` | VARCHAR(255) | existente | Nombre del usuario |
| `email` | VARCHAR(255) UNIQUE | existente | Correo (login) |
| `password` | VARCHAR(255) | existente | Contraseña bcrypt |
| `role` | VARCHAR(50) | existente | Rol: `super_admin`, `admin`, `admin_n2`, `documentador`, `cliente` |
| `cliente_id` | BIGINT UNSIGNED FK NULL | existente | Para usuarios tipo cliente |
| `active` | BOOLEAN | existente | Estado del usuario |
| `remember_token` | VARCHAR(100) NULL | existente | |
| `created_at` | TIMESTAMP | existente | |
| `updated_at` | TIMESTAMP | existente | |

**Nuevo rol `super_admin`:** Solo para usuarios de NexaCore que administran la plataforma completa. No pertenecen a ningún tenant (tenant_id = NULL).

**Roles y sus capacidades V2:**

| Rol | Scope | Descripción |
|---|---|---|
| `super_admin` | Plataforma completa | Administrador de NexaCore. Ve métricas globales, gestiona tenants |
| `admin` | Su tenant | Acceso total dentro de su agencia |
| `admin_n2` | Su tenant | Administrador nivel 2 (permisos parciales) |
| `documentador` | Su tenant | Captura, edita y procesa operaciones completas |
| `cliente` | Su tenant (sus ops) | Solo lectura de sus operaciones |

---

#### 🏭 Tabla: `cliente` *(EXISTENTE — se agrega `tenant_id`)*

| Campo | Tipo | Estado | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | existente | |
| `tenant_id` | BIGINT UNSIGNED FK | **NUEVO** | Referencia a `tenants.id` |
| `nombre` | VARCHAR(255) | existente | Nombre de la empresa |
| `rfc` | VARCHAR(20) NULL | existente | RFC |
| `tax_id` | VARCHAR(50) NULL | existente | Tax ID (extranjeros) |
| `telefono` | VARCHAR(20) NULL | existente | |
| `correo` | VARCHAR(255) NULL | existente | |
| `direccion` | TEXT NULL | existente | |
| `created_at` | TIMESTAMP | existente | |
| `updated_at` | TIMESTAMP | existente | |

---

#### 📒 Tabla: `directorio` *(NUEVA — Contactos del cliente)*

> Personas de contacto asociadas a cada cliente. Destinatarios de notificaciones WhatsApp/Email.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | |
| `tenant_id` | BIGINT UNSIGNED FK | Referencia a `tenants.id` |
| `cliente_id` | BIGINT UNSIGNED FK | Referencia a `cliente.id` |
| `nombre` | VARCHAR(150) | Nombre del contacto |
| `puesto` | VARCHAR(100) NULL | Cargo |
| `correo` | VARCHAR(255) NULL | |
| `telefono` | VARCHAR(20) NULL | |
| `whatsapp` | VARCHAR(20) NULL | Con código de país: +521... |
| `recibe_notificaciones` | BOOLEAN DEFAULT 1 | |
| `canal_preferido` | ENUM('whatsapp','email','ambos') DEFAULT 'ambos' | |
| `activo` | BOOLEAN DEFAULT 1 | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

---

#### 🚢 Tabla: `importadores` *(EXISTENTE — se agrega `tenant_id`)*

| Campo | Tipo | Estado |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | existente |
| `tenant_id` | BIGINT UNSIGNED FK | **NUEVO** |
| `nombre` | VARCHAR(255) | existente |
| `tax_id` | VARCHAR(50) NULL | existente |
| `rfc` | VARCHAR(20) NULL | existente |
| `pais` | VARCHAR(100) NULL | existente |
| `created_at` | TIMESTAMP | existente |
| `updated_at` | TIMESTAMP | existente |

---

#### 🏛️ Tabla: `aduanas` *(EXISTENTE — catálogo global, SIN `tenant_id`)*

| Campo | Tipo | Estado |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | existente |
| `nombre` | VARCHAR(200) | existente |
| `clave` | VARCHAR(10) NULL | existente |
| `created_at` | TIMESTAMP | existente |
| `updated_at` | TIMESTAMP | existente |

> 📌 Esta tabla NO lleva `tenant_id` — es un catálogo global compartido.

---

#### 🏪 Tabla: `bodegas` *(EXISTENTE — se agrega `tenant_id`)*

| Campo | Tipo | Estado |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | existente |
| `tenant_id` | BIGINT UNSIGNED FK | **NUEVO** |
| `nombre` | VARCHAR(200) | existente |
| `aduana_id` | BIGINT UNSIGNED FK | existente |
| `created_at` | TIMESTAMP | existente |
| `updated_at` | TIMESTAMP | existente |

---

#### 📜 Tabla: `patentes` *(EXISTENTE — se agrega `tenant_id`)*

| Campo | Tipo | Estado |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | existente |
| `tenant_id` | BIGINT UNSIGNED FK | **NUEVO** |
| `numero` | VARCHAR(50) | existente |
| `nombre` | VARCHAR(200) | existente |
| `created_at` | TIMESTAMP | existente |
| `updated_at` | TIMESTAMP | existente |

---

#### 📄 Tabla: `expedientes` *(EXISTENTE — se agrega `tenant_id`)*

| Campo | Tipo | Estado |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | existente |
| `tenant_id` | BIGINT UNSIGNED FK | **NUEVO** |
| `cliente_id` | BIGINT UNSIGNED FK | existente |
| `patente_id` | BIGINT UNSIGNED FK | existente |
| `aduana_id` | BIGINT UNSIGNED FK | existente |
| `numero_pedimento` | VARCHAR(50) | existente |
| `tipo_expediente` | VARCHAR(50) | existente |
| `fecha_pago_pedimento` | DATE NULL | existente |
| `fecha_apertura` | DATE NULL | existente |
| `fecha_cierre` | DATE NULL | existente |
| `categoria` | VARCHAR(100) NULL | existente |
| `observaciones` | TEXT NULL | existente |
| `estado` | VARCHAR(50) | existente |
| `registrado_por` | BIGINT UNSIGNED FK | existente |
| `cerrado_por` | BIGINT UNSIGNED FK NULL | existente |
| `clave_pedimento` | VARCHAR(20) NULL | existente |
| `created_at` | TIMESTAMP | existente |
| `updated_at` | TIMESTAMP | existente |
| `deleted_at` | TIMESTAMP NULL | existente (SoftDeletes) |

---

#### ⭐ Tabla: `exportaciones` *(EXISTENTE — Tabla Central, se agrega `tenant_id`, cambiar nombre por operaciones)*

> La tabla más importante del CRM. Cada registro representa una operación aduanal completa.

| Campo | Tipo | Estado | Descripción |
|---|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | existente | |
| `tenant_id` | BIGINT UNSIGNED FK | **NUEVO** | 🔑 Clave de aislamiento multi-tenant |
| `referencia` | VARCHAR(100) NULL | existente | Referencia consecutiva auto *(el tenant puede definir el formato en la configuración)*|
| `fecha_registro` | DATE | existente | Fecha de captura |
| `fecha_cruce_estimada` | DATE | existente | Fecha de cruce estimada |
| `cliente_id` | BIGINT UNSIGNED FK | existente | |
| `importador_id` | BIGINT UNSIGNED FK NULL | existente | |
| `nombre_producto` | TEXT | existente | |
| `bodega_id` | BIGINT UNSIGNED FK NULL | existente | |
| `num_factura` | VARCHAR(100) NULL | existente | |
| `aduana_id` | BIGINT UNSIGNED FK | existente | |
| `patente_id` | BIGINT UNSIGNED FK NULL | existente | |
| `expediente_id` | BIGINT UNSIGNED FK NULL | existente | |
| `num_thermo` | VARCHAR(50) NULL | existente | Número económico |
| `codigo_alpha` | VARCHAR(50) NULL | existente | |
| `num_doda` | VARCHAR(100) NULL | existente | Activa bot SOIA |
| `modulacion` | VARCHAR(100) NULL | existente | Resultado SOIA |
| `fecha_modulacion` | TIMESTAMP NULL | existente | |
| `usuario_registro_id` | BIGINT UNSIGNED FK | existente | Quien registró |
| `usuario_cierre_id` | BIGINT UNSIGNED FK NULL | existente | Quien termina la operación |
| `prioridad` | VARCHAR(20) DEFAULT 'normal' | existente | |
| `estado` | VARCHAR(50) DEFAULT 'capturada' | existente | |
| `observaciones` | TEXT NULL | existente | |
| `created_at` | TIMESTAMP | existente | |
| `updated_at` | TIMESTAMP | existente | |
| `deleted_at` | TIMESTAMP NULL | existente | SoftDeletes |

**Estados:** `capturada` → `en_proceso` → `doda_asignado` → `libre` / `reconocimiento` → `reconocimiento_concluido` → `cerrada` | `cancelada`

---

#### 📁 Tabla: `documentos` *(EXISTENTE — se agrega `tenant_id`)*

| Campo | Tipo | Estado |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | existente |
| `tenant_id` | BIGINT UNSIGNED FK | **NUEVO** |
| `pedimento_id` | BIGINT UNSIGNED FK NULL | existente |
| `operacion_id` | BIGINT UNSIGNED FK NULL | existente |
| `nombre` | VARCHAR(255) | existente |
| `ruta` | VARCHAR(500) | existente |
| `tipo_documento` | VARCHAR(50) | existente |
| `created_at` | TIMESTAMP | existente |
| `updated_at` | TIMESTAMP | existente |

---

#### 📡 Tabla: `operacion_historial_doda` *(NUEVA)*

> Registro cronológico de cada consulta al sistema SOIA para el DODA de una operación.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | |
| `tenant_id` | BIGINT UNSIGNED FK | |
| `operacion_id` | BIGINT UNSIGNED FK | Referencia a `operaciones.id` |
| `doda` | VARCHAR(100) | Número DODA consultado |
| `estatus_anterior` | VARCHAR(100) NULL | |
| `estatus_nuevo` | VARCHAR(100) | |
| `hubo_cambio` | BOOLEAN | |
| `respuesta_json` | JSON | JSON completo de SOIA |
| `consultado_at` | TIMESTAMP | |

---

#### 🔔 Tabla: `notificaciones` *(EXISTENTE — se agrega `tenant_id`)*

| Campo | Tipo | Estado |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | existente |
| `tenant_id` | BIGINT UNSIGNED FK | **NUEVO** |
| `tipo` | VARCHAR(50) | existente |
| `titulo` | VARCHAR(255) | existente |
| `mensaje` | TEXT | existente |
| `user_id` | BIGINT UNSIGNED FK | existente |
| `operacion_id` | BIGINT UNSIGNED FK NULL | existente |
| `leida` | BOOLEAN DEFAULT 0 | existente |
| `created_at` | TIMESTAMP | existente |
| `updated_at` | TIMESTAMP | existente |

---

#### 💳 Tabla: `facturacion_nexacore` *(NUEVA — Cobros de NexaCore a Agencias)*

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT UNSIGNED PK AI | |
| `tenant_id` | BIGINT UNSIGNED FK | Agencia a la que se factura |
| `periodo` | VARCHAR(7) | Período: `2026-03` |
| `monto_renta` | DECIMAL(10,2) | Cargo fijo mensual |
| `monto_extras` | DECIMAL(10,2) DEFAULT 0 | Cargos adicionales |
| `monto_total` | DECIMAL(10,2) | Total |
| `estado` | ENUM('pendiente','pagada','vencida','cancelada') DEFAULT 'pendiente' | |
| `fecha_emision` | DATE | |
| `fecha_vencimiento` | DATE | |
| `fecha_pago` | DATE NULL | |
| `metodo_pago` | VARCHAR(50) NULL | |
| `referencia_pago` | VARCHAR(255) NULL | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

---

### 🔗 RESUMEN DE RELACIONES ENTRE TABLAS

| Tabla | Relación | Tabla Relacionada | Tipo |
|---|---|---|---|
| `tenants` | tiene muchos | `users` | 1:N |
| `tenants` | tiene muchos | `cliente` | 1:N |
| `tenants` | tiene muchos | `importadores` | 1:N |
| `tenants` | tiene muchos | `bodegas` | 1:N |
| `tenants` | tiene muchos | `patentes` | 1:N |
| `tenants` | tiene muchos | `expedientes` | 1:N |
| `tenants` | tiene muchos | `exportaciones` | 1:N |
| `tenants` | tiene muchos | `facturacion_nexacore` | 1:N |
| `cliente` | tiene muchos | `directorio` | 1:N |
| `cliente` | tiene muchos | `expedientes` | 1:N |
| `cliente` | tiene muchos | `exportaciones` | 1:N |
| `aduanas` | tiene muchas | `bodegas` | 1:N |
| `aduanas` | tiene muchos | `expedientes` | 1:N |
| `aduanas` | tiene muchas | `exportaciones` | 1:N |
| `patentes` | tiene muchos | `expedientes` | 1:N |
| `patentes` | tiene muchas | `exportaciones` | 1:N |
| `expedientes` | tiene muchas | `exportaciones` | 1:N |
| `importadores` | tiene muchas | `exportaciones` | 1:N |
| `bodegas` | tiene muchas | `exportaciones` | 1:N |
| `exportaciones` | tiene muchos | `documentos` | 1:N |
| `exportaciones` | tiene muchos | `operacion_historial_doda` | 1:N |
| `exportaciones` | tiene muchas | `notificaciones` | 1:N |
| `exportaciones` | tiene muchos | `conceptos_adicionales` | 1:N |
| `users` | captura muchas | `exportaciones` | 1:N |

---

### 🔐 REGLAS DE AISLAMIENTO MULTI-TENANT (Implementación Laravel)

1. **Global Scope automático**: El modelo base `TenantModel` incluye un scope global que siempre filtra por `tenant_id` del usuario autenticado.
2. **Middleware de tenant**: Al recibir cada request, el middleware establece el `tenant_id` activo basado en el usuario autenticado (NO por subdominio).
3. **Auto-asignación de tenant_id**: Al crear cualquier registro, se asigna automáticamente el `tenant_id` del usuario autenticado.
4. **Validaciones en formularios**: Siempre validar que los IDs referenciados pertenezcan al mismo `tenant_id`.
5. **Tests de aislamiento**: Suite de tests que verifica que ningún tenant puede leer datos de otro.
6. **Excepción `super_admin`**: Los usuarios `super_admin` no tienen `tenant_id` y pueden ver métricas globales.

```php
// Modelo base para todos los modelos con tenant_id
class TenantModel extends Model
{
    protected static function booted(): void
    {
        // Filtrar automáticamente por tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('tenant_id', auth()->user()->tenant_id);
            }
        });

        // Auto-asignar tenant_id al crear
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
```

---

## 🚀 PLAN DE MIGRACIÓN: Crosspoint → NexaCore SaaS

### Fase 0 — Preparación de base de datos local

**Base de datos local para desarrollo y pruebas:**
```
Host: localhost (127.0.0.1)
Puerto: 3306
Usuario: root
Password: Cb15a33a1c
Base de datos: nexacore_dev
```

**Pasos:**
1. Crear la BD `nexacore_dev` en MySQL local
2. Configurar `.env` local para apuntar a la BD de desarrollo
3. Importar estructura existente de Crosspoint
4. Ejecutar migraciones de multi-tenancy

---

### Fase 1 — Multi-tenancy base

1. **Crear tabla `tenants`**
2. **Agregar columna `tenant_id`** a todas las tablas operativas:
   - `users`
   - `cliente`
   - `importadores`
   - `bodegas`
   - `patentes`
   - `expedientes`
   - `operaciones`
   - `documentos`
   - `notificaciones`
   - `facturas`
   - `conceptos_adicionales`
   - `recorridos`
   - `referencias`
   - `reportes_acceso`
3. **Crear modelo base `TenantModel`** con Global Scope
4. **Crear middleware `EnsureTenant`** que valide que el usuario tiene tenant
5. **Migrar modelos existentes** para extender `TenantModel`
6. **Insertar tenant inicial** (Crosspoint) y asignar `tenant_id` a datos existentes

---

### Fase 2 — Autenticación SaaS

1. **Adaptar login** para funcionar en `agencias.nexacore.com.mx`
2. **Agregar rol `super_admin`** para administradores de NexaCore
3. **Crear panel Super Admin** para gestión de tenants
4. **Crear flujo de alta de agencia** (NexaCore crea tenant + usuario admin)

---

### Fase 3 — Flujo unificado de operaciones

1. **Unificar interfaces** de tráfico y documentación
2. **Permitir que cualquier usuario autorizado** pueda crear, editar y completar operaciones
3. **Mantener las vistas existentes** pero sin restricción por departamento
4. **Implementar permisos granulares** por rol dentro del tenant

---

### Fase 4 — Tablas nuevas

1. **Crear tabla `directorio`** (contactos de clientes)
2. **Crear tabla `operacion_historial_doda`**
3. **Crear tabla `facturacion_nexacore`**

---

### Fase 5 — Bot SOIA y Notificaciones

1. **Implementar bot de monitoreo SOIA** como Laravel Job en queue
2. **Integrar notificaciones multicanal** (WhatsApp + Email)
3. **Implementar plantillas de mensajes** por tenant
4. **Log completo de envíos**

---

### Fase 6 — Facturación NexaCore → Agencias

1. **Generación automática de facturas mensuales**
2. **Integración con pasarela de pagos**
3. **Panel financiero NexaCore**
4. **Suspensión automática por impago**

---

### Fase 7 — Analítica e Inteligencia

1. **Dashboards por tenant**
2. **Dashboard NexaCore (métricas globales)**
3. **Reportes automáticos**
4. **Interfaces de monitoreo en tiempo real**

---

## 🚀 ORDEN DE IMPLEMENTACIÓN DE MIGRACIONES (Nuevas)

> Las migraciones nuevas se ejecutan sobre la BD existente de Crosspoint:

1. `create_tenants_table` ← **NUEVA**
2. `add_tenant_id_to_users_table` ← **NUEVA**
3. `add_tenant_id_to_cliente_table` ← **NUEVA**
4. `add_tenant_id_to_importadores_table` ← **NUEVA**
5. `add_tenant_id_to_bodegas_table` ← **NUEVA**
6. `add_tenant_id_to_patentes_table` ← **NUEVA**
7. `add_tenant_id_to_expedientes_table` ← **NUEVA**
8. `add_tenant_id_to_exportaciones_table` ← **NUEVA**
9. `add_tenant_id_to_documentos_table` ← **NUEVA**
10. `add_tenant_id_to_notificaciones_table` ← **NUEVA**
11. `add_tenant_id_to_facturas_table` ← **NUEVA**
12. `add_tenant_id_to_conceptos_adicionales_table` ← **NUEVA**
13. `add_tenant_id_to_recorridos_table` ← **NUEVA**
14. `add_tenant_id_to_referencias_table` ← **NUEVA**
15. `add_tenant_id_to_reportes_acceso_table` ← **NUEVA**
16. `create_directorio_table` ← **NUEVA**
17. `create_operacion_historial_doda_table` ← **NUEVA**
18. `create_facturacion_nexacore_table` ← **NUEVA**
19. `seed_initial_tenant_crosspoint` ← **SEEDER** para asignar tenant existente

---

## ⚠️ RIESGOS Y MITIGACIONES

| Riesgo | Mitigación |
|---|---|
| Cambios en la estructura de SOIA | Diseñar el bot con adaptadores intercambiables, alertas de fallo |
| Bloqueo de scraping por aduana | Explorar API oficial si existe; fallback manual con aviso |
| Políticas de WhatsApp Business API | Usar proveedores certificados (Meta BSP); preparar fallback a Email |
| Agencias que no pagan | Suspensión automática + notificaciones previas; política de gracia |
| Fuga de datos entre tenants | Tests de aislamiento como parte del CI/CD + Global Scopes |
| Dependencia de proveedor de pagos | Arquitectura de pagos con proveedor intercambiable |
| Migración de datos existentes | Seeder que asigne `tenant_id` a todos los registros existentes de Crosspoint |
| Performance con muchos tenants | Índices en `tenant_id` en todas las tablas + monitoreo de queries |

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

## 📊 ÍNDICES Y PERFORMANCE

**Índices críticos (todos incluyen `tenant_id` como primera columna):**

```sql
-- Operaciones por tenant (el más frecuente)
INDEX idx_exportaciones_tenant (tenant_id)
INDEX idx_exportaciones_tenant_estado (tenant_id, estado)
INDEX idx_exportaciones_tenant_fecha (tenant_id, fecha)

-- Búsqueda por DODA (bot de monitoreo)
INDEX idx_exportaciones_doda (num_doda)

-- Expedientes por tenant
INDEX idx_expedientes_tenant (tenant_id)

-- Clientes por tenant
INDEX idx_cliente_tenant (tenant_id)

-- Documentos por tenant
INDEX idx_documentos_tenant (tenant_id)

-- Historial DODA
INDEX idx_historial_doda_exportacion (exportacion_id, consultado_at)

-- Notificaciones pendientes
INDEX idx_notificaciones_estado (tenant_id, leida, created_at)
```

---

## 🧠 NOTAS FINALES

1. **Este documento V2 reemplaza al V1** como fuente de verdad del proyecto NexaCore SaaS.
2. **El proyecto existente Crosspoint es la base funcional.** No se reescribe desde cero.
3. **La adaptación es incremental:** primero multi-tenancy, luego flujo unificado, luego funcionalidades nuevas.
4. **La BD local** (`nexacore_dev`, root/Cb15a33a1c) se usa para desarrollo y pruebas antes de cualquier deploy a producción.
5. **El acceso es por `agencias.nexacore.com.mx`**, no por subdominio individual por agencia.
6. **El flujo es unificado:** un mismo usuario puede registrar y culminar operaciones, sin necesidad de pasar por departamentos separados.

---

*NexaCore — Tecnología para aduanas. Construido en Reynosa, para el mundo.*

---

**FIN DOCUMENTO MAESTRO V2 (v4.0)**
