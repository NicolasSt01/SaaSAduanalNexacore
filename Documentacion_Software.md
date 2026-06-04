# Documentación Completa del Software - NexaCore Aduanal SaaS

NexaCore Aduanal es una plataforma SaaS diseñada para agencias aduanales, proporcionando un ecosistema completo para el control de tráfico, documentación, trazabilidad operativa (integración con SAT/SOIA), expedientes digitales y reporteo avanzado. 

Este documento describe el flujo de trabajo principal de la aplicación, su arquitectura, módulos, métodos y vistas clave.

---

## 1. Flujo de Trabajo (Workflow)

El flujo operativo sigue una lógica secuencial basada en la vida real de un despacho aduanero, adaptada a una arquitectura multi-tenant (donde cada Tenant es una agencia aduanal).

### Fase 1: Onboarding y Configuración (Super Admin & Admin Tenant)
1. **Creación del Tenant:** El *Super Admin* registra una nueva agencia y define sus **Límites y Capacidades** (p. ej. qué reportes puede ver, cuántos usuarios/operaciones puede crear) usando el panel en `/nexacore-admin/tenants/{id}/capabilities`. La configuración se guarda en un JSON flexible dentro de la tabla `tenants`.
2. **Registro de Catálogos:** El administrador del Tenant captura sus datos maestros: **Clientes**, **Importadores**, **Patentes**, **Aduanas** y **Bodegas**.
3. **Directorio:** Se configuran contactos por cliente en el **Directorio** para enviar notificaciones automáticas (Emails y WhatsApp).

### Fase 2: Registro y Gestión Operativa
1. **Gestión Centralizada:** Toda la operativa diaria se maneja de manera centralizada desde el panel del documentador (`/documentador/dashboard`). En este proyecto no existe un flujo separado de "Tráfico".
2. **Creación y Actualización:** Desde este único panel, los documentadores inician la operación (generando la referencia e ingresando datos base como cliente, aduana, factura, bodega, thermo y código alpha) y, en el mismo flujo, asignan los números de Pedimento y DODA.
3. **Expediente Digital:** Se suben los documentos de la operación (Pedimentos, Facturas, BL, CFDI) vinculándolos al trámite activo. Estos archivos se almacenan en **Cloudflare R2**, aislados lógicamente por Tenant y Operación. También se leen e importan datos a través de XML (`FacturaXMLController`).
*Nota: En este sistema el trabajo es directo y asíncrono sobre los registros; no se utiliza la lógica de "tomar y soltar trámite" (bloqueo de concurrencia).*

### Fase 3: Trazabilidad Automatizada (Bot DODA/PECEM)
1. **Scraping del SAT:** Mediante un Job programado (CRON) o activación manual, el `DodaBotController` consulta automáticamente el portal **PECEM/SOIA** usando el número de integración de la operación.
2. **Captura Integral:** El bot extrae el estatus visual (Modulación), detalles financieros (línea de captura, fecha de pago), y contenedores.
3. **Notificación Proactiva:** Si hay un cambio de estatus (ej. Desaduanamiento Libre), el bot dispara notificaciones automáticas a los clientes según las reglas de negocio del directorio. Toda extracción se registra en el campo `bot_logs_json`.

### Fase 4: Reporteo, Finanzas y Cierre
1. **Reportes:** Se generan reportes (Diarios, Semanales, de Remesas, etc.) mediante el `ReporteController`. **Nota clave:** La fecha principal para las métricas operativas es la `fecha_cruce_estimada`.
2. **Finanzas:** Se procesan las facturas y conceptos adicionales del despacho (`FinanzasController` y `ConceptoAdicionalController`), permitiendo control de estado financiero por operación y exportaciones a PDF.

---

## 2. Módulos, Controladores y Vistas Clave

El sistema se estructura en una arquitectura MVC (Model-View-Controller) tradicional de Laravel. A continuación, se desglosan los controladores y vistas más importantes por módulo.

### 2.1 Módulo: Super Admin y Tenant Configuration
Controla el acceso y límites del sistema SaaS.
- **Controlador:** `App\Http\Controllers\Admin\TenantController`
  - **Métodos Clave:**
    - `capabilities()` / `updateCapabilities()`: Gestionan el JSON de configuración de un tenant, definiendo qué reportes y features están habilitados (`REPORT_ACCESS_CONTROL_SPEC.md`).
    - `index()`, `store()`, `update()`: CRUD clásico de Tenants.
- **Vistas:**
  - `admin.tenants.index`: Listado de agencias.
  - `admin.tenants.capabilities`: Formulario de configuración granular (checklists de reportes, límites).

### 2.2 Módulo: Panel de Administración y Configuración (Admin del Tenant)
Este módulo permite a los administradores de cada agencia (Tenant) personalizar la experiencia, la facturación y el comportamiento del software de manera aislada para su negocio.
- **Controladores:** 
  - `App\Http\Controllers\Admin\ConfigController`
  - `App\Http\Controllers\UserController`
- **Secciones de Configuración Clave:**
  - **Generación de Referencias (`/configuracion-referencias`):** Define prefijos, sufijos y el formato automático secuencial para la creación de nuevas referencias operativas.
  - **Analíticas y Metas (`/configuracion-analiticas`):** Establece objetivos de KPIs (ej. meta de despachos semanales) para visualizarlos y medirlos en los gráficos de reportes.
  - **Plantillas de Correo (`/configuracion-plantillas`):** Personalización visual (HTML/Texto) de las notificaciones enviadas a los clientes, incluyendo previsualización en vivo.
  - **Servidor SMTP (`/configuracion-smtp`):** Permite a la agencia configurar sus propias credenciales de envío de correo (Host, Puerto, Usuario, Contraseña) para que las notificaciones salgan bajo su dominio personalizado (ej. `operaciones@miagencia.com`). Incluye funcionalidad de prueba (`probarSmtp`).
  - **Gestión de Usuarios (`/usuarios`):** CRUD completo para añadir, editar o desactivar a su personal, manteniendo la restricción de acceso exclusiva a su propio Tenant.
- **Vistas:**
  - `admin.config.referencia`, `admin.config.analiticas`, `admin.config.plantillas`, `admin.config.smtp`, `usuarios.index`.

### 2.3 Módulo: Gestión Operativa y Documentación
Gestión centralizada de operaciones. No existe un módulo de "Tráfico" independiente; todo el despacho, captura de DODA/Pedimento y subida de evidencias se opera desde el panel del documentador.
- **Controladores:** 
  - `App\Http\Controllers\DocumentadorController` (Gestión principal del dashboard UI y flujos de actualización).
  - `App\Http\Controllers\OperacionController` (Lógica base de BD y endpoints de creación `storetrafico`).
  - `App\Http\Controllers\ExpedienteController`
  - `App\Http\Controllers\DocumentoController`
- **Métodos Clave:**
  - `index()` / `liveData()` en `DocumentadorController`: Renderizan y alimentan el dashboard principal interactivo de operaciones en curso.
  - `storeOperacion()` / `updateDodaPedimento()`: Creación de registros y actualización directa de la metadata operativa.
  - `uploadDocument()` / `store()`: Almacenamiento seguro en el bucket de Cloudflare R2 con URLs temporales.
- **Vistas:**
  - `documentador.dashboard`: Panel único y central (sustituye a cualquier dashboard de tráfico) desde el cual se monitorizan y gestionan todos los trámites.
  - `expedientes.show`: Vista detallada de un expediente (`/expedientes/{id}`) que concentra las operaciones asociadas y valida su cumplimiento legal.

#### 2.3.1 Expedientes Digitales y Cumplimiento del Art. 36-A L.A.
Dentro del detalle de un Expediente, el sistema audita de forma proactiva el cumplimiento estricto del Artículo 36-A de la Ley Aduanera referente al Expediente Electrónico/Digital. Para que un expediente logre el estatus de cumplimiento al 100%, el sistema valida dos categorías:

**1. Documentos del Expediente Maestro (Permanentes):**
Documentación legal base de la empresa o cliente:
- Constancia de Situación Fiscal (RFC)
- Comprobante de Domicilio
- Acta Constitutiva
- Poder Notarial
- Identificación Oficial

**2. Documentos Transaccionales (Por Operación):**
Documentación que debe ser cargada obligatoriamente por **cada una de las operaciones** que conforman el expediente (es decir, si el expediente tiene 3 operaciones, las 3 deben contar con su respectiva factura):
- Factura Comercial
- Encargo Conferido
- Documentos de Transporte (BL / Guía Aérea / Carta Porte)
- Lista de Empaque (Packing List)
- Certificado de Origen
- Cumplimiento RRNA's (Regulaciones y Restricciones No Arancelarias)
- Comprobantes de Gastos Incrementables
- DODA / PITA
- Carta de Cupo
- Certificación de Valor (VAL)

*Nota de Flexibilidad:* El sistema calcula de manera automática la cobertura documental (ej. "2 de 3 operaciones con archivo"). No obstante, si un documento no aplica a la naturaleza del trámite (p. ej. si no requiere Carta de Cupo), el documentador tiene la potestad de marcar manualmente la validación desde el checklist (Override) para dar el requisito por cumplido y avanzar hacia el cierre.

#### 2.3.2 Cierre de Pedimento y Firma de Expediente
Una vez que el expediente ha alcanzado el 100% de cumplimiento en su checklist documental (o los requisitos faltantes han sido omitidos manualmente según la naturaleza del despacho), el sistema permite ejecutar el **Cierre de Pedimento**. 
Durante este flujo de finalización ocurre lo siguiente:
- **Cambio de Estatus:** El estado operativo del expediente cambia definitivamente a **"Cerrado"**, bloqueándolo para modificaciones no autorizadas y estableciendo al usuario que realizó la acción.
- **Trazabilidad y Fechas Clave:** Se capturan y guardan permanentemente la **Fecha de Pago** del pedimento y la **Fecha de Cierre** formal de la operación.
- **Archivo Final (Pedimento Pagado):** Como paso crucial durante el modal de cierre, es necesario (y altamente recomendado) adjuntar el archivo PDF del **"Pedimento Pagado"**. Este documento se aloja en el expediente como el comprobante maestro que sella, finaliza y avala la conclusión total del despacho ante una auditoría aduanera.

### 2.4 Módulo: Bot SOIA/PECEM (Integración SAT)
Motor autónomo de seguimiento de aduanas.
- **Controlador:** `App\Http\Controllers\DodaBotController`
- **Métodos Clave:**
  - `ejecutar()`: Ejecución principal de la API del bot que lee operaciones pendientes, hace requests vía Guzzle Pool (scraping de tablas HTML del SAT) y actualiza BD.
  - `showTestPanel()`, `runLocal()`: Interfaz de prueba manual del bot.
- **Modelo:** `OperacionHistorialDoda` / `Operacion` (campo `bot_logs_json`).
- **Rutas de API:** `/api/bot/doda/ejecutar`.

### 2.5 Módulo: Reportes y Analítica
Generación de métricas e inteligencia de negocio, controlados estrictamente por los permisos y límites del Tenant configurados por el Super Admin (basado en `REPORT_ACCESS_CONTROL_SPEC.md`).
- **Controladores:**
  - `App\Http\Controllers\ReporteController`
  - `App\Http\Controllers\ReporteClienteMailController` (Control de distribución pública de reportes)
- **Catálogo de Reportes Disponibles:**
  - **Reporte por Cliente (`/reportes/cliente`):** Analítica detallada de operaciones filtrada por un cliente específico, fuertemente vinculada a la `fecha_cruce_estimada`. Permite exportación a PDF.
  - **Operaciones Diarias y Semanales (`/reportes/operaciones-diarias`, `/reportes/operaciones_semanas`):** Seguimiento del volumen operativo día a día o agrupado por semanas para medir la carga de trabajo en la agencia.
  - **Trámites Anuales y Comparativos (`/reportes/tramites-anuales`, `/reportes/tramites-comparativos`):** Visión macroscópica para observar el crecimiento año con año y comparar rendimientos entre periodos (meses, trimestres, años).
  - **Reporte de Remesas (`/reportes/remesas`):** Control y seguimiento de los envíos o cruces agrupados por remesas para un mejor control documental.
  - **Reporte de Aduanas (`/reportes/aduanas`):** Mide la distribución del volumen de trabajo a través de las distintas aduanas utilizadas por la agencia.
  - **Reporte de Gerencia (`/reportes/gerencia`):** Tablero ejecutivo con los KPIs financieros y operativos de alto nivel para la toma de decisiones.
  - **Patrones de Cliente (`/reportes/patrones-cliente`):** Analítica avanzada sobre el comportamiento del cliente, frecuencias de despacho y tendencias operativas.
  - **Calendario de Primeras Operaciones (`/reportes/calendario-primeras-operaciones`):** Vista de calendario interactiva que resalta los hitos de las operaciones entrantes y planeadas.
  - **Reporte de Pedimentos (`/reportes/pedimentos`):** Directorio completo de pedimentos con KPIs (Total, Cumplidos, Pendientes, Docs Faltantes), filtros por fecha, numero de pedimento, cliente, estado y categoria. Incluye modal de detalle con checklist de documentos faltantes, link directo al expediente y exportacion a PDF. (INC-035)
- **Métodos Clave:**
  - `enviarMasivo()`: Envío por lotes de estados de cuenta o estatus operativos en formato PDF directamente al cliente final desde la plataforma.

### 2.6 Módulo: Finanzas
Conciliación de pagos, facturación y anexos (sobrepeso, maniobras, etc.).
- **Controladores:**
  - `App\Http\Controllers\FinanzasController`
  - `App\Http\Controllers\ConceptoAdicionalController`
- **Métodos Clave:**
  - `guardarFactura()`, `detalleClientePatente()`.
  - `exportarPDF()`, `exportarDetalleClientePatentePDF()`.
- **Vistas:**
  - `finanzas.index`, `finanzas.detalle.cliente.patente`.

---

## 3. Arquitectura y Modelos Clave (Modelos de BD)

La aplicación utiliza Eloquent ORM con los siguientes modelos principales:
- **`Tenant`**: Entidad raíz para la estructura SaaS. Contiene el campo JSON `configuracion` vital para los límites y reportes habilitados.
- **`User`**: Pertenece a un Tenant. Administra su autenticación y rol (`super_admin`, `admin`, `trafico`, `documentador`, `cliente`).
- **`Operacion`**: Corazón del sistema. Relaciona a un `Cliente`, `Patente`, `Aduana`, `Bodega` y `Importador`. Guarda estatus, modulación y logs del bot.
- **`Documento`**: Representa un archivo almacenado en Cloudflare R2 (`filesystem_disk = r2`), vinculado a un `tenant_id` y `operacion_id`.
- **`Notificacion` / `Directorio`**: Controla el enrutamiento inteligente (Email/WhatsApp) cuando cambia el estatus de modulación.

## 4. Consideraciones Técnicas Generales
- **Seguridad en Rutas**: Implementación rigurosa de Middlewares (ej. `role:admin`, `report.access:nombre_reporte`) para evitar filtración de datos entre tenants o exposición de características no pagadas.
- **Almacenamiento (Cloudflare R2)**: Evita la saturación del disco local. Genera rutas dinámicas como `tenant_{tenant_id}/op_{operacion_id}/{timestamp}_{archivo.ext}`.
- **Trabajo asíncrono**: Dashboards de Tráfico y Documentador hacen uso intensivo de peticiones AJAX a endpoints como `/dashboard/ajax` y la api de `notificaciones-sistema/no-leidas`.

---

## 5. Analisis KimiK2.6

### 5.1 Diagnóstico General del Código Base

El proyecto NexaCore Aduanal presenta una arquitectura SaaS multi-tenant sólida con buenas prácticas en seguridad (middleware de roles, aislamiento por tenant) y una integración robusta con servicios externos (SAT/SOIA, Cloudflare R2). Sin embargo, se detectó deuda técnica acumulada que requiere atención inmediata para garantizar la escalabilidad, mantenibilidad y consistencia del código.

**Hallazgos Positivos:**
- Modelo `Tenant` con JSON `configuracion` altamente flexible para feature flags y límites de suscripción.
- Bot DODA/SOIA con mecanismos de anti-concurrencia (Cache lock), health checks y logging dedicado.
- Cumplimiento del Art. 36-A con lógica de override manual y cálculo automático de cobertura documental.
- Sistema de Trial completo con verificación de email y flujo de onboarding.
- Middleware `report.access` para control granular de reportes por plan.

