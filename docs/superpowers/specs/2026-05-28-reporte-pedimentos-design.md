# Diseño: Reporte de Pedimentos

**Fecha:** 2026-05-28
**Estado:** Aprobado

## Contexto

El sistema NexaCore (agencia aduanal, Laravel 12) tiene una sección de reportes (`/reportes`) con múltiples reportes existentes. Se necesita un nuevo reporte llamado "Reporte de Pedimentos" que permita ver todos los pedimentos trabajados por el tenant.

## Alcance

- Mostrar todos los pedimentos del tenant (sin filtrar por usuario)
- Filtros por rango de fechas, número de pedimento, cliente, estado y categoría
- KPIs: Total, Cumplidos, Pendientes por Cerrar, Docs Faltantes
- Tabla con datos de cada pedimento y documentos faltantes
- Modal resumido al hacer clic en un pedimento
- Exportación a PDF de la tabla completa con filtros aplicados

## KPIs

| KPI | Cálculo | Color |
|-----|---------|-------|
| Total Pedimentos | COUNT de expedientes filtrados | Blue |
| Cumplidos | Estado = "Cerrado" | Green |
| Pendientes por Cerrar | Estado = "En proceso" o "Abierto" | Amber |
| Docs Faltantes | Pedimentos con checklist_cumplimiento incompleto | Red |

## Filtros

- `desde` / `hasta` → rango de fechas sobre `fecha_apertura`
- `numero_pedimento` → búsqueda parcial
- `cliente_id` → select con clientes del tenant
- `estado` → select: Todos, En proceso, Abierto, Cerrado, Cancelado
- `categoria` → select: Todos, Importación, Exportación, Rectificaciones

## Tabla

Columnas: Pedimento #, Cliente, Categoría, Estado (badge con color), Docs Faltantes (lista), Fecha Apertura, Acciones (botón "Ver detalle")

## Modal de Detalle

- Datos generales: número, cliente, patente, aduana, categoría, estado, fechas
- Checklist de cumplimiento con documentos faltantes resaltados
- Link "Ir a expediente completo" → `/expedientes/{id}`

## PDF

- Vista Blade `pdf-pedimentos.blade.php`
- Imprime tabla completa con filtros aplicados + KPIs en header
- Usa DomPDF (barryvdh/laravel-dompdf) ya integrado en el proyecto

## Archivos a Crear/Modificar

### Crear
1. `resources/views/reportes/reporte-pedimentos.blade.php` — Vista principal
2. `resources/views/reportes/pdf-pedimentos.blade.php` — Vista PDF

### Modificar
1. `app/Models/Tenant.php` — Agregar 'pedimentos' en `getAllAvailableReports()`
2. `routes/web.php` — Agregar ruta `GET /reportes/pedimentos`
3. `resources/views/reportes/index.blade.php` — Agregar entrada en `$routeMap`
4. `app/Http/Controllers/ReporteController.php` — Método `reportePedimentos()`

## Patrones a Seguir

- Vista principal sigue patrón de `reporte-cliente.blade.php` y `reporte_gerencia.blade.php`
- Filtros siguen patrón de `expedientes/index.blade.php`
- KPI cards siguen patrón de `reporte_gerencia.blade.php`
- PDF sigue patrón de `pdf-reporte.blade.php`
- Multi-tenant: todas las queries usan `where('tenant_id', auth()->user()->tenant_id)`
- Layout: `layouts.app`
