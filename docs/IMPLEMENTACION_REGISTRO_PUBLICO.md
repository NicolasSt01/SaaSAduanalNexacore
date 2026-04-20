# 🚀 Implementación: Registro Público de Tenants (Trial)

## 📋 Resumen

Permitir que usuarios nuevos se registren públicamente para una **prueba gratuita de 7 días** con configuración limitada.

---

## 🎯 Configuración del Trial

| Parámetro | Valor |
|-----------|-------|
| **Duración** | 7 días desde primer login |
| **Plan** | Trial (configuración especial) |
| **Usuarios máximos** | 1 (solo admin) |
| **Modulaciones/mes** | 20 |
| **Bot mode** | Manual (no automático) |
| **Clientes** | 5 |
| **Importadores** | 2 |
| **Bodegas** | 1 |
| **Patentes** | 1 |

---

## 📝 Plan de Implementación

### PASO 1: Crear vistas de registro público
- [ ] `resources/views/auth/register.blade.php` - Formulario de registro
- [ ] `resources/views/auth/verify-email.blade.php` - Página de verificación
- [ ] `resources/views/auth/first-login-change-password.blade.php` - Cambio de contraseña obligatorio

### PASO 2: Crear controlador de registro público
- [ ] `app/Http/Controllers/Auth/PublicRegisterController.php`
  - `showRegister()` - Mostrar formulario
  - `register()` - Procesar registro
  - `verifyEmail($token)` - Verificar correo
  - `showFirstLogin()` - Mostrar formulario de cambio de contraseña
  - `changeFirstPassword()` - Procesar cambio de contraseña

### PASO 3: Crear Mailable de verificación
- [ ] `app/Mail/RegistroExitosoMail.php` - Correo con contraseña y link de verificación

### PASO 4: Crear tabla de tokens de verificación
- [ ] Migración: `create_email_verification_tokens_table`
  - `id`, `user_id`, `email`, `token`, `created_at`, `expires_at`

### PASO 5: Modificar modelo Tenant
- [ ] Agregar método `applyTrialConfig()` para aplicar configuración de trial
- [ ] Agregar campo `trial_started_at` (nullable) para rastrear inicio del trial

### PASO 6: Modificar modelo User
- [ ] Agregar campo `must_change_password` (boolean, default true para trials)
- [ ] Middleware `MustChangePassword` para forzar cambio en primer login

### PASO 7: Crear middleware de verificación
- [ ] `app/Http/Middleware/MustChangePassword.php` - Redirige a cambio de contraseña si es requerido
- [ ] `app/Http/Middleware/CheckTrialExpired.php` - Verifica si el trial expiró

### PASO 8: Agregar rutas públicas
- [ ] `GET /registro` - Formulario de registro
- [ ] `POST /registro` - Procesar registro
- [ ] `GET /verificar-correo/{token}` - Verificar correo
- [ ] `GET /cambiar-contraseña` - Formulario de cambio (requiere auth)
- [ ] `POST /cambiar-contraseña` - Procesar cambio

### PASO 9: Configurar defaults del trial
- [ ] Actualizar `TenantCapabilityService::getDefaultConfigForPlan('trial')`
- [ ] Asegurar que se aplique al crear tenant desde registro público

### PASO 10: Job de expiración de trials
- [ ] `app/Jobs/CheckExpiredTrials.php` - Verifica trials expirados
- [ ] Agregar al scheduler para ejecución diaria

---

## 🔄 Flujo de Registro

```
1. Usuario entra a /registro
   ↓
2. Llena formulario:
   - Nombre completo
   - Nombre de empresa
   - Correo electrónico
   - Teléfono
   ↓
3. Sistema crea:
   - Tenant con configuración trial
   - Usuario admin con contraseña aleatoria
   - Token de verificación de correo
   ↓
4. Sistema envía correo con:
   - Link de verificación
   - Contraseña temporal
   - Instrucciones de primer acceso
   ↓
5. Usuario hace click en link de verificación
   ↓
6. Usuario inicia sesión con contraseña temporal
   ↓
7. Sistema detecta must_change_password = true
   ↓
8. Sistema redirige a /cambiar-contraseña
   ↓
9. Usuario coloca nueva contraseña
   ↓
10. Sistema marca must_change_password = false
    ↓
11. Sistema registra trial_started_at = now()
    ↓
12. Usuario accede al dashboard con 7 días de trial
```

---

## 📧 Contenido del Correo de Verificación

**Asunto:** ¡Bienvenido a NexaCore Aduanal! Tu cuenta ha sido creada

**Contenido:**
- Saludo personalizado con nombre
- Nombre de la empresa registrada
- Contraseña temporal (destacada)
- Botón de verificación de correo
- Instrucciones para primer login
- Resumen de lo que incluye el trial (7 días, 20 modulaciones, etc.)
- Link a soporte

---

## 🔒 Seguridad

1. **Contraseña temporal:**
   - Generada con `Str::random(12)`
   - Enviada solo por correo
   - Expira si no se usa en 48 horas

2. **Token de verificación:**
   - Generado con `Str::uuid()`
   - Expira en 24 horas
   - Un solo uso

3. **Forzar cambio de contraseña:**
   - Middleware verifica `must_change_password`
   - No permite acceder a otras rutas hasta cambiar
   - Nueva contraseña debe cumplir requisitos de seguridad

---

## 📊 Estructura de Datos del Trial

```json
{
    "tenant": {
        "plan": "basico",
        "estado": "activo",
        "trial_started_at": "2026-04-03",
        "trial_ends_at": "2026-04-10",
        "max_usuarios": 1,
        "max_operaciones_mes": 20,
        "configuracion": {
            "bot": {
                "mode": "manual",
                "consultas_limite_mes": 20
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
            "features_enabled": ["basic_dashboard", "email_notifications"]
        }
    },
    "user": {
        "role": "admin",
        "must_change_password": true,
        "email_verified_at": null → se llena al verificar
    }
}
```

---

## 🧪 Pruebas a Realizar

1. **Registro exitoso:**
   - Llenar formulario → Recibir correo → Verificar → Login → Cambiar contraseña → Acceder

2. **Verificación expirada:**
   - Intentar verificar token después de 24 horas → Error

3. **Contraseña expirada:**
   - Intentar login después de 48 horas sin verificar → Error

4. **Trial expirado:**
   - Esperar 7 días → Intentar acceder → Redirigir a página de upgrade

5. **Forzar cambio de contraseña:**
   - Login con contraseña temporal → Redirigido a cambio → Cambiar → Acceder

---

## 🚀 Comandos para Ejecutar

```bash
# 1. Crear migración para tokens de verificación
php artisan make:migration create_email_verification_tokens_table

# 2. Crear campos adicionales en tenants y users
php artisan make:migration add_trial_fields_to_tenants_and_users

# 3. Crear Mailable
php artisan make:mail RegistroExitosoMail

# 4. Crear middleware
php artisan make:middleware MustChangePassword
php artisan make:middleware CheckTrialExpired

# 5. Ejecutar migraciones
php artisan migrate

# 6. Limpiar caché
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

**Documento creado:** 2026-04-03  
**Versión:** 1.0  
**Estado:** Listo para implementación
