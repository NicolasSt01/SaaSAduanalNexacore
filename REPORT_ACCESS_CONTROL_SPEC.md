# 📊 Sistema de Control de Acceso a Reportes por Tenant

## 📋 Descripción

Sistema que permite al **Super Admin** controlar qué tipos de reportes puede generar cada tenant, basado en su plan de suscripción. Toda la configuración se guarda en el campo JSON `configuracion` de la tabla `tenants`.

---

## 🎯 Objetivo

- **Control granular**: Definir qué reportes puede acceder cada tenant
- **Basado en planes**: Diferentes planes = diferentes accesos a reportes
- **Flexible**: Fácil agregar nuevos reportes en el futuro
- **Centralizado**: Todo en el JSON de configuración del tenant

---

## 📑 Reportes Disponibles

### Reportes Actuales:

| ID del Reporte | Nombre | Descripción | Ruta Actual |
|----------------|--------|-------------|-------------|
| `clientes` | Reporte de Clientes | Listado y estadísticas de clientes | `/reportes/clientes` |
| `operacion_semanal` | Operación Semanal | Resumen semanal de operaciones | `/reportes/operacion-semanal` |
| `remesas` | Reporte de Remesas | Control de remesas | `/reportes/remesas` |
| `clientes_pdf` | Envío PDF Clientes | Envío automático de PDF a clientes | `/reportes/clientes-pdf` |
| `aduanas` | Reporte Aduanas | Estadísticas por aduana | `/reportes/aduanas` |
| `patron_clientes` | Patrón de Clientes | Análisis de patrones de clientes | `/reportes/patron-clientes` |

### Reportes Futuros:

| ID del Reporte | Nombre | Estado |
|----------------|--------|--------|
| `financiero` | Reporte Financiero | 🚧 En desarrollo |
| `logistica` | Reporte de Logística y Tiempo | 🚧 En desarrollo |

---

## 🔧 Estructura del JSON de Configuración

### Ubicación en el JSON:

```json
{
    "bot": { ... },
    "limites": { ... },
    "features_enabled": [ ... ],
    
    "reportes": {
        "enabled": [
            "clientes",
            "operacion_semanal",
            "remesas"
        ],
        "disabled": [
            "financiero",
            "logistica"
        ]
    }
}
```

### Estructura Completa del Tenant:

```json
{
    "bot": {
        "mode": "manual",
        "consultas_limite_mes": 20,
        "consultas_mes": 0,
        "consultas_mes_periodo": "2026-04"
    },
    "limites": {
        "recursos": {
            "clientes": 5,
            "importadores": 2,
            "bodegas": 1,
            "aduanas": 1,
            "patentes": 1,
            "pedimentos_mes": 20,
            "documentos_mes": 40
        },
        "funcionalidades": {
            "reportes_mes": 0,
            "correos_dia": 10,
            "whatsapp_mes": 0
        }
    },
    "features_enabled": [
        "basic_dashboard",
        "email_notifications"
    ],
    "reportes": {
        "enabled": [
            "clientes",
            "operacion_semanal",
            "remesas",
            "clientes_pdf",
            "aduanas",
            "patron_clientes"
        ],
        "disabled": [
            "financiero",
            "logistica"
        ]
    }
}
```

---

## 📦 Configuración por Defecto por Plan

### Plan Trial
```json
{
    "reportes": {
        "enabled": [
            "clientes",
            "operacion_semanal"
        ],
        "disabled": [
            "remesas",
            "clientes_pdf",
            "aduanas",
            "patron_clientes",
            "financiero",
            "logistica"
        ]
    }
}
```

### Plan Básico
```json
{
    "reportes": {
        "enabled": [
            "clientes",
            "operacion_semanal",
            "remesas",
            "aduanas"
        ],
        "disabled": [
            "clientes_pdf",
            "patron_clientes",
            "financiero",
            "logistica"
        ]
    }
}
```

### Plan Profesional
```json
{
    "reportes": {
        "enabled": [
            "clientes",
            "operacion_semanal",
            "remesas",
            "clientes_pdf",
            "aduanas",
            "patron_clientes"
        ],
        "disabled": [
            "financiero",
            "logistica"
        ]
    }
}
```

### Plan Enterprise
```json
{
    "reportes": {
        "enabled": [
            "clientes",
            "operacion_semanal",
            "remesas",
            "clientes_pdf",
            "aduanas",
            "patron_clientes",
            "financiero",
            "logistica"
        ],
        "disabled": []
    }
}
```

---

## 🎨 Interfaz de Usuario

### En el Panel de Super Admin (`/nexacore-admin/tenants/{id}/capabilities`)

Se agregará una nueva sección:

```
┌─────────────────────────────────────────────────────┐
│  📊 Configuración de Reportes                       │
│  Controla qué reportes puede generar este tenant    │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ☑️ Clientes                                        │
│  ☑️ Operación Semanal                               │
│  ☑️ Reporte de Remesas                              │
│  ☑️ Envío PDF Clientes                              │
│  ☑️ Reporte Aduanas                                 │
│  ☑️ Patrón de Clientes                              │
│  ⬜ Reporte Financiero (Próximamente)               │
│  ⬜ Logística y Tiempo (Próximamente)               │
│                                                     │
│  [Seleccionar Todos] [Deseleccionar Todos]          │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 🔒 Control de Acceso en los Reportes

### Middleware o Verificación en Controllers:

```php
// En cada controller de reportes
public function index()
{
    $tenant = auth()->user()->tenant;
    
    if (!$tenant->hasReportAccess('clientes')) {
        abort(403, 'No tienes acceso a este reporte. Contacta a soporte.');
    }
    
    // Lógica del reporte...
}
```

### Métodos en el Modelo Tenant:

```php
/**
 * Verifica si el tenant tiene acceso a un reporte específico
 */