**Deuda Técnica Crítica:**
- **Archivos duplicados/legacy**: 17 archivos Blade con sufijo `*_Original*` que desordenan el proyecto.
- **Rutas duplicadas**: `dashcliente` definido dos veces en `routes/web.php`.
- **Almacenamiento local no migrado**: A pesar de tener R2 configurado en `config/filesystems.php`, los controladores (`DocumentoController`, `ExpedienteController`) siguen usando `store('documentos')` en disco local.
- **Código comentado legacy**: Múltiples métodos `index_OLD`, `index_Old2`, `cerrarFirma_OLD`, `OLD__construct_OLD` en controladores.
- **Uso de `dd()` en producción**: Varios métodos en `DocumentoController` y `ExpedienteController` usan `dd()` para debugueo en lugar de logging adecuado.
- **Inconsistencia en imports**: Uso de clases con barra invertida (`\DB`, `\App\Models\Operacion`) en lugar de `use` statements.
- **Dependencia obsoleta**: `maatwebsite/excel` en versión `^1.1` (muy antigua, inestable con Laravel 12).
- **Cobertura de tests insuficiente**: Solo 4 archivos de test para un sistema de esta magnitud.

### 5.2 Checklist de Acciones de Optimización

| # | Acción | Prioridad | Estado |
|---|--------|-----------|--------|
| 1 | **INC-001**: Completar implementación de Cloudflare R2 para almacenamiento de documentos | Alta | Cerrado |
| 2 | **INC-002**: Asignar tipo de documento Art. 36-A por archivo individual en subida múltiple y conectar con checklist automático | Alta | Cerrado |
| 3 | **INC-003**: Actualizar vistas de expedientes para soportar R2 y selección de tipos individuales | Alta | Cerrado |
| 4 | **INC-004**: Eliminar archivos `*_Original*` y métodos legacy comentados | Media | En Progreso |
| 5 | **INC-005**: Actualizar `maatwebsite/excel` a versión 3.x y crear tests de Feature críticos | Media | Pendiente |
| 6 | **INC-006**: Corregir ruta duplicada `dashcliente` y limpiar rutas comentadas | Media | Cerrado |
| 7 | **INC-007**: Reemplazar `dd()` por logging (`Log::error`) en controladores | Media | Cerrado |
| 8 | **INC-008**: Estandarizar imports (reemplazar `\DB`, `\App\Models` por `use` statements) | Baja | Pendiente |
| 9 | **INC-009**: Optimizar rendimiento de `/documentador/dashboard` (N+1 en liveData, consolidados y modal de archivos) | Alta | Cerrado |
| 10 | **INC-010**: Error al subir documentos a R2 — Bucket null en AwsS3V3Adapter | Alta | Cerrado |
| 11 | **INC-011**: Selector de tipo de documento Art. 36-A en modal de subida del dashboard | Alta | Cerrado |
| 12 | **INC-012**: Vista Previa de documentos retorna 404 (R2 privado + disco local incorrecto) | Alta | Cerrado |
| 13 | **INC-013**: Subida en modal "Nueva Operación" sin metadatos R2 y sin selector Art. 36-A | Alta | Cerrado |
| 14 | **INC-015**: Filtros de búsqueda global y comportamiento LiveData | Alta | Cerrado |
| 15 | **INC-016**: Exclusión de operaciones canceladas en todos los reportes y dashboards | Alta | Cerrado |
| 16 | **INC-017**: Auditoría y Corrección de Aislamiento Multi-Tenant en Todos los Reportes | Alta | Cerrado |
| 17 | **INC-018**: Reparación de Vistas de Clientes (Bootstrap a Tailwind + Campos Correctos) | Alta | Cerrado |
| 18 | **INC-019**: Reestructuración de Documentos Maestros Art. 36-A a Nivel Cliente | Alta | Cerrado |
| 19 | **INC-020**: Auditoría y Corrección del Sistema de Notificaciones (Enlaces Rotos y Bugs) | Alta | Cerrado |
| 20 | **INC-021**: Migración Masiva de Vistas Bootstrap a Tailwind (Catálogos y Módulos) | Alta | Cerrado |
| 21 | **INC-022**: Pendiente de limpieza Bootstrap en Reportes, Finanzas y Dashboards Legacy | Media | Pendiente |
| 22 | **INC-039**: Bot DODA se detiene al encontrar errores "DODA NO COINCIDE" — no consulta operaciones restantes | Alta | Cerrado |
| 23 | **INC-040**: Botón "Reenviar" WhatsApp no valida límite de mensajes + doble incremento del contador | Alta | Cerrado |
| 24 | **INC-041**: Capturar fecha real de activación del PECEM y actualizar fecha_cruce_estimada | Alta | Cerrado |
| 25 | **INC-042**: Containerización Docker completa para despliegue en VPS con dockploy | Alta | Cerrado |
| 26 | **INC-043**: Corrección de build Docker — eliminación de dependencia imagick innecesaria | Media | Cerrado |
| 27 | **INC-044**: Conflicto de puerto 80 en deploy Docker — limpieza de contenedores previos | Media | Cerrado |
| 28 | **INC-045**: Eliminación de mapeo directo de puertos — delegar ruteo a Traefik de dockploy | Media | Cerrado |
| 29 | **INC-046**: Corrección de red Docker — migración a red overlay dokploy-network para conectividad con Traefik | Media | Cerrado |
| 30 | **INC-047**: Corrección de seeder InitialTenant — columna tenant_id inexistente en reportes_acceso | Alta | Cerrado |
| 31 | **INC-048**: Corrección de seeder DatabaseSeeder — eliminación de User::factory() que requiere Faker en producción | Alta | Cerrado |
| 32 | **INC-049**: Alerta de conexión no segura en formularios HTTP — habilitar HTTPS con Let's Encrypt | Alta | Cerrado |
| 33 | **INC-050**: Superadmin no puede suspender/bloquear una agencia (Tenant) | Alta | Cerrado |
| 34 | **INC-051**: Superadmin no puede crear usuarios manualmente para un Tenant | Media | Cerrado |
| 35 | **INC-052**: Sistema de facturación y gestión de pagos por Tenant (MVP + automatización) | Alta | Pendiente |

---

## 6. Registro de Incidentes (INC)

Formato estándar para documentar mejoras y correcciones aplicadas:

```
### INC-XXX: [Título del Incidente]
**Fecha:** YYYY-MM-DD
**Descripción:** Breve explicación del problema o mejora identificada.
**Solución Propuesta:** Descripción técnica de la solución a implementar.
**Archivos Modificados:** Lista de archivos afectados.
**Solución Aplicada:** (Se completa después de la implementación) Descripción de lo que finalmente se hizo.
**Estado:** Abierto / En Progreso / Cerrado
```

---

### INC-001: Implementación Completa de Cloudflare R2
**Fecha:** 2026-05-05
**Descripción:** Aunque el disco `r2` está configurado en `config/filesystems.php` y la tabla `documentos` ya cuenta con las columnas `url_archivo`, `peso` y `extension`, todos los controladores (`DocumentoController`, `ExpedienteController`) siguen utilizando `store('documentos')` y `Storage::disk('local')` para guardar y servir archivos. Esto provoca saturación del disco local, falta de aislamiento por tenant y imposibilidad de escalar.
**Solución Propuesta:**
1. Crear un `DocumentoStorageService` centralizado que gestione la subida a R2 usando `Storage::disk('r2')`.
2. Generar rutas dinámicas: `tenant_{tenant_id}/op_{operacion_id}/{tipo_documento}/{timestamp}_{nombre_archivo}`.
3. Persistir `url_archivo`, `peso`, `extension` en cada registro de `Documento`.
4. Actualizar todos los métodos de subida (`store`, `store2`, `store3`, `storeConceptoAdicional`, `processDocuments`, `cerrarFirma`).
5. Actualizar métodos de lectura (`download`, `preview`) para servir desde R2 (URLs firmadas o públicas según configuración).
6. Agregar variables de entorno al `.env.example` para facilitar la configuración.
**Archivos Modificados:**
- `app/Services/DocumentoStorageService.php` (Nuevo)
- `app/Models/Documento.php`
- `app/Http/Controllers/DocumentoController.php`
- `app/Http/Controllers/ExpedienteController.php`
- `config/filesystems.php` (ya tenía disco r2)
- `.env.example`
**Solución Aplicada:**
1. Se creó `DocumentoStorageService` que centraliza la subida, eliminación, descarga y preview de archivos en Cloudflare R2 usando `Storage::disk('r2')`.
2. Se generan rutas estructuradas: `tenant_{id}/op_{id}/{tipo_doc}/{timestamp}_{nombre}.ext`.
3. Se actualizó `DocumentoController`: `store`, `store2`, `store3`, `storeConceptoAdicional`, `destroy`, `download`, `preview` — todos usan el servicio R2.
4. Se actualizó `ExpedienteController`: `processDocuments` y `cerrarFirma` ahora suben a R2.
5. Se actualizó `downloadAllDocuments` para leer desde R2 o local según el origen del documento.
6. Se actualizó el modelo `Documento` con accessors `en_r2`, `url_preview` y fallback a local.
7. Se agregaron variables R2 al `.env.example`.
8. Se reemplazaron todos los `dd()` por `Log::error` y manejo de excepciones adecuado.
**Estado:** Cerrado

---

### INC-002: Tipos de Documento Art. 36-A por Archivo Individual
**Fecha:** 2026-05-05
**Descripción:** Actualmente, al subir múltiples archivos a una operación desde el modal de expedientes (`uploadOpModal`), solo existe un select único de `tipo_documento` que se aplica a TODOS los archivos. Esto impide clasificar correctamente cada documento según el Art. 36-A (Factura, BL, Encargo Conferido, etc.). Además, aunque el modelo `Expediente` ya calcula `cumplimiento_completo` basado en `tipo_documento`, la UI no permite asignar tipos individuales, lo que rompe la lógica del checklist automático.
**Solución Propuesta:**
1. Modificar la vista `expedientes/show.blade.php` para que cada archivo en la lista de pre-visualización tenga su propio select de tipo de documento.
2. Enviar un array `tipos_documento[]` al backend, alineado con el array de archivos.
3. Actualizar `DocumentoController::store3` (y `store2` si aplica) para asignar el tipo correspondiente a cada archivo.
4. Asegurar que el checklist del modal se actualice visualmente tras la subida (o al menos recargar la página con el mensaje de éxito).
5. Mantener el diseño premium Tailwind siguiendo el skill de NexaCore Design Language.
**Archivos Modificados:**
- `resources/views/expedientes/show.blade.php`
- `app/Http/Controllers/DocumentoController.php`
**Solución Aplicada:**
1. Se eliminó el select único de tipo de documento del formulario de subida múltiple.
2. Se actualizó `handleMultipleFiles()` en el JS para generar un `<select name="tipos_documento[]">` por cada archivo listado, permitiendo asignar el tipo individualmente.
3. Se actualizó `uploadFiles()` para enviar la petición a `documentos_operacion.store2` (ruta de `store3`) que ya soporta `tipos_documento[]`.
4. Se mantuvo el diseño Tailwind siguiendo el skill de NexaCore (bordes redondeados `rounded-xl`, espaciado consistente, tipografía `text-[11px] font-bold`).
5. Se agregó manejo de `finally` en el loader del botón para evitar bloqueos.
**Estado:** Cerrado

---

### INC-004: Limpieza de Código Legacy
**Fecha:** 2026-05-05
**Descripción:** El proyecto acumuló múltiples métodos comentados, copias de respaldo de vistas (sufijo `_Original`) y constructores legacy que aumentan la deuda técnica, dificultan la navegación del código y pueden confundir al equipo de desarrollo.
**Solución Propuesta:**
1. Eliminar todos los archivos Blade con sufijo `_Original`, `_Original2` o similar.
2. Eliminar métodos `index_OLD`, `index_Old2`, `cerrarFirma_OLD`, `OLD__construct_OLD` y similares de los controladores.
3. Eliminar código comentado obsoleto.
**Archivos Modificados:**
- `resources/views/` (eliminados 17 archivos `_Original`)
- `app/Http/Controllers/DocumentadorController.php`
- `app/Http/Controllers/ExpedienteController.php`
**Solución Aplicada:**
- Se eliminaron 17 archivos Blade duplicados/legacy.
- Se eliminó `OLD__construct_OLD` comentado de `DocumentadorController`.
- Se eliminaron `index_original`, `index_original2` e `index_OLD` de `DocumentadorController`.
- Se eliminaron `index_OLD` e `index_Old2` de `ExpedienteController`.
- Se eliminó `cerrarFirma_OLD` de `ExpedienteController`.
**Estado:** En Progreso (quedan métodos legacy en `OperacionController`, `ReporteController`, `DashboardController`, `FinanzasController`, `ConceptoAdicionalController`, `OperacionImportController`, `ReporteClienteMailController`)

---

### INC-006: Corrección de Ruta Duplicada `dashcliente`
**Fecha:** 2026-05-05
**Descripción:** En `routes/web.php` la ruta `dashcliente` estaba definida dos veces (líneas 594 y 596), causando que la segunda sobreescribiera a la primera y pudiera generar comportamientos inesperados.
**Solución Aplicada:** Se eliminó la definición duplicada `Route::get('dashcliente', ...)->name('cliente.index');` y se dejó la ruta única con nombre `cliente.admindashboard2`.
**Archivos Modificados:** `routes/web.php`
**Estado:** Cerrado

---

### INC-007: Reemplazo de `dd()` por Logging
**Fecha:** 2026-05-05
**Descripción:** Múltiples métodos en `DocumentoController` y `ExpedienteController` utilizaban `dd()` para depuración, lo cual expone información sensible y rompe el flujo de la aplicación en producción.
**Solución Aplicada:**
- `DocumentoController`: Todos los `dd()` fueron reemplazados por bloques `try/catch` con `Log::error` y redirección con mensaje flash.
- `ExpedienteController`: Los `dd()` en `store`, `update` y `cerrarFirma` fueron eliminados y reemplazados por logging.
**Archivos Modificados:** `app/Http/Controllers/DocumentoController.php`, `app/Http/Controllers/ExpedienteController.php`
**Estado:** Cerrado

---

### INC-009: Optimización de Rendimiento en /documentador/dashboard
**Fecha:** 2026-05-05
**Descripción:** El panel del documentador presentaba latencia excesiva al abrir el modal de detalles de una operación y al subir archivos. El problema se identificó en tres áreas: (1) `liveData` cargaba **todos los documentos de todas las operaciones** en cada petición AJAX (cada 6 segundos), (2) `index` ejecutaba **2 queries adicionales por cada operación** en un loop para calcular consolidados (N+1), y (3) `trabajarOperacion2` no hacía eager loading de `patente` en los expedientes, generando N+1 en la vista de trabajo.
**Solución Propuesta:**
1. Crear endpoint API dedicado `documentador/api/operaciones/{id}/documentos` que devuelva solo los documentos de una operación bajo demanda.
2. Eliminar `documentos` del `with()` de `liveData`; reemplazar por `documentos_count`.
3. Actualizar el frontend para cargar documentos del modal vía el endpoint dedicado, y solo refrescar `liveData` en background para contadores.
4. Precalcular datos de consolidados en `index` con una única query agrupada en lugar de un loop con queries individuales.
5. Agregar `with('patente')` a la consulta de expedientes en `trabajarOperacion2`.
**Archivos Modificados:**
- `app/Http/Controllers/DocumentadorController.php`
- `resources/views/documentador/dashboard.blade.php`
- `routes/web.php`
**Solución Aplicada:**
1. Se creó `DocumentadorController::getDocumentosOperacion()` — endpoint API ligero que retorna solo documentos de una operación con sus URLs.
2. Se optimizó `liveData()`: removido `documentos` del `with`, ahora devuelve solo `documentos_count`. Esto reduce drásticamente el payload JSON y el tiempo de query.
3. Se optimizó `index()`: el loop que calculaba `consolidado_count` y `consolidado_first` se reemplazó por una única query agrupada por `num_thermo|codigo_alpha` usando `where ... orWhere` dinámico, eliminando 2 queries N+1 por operación.
4. Se optimizó `trabajarOperacion2()`: agregado `with('patente')` a `$expedientes`, eliminando N+1 al renderizar la lista de pedimentos en la vista `trabajar`.
5. Se actualizó `dashboard.blade.php`:
   - `openDetailsModal()` ahora llama `fetchModalDocuments(opId)` para cargar archivos bajo demanda.
   - `uploadSingleFile()` y `deleteDocumentModal()` ahora llaman `fetchModalDocuments()` en lugar de `fetchLiveDataForModal()`, evitando recargar TODO el dashboard en cada acción.
   - `fetchLiveDataForModal()` fue eliminado.
**Estado:** Cerrado

---

