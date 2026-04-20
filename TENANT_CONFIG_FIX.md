# 🔧 Tenant Configuration Fix - SOIA-Bot Mode

## ✅ Problema Solucionada

El método `updateCapabilities` no estaba actualizando correctamente el JSON de configuración del tenant. Ahora se ha mejorado para:

1. **Preservar la estructura del JSON** - Se aseguran que todas las claves existan antes de actualizar
2. **Manejar valores nulos correctamente** - Los campos vacíos se guardan como `null` (sin límite)
3. **Preservar contadores existentes** - Los contadores de consultas del bot no se pierden
4. **Logging detallado** - Se registra toda la configuración para debugging
5. **Panel de depuración en la UI** - Puedes ver el JSON completo en tiempo real

---

## 🎯 Cambios Realizados

### 1. **Controller Mejorado** (`app/Http/Controllers/Admin/TenantController.php`)

#### Antes:
```php
// Reemplazaba todo el array 'bot' desde cero
$config['bot'] = [
    'mode' => $request->bot_mode,
    'consultas_limite_mes' => $request->bot_consultas_limite_mes,
    // ... podía perder valores existentes
];
```

#### Ahora:
```php
// Asegura que existan las estructuras
if (!isset($config['bot'])) {
    $config['bot'] = [];
}

// Actualiza solo el mode, preserva lo demás
$config['bot']['mode'] = $request->bot_mode;

// Solo actualiza si se proporcionó valor
if ($request->filled('bot_consultas_limite_mes')) {
    $config['bot']['consultas_limite_mes'] = (int) $request->bot_consultas_limite_mes;
}

// Preserva contadores existentes
$config['bot']['consultas_mes'] = $config['bot']['consultas_mes'] ?? 0;
$config['bot']['consultas_mes_periodo'] = $config['bot']['consultas_mes_periodo'] ?? now()->format('Y-m');
```

### 2. **Panel de Depuración en la Vista** (`resources/views/admin/tenants/capabilities.blade.php`)

Se agregó un panel que muestra:
- El JSON completo de configuración
- Badges con los valores actuales del bot
- Botón para mostrar/ocultar

```blade
<!-- Panel de depuración -->
<div class="mb-6 bg-gray-50 border border-gray-200 rounded-xl p-4">
    <h3>Configuración Actual (JSON)</h3>
    <pre>{{ json_encode($config, JSON_PRETTY_PRINT) }}</pre>
    
    <!-- Badges informativos -->
    <span>Bot Mode: {{ $botConfig['mode'] }}</span>
    <span>Bot Enabled: Sí/No</span>
    <span>Bot Automático: Sí/No</span>
</div>
```

### 3. **Logging para Debugging**

Cada vez que se actualiza la configuración, se registra en los logs:

```php
\Log::info('Tenant configuración actualizada', [
    'tenant_id' => $tenant->id,
    'tenant_nombre' => $tenant->nombre_empresa,
    'bot_mode' => $config['bot']['mode'],
    'configuracion_completa' => json_encode($config, JSON_PRETTY_PRINT),
]);
```

---

## 🧪 Cómo Probar

### Paso 1: Ir al Panel de Capacidades

```
https://tudominio.com/nexacore-admin/tenants/{tenant_id}/capabilities
```

### Paso 2: Ver el JSON Actual

