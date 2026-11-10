<?php

namespace App\Traits;

use App\Models\Archivo_Descarga;
use App\Models\Archivo_Encabezado;
use App\Models\Archivo_Recibido;
use App\Models\Archivo_Detalle;
use App\Models\Log_Error_Proceso;
use App\Models\Log_Crear_Bansta;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Importante importar esto para trabajar con archivo fisico

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

//use Illuminate\Support\Facades\Log;

use Exception;

trait CrearBanstas
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    //Permite almacenar en un arreglo los nombre de los archivos contenido en una carpeta 
    //en un momento dado
    public function crearArchivoBansta()
    {

        $swArchivo = 0;    

        //La consulta se realizara cuando el archivo los pagos estes validados nivel 5,
        //Tabien cuenado exista un error durante el pago de un encabezado o detalle nivel 3
        // o cuando la ejecución del pago sea erronea nivel 4     

        $arrgArchivo = Archivo_Recibido::where('imp_bansta', 0)
            ->where(function ($query) {
                $query->where('nivel_proceso', 5)
                      ->orWhere(function ($q) {
                          $q->where('nivel_proceso', 3)
                            ->where('error_archivo', '<>', 0);
                      })
                      ->orWhere(function ($q) {
                          $q->where('nivel_proceso', 4)
                            ->where('error_archivo', '<>', 0);
                      });
            })
            ->get();




        if(count($arrgArchivo) > 0){
            foreach ($arrgArchivo as $archivo) {
                $idDescarga = $archivo->id_archivo_descarga;
                $swArchivo = 1;
                $idArchivo = $archivo->id;

                $rifPagador = $archivo->rif_pagador;
                $nroOrden = $archivo->nro_documento;

                $respHeader = $this->headerBansta($idArchivo);
                $respEncabezado = $this->encabezadoBansta($idArchivo);




                if($respEncabezado['cod'] = '00'){
                    $header = $respHeader['cadena'];
                    $encabezado = $respEncabezado['cadena'];
                    $respDetalle = $this->detalleBansta($idArchivo);

                    if($respDetalle['cod'] = '00'){
                        $detalle = $respDetalle['cadena'];
                        //guardar archivo carpeta bansta
                        $cadenaBansta = $header . $encabezado . $detalle;
                        $nombreArchivo = 'bansta'. trim($archivo->rif_pagador) . trim($archivo->nro_documento). '.txt'; 
                        $this->generarArcBansta($nombreArchivo, $cadenaBansta);
                        ////////////////////////////////////////////
                        //Guardar archivo en carpeta FTP de salida
                        //Activar en el trabajo
                        $respFTP = true;  //quitar al activar rutima ftp
                        $respFTP = $this->copiarBanstaFtp($nombreArchivo); 
                        ////////////////////////////////////////////

                        if($respFTP == true){
                            //CAmbiar nivel de proceso y estatus de proceso
                            $respArchivoRecibido = $this->cambiarStatusArchivoRecibido($idArchivo, $nombreArchivo);
                            //CAmbiar nivel de proceso y estatus de proceso
                            //$respArchivoDescarga = $this->cambiarStatusArchivoDescarga($idDescarga);
                            $data = [
                                'cod' => '600',
                                'desc' => '600 - ARCHIVO BANSTA / ' . $rifPagador . ' / ' . $nroOrden . ' CREADO SATISFACTORIAMENTE',
                                'rif' => $rifPagador,
                                'nroOrden' => $nroOrden,
                                'archivoBansta'  => $nombreArchivo                                   
                            ];  
                            $respError = Log_Crear_Bansta::guardarBansta($data); 
                            
                        }else{
                            //Error servidor FTP al copiar el archivo
                            $data = [
                                'cod' => '650',
                                'desc' => '650 - FALLA AL COPIAR ARCHIVO BANSTA EN LA CARPETA FTP',
                                'rif' => $rifPagador,
                                'nroOrden' => $nroOrden,  
                                'archivoBansta'  => $nombreArchivo                                   
                            ];
                            $respError = Log_Crear_Bansta::guardarBansta($data);
                            //////////////////////////////////////////
                            //Mandar mensaje Whataspp a servidores
                            //////////////////////////////////////////


                        }                                                            

                    }else{
                        $data = [
                            'cod' => '651',
                            'desc' => '651 - ARCHIVO SIN DETALLE / ' . $rifPagador . ' / ' . $nroOrden,
                            'rif' => $rifPagador,
                            'nroOrden' => $nroOrden,
                            'archivoBansta'  => $nombreArchivo 
                        ];
                        $respError = Log_Crear_Bansta::guardarBansta($data);


                    }
                }else{
                   $data = [
                        'cod' => '651',
                        'desc' => '651 - ARCHIVO SIN DETALLE / ' . $rifPagador . ' / ' . $nroOrden,
                        'rif' => $rifPagador,
                        'nroOrden' => $nroOrden,
                        'archivoBansta'  => $nombreArchivo 

                    ];
                    $respError = Log_Crear_Bansta::guardarBansta($data);

                }
            } //Fin foreach Archivo
            Session::forget('swFormBansta');
            return 0;

        } //fin validar archivos 
        else{
            $data = [
                'cod' => '652',
                'desc' => '652 - SIN ARCHIVO PARA GENERAR LOS ARCHIVOS BANSTA',
                'rif' => '0',
                'nroOrden' => 0,
                'archivoBansta'  => ''
            ];
            $respError = Log_Crear_Bansta::guardarBansta($data);
            return 1;
                    
        }   
    }

    public function headerBansta($idArchivo){
        //datos encabezado

        $arrgHeader = Archivo_Recibido::join('clientes', 'archivo_recibidos.id_cliente', '=', 'clientes.id')
                                ->select('archivo_recibidos.*', 'clientes.codigo_edi')
                                ->where('archivo_recibidos.id',$idArchivo)
                                ->first();
        if($arrgHeader){

           $codigoEdi = str_pad(trim($arrgHeader->codigo_edi), 15, " ", STR_PAD_RIGHT); 
           $tipoRegistro = 'HDR';
           $estandar = 'E';
           $version = 'D  95B';
           $mensaje = 'BANSTA';
           $modo = 'P';

            //Construir Header
            $cadHeader = $tipoRegistro;
            $cadHeader.= $codigoEdi;
            $cadHeader.= $estandar;
            $cadHeader.= $version;
            $cadHeader.= $mensaje;
            $cadHeader.= $modo;

            $data = [
                'cod' => '00',
                'cadena' => $cadHeader,
            ];
        }else{
            $data = [
                'cod' => '01',
                'cadena' => 'SIN Header',
            ];    
        } 
        return $data;   
    }                           





    public function encabezadoBansta($idArchivo){
        //datos encabezado
        $arrgEncabezado = Archivo_Encabezado::where('id_archivo',$idArchivo)
                               ->first();
        if($arrgEncabezado){
            $nroRefBanco = str_pad(trim($arrgEncabezado->nro_ref_banco_bansta), 35, " ", STR_PAD_RIGHT);
            $fechaBansta = date('Ymdhis');
            $fecRefBanco = str_pad(trim($fechaBansta), 14, " ", STR_PAD_RIGHT);
            $tipoOrdBansta = str_pad(trim($arrgEncabezado->tipo_pago), 3, " ", STR_PAD_RIGHT);
            $refBansta = str_pad(trim($arrgEncabezado->nro_ref_orden_bansta), 15, " ", STR_PAD_RIGHT); 
            $bcoCod = 'ACTIVECAXXX';
            $bcoCod = str_pad($bcoCod, 12, " ", STR_PAD_RIGHT);
            $rifEmpresa = trim($arrgEncabezado->tipo_documento) . trim(str_pad($arrgEncabezado->nro_documento, 16, 0, STR_PAD_LEFT));
            //$rifEmpresa = str_pad($rifEmpresa, 17, " ", STR_PAD_RIGHT); 
            $nobEmpresa = str_pad(trim($arrgEncabezado->nob_empresa), 70, " ", STR_PAD_RIGHT); 

            //Construir Encabezado
            $cadEncabezado = "\n01";

            $cadEncabezado.= $nroRefBanco;
            $cadEncabezado.= $fecRefBanco;
            $cadEncabezado.= $tipoOrdBansta;
            $cadEncabezado.= $refBansta;
            $cadEncabezado.= $bcoCod;
            $cadEncabezado.= $rifEmpresa;
            $cadEncabezado.= $nobEmpresa;
            $data = [
                'cod' => '00',
                'cadena' => $cadEncabezado,
            ];
        }else{
            $data = [
                'cod' => '01',
                'cadena' => 'SIN ENCABEZADO',
            ];    
        } 
        return $data;   
    }                           


    public function detalleBansta($idArchivo){
        $arrgDetalle = Archivo_Detalle::where('id_archivo',$idArchivo)
                         ->get();
        $cadDetalle = '';
        if(count($arrgDetalle) > 0){
            foreach ($arrgDetalle as $detalle) {
                $cadDetalle.= "\n02";

                //Preguntar y establecer segun Ind tipo referncia
                $indTipoRef = 'CD';
                //Preguntar y establecer segun Ind tipo referncia


                $indTipoRef = str_pad(trim($indTipoRef), 3, " ", STR_PAD_RIGHT);
                $nroReferencia = str_pad(trim($detalle->nro_referencia), 15, " ", STR_PAD_RIGHT);
                $fecTarjeta = '';
                $fecTarjeta = str_pad(trim($fecTarjeta), 6, " ", STR_PAD_RIGHT);

                //Preguntar y establecer segun Ind tipo referncia
                //$fecAplicacion = trim($detalle->fecha_pago);
                //$fecAplicacion = substr($fecAplicacion, 0,4) . substr($fecAplicacion, 5,2) . substr($fecAplicacion, 8,2);
                $fecAplicacion = '';
                if(!is_null($detalle->fec_valid_pago)){
                    $fecAplicacion = $detalle->fec_valid_pago;
                }else if(!is_null($detalle->fecha_pago)){
                    $fecAplicacion = $detalle->fecha_pago;
                }
            
                if(!empty($fecAplicacion)){
                    $fecAplicacion = substr($fecAplicacion,0,4).substr($fecAplicacion,5,2).substr($fecAplicacion,8,2);
                }else{
                    $fecAplicacion = date('Ymd');
                }    

                //si lo piden con hora  
                //$fecAplicacion = substr($fecAplicacion,0,4).substr($fecAplicacion,5,2).substr($fecAplicacion,8,2).substr($fecAplicacion,11,2).substr($fecAplicacion,14,2).substr($fecAplicacion,17,2);
                $fecAplicacion = str_pad(trim($fecAplicacion), 8, " ", STR_PAD_RIGHT);
                //Preguntar y establecer segun Ind tipo referncia
                $arrgMonto = explode('.', $detalle->monto_pagar);
                $montoE = str_pad(trim($arrgMonto[0]), 15, "0", STR_PAD_LEFT);
                $montoD = str_pad(trim($arrgMonto[1]), 2, " ", STR_PAD_RIGHT);
                $mtoPago = $montoE . $montoD;
                $moneda = 'VES';
                $rifBene = trim($detalle->tipo_documento) . trim($detalle->nro_documento);
                $rifBene = str_pad(trim($rifBene), 17, " ", STR_PAD_RIGHT);                


                $nroCuenta = str_pad(trim($detalle->cta_receptora), 20, " ", STR_PAD_RIGHT);
                $espacioVacio = '';
                $espacioVacio = str_pad(trim($espacioVacio), 157, " ", STR_PAD_RIGHT);
                //Estatus pago
                $codStatus = str_pad(trim($detalle->cod_error_pago), 3, " ", STR_PAD_RIGHT);
                $descStatus = str_pad(trim($detalle->desc_error_pago), 70, " ", STR_PAD_RIGHT);

                $cadDetalle.= $indTipoRef . $nroReferencia . $fecTarjeta . $fecAplicacion;
                $cadDetalle.= $mtoPago . $moneda . $rifBene . $nroCuenta . $espacioVacio;
                $cadDetalle.="\n03" . $codStatus . $descStatus;
            }    
            $data = [
                'cod' => '00',
                'cadena' => $cadDetalle,
            ];                
        }else{
            $data = [
                'cod' => '01',
                'cadena' => 'SIN DETALLE',
            ];             
        }
        return $data;
    }


    public function generarArcBansta($nobArchivo, $cadenaBansta)
    {
        $nobArchivo = trim($nobArchivo);
        $path = '/archivoBansta/' . $nobArchivo;
        try {
            Storage::disk('archivos')->put($path, $cadenaBansta);
            return response()->json([
                'success' => true,
                'message' => 'archivo Creado',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el archivo: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function cambiarStatusArchivoRecibido($idArchivo, $nombreArchivo){
        $arrgArchivo = Archivo_Recibido::find($idArchivo);

        $arrgArchivo->nivel_proceso = 6;
        $arrgArchivo->imp_bansta = 1;
        $arrgArchivo->archivo_bansta = $nombreArchivo;
        $arrgArchivo->save();
        return true;
    }


 /*   public function cambiarStatusArchivoDescarga($idDescarga){
        $arrgArchivo = Archivo_Recibido::where('id_archivo_descarga',$idDescarga)
                            ->where('nivel_proceso','<>', 6)
                            ->get();



        if(count($arrgArchivo) == 0){
            $arrgDescarga = Archivo_Descarga::find($idDescarga);
            if($arrgDescarga->status_descarga == 'P'){
                $arrgDescarga->status_descarga = 'C';
                $arrgDescarga->save();
            }
        }
        return true;


    } 
*/    

    //fin del trait
}