### INC-010: Error al Subir Documentos a Cloudflare R2 — Bucket null en AwsS3V3Adapter
**Fecha:** 2026-05-06
**Descripción:** Al intentar subir cualquier documento a Cloudflare R2, la aplicación lanzaba el error `League\Flysystem\AwsS3V3\AwsS3V3Adapter::__construct(): Argument #2 ($bucket) must be of type string, null given`, lo que impedía completamente la operación de subida de archivos. El error se originaba en `FilesystemManager.php:257` al intentar construir el adaptador S3 para el disco `r2`, donde el parámetro `bucket` llegaba como `null`.
**Causa Raíz:** Se identificaron dos factores contribuyentes:
1. **Archivos `.env.local` sin variables R2:** El archivo `.env.local` existía con `FILESYSTEM_DISK=local` pero sin las variables `R2_*` (access key, secret, bucket, endpoint, url). Al cargar el entorno local, Laravel mergea `.env.local` sobre `.env`; aunque las variables R2 de `.env` se preservaban, la ausencia en `.env.local` creaba un escenario de riesgo donde un `config:cache` ejecutado en ese contexto podría cachear valores `null` para las variables R2.
2. **Configuración sin valores fallback:** `config/filesystems.php` tenía `'bucket' => env('R2_BUCKET')` sin un valor por defecto. Si por cualquier motivo (cache de configuración obsoleto, `.env` sin la variable, despliegue parcial) la variable de entorno no se resolvía, el bucket quedaba como `null`, causando el TypeError fatal.
**Solución Propuesta:**
1. Agregar valor fallback `'nexacoreaduanal'` al campo `bucket` en `config/filesystems.php` para prevenir `null`.
2. Actualizar `.env.local` con todas las variables R2 necesarias, igualando el entorno `.env`.
3. Cambiar `FILESYSTEM_DISK=r2` en `.env.local` para que el disco por defecto sea R2 en desarrollo local (consistente con el uso del sistema).
4. Ejecutar `php artisan optimize:clear` y `php artisan config:cache` para regenerar la cache de configuración.
**Archivos Modificados:**
- `config/filesystems.php` (fallback en `R2_BUCKET`)
- `.env.local` (variables R2 y `FILESYSTEM_DISK=r2`)
**Solución Aplicada:**
1. Se agregó fallback en `config/filesystems.php`: `'bucket' => env('R2_BUCKET', 'nexacoreaduanal')` para que nunca sea `null`.
2. Se actualizó `.env.local` con todas las variables R2 (`R2_ACCESS_KEY_ID`, `R2_SECRET_ACCESS_KEY`, `R2_REGION`, `R2_BUCKET`, `R2_URL`, `R2_ENDPOINT`) alineadas con `.env`.
3. Se cambió `FILESYSTEM_DISK=local` a `FILESYSTEM_DISK=r2` en `.env.local`.
4. Se ejecutó `php artisan optimize:clear` y `php artisan config:cache`.
5. Se verificó con `tinker` que `Storage::disk('r2')` resuelve correctamente y que la subida/lectura/borrado de archivos funciona sin errores.
**Estado:** Cerrado

---

### INC-011: Selector de Tipo de Documento Art. 36-A en Modal de Subida del Dashboard
**Fecha:** 2026-05-06
**Descripción:** Al subir archivos desde el modal de detalles de operación en el dashboard del documentador (`/documentador/dashboard`), el tipo de documento se hardcodedaba como `'otros'` en la función `uploadSingleFile()` del JavaScript. Esto impedía al usuario clasificar correctamente cada documento según el Art. 36-A de la Ley Aduanera (Factura, Encargo Conferido, BL, DODA, etc.), lo que rompía la lógica del checklist de cumplimiento del expediente digital.
**Solución Propuesta:**
1. Agregar un selector `<select>` de tipo de documento en el panel derecho del modal de detalles, con las opciones del catálogo Art. 36-A (Maestros y Transaccionales) más "Otros".
2. Modificar `uploadSingleFile()` para enviar el valor seleccionado como `tipos_documento[0]` en vez del hardcoded `'otros'`.
3. Cambiar el flujo: al hacer clic en "Subir Archivo", primero se muestra el selector de tipo, y al confirmar se abre el explorador de archivos.
4. Mostrar las etiquetas legibles en la lista de documentos del modal usando un mapa JS de claves a etiquetas.
**Archivos Modificados:**
- `resources/views/documentador/dashboard.blade.php` (selector de tipo, funciones JS)
**Solución Aplicada:**
1. Se agregó un panel colapsable `#uploadTypeSelector` con un `<select id="uploadTipoDocumento">` que contiene los tipos del Art. 36-A (Maestros: Acta, Poder, Identificación, CSF, Domicilio; Transaccionales: Factura, Encargo, Transporte, Empaque, Origen, RRNA, Gastos, DODA, Cupo, VAL; Otros: Pedimento Pagado, Concepto Adicional, Otros). Valor por defecto: "factura".
2. Se reemplazó el botón directo de subida por un flujo de dos pasos: (1) clic en "Subir Archivo" muestra el selector, (2) clic en "Confirmar y Seleccionar Archivo" abre el explorador de archivos.
3. Se modificó `uploadSingleFile()` para leer `document.getElementById('uploadTipoDocumento')?.value || 'otros'` y enviarlo como `tipos_documento[0]` en lugar del hardcoded `'otros'`.
4. Se oculta el selector automáticamente después de la subida (en el bloque `finally`).
5. Se actualizó `renderModalFiles()` con un mapa `tipoLabels` para mostrar las etiquetas legibles en español (ej. `'factura'` → `'Factura Comercial'`) en lugar de la clave cruda. Se cambió el estilo del badge de `text-gray-500 bg-gray-100` a `text-indigo-600 bg-indigo-50` para distinguir mejor el tipo de documento.
**Estado:** Cerrado

---

### INC-012: Vista Previa de Documentos Retorna 404
**Fecha:** 2026-05-06
**Descripción:** Al hacer clic en el ícono de "Vista Previa" en el modal de detalles de operación (`/documentos/{id}/preview`), la ruta retornaba 404. Se identificaron dos causas:
1. **Documentos en R2 (nuevos):** El método `preview()` redirigía a `$documento->url_archivo` (URL pública del bucket R2), pero el bucket de Cloudflare R2 es privado por defecto, lo que resulta en acceso denegado (403/404).
2. **Documentos locales (legacy):** El método usaba `Storage::exists()` y `Storage::path()` sin especificar el disco, que desde INC-010 resuelve al disco `r2` por defecto. Los archivos locales legacy se buscaban en R2 en vez del disco local, resultando en 404.
**Causa Raíz:**
1. Para R2: `$documento->url_archivo` genera una URL directa al objeto en un bucket privado sin firma de acceso.
2. Para local: El cambio de `FILESYSTEM_DISK` de `local` a `r2` hizo que `Storage::exists()` y `Storage::path()` buscaran en el disco equivocado.
**Solución Propuesta:**
1. Para documentos en R2: usar `Storage::disk('r2')->temporaryUrl($documento->ruta, now()->addMinutes(30))` para generar URLs firmadas temporales.
2. Para documentos locales: usar explícitamente `Storage::disk('local')` en vez del facade genérico `Storage`.
3. Aplicar la misma corrección a los métodos `download()` y `destroy()`.
4. Agregar `request()->wantsJson()` al `destroy()` para devolver JSON en peticiones AJAX del dashboard.
**Archivos Modificados:**
- `app/Http/Controllers/DocumentoController.php` (métodos `preview`, `download`, `destroy`)
**Solución Aplicada:**
1. `preview()`: Si `en_r2`, genera URL firmada temporal de 30 minutos con `Storage::disk('r2')->temporaryUrl()` y redirige. Si es local, usa `Storage::disk('local')` para verificar existencia y servir el archivo.
2. `download()`: Mismo patrón — R2 usa `storageService->download()`, local usa `Storage::disk('local')->download()`. Se corrigió el fallback local para usar el disco correcto.
3. `destroy()`: Ahora distingue entre R2 y local. Para R2 usa `storageService->delete()`, para local usa `Storage::disk('local')->delete()`. También devuelve JSON para peticiones AJAX del dashboard.
4. Se eliminó el uso de `Storage::exists()`, `Storage::download()` y `Storage::path()` sin disco explícito, previniendo que el disco por defecto (`r2`) cause falsos negativos en archivos locales.
**Estado:** Cerrado

---

### INC-013: Subida de Archivos en Modal "Nueva Operación" sin Metadatos R2 y sin Selector de Tipo Art. 36-A
**Fecha:** 2026-05-06
**Descripción:** El modal de "Registrar Nueva Operación" en el dashboard del documentador tenía dos problemas:
1. **Tipo de documento incorrecto:** El selector de tipo de archivo solo tenía opciones genéricas (`Factura`, `DODA`, `Guía`, `Permiso`, `Pedimento`, `Otros`) que no correspondían a las claves del catálogo Art. 36-A definido en el modelo `Expediente`. El valor por defecto era `'otros'` en lugar de `'factura'`.
2. **Metadatos incompletos en R2:** El controlador `DocumentadorController::storeOperacion()` usaba `$archivo->store('documentos', 'r2')` para guardar archivos, lo cual solo almacena la ruta relativa en el bucket pero no persiste `url_archivo`, `peso`, `extension` ni `tenant_id` en el registro de `Documento`. Lo mismo ocurría en `OperacionController::storetrafico()`.
**Causa Raíz:**
1. El JS del modal tenía opciones hardcodeadas (`Factura`, `DODA`, etc.) que no coincidían con las claves del sistema (`factura`, `doda`, `encargo`, etc.) y no incluía todos los tipos del Art. 36-A.
2. Los controladores usaban el helper `store()` de Laravel en vez del `DocumentoStorageService`, lo cual no persiste los metadatos completos necesarios para que `en_r2`, `url_preview` y la descarga funcionen correctamente.
**Solución Propuesta:**
1. Reemplazar las opciones del selector en el JS del modal por el catálogo completo del Art. 36-A (`acta`, `poder`, `identificacion`, `rfc`, `domicilio`, `factura`, `encargo`, `transporte`, `empaque`, `origen`, `rrna`, `gastos`, `doda`, `cupo`, `val`, `pedimento_pagado`, `concepto_adicional`, `otros`), organizados en `<optgroup>`.
2. Cambiar el valor por defecto de `type` de `'otros'` a `'factura'` (el tipo más común en operaciones aduanales).
3. Refactorizar `storeOperacion()` y `storetrafico()` para usar `DocumentoStorageService::upload()` en vez de `$archivo->store()`, persistiendo `url_archivo`, `peso`, `extension` y `tenant_id`.
4. Inyectar `DocumentoStorageService` en ambos controladores vía constructor.
**Archivos Modificados:**
- `resources/views/documentador/dashboard.blade.php` (selector de tipo Art. 36-A en modal crear operación y render de archivos)
- `app/Http/Controllers/DocumentadorController.php` (inyección de `DocumentoStorageService`, uso de `upload()` en `storeOperacion()`)
- `app/Http/Controllers/OperacionController.php` (inyección de `DocumentoStorageService`, uso de `upload()` en `storetrafico()`)
**Solución Aplicada:**
1. Se reemplazaron las opciones del selector en `handleFiles()` y `renderFileList()` del JS del modal de "Nueva Operación" por el catálogo completo del Art. 36-A, organizado en `<optgroup>` por categoría (Maestros, Transaccionales, Otros). Se cambió el valor por defecto de `'otros'` a `'factura'`.
2. Se actualizó `renderFileList()`: después de inyectar el HTML con las opciones `<select>` compartidas vía variable `tipoOpts`, se establece el valor seleccionado programáticamente usando `list.querySelectorAll('select')` para reflejar `item.type` en cada archivo.
3. Se refactorizó `DocumentadorController::storeOperacion()`: se eliminó `$archivo->store('documentos', 'r2')` y se reemplazó por `$this->storageService->upload()`, persistiendo `url_archivo`, `peso`, `extension`, `tenant_id` y `tipo_documento` con las claves correctas del catálogo.
4. Se refactorizó `OperacionController::storetrafico()`: mismo patrón — se inyectó `DocumentoStorageService`, se reemplazó `$archivo->store()` por `$this->storageService->upload()`, y se persisten todos los metadatos. Se agregó receipt de `tipos_archivos[$index]` para tipos individuales.
5. Se eliminó la etiqueta `<script>` residual que no ejecutaba lógica en el template de `renderFileList()`.
**Estado:** Cerrado

---

### INC-014: Cancelación de Operaciones, Filtros de Búsqueda y KPI de Canceladas
**Fecha:** 2026-05-06
**Descripción:** El sistema no permitía cancelar operaciones. Cuando una operación no iba a llegar (ej. accidente de camión), el documentador no tenía forma de marcarla como cancelada con un motivo, lo que afectaba las métricas operativas. Tampoco existían filtros de búsqueda ni se mostraba la fecha de cruce estimada en el dashboard.
**Solución Propuesta:**
1. Agregar campos `motivo_cancelacion`, `fecha_cancelacion` y `usuario_cancelacion_id` a la tabla `operaciones` vía migración.
2. Crear endpoint `POST /documentador/cancelar/{id}` con validación de motivo obligatorio y opción de eliminar documentos.
3. Modificar `liveData()` para incluir operaciones canceladas, excluir canceladas de métricas normales, y agregar KPI de canceladas.
4. Actualizar `index()` para excluir canceladas de estadísticas del día y agregar conteo de canceladas.
5. En el dashboard (JS): mostrar filas canceladas en rojo tenue, deshabilitar botón DODA/Pedimento, agregar botón de cancelación, mostrar motivo y fecha de cruce.
6. Agregar panel de filtros (cliente, referencia, aduana, bodega, rango de fechas).
7. Agregar gráfica y contador de canceladas en el Monitor de Modulación.
**Archivos Modificados:**
- `database/migrations/2026_05_06_092817_add_cancelacion_fields_to_operaciones_table.php` (nuevo)
- `app/Models/Operacion.php` (nuevos campos fillable, casts, relación `usuarioCancelacion`)
- `app/Http/Controllers/DocumentadorController.php` (método `cancelarOperacion`, filtros en `liveData`, KPI canceladas en `index`)
- `routes/web.php` (ruta `cancelarOperacion`)
- `resources/views/documentador/dashboard.blade.php` (modal cancelar, filtros, fila cancelada, fecha_cruce, KPI canceladas)
**Solución Aplicada:**
1. **Migración:** Se creó la migración adding `motivo_cancelacion` (string nullable), `fecha_cancelacion` (timestamp nullable), `usuario_cancelacion_id` (foreign key nullable a users) a la tabla `operaciones`.
2. **Modelo:** Se agregaron los 3 campos a `$fillable` y `$casts`, y la relación `usuarioCancelacion()` al modelo `Operacion`.
3. **Endpoint:** Se creó `cancelarOperacion()` que valida `motivo_cancelacion` (required), establece `estado = 'cancelada'`, `fecha_cancelacion`, `usuario_cancelacion_id`, y opcionalmente elimina documentos de R2 o local si se solicita. Devuelve JSON para peticiones AJAX.
4. **liveData():** Se incluyó `'cancelada'` en `whereIn` de estados. Se agregaron parámetros de filtro (`cliente_id`, `referencia`, `aduana_id`, `bodega_id`, `fecha_desde`, `fecha_hasta`, `estado_filtro`). Se计算 `canceladasCount` excluyendo canceladas de verdes/rojas. Se agregaron campos `fecha_cruce` y `motivo_cancelacion` al response.
5. **index():** Se excluyeron canceladas de `$operacionesHoy` (efectividad, completados). Se agregó `$canceladasHoy` como KPI separado. Se agregó conteo de `canceladas_semana` al ranking semanal. Se pasó `canceladas_hoy` al array `$stats`.
6. **Frontend - Modal Cancelar:** Se creó modal `#cancelOpModal` con campo de motivo obligatorio, checkbox para eliminar documentos, y confirmación AJAX al endpoint `cancelarOperacion`.
7. **Frontend - Fila Cancelada:** Las operaciones canceladas se muestran con `bg-red-50/60 opacity-75`, referencia tachada (`line-through text-red-400`), badge rojo "CANCELADA", motivo truncado, botón DODA deshabilitado, y botón para ver motivo de cancelación.
8. **Frontend - Filtros:** Se agregó panel colapsable con filtros de cliente, referencia, aduana, bodega y rango de fechas que invoca `liveData` con query parameters.
9. **Frontend - KPI Canceladas:** Se agregó contador gris `#count_canceladas` en el Monitor de Modulación y se incluyó en el dataset del gráfico doughnut (color `#9ca3af`).
10. **Frontend - Fecha Cruce:** Se muestra `fecha_cruce` debajo del cliente en cada fila de la tabla.
**Estado:** Cerrado

---