1. Haz clic en **"Mostrar/Ocultar"** en el panel de depuración
2. Verás el JSON completo de la configuración
3. Revisa que la estructura sea correcta:

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
            ...
        },
        "funcionalidades": {
            "reportes_mes": null,
            ...
        }
    },
    "features_enabled": ["basic_dashboard", "email_notifications"]
}
```

### Paso 3: Cambiar el Modo del Bot

1. En el formulario, cambia **"Modo del Bot"** a `automatico`
2. Haz clic en **"Guardar Cambios"**
3. Verifica el mensaje de éxito: `"Capacidades y límites actualizados correctamente. Bot mode: automatico"`

### Paso 4: Verificar que se Guardó

1. La página se recargará
2. Abre el panel de depuración nuevamente
3. Verifica que `"mode": "automatico"` en el JSON
4. Los badges deben mostrar:
   - **Bot Mode**: automatico
   - **Bot Enabled**: Sí
   - **Bot Automático**: Sí

### Paso 5: Verificar en el Dashboard del Documentador

1. Inicia sesión como usuario de ese tenant
2. Ve a `/documentador/dashboard`
3. Si el modo es `manual`, debes ver el botón "🤖 Consultar Modulación"
4. Si el modo es `automatico`, el botón **NO** debe aparecer

---

## 🔍 Logs de Depuración

Si algo no funciona, revisa los logs:

```bash
tail -f /Users/nicolas/Downloads/_portalcross/storage/logs/laravel.log | grep "Tenant configuración actualizada"
```

Verás algo como:

```
[2026-04-04 12:00:00] local.INFO: Tenant configuración actualizada 
{
    "tenant_id": 1,
    "tenant_nombre": "Mi Empresa",
    "bot_mode": "automatico",
    "configuracion_completa": "{\n    \"bot\": {\n        \"mode\": \"automatico\",\n        ..."
}
```

---

## 📊 Estructura Completa del JSON

La configuración del tenant debe tener esta estructura:

```json
{
    "bot": {
        "mode": "manual|automatico|deshabilitado",
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
            "documentos_mes": null
        },
        "funcionalidades": {
            "reportes_mes": null,
            "correos_dia": 10,
            "whatsapp_mes": null
        }
    },
    "features_enabled": [
        "basic_dashboard",
        "email_notifications"
    ]
}
```

---

## 🎨 Badges Informativos

En el panel de depuración verás 3 badges:

| Badge | Color | Significado |
|-------|-------|-------------|
| **Bot Mode** | Púrpura | Modo actual: manual, automatico, deshabilitado |
| **Bot Enabled** | Azul | Si el bot está habilitado (mode != deshabilitado) |
| **Bot Automático** | Naranja | Si el bot está en modo automático |

---

## 🚀 Flujo Completo de Prueba

```
1. Ve a /nexacore-admin/tenants/{id}/capabilities
   ↓
2. Abre el panel de depuración
   ↓
3. Verifica el JSON actual
   ↓
4. Cambia "Modo del Bot" a "automatico"
   ↓
5. Guarda cambios
   ↓
6. Verifica el mensaje de éxito
   ↓
7. Revisa el JSON actualizado
   ↓
8. Ve a /documentador/dashboard (como usuario del tenant)
   ↓
9. El botón "🤖 Consultar Modulación" NO debe aparecer
   ↓
10. ¡Listo! La configuración se actualizó correctamente ✅
```

---

## 💡 Notas Importantes

### Valores Null vs Vacíos

- **Campo vacío** → Se guarda como `null` (sin límite)
- **Campo con valor** → Se guarda como entero

Ejemplo:
```
limite_clientes = (vacío)  → "clientes": null
limite_clientes = 10       → "clientes": 10
```

### Preservación de Datos

El código ahora **preserva** los siguientes valores:
- `consultas_mes` - Contador actual de consultas
- `consultas_mes_periodo` - Periodo actual (YYYY-MM)
- Estructuras completas si no existen

### Mensaje de Éxito Personalizado

Ahora el mensaje de éxito incluye el modo configurado:

```
"Capacidades y límites actualizados correctamente. Bot mode: automatico"
```

---

## 🐛 Troubleshooting

### El JSON no se actualiza

1. Revisa los logs de Laravel
2. Verifica que el tenant tenga un valor en la columna `configuracion`
3. Comprueba que el usuario tenga permisos de admin

### El botón sigue apareciendo en modo automático

1. Limpia el caché: `php artisan view:clear && php artisan config:clear`
2. Verifica que el tenant tenga `bot.mode = 'automatico'` en el JSON
3. Recarga la página del dashboard con Ctrl+F5

### Error de validación

Verifica que los campos numéricos tengan valores válidos:
- Deben ser enteros
- Mínimo 1 si se proporciona valor
- Pueden estar vacíos (se guardan como null)

---

## ✅ Checklist de Verificación

- [ ] El JSON se actualiza correctamente
- [ ] El modo del bot cambia (manual/automatico/deshabilitado)
- [ ] Los contadores del bot se preservan
- [ ] Los límites se guardan correctamente
- [ ] El panel de depuración muestra el JSON
- [ ] Los badges muestran los valores correctos
- [ ] El mensaje de éxito incluye el bot mode
- [ ] Los logs registran la configuración
- [ ] El dashboard del documentador refleja el cambio

---

**Implementación completada exitosamente** ✅

Ahora puedes configurar el modo del SOIA-Bot para cada tenant desde el panel de administración y verificar que se guarda correctamente en el JSON de configuración.
