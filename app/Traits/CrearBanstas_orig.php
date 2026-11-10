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
        $swDescarga = 0;
        $arrgDescarga = Archivo_Descarga::where('status_descarga','<>', 'C')
                             ->get();
        if(count($arrgDescarga) > 0){
            foreach ($arrgDescarga as $descarga) {
                $swDescarga = 1;
                $swArchivo = 0;
                $idDescarga = $descarga->id;
                $arrgArchivo =  Archivo_Recibido::where('imp_bansta',0)
                    ->where('nivel_proceso', 5)
                    ->orWhere(function ($query) {
                    $query->where('nivel_proceso', 3)
                    ->where('error_archivo','<>',1);
                })->get();
                if(count($arrgArchivo) > 0){
                    foreach ($arrgArchivo as $archivo) {
                        $swArchivo = 1;
                        $idArchivo = $archivo->id;
                        $rifPagador = $archivo->rif_pagador;
                        $nroOrden = $archivo->nro_documento;
                        $respEncabezado = $this->encabezadoBansta($idArchivo);
                        if($respEncabezado['cod'] = '00'){
                            $encabezado = $respEncabezado['cadena'];
                            $respDetalle = $this->detalleBansta($idArchivo);
                            if($respDetalle['cod'] = '00'){
                                $detalle = $respDetalle['cadena'];
                                //guardar archivo carpeta bansta
                                $cadenaBansta = $encabezado . $detalle;
                                $nombreArchivo = 'bansta'. trim($archivo->rif_pagador) . trim($archivo->nro_documento). '.txt'; 
                                $this->generarArcBansta($nombreArchivo, $cadenaBansta);
                                ////////////////////////////////////////////
                                //Guardar archivo en carpeta FTP de salida
                                //Activar en el trabajo
                                $respFTP = true;  //quitar al activar rutima ftp
                                //$respFTP = $this->copiarBanstaFtp($nombreArchivo); 
                                ////////////////////////////////////////////

                                if($respFTP == true){
                                    //CAmbiar nivel de proceso y estatus de proceso
                                    $respArchivoRecibido = $this->cambiarStatusArchivoRecibido($idArchivo);
                                    //CAmbiar nivel de proceso y estatus de proceso
                                    $respArchivoDescarga = $this->cambiarStatusArchivoDescarga($idDescarga);
                                    $data = [
                                        'cod' => '600',
                                        'desc' => 'ARCHIVO BANSTA / ' . $rifPagador . ' / ' . $nroOrden . ' CREADO SATISFACTORIAMENTE',
                                        'rif' => $rifPagador,
                                        'nroOrden' => $nroOrden,                                    
                                    ];  
                                    $respError = Log_Crear_Bansta::guardarBansta($data); 


                                }else{
                                    //Error servidor FTP al copiar el archivo
                                    $data = [
                                        'cod' => '602',
                                        'desc' => 'FALLA AL COPIAR ARCHIVO BANSTA EN LA CARPETA FTP',
                                        'rif' => $rifPagador,
                                        'nroOrden' => $nroOrden,                                    
                                    ];
                                    $respError = Log_Crear_Bansta::guardarBansta($data);
                                    //////////////////////////////////////////
                                    //Mandar mensaje Whataspp a servidores
                                    //////////////////////////////////////////

                                    exit();

                                }                                                            

                            }else{
                                $data = [
                                    'cod' => '603',
                                    'desc' => 'ARCHIVO SIN DETALLE / ' . $rifPagador . ' / ' . $nroOrden,
                                    'rif' => $rifPagador,
                                    'nroOrden' => $nroOrden,
                                ];
                                $respError = Log_Crear_Bansta::guardarBansta($data);

                                exit();
                            }
                        }else{
                           $data = [
                                'cod' => '603',
                                'desc' => 'ARCHIVO SIN DETALLE / ' . $rifPagador . ' / ' . $nroOrden,
                                'rif' => $rifPagador,
                                'nroOrden' => $nroOrden,
                            ];
                            $respError = Log_Crear_Bansta::guardarBansta($data);

                            exit();
                        }
                    } //Fin foreach Archivo
                } //fin validar archivos 
                else{
                   $data = [
                        'cod' => '605',
                        'desc' => 'ARCHIVO SIN DETALLE / ' . $rifPagador . ' / ' . $nroOrden,
                        'rif' => $rifPagador,
                        'nroOrden' => $nroOrden,
                    ];
                    $respError = Log_Crear_Bansta::guardarBansta($data);
                   exit();                     
                }   
            }  //Fin foreach Descarga
        }else{
           $data = [
                'cod' => '601',
                'desc' => 'SIN ARCHIVOS PARA GENERAR EL BANSTA',
                'rif' => '',
                'nroOrden' => '',
            ];
            $respError = Log_Crear_Bansta::guardarBansta($data);
           exit();
        } 

    }

    public function encabezadoBansta($idArchivo){
        //datos encabezado
        $arrgEncabezado = Archivo_Encabezado::where('id_archivo',$idArchivo)
                               ->first();
        if($arrgEncabezado){
            $nroRefBanco = str_pad(trim($arrgEncabezado->nro_ref_banco_bansta), 35, " ", STR_PAD_RIGHT);
            $fecRefBanco = str_pad(trim($arrgEncabezado->fec_status_banco_bansta), 14, " ", STR_PAD_RIGHT);
            $tipoOrdBansta = str_pad(trim($arrgEncabezado->tipo_pago), 3, " ", STR_PAD_RIGHT);
            $refBansta = str_pad(trim($arrgEncabezado->nro_ref_orden_bansta), 15, " ", STR_PAD_RIGHT); 
            $bcoCod = '0171';
            $bcoCod = str_pad($bcoCod, 12, " ", STR_PAD_RIGHT);
            $rifEmpresa = trim($arrgEncabezado->tipo_documento) . trim($arrgEncabezado->nro_documento);
            $rifEmpresa = str_pad($rifEmpresa, 17, " ", STR_PAD_RIGHT); 
            $nobEmpresa = str_pad(trim($arrgEncabezado->nob_empresa), 70, " ", STR_PAD_RIGHT); 


            //Construir Encabezado
            $cadEncabezado = '01';
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


    public function cambiarStatusArchivoRecibido($idArchivo){
        $arrgArchivo = Archivo_Recibido::find($idArchivo);
        if($arrgArchivo->status_proceso == '0'){
            $arrgArchivo->status_proceso = 2;
        }
        $arrgArchivo->nivel_proceso = 5;
        $arrgArchivo->save();
        return true;
    }


    public function cambiarStatusArchivoDescarga($idDescarga){
        $arrgArchivo = Archivo_Recibido::where('id_archivo_descarga',$idDescarga)
                            ->where('nivel_proceso','<>', 5)
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

    //fin del trait
}