### INC-015: Filtros de Búsqueda Global y Comportamiento LiveData
**Fecha:** 2026-05-06
**Descripción:** Los filtros actuales del dashboard del documentador dependen únicamente de desplegables (selects) y no ofrecen un campo de búsqueda abierta por texto. El usuario requiere poder buscar libremente por palabras clave que coincidan con clientes, bodegas, referencias, pedimentos, códigos alpha, thermos, etc., sin que la búsqueda se restrinja por fechas (es decir, la búsqueda por texto debe ignorar filtros de fecha y buscar en todo el histórico del tenant). Adicionalmente, se detecta un bug de UX crítico: al aplicar filtros manualmente, el intervalo de `liveData` (refresco automático cada segundos) sobrescribe/restablece los filtros aplicados por el usuario, haciendo imposible mantener una vista filtrada de forma estable.
**Solución Propuesta:**
1. Implementar un input de búsqueda global tipo `search` en el panel de filtros del dashboard (`/documentador/dashboard`).
2. Modificar el backend `DocumentadorController::liveData()` para soportar un parámetro `q` (query string) que ejecute búsquedas tipo `LIKE` en las relaciones `cliente.nombre`, `bodega.nombre`, `referencia`, `num_pedimento`, `codigo_alpha`, `num_thermo`, etc.
3. La búsqueda global debe ignorar filtros de fecha: no debe restringir por `fecha_cruce_estimada` ni por rangos de fecha cuando se envía el parámetro `q`.
4. Implementar un mecanismo en el frontend para que, cuando existan filtros activos (incluyendo búsqueda por texto), el auto-refresh de `liveData` se pause o respete los parámetros de filtro actuales en lugar de recargar el dataset base sin filtros.
5. Asegurar que todos los scopes de búsqueda apliquen `where('tenant_id', ...)` para mantener el aislamiento multi-tenant.
**Archivos Modificados:**
- `app/Http/Controllers/DocumentadorController.php` (método `liveData`)
- `resources/views/documentador/dashboard.blade.php` (panel de filtros y lógica JS de auto-refresh)
**Solución Aplicada:**
1. **Backend (`DocumentadorController::liveData`):**
   - Se modificó el query base para que, si NO hay parámetro `q`, aplique el filtro de fecha por defecto (`fecha_cruce_estimada >= hoy`). Si existe `q`, **omite completamente** la restricción de fecha, permitiendo buscar en todo el histórico del tenant.
   - Se agregó scope de aislamiento multi-tenant explícito: `->where('tenant_id', $user->tenant_id)`.
   - Se implementó bloque de búsqueda global con `where(function ($sq) { ... })` que ejecuta `LIKE` en: `referencia`, `num_factura`, `nombre_producto`, `codigo_alpha`, `num_thermo`, `num_doda`, y en las relaciones `cliente.nombre`, `bodega.nombre`, `aduana.nombre`, `importador.nombre` y `expediente.numero_pedimento`.
   - Los filtros individuales (cliente, referencia, aduana, bodega, fecha, estado) se mantienen y pueden combinarse con `q`.
2. **Frontend (`dashboard.blade.php`):**
   - Se agregó un input de tipo `search` (`#filter_q`) en el panel de filtros con placeholder descriptivo y nota informativa indicando que ignora fechas.
   - Se creó función `getFilterParams()` que lee todos los filtros activos (incluyendo `q`) y devuelve un objeto `URLSearchParams`.
   - Se refactorizó `fetchLiveData()` para que **siempre** invoque `getFilterParams()` y envíe los filtros en cada petición (tanto en carga inicial como en polling automático cada 6 segundos). Esto garantiza que el auto-refresh nunca sobrescriba/restablezca los filtros del usuario.
   - Se simplificó `applyFilters()` para que solo llame `fetchLiveData()`.
   - Se actualizó `clearFilters()` para limpiar también el campo `#filter_q`.
**Archivos Modificados:**
- `app/Http/Controllers/DocumentadorController.php`
 - `resources/views/documentador/dashboard.blade.php`
**Estado:** Cerrado

---

### INC-016: Exclusión de Operaciones Canceladas en Reportes y Dashboards
**Fecha:** 2026-05-06
**Descripción:** Todas las vistas de reportes y dashboards estaban contabilizando operaciones con estado `cancelada` en sus métricas, conteos, gráficas y rankings. Esto distorsionaba los KPIs operativos, financieros y de gerencia. El usuario reportó que si tenía 4 operaciones del día y 1 estaba cancelada, el reporte debería mostrar únicamente 3 operaciones activas.
**Solución Propuesta:**
1. Identificar todos los controladores que generan reportes, métricas o dashboards (`ReporteController`, `ReporteClienteMailController`, `DashboardController`, `OperacionController`, `FinanzasController`).
2. Agregar `->where('estado', '!=', 'cancelada')` (o `->where('operaciones.estado', '!=', 'cancelada')` en joins) a todas las queries de `Operacion` que se usan para fines de reporteo, conteo, agrupación, gráficas y estadísticas.
3. Aplicar el filtro tanto a queries directas (`Operacion::where(...)`) como a relaciones anidadas (`with(['operaciones' => function ($q) { ... }])`) y queries raw (`DB::table('operaciones')`).
4. Mantener intactos los métodos CRUD que necesitan acceso a operaciones canceladas por ID (`findOrFail`, `update`, `cancelarOperacion`).
**Archivos Modificados:**
- `app/Http/Controllers/ReporteController.php`
- `app/Http/Controllers/ReporteClienteMailController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/OperacionController.php`
- `app/Http/Controllers/FinanzasController.php`
**Solución Aplicada:**
1. **ReporteController:** Se agregó `->where('estado', '!=', 'cancelada')` a todas las queries de `Operacion::where('tenant_id', ...)` vía reemplazo masivo seguro con `perl`. Se corrigieron manualmente las queries restantes que usaban `Operacion::where('operaciones.tenant_id', ...)`, relaciones `Cliente::with(['operaciones' => ...])`, `whereHas('operaciones', ...)`, y joins con `aduanas`.
2. **ReporteClienteMailController:** Se aplicó el mismo patrón masivo a queries con `tenant_id`, y manualmente a queries con `operaciones.tenant_id` y `Operacion::withoutGlobalScope('tenant')`.
3. **DashboardController:** Se aplicó reemplazo masivo con script Python a queries de `Operacion::where('tenant_id', $tenantId)`, `Operacion::where($filtroCliente)`, `Operacion::where('cliente_id', ...)`, `Operacion::select(...)`, `Operacion::join(...)`, y `DB::table('operaciones')`.
4. **OperacionController:** Se agregó el filtro a estadísticas del `index` (`$statsHoy`, `$topRegistradores`, `$topCerradores`), a todos los métodos de dashboard de tráfico (`dashboardTrafico`, `dashboardTraficoAjax`, `modalModulacion`, `printModulacion`, `modalConceptos`), a métodos del bot (`check_FUNCIONALPortal`, `checkModulacion`, `modulaciones`), y a queries de ranking del documentador (`getDocumentadorStats`, `getDocumentadorRanking`).
5. **FinanzasController:** Se agregó `->where('estado', '!=', 'cancelada')` dentro de la relación `operaciones` en el `with` de `indexNew`, y a las queries de `detalleClientePatente` y `detalleExpediente`.
**Estado:** Cerrado

---

### INC-017: Auditoría y Corrección de Aislamiento Multi-Tenant en Todos los Reportes
**Fecha:** 2026-05-06
**Descripción:** Se realizó una auditoría exhaustiva de aislamiento multi-tenant en todos los reportes del sistema tras detectar una inconsistencia en el reporte de cliente: al seleccionar un cliente y un rango de fechas, el reporte mostraba operaciones pero el calendario mensual aparecía vacío, generando sospecha de filtración de datos entre tenants. La auditoría reveló los siguientes hallazgos:

1. **CRÍTICO — `ReporteClienteMailController::obtenerDatosReporte()` (líneas 1179-1296):** De 10 queries totales, solo la primera usaba correctamente `withoutGlobalScope('tenant')->where('tenant_id', $tenantId)`. Las otras 9 queries usaban `auth()->user()->tenant_id` que es **null** cuando el método es invocado desde `verReportePublico()` (ruta pública `/reporte/{token}` sin autenticación). Esto provocaba que el reporte público fallara o mostrara datos incorrectos.

2. **CRÍTICO — `ReporteClienteService::generar()` y `generar_old()`:** Ninguna query incluía filtro `tenant_id`. Dependían exclusivamente del BelongsToTenant global scope, el cual no se aplica en contextos sin autenticación (queue workers, CLI). El servicio es utilizado por `EnviarReporteClienteJob` y `preview_OLD2()`, creando riesgo de fuga de datos entre tenants en procesamiento asíncrono.

3. **ALTO — `ReporteController::reporteAduanas()` (4 queries de Expediente):** Las queries al modelo `Expediente` en las líneas 2790, 2822 y 2878 no incluían `tenant_id` explícito. Aunque `Expediente` tiene el trait `BelongsToTenant` y estas rutas requieren autenticación, la omisión de `tenant_id` explícito representa fragilidad en caso de cambios futuros.

4. **ALTO — `ReporteController::reporteGerencia()` (2 queries de Expediente):** Misma situación — línea 1878 y 2198 sin `tenant_id` explícito en queries de `Expediente`.

5. **MEDIO — `ReporteController::reporteCliente()` — Calendario desincronizado:** El parámetro `mes_calendario` del calendario mensual usaba `now()->format('Y-m')` como valor por defecto, independiente del rango `desde`/`hasta` del reporte. Si el usuario seleccionaba fechas de un mes distinto al actual, el calendario aparecía vacío aunque hubiera datos en el rango.

6. **MEDIO — `ReporteController::reporteCliente()` — Total incorrecto en la vista:** La card de "Total Operaciones" mostraba `$greens + $reds` en lugar de `$total`, excluyendo operaciones con otros estados de modulación (como null, "EN PROCESO", etc.).

7. **MEDIO — `ReporteController::operacionesPorSemanas2()` y `expsem()`:** Usaban `Cliente::with(['operaciones' => ...])` sin `tenant_id` explícito, dependiendo completamente del BelongsToTenant global scope.

**Solución Propuesta:**
1. **obtenerDatosReporte():** Reemplazar `auth()->user()->tenant_id` por `$tenantId` (obtenido del cliente) en las 9 queries faltantes, usando `withoutGlobalScope('tenant')` de manera consistente.
2. **ReporteClienteService:** Obtener `$tenantId` del cliente (`$cliente->tenant_id`) y agregar `withoutGlobalScope('tenant')->where('tenant_id', $tenantId)` a todas las queries en `generar()` y `generar_old()`.
3. **reporteAduanas():** Agregar `->where('tenant_id', auth()->user()->tenant_id)` explícito a todas las queries de `Expediente`.
4. **reporteGerencia():** Agregar `->where('tenant_id', auth()->user()->tenant_id)` explícito a las 2 queries de `Expediente`.
5. **reporteCliente() calendario:** Cambiar el valor por defecto de `mes_calendario` de `now()->format('Y-m')` a `Carbon::parse($hasta)->format('Y-m')` para sincronizar con el rango seleccionado.
6. **reporteCliente() vista:** Cambiar `$greens + $reds` por `$total` en la card de operaciones totales.
7. **operacionesPorSemanas2() y expsem():** Agregar `->where('tenant_id', $tenantId)` explícito a las queries de `Cliente` y dentro de los closures de `with()` y `whereHas()`.

**Archivos Modificados:**
- `app/Http/Controllers/ReporteClienteMailController.php` (obtenerDatosReporte)
- `app/Services/ReporteClienteService.php` (generar, generar_old)
- `app/Http/Controllers/ReporteController.php` (reporteCliente, reporteAduanas, reporteGerencia, operacionesPorSemanas2, expsem)
- `resources/views/reportes/reporte-cliente.blade.php` (total card)

**Solución Aplicada:**
1. **obtenerDatosReporte() — 9 queries corregidas:** Todas las queries (`greens`, `reds`, `totalSobrepesos`, `porAduana`, `verdesPorAduana`, `rojosPorAduana`, `historial`, `tramitesPorImportador`, `rawPorDia`, `rawCalendario`) ahora usan `withoutGlobalScope('tenant')->where('tenant_id', $tenantId)`, idéntico a la primera query. Esto garantiza que tanto la vista previa autenticada como el reporte público funcionen correctamente.
2. **ReporteClienteService — 15 queries corregidas:** `generar()` (10 queries) y `generar_old()` (5 queries) ahora obtienen `$tenantId = $cliente->tenant_id` y agregan `withoutGlobalScope('tenant')->where('tenant_id', $tenantId)` a cada query, eliminando la dependencia del global scope y protegiendo contra fugas en contexto de queue worker.
3. **reporteAduanas() — 3 queries de Expediente corregidas:** `$queryPedimentos` (línea 2790), `$qPed` en loop (línea 2822), y `$compPedimentosPorMes` (línea 2878) ahora incluyen `->where('tenant_id', auth()->user()->tenant_id)`.
4. **reporteGerencia() — 2 queries de Expediente corregidas:** Las queries en secciones "9" (línea 1878) y "18" (línea 2198) ahora incluyen `->where('tenant_id', auth()->user()->tenant_id)`.
5. **Calendario en reporteCliente():** `$mesCalendario` ahora usa `Carbon::parse($hasta)->format('Y-m')` como valor por defecto, alineándose con el rango de fechas del reporte. Esto resuelve el problema original reportado: el calendario ahora muestra el mes correspondiente al periodo consultado.
6. **Vista reporte-cliente:** La card "Total Operaciones" ahora muestra `$total` (todas las operaciones no canceladas) en lugar de `$greens + $reds` (solo las que tienen modulación específica), reflejando el conteo real.
7. **operacionesPorSemanas2() y expsem():** Agregado `->where('tenant_id', $tenantId)` a `Cliente` y dentro de los closures de `with(['operaciones' => ...])` y `whereHas('operaciones', ...)`, garantizando aislamiento explícito.

**Verificación:** El modelo `Operacion` y el modelo `Expediente` tienen el trait `BelongsToTenant` que aplica un global scope filtrando por `tenant_id` en contexto autenticado. Todas las queries en `ReporteController` (29 métodos) ya tenían `->where('tenant_id', auth()->user()->tenant_id)` explícito. Las correcciones aplicadas refuerzan la defensa en profundidad para los casos donde el global scope podría no aplicarse (rutas públicas, queue workers, CLI).
**Estado:** Cerrado

---

### INC-018: Reparación de Vistas de Clientes (Bootstrap a Tailwind + Campos Correctos)
**Fecha:** 2026-05-06
**Descripción:** Las vistas de clientes (`show.blade.php`, `create.blade.php`, `edit.blade.php`) estaban rotas por dos problemas simultáneos:
1. **Estilos rotos:** Usaban clases CSS de Bootstrap (`container`, `row`, `card`, `bg-primary`, `form-label`, `btn-danger`, etc.) cuando Bootstrap está deshabilitado en el layout principal (`layouts/app.blade.php` carga `bootstrap.min.css` con atributo `disabled`). Las vistas se renderizaban sin ningún estilo aplicado.
2. **Nombres de campo incorrectos:** Las vistas referenciaban propiedades que no existen en el modelo `Cliente`:
   - `nombre_empresa` → el campo real es `nombre`
   - `correo_contacto_principal` → el campo real es `correo`
   - `telefono_contacto` → el campo real es `telefono`
   - `direccion_fiscal` → el campo real es `direccion`
   - `persona_contacto` → no existe en el modelo
   Esto provocaba que los formularios enviaran nombres de campo que el controlador no reconocía en la validación, haciendo imposible crear o editar clientes desde estas vistas.
3. **Vista de expedientes afectada:** `expedientes/show.blade.php` también referenciaba `$expediente->cliente->nombre_empresa` en lugar de `$expediente->cliente->nombre`.
4. **Alertas en Bootstrap:** El partial `partials/alerts.blade.php` usaba clases Bootstrap para alertas.

**Solución Aplicada:**
1. Se reescribieron `show.blade.php`, `create.blade.php` y `edit.blade.php` usando exclusivamente Tailwind CSS siguiendo el sistema de diseño NexaCore (bordes `rounded-3xl`, sombras `shadow-sm`, tipografía `font-black`, paleta `emerald`).
2. Se corrigieron todos los nombres de campo en formularios y visualizaciones:
   - `nombre_empresa` → `nombre`
   - `correo_contacto_principal` → `correo`
   - `telefono_contacto` → `telefono`
   - `direccion_fiscal` → `direccion`
   - Se eliminó `persona_contacto` (no existe en el modelo).
3. Se corrigió `expedientes/show.blade.php:68` → `$cliente->nombre_empresa` por `$cliente->nombre`.
4. Se corrigió `clientes/index.blade.php:86` eliminando referencia a `persona_contacto`, reemplazándola por `direccion`.
5. Se actualizó `partials/alerts.blade.php` de Bootstrap a Tailwind (diseño consistente con el resto del sistema).

**Archivos Modificados:**
- `resources/views/clientes/show.blade.php`
- `resources/views/clientes/create.blade.php`
- `resources/views/clientes/edit.blade.php`
- `resources/views/clientes/index.blade.php`
- `resources/views/expedientes/show.blade.php`
- `resources/views/partials/alerts.blade.php`

