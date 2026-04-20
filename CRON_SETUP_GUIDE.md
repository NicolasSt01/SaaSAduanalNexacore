# 🤖 Configuración de Cron Jobs para DodaBot

## Cron Jobs Requeridos

### 1. Rollover de Fechas (NUEVO) - 23:50 PM

Este cron job actualiza las fechas de las operaciones rezagadas que no tienen modulacion.

#### Opción A: Usando Laravel Task Scheduler (Recomendado)

Agregar en `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Rollover de fechas - 23:50 diario
    $schedule->call(function () {
        $token = env('CHECK_TRAFICO_TOKEN');
        $url = config('app.url') . '/api/bot/doda/rollover-dates?token=' . $token;
        
        $client = new \GuzzleHttp\Client();
        $response = $client->post($url, [
            'timeout' => 30,
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        \Log::info('Cron: Rollover de fechas ejecutado', [
            'resultado' => $result,
        ]);
        
    })->dailyAt('23:50')->withoutOverlapping();
    
    // Consulta masiva de modulacion - 00:00 diario
    $schedule->call(function () {
        $token = env('CHECK_TRAFICO_TOKEN');
        $url = config('app.url') . '/api/bot/doda/ejecutar?token=' . $token;
        
        $client = new \GuzzleHttp\Client();
        $response = $client->get($url, [
            'timeout' => 300, // 5 minutos timeout
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        \Log::info('Cron: Consulta masiva ejecutada', [
            'resultado' => $result,
        ]);
        
    })->dailyAt('00:00')->withoutOverlapping();
}
```

**Crontab del VPS** (ejecuta el scheduler de Laravel cada minuto):
```bash
* * * * * cd /var/www/nexacore && php artisan schedule:run >> /dev/null 2>&1
```

#### Opción B: Cron Job Directo en VPS

Agregar al crontab (`crontab -e`):

```bash
# Rollover de fechas - 23:50 PM diario
50 23 * * * curl -s -X POST "https://app.nexacore.com/api/bot/doda/rollover-dates?token=TU_TOKEN_AQUI" >> /var/log/nexacore/date-rollover.log 2>&1

# Consulta masiva de modulacion - 00:00 AM diario
0 0 * * * curl -s -X GET "https://app.nexacore.com/api/bot/doda/ejecutar?token=TU_TOKEN_AQUI" >> /var/log/nexacore/doda-consulta.log 2>&1
```

#### Opción C: Windows Task Scheduler (si usas el bot externo)

**Para Rollover de Fechas:**
```powershell
# Crear una tarea programada que ejecute a las 23:50
$action = {
    Invoke-RestMethod -Uri "https://app.nexacore.com/api/bot/doda/rollover-dates?token=TU_TOKEN_AQUI" -Method POST
}

# Registrar en Event Viewer
$action | Out-File -FilePath "C:\scripts\rollover-fechas.ps1"
```

**Para Consulta Masiva:**
```powershell
# Crear una tarea programada que ejecute a las 00:00
$action = {
    Invoke-RestMethod -Uri "https://app.nexacore.com/api/bot/doda/ejecutar?token=TU_TOKEN_AQUI" -Method GET
}

# Registrar en Event Viewer
$action | Out-File -FilePath "C:\scripts\consulta-masiva.ps1"
```

## Verificación de Cron Jobs

### Verificar que están corriendo

```bash
# Ver crontab actual
crontab -l

# Ver logs del rollover
tail -f /var/log/nexacore/date-rollover.log

# Ver logs de Laravel
tail -f /var/www/nexacore/storage/logs/doda_bot.log
```

### Probar manualmente

```bash
# Probar rollover de fechas
curl -X POST "https://app.nexacore.com/api/bot/doda/rollover-dates?token=TU_TOKEN"

# Probar consulta masiva
curl -X GET "https://app.nexacore.com/api/bot/doda/ejecutar?token=TU_TOKEN"
```

## Flujo Diario Sugerido

```
23:50 - Rollover de fechas (actualiza fechas rezagadas)
  ↓
00:00 - Consulta masiva de modulacion (bot externo consulta SOIA)
  ↓
06:00 - Envío de reportes por email (si aplica)
```

### ¿Por qué este orden?

1. **23:50 - Rollover**: Primero actualizamos las fechas de las operaciones que no llegaron
2. **00:00 - Consulta**: Luego el bot consulta la modulacion con las fechas ya actualizadas
3. **06:00 - Reportes**: Los reportes matutinos muestran la información actualizada

## Monitoreo

### Logs a revisar

```bash
# Logs del rollover de fechas
tail -f /var/www/nexacore/storage/logs/doda_bot.log | grep "Rollover"

# Logs de la consulta masiva
tail -f /var/www/nexacore/storage/logs/doda_bot.log | grep "Ejecución"

# Ver operaciones rezagadas actuales
mysql -u usuario -p -e "SELECT COUNT(*) FROM operaciones WHERE fecha_cruce_estimada <= CURDATE() AND (modulacion IS NULL OR modulacion = '' OR modulacion = '0');"
```

### Alertas (Opcional)

Puedes configurar alertas con un script como:

```bash
#!/bin/bash
# /var/www/nexacore/scripts/check-rollover.sh

RESULT=$(curl -s -X POST "https://app.nexacore.com/api/bot/doda/rollover-dates?token=TU_TOKEN")
ACTUALIZADAS=$(echo $RESULT | jq '.total_actualizadas')

if [ "$ACTUALIZADAS" -gt 0 ]; then
    echo "⚠️ Se actualizaron $ACTUALIZADAS operaciones rezagadas" | mail -s "Alerta: Rollover de Fechas" tu@email.com
fi
```

## Troubleshooting

### El cron no se ejecuta

```bash
# Verificar que el cron daemon está corriendo
systemctl status cron

# Reiniciar cron si es necesario
systemctl restart cron
```

### Error de token

```bash
# Verificar que el token está configurado
grep CHECK_TRAFICO_TOKEN /var/www/nexacore/.env
```

### Error de timeout

Aumentar el timeout en el cron job:
```bash
# Cambiar timeout de 30 a 60 segundos
curl -s --max-time 60 -X POST "https://app.nexacore.com/api/bot/doda/rollover-dates?token=TU_TOKEN"
```
