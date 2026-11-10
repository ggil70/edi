<?php

namespace App\Traits;

use App\Models\Log_Error_Proceso;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Importante importar esto para trabajar con archivo fisico

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;




//use Illuminate\Support\Facades\Log;

use SoapClient;
use SimpleXMLElement;

use Exception;
use SoapFault;

trait AS400_validar
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    use AS400_3010;
    use Whatsapp;


    //Permite verificar los datos de un cliente
    public function validarAS400(){
        $litRif = 'J';
        $nroRif = '000000000';
        $valAS400 = $this->consultaCliente($litRif, $nroRif);
        if($valAS400['error'] == '98' || $valAS400['error'] == '99'){
            $codOperacion = 'AS400';                
            $motivoError = $valAS400['respuesta'] . ' / ' . $valAS400['mensajeTecnico'];
            $respError = Log_Error_Proceso::guardarErrorProceso($codOperacion, $motivoError); 
            $this->enviarEmail('AS400','',$motivoError);
            exit();
        }

    }

    public function reportarErrorAS400($dataError){
        $codOperacion = 'AS400';      
        $motivoError = $dataError['respuesta'] . ' / ' . $dataError['mensajeTecnico'];
        $respError = Log_Error_Proceso::guardarErrorProceso($codOperacion, $motivoError); 
        $this->enviarEmail('AS400','', $motivoError);
        exit();
    }

    //fin del trait
}
