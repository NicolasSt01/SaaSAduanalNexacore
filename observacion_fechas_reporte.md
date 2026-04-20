# Observación del Sistema de Reportes - NexaCore

## Referencia Temporal para Reporte de Clientes

En el `ReporteController.php`, específicamente dentro del método `reporteCliente` y sus derivados (analítica diaria, calendario mensual, desgloses por aduana), la fecha de referencia principal para la contabilidad de operaciones es la **`fecha_cruce_estimada`**.

### Justificación
Las operaciones en este sistema se cuentan y analizan preferentemente por el **día de cruce**, ya que es la métrica de rendimiento y seguimiento operativa más relevante para el cliente final y la gestión aduanal diaria.

### Uso Alternativo
La columna **`fecha_registro`** se mantiene como una métrica disponible pero separada, reservada para propósitos de auditoría interna, trazabilidad de carga de expedientes y otros análisis administrativos que no correspondan estrictamente al flujo de operaciones de comercio exterior (cruce).

---
*Documento generado por solicitud del usuario el 28 de marzo de 2026.*