public function hasReportAccess(string $reporte): bool
{
    $config = $this->getConfig();
    $enabled = $config['reportes']['enabled'] ?? [];
    
    return in_array($reporte, $enabled);
}

/**
 * Obtiene todos los reportes habilitados
 */
public function getEnabledReports(): array
{
    $config = $this->getConfig();
    return $config['reportes']['enabled'] ?? [];
}

/**
 * Obtiene todos los reportes deshabilitados
 */
public function getDisabledReports(): array
{
    $config = $this->getConfig();
    return $config['reportes']['disabled'] ?? [];
}

/**
 * Verifica si puede generar un reporte específico
 */
public function canGenerateReport(string $reporte): bool
{
    return $this->hasReportAccess($reporte);
}
```

---

## 🚀 Implementación

### Archivos a Modificar:

1. **`app/Models/Tenant.php`**
   - Agregar métodos de control de reportes

2. **`app/Http/Controllers/Admin/TenantController.php`**
   - Actualizar `updateCapabilities` para manejar reportes
   - Agregar lista de reportes disponibles

3. **`resources/views/admin/tenants/capabilities.blade.php`**
   - Agregar sección de configuración de reportes

4. **`app/Http/Controllers/ReporteClienteMailController.php`** y otros
   - Agregar verificación de acceso en cada método

5. **Rutas de reportes**
   - Agregar middleware o verificación

---

## 📊 Flujo de Uso

### Como Super Admin:

```
1. Ve a /nexacore-admin/tenants/{id}/capabilities
   ↓
2. Busca la sección "📊 Configuración de Reportes"
   ↓
3. Selecciona los reportes que deseas habilitar
   ↓
4. Guarda cambios
   ↓
5. El JSON se actualiza automáticamente
   ↓
6. El tenant ahora tiene acceso solo a los reportes seleccionados
```

### Como Usuario del Tenant:

```
1. Ve a la sección de Reportes
   ↓
2. Solo ve los reportes que tiene habilitados
   ↓
3. Si intenta acceder a un reporte no habilitado:
   → Error 403: "No tienes acceso a este reporte"
```

---

## 🎯 Plan de Implementación

### Fase 1: Backend ✅
- [ ] Agregar métodos en Tenant.php
- [ ] Actualizar TenantController
- [ ] Agregar verificación en controllers de reportes

### Fase 2: Frontend ✅
- [ ] Agregar UI en capabilities.blade.php
- [ ] Agregar checkboxes para cada reporte
- [ ] Mostrar reportes futuros como "Próximamente"

### Fase 3: Protección de Rutas ✅
- [ ] Agregar middleware o verificación en controllers
- [ ] Redirigir a página de "Sin acceso" si no tiene permiso

### Fase 4: Testing ✅
- [ ] Probar con diferentes planes
- [ ] Verificar que el JSON se guarda correctamente
- [ ] Probar acceso a reportes habilitados y deshabilitados

---

## 💡 Características Adicionales (Futuras)

- **Límite de generaciones por mes**: Ej. máximo 10 reportes al mes
- **Tracking de uso**: Registrar cuántas veces se genera cada reporte
- **Alertas**: Notificar cuando un tenant está cerca del límite
- **Reportes personalizados**: Crear reportes custom para tenants específicos
- **Exportación**: Permitir exportar configuración de reportes

---

## 🔍 Monitoreo y Debugging

### Ver reportes habilitados de un tenant:

```php
$tenant = Tenant::find(1);
$enabledReports = $tenant->getEnabledReports();
// ['clientes', 'operacion_semanal', 'remesas', ...]
```

### Verificar acceso:

```php
if ($tenant->hasReportAccess('financiero')) {
    // Tiene acceso al reporte financiero
}
```

### Logs:

```php
\Log::info('Tenant generó reporte', [
    'tenant_id' => $tenant->id,
    'reporte' => 'clientes',
    'timestamp' => now(),
]);
```

---

## 📝 Notas Importantes

1. **No se crean nuevas columnas**: Todo se guarda en el JSON existente
2. **Flexible**: Fácil agregar nuevos reportes en el futuro
3. **Compatible con planes existentes**: Se puede aplicar a tenants actuales
4. **Escalable**: Puede agregar límites de uso en el futuro
5. **Seguro**: Verificación tanto en frontend como en backend

---

## ✅ Checklist de Implementación

- [ ] Métodos en Tenant.php
- [ ] Actualizar TenantController
- [ ] UI en capabilities.blade.php
- [ ] Verificación en controllers de reportes
- [ ] Probar con diferentes planes
- [ ] Documentación actualizada
- [ ] Logs de debugging
- [ ] Mensajes de error amigables

---

**Especificación lista para implementación** 🚀