**Estado:** Cerrado

---

### INC-019: Reestructuración de Documentos Maestros Art. 36-A a Nivel Cliente
**Fecha:** 2026-05-06
**Descripción:** Actualmente, la sección **"1. Expediente Maestro (Cliente)"** del checklist de cumplimiento del Art. 36-A de la Ley Aduanera se valida a nivel de **Expediente**, solicitando 5 documentos permanentes del cliente (Acta Constitutiva, Poder Notarial, Identificación Oficial, Constancia de Situación Fiscal y Comprobante de Domicilio). Esta arquitectura es incorrecta por las siguientes razones:

1. **Redundancia operativa:** Un expediente pertenece a un cliente y a una patente aduanal. El mismo cliente tendrá múltiples expedientes (uno por cada pedimento). Con la arquitectura actual, cada nuevo expediente exige volver a subir los mismos 5 documentos del cliente para aprobar el checklist, lo cual es ineficiente y poco práctico.
2. **Principio de realidad aduanera:** En la práctica, un pedimento debe **referenciar** los documentos del cliente que despacha, no duplicarlos. Los documentos del cliente (acta constitutiva, poder notarial, identificación, CSF, comprobante de domicilio) son inherentes al cliente, no a cada operación individual.
3. **Riesgo de desactualización:** Si un documento del cliente caduca (ej. la CSF vence mensualmente), los expedientes existentes no reflejan esta caducidad porque los documentos están anclados en el expediente, no en el cliente.
4. **Ineficiencia en la gestión documental:** Se obliga a los documentadores a re-subir los mismos archivos una y otra vez para cada nuevo pedimento del mismo cliente, generando duplicación de almacenamiento en R2 y trabajo administrativo innecesario.

**Caso especial — Constancia de Situación Fiscal (CSF):** La CSF debe validarse **una vez al mes** (al inicio de cada mes) para verificar el nivel de cumplimiento fiscal del cliente antes de facturarle. Si la CSF vence o muestra irregularidades (opinión negativa del SAT), el sistema debe alertar como **red flag** e impedir o advertir sobre la facturación hasta que se actualice. Esta validación periódica solo es factible si la CSF está almacenada a nivel cliente y tiene metadata de vigencia.

**Solución Propuesta:**
1. **Migración de base de datos:** Crear una tabla o relación que permita asociar documentos directamente al modelo `Cliente`. Alternativamente, agregar una relación polimórfica a `Documento` (`documentable_type` / `documentable_id`) o crear campos específicos en la tabla `cliente` para almacenar las rutas R2 de cada documento maestro.
2. **Vista de gestión documental en el perfil del cliente:** Agregar una sección de "Documentación Legal (Art. 36-A)" en la vista de edición/ajustes del cliente (`clientes/{id}/edit`) y en el detalle (`clientes/{id}`) donde se puedan:
   - Subir/actualizar cada uno de los 5 documentos maestros.
   - Visualizar el estado actual de cada documento (cargado/pendiente).
   - Ver la fecha de vigencia (especialmente importante para la CSF).
   - Recibir alertas cuando un documento esté por vencer o requiera actualización.
3. **Refactorización del checklist Art. 36-A en expedientes:** Modificar la lógica en `ExpedienteController::updateChecklist()` y el modelo `Expediente` para que la sección "1. Expediente Maestro (Cliente)":
   - **Consulte los documentos del cliente asociado** en lugar de buscarlos en el propio expediente.
   - Si el cliente tiene el documento registrado → **check verde automático** (cumplido).
   - Si el cliente NO tiene el documento → **red flag** que indique "Falta en perfil del cliente", con un enlace directo para subirlo en la ficha del cliente (no en el expediente).
   - El botón de toggle/override manual debe mantenerse para casos excepcionales.
4. **Validación automática de CSF:** Implementar un job programado (CRON) que **el primer día de cada mes** verifique la fecha de vigencia de la CSF de cada cliente activo. Si la CSF está por vencer o vencida:
   - Marcar al cliente con un flag `csf_alerta = true`.
   - Mostrar una alerta en el dashboard del documentador y en el perfil del cliente.
   - Opcionalmente, bloquear la creación de nuevos expedientes o la facturación hasta que se actualice la CSF.
5. **Limpieza de documentos huérfanos:** Una vez migrados los documentos maestros a nivel cliente, eliminar de R2 los documentos de tipo `acta`, `poder`, `identificacion`, `rfc` y `domicilio` que estén asociados a expedientes (para evitar almacenamiento duplicado y confusión). Mantener un script de migración única para los documentos existentes.

**Archivos a Modificar:**
- `database/migrations/` — Nueva migración para storage de documentos a nivel cliente
- `app/Models/Cliente.php` — Agregar relación con documentos, campos de metadata de vigencia
- `app/Models/Expediente.php` — Refactorizar `isDocComplete()` y `cumplimiento_completo` para consumir docs del cliente
- `app/Http/Controllers/ClienteController.php` — Agregar métodos de subida/gestión de documentos
- `app/Http/Controllers/ExpedienteController.php` — Actualizar `updateChecklist()` para referenciar docs del cliente
- `app/Services/DocumentoStorageService.php` — Soporte para storage a nivel cliente (path: `tenant_{id}/cliente_{id}/{tipo_doc}/...`)
- `resources/views/clientes/edit.blade.php` — Sección de "Documentación Legal Art. 36-A"
- `resources/views/clientes/show.blade.php` — Indicador visual de estado de documentos del cliente
- `resources/views/expedientes/show.blade.php` — Actualizar checklist modal para referenciar docs del cliente
- `app/Jobs/ValidarCSFClientes.php` — (Nuevo) Job programado para validación mensual de CSF

**Solución Aplicada:**
1. **Migración `2026_05_06_100000_add_cliente_id_to_documentos_table.php`:** Se agregó la columna `cliente_id` (FK a `cliente`) y `fecha_vencimiento` (date nullable) a la tabla `documentos`. Se creó un índice compuesto `[cliente_id, tipo_documento]` para búsquedas rápidas.
2. **Modelo `Documento`:** Se agregó `cliente_id` y `fecha_vencimiento` al `$fillable`, con cast `date` para `fecha_vencimiento`. Se agregó la relación `cliente()` (belongsTo) y el scope `scopeDeCliente()`.
3. **Modelo `Cliente`:** Se agregó la relación `documentos()` (hasMany por `cliente_id`) y el accessor helper `documentosMaestros()` que filtra documentos sin `pedimento_id` (documentos propios del cliente, no de expedientes).
4. **`DocumentoStorageService`:** Se agregó el parámetro opcional `$clienteId` al método `upload()`. La ruta ahora soporta `tenant_{id}/cliente_{id}/{tipo_doc}/...` para documentos de cliente, manteniendo compatibilidad con el path de operaciones existente.
5. **`ClienteController`:**
   - Inyecta `DocumentoStorageService` y `SistemaNotificacionesService`.
   - `show()` ahora carga `documentosMaestros` eager-loaded y pasa `Expediente::MAESTRO_DOCS` a la vista.
   - `subirDocumento()`: Valida `tipo_documento` contra el catálogo Art. 36-A, elimina el documento previo del mismo tipo si existe (reemplazo), sube a R2 con path de cliente, calcula `fecha_vencimiento` automática para CSF (día 5 del mes siguiente).
   - `eliminarDocumento()`: Elimina de R2 y BD, con verificación de pertenencia al cliente, soporte JSON para AJAX.
6. **Rutas `web.php`:**
   - `POST /clientes/{cliente}/documentos` → `clientes.subirDocumento`
   - `DELETE /clientes/{cliente}/documentos/{documento}` → `clientes.eliminarDocumento`
   - Las rutas de preview/download se reutilizan del `DocumentoController` existente (`documentos.preview`, `documentos.download`).
7. **Vista `clientes/show.blade.php`:** Nueva sección "Documentación Legal" con:
   - Barra de progreso (% de documentos cargados).
   - Listado de los 5 documentos con icono de estado (check verde, alerta roja si CSF vencida, gris si no cargado).
   - Para CSF: contador de días restantes con badge de colores (verde vigente, ámbar por vencer ≤5 días, rojo vencida).
   - Acciones por documento: vista previa, descargar, eliminar, subir (reemplazo automático).
   - Círculo de progreso SVG en sidebar.
   - Mensaje de advertencia si faltan documentos.
8. **Comando `clientes:verificar-csf` (`app/Console/Commands/VerificarCSFClientes.php`):**
   - Itera todos los clientes con sus documentos tipo `rfc`.
   - Crea `NotificacionSistema` de tipo `csf_faltante` (error) si no hay CSF.
   - Crea `NotificacionSistema` de tipo `csf_vencida` (error) si la fecha venció, una vez por mes.
   - Crea `NotificacionSistema` de tipo `csf_por_vencer` (warning) si quedan ≤5 días, una vez por mes.
   - Deduplica alertas: no repite notificaciones del mismo tipo para el mismo cliente en el mismo mes.
9. **Schedule en `routes/console.php`:** `Schedule::command('clientes:verificar-csf')->dailyAt('07:00')` para ejecución diaria a las 7 AM.

**Configuración de CRON requerida en el servidor:**
```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```
**Estado:** Cerrado

---

### INC-020: Auditoría y Corrección del Sistema de Notificaciones (Enlaces Rotos y Bugs)
**Fecha:** 2026-05-06
**Descripción:** Se realizó una auditoría exhaustiva del sistema de notificaciones (emails, notificaciones in-app, notificaciones de sistema) para verificar que todos los enlaces/URLs incrustados en las notificaciones dirijan al usuario a páginas válidas. Se identificaron los siguientes bugs:

1. **CRÍTICO — Vista de notificaciones inexistente (`notificaciones/index.blade.php`):** El método `NotificacionController::index()` retorna `view('notificaciones.index', ...)` pero el archivo `resources/views/notificaciones/index.blade.php` no existía (el directorio ni siquiera estaba creado). Navegar a `/notificaciones` producía un error 500.

2. **ALTO — URL incorrecta para marcar como leída en el bell antiguo:** En `layouts/app.blade.php:730`, la función JS `marcarComoLeida()` enviaba `fetch(/notificaciones/${id}/leer)` pero la ruta real registrada en `web.php:566` es `POST /notificaciones/{id}/marcar-leida`. Esto causaba que el botón de marcar como leída en el bell de notificaciones (para roles Trafico/Documentador) fallara silenciosamente.

3. **ALTO — Clase duplicada en archivos Mail:** `EstatusModulacionMail.php` y `EstatusModulacionMail_nomegusto.php` declaraban la misma clase `App\Mail\EstatusModulacionMail`, causando colisión de nombres. Solo una de las dos implementaciones se cargaba efectivamente, potencialmente usando la incorrecta.

4. **MEDIO — URL muerta `#` en notificaciones de sistema:** `SistemaNotificacionesService::verificarLimitesBot()` usaba `'#'` como `accion_url` en las 4 notificaciones de límite del SOIA-Bot (80%, 90%, 100% manual, 100% automático). El botón "Actualizar Plan" / "Actualizar Plan Ahora" en el dropdown de notificaciones no navegaba a ninguna parte.

5. **MEDIO — Archivo/Clase con nombre inconsistente:** El archivo `ExportacionStatusMail.php` declaraba la clase `OperacionStatusMail`, rompiendo la convención PSR-4. El autoloader de Composer podría no encontrar la clase en ciertas condiciones.

6. **MEDIO — `auth()->user()` sin guard en `NotificacionService`:** Los métodos `notificarAlphaPendiente()`, `notificarAlphaActualizado()`, `crearNotificacionGlobal_old()` y `crearNotificacionGlobal()` llamaban a `auth()->user()` / `auth()->id()` sin verificar `auth()->check()`. Si se invocaran desde un contexto sin autenticación (cron, queue worker), causarían un error fatal.

**Solución Propuesta:**
1. Crear `resources/views/notificaciones/index.blade.php` con diseño Tailwind consistente con NexaCore Design Language.
2. Cambiar `/notificaciones/${id}/leer` → `/notificaciones/${id}/marcar-leida` en `app.blade.php:730`.
3. Renombrar la clase en `EstatusModulacionMail_nomegusto.php` a `EstatusModulacionMailNomegusto` para eliminar la colisión.
4. Reemplazar `'#'` por `route('admin.config')` para que el botón dirija a la página de configuración del tenant.
5. Renombrar `ExportacionStatusMail.php` → `OperacionStatusMail.php`.
6. Agregar `auth()->check() ? auth()->id() : null` en los 4 métodos afectados de `NotificacionService`.

**Archivos Modificados:**
- `resources/views/notificaciones/index.blade.php` (nuevo)
- `resources/views/layouts/app.blade.php`
- `app/Mail/EstatusModulacionMail_nomegusto.php`
- `app/Mail/OperacionStatusMail.php` (renombrado desde ExportacionStatusMail.php)
- `app/Services/SistemaNotificacionesService.php`
- `app/Services/NotificacionService.php`
- `app/Http/Controllers/NotificacionController.php` (corrección enlace trafico → documentador)
- `app/Http/Controllers/DocumentadorController.php` (soporte ?op= en liveData)
- `resources/views/documentador/dashboard.blade.php` (auto-apertura modal desde ?op=)

**Solución Aplicada:**
1. **Vista creada:** `notificaciones/index.blade.php` con diseño NexaCore (Tarjetas con borde `rounded-2xl`, tipografía `font-black`, badges de estado leída/no leída, paginación, estado vacío con ícono de campana).
2. **URL JS corregida:** `app.blade.php:730` ahora envía `fetch(/notificaciones/${notificacionId}/marcar-leida, ...)`, coincidiendo con la ruta registrada. El bell nuevo (línea 920) ya usaba la URL correcta y no fue modificado.
3. **Clase renombrada:** `EstatusModulacionMail_nomegusto.php` ahora declara `class EstatusModulacionMailNomegusto`, eliminando la colisión con `EstatusModulacionMail.php`. El archivo no es referenciado por ningún import, así que no hay impacto en otras partes del código.
4. **URLs de notificaciones de sistema corregidas:** Las 4 notificaciones de límite del bot ahora usan `route('admin.config')` como `accion_url`, dirigiendo al administrador a la página de configuración (`/admin/config`).
5. **Archivo renombrado:** `ExportacionStatusMail.php` → `OperacionStatusMail.php`. La clase `App\Mail\OperacionStatusMail` ahora se encuentra en el archivo correcto según PSR-4.
6. **Guards de auth agregados:** Los 4 métodos en `NotificacionService` ahora usan `auth()->check() ? auth()->id() : null` para `created_by`/`creador_id`, y `auth()->check() ? auth()->user() : null` para `$creador`, con fallback `'Sistema'` en el nombre.
7. **Corrección de enlace en notificaciones (2026-05-06):** La solución inicial usaba `route('trafico.operaciones.show')` en los enlaces de notificaciones, lo cual es incorrecto porque:
   - El módulo de Tráfico (`/trafico`) es legacy y no es el flujo principal del sistema.
   - La documentación del proyecto establece que "no existe un flujo separado de Tráfico" y que la gestión es centralizada en `/documentador/dashboard`.
   - Se reemplazaron todas las ocurrencias en `NotificacionController::noLeidas()` y `notificaciones/index.blade.php` por `route('documentador.dashboard', ['op' => $operacion_id])`.
   - Se modificó `DocumentadorController::liveData()` para incluir la operación por ID cuando se recibe `?op=`, ignorando el filtro de fecha por defecto que solo muestra operaciones de hoy en adelante.
   - Se agregó lógica JS en `dashboard.blade.php` para auto-abrir el modal de detalle de la operación cuando `?op=` está presente en la URL.

**Verificación:** Todos los archivos PHP modificados pasan `php -l` sin errores de sintaxis. Las rutas de notificaciones ahora apuntan a `documentador.dashboard?op={id}` que dirige al panel centralizado de operaciones. El dashboard detecta el parámetro `?op=` en la URL y abre automáticamente el modal de detalle de la operación correspondiente, incluso si es de días anteriores (se agregó `orWhere('id', $opId)` en `liveData()` para omitir el filtro de fecha).
**Estado:** Cerrado

---

### INC-021: Migración Masiva de Vistas Bootstrap a Tailwind (Catálogos y Módulos Cliente)
**Fecha:** 2026-05-06
**Descripción:** Una auditoría reveló que 89 archivos Blade contenían clases CSS de Bootstrap, a pesar de que el layout principal carga Bootstrap 5.1.3 con el atributo `disabled` y Tailwind como framework activo. El enlace "Gerencia" en la navbar de admin apuntaba a `reportes.gerencia` en lugar del panel de administración.

