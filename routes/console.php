<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// INC-019: Verificación diaria de CSF de clientes (Art. 36-A)
// Ejecutar a las 07:00 AM todos los días
Schedule::command('clientes:verificar-csf')->dailyAt('07:00');

// INC-052: Verificación diaria de tenants vencidos para corte automático
Schedule::job(new \App\Jobs\VerificarTenantsVencidos)->dailyAt('08:00');
