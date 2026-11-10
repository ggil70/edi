<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


use App\Models\Archivo_Descarga;
use App\Models\Archivo_Recibido;
use App\Models\Archivo_Encabezado;
use App\Models\Archivo_Detalle;
use App\Models\Simf_Error;

use SoapClient;
use SimpleXMLElement;


use Exception;
use SoapFault;

trait FuncGestionPagos
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    //Permite validar los campos según reglas del negocio

    use AS400CabGestion;
    use AS400GestionPago;



    public function obtenerCabeceraGestion($tDocumento, $nDocumento, $lote){

        //$lote = str_pad($lote, 9, "0", STR_PAD_LEFT);
        $lote = $lote * 1;
        $lote = trim($lote);

        $respCabecera = $this->cabeceraGestion($tDocumento, $nDocumento, $lote);

    

        if(isset($respCabecera['IBPINFDATReturn']['IBPINFDAT'])){
            $arrgEncabezadoGestion = $respCabecera['IBPINFDATReturn']['IBPINFDAT'];
            $arrgEncabezadoGestion['IBPSTS'] = strtoupper($arrgEncabezadoGestion['IBPSTS']);
            if($arrgEncabezadoGestion['IBPSTS'] == 'P'){
                return 0;
            }else if(empty($arrgEncabezadoGestion['IBPSTS'])){
                //La respuest Pendiente por procesar
                return 1;
            }else{                //La respuest fue rechazado
                return 2;
            }    
        }else{
            //no encontro la cabecera detener la validacion del archivo y reportarlo en el log de 
            //gestion de pago 
            return 3;
        }
    }

    
    //REvisa los pagos realizados en activo en linea y la simf, y los actiualiza en la tabla
    //archivo_detalles segun sea el resultado
    public function validarGestionPago($arrgArchivo){
        $tDocumento = $arrgArchivo['tipoDocumento'];
        $nDocumento = $arrgArchivo['nroDocumento'];
        $lote = $arrgArchivo['lote'];

        //$lote = str_pad($lote, 9, "0", STR_PAD_LEFT);
        $lote = $lote * 1;
        $lote = trim($lote);

        

        $idArchivo = $arrgArchivo['idArchivo'];
        $idDescarga = $arrgArchivo['idDescarga'];

        $mtoPagado = 0.00;
        $mtoRechazo = 0.00;
        $nroPagado = 0;
        $nroRechazo = 0;  


        $swRegEspera = 0;
        $respGestion = $this->verificarGestionPago($tDocumento, $nDocumento, $lote);

        
        $arrgPago = $respGestion['respuesta']['INPYRLSJV2Return']['INPYRLSJV'];


        //VAlida si algun registro en gestion pago esta en espera, de ser asi se culmina el 
        //proceso de validacion 

        $tipoEspera = 0;
        foreach($arrgPago as $detalleGestion){
            if($detalleGestion['PYRSTS'] == 'E' || $detalleGestion['PYRSTS'] == 'A'){    
                if($detalleGestion['PYRSTS'] == 'E'){
                   $tipoEspera = 1; 
                }else{
                    $tipoEspera = 2; 
                }
                $swRegEspera = 1;
            }
        }

        if($swRegEspera == 1){
            //culmina la validacion de la gestion del pago
            $data = [
                'tipoEspera'=>$tipoEspera,
                'resp'=>1
            ];
            return $data;
        }

        if($respGestion['error'] == '00'){
            $dataDetalle = Archivo_Detalle::where('id_archivo',$idArchivo)->get();

            if(count($dataDetalle) > 0){
                $arrgPago = $respGestion['respuesta']['INPYRLSJV2Return']['INPYRLSJV'];


                $swDetenerEspera = 0;

                foreach ($dataDetalle as $detallePago) {
                    $nroDocumento = str_pad(trim($detallePago['nro_documento']), 9, "0", STR_PAD_LEFT);
                    $documento = trim($detallePago['tipo_documento']) . trim($nroDocumento);
                    $montoD = trim($detallePago['monto_pagar_ok']);
                    $cuentaD = trim($detallePago['cta_receptora']);


                    foreach($arrgPago as $detalleGestion){
                        if($detalleGestion['PYRBAD1'] == $documento){
                            $montoG = trim($detalleGestion['PYRAMT']);
                            $cuentaG = trim($detalleGestion['PYRBACC']);

                            if($montoD == $montoG && $cuentaD == $cuentaG){
                                if($detalleGestion['PYRSTS'] == 'E' || $detalleGestion['PYRSTS'] == 'A' ){
                                    $this->pagoEspera($idArchivo);
                                    $swDetenerEspera = 1;
                                    break;
                                }else if($detalleGestion['PYRSTS'] == 'P'){
                                   $mtoPagado+=$montoD;
                                   $nroPagado+=1;
                                   $this->pagoProcesado($detallePago);
                                }else if($detalleGestion['PYRSTS'] == 'R'){
                                    $mtoRechazo+=$montoD ;
                                    $nroRechazo+=1;
                                    $this->pagoRechazado($detallePago, $detalleGestion['PYRERD']);

                                }    
                            }
                        }
                    }   ////fin foreach foreach gestion detall as400

                    //si hay un registro en espera se detiene el proceso 
                    // para una proxima revision de gestion de pago,.
                    if($swDetenerEspera == 1){
                        break;
                    }
                }  //fin foreach foreach ($dataDetalle as $detallePago) / archivo Detalle  


                if($swDetenerEspera == 0){
                    $data = [
                        'resp' =>'0',
                        'nroPagado'=>$nroPagado,
                        'nroRechazo'=>$nroRechazo,
                        'mtoPagado'=>$mtoPagado,
                        'mtoRechazo'=>$mtoRechazo,
                    ];
                    return $data;
                }else{
                    $data = [
                        'resp' =>'1',
                    ];
                    return $data;
                }    
            }else{
                //existe una anomalidad donde el archivo no posee detalle de pago
                return 1;    
            }
        }else{
            //Se genero u error en el as400
            //programar problemas con as400
        }    
        
    } //fin funcion validarGestionPago


    public function  pagoProcesado($detallePago){
        $detallePago->sw_pago = 1;
        $detallePago->cod_error_pago = '500';
        $detallePago->desc_error_pago = 'PAGO EXITOSO';
        $detallePago->fec_valid_pago = date('Y-m-d H:i:s');
        $detallePago->save();
        return 0;
    }  


    public function pagoEspera($idArchivo){
        Archivo_Detalle::where('id_archivo', $idArchivo)
                 ->update(['cod_error_pago' =>  '400',
                          'desc_error_pago'=> 'PAGO EN ESPERA',
                          'sw_pago'=>'0',
                          'error_pago'=>'0']);        
        return 0;
    }  

    public function  pagoRechazado($detallePago, $codError){
        $detallePago->sw_pago = 1;

        

        //buscar error en la tabla simf errores
        $codSimf = Simf_Error::where('cod_simf', $codError)
                        ->first();
        if($codSimf){
            $errorSimf = $codSimf->codigo;
            $descErrorSimf = $codSimf->desc_simf;            
        }else{
            $errorSimf = "549";
            $descErrorSimf = "RECHAZO POR LA SIMF";

        }               
        $detallePago->cod_error_pago = $errorSimf;
        $detallePago->desc_error_pago = $descErrorSimf;
        $detallePago->error_pago = 1;
        $detallePago->fec_valid_pago = date('Y-m-d H:i:s');
        $detallePago->save();
        return 0;
    }  

    //---------------------------------------------------------------------------------------
    //fin del trait
}
