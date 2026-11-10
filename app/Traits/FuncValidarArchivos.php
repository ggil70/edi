<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Importante importar esto para trabajar con archivo fisico

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

use App\Models\Archivo_Recibido;
use App\Models\Log_Error_Archivo;
use App\Models\Archivo_Descarga;
use App\Models\Simf_Error;

use SoapClient; // Usar la clase nativa de PHP
use SoapFault; // Importa la clase SoapFault para manejar excepciones SOAP

//use Illuminate\Support\Facades\Log;

use Exception;

trait funcValidarArchivos
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    //librerias para as400 para identificacion cliente
    use AS400_3010;
    //EnviarCorreoEdi EDI
    use MailEdi;     

    public function validacionArchivo($arrgArchivos, $idArchivoDescarga){

        //Validar estructura del nombre del archivo
        $archivoOK = 0;
        $archivoError = 0;
        foreach ($arrgArchivos as $archivo) {
            //validar archivo .txt
            $extension = "";
            $validaArchivo = explode(".", $archivo);

            if(strlen($validaArchivo[0])!=31){
                $archivoError++;
                //cambiar archivo de la carpeta temporal a la carpeta errorArchivo
                //FuncArchivos
                $this->copiarArchivoError($archivo); 
                //registrar en log de archivo
                $mensaje = strlen($validaArchivo[0]);
                $motivo = Simf_Error::mensajeError('103',$mensaje);
                $motivo.= ' [CARACTERES]';
                $this->guardarArchivoRec($archivo, '1', $motivo, $idArchivoDescarga);
                $res = Log_Error_Archivo::guardarErrorArchivo($idArchivoDescarga, $archivo, $motivo);
                $this->enviarEmail('FUNCIONAL',$archivo, $motivo);
            }else{
                if (isset($validaArchivo[1])) {
                    $extension = trim(strtolower($validaArchivo[1]));
                }
                if ($extension == 'txt') {
                    $arrayResultadoArchivo = $this->validarArchivo($archivo);
                    if ($arrayResultadoArchivo['codError'] != "00") {
                        $archivoError++;
                        //cambiar archivo de la carpeta temporal a la carpeta errorArchivo
                        //FuncArchivos
                        $this->copiarArchivoError($archivo); 
                        //registrar en log de archivo
                        $motivo = $arrayResultadoArchivo['descError'];
                        $this->guardarArchivoRec($archivo, '2', $motivo, $idArchivoDescarga);
                        $res = Log_Error_Archivo::guardarErrorArchivo($idArchivoDescarga, $archivo, $motivo);
                        $this->enviarEmail('FUNCIONAL',$archivo, $motivo);
                    }else{
                        //guardar estructura nombre archivo OK
                        $archivoOK++;
                        //$motivo = $arrayResultadoArchivo['descError'];
                        $motivo = '100 - ARCHIVO VALIDO';    
                        $this->guardarArchivoRec($archivo, '0', $motivo, $idArchivoDescarga);
                    }
                } else {
                    $archivoError++;
                    //cambiar archivo de la carpeta temporal a la carpeta errorArchivo
                    //FuncArchivos
                    $this->copiarArchivoError($archivo); 
                    //registrar en log de archivo
                    $motivo = Simf_Error::mensajeError('104',$archivo);
                    $this->guardarArchivoRec($archivo, '1', $motivo, $idArchivoDescarga);
                    $res = Log_Error_Archivo::guardarErrorArchivo($idArchivoDescarga, $archivo, $motivo);
                    $this->enviarEmail('FUNCIONAL',$archivo, $motivo);
                } 
            }    
        } 

        //modificar archivo_descarga para totalizar archivos
        $swArchivo = 0;
        $archivoDescarga = Archivo_Descarga::find($idArchivoDescarga);

        if ($archivoOK == 0) {
            $archivoDescarga->status_descarga = 'X';
            $archivoDescarga->mot_status = 'TODOS LOS ARCHIVOS DESCARGADOS SON INVALIDOS';
            $swArchivo = 1;
        }
        //$archivoDescarga->nro_archivo = $archivoOK + $archivoError;
        $archivoDescarga->nro_archivo_ok = $archivoOK;
        $archivoDescarga->nro_archivo_error = $archivoError;
        $archivoDescarga->save();
        return $swArchivo;
    }


    ///////////////////////////////////////////////////////////////////////////
    //Permite validar el archivo según su composición
    public function validarArchivo($archivo){
        //buscar banco
        $archivo = trim($archivo);
        $nombreBase = strtoupper(substr($archivo, 0, 6));
        $rifPagador = strtoupper(substr($archivo, 6, 10));
        $docPagador = trim(substr($archivo, 16, 15));
        Session::put('ordenPago', $docPagador);  

        //validar estructura nombre archivo
        $valNombreArc = $this->valNombreArchivo($rifPagador, $docPagador, $nombreBase, $archivo);
        if ($valNombreArc['codError'] != '00') {
            $dataError = [
                'codError' => '01',
                //'descError' => 'ERROR NOMBRE DEL ARCHIVO [ ' . $valNombreArc['descError'] . ' ]',
                'descError' => $valNombreArc['descError'],
            ];
            return $dataError;
        } else {
            $dataError = [
                'codError' => '00',
                'descError' => 'NOMBRE DEL ARCHIVO VALIDO',
            ];
            return $dataError;
        }
    }


    public function valNombreArchivo($rif, $nroOrden, $nombreBase, $archivo)
    {
        $tipoDocumento = substr($rif, 0, 1);
        $nroDocumento =  substr($rif, 1);
        $descripcion = '';
        $swError = 0;
        $motivo = '';


        //Nombre base PAYMUL
        if($nombreBase != 'PAYMUL'){
            $swError = 1;
            $descripcion = Simf_Error::mensajeError('105', $nombreBase);  

            //$descripcion .= 'EL NOMBRE BASE PAYMUL ES INVALIDO [ ' . $nombreBase . ' ]';
        } 

        //validar tipo documento
        //todas las validaciones van contra FuncValidarCampos
        $swError = $this->valTipoDocumento($tipoDocumento); 
        if($swError == 1){
            if(!empty($descripcion)){
                $descripcion.= ' - ';
            }
            $descripcion.= Simf_Error::mensajeError('106', $tipoDocumento);
        }

        //validar nro documento
        $swError = $this->valDigitos($nroDocumento);
        if ($swError == 1) {
            if (!empty($descripcion)) {
                $descripcion .= ' - ';
            }
            //$descripcion .= 'NUMERO DOCUMENTO INVALIDO [ ' . $nroDocumento . ' ]';
            $descripcion .= Simf_Error::mensajeError('107', $nroDocumento);
        }

        //AS400_3010
        $swError = $this->consultaCliente($tipoDocumento, $nroDocumento); 
        
        if($swError['error'] == '01'){
            if(!empty($descripcion)){
                $descripcion.= ' - ';
            }
            //$descripcion .= 'EL CLIENTE NO EXISTE EN EL CORE BANCARIO [ ' . $tipoDocumento . $nroDocumento . ' ]';
            $documento = $tipoDocumento . $nroDocumento;
            $descripcion .= Simf_Error::mensajeError('108', $documento);
        }

        //Si no hay error en la identificacion del cliente
        if (empty($descripcion)) {
            $swError = $this->valDigitos($nroOrden);
            if ($swError == 1) {
                if (!empty($descripcion)) {
                    $descripcion .= ' - ';
                }
                $descripcion .= Simf_Error::mensajeError('109', $nroOrden);
            } else {
                //Verificar que el nro de la orden no este cancelada
                $respNroOrden = Archivo_Recibido::where('rif_pagador', $rif)
                    ->where('nro_documento', $nroOrden)
                    ->where('error_archivo', 0)
                    ->first();
                if ($respNroOrden) {
                    $descripcion .= Simf_Error::mensajeError('110', $nroOrden);
                }
            }
        }


        if (empty($descripcion)) {
            $dataError = [
                'codError' => '00',
                'descError' => 'VALIDACION EXITOSA',
            ];
        } else {
            $dataError = [
                'codError' => '01',
                'descError' => $descripcion,
            ];
        }
        return $dataError;
    }

    public function guardarArchivoRec($archivo, $errorArchivo, $motivo, $idArchivoDescarga){
        $rifPagador = strtoupper(substr($archivo, 6, 10));
        $tipoDocumento = substr($rifPagador, 0, 1);
        $nroDocumento = substr($rifPagador, 1);
        $docPagador = trim(substr($archivo, 16, 15));
        $archivoRecibido = new Archivo_Recibido();
        $archivoRecibido->id_archivo_descarga = $idArchivoDescarga;
        $archivoRecibido->nob_archivo = $archivo;
        $archivoRecibido->nro_documento = $docPagador;  
        $archivoRecibido->fec_proceso = date("Y-m-d H:i:s"); 
        $archivoRecibido->rif_pagador = $rifPagador; 
        $archivoRecibido->nivel_proceso = 1; 
        $archivoRecibido->error_archivo = $errorArchivo; 
        $archivoRecibido->desc_error = $motivo;                
        if($errorArchivo != 0){
           $archivoRecibido->status_proceso = 2;
           $archivoRecibido->ruta_archivo = 'conErrores';
        }

        $archivoRecibido->save();
    }

    //fin del trait
}
