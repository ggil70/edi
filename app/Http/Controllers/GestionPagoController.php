<?php

namespace App\Http\Controllers;
//namespace App\Traits;

//Definicion de modelos
use App\Models\Adm_Proceso;
use App\Models\Archivo_Descarga;
use App\Models\Archivo_Encabezado;
use App\Models\Archivo_Recibido;
use App\Models\Archivo_Detalle;
use App\Models\Log_Error_Proceso;
use App\Models\Log_Gestion_Pago;

use App\Models\Proveedor_Acumulado;
use App\Models\Proveedor_Limite;

//Definicion traits

use App\Traits\FuncGestionPagos;
use App\Traits\AS400_validar;
use App\Traits\ValidarEdi;
use App\Traits\Whatsapp;
use App\Traits\MailEdi;



use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class GestionPagoController extends Controller
{

    use FuncGestionPagos;
    use AS400_validar;
    use Whatsapp;
    
    //validar proceso EDI
    use ValidarEdi; 
    use MailEdi;

///////////////////////////////// Proceso gestion pago
   

    public function callGestionPago(){
        Session::put('swFormGP', 1);
        return Redirect::route('valGestionPago');
    }

 public function valGestionPago(){
    //validar estatus servicio edi.
    //trials ValidarEdi
    $respServicio = $this->validarServicioEdi(); 

    //Validar funcionamiento del AS400 antes de procesar la validacion del archivo
    //de no haber conexion se detiene el proceso.
    //Activar despues de las pruebas

    $this->validarAS400();

    //Obtener descargas no culminadas
    $arrgDescarga = Archivo_Descarga::where('status_descarga','P')
                         ->get();


    if(count($arrgDescarga) > 0){
       
        foreach ($arrgDescarga as $arrgD) {

            $idDescarga = $arrgD->id;
            //obtener los archivos procesador de la decarga consultada con estatus
            // de pagos procesados (nivel_proceso = 3)
            $arrgArchivo = Archivo_recibido::where('status_proceso',0)
                              ->where('id_archivo_descarga',$idDescarga)
                              ->where('nivel_proceso', 4)
                              ->where('error_archivo', 0)
                              ->get(); 


            if(count($arrgArchivo) > 0){  
                $fechaActual = date('Y-m-d');    

 

                $swPagoTotal = 0;
                $swNroPago = 0;           
                foreach ($arrgArchivo as $arrgA) {
                    $swNroPago = 1;           
                    $idArchivo = $arrgA->id;
                    $arrgEncabezado = Archivo_Encabezado::where('id_archivo',$idArchivo)
                        ->first();
   
                    if($arrgEncabezado){
                        //verificar que la fecha valor sea menor o igual a la actual
                        $fechaValor = $arrgEncabezado->fecha_valor;

                        
                        // Convertimos ambas a objetos Carbon para comparar con seguridad
                        $esMenorOIgual = Carbon::parse($fechaValor)->lessThanOrEqualTo(Carbon::parse($fechaActual));
                        
                        if ($esMenorOIgual) {
                            $tDocumento = $arrgEncabezado->tipo_documento;
                            $nDocumento = $arrgEncabezado->nro_documento;
                            $lote = $arrgEncabezado->lote * 1;
                            $lote = trim($lote);

                            $resCabeceraGestion = $this->valCabeceraGestion($tDocumento, $nDocumento, $lote, $arrgA->id_cliente, $arrgA->nob_archivo);
                            
                            if($resCabeceraGestion == 0){
                                $dataResp = $this->valDetalleGestionPago($tDocumento, $nDocumento, $lote, $idArchivo, $idDescarga, $arrgA->id_cliente, $fechaValor, $arrgEncabezado->tipo_pago, $arrgA->nob_archivo);
                                if($dataResp == 1){
                                     $swPagoTotal = 1; 
                                }
                            }else if($resCabeceraGestion == 2){
                                //reversar pago
                                $resp = $this->reversarPago($idArchivo, $idDescarga, $arrgA->id_cliente, $arrgEncabezado->tipo_pago, $fechaValor, $arrgA->tot_monto_pago, $arrgA->nob_archivo);
                            }
                        }
                    } 

                }     //Fin de foreach archivo 
                if($swNroPago == 1 && $swPagoTotal == 0){
                    Archivo_Descarga::where('id', $idDescarga)
                         ->update(['status_descarga' =>  'C',
                                   'mot_status'=>'PROCESO CULMINADO',
                        ]); 
                }  
 
            } 

        }  // fin de foreach descarga
            if (session()->has('swFormGP')) {
                Session::forget('swFormGP');
                return view('procesoPago/gestionPago');
            }else{
                die("Fin de proceso de gestion de pago");
            }  
        

    }else{
        ///////////////////////////////////////////////////////////////////
        //culminar proceso registrarlo en un log de validacion gestion
        ///////////////////////////////////////////////////////////////////
        Log_Gestion_Pago::guardarGestionPago('04', 'SIN ARCHIVO PARA PROCESAR', 0, 'SIN ARCHIVO');
        die("Sin Descarga pendientes");
    }
 }


    public function valCabeceraGestion($tDocumento, $nDocumento, $lote, $idCliente, $nobArchivo){
        $tipo_documento=$tDocumento;
        $nro_documento=$nDocumento;
         
        $lote = $lote * 1; 
        $lote = trim($lote);

        //$lote = str_pad($lote, 9, "0", STR_PAD_LEFT);
        //trait / AS400CabGestion
        //Permite obtener la validacion del lote asociado al rif a procesar
        $respCabecera = $this->cabeceraGestion($tipo_documento, $nro_documento, $lote);

        if($respCabecera['error']=='98' || $respCabecera['error']=='99'){
            $this->reportarErrorAS400($respValidacion);
        }else{    
            $arrgCabecera = $respCabecera['respuesta'];
            $respuestaCabecera = trim($arrgCabecera['IBPINFDATReturn']['IBPINFDAT']['IBPSTS']);
            
            if(empty($respuestaCabecera)){
                $resp = Log_Gestion_Pago::guardarGestionPago('01', 'PENDIENTE', $idCliente, $nobArchivo);
                return 1;

            }else if($respuestaCabecera == 'P'){
                return 0;
            }else if ($respuestaCabecera == 'C'){
                return 2;
            } 
        }        

    }


    public function valDetalleGestionPago($tDocumento, $nDocumento, $lote, $idArchivo, $idDescarga, $idCliente, $fechaValor, $tipoPago, $nobArchivo){
        //obtener los registros de pago validados en as400
        $arrgArchivo = [
            'lote'=>$lote,
            'tipoDocumento'=>$tDocumento,
            'nroDocumento'=>$nDocumento,
            'idArchivo'=>$idArchivo,
            'idDescarga'=>$idDescarga
        ];
        
        //trait funcGestionPago
        //obtiene los registros procesados por bco activo y la simf
        $arrgPago = $this->validarGestionPago($arrgArchivo);


        if($arrgPago['resp'] == 0){
                //actualizar archivo recibido
                Archivo_Recibido::where('id', $idArchivo)
                         ->update(['status_proceso' =>  '2',
                                  'nivel_proceso'=> '5',
                                  'desc_error'=>'PAGO ARCHIVO REALIZADO',
                                  'tot_mto_pago_final'=>$arrgPago['mtoPagado'],
                                  'nro_registro_pagado'=>$arrgPago['nroPagado'],
                                  'nro_registro_rechazo'=>$arrgPago['nroRechazo'],
                                  'mto_pagado'=>$arrgPago['mtoPagado'],
                                  'mto_rechazo'=>$arrgPago['mtoRechazo'],
                                  ]);        

/*                //actualizar archivo descarga
                Archivo_Descarga::where('id', $idDescarga)
                         ->update(['status_descarga' =>  'C',
                                   'mot_status'=>'PROCESO CULMINADO',
                                  ]); 
*/

                //actualizar acumumulado de proveedores y beneficiario
                $this->registrarAcumulado($idCliente, $arrgPago['mtoPagado'], $arrgPago['mtoRechazo'], $fechaValor, $tipoPago); 
                $resp = Log_Gestion_Pago::guardarGestionPago('00', 'PROCESADO', $idCliente, $nobArchivo);       
            return 0;  
        }else{
            $mensEspera = "";
            $codRespuesta = "";

            if($arrgPago['tipoEspera'] == 'E'){
                $codRespuesta = '01';
                $mensEspera = 'ESPERA RESPUESTA SIFM';
            }else{
                $codRespuesta = '05';
                $mensEspera = 'ESPERA RESPUESTA ALTO VALOR';
            }

            $resp = Log_Gestion_Pago::guardarGestionPago($codRespuesta, $mensEspera, $idCliente,$nobArchivo);
            return 1;
        }
  
    }


    public function registrarAcumulado($idCliente, $mtoPagado, $mtoRechazo, $fechaValor, $tipoPago){
        //Registrar acumulado
        Proveedor_Acumulado::modAcumulado($mtoRechazo, $fechaValor, $idCliente, $tipoPago);

        return true;

    }


    public function reversarPago($idArchivo, $idDescarga, $idCliente, $tipoPago, $fechaValor, $mtoTotal, $nobArchivo){

        Archivo_Detalle::where('id_archivo', $idArchivo)
                    ->update(['cod_error_pago' =>  '595',
                              'desc_error_pago'=> 'CANCELACION EJECUCION PAGO',
                              'sw_pago'=>'0',
                              'error_pago'=>'1']);


        Archivo_Recibido::where('id',  $idArchivo)
                    ->update(['status_proceso' => '2',
                              'error_archivo'=>'10',
                              'desc_error'=>'PAGO [ CANCELACION PAGO ]',
                              'nivel_proceso'=>'4']);

        Archivo_Descarga::errorDescarga($idDescarga); 

        Proveedor_Acumulado::reversarAcumulado($mtoTotal, $fechaValor, $idCliente, $tipoPago);

        Log_Gestion_Pago::guardarGestionPago('02', 'CANCELADO', $idCliente, $nobArchivo);
        return true;        

    }


}
