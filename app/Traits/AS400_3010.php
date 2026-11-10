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

trait AS400_3010
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    //Permite verificar los datos de un cliente
    public function consultaCliente($tipoDocumento, $nroDocumento)
    {
        ////procesar pago en el as400 
        $wsdlUrl = env('URL_GATEWAY_AEL');
        $nroDocumento = str_pad($nroDocumento, 12, "0", STR_PAD_LEFT);

        try {
            // 1. Crear una instancia del cliente SOAP.
            $client = new \SoapClient($wsdlUrl);
            // 2. Definir parametros de consulta 
            $parametros = [
                'req' => [
                    'NACIONALIDAD' => $tipoDocumento,
                    'NROIDENTIFICACION' => $nroDocumento,
                    'NROPARAM' => '1',
                    'INTLIDCL' => '1',
                ]

            ];

            $metodo = 'NIBPF3010';
            $response = $client->__call($metodo, [$parametros]);
            $json = json_encode($response);
            $arrayResp = json_decode($json, true);

            if (isset($arrayResp['NIBPF3010Return']['error']['ERRNUM'])) {
                $data = [
                    'error'=>'01',
                    'respuesta'=>'CLIENTE NO EXISTE EN EL CORE BANCARIO',
                    'mensajeTecnico'=>'SM',
                ];
                return $data;                
            } else {
                foreach ($arrayResp as $identi) {
                    Session::put('nobCliente', $identi['NIBPF3010']['INTLNADB']);
                };
                $data = [
                    'error'=>'00',
                    'respuesta'=>'CLIENTE EXISTENTE',
                    'mensajeTecnico'=>'SM',
                ];
                return $data; 
            }
        } catch (SoapFault $e) {
                $data = [
                    'error'=>'98',
                    'respuesta'=>'Error al consumir el servicio SOAP 500',
                    'mensajeTecnico'=>$e->getMessage(),
                ];
                return $data;
        } catch (\Exception $e) {
                //Maneja errores Inesperado
                $data = [
                    'error'=>'99',
                    'respuesta'=>'Error Inespserado, consulta al administrador 500',
                    'mensajeTecnico'=>$e->getMessage(),
                ];
                return $data;
        }
    }








    //fin del trait
}
