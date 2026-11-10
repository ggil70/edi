<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('app:reproceso-pago')->daily(); // Se ejecuta diario
Schedule::command('app:procesar-archivos')->daily(); // Se ejecuta diario
Schedule::command('app:ejecutar-gestion-pago-controlador')->daily(); // Se ejecuta diario
Schedule::command('app:crear-archivo-banstas')->daily(); // Se ejecuta diario