**Vistas convertidas (28 archivos):** `layouts/cliente.blade.php`, `home.blade.php`, `dashboard.blade.php`, `aduanas/{create,index,show}`, `bodegas/{create,edit,show}`, `importadores/{create,edit,show}`, `patentes/{create,edit,show}`, `usuarios/{index,create,edit}`, `expedientes/{showclient,indexcliente,create,edit}`, `layouts/app.blade.php` (navbar).

**Solución Aplicada:** Conversión completa Bootstrap→Tailwind siguiendo el sistema NexaCore: tarjetas `rounded-3xl`, formularios `rounded-xl`, botones `bg-indigo-600`, alertas con `border-l-4`, modales con `fixed inset-0 z-50`, reemplazo de `bootstrap.Modal/Collapse/Tooltip` por JS vanilla.
**Estado:** Cerrado

---

### INC-022: Pendiente de Limpieza Bootstrap en Reportes, Finanzas y Dashboards Legacy
**Fecha:** 2026-05-06
**Descripción:** Tras INC-021, quedan ~60 vistas con Bootstrap en módulos de Finanzas, Reportes y dashboards legacy. No bloquean la operación diaria pero deben convertirse para consistencia visual completa.

**Módulos pendientes:** Finanzas (7 vistas), Reportes (7 vistas), Cliente dashboards (4 vistas), Admin dashboards (4 vistas), Documentador (2 vistas), Expedientes legacy (3 vistas), Varios (8 vistas).

**Estado:** Pendiente

---

### INC-035: Reporte de Pedimentos

**Fecha:** 2026-05-28
**Descripcion:** Se agrega un nuevo card en la seccion de reportes (`/reportes`) llamado "Reporte de Pedimentos" que permite visualizar, filtrar y exportar todos los pedimentos trabajados por el tenant. Incluye KPIs en tiempo real (Total, Cumplidos, Pendientes, Docs Faltantes), filtros por rango de fechas, numero de pedimento, cliente, estado y categoria, tabla de datos con tooltips de documentos faltantes, modal de detalle con checklist y link al expediente completo, y exportacion a PDF de la tabla completa.

**Solucion Propuesta:**
1. Registrar el reporte `pedimentos` en `Tenant::getAllAvailableReports()` con icono `fa-file-invoice`, color `blue`, status `active`.
2. Agregar rutas `GET /reportes/pedimentos` (main view), `GET /reportes/pedimentos/pdf` (PDF) con middleware `report.access:pedimentos`.
3. Agregar ruta API `GET /expedientes/{expediente}/documentos-pendientes` para el modal de detalle.
4. Agregar entrada `'pedimentos' => 'reportes.pedimentos'` al `$routeMap` de la vista indice de reportes.
5. Crear metodo `reportePedimentos()` en `ReporteController` con paginacion (15/pp), filtros condicionales y calculo de KPIs. Los KPIs de `cumplidos` y `pendientes` NO aplican el filtro `estado` del request para evitar contradicciones logicas.
6. Crear metodo `reportePedimentosPdf()` en `ReporteController` que genera PDF via DomPDF con KPIs y tabla completa.
7. Crear metodo `documentosPendientes()` en `ExpedienteController` que retorna JSON con los documentos pendientes (con validacion de tenant).
8. Crear vista `reporte-pedimentos.blade.php` con KPIs, panel de filtros colapsable, tabla de datos, modal de detalle con fetch AJAX, y paginacion.
9. Crear vista `pdf-pedimentos.blade.php` con DejaVu Sans, KPIs en header, tabla de pedimentos con badges de estado y docs faltantes.

**Archivos Modificados:**
- `app/Models/Tenant.php` — Agregado `pedimentos` en `getAllAvailableReports()`
- `routes/web.php` — Agregadas 3 rutas
- `resources/views/reportes/index.blade.php` — Agregado a `$routeMap`
- `app/Http/Controllers/ReporteController.php` — Agregados `reportePedimentos()` y `reportePedimentosPdf()`
- `app/Http/Controllers/ExpedienteController.php` — Agregado `documentosPendientes()`

**Archivos Creados:**
- `resources/views/reportes/reporte-pedimentos.blade.php` — Vista principal (420 lineas)
- `resources/views/reportes/pdf-pedimentos.blade.php` — Template PDF (113 lineas)

**Notas:**
- Para que el card aparezca en `/reportes`, el super-admin debe habilitar el reporte `pedimentos` en las capacidades del tenant desde `/nexacore-admin/tenants/{tenant}/capabilities`.
- El KPI `docsFaltantes` cuenta todos los registros coincidentes (no solo la pagina actual).
- La validacion de cumplimiento usa los accesores `cumplimiento_completo` y `documentos_pendientes` del modelo `Expediente`.

**Estado:** Cerrado

---

### INC-036: Migración de Gráficos PDF de QuickChart a SVG Inline — Eliminación de Dependencia Externa

**Fecha:** 2026-05-30
**Descripción:** La generación de reportes PDF del cliente (`/reportes/cliente/pdf`) y el envío por correo utilizaban QuickChart.io (API externa) para generar gráficos como imágenes PNG base64 embebidas en el PDF. Esto provocaba:

1. **Latencia:** Cada gráfico requería una llamada HTTP a QuickChart.io (2-8 segundos por chart), con un timeout de 10 segundos. Un reporte con 7 gráficos podía tardar 14-56 segundos adicionales solo en generar imágenes.
2. **Fallas silenciosas:** Si la API estaba caída, el timeout se agotaba, o el JSON de configuración excedía 8KB (límite hardcoded), el gráfico simplemente no aparecía en el PDF sin informar al usuario.
3. **Dependencia externa:** 100 tenants podrían generar reportes concurrentemente, saturando el servicio gratuito de QuickChart y degradando el rendimiento del VPS.
4. **Solapamiento en DomPDF:** Las imágenes PNG base64 embebidas causaban problemas de renderizado (contenido solapado) debido a las limitaciones de DomPDF con CSS y posicionamiento de imágenes.
5. **Calidad:** Los PNG base64 son rasterizados — al imprimir o hacer zoom se pixelan, a diferencia del formato vectorial.

**Solución Propuesta:**
1. Crear un servicio PHP puro (`SvgChartService`) que genere gráficos como SVG inline directamente en el servidor, sin llamadas HTTP externas.
2. Implementar 4 tipos de gráficos SVG: doughnut (dona), line (líneas con área), bar (barras apiladas/agrupadas), horizontalBar (barras horizontales).
3. Reemplazar el método `generarChartUrlsPdfCompleto()` en `ReporteController` y `generarChartUrls()` en `ReporteClienteMailController` para usar el nuevo servicio.
4. Actualizar la vista `pdf-reporte.blade.php` para renderizar SVG inline con `{!! !!}` en lugar de `<img src="{{ }}">`.
5. Agregar CSS `page-break-inside: avoid` a secciones y gráficos para evitar solapamiento en DomPDF.

**Archivos Creados:**
- `app/Services/SvgChartService.php` — Servicio con 4 métodos de generación de gráficos SVG: `doughnut()`, `lineChart()`, `barChart()`, `horizontalBar()`. Incluye helpers privados para path arcs de dona, escalas de ejes, leyendas, y manejo de datos vacíos. Sin dependencias externas.

**Archivos Modificados:**
- `app/Http/Controllers/ReporteController.php` — Agregado `use App\Services\SvgChartService`. Reemplazado `generarChartUrlsPdfCompleto()` por `generarChartsSvg()` que instancia `SvgChartService` y genera 7 gráficos SVG inline (greensReds doughnut, aduanas doughnut, historico lineChart, tendencia lineChart dual, patentes stacked barChart, importadores horizontalBar, bodegas doughnut). Eliminada dependencia de `file_get_contents` y QuickChart.io.
- `app/Http/Controllers/ReporteClienteMailController.php` — Agregado `use App\Services\SvgChartService`. Agregado método `generarChartSvgs()` con la misma lógica de mapeo de datos que `ReporteController::generarChartsSvg()`. El método `generarPDF()` ahora usa `generarChartSvgs()` en lugar de `generarChartUrls()`.
- `resources/views/reportes/pdf-reporte.blade.php` — Cambio de `<img src="{{ $charts['key'] }}" width="...">` a `{!! $charts['key'] !!}` para renderizar SVG inline. Agregado CSS `.section { page-break-inside: avoid; }` y `.chart-wrap { page-break-inside: avoid; }` para evitar solapamiento. Cada sección de reporte envuelta en `<div class="section">`.

**Impacto en Rendimiento (VPS con 100 tenants):**

| Métrica | Antes (QuickChart) | Después (SVG Inline) |
|---------|--------------------|-----------------------|
| Latencia por gráfico | 2-8 seg (HTTP externo) | ~0.001 seg (PHP puro) |
| Latencia total reporte | 14-56 seg (7 charts) | <0.01 seg (7 charts) |
| RAM adicional por PDF | 0 (pero HTTP blocking) | 0 |
| Dependencia externa | QuickChart.io | Ninguna |
| Calidad de impresión | PNG rasterizado (pixela) | SVG vectorial (nítido) |
| Límite de datos | 8KB JSON por chart | Sin límite |
| Concurrency segura | No (rate limiting) | Sí (cálculo local) |

**Detalle de SvgChartService:**
- `doughnut(array $slices, int $width, int $height, ?string $centerLabel)`: Genera gráfico de dona con arcos SVG (path arcs), leyenda lateral con porcentajes, y soporte para anillo completo (full circle split technique). Convierte el SVG a PNG retina (2x) vía Imagick para compatibilidad con DomPDF.
- `lineChart(array $datasets, array $labels, int $width, int $height)`: Gráfico de líneas con área rellena, puntos de datos, ejes con escala automática (`niceStep`/`niceMax`), leyenda opcional para datasets múltiples, y soporte para valores null. Convierte a PNG retina vía Imagick.
- `barChart(array $series, array $labels, int $width, int $height)`: Barras agrupadas o apiladas (propiedad `stacked`), etiquetas rotadas a -40°, leyenda de colores. Convierte a PNG retina vía Imagick.
- `horizontalBar(array $bars, int $width, int $height)`: Barras horizontales con etiquetas truncadas, valores numéricos al final de cada barra, máximo 8 barras. Convierte a PNG retina vía Imagick.
- Todos los métodos sanitizan texto con `htmlspecialchars()` para prevenir XSS en SVG.
- El helper `lighten()` genera colores de relleno para áreas de gráficos de líneas.
- **Conversión SVG→PNG vía Imagick:** El constructor recibe `$asImage=true` por defecto. Cuando está activo, cada gráfico se genera como SVG internamente y se convierte a PNG retina (2x escala, filtro LANCZOS) usando la extensión PHP Imagick. El resultado es un data URI `data:image/png;base64,...` compatible con DomPDF. Si Imagick falla, hace fallback al SVG inline original.

**Nota sobre DomPDF:** DomPDF v3.1.2 no renderiza SVGs inline correctamente. Los SVGs se ignoran en el PDF final sin error. Por esto, `SvgChartService` convierte cada gráfico a PNG embebido (base64) antes de passarlo a la vista Blade, que lo muestra como `<img src="data:image/png;base64,...">`.

**Nota:** Los métodos `generarChartUrls()` y `quickChart()` se mantienen en `ReporteClienteMailController` por compatibilidad con el envío masivo de reportes por correo (código legacy), pero los nuevos métodos `generarChartsPng()` se usan exclusivamente para la generación de PDF.

**Estado:** Cerrado

---

### INC-037: Gráficos en PDF No Renderizan — Migración de QuickChart a SVG a PNG Nativo (GD)

**Fecha:** 2026-05-30
**Descripción:** Tras la migración de INC-036 (QuickChart → SVG inline), los gráficos no aparecían en el PDF generado por DomPDF. Investigación sistemática reveló:

1. **DomPDF v3.1.2 no soporta SVG inline.** Los SVGs embebidos con `{!! !!}` son completamente ignorados por DomPDF — no se renderizan ni muestran error. No existe soporte nativo para inline SVG en esta versión.
2. **Solución intermedia (INC-036):** Se intentó convertir SVGs a PNG vía Imagick (`SvgChartService::render()`), pero Imagick renderiza incorrectamente los `<path>` con arcos (doughnut muestra un solo círculo de color) y los `<polygon>`/`<polyline>` (barras aparecen como líneas delgadas). Esto se debe a que el motor SVG de Imagick tiene soporte limitado para elementos SVG complejos.
3. **Solución final:** Crear `PngChartService` que genera gráficos directamente como imágenes PNG usando la extensión PHP **GD** (disponible y confiable en el servidor), sin pasar por SVG como formato intermedio. GD tiene funciones nativas para dibujar arcos (`imagefilledarc`), líneas (`imageline`), rectángulos (`imagefilledrectangle`), polígonos (`imagefilledpolygon`) y texto (`imagettftext`), lo que produce resultados pixel-perfect.

**Archivos Creados:**
- `app/Services/PngChartService.php` — Servicio que genera gráficos PNG directamente con GD. Métodos:
  - `doughnut()` — Doughnut/pie usando `imagefilledarc()` con agujero central (`imagefilledellipse()` blanco). Leyenda lateral con porcentajes.
  - `lineChart()` — Gráfico de líneas con área rellena, puntos de datos, ejes, leyenda para datasets múltiples.
  - `barChart()` — Barras agrupadas o apiladas con `imagefilledrectangle()`, etiquetas, ejes, leyenda de colores.
  - `horizontalBar()` — Barras horizontales con valores al final de cada barra.
  - Manejo de fuentes TTF con fallback a Helvetica (macOS) → DejaVuSans (Linux) → `imagestring()` nativo de GD si no hay fuentes.
  - Todos los métodos retornan `data:image/png;base64,...` compatible con `<img src="">` en DomPDF.

**Archivos Modificados:**
- `app/Http/Controllers/ReporteController.php` — Reemplazado `SvgChartService` por `PngChartService`. Método `generarChartsSvg()` renombrado a `generarChartsPng()`.
- `app/Http/Controllers/ReporteClienteMailController.php` — Reemplazado `SvgChartService` por `PngChartService`. Método `generarChartSvgs()` renombrado a `generarChartsPng()`.
- `resources/views/reportes/pdf-reporte.blade.php` — Sin cambios adicionales (ya usaba `<img src="{{ $charts['key'] }}">` del INC-036).

**Archivos Obsoletos (no eliminados, conservados como referencia):**
- `app/Services/SvgChartService.php` — Ya no es utilizado. Reemplazado por `PngChartService`.

**Comparativa de Enfoques:**

| Enfoque | QuickChart (original) | SVG inline (INC-036) | SVG→PNG vía Imagick | PNG directo GD (INC-037) |
|---------|----------------------|---------------------|-------------------|--------------------------|
| Latencia | 2-8 seg por chart | ~0 ms | ~50 ms por chart | ~5 ms por chart |
| Dependencia | API externa | Ninguna | Extensión Imagick | Extensión GD |
| Calidad PDF | PNG rasterizado | No renderiza en DomPDF | Doughnut roto, barras delgadas | Correcto, nítido |
| Escala 100 tenants | Rate-limited, timeouts | N/A | Funciona pero mal | Funciona perfecto |
| RAM por request | ~0 | ~0 | ~30-50 MB (Imagick) | ~2-5 MB |

**Estado:** Cerrado

### INC-038: Mejora de Calidad de Gráficos PNG — Escala 2x, Leyenda Debajo, Sin Truncado

**Fecha:** 2026-05-30
**Severidad:** Media
**Módulo:** PngChartService, pdf-reporte.blade.php, ReporteController, ReporteClienteMailController

**Problema:**
Los gráficos generados por PngChartService (INC-037) presentaban:
- Imágenes borrosas en PDF (escala 1x, resolución insuficiente)
- Texto de leyendas truncado en doughnut charts (leyenda a la derecha no cabía)
- Labels de barras y ejes cortados
- Exceso de espacio en blanco entre secciones del PDF

**Solución implementada:**

1. **Escala 2x en PngChartService — coordenadas físicas directas:**
   - `SCALE = 2`: imágenes se crean a `2× displayW/displayH` pixeles reales
   - Función helper `px()` escala coordenadas lógicas a físicas
   - `drawText()` recibe tamaño en pt (lógica), convierte internamente a píxeles físicos
   - Se eliminó `imagesetresolution()` — DomPDF no lo necesita, usa `width` attribute del `<img>`

2. **Doughnut: leyenda ABAJO en vez de a la derecha:**
   - Layout dinámico: `displayH` se divide entre área del pie y área de leyenda
   - `pieAreaH = displayH - legendBlockH - legendGap`
   - Cada item de leyenda: rectángulo de color + texto completo (sin truncado)
   - Esto elimina completamente el problema de texto cortado en leyendas

3. **Tamaños de fuente aumentados (pt lógicos):**
   - TITLE: 14pt, LABEL: 10pt, VALUE: 10pt, LEGEND: 10pt, AXIS: 9pt

