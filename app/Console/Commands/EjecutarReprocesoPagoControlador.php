<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// Importamos el controlador necesario
use App\Http\Controllers\ProcesarPagoController;

class EjecutarReprocesoPagoControlador extends Command
{
    /**
     * El nombre y firma del comando (lo que escribes en la terminal).
     *
     * @var string
     */
    protected $signature = 'app:reproceso-pago';

    /**
     * La descripción que aparecerá al ejecutar php artisan list.
     *
     * @var string
     */
    protected $description = 'Llama al método reprocesarPago del ProcesarPagoController';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando el reproceso de pago...');

        try {
            // Instanciamos el controlador usando el Service Container de Laravel
            $controlador = app(ProcesarPagoController::class);
            
            // Ejecutamos el método
            $controlador->reprocesarPago();

            $this->info('El proceso se ejecutó con éxito.');
        } catch (\Exception $e) {
            $this->error('Ocurrió un error: ' . $e->getMessage());
        }
    }
}
