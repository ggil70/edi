<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CrearArchivoBanstaController;

class EjecutarBanstaControlador extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crear-archivo-banstas';

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
        $controlador = app(CrearArchivoBanstaController::class);
        $controlador->crearArchivoBanstas();

        $this->info('El método se ejecutó correctamente.');
    }
}