4. **Dimensiones de chart aumentadas (displayW × displayH):**
   - greensReds: 240×170 → 300×260 (doughnut con leyenda abajo)
   - aduanas: 260×180 → 340×320 (doughnut con leyenda abajo)
   - historico: 440×170 → 520×230
   - tendencia: 440×170 → 520×230
   - patentes: 380×190 → 460×240
   - importadores: 320×190 → 420×240
   - bodegas: 220×170 → 320×280 (doughnut con leyenda abajo)

5. **Truncación de texto eliminada o ampliada:**
   - doughnut: sin truncado (leyenda abajo tiene espacio completo)
   - barChart labels: 10→14 caracteres
   - horizontalBar labels: 16→22 caracteres
   - Aumentado left pad en horizontalBar: 100→120px lógicos

6. **Template pdf-reporte.blade.php:**
   - `width` attributes actualizados a nuevas dimensiones display
   - `h2.pb` removido de secciones menores
   - Márgenes reducidos

**Archivos modificados:**
- `app/Services/PngChartService.php` — reescritura completa: escala 2x con px(), doughnut con leyenda abajo
- `app/Http/Controllers/ReporteController.php` — dimensiones de chart actualizadas
- `app/Http/Controllers/ReporteClienteMailController.php` — dimensiones de chart actualizadas
- `resources/views/reportes/pdf-reporte.blade.php` — widths, page breaks, margins

**Resultado:** Gráficos nítidos 2x, texto completo sin truncado, leyendas legibles, menos espacio en blanco.

**Estado:** Cerrado

---

### INC-039: Bot DODA se Detiene al Encontrar Errores "DODA NO COINCIDE" — No Consulta Operaciones Restantes

**Fecha:** 2026-05-30
**Severidad:** Alta
**Módulo:** DodaConsultaService (Bot SOIA/PECEM)

**Problema:**
El bot de consulta de modulación detenía la ejecución completa cuando las primeras operaciones de un tenant fallaban con "ERROR DODA NO COINCIDE". En el escenario reportado, un tenant tenía 5 operaciones del día con DODA registrado, pero las primeras 2 fallaron con error de coincidencia. Al hacer clic en "Consultar Modulación" nuevamente, el bot no procesaba las 3 operaciones restantes.

**Causa Raíz:**
Se identificaron múltiples problemas en `DodaConsultaService.php`:

1. **`obtenerOperacionesPendientes()` (línea 351) — `take()` prematuro:** El método limitaba las operaciones obtenidas con `$opsTenant->take($consultasRestantes)` basado en los créditos restantes del tenant. Si el tenant tenía 2 créditos restantes, solo obtenía 2 operaciones. Si esas 2 fallaban con "DODA NO COINCIDE" (que NO consumen crédito), las 3 restantes nunca se intentaban.

2. **Sin try/catch por operación individual:** En `procesarRespuestaDoda()`, el `foreach` que procesa cada operación asociada a un DODA no tenía protección. Si una operación lanzaba una excepción no capturada, rompía el flujo de las operaciones restantes del mismo DODA.

3. **Sin try/catch en callback `fulfilled` del Guzzle Pool:** Si `procesarRespuestaDoda()` lanzaba una excepción que escapaba el try/catch interno, el generador de Guzzle se detenía y las consultas restantes no se ejecutaban.

4. **Variable `$urlBase` sobrescrita en `prepararConsultas()`:** La variable se declaraba fuera del loop y se sobrescribía en cada iteración con la config del tenant actual, afectando la URL de DODAs subsecuentes de diferentes tenants.

**Solución Aplicada:**

1. **Eliminado `take()` en `obtenerOperacionesPendientes()`:** Ahora obtiene TODAS las operaciones pendientes del tenant sin limitar por créditos restantes. El límite de créditos se respeta durante el procesamiento en tiempo real. Se almacena `creditosUsadosOriginal[$tenant->id]` al inicio de la ejecución para el cálculo.

2. **Check de créditos en `procesarOperacion()` (línea 622):** Antes de contar una consulta exitosa contra el límite del tenant, se verifica `($creditosOriginales + $consultasProcesadas) >= $limiteTenant`. Si el tenant ya alcanzó su límite durante la ejecución, la operación se salta sin consumir crédito. Las operaciones con error de validación ("DODA NO COINCIDE") siguen sin consumir crédito.

3. **try/catch por operación individual en `procesarRespuestaDoda()`:** Cada llamada a `procesarOperacion()` dentro del `foreach` está envuelta en try/catch. Si una operación falla, las demás continúan procesándose.

4. **try/catch en callback `fulfilled` del Pool:** El callback completo está protegido para que excepciones no capturadas no detengan el generador de Guzzle.

5. **Fix de `$urlBase` en `prepararConsultas()`:** Renombrado a `$urlBaseDefault` y usado como fallback en `env('PECEM_API_URL', $urlBaseDefault)`. La variable `$urlBase` ahora se declara dentro del bloque `if (!isset($consultas[$doda]))` para cada DODA nuevo.

**Archivos Modificados:**
- `app/Services/DodaConsultaService.php` — Propiedad `$creditosUsadosOriginal`, `obtenerOperacionesPendientes()`, `prepararConsultas()`, `ejecutarConsultasConcurrentes()`, `procesarRespuestaDoda()`, `procesarOperacion()`

**Estado:** Cerrado

---

### INC-040: Botón "Reenviar" WhatsApp No Valida Límite de Mensajes + Doble Incremento del Contador

**Fecha:** 2026-05-30
**Severidad:** Alta
**Módulo:** WhatsAppController, NotificacionWhatsAppService, whatsapp.blade.php

**Problema:**
El sistema de notificaciones WhatsApp tenía un límite de 3 mensajes configurado para un tenant. Cuando el límite se alcanzaba, las notificaciones se encolaban correctamente como pendientes (no se enviaban). Sin embargo, al ir a Configuraciones → WhatsApp y hacer clic en "Reenviar" en un mensaje pendiente, el sistema:

1. **No validaba el límite disponible:** Enviaba el mensaje sin verificar si el tenant aún tenía créditos de WhatsApp, permitiendo enviar mensajes ilimitados vía reintentos.
2. **Doble incremento del contador:** `reenviarWhatsapp()` llamaba `$tenant->incrementarConsumoWhatsapp()` incondicionalmente (línea 707), pero `NotificacionWhatsAppService::notificar()` también lo llamaba internamente al tener éxito (línea 159). Esto incrementaba el contador el doble por cada reintento exitoso.
3. **Sin retroalimentación visual de límite excedido:** No existía modal ni notificación informando al usuario que había alcanzado su límite. El usuario solo descubría el problema al navegar a Configuraciones → WhatsApp.

**Solución Aplicada:**

1. **Validación de límite antes de reenviar (`WhatsAppController::reenviarPendiente()`):** Se agregó check de `$tenant->canSendWhatsapp()` antes de llamar `reenviarWhatsapp()`. Si el límite está alcanzado, retorna JSON con `limit_exceeded: true`, HTTP 403, mensaje informativo y datos de uso/limite. El pendiente NO se elimina de la cola para poder reintentarse cuando haya créditos disponibles (mes siguiente o ampliación de plan).

2. **Eliminado doble incremento (`WhatsAppController::reenviarWhatsapp()`):** Se eliminó la línea `$tenant->incrementarConsumoWhatsapp()` que estaba fuera del servicio. El incremento ahora ocurre exclusivamente dentro de `NotificacionWhatsAppService::notificar()` cuando el envío al webhook de n8n es exitoso (línea 159).

3. **Modal de límite excedido (`whatsapp.blade.php`):** Se creó un modal `#limiteWhatsappModal` con icono de WhatsApp, contador de uso actual (X/Y), y enlace a `contacto@nexacore.com.mx` para ampliar el límite. Diseño consistente con NexaCore Design Language (rounded-2xl, backdrop-blur, paleta indigo/red).

4. **JS `reenviarPendiente()` actualizado:** Detecta `data.limit_exceeded` en la respuesta JSON y muestra el modal de límite en lugar de un simple `alert()`. Incluye manejo de errores con `.catch()`.

**Archivos Modificados:**
- `app/Http/Controllers/Admin/WhatsAppController.php` — `reenviarPendiente()` (validación de límite), `reenviarWhatsapp()` (eliminado doble incremento)
- `resources/views/admin/config/whatsapp.blade.php` — Modal `#limiteWhatsappModal`, funciones `mostrarLimiteModal()`, `cerrarLimiteModal()`, actualización de `reenviarPendiente()`

**Estado:** Cerrado

---

### INC-041: Capturar Fecha Real de Activación del PECEM y Actualizar fecha_cruce_estimada

**Fecha:** 2026-05-31
**Severidad:** Alta
**Módulo:** DodaConsultaService (Bot SOIA/PECEM)

**Problema:**
El scraper del PECEM no estaba capturando la fecha y hora exacta en que se presentó la documentación en el módulo de selección automatizado. Esta información aparece en la sección "Datos Generales Consultados" del HTML del PECEM con el formato:
```
Activación del Mecanismo de Selección Automatizado
27-05-2026 15:44:11 OPER:521-855403
***DESADUANAMIENTO LIBRE***
```

La fecha real de activación es crítica para:
1. Actualizar `fecha_cruce_estimada` con la fecha real del cruce
2. Calcular métricas de tiempo entre estimación y realidad
3. Auditoría y trazabilidad de operaciones

**Solución Aplicada:**

1. **Nuevos campos en `extraerDatosCompletos()`:** Se agregaron `fecha_activacion` y `operador_sat` al array de datos extraídos. Regex utilizado:
   ```php
   /(\d{2}-\d{2}-\d{4}\s+\d{2}:\d{2}:\d{2})\s+OPER:([A-Z0-9\-]+)/i
   ```

2. **Actualización de `fecha_cruce_estimada` en `procesarOperacion()`:** Cuando se detecta una modulación definitiva (`esEstatusDefinitivo()`) y hay `fecha_activacion` disponible:
   - Parsea la fecha del formato `d-m-Y H:i:s` a Carbon
   - Actualiza `fecha_cruce_estimada` con la fecha real del PECEM
   - Registra log informativo con fecha anterior, fecha real y operador SAT

3. **Inclusión en `bot_logs_json`:** Cada entrada de log ahora incluye:
   - `fecha_activacion_peceem`: Fecha/hora de activación del mecanismo
   - `operador_sat`: Identificador del operador SAT que procesó la operación

4. **Manejo de errores:** Si el parseo de fecha falla, se registra warning con el valor raw y el error, pero no interrumpe el flujo.

**Archivos Modificados:**
- `app/Services/DodaConsultaService.php` — `extraerDatosCompletos()` (nuevos campos), `procesarOperacion()` (actualización de fecha_cruce_estimada y bot_logs_json)

**Prueba con DODA 144889110:**
- ✓ fecha_activacion: "27-05-2026 15:44:11"
- ✓ operador_sat: "521-855403"
- ✓ fecha parseada: "2026-05-27 15:44:11"

**Estado:** Cerrado

---

### INC-042: Containerización Docker Completa para Despliegue en VPS con Dockploy

**Fecha:** 2026-06-01
**Severidad:** Alta
**Módulo:** Infraestructura / DevOps

**Problema:**
El proyecto no contaba con configuración Docker ni archivos de despliegue containerizado. Esto impedía una instalación reproducible y automatizada en un VPS, requiriendo configuración manual de PHP-FPM, Nginx, MySQL, extensiones PHP, cron, y dependencias del sistema en cada despliegue.

**Solución Aplicada:**

1. **Dockerfile multi-servicio (contenedor único):** Imagen `php:8.4-fpm` con Nginx, supervisor y cron integrados. Incluye:
   - Extensiones PHP: `pdo_mysql`, `gd` (freetype/jpeg/webp), `imagick`, `redis`, `zip`, `intl`, `bcmath`, `soap`, `pcntl`, `opcache`
   - Nginx 1.27 como servidor web directo
   - Supervisor para gestionar PHP-FPM + Nginx + cron en un solo contenedor
   - Cron para `php artisan schedule:run` (tareas programadas de Laravel)
   - `composer install --no-dev --optimize-autoloader` durante el build

2. **docker-compose.yml (2 servicios):**
   - **app**: Contenedor principal (PHP-FPM + Nginx + cron + supervisor) con todas las variables de entorno via `${VAR}` interpolation
   - **mysql**: MySQL 8.4 con healthcheck, volumen persistente para datos y configuración `utf8mb4`
   - Red interna `nexacore` bridge
   - Volumen `storage_data` para archivos persistentes de Laravel (uploads, logs)

3. **Configuraciones Docker:**
   - `docker/nginx/default.conf`: Nginx con `try_files` para SPA de Laravel, `fastcgi_pass 127.0.0.1:9000` (interno al contenedor), `client_max_body_size 128M`
   - `docker/php/php.ini`: `upload_max 128M`, `memory_limit 512M`, `opcache` activado con 256MB
   - `docker/mysql/my.cnf`: `utf8mb4_unicode_ci`, `innodb_buffer_pool_size 256M`
   - `docker/supervisor/supervisord.conf`: 3 programas (php-fpm, nginx, cron) con `autorestart=true`
   - `.dockerignore`: Excluye `.env*`, `node_modules`, `vendor`, `*.md`, `*.py`, `*.bak`, `*.pdf`

4. **Template `.env.production`:** Archivo con todas las variables requeridas para producción (R2, PECEM, Evolution API, SMTP, etc.) listo para ser llenado con valores reales e ingresado en la UI de dockploy.

**Beneficios:**
- Un solo `docker compose up -d` levanta todo el stack
- Build reproducible: misma imagen en local y VPS
- Sin dependencias del sistema operativo anfitrión
- Supervisor mantiene los 3 procesos vivos dentro del contenedor
- Volumen de BD persistente entre redeploys
- Compatible con dockploy: solo requiere las variables `${VAR}` en la UI

**Archivos Creados:**
- `Dockerfile`
- `docker-compose.yml`
- `.dockerignore`
- `.env.production`
- `docker/nginx/default.conf`
- `docker/php/php.ini`
- `docker/mysql/my.cnf`
- `docker/supervisor/supervisord.conf`

**Estado:** Cerrado

---

### INC-043: Corrección de Build Docker — Eliminación de Dependencia Imagick Innecesaria

**Fecha:** 2026-06-01
**Severidad:** Media
**Módulo:** Infraestructura / Docker

**Problema:**
El `Dockerfile` incluía la instalación de la extensión `imagick` vía PECL, la cual requiere las librerías de desarrollo de ImageMagick (`libmagickwand-dev`) instaladas en el sistema. Al no estar presentes, el build fallaba con:
```
configure: error: not found. Please provide a path to MagickWand-config
ERROR: /tmp/pear/temp/imagick/configure failed
```

**Causa Raíz:**
La extensión `imagick` se incluyó en el Dockerfile porque estaba presente en el entorno de desarrollo local (macOS), pero el proyecto **no utiliza Imagick en producción**. El servicio `SvgChartService` (que usaba Imagick para convertir SVG a PNG) es código muerto — fue reemplazado por `PngChartService` (INC-037) que usa exclusivamente la extensión nativa **GD** de PHP para generar gráficos.

**Solución Aplicada:**
Se eliminó `imagick` del `pecl install` y del `docker-php-ext-enable` en el Dockerfile. Se mantienen únicamente las extensiones necesarias: `redis` e `igbinary`.

**Archivos Modificados:**
- `Dockerfile`

**Estado:** Cerrado

---

### INC-044: Conflicto de Puerto 80 en Deploy Docker — Limpieza de Contenedores Previos

**Fecha:** 2026-06-01
**Severidad:** Media
**Módulo:** Infraestructura / Docker

**Problema:**
Al hacer redeploy en dockploy, el nuevo contenedor fallaba con:
```
Bind for :::80 failed: port is already allocated
```
El puerto 80 del host ya estaba ocupado, impidiendo que el nuevo contenedor se levantara.

**Causa Raíz:**
Contenedores Docker de deploys anteriores quedaron corriendo en el VPS, manteniendo el bind del puerto 80. También es posible que `nginx` o `apache2` del sistema operativo anfitrión estuvieran escuchando en el puerto 80.

**Solución Aplicada:**
Procedimiento de limpieza manual desde SSH en el VPS antes del redeploy:

1. Identificar proceso en puerto 80: `sudo lsof -i :80`
2. Detener y eliminar contenedores viejos: `docker stop/rm nexacore_app nexacore_mysql`
3. Si es nginx/apache del host: `sudo systemctl stop nginx/apache2`
4. Limpiar redes Docker huérfanas: `docker network prune -f`
5. Redeploy desde dockploy

**Prevención futura:** En el `docker-compose.yml`, el puerto se mapea como `"80:80"`. Para evitar este conflicto en redeploys, dockploy debería ejecutar `docker compose down` antes de `docker compose up -d`. Si el problema persiste, se puede cambiar a un puerto alternativo (ej. `"8080:80"`) y configurar dockploy para proxy inverso en ese puerto.

