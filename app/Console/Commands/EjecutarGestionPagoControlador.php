<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GestionPagoController;

class EjecutarGestionPagoControlador extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ejecutar-gestion-pago-controlador';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controlador = app(GestionPagoController::class);
        $controlador->valGestionPago();

        $this->info('El método se ejecutó correctamente.');
    }
}
