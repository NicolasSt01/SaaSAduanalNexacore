# 🚀 Estrategia de Monetización y Planes - SOIA-Bot

## 📋 Resumen Ejecutivo

Documento de planificación para convertir SOIA-Bot en una plataforma SaaS abierta al público con:
- Período de prueba gratuito (trial)
- Planes de membresía escalables
- Limitaciones de uso por plan
- Estrategia de conversión de trial a pago

---

## 🎯 1. Período de Prueba Gratuito (Trial)

### Objetivo
Permitir que los usuarios prueben la plataforma antes de comprar, pero con limitaciones que incentiven la conversión a planes de pago.

### Configuración del Trial

| Parámetro | Valor Sugerido | Justificación |
|-----------|---------------|---------------|
| **Duración** | 7 días | Suficiente para evaluar la plataforma, no tanto para acostumbrarse gratis |
| **Operaciones máximas** | 10-15 operaciones | Permite probar el flujo completo sin abuso |
| **Clientes permitidos** | 2 clientes | Suficiente para probar, limitado para necesitar más |
| **Usuarios** | 1 usuario (solo admin) | Sin equipo de trabajo en trial |
| **Notificaciones por correo** | ✅ Habilitadas | Para que prueben la funcionalidad completa |
| **Soporte** | Solo documentación | Sin soporte prioritario |

### Flujo de Registro Trial

```
Usuario se registra → Crea cuenta → Tenant creado automáticamente → 
Acceso por 7 días → Notificación al día 5 → Bloqueo al día 7 → 
Ofrecer plan de pago
```

### Después del Trial

Cuando expira el trial, el usuario tiene 3 opciones:

1. **Suscribirse a un plan** → Acceso completo inmediato
2. **Solicitar extensión** → +3 días (una sola vez, con justificación)
3. **No convertir** → Cuenta desactivada, datos preservados 30 días

---

## 💰 2. Planes de Membresía

### Estructura de 3 Planes

| Característica | 🟢 Básico | 🔵 Profesional | 🟣 Enterprise |
|---------------|-----------|---------------|---------------|
| **Precio mensual** | $499 MXN | $999 MXN | $1,999 MXN |
| **Operaciones/mes** | 50 | 200 | Ilimitadas |
| **Clientes** | 5 | 20 | Ilimitados |
| **Usuarios** | 2 | 5 | Ilimitados |
| **Plantillas de correo** | 1 | 3 | Personalizadas |
| **Soporte** | Email | Email + Chat | Prioritario |
| **Reportes** | Básicos | Avanzados | Custom + API |
| **Integraciones** | ❌ | ✅ PECEM | ✅ + Webhooks |
| **SLA** | 99% | 99.5% | 99.9% |
| **Backup** | 7 días | 30 días | 90 días |

### 💡 Ideas de Precios Alternativos

#### Opción A: Precio por Operación
- **Básico**: $299/mes (30 ops) + $15 MXN por operación extra
- **Profesional**: $799/mes (150 ops) + $10 MXN por operación extra
- **Enterprise**: $1,499/mes (ilimitado)

#### Opción B: Precio por Cliente
- **Starter**: $199/mes (hasta 3 clientes)
- **Growth**: $599/mes (hasta 10 clientes)
- **Scale**: $1,199/mes (hasta 25 clientes)
- **Unlimited**: $2,499/mes (ilimitado)

#### Opción C: Híbrido (Recomendado)
- **Básico**: $399/mes (50 ops + 5 clientes)
- **Profesional**: $899/mes (200 ops + 20 clientes)
- **Enterprise**: $1,799/mes (ilimitado)

---

## 🔒 3. Sistema de Limitaciones

### ¿Cómo limitar el uso?

#### A. Por Operaciones (Recomendado)
```php
// Ejemplo de validación
$operacionesEsteMes = Operacion::where('tenant_id', $tenant->id)
    ->whereMonth('created_at', now()->month)
    ->count();

if ($operacionesEsteMes >= $plan->limite_operaciones) {
    return response()->json([
        'error' => 'Has alcanzado tu límite de operaciones. Actualiza tu plan.',
        'plan_actual' => $plan->nombre,
        'limite' => $plan->limite_operaciones,
        'usado' => $operacionesEsteMes,
    ], 429);
}
```

