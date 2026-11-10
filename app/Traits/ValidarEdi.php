<?php

namespace App\Traits;

use App\Models\Servicio_Edi;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Importante importar esto para trabajar con archivo fisico
use Illuminate\Support\Str; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

//use Illuminate\Support\Facades\Log;

use Exception;

trait ValidarEdi
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    //Permite almacenar en un arreglo los nombre de los archivos contenido en una carpeta 
    //en un momento dado
    public function validarServicioEdi(){
        $resp = Servicio_Edi::estatus_servicio();
        if($resp == 2){
            exit("No existe registro en la tabla servicio EDI");
        }else if($resp == 0){
            //el servicio esta detenedo
            exit("Servicio inactivo");
        }else{
            return 1;
        }
    } 


      

    //fin del trait
}