**Estado:** Cerrado

---

### INC-045: Eliminación de Mapeo Directo de Puertos — Delegar Ruteo a Traefik de Dockploy

**Fecha:** 2026-06-01
**Severidad:** Media
**Módulo:** Infraestructura / Docker

**Problema:**
El `docker-compose.yml` mapeaba los puertos `80:80` y `443:443` directamente desde el contenedor `app` al host. Esto generaba conflicto con Traefik (`dokploy-traefik`), el proxy inverso nativo de dockploy que ya ocupa los puertos 80 y 443 del VPS para enrutar tráfico a todos los servicios.

**Causa Raíz:**
Dockploy ya incluye Traefik como reverse proxy que escucha en 80/443 y enruta por dominio a cada servicio. Al intentar bindear manualmente `80:80` en el compose, se produce colisión de puertos con el Traefik del sistema.

**Solución Aplicada:**
Se eliminó la sección `ports` del servicio `app` en `docker-compose.yml`. El contenedor ahora solo expone su puerto 80 internamente en la red Docker `nexacore`. Traefik se encarga del resto:

- En la UI de dockploy, en la pestaña **Domains** del servicio, se asigna un dominio (o IP) al servicio `app` en puerto interno `80`
- Traefik genera automáticamente las rutas y certificados SSL sin conflicto de puertos

**Archivos Modificados:**
- `docker-compose.yml`

**Configuración requerida en dockploy:**
- Pestaña Domains → Service: `app`, Port: `80`, Domain: `76.13.127.66` (o dominio propio)

**Estado:** Cerrado

---

### INC-046: Corrección de Red Docker — Migración a Red Overlay dokploy-network

**Fecha:** 2026-06-01
**Severidad:** Alta
**Módulo:** Infraestructura / Docker

**Problema:**
Tras configurar el dominio en dockploy, Traefik retornaba 404 al intentar acceder a la aplicación. La app funcionaba correctamente dentro de su contenedor (probado con `curl localhost/`), pero Traefik no podía enrutar tráfico hacia ella.

**Causa Raíz:**
1. El `docker-compose.yml` usaba una red `bridge` personalizada (`nexacore`) para los servicios `app` y `mysql`
2. Traefik (`dokploy-traefik`) está conectado a la red `dokploy-network` que es de tipo **overlay** (Docker Swarm), no bridge
3. Al estar en redes distintas y de tipos incompatibles, Traefik no podía alcanzar el contenedor `nexacore_app` para enrutar las peticiones HTTP

**Solución Aplicada:**
1. Se eliminó la red `bridge` personalizada `nexacore`
2. Ambos servicios (`app` y `mysql`) se conectaron directamente a la red externa `dokploy-network` (overlay)
3. Se eliminó el mapeo explícito del puerto `3306` de MySQL — ya no es necesario porque `app` se conecta a `mysql` internamente por Docker DNS
4. Se eliminó la sección `ports` del servicio `app` (ya removida en INC-045)

**Archivos Modificados:**
- `docker-compose.yml`

**Estado:** Cerrado

---

### INC-047: Corrección de Seeder InitialTenant — Columna tenant_id Inexistente en reportes_acceso

**Fecha:** 2026-06-01
**Severidad:** Alta
**Módulo:** Base de Datos / Seeders

**Problema:**
Al ejecutar `php artisan db:seed --force`, el seeder `InitialTenantSeeder` fallaba con:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'tenant_id' in 'field list'
SQL: update `reportes_acceso` set `tenant_id` = 1
```
La tabla `reportes_acceso` no tiene columna `tenant_id` (es una tabla de tokens de acceso a reportes vinculada a `cliente_id`, no a `tenant_id`).

**Causa Raíz:**
El seeder `InitialTenantSeeder` iteraba sobre una lista de 14 tablas aplicando `UPDATE SET tenant_id = $tenantId` sin verificar si cada tabla realmente tenía la columna `tenant_id`.

**Solución Aplicada:**
1. Se eliminó `reportes_acceso` de la lista de tablas en el seeder
2. Se agregó validación `hasColumn($tableName, 'tenant_id')` antes de cada `UPDATE`, haciendo el seeder tolerante a tablas sin esa columna
3. Esto previene fallos futuros si otras tablas tampoco tienen `tenant_id`

**Archivos Modificados:**
- `database/seeders/InitialTenantSeeder.php`

**Estado:** Cerrado

---

### INC-048: Corrección de Seeder DatabaseSeeder — User::factory() Requiere Faker en Producción

**Fecha:** 2026-06-01
**Severidad:** Alta
**Módulo:** Base de Datos / Seeders

**Problema:**
Al ejecutar `php artisan migrate:fresh --seed --force` en el contenedor de producción, el seeder fallaba con:
```
Class "Faker\Factory" not found
```
El error ocurría en `DatabaseSeeder` al llamar `User::factory(10)->create()`, que internamente usa Faker para generar datos aleatorios (nombres, emails, etc.).

**Causa Raíz:**
Faker es una dependencia `require-dev` en `composer.json`. El `Dockerfile` ejecuta `composer install --no-dev`, por lo que Faker no está disponible en el entorno de producción. La creación de 10 usuarios dummy con factories no es necesaria para el funcionamiento del sistema.

**Solución Aplicada:**
Se eliminó la línea `User::factory(10)->create()` y la estructura partida de llamadas al seeder. `DatabaseSeeder` ahora ejecuta todos los seeders en una sola secuencia:
1. `InitialAduanaSeeder`
2. `InitialTenantSeeder`
3. `SuperAdminSeeder`
4. `AdminUserSeeder`
5. `ExpedienteSeeder`

**Archivos Modificados:**
- `database/seeders/DatabaseSeeder.php`

**Estado:** Cerrado

---

### INC-049: Alerta de Conexión No Segura en Formularios HTTP

**Fecha:** 2026-06-02
**Severidad:** Alta
**Módulo:** Seguridad / Infraestructura

**Problema:**
Al realizar cualquier acción (iniciar sesión, enviar formularios, etc.), el navegador muestra la alerta: "The information you're about to submit is not secure because this form is being submitted using a connection that's not secure." Esto ocurre porque el sitio se está sirviendo sobre HTTP en lugar de HTTPS, y el navegador advierte que los datos viajan sin cifrado.

**Solución Confirmada:**
- Activar HTTPS en la configuración del dominio `agencias.nexacore.com.mx` en dockploy
- Traefik genera automáticamente certificado SSL con Let's Encrypt
- Configurar redirección forzada HTTP → HTTPS
- El DNS del subdominio debe apuntar al VPS (`76.13.127.66`)

**Solución Aplicada:**
1. **Dockploy — Dominio:** Se activó el checkbox HTTPS en la configuración del dominio. Host: `agencias.nexacore.com.mx`, Container Port: `80`. Traefik gestiona automáticamente el certificado SSL con Let's Encrypt y la redirección HTTP → HTTPS.

2. **Backend — `AppServiceProvider::boot()`:** Se agregó `URL::forceScheme('https')` cuando `APP_ENV=production`. Esto fuerza a Laravel a generar todas las URLs (assets, rutas, redirects) con el esquema `https://`, eliminando recursos mixtos (mixed content) que causaban la alerta de "No seguro" en el navegador.

3. **Environment — `APP_URL`:** Se configuró `APP_URL=https://agencias.nexacore.com.mx` en las variables de entorno de dockploy para que rutas absolutas y links en emails también usen HTTPS.

**Archivos Modificados:**
- `app/Providers/AppServiceProvider.php` — Agregado `URL::forceScheme('https')` en `boot()` para producción
- Docker environment — `APP_URL=https://agencias.nexacore.com.mx`

**Estado:** Cerrado

---

### INC-050: Superadmin No Puede Dar de Baja o Bloquear una Agencia (Tenant)

**Fecha:** 2026-06-02
**Severidad:** Alta
**Módulo:** Superadmin / Gestión de Tenants

**Problema:**
Desde el panel de superadmin no es posible eliminar, dar de baja o bloquear una agencia (tenant). Cuando una agencia deja de ser cliente, se necesita poder desactivarla para que nadie pueda acceder, sin perder los datos históricos.

**Solución Confirmada:**
- Botón "Suspender" en la vista de tenants del superadmin
- Al suspender: `estado = 'suspendido'`, todos los usuarios del tenant no pueden iniciar sesión
- Datos históricos intactos (operaciones, expedientes, documentos)
- Botón "Reactivar" para restaurar acceso (`estado = 'activo'`)
- Middleware de autenticación que verifique `tenant.estado === 'activo'` al hacer login

**Archivos a modificar:**
- `app/Http/Controllers/Admin/TenantController.php` — Métodos `suspend()` y `reactivate()`
- `app/Models/Tenant.php` — Agregar `suspendido` a estados válidos
- `app/Http/Middleware/` — Verificar tenant activo en login
- `resources/views/admin/tenants/` — Botones de suspender/reactivar

**Solución Aplicada:**

1. **`Tenant` Model** — Nuevos métodos:
   - `isActive()`: retorna `true` si `estado === 'activo'`
   - `isSuspended()`: retorna `true` si `estado === 'suspendido'`
   - `suspend()`: cambia estado a `'suspendido'` y guarda
   - `reactivate()`: cambia estado a `'activo'` y guarda

2. **`TenantController::toggleStatus(Tenant)`** — Nuevo método que alterna entre suspender y reactivar según el estado actual del tenant. Retorna mensaje descriptivo.

3. **Ruta** — `PATCH /nexacore-admin/tenants/{tenant}/toggle-status` con nombre `admin.tenants.toggle-status`.

4. **`AuthController::login()`** — Después de verificar que el usuario está activo, se agregó verificación de tenant: si el tenant está suspendido, se cierra sesión con mensaje "Tu agencia ha sido suspendida".

5. **`CheckTenantActive` middleware** — Nuevo middleware registrado en el grupo `web`. En cada petición verifica que el tenant del usuario autenticado no esté suspendido. Los superadmins están exentos (pueden acceder aunque el tenant esté suspendido).

6. **Vista `show.blade.php`** — Botón rojo "Suspender Agencia" (con confirmación) si está activa, o botón verde "Reactivar Agencia" si está suspendida.

**Archivos Modificados:**
- `app/Models/Tenant.php` — `isActive()`, `isSuspended()`, `suspend()`, `reactivate()`
- `app/Http/Controllers/Admin/TenantController.php` — `toggleStatus()`
- `app/Http/Controllers/AuthController.php` — verificación de tenant suspendido en login
- `app/Http/Middleware/CheckTenantActive.php` — nuevo middleware
- `app/Http/Kernel.php` — registro del middleware en grupo `web`
- `routes/web.php` — ruta `tenants.toggle-status`
- `resources/views/admin/tenants/show.blade.php` — botones suspender/reactivar

**Estado:** Cerrado

---

### INC-051: Superadmin No Puede Crear Usuarios para un Tenant Manualmente

**Fecha:** 2026-06-02
**Severidad:** Media
**Módulo:** Superadmin / Gestión de Usuarios

**Problema:**
Desde el panel de superadmin no existe la funcionalidad para crear manualmente un usuario dentro de un tenant específico. Esto obliga a que sea el admin de cada agencia quien cree sus propios usuarios, cuando en muchos casos el superadmin necesita hacerlo directamente (ej. durante el onboarding).

**Solución Confirmada:**
- Formulario "Crear Usuario" en la vista de detalle del tenant (`/nexacore-admin/tenants/{id}`)
- Campos: nombre, email, contraseña (o autogenerada), rol (admin, admin_n2, documentador, etc.)
- El usuario se crea con `tenant_id` del tenant correspondiente
- Enviar email de bienvenida automático con credenciales y link de acceso

**Archivos a modificar:**
- `app/Http/Controllers/Admin/TenantController.php` — Método `createUser()`
- `app/Mail/WelcomeMail.php` — Email de bienvenida
- `resources/views/admin/tenants/show.blade.php` — Sección de creación de usuario
- `routes/web.php` — Ruta POST

**Solución Aplicada:**

1. **`TenantController::createUser(Request, Tenant)`:** Valida nombre, email (único) y rol (`admin`, `admin_n2`, `documentador`). Genera contraseña aleatoria de 12 caracteres con `Str::random(12)`. Crea el usuario con `tenant_id`, `active = true`, `must_change_password = true`. Envía email de bienvenida con credenciales.

2. **`WelcomeMail`:** Mailable con template Markdown que incluye nombre del tenant, email del usuario, contraseña temporal y botón de acceso al login. La contraseña se muestra en un panel destacado con formato monospace.

3. **Vista `show.blade.php`:** Botón "Crear Usuario" que despliega un formulario colapsable con campos: Nombre, Email, Rol (select). Al enviar, se crea el usuario y se envía el email. Texto informativo: "Se generará una contraseña aleatoria y se enviará por email."

4. **Ruta:** `POST /nexacore-admin/tenants/{tenant}/users` con nombre `admin.tenants.users.store`.

5. **Template `emails/welcome.blade.php`:** Email Markdown con panel de credenciales, botón de login y nota sobre cambio de contraseña obligatorio al primer acceso.

**Archivos Modificados:**
- `app/Http/Controllers/Admin/TenantController.php` — imports + `createUser()`
- `app/Mail/WelcomeMail.php` — nuevo
- `resources/views/emails/welcome.blade.php` — nuevo
- `resources/views/admin/tenants/show.blade.php` — formulario colapsable
- `routes/web.php` — ruta POST

**Estado:** Cerrado

---

### INC-052: Sistema de Facturación y Gestión de Pagos por Tenant

**Fecha:** 2026-06-02
**Severidad:** Alta
**Módulo:** Superadmin / Finanzas

**Problema:**
No existe un sistema para gestionar la facturación y pagos de cada tenant. Se necesita:

1. **Configuración de renta por tenant:** Límite de usuarios, renta fija mensual, período de gracia
2. **Gestión de pagos:** Registro de pagos recibidos, estado de cuenta, saldo pendiente
3. **Paquetes predefinidos:** Planes con sus características y precios (Básico, Profesional, Enterprise)
4. **Servicios adicionales:** Cobros extra por características opcionales (más usuarios, WhatsApp, reportes avanzados, etc.)
5. **Control de ventas:** Dashboard con ingresos mensuales, tenants morosos, proyecciones

**Solución Confirmada:**
**Fase 1 — MVP + Automatización:**

1. **Planes predefinidos + personalizables:**
   - Modelo `Plan` con: nombre, precio_mensual, max_usuarios, max_operaciones, features (JSON)
   - Planes base: Básico, Profesional, Enterprise
   - Posibilidad de crear planes personalizados por tenant

2. **Configuración de renta por tenant:**
   - Campos en `Tenant`: `plan_id`, `renta_mensual`, `limite_usuarios`, `periodo_gracia_dias`, `dia_corte`
   - El superadmin asigna plan y puede sobrescribir precio/características

3. **Gestión de pagos:**
   - Modelo `Pago`: tenant_id, monto, fecha_pago, metodo, comprobante, periodo_cubierto (mes/año), notas
   - Registro manual de pagos por el superadmin
   - Estado de cuenta: pagado, pendiente, vencido

4. **Facturación PDF:**
   - Modelo `Factura`: tenant_id, folio, periodo, monto, estado, pdf_path
   - Generación automática de factura PDF al registrar pago
   - Descarga desde el panel de superadmin y desde el panel del tenant

5. **Recordatorios automáticos:**
   - Notificaciones 7, 3 y 1 día antes del vencimiento
   - Email al `correo_admin` del tenant
   - Notificación in-app en el panel del tenant

6. **Corte automático por impago:**
   - Al vencer el período de gracia configurable (por tenant), cambiar `estado = 'suspendido'`
   - Todos los usuarios del tenant bloqueados hasta regularizar pago
   - Job programado (cron) que verifica tenants vencidos diariamente

7. **Dashboard financiero en superadmin:**
   - Ingresos mensuales (gráfico)
   - Tenants al corriente vs morosos
   - Próximos vencimientos (7 días)
   - Total facturado en el mes

**Archivos a crear/modificar:**
- `database/migrations/` — Tablas: `plans`, `pagos`, `facturas`
- `app/Models/Plan.php`, `Pago.php`, `Factura.php`
- `app/Http/Controllers/Admin/PlanController.php`
- `app/Http/Controllers/Admin/PagoController.php`
- `app/Http/Controllers/Admin/FacturaController.php`
- `app/Http/Controllers/Admin/TenantController.php` — Campos de facturación
- `app/Jobs/VerificarTenantsVencidos.php`
- `app/Mail/RecordatorioPago.php`
- `resources/views/admin/finanzas/` — Dashboard, planes, pagos
- `resources/views/admin/tenants/show.blade.php` — Sección facturación
- `routes/web.php`

**Estado:** Pendiente