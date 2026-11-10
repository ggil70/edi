<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

// Importamos tu controlador
use App\Http\Controllers\ProcesarPagoController;

class EjecutarPagoControlador extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:procesar-archivos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta el proceso de lectura de archivos de pago desde el controlador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Llamamos al controlador de forma manual
                $controlador = app(ProcesarPagoController::class);
                $controlador->procesarArchivos();

                $this->info('El método se ejecutó correctamente.');
    }
}
