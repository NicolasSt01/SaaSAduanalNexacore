# 📊 Guía Completa de la Tabla `tenants`

## 📋 Resumen General

La tabla `tenants` almacena **todas las agencias aduanales** que usan la plataforma NexaCore Aduanal como SaaS. Cada tenant es un cliente independiente con su propia configuración, límites y capacidades.

**Total de columnas:** 15 campos (¡consolidados!)

---

## 🗂️ Categorías de Campos

### 1️⃣ **IDENTIFICACIÓN BÁSICA** (6 campos)

| Campo | Tipo | Default | Descripción | Ejemplo |
|-------|------|---------|-------------|---------|
| `id` | BIGINT | Auto | ID único del tenant | `1` |
| `slug` | VARCHAR(255) | - | Subdominio único (identificador URL) | `agencia-norte` |
| `nombre_empresa` | VARCHAR(255) | - | Nombre de la agencia aduanal | `Agencia Aduanal del Norte` |
| `rfc` | VARCHAR(20) | NULL | RFC de la empresa | `AAN010101ABC` |
| `correo_admin` | VARCHAR(255) | - | Email del administrador del tenant | `admin@agencianorte.com` |
| `telefono` | VARCHAR(20) | NULL | Teléfono de contacto | `+52 656 123 4567` |

---

### 2️⃣ **BRANDING Y APARIENCIA** (1 campo)

| Campo | Tipo | Default | Descripción | Ejemplo |
|-------|------|---------|-------------|---------|
| `logo_url` | VARCHAR(500) | NULL | URL del logo de la empresa | `https://cdn.ejemplo.com/logos/agencia.png` |

---

### 3️⃣ **PLAN Y ESTADO** (4 campos)

| Campo | Tipo | Default | Descripción | Valores |
|-------|------|---------|-------------|---------|
| `plan` | ENUM | `basico` | Plan de membresía actual | `basico`, `profesional`, `enterprise` |
| `estado` | ENUM | `activo` | Estado actual del tenant | `activo`, `suspendido`, `cancelado` |
| `fecha_inicio` | DATE | - | Fecha de inicio de la suscripción | `2026-01-15` |
| `fecha_vencimiento` | DATE | NULL | Fecha de vencimiento de la suscripción | `2026-02-15` |

---

### 4️⃣ **LÍMITES GENERALES** (2 campos)

| Campo | Tipo | Default | Descripción | Ejemplo |
|-------|------|---------|-------------|---------|
| `max_usuarios` | INT | `10` | Máximo de usuarios permitidos | `5` |
| `max_operaciones_mes` | INT | NULL | Máximo de operaciones por mes | `100` |

---

### 5️⃣ **CONFIGURACIÓN FLEXIBLE (JSON)** (1 campo) ⭐

| Campo | Tipo | Default | Descripción | Contenido |
|-------|------|---------|-------------|-----------|
| `configuracion` | JSON | NULL | **TODA la configuración del tenant** | Ver sección abajo |

#### 🔧 Estructura completa de `configuracion`:

```json
{
    // ==========================================
    // 🤖 SOIA-BOT CONFIGURATION
    // ==========================================
    "bot": {
        "mode": "manual",  // "manual", "automatico", "deshabilitado"
        "consultas_limite_mes": 50,  // Límite de consultas por mes (null = ilimitado)
        "consultas_mes": 42,  // Contador actual de consultas este mes
        "consultas_mes_periodo": "2026-04"  // Periodo actual (YYYY-MM)
    },
    
    // ==========================================
    // 📏 LÍMITES DE RECURSOS
    // ==========================================
    "limites": {
        "recursos": {
            "clientes": 20,  // Máximo de clientes (null = ilimitado)
            "importadores": 10,
            "bodegas": 5,
            "aduanas": 3,
            "patentes": 4,
            "pedimentos_mes": 100,
            "documentos_mes": 500
        },
        "funcionalidades": {
            "reportes_mes": 50,  // Máximo de reportes por mes
            "correos_dia": 100,  // Máximo de correos por día
            "whatsapp_mes": 200  // Máximo de WhatsApp por mes
        }
    },
    
    // ==========================================
    // ✅ FEATURES HABILITADAS
    // ==========================================
    "features_enabled": [
        "basic_dashboard",
        "email_notifications",
        "advanced_reports",
        "api_access",
        "priority_support"
    ],
    
    // ==========================================
    // ⚙️ OTRAS CONFIGURACIONES (legacy)
    // ==========================================
    // Metas analíticas
    "meta_ideal_diaria": 50,
    "meta_buena_diaria": 30,
    "meta_mala_diaria": 10,
    "meta_ideal_mensual": 1000,
    "proyeccion_1": 1500,
    "proyeccion_2": 2000,
    
    // Plantillas de correo
    "plantilla_correo_modulacion": "basica_azul",
    "plantilla_correo_personalizada": "custom_template",
    
    // Reglas de notificación
    "notificaciones": {
        "solo_rojos_clientes": ["Cliente A", "Cliente B"],
        "correos_bcc_fijos": ["gerencia@empresa.com"]
    },
    
    // Integración PECEM
    "pecem": {
        "correos_internos_bcc": ["sistemas@empresa.com"],
        "url_base": "https://pecem.mat.sat.gob.mx/..."
    },
    
    // Permisos habilitados
    "permisos": ["gestionar_usuarios", "gestionar_patentes", ...],
    
    // Facturación
    "renta_mensual": 500.00,
    "dias_gracia": 5
}
```

---

### 6️⃣ **REFERENCIAS Y FOLIOS** (2 campos)

| Campo | Tipo | Default | Descripción | Ejemplo |
|-------|------|---------|-------------|---------|
| `referencia_prefijo` | VARCHAR(10) | NULL | Prefijo para referencias de operaciones | `NORTE` |
| `referencia_consecutivo` | INT UNSIGNED | `0` | Consecutivo actual de referencias | `1234` |

