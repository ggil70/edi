<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Importante importar esto para trabajar con archivo fisico

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\Archivo_Recibido;





use Illuminate\Support\Facades\Session;


use Exception;

trait FuncProveedorAcumulado
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    //librerias para as400 para identificacion cliente


    public function validacionArchivo($arrgArchivos, $idArchivoDescarga)
    {

            $arrayPago = Archivo_Recibido::query()
                    ->select([
                        'archivo_encabezados.*', 'archivo_recibidos.id_cliente',
                    ])
                    ->join('archivo_encabezados', 'archivo_recibidos.id', '=', 'archivo_encabezados.id_archivo')
                    ->where('archivo_recibidos.id', $archivoPago->id)
                    ->first();





        
    }









    //fin del trait
}
