# ⚠️ IMPORTANTE: Middleware en Laravel 11/12

## 🚨 Cambio Crítico de Arquitectura

En **Laravel 11 y 12**, la configuración de middlewares **YA NO** se hace en `app/Http/Kernel.php`.

Ahora se configura en **`bootstrap/app.php`** usando la nueva API fluida.

---

## ❌ Lo que NO funciona (Laravel 10 o anterior):

```php
// app/Http/Kernel.php - YA NO SE USA EN LARAVEL 11/12
protected $routeMiddleware = [
    'report.access' => \App\Http\Middleware\CheckReportAccess::class,
];
```

---

## ✅ Lo que SÍ funciona (Laravel 11/12):

```php
// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middlewares globales para web
        $middleware->web(append: [
            \App\Http\Middleware\SingleSessionGuard::class,
        ]);
        
        // Alias de middlewares para rutas
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin_n2' => \App\Http\Middleware\CheckAdminN2::class,
            'cliente' => \App\Http\Middleware\CheckCliente::class,
            'documentador' => \App\Http\Middleware\CheckDocumentador::class,
            'register.access' => \App\Http\Middleware\RegisterAccess::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'single.session' => \App\Http\Middleware\SingleSessionGuard::class,
            'must.change.password' => \App\Http\Middleware\MustChangePassword::class,
            'check.trial.expired' => \App\Http\Middleware\CheckTrialExpired::class,
            'report.access' => \App\Http\Middleware\CheckReportAccess::class, // ✅ NUEVO
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

---

## 🔍 Error que indica este problema:

```
Target class [report.access] does not exist.
```

Este error aparece cuando:
1. Registras el middleware en `app/Http/Kernel.php` (forma antigua)
2. Pero Laravel 11/12 busca en `bootstrap/app.php` (forma nueva)

---

## 📋 Middlewares Registrados

### **Middleware Globales (web group):**
- `single.session` - Control de una sesión por usuario

### **Alias de Rutas:**
| Alias | Clase | Propósito |
|-------|-------|-----------|
| `admin` | `AdminMiddleware` | Acceso de administrador |
| `admin_n2` | `CheckAdminN2` | Acceso de admin N2 |
| `cliente` | `CheckCliente` | Acceso de cliente |
| `documentador` | `CheckDocumentador` | Acceso de documentador |
| `register.access` | `RegisterAccess` | Acceso a registro |
| `role` | `CheckRole` | Verificación de rol |
| `super_admin` | `SuperAdminMiddleware` | Acceso de super admin |
| `single.session` | `SingleSessionGuard` | Control de sesión única |
| `must.change.password` | `MustChangePassword` | Forzar cambio de contraseña |
| `check.trial.expired` | `CheckTrialExpired` | Verificar trial expirado |
| **`report.access`** | **`CheckReportAccess`** | **Control de acceso a reportes** ✅ |

---

## 🎯 Cómo usar el middleware `report.access`

### **En rutas:**

```php
Route::get('/reportes/cliente', [ReporteController::class, 'reporteCliente'])
    ->middleware('report.access:clientes')
    ->name('reportes.cliente');
```

### **En controllers:**

```php
public function __construct()
{
    $this->middleware('report.access:clientes');
}
```

---

## 📝 Notas Importantes

1. **`app/Http/Kernel.php` aún existe** pero ya no se usa para registrar middlewares
2. **`bootstrap/app.php` es el nuevo punto de entrada** para configuración de la app
3. **La sintaxis es fluida** usando `->withMiddleware(function (Middleware $middleware))`
4. **Los middlewares globales** se agregan con `$middleware->web(append: [...])`
5. **Los alias** se registran con `$middleware->alias([...])`

---

## 🔧 Si agregas un nuevo middleware:

### **Paso 1: Crear el middleware**
```bash
php artisan make:middleware MiNuevoMiddleware
```

### **Paso 2: Registrar en bootstrap/app.php**
```php
$middleware->alias([
    // ... existentes ...
    'mi.nuevo' => \App\Http\Middleware\MiNuevoMiddleware::class,
]);
```

### **Paso 3: Usar en rutas**
```php
Route::get('/ruta', [Controller::class, 'method'])
    ->middleware('mi.nuevo:parametro');
```

---

## 📚 Referencia

- **Laravel 11 Docs**: https://laravel.com/docs/11.x/middleware
- **Laravel 12 Docs**: https://laravel.com/docs/12.x/middleware
- **Bootstrap App**: https://laravel.com/docs/11.x/structure#the-bootstrap-directory

---

## ✅ Checklist para nuevos middlewares

- [ ] Crear middleware con `php artisan make:middleware`
- [ ] Agregar alias en `bootstrap/app.php`
- [ ] Usar middleware en rutas o controllers
- [ ] Probar que funcione correctamente
- [ ] Limpiar caché: `php artisan optimize:clear`

---

**Nota:** Este documento es para referencia futura. El middleware `report.access` ya está correctamente configurado.
