# 📊 Sistema de Control de Acceso a Reportes - Implementación Completa

## ✅ Implementación Completada

Se ha implementado exitosamente el sistema de control de acceso a reportes por tenant. Ahora como **Super Admin** puedes controlar exactamente qué reportes puede generar cada tenant basado en su plan de suscripción.

---

## 🎯 Lo que se Implementó

### **1. Métodos en el Modelo Tenant** (`app/Models/Tenant.php`)

Se agregaron 7 nuevos métodos para controlar el acceso a reportes:

```php
// Lista todos los reportes disponibles (8 reportes en total)
Tenant::getAllAvailableReports()

// Verifica si el tenant tiene acceso a un reporte específico
$tenant->hasReportAccess('clientes') // true/false

// Obtiene todos los reportes habilitados
$tenant->getEnabledReports() // ['clientes', 'operacion_semanal', ...]

// Obtiene todos los reportes deshabilitados
$tenant->getDisabledReports() // ['financiero', 'logistica', ...]

// Alias de hasReportAccess
$tenant->canGenerateReport('remesas') // true/false

// Obtiene la configuración completa de reportes
$tenant->getReportConfig() // ['enabled' => [...], 'disabled' => [...]]

// Obtiene solo reportes activos habilitados (excluye coming_soon)
$tenant->getActiveEnabledReports()
```

### **2. Reportes Disponibles**

| ID | Nombre | Estado | Icono | Color |
|----|--------|--------|-------|-------|
| `clientes` | Reporte de Clientes | ✅ Activo | fa-users | blue |
| `operacion_semanal` | Operación Semanal | ✅ Activo | fa-calendar-week | green |
| `remesas` | Reporte de Remesas | ✅ Activo | fa-money-bill-wave | emerald |
| `clientes_pdf` | Envío PDF Clientes | ✅ Activo | fa-file-pdf | red |
| `aduanas` | Reporte Aduanas | ✅ Activo | fa-building | purple |
| `patron_clientes` | Patrón de Clientes | ✅ Activo | fa-chart-line | orange |
| `financiero` | Reporte Financiero | 🚧 Próximamente | fa-chart-pie | indigo |
| `logistica` | Logística y Tiempo | 🚧 Próximamente | fa-truck | teal |

### **3. Controller Actualizado** (`app/Http/Controllers/Admin/TenantController.php`)

Se mejoró el método `updateCapabilities` para:
- ✅ Validar los reportes habilitados
- ✅ Guardar la configuración en el JSON
- ✅ Calcular automáticamente los reportes deshabilitados
- ✅ Logging detallado de reportes habilitados

**Nuevo JSON que se guarda:**

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

### **4. UI en el Panel de Super Admin** (`resources/views/admin/tenants/capabilities.blade.php`)

Se agregó una nueva sección completa:

```
┌─────────────────────────────────────────────────────┐
│  📊 Configuración de Reportes                       │
│  Controla qué reportes puede generar este tenant    │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ☑️ 👥 Reporte de Clientes                          │
│     Listado y estadísticas de clientes              │
│                                                     │
│  ☑️ 📅 Operación Semanal                            │
│     Resumen semanal de operaciones                  │
│                                                     │
│  ☑️ 💰 Reporte de Remesas                           │
│     Control de remesas                              │
│                                                     │
│  ☑️ 📄 Envío PDF Clientes                           │
│     Envío automático de PDF a clientes              │
│                                                     │
│  ☑️ 🏢 Reporte Aduanas                              │
│     Estadísticas por aduana                         │
│                                                     │
│  ☑️ 📈 Patrón de Clientes                           │
│     Análisis de patrones de clientes                │
│                                                     │
│  ⬜ 📊 Reporte Financiero [Próximamente]            │
│     Análisis financiero detallado                   │
│                                                     │
│  ⬜ 🚚 Logística y Tiempo [Próximamente]            │
│     Análisis de logística y tiempos de entrega      │
│                                                     │
│  [✓ Seleccionar Todos] [✗ Deseleccionar Todos]     │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Características de la UI:**
- ✅ Checkboxes con colores por tipo de reporte
- ✅ Reportes futuros marcados como "Próximamente" (deshabilitados)
- ✅ Estilo visual diferente para reportes habilitados vs deshabilitados
- ✅ Botones de selección rápida (todos/ninguno)
- ✅ Iconos y descripciones para cada reporte

### **5. Middleware de Protección** (`app/Http/Middleware/CheckReportAccess.php`)

Se creó un middleware que:
- ✅ Verifica si el tenant tiene acceso al reporte
- ✅ Registra intentos de acceso no autorizado en los logs
- ✅ Retorna error 403 con mensaje claro
- ✅ Funciona con peticiones JSON y HTML

### **6. Rutas Protegidas** (`routes/web.php`)

Se agregó el middleware `report.access:{reporte_id}` a las siguientes rutas:

| Ruta | Middleware | Reporte |
|------|-----------|---------|
| `/reportes/cliente` | `report.access:clientes` | Clientes |
| `/reportes/cliente/pdf` | `report.access:clientes` | Clientes PDF |
| `/reportes/operacion-semanal` | `report.access:operacion_semanal` | Operación Semanal |
| `/reportes/remesas` | `report.access:remesas` | Remesas |
| `/reportes/aduanas` | `report.access:aduanas` | Aduanas |
| `/reportes/patrones-cliente` | `report.access:patron_clientes` | Patrón Clientes |
| `/reportes/cliente-mail` | `report.access:clientes_pdf` | Envío PDF Clientes |
| Todas las rutas de cliente-mail/* | `report.access:clientes_pdf` | Envío PDF Clientes |

### **7. Panel de Depuración Mejorado**

Se agregó un badge que muestra cuántos reportes están habilitados:

```
[Bot Mode: manual] [Bot Enabled: Sí] [Bot Automático: No] [Reportes Habilitados: 6]
```

---

## 📦 Estructura Completa del JSON del Tenant

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

## 🚀 Cómo Usar

### **Como Super Admin:**

1. **Ir al panel de capacidades:**
   ```
   /nexacore-admin/tenants/{tenant_id}/capabilities
   ```

2. **Buscar la sección "📊 Configuración de Reportes"**

3. **Seleccionar los reportes que deseas habilitar:**
   - Haz clic en los checkboxes
   - Usa "Seleccionar Todos" o "Deseleccionar Todos"

4. **Guardar cambios**

5. **Ver el resultado:**
   - Abre el panel de depuración
   - Verifica que el JSON tenga la sección `reportes`
   - Revisa el badge "Reportes Habilitados: X"

### **Como Usuario del Tenant:**

1. **Ir a la sección de Reportes**

2. **Solo verás los reportes que tienes habilitados**

3. **Si intentas acceder directamente a un reporte no habilitado:**
   - Serás redirigido al índice de reportes
   - Verás mensaje: *"No tienes acceso a este reporte. Contacta a tu administrador para habilitar esta funcionalidad."*

---

## 🧪 Testing

### **Prueba 1: Configurar Reportes**

```bash
# 1. Ve al panel de admin
/nexacore-admin/tenants/1/capabilities