**Formato generado:** `NORTE-2601234` (Prefijo-AñoConsecutivo)

---

### 7️⃣ **TIMESTAMPS** (2 campos)

| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| `created_at` | TIMESTAMP | NOW() | Fecha de creación del tenant |
| `updated_at` | TIMESTAMP | NOW() | Fecha de última actualización |

---

## 📊 Resumen Visual por Categoría

```
┌─────────────────────────────────────────────────────────┐
│                    TABLA TENANTS                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  🆔 IDENTIFICACIÓN (6)                                  │
│  ├── id, slug, nombre_empresa, rfc                     │
│  ├── correo_admin, telefono                            │
│  │                                                      │
│  🎨 BRANDING (1)                                        │
│  └── logo_url                                          │
│  │                                                      │
│  💳 PLAN Y ESTADO (4)                                   │
│  ├── plan, estado                                      │
│  └── fecha_inicio, fecha_vencimiento                   │
│  │                                                      │
│  📏 LÍMITES GENERALES (2)                               │
│  └── max_usuarios, max_operaciones_mes                 │
│  │                                                      │
│  ⚙️ CONFIGURACIÓN JSON (1) ⭐                           │
│  ├── bot (mode, consultas_limite_mes, etc.)            │
│  ├── limites (recursos, funcionalidades)               │
│  ├── features_enabled                                  │
│  ├── metas analíticas, plantillas, permisos            │
│  └── facturación, PECEM, notificaciones                │
│  │                                                      │
│  🔢 REFERENCIAS (2)                                     │
│  └── referencia_prefijo, referencia_consecutivo        │
│  │                                                      │
│  📅 TIMESTAMPS (2)                                      │
│  └── created_at, updated_at                            │
│                                                         │
│  TOTAL: 15 CAMPOS (antes 33!)                           │
└─────────────────────────────────────────────────────────┘
```

---

## 🔍 Consultas Útiles

### Ver todos los tenants activos con su uso del bot:
```sql
SELECT 
    id,
    nombre_empresa,
    plan,
    bot_mode,
    bot_consultas_limite_mes AS limite,
    bot_consultas_mes AS usadas,
    bot_consultas_mes_periodo AS periodo,
    CASE 
        WHEN bot_consultas_limite_mes IS NULL THEN '∞'
        ELSE CONCAT(bot_consultas_mes, '/', bot_consultas_limite_mes)
    END AS uso_bot
FROM tenants
WHERE estado = 'activo';
```

### Ver tenants que alcanzaron su límite del bot este mes:
```sql
SELECT 
    id,
    nombre_empresa,
    bot_consultas_mes AS usadas,
    bot_consultas_limite_mes AS limite
FROM tenants
WHERE estado = 'activo'
    AND bot_consultas_limite_mes IS NOT NULL
    AND bot_consultas_mes >= bot_consultas_limite_mes
    AND bot_consultas_mes_periodo = DATE_FORMAT(NOW(), '%Y-%m');
```

### Ver configuración completa de un tenant:
```sql
SELECT 
    id,
    nombre_empresa,
    plan,
    estado,
    max_usuarios,
    max_operaciones_mes,
    bot_mode,
    bot_consultas_limite_mes,
    limite_clientes,
    limite_importadores,
    limite_bodegas,
    limite_patentes,
    limite_pedimentos_mes,
    features_enabled
FROM tenants
WHERE id = 1;
```

---

## 📝 Métodos del Modelo Tenant

### Límites del Bot:
```php
$tenant->getBotConsultasUsadas();        // Retorna consultas usadas este mes
$tenant->getBotConsultasLimite();        // Retorna límite configurado
$tenant->canMakeBotConsulta();           // true/false si puede hacer más consultas
$tenant->incrementarBotConsultas(5);     // Incrementa contador en 5
$tenant->getBotMode();                   // 'manual', 'automatico', 'deshabilitado'
$tenant->isBotEnabled();                 // true/false
$tenant->isBotAutomatic();               // true/false
```

### Límites de Recursos:
```php
$tenant->getLimite('clientes');          // Retorna límite de clientes
$tenant->getUso('clientes');             // Retorna uso actual
$tenant->canAddResource('clientes');     // true/false si puede agregar más
$tenant->getUsoPorcentaje('clientes');   // Retorna porcentaje usado (0-100)
$tenant->getRecursoInfo('clientes');     // Array completo de info
```

### Features:
```php
$tenant->hasFeature('api_access');       // true/false
```

---

## 🎯 Configuración por Defecto por Plan

| Recurso | Trial | Básico | Profesional | Enterprise |
|---------|-------|--------|-------------|------------|
| **Bot Mode** | Manual | Manual | Automático | Automático |
| **Bot Consultas/Mes** | 15 | 50 | 200 | ∞ |
| **Max Usuarios** | 1 | 5 | 20 | 100 |
| **Clientes** | 2 | 10 | 50 | ∞ |
| **Importadores** | 1 | 5 | 20 | ∞ |
| **Bodegas** | 1 | 3 | 10 | ∞ |
| **Patentes** | 1 | 3 | 10 | ∞ |
| **Pedimentos/Mes** | 10 | 100 | 500 | ∞ |
| **Documentos/Mes** | 20 | 200 | 1000 | ∞ |
| **Reportes/Mes** | 0 | 10 | 50 | ∞ |
| **Correos/Día** | 10 | 50 | 200 | ∞ |
| **WhatsApp/Mes** | 0 | 100 | 500 | ∞ |

---

**Documento creado:** 2026-04-03  
**Versión:** 1.0  
**Total de campos:** 33
