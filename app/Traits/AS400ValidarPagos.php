<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Importante importar esto para trabajar con archivo fisico

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;




//use Illuminate\Support\Facades\Log;

use SoapClient;
use SimpleXMLElement;

use Exception;
use SoapFault;

trait AS400ValidarPago
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    //Permite verificar los datos de un cliente
    public function validarPagoAS400($tDocumento, $nDocumento, $lote, $ctaDebitar, $nroTotal, $mtoTotal, $tipoPago, $fechaValor){
    //Formatear los datos para enviarlo al as4oo para su validación y bloqueo del monto     
    
    $rif =  trim($tDocumento) . trim($nDocumento);
    $lote = $lote * 1;
    $lote = trim($lote);

    //ltrim($lote, "0");
    $ctaDebitar = trim($ctaDebitar);
    $ctaDebitar = substr($ctaDebitar,-10);
    $nroTotal = trim($nroTotal);
    $mtoTotal = trim($mtoTotal);


    $diaPago = substr($fechaValor,8,2);
    $mesPago = substr($fechaValor,5,2);
    $anioPago = substr($fechaValor,2,2);
    // Se usa el helper global request() de Laravel; dentro del trait no existe $request
    $ip = request()->ip();

    $puertoIp = '04'.$ip;
    
    //tipo pago nomina o orden de pago
    //SAL = pago nomina
    //SCV = pago Proveedor
    if(trim($tipoPago) == 'SAL'){  //PAgo nomina
        $tipoPago = 'PAGO NOMINA ' . trim($lote); 
    }else{
        $tipoPago = 'ORDEN DE PAGO ' . trim($lote);  
    }

    // Ejecutar operación en AS400 
    $wsdlUrl = env('URL_GATEWAY_AEL');

    try {
        // 1. Crear una instancia del cliente SOAP.
        $client = new \SoapClient($wsdlUrl);
        //´2. Definir parametros de consulta 


        $parametros = [
            'req' => [
                'PYRUSR' => $rif,
                'PYRPID' => $lote,
                'PYRACC' => $ctaDebitar,
                'PYRAMT' => $mtoTotal,
                'PYRNUM' => $nroTotal,
                'PYRVDD' => $diaPago,
                'PYRVDM' => $mesPago,
                'PYRVDY' => $anioPago,
                'PYRMEM1' => $tipoPago,
                'PYRBBKA2'=> $puertoIp,
                'PYROPC' => '00',
            ]
        ];

        $metodo = 'INPYRLSJV';
        $response = $client->__call($metodo, [$parametros]);
        $json = json_encode($response);
        $arrayResp = json_decode($json, true);

        if(isset($arrayResp['INPYRLSJVReturn']['error']['ERRNUM'])){
            $data = [
                'error'=>$arrayResp['INPYRLSJVReturn']['error']['ERNU01'],
                'respuesta'=>$arrayResp['INPYRLSJVReturn']['error']['ERDS01'],
                'mensajeTecnico'=>'ERROR VALIDACION',
            ];
            return $data;
        }else{
            $data = [
                'error'=>'00',
                'respuesta'=>'VALIDADO OK',
                'mensajeTecnico'=>'OK'
            ];
            return $data;
        }
            
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

    }  //fin function validarPagoAS400E  



//////////////////////////////
    //Permite registrar los pagos
    public function registrarDetallePago($arrgPago){
    //Formatear los datos para enviarlo al as4oo para su validación y bloqueo del monto     
    
    // Ejecutar operación en AS400 
    $wsdlUrl = env('URL_GATEWAY_AEL');
    try {
        // 1. Crear una instancia del cliente SOAP.
        $client = new \SoapClient($wsdlUrl);
        //´2. Definir parametros de consulta 
        $parametros = [
            'req' => [
                'PYRUSR'   => $arrgPago['PYRUSR'],
                'PYRPID'   => $arrgPago['PYRPID'],
                'PYROPC'   => '01',
                'PYRTYP'   => $arrgPago['PYRTYP'],
                'PYRNUM'   => $arrgPago['PYRNUM'],
                'PYRBACC'  => $arrgPago['PYRBACC'],
                'PYRBBNK'  => $arrgPago['PYRBBNK'],
                'PYRBBKN'  => $arrgPago['PYRBBKN'],
                'PYRIBKID' => $arrgPago['PYRIBKID'],
                'PYRIBKNM' => $arrgPago['PYRIBKNM'],
                'PYRIBKAD' => $arrgPago['PYRIBKAD'],
                'PYRIBKA2' => $arrgPago['PYRIBKA2'],
                'PYRBNAM'  => $arrgPago['PYRBNAM'],
                'PYRBBKA2' => $arrgPago['PYRBBKA2'],
                'PYRAMT'   => $arrgPago['PYRAMT'],
                'PYRCCY'   => $arrgPago['PYRCCY'],
                'PYRMEM1'  => $arrgPago['PYRMEM1'],
                'PYRMEM2'  => $arrgPago['PYRMEM2'],
                'PYRBAD1'  => $arrgPago['PYRBAD1'],
             ]
        ];

        $metodo = 'INPYRLSJV';
        $response = $client->__call($metodo, [$parametros]);
        $json = json_encode($response);
        $arrayResp = json_decode($json, true);

        if(isset($arrayResp['INPYRLSJVReturn']['error']['ERRNUM'])){
            $data = [
                'error'=>$arrayResp['INPYRLSJVReturn']['error']['ERRNUM'],
                'mensaje'=>$arrayResp['INPYRLSJVReturn']['error']['ERDS01'],
                'mensaje_tecnico'=>'ERROR',
            ];
            //$error = $arrayResp['INPYRLSJVReturn']['error']['ERRNUM'] . ';' . $arrayResp['INPYRLSJVReturn']['error']['ERDS01'] . "; ERROR" ;
            return $data;
        }    
        else{
            $data = [
                'error'=>00,
                'mensaje'=>'PAGO OK',
                'mensaje_tecnico'=>'OK',
            ];            

            //$error = "00;Lote exitoso;OK"; 
            return $data;
        }

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
}  //fin function validarPagoAS400E  





    public function ejecutarPago400($arrgPago, $mtoTotal, $nroRegistro){




         ////procesar pago en el as400 
        $wsdlUrl = env('URL_GATEWAY_AEL');
        try {
            // 1. Crear una instancia del cliente SOAP.
            $client = new \SoapClient($wsdlUrl);
            // 2. Definir parametros de consulta 
            $parametros = [
               'req' => [
                    'PYRUSR' =>  $arrgPago['PYRUSR'],
                    'PYRPID' =>  $arrgPago['PYRPID'],
                    'PYROPC' =>  '02',
                    'PYRTYP' =>  $arrgPago['PYRTYP'],
                    'PYRMEM1' => $arrgPago['PYRMEM1'],
                    'PYRMEM2' => $arrgPago['PYRMEM2'],
                    'PYRACC' =>  $arrgPago['PYRACC'],
                    'PYRAMT' =>  $mtoTotal,
                    'PYRNUM' =>  $nroRegistro,
                    'PYRVDD' =>  $arrgPago['PYRVDD'],
                    'PYRVDM' =>  $arrgPago['PYRVDM'],
                    'PYRVDY' =>  $arrgPago['PYRVDY'],
                    //'PYRBBKA2' => $arrgPago['PYRBBKA2'],
               ]
            ];


            $metodo = 'INPYRLSJV';
            $response = $client->__call($metodo, [$parametros]);
            $json = json_encode($response);
            $arrayResp = json_decode($json, true);

            if(isset($arrayResp['INPYRLSJVReturn']['error']['ERRNUM'])){
                $data = [
                    'error'=>$arrayResp['INPYRLSJVReturn']['error']['ERNU01'],
                    'mensaje'=>$arrayResp['INPYRLSJVReturn']['error']['ERDS01'],
                    'mensajeTecnico'=>'ERROR VALIDACION',
                ];
                return $data;                
            }    
            else{
                
                $data = [
                    'error'=>'00',
                    'mensaje'=>'PAGO OK',
                    'mensaje_tecnico'=>'OK',
                ];            
                return $data;
            }


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