# 2. En la sección de Reportes, selecciona solo:
☑️ Clientes
☑️ Operación Semanal

# 3. Guarda cambios

# 4. Verifica en el JSON (panel de depuración):
"reportes": {
    "enabled": ["clientes", "operacion_semanal"],
    "disabled": ["remesas", "clientes_pdf", "aduanas", "patron_clientes", "financiero", "logistica"]
}
```

### **Prueba 2: Acceso como Usuario**

```bash
# 1. Inicia sesión como usuario del tenant

# 2. Intenta acceder a un reporte habilitado:
/reportes/cliente ✅ (Debe funcionar)

# 3. Intenta acceder a un reporte NO habilitado:
/reportes/remesas ❌ (Debe redirigir con error)
```

### **Prueba 3: Verificar Logs**

```bash
tail -f storage/logs/laravel.log | grep "Tenant configuración actualizada"

# Debes ver algo como:
{
    "tenant_id": 1,
    "tenant_nombre": "Mi Empresa",
    "bot_mode": "manual",
    "reportes_enabled": ["clientes", "operacion_semanal"],
    "configuracion_completa": "{...}"
}
```

---

## 🔒 Seguridad

### **Protección en Múltiples Niveles:**

1. **Middleware en rutas:**
   - Cada ruta de reporte está protegida por middleware
   - Verifica el acceso antes de ejecutar el controller

2. **Verificación en el modelo:**
   ```php
   $tenant->hasReportAccess('clientes') // true/false
   ```

3. **Logging de intentos no autorizados:**
   ```php
   \Log::warning('Intento de acceso a reporte sin permiso', [
       'tenant_id' => $tenant->id,
       'reporte' => 'remesas',
   ]);
   ```

4. **Mensajes de error claros:**
   - HTML: Redirige con mensaje flash
   - JSON: Retorna 403 con mensaje de error

---

## 📊 Configuración por Defecto por Plan

Puedes configurar defaults por plan en `TenantCapabilityService::applyPlanDefaults`:

### **Plan Trial:**
```php
'reportes' => [
    'enabled' => ['clientes', 'operacion_semanal'],
    'disabled' => ['remesas', 'clientes_pdf', 'aduanas', 'patron_clientes', 'financiero', 'logistica'],
]
```

### **Plan Básico:**
```php
'reportes' => [
    'enabled' => ['clientes', 'operacion_semanal', 'remesas', 'aduanas'],
    'disabled' => ['clientes_pdf', 'patron_clientes', 'financiero', 'logistica'],
]
```

### **Plan Profesional:**
```php
'reportes' => [
    'enabled' => ['clientes', 'operacion_semanal', 'remesas', 'clientes_pdf', 'aduanas', 'patron_clientes'],
    'disabled' => ['financiero', 'logistica'],
]
```

### **Plan Enterprise:**
```php
'reportes' => [
    'enabled' => ['clientes', 'operacion_semanal', 'remesas', 'clientes_pdf', 'aduanas', 'patron_clientes', 'financiero', 'logistica'],
    'disabled' => [],
]
```

---

## 💡 Agregar Nuevos Reportes

Para agregar un nuevo reporte en el futuro:

### **1. Agregar en Tenant.php:**

```php
public static function getAllAvailableReports(): array
{
    return [
        // ... reportes existentes ...
        'nuevo_reporte' => [
            'name' => 'Nuevo Reporte',
            'description' => 'Descripción del nuevo reporte',
            'icon' => 'fa-icon',
            'color' => 'blue',
            'status' => 'active', // o 'coming_soon'
        ],
    ];
}
```

### **2. Agregar ruta con middleware:**

```php
Route::get('/reportes/nuevo-reporte', [ReporteController::class, 'nuevoReporte'])
    ->middleware('report.access:nuevo_reporte')
    ->name('reportes.nuevo-reporte');