#### B. Por Clientes
```php
$clientesActivos = Cliente::where('tenant_id', $tenant->id)
    ->where('activo', true)
    ->count();

if ($clientesActivos >= $plan->limite_clientes) {
    return redirect()->back()->with('error', 
        'Has alcanzado el límite de clientes. Actualiza tu plan.');
}
```

#### C. Por Usuarios
```php
$usuariosActivos = User::where('tenant_id', $tenant->id)
    ->where('activo', true)
    ->count();

if ($usuariosActivos >= $plan->limite_usuarios) {
    return redirect()->back()->with('error', 
        'Has alcanzado el límite de usuarios. Actualiza tu plan.');
}
```

### Tabla de Limitaciones por Plan

| Límite | Trial | Básico | Profesional | Enterprise |
|--------|-------|--------|-------------|------------|
| **Duración** | 7 días | ∞ | ∞ | ∞ |
| **Operaciones totales** | 15 | 50/mes | 200/mes | ∞ |
| **Clientes** | 2 | 5 | 20 | ∞ |
| **Usuarios** | 1 | 2 | 5 | ∞ |
| **Bodegas** | 1 | 2 | 5 | ∞ |
| **Patentes** | 1 | 2 | 5 | ∞ |
| **Envíos de correo/día** | 10 | 50 | 200 | ∞ |
| **Reportes/mes** | 0 | 5 | 20 | ∞ |
| **Almacenamiento** | 100 MB | 1 GB | 5 GB | 20 GB |

---

## 🎮 4. Implementación Técnica

### A. Modificar el modelo Tenant

```php
// Agregar campos a la tabla tenants
Schema::table('tenants', function (Blueprint $table) {
    $table->enum('tipo_cuenta', ['trial', 'pago'])->default('trial');
    $table->enum('plan', ['trial', 'basico', 'profesional', 'enterprise'])->default('trial');
    $table->timestamp('trial_inicio')->nullable();
    $table->timestamp('trial_fin')->nullable();
    $table->timestamp('suscripcion_inicio')->nullable();
    $table->timestamp('suscripcion_fin')->nullable();
    $table->boolean('activo')->default(true);
    $table->json('limites_personalizados')->nullable();
});
```

### B. Middleware de Validación de Plan

```php
// app/Http/Middleware/ValidateTenantPlan.php
class ValidateTenantPlan
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = auth()->user()->tenant;
        
        // Verificar si el trial expiró
        if ($tenant->tipo_cuenta === 'trial' && $tenant->trial_fin < now()) {
            return redirect()->route('plan.expirado')
                ->with('error', 'Tu período de prueba ha expirado. Suscríbete para continuar.');
        }
        
        // Verificar límite de operaciones
        if ($tenant->plan !== 'enterprise') {
            $limite = $this->getPlanLimit($tenant->plan);
            $usado = $this->getOperacionesDelMes($tenant->id);
            
            if ($usado >= $limite) {
                return redirect()->route('plan.upgrade')
                    ->with('error', 'Alcanzaste tu límite de operaciones. Actualiza tu plan.');
            }
        }
        
        return $next($request);
    }
}
```

### C. Job de Verificación de Trial

```php
// app/Jobs/CheckExpiredTrials.php
class CheckExpiredTrials implements ShouldQueue
{
    public function handle()
    {
        $trialsExpirados = Tenant::where('tipo_cuenta', 'trial')
            ->where('trial_fin', '<', now())
            ->where('activo', true)
            ->get();
        
        foreach ($trialsExpirados as $tenant) {
            // Enviar correo de notificación
            Mail::to($tenant->correo_admin)->send(
                new TrialExpiradoMail($tenant)
            );
            
            // Desactivar cuenta (pero preservar datos)
            $tenant->update(['activo' => false]);
        }
    }
}
```

---

## 💳 5. Sistema de Pagos

### Opciones de Cobro

#### A. Stripe (Recomendado)
**Ventajas:**
- ✅ Fácil integración con Laravel Cashier
- ✅ Soporte para suscripciones recurrentes
- ✅ Manejo automático de fallos de pago
- ✅ Dashboard de métricas
- ✅ Soporte para México

**Integración:**
```bash
composer require laravel/cashier
php artisan cashier:install
```

