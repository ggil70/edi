<?php

namespace App\Traits;

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

trait AS400CabGestion
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    //Permite verificar los datos de un cliente
    public function cabeceraGestion($tipoDocumento, $nroDocumento, $lote)
    {
        ////procesar pago en el as400 
        $wsdlUrl = env('URL_GATEWAY_AEL');
        $rif = trim($tipoDocumento) . trim($nroDocumento);
        $lote = $lote * 1;
        $lote = trim($lote);

        try {
            // 1. Crear una instancia del cliente SOAP.
            $client = new \SoapClient($wsdlUrl);
            // 2. Definir parametros de consulta 
            $parametros = [
                'req' => [
                    'IBPUSR' => $rif,
                    'IBPPID' => $lote,
                ]

            ];

            $metodo = 'IBPINFDAT';
            $response = $client->__call($metodo, [$parametros]);
            $json = json_encode($response);
            $arrayResp = json_decode($json, true);
            $data = [
                  'error'=>'00',
                  'respuesta'=>$arrayResp,
                  'mensajeTecnico'=>''
                    ];
            return $data;
        
        } catch (SoapFault $e) {

            //Maneja errores soap
            $data = [
                  'error'=>'98',
                  'respuesta'=>'Error al consumir el servicio SOAP 500',
                  'mensajeTecnico'=>$e->getMessage()
                    ];            
            return $data;
        } catch (\Exception $e) {
            $data = [
                  //Maneja errores inesperado
                  'error'=>'99',
                  'respuesta'=>'Error Inespserado, consulta al administrador 500',
                  'mensajeTecnico'=>$e->getMessage()
                    ];            
            return $data;            
        }
    }








    //fin del trait
}