```

### **3. ¡Listo!** El sistema automáticamente:
- Mostrará el nuevo reporte en la UI de capacidades
- Permitirá habilitarlo/deshabilitarlo
- Protegerá la ruta con el middleware

---

## 🎨 Personalización Visual

Los reportes tienen colores e iconos personalizables:

```php
'clientes' => [
    'name' => 'Reporte de Clientes',
    'icon' => 'fa-users',        // Icono de FontAwesome
    'color' => 'blue',           // Color de Tailwind
    'status' => 'active',        // active o coming_soon
],
```

**Colores disponibles:**
- blue, green, emerald, red, purple, orange, indigo, teal, cyan, etc.

---

## 📝 Logs y Monitoreo

### **Ver configuración actual de un tenant:**

```php
$tenant = Tenant::find(1);
$enabledReports = $tenant->getEnabledReports();
// ['clientes', 'operacion_semanal', ...]

$reportConfig = $tenant->getReportConfig();
// ['enabled' => [...], 'disabled' => [...]]
```

### **Verificar acceso programáticamente:**

```php
if ($tenant->hasReportAccess('financiero')) {
    // Generar reporte financiero
} else {
    // Mostrar mensaje de "Sin acceso"
}
```

### **Logs generados:**

```
[2026-04-04 15:30:00] local.INFO: Tenant configuración actualizada
{
    "tenant_id": 1,
    "tenant_nombre": "Mi Empresa",
    "bot_mode": "manual",
    "reportes_enabled": ["clientes", "operacion_semanal"],
    "configuracion_completa": "{...}"
}

[2026-04-04 15:35:00] local.WARNING: Intento de acceso a reporte sin permiso
{
    "tenant_id": 1,
    "tenant_nombre": "Mi Empresa",
    "reporte": "remesas",
    "user_id": 5
}
```

---

## 🐛 Troubleshooting

### **Los reportes no se guardan en el JSON:**

1. Revisa los logs de Laravel
2. Verifica que el checkbox tenga el `name="reportes_enabled[]"`
3. Comprueba que el valor sea uno de los keys válidos

### **El middleware no funciona:**

1. Verifica que esté registrado en `app/Http/Kernel.php`
2. Limpia el caché: `php artisan route:clear`
3. Revisa que la ruta tenga el middleware correcto

### **Los reportes futuros aparecen habilitados:**

1. Verifica que `'status' => 'coming_soon'` en el array
2. El checkbox debe tener `disabled` attribute
3. El estilo debe tener `opacity-60`

---

## ✅ Checklist de Verificación

- [x] Métodos agregados en Tenant.php
- [x] Controller actualizado para guardar reportes
- [x] UI agregada en capabilities.blade.php
- [x] Middleware creado y registrado
- [x] Rutas protegidas con middleware
- [x] Panel de depuración muestra conteo de reportes
- [x] JavaScript para seleccionar todos
- [x] Logs de debugging configurados
- [x] Mensajes de error claros
- [x] Reportes futuros marcados como "Próximamente"

---

## 🎯 Resumen de Archivos Modificados

| Archivo | Cambio |
|---------|--------|
| `app/Models/Tenant.php` | +7 métodos para control de reportes |
| `app/Http/Controllers/Admin/TenantController.php` | Actualizado para guardar reportes en JSON |
| `app/Http/Middleware/CheckReportAccess.php` | **NUEVO** - Middleware de protección |
| `app/Http/Kernel.php` | Registrado middleware `report.access` |
| `routes/web.php` | Agregado middleware a rutas de reportes |
| `resources/views/admin/tenants/capabilities.blade.php` | UI para configurar reportes + JS |

---

## 🚀 Próximos Pasos (Opcionales)

- [ ] Agregar límite de generaciones por mes por reporte
- [ ] Tracking de uso (cuántas veces se genera cada reporte)
- [ ] Alertas cuando un tenant está cerca del límite
- [ ] Dashboard de analytics para Super Admin
- [ ] Exportar configuración de reportes a JSON
- [ ] Importar configuración de reportes desde JSON

---

**Implementación completada exitosamente** ✅

Ahora tienes control total sobre qué reportes puede acceder cada tenant. Todo se guarda en el JSON existente de configuración, sin necesidad de crear nuevas columnas en la base de datos.