**Configuración:**
```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

#### B. MercadoPago
**Ventajas:**
- ✅ Muy popular en México/Latam
- ✅ Acepta OXXO, SPEI, tarjetas
- ✅ Menores comisiones que Stripe en algunos casos

**Desventajas:**
- ❌ Integración más compleja
- ❌ Sin paquete oficial de Laravel

#### C. PayPal
**Ventajas:**
- ✅ Reconocido globalmente
- ✅ Fácil para usuarios

**Desventajas:**
- ❌ Comisiones más altas
- ❌ Menos flexible para suscripciones

### Recomendación Final
**Usa Stripe** como procesador principal + **MercadoPago** como alternativa para pagos con OXXO/SPEI.

---

## 📊 6. Flujo de Conversión

### Del Trial al Plan de Pago

```
Día 1: Usuario se registra
  ↓
Día 3: Email "¿Cómo te va con SOIA-Bot?"
  ↓
Día 5: Email "Tu trial expira en 2 días - Mira nuestros planes"
  ↓
Día 7: Trial expira → Redirigir a página de planes
  ↓
Día 7-30: Datos preservados, cuenta desactivada
  ↓
Día 30+: Datos eliminados (GDPR)
```

### Emails Automatizados

| Email | Cuándo | Contenido |
|-------|--------|-----------|
| **Bienvenida** | Día 1 | Tutorial rápido + video de 2 min |
| **Check-in** | Día 3 | "¿Necesitas ayuda?" + tips |
| **Urgencia** | Día 5 | "2 días restantes" + beneficios |
| **Expiración** | Día 7 | "Tu trial expiró" + planes |
| **Recuperación** | Día 14 | "Te extrañamos" + 10% descuento |
| **Última oportunidad** | Día 28 | "Datos se eliminarán en 2 días" |

---

## 🎁 7. Estrategias de Conversión

### A. Descuento por Pago Anual
- **Básico**: $399/mes → $3,990/año (ahorra $798 = 1.6 meses gratis)
- **Profesional**: $899/mes → $8,990/año (ahorra $1,798 = 2 meses gratis)
- **Enterprise**: $1,799/mes → $17,990/año (ahorra $3,598 = 2 meses gratis)

### B. Referidos
- Usuario actual refiere a otro → 1 mes gratis para ambos
- Sin límite de referidos

### C. Descuento por Volumen
- Si un tenant tiene más de 10 usuarios → 10% descuento
- Si tiene más de 500 operaciones/mes → plan Enterprise con 15% descuento

### D. Promociones de Lanzamiento
- **Primeros 50 clientes**: 50% descuento de por vida
- **Primer trimestre**: 30% descuento en plan anual

---

## 📈 8. Métricas Clave a Monitorear

| Métrica | Fórmula | Meta |
|---------|---------|------|
| **Trial → Pago** | (Usuarios que pagan / Trials creados) × 100 | > 15% |
| **Churn Rate** | (Cancelaciones / Total suscriptores) × 100 | < 5% mensual |
| **MRR** | Suma de ingresos recurrentes mensuales | Creciente |
| **ARPU** | MRR / Total suscriptores | Creciente |
| **LTV** | ARPU × Vida promedio del cliente | > $10,000 MXN |
| **CAC** | Gastos marketing / Nuevos clientes | < $500 MXN |

---

## 🛡️ 9. Protección contra Abusos

### Limitar Creación de Trials
```php
// Evitar que un usuario cree múltiples trials
class RegisterController
{
    public function register(Request $request)
    {
        // Verificar por email
        $existingTrial = Tenant::where('correo_admin', $request->email)
            ->where('tipo_cuenta', 'trial')
            ->first();
        
        if ($existingTrial) {
            return back()->withErrors([
                'email' => 'Ya tienes un trial activo con este correo.'
            ]);
        }
        
        // Verificar por IP (rate limiting)
        $recentTrials = Tenant::where('created_at', '>', now()->subHours(24))
            ->count();
        
        if ($recentTrials > 5) {
            return back()->withErrors([
                'email' => 'Demasiados registros. Intenta más tarde.'
            ]);
        }
    }
}
```

### Otras Protecciones
- ✅ Rate limiting por IP
- ✅ Verificación de email antes de activar trial
- ✅ CAPTCHA en registro
- ✅ Limitar trials por dominio de correo (evitar emails desechables)
- ✅ Monitoreo de patrones sospechosos

---

## 📝 10. Plan de Implementación

### Fase 1: Trial (Semana 1-2)
- [ ] Modificar modelo Tenant con campos de trial
- [ ] Crear registro público (sin necesidad de admin)
- [ ] Middleware de validación de trial
- [ ] Job de expiración de trials
- [ ] Emails automatizados de trial

### Fase 2: Planes (Semana 3-4)
- [ ] Crear tabla `plans` con límites
- [ ] Integrar Stripe con Laravel Cashier
- [ ] Página de precios
- [ ] Flujo de suscripción
- [ ] Webhooks de Stripe

### Fase 3: Limitaciones (Semana 5-6)
- [ ] Middleware de validación de límites
- [ ] Contadores de uso (operaciones, clientes, etc.)
- [ ] UI para mostrar uso actual vs límite
- [ ] Notificaciones de límite alcanzado

### Fase 4: Métricas (Semana 7-8)
- [ ] Dashboard de métricas de negocio
- [ ] Reportes de conversión
- [ ] Análisis de churn
- [ ] Emails de recuperación

---

## 💡 11. Ideas Adicionales

### A. Plan "Pay as You Go"
Para usuarios que no quieren suscripción mensual:
- $25 MXN por operación
- Sin compromiso mensual
- Pago con tarjeta

### B. Add-ons (Extras de Pago)
| Add-on | Precio | Descripción |
|--------|--------|-------------|
| **Usuarios extra** | $99/mes c/u | Más allá del límite del plan |
| **Operaciones extra** | $199/50 ops | Paquete de 50 operaciones |
| **Soporte prioritario** | $299/mes | Respuesta en < 2 horas |
| **API Access** | $499/mes | Acceso completo a API REST |
| **White-label** | $999/mes | Sin branding de SOIA-Bot |

### C. Programa de Lealtad
- 6 meses consecutivo → 5% descuento permanente
- 12 meses → 10% descuento permanente
- 24 meses → 15% descuento permanente

### D. Garantía de Devolución
- 30 días de garantía en cualquier plan
- Si no está satisfecho → reembolso completo

---

## 🎯 12. Recomendaciones Finales

### Lo que DEBES hacer:
1. ✅ **Empieza con trial de 7 días** - Es el estándar de la industria
2. ✅ **Usa Stripe** - Integración más fácil con Laravel
3. ✅ **Limita por operaciones** - Es la métrica más fácil de medir
4. ✅ **Automatiza emails** - La mayoría de conversiones vienen de emails
5. ✅ **Muestra el uso actual** - Que el usuario vea cuánto le queda

### Lo que NO debes hacer:
1. ❌ **No hagas el trial muy largo** - 7 días es suficiente
2. ❌ **No limites demasiado el trial** - Debe ser funcional para evaluar
3. ❌ **No cobres sin aviso** - Siempre notifica antes de cobrar
4. ❌ **No elimines datos inmediatamente** - Preserva 30 días mínimo
5. ❌ **No ignores métricas** - Mide todo desde el día 1

---

## 📞 13. Ejemplo de Página de Precios

```
┌─────────────────────────────────────────────────────────────┐
│                    ELIGE TU PLAN IDEAL                       │
│                                                             │
│   🟢 BÁSICO          🔵 PROFESIONAL       🟣 ENTERPRISE    │
│   $399/mes           $899/mes             $1,799/mes        │
│                                                             │
│   ✅ 50 ops/mes      ✅ 200 ops/mes       ✅ Ilimitado      │
│   ✅ 5 clientes      ✅ 20 clientes       ✅ Ilimitados     │
│   ✅ 2 usuarios      ✅ 5 usuarios        ✅ Ilimitados     │
│   ✅ Email soporte   ✅ Chat + Email      ✅ Prioritario    │
│   ✅ Reportes básicos✅ Reportes avanz.   ✅ API + Webhooks │
│                                                             │
│   [Empezar Trial]    [Empezar Trial]     [Contactar Ventas]│
└─────────────────────────────────────────────────────────────┘
```

---

## 📚 Recursos Útiles

- [Laravel Cashier (Stripe)](https://laravel.com/docs/11.x/billing)
- [Stripe Pricing Best Practices](https://stripe.com/docs/billing/pricing-guide)
- [SaaS Metrics That Matter](https://www.baremetrics.com/blog/saas-metrics)
- [Trial Length Optimization](https://www.chargebee.com/blog/saas-free-trial-best-practices/)

---

**Documento creado:** 2026-04-02  
**Versión:** 1.0  
**Estado:** Borrador para revisión
