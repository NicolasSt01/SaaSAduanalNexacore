# Integración NexaCore Aduanal - PECEM (Consulta SOIA/DODA)

Este documento describe la funcionalidad del bot de consulta automática de modulaciones y captura integral de datos (Scraping) implementado en NexaCore Aduanal.

## 📌 Objetivo del Bot
Realizar una verificación exhaustiva y automatizada de las operaciones ante el SAT (PECEM/SOIA) utilizando el Número de Integración/DODA. El bot no solo actualiza el estado de modulación, sino que extrae toda la información legal y técnica disponible en el portal oficial para garantizar la trazabilidad total del expediente.

---

## 🛠️ Configuración Técnica

### Variables de Entorno (.env)
*   `PECEM_API_URL`: URL base del validador QR del SAT (ej. `https://pecem.mat.sat.gob.mx/app/qr/ce/faces/pages/mobile/validadorqr.jsf?D1=...`).
*   `CHECK_TRAFICO_TOKEN`: Token de seguridad para ejecuciones vía CRON.

### Modelo de Datos (Extendido)
Cada operación mantiene un historial de consultas en formato **JSON** para auditoría, permitiendo ver qué datos se obtuvieron en cada ejecución del bot.

---

## 🔍 Alcance del Scraping (Captura Integral)

El bot realiza un "Full Scraping" del HTML capturando los siguientes bloques de datos:

### 1. Datos de Identificación
*   **Número de Integración**: El identificador único de la consulta.
*   **Datos Generales Consultados**: Fecha/Hora de activación del mecanismo de selección automatizada y estatus visual (Desaduanamiento Libre / Reconocimiento).

### 2. Detalle del Pedimento
*   **Encabezados**: Tipo de Pedimento, Pedimento (Patente-Aduana-Número), Remesas Presentadas.
*   **Cumplimiento**: Número de Acuse de Valor (COVE), Tipo de Operación, Clave de Pedimento.
*   **Logística**: Datos de Identificación del Vehículo, Cantidad de Mercancía.

### 3. Información Financiera (Pago)
*   Línea de captura.
*   Institución bancaria.
*   Fecha y hora exacta del pago.
*   Número de operación bancaria.
*   Número de transacción SAT.
*   Clave del prevalidador.

### 4. Contenedores y Seguridad
*   Listado de contenedores asociados.
*   Números de candados (Sellos) aplicados.

### 5. Documentación Digital
*   UUID del CFDI Carta Porte (cuando aplique).

---

## ⚙️ Flujo de Operación Actualizado

### 1. Requerimiento de Datos
El bot consulta el URL proporcionado por el usuario o generado automáticamente:
`https://pecem.mat.sat.gob.mx/app/qr/ce/faces/pages/mobile/validadorqr.jsf?D1=[Aduana]&D2=[Patente]&D3=[Integracion]`

### 2. Procesamiento Estructural (Parsing)
A diferencia de la versión anterior (basada solo en Regex), el bot analiza la estructura de tablas y listas del portal PrimeFaces:
*   Identifica los `list-divider` para separar las secciones.
*   Extrae los valores de las celdas `<td>` correspondientes a cada etiqueta (`<br/>`).

### 3. Trazabilidad en JSON
Cada vez que el bot ejecuta una consulta, añade una entrada al campo `bot_logs_json` de la operación:
```json
{
    "timestamp": "2026-03-31T20:00:00Z",
    "execution_id": "bot_605b...",
    "status": "success",
    "scraped_data": {
        "integracion": "142512420",
        "modulacion": "DESADUANAMIENTO LIBRE",
        "pago": {
            "fecha": "2026-03-31 11:09:23",
            "banco": "Santander",
            "transaccion_sat": "40014310320261109236"
        },
        "vehiculo": "5736",
        "carta_porte": "903C7F98-A28E-4852-9E66-0B4941D6F3AD"
    }
}
```

---

## 🚀 Lógica de Notificación Proactiva
Las notificaciones (WhatsApp/Email) se disparan **únicamente** cuando se detecta un cambio en el campo "Modulación" dentro de los datos generales, asegurando que el cliente reciba la información crítica de liberación de mercancía al instante.

---

## 📈 Beneficios del Nuevo Modelo
*   **Auditoría**: Se elimina el "yo no sabía" al tener el log exacto de cuándo el SAT reconoció el pago y la modulación.
*   **Integridad**: El expediente digital se completa automáticamente con datos que antes se capturaban a mano (Líneas de captura, CFDI).
*   **Detección de Errores**: Identificación inmediata de inconsistencias entre lo declarado y lo registrado en el portal PECEM.

---

## 🏗️ Arquitectura V2: Multi-Tenant & Controlador Independiente (Abril 2026)

Con la transición a un modelo SaaS, el bot DODA se reestructuró en una capa de servicios independientes (`DodaConsultaService` y `NotificacionModulacionService`) servidos por una API exlusiva (`DodaBotController`).

### Novedades del Bot V2
1. **Routing Inteligente de Notificaciones**: En lugar de correos *hardcodeados*, el bot utiliza el catálogo **Directorio** de cada Tenant para identificar a quién y por qué medio (Email/WhatsApp) notificar.
2. **Reglas Específicas por Tenant**: Soporta configuraciones de agencia aduanal, como clientes que "solo quieren recibir correos rojos" o copias ocultas fijas (`BCC`) a gerencia.
3. **Escalabilidad y Concurrencia**:
    *   API *stateless* autenticada por token (`/api/bot/doda/ejecutar`).
    *   Usa *Guzzle Pool* para enviar Múltiples Peticiones Simultáneas (Max: 10), reduciendo el tiempo de ejecución a segundos aunque hayan +120 operaciones activas.
    *   Mecanismo *Anti-concurrencia* (Cache Lock) previniendo que un *Cron Job* monte ejecuciones sobre otras si el SAT está lento.
4. **URLs PECEM Multi-Aduana**: Las claves de la URL `D1` (Aduana) y `D2` (Patente) ahora se auto-determinan revisando: 
    * a) Las preferencias en la configuración (`configuracion.pecem`) del Tenant.
    * b) La Aduana/Patente específicas adjuntas a la "Operación".

### ¿Cómo configurarlo en un Servidor / Windows Task Scheduler?
Solo es necesario ejecutar una petición GET (p.ej. con CURL, PowerShell o cron) al endpoint con el respectivo Token:
```bash
curl -X GET "https://[tu-dominio]/api/bot/doda/ejecutar?token=[TU_TOKEN]"
```
Ejecutar idealmente **cada minuto** o según la intensidad de cruce.
