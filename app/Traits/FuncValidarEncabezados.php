<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Importante importar esto para trabajar con archivo fisico

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\Archivo_Recibido;
use App\Models\Archivo_Descarga;
use App\Models\Cliente;
use App\Models\Archivo_Encabezado;
use App\Models\Log_Error_Proceso;

use App\Models\Proveedor_Limite;
use App\Models\Proveedor_Acumulado;
use App\Models\Contador;
use App\Models\Simf_Error;
use App\Models\Log_Error_Archivo;


/*

use App\Models\Archivo_Detalle;

*/

use Illuminate\Support\Facades\Session;

/*use SoapClient; // Usar la clase nativa de PHP
use SoapFault; // Importa la clase SoapFault para manejar excepciones SOAP
*/

//use Illuminate\Support\Facades\Log;

use Exception;

trait funcValidarEncabezados
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */
    use AS400ValidarPago;
    use AS400Cuentas;
    use Whatsapp;
    //EnviarCorreoEdi EDI
    use MailEdi;  



    //Permite validar el archivo según su composición
    public function validarEncabezadosArchivos($idDescarga, $idArchivo, $reproceso){
        
        //Validar funcionamiento del AS400 antes de procesar la validacion del archivo
        //de no haber conexion se detiene el proceso.
        //Activar al culminar las pruebas


        $this->validarAS400();

        //obtener los archivos recibidos sin haber validado el encabezado
        if($reproceso == 0){
            $nivelProceso = 1;
            $errorArchivo = 0;
            $detalle = 0;
            $resArchivos = Archivo_Recibido::obtenerArchivoRecibido($idDescarga, $nivelProceso, $errorArchivo,$detalle);
        }else{
            $resArchivos = Archivo_Recibido::obtenerArchivorReproceso($idArchivo,0);
        }

        if($resArchivos['respuesta']=='0'){
            $respValArchivo = 0;
            $archivoOK = 0;
            $archivoError = 0;
            foreach ($resArchivos['arrgData'] as $archivo){

                $idArchivo  = $archivo->id;
                $nobArchivo = trim($archivo->nob_archivo);
                $rifPagador = trim($archivo->rif_pagador);
                $docPagador = trim($archivo->nro_documento);

                //$rutaArchivo  = storage_path('app\archivos\temporales\\' . $nobArchivo);
                $rutaArchivo = storage_path('app/archivos/temporales/' . $nobArchivo);
                if (File::exists($rutaArchivo)) {
                    $lineas = File::lines($rutaArchivo); 
                    $archivoLinea=$lineas;
                    $numeroLinea = 0;
                    $errorEncabezado=0;
                    foreach($lineas as $linea){
                        $numeroLinea = $numeroLinea + 1;
                        $linea = trim($linea); 
                        if(!empty($linea)){
                            $arrayLinea = explode(';', $linea);
                            if(count($arrayLinea)!=11){
                                $archivoError++;
                                $respError =$this->errorEncabezadoEstructura($idDescarga, $idArchivo, $nobArchivo, count($arrayLinea));


                                $motivoError = 'LOS CAMPOS DEL ENCABEZADO INCOMPLETO [ ' . count($arrayLinea) . ' ]';    
                                Log_Error_Archivo::guardarErrorArchivo($idDescarga, $nobArchivo, $motivoError);

                                //trait mail

                                $this->enviarEmail('FUNCIONAL',$nobArchivo, $motivoError);

                                //FuncArchivos
                                $this->copiarArchivoError($nobArchivo);  
                                $errorEncabezado=1;
                                break;
                            }else{  

                                $arrayEncabezado = $this->validarEncabezado($arrayLinea, $rifPagador, $numeroLinea, $archivoLinea);
                                if($arrayEncabezado['error'] != '200'){
                                    $errorEncabezado = 1;
                                    $archivoError++;
                                }else{
                                    $archivoOK++;
                                }
                                //Guardar registro archivo
                                //almacenar el nombre del archivo ha procesar 
                                //y la información asociada.
                                $this->guardarDatosArchivo($idDescarga, $nobArchivo, $rifPagador, $arrayLinea[3], $docPagador, $arrayLinea[8], $arrayLinea[9], $errorEncabezado, $arrayEncabezado['error'],$arrayEncabezado['error'], $arrayEncabezado['descripcion'], $idArchivo);

                                //Guardar datos encabezado
                                $idEncabezado = $this->guardarDatosEncabezado($idArchivo, $arrayLinea, $linea, $arrayEncabezado['error'], $arrayEncabezado['descripcion'], $numeroLinea, $docPagador);
                                break;
                            }
                        }
                    }  //fin del ciclo lineas            

                } //fin de apertura archivo                   
            } //Fin ciclo de archivo
        }  //fin de if  $resArchivos['error']=='0'  

        //VAlidar existencia de encabezados validos
        $swEncabezado = 0;
        $archivoDescarga = Archivo_Descarga::find($idDescarga);
        $archivoDescarga->nro_archivo_ok= $archivoOK;
        $archivoDescarga->nro_archivo_error= $archivoError;
        
        if($archivoOK == 0){
            $archivoDescarga->status_descarga = 'X';
            $archivoDescarga->mot_status = 'TODOS LOS ARCHIVOS DESCARGADOS SON INVALIDOS';
            $swEncabezado = 1;
        } 
        $archivoDescarga->save();
        return $swEncabezado;     

    } //fin function validarEncabezadosArchivos  

    public function errorEncabezadoEstructura($idArchivoDescarga, $idArchivo, $archivo, $nroCampo){
        //registrar log archivo
        $motivo = "290;" . Simf_Error::mensajeError('290', $nroCampo);
        $actArchivo = Archivo_Recibido::find($idArchivo);
        $actArchivo->status_proceso = '1';
        $actArchivo->error_archivo = 4;
        $actArchivo->desc_error = $motivo;
        //$actArchivo->arc_descargado = 1;
        $actArchivo->nivel_proceso = 2;
        $actArchivo->ruta_archivo = 'conErrores';
        $actArchivo->save();
                           
        //mover archivo a carpeta conErrror
        //FuncArchivos
        $this->copiarArchivoError($archivo);

        //Modificar tabla archivo_descarga para restarle 1 a nro_archivo_ok
        $actDescarga = Archivo_Descarga::find($idArchivoDescarga);
        $nroArchivo = $actDescarga->nro_archivo_ok - 1;
        $actDescarga->nro_archivo_ok = $nroArchivo;
        $nroArchivoError = $actDescarga->nro_archivo_error + 1;
        $actDescarga->nro_archivo_error = $nroArchivoError;

        if($nroArchivo == 0){
            $actDescarga->status_descarga = "X";
            $actDescarga->mot_status = "LOS ARCHIVOS DESCARGADO PRESENTAN FALLAS";
        }
        $actDescarga->save();
        return 1;
    }  //fin de function errorEncabezadoEstructura




    public function validarEncabezado($arrayLinea, $rifPagador, $numeroLinea, $archivo){
        $error = '';   
        $descripcion = '';

        //validar campos del registro según reglas del negocio
        $descripcion = $this->validarCamposEncabezado($arrayLinea, $rifPagador, $archivo);
        $arrgResp = explode(";", $descripcion);
        
        if($arrgResp[0] == '200'){
            $arreglo = [
                'error'=>'200',
                'descripcion'=>'200 - ENCABEZADO VALIDADO OK'
            ];
        }else{
            $arreglo = [
                'error'=>$arrgResp[0],
                'descripcion'=>$arrgResp[1],
            ];
        }
        return $arreglo;
    }


    public function valNumeroRegistros($archivo, $nroRegistro){
         //Validar si el campo es numerico   
         $error = $this->valDigitos($nroRegistro);
         if($error == 1){
            return 1;
         }

        $nroRegistroDetalle = 0;
        foreach($archivo as $linea){        
            if(!empty($linea)){
                $arrayLinea = explode(';', $linea);

                if(strtoupper($arrayLinea[0]) == 'P'){
                    $nroRegistroDetalle+=1;
                }
            }
        }

        if($nroRegistroDetalle == $nroRegistro){
            return 0;
        }else{
            return 1;
        }        
    }


    public function valMontoRegistros($archivo, $mtoRegistro){
         //Validar si el campo es numerico   
         $error = $this->valDecimales($mtoRegistro);
         if($error == 1){
            return 1;
         }
        $montoDetalle = 0.00;
        foreach($archivo as $linea){        
            if(!empty($linea)){
                $arrayLinea = explode(';', $linea);
                if(strtoupper($arrayLinea[0]) == 'P'){
                    $monto = $arrayLinea[4];
                    $error = $this->valDecimales($monto);

                    if($error == 1){
                        return 1;
                    }
                    $monto = (float) $monto;
                    $montoDetalle+=$monto;
                }
            }
        }
        $mtoRegistro = (float) $mtoRegistro;
        $mtoRegistro = trim($mtoRegistro);
        $montoDetalle = trim($montoDetalle);

        if($montoDetalle == $mtoRegistro){
            return 0;
        }else{
            return 1;
        }        
    }

public function validarCamposEncabezado($arrayLinea, $rifPagador, $archivo){
    $descripcion = "";
    //Validar identificacion del encabezado
    $identificacion  = trim($arrayLinea[0]);
    $identificacion  = strtoupper($identificacion);

    //if (strcmp($identificacion, "H") == 0) {
    if($identificacion != "H"){
        $descripcion = Simf_Error::mensajeError('201', $arrayLinea[0]);
        $descripcion = '201;' . $descripcion;
        return $descripcion;        
    }

    //validar tipoDocumento, rif y cliente. 
    $idCliente = '0';
    $respCliente = $this->valRifCliente($arrayLinea[1], $arrayLinea[2], $arrayLinea[3], $rifPagador);
    $arrgCliente = explode(';', $respCliente);
    if($arrgCliente[0] != '200'){
        return $respCliente;           
    }else{
        $idCliente = $arrgCliente[1]; 
    }

    //Validar fecha valor
    $swError = $this->valFecha($arrayLinea[6]); 

    if($swError > 0){
        if($swError == 1){
            $descripcion = '214;' . Simf_Error::mensajeError('214', $arrayLinea[6]);                    
        }else if($swError == 2){
            $descripcion = '215;' . Simf_Error::mensajeError('215', $arrayLinea[6]);                    
        }    
        return $descripcion; 
    }


    //Validar nro de registros detalles 
    $swError = $this->valNumeroRegistros($archivo, $arrayLinea[8]); 
    if($swError == 1){
        $descripcion = '291;' . Simf_Error::mensajeError('291', $arrayLinea[8]);
        return $descripcion;    
    }

    //Monto total de pago de los registro
    //Falta recorrer el archivo para verificar el monto total a pagra los detalles correspondan con el monto //encabezado    
    //Validar nro de registros detalles 
    $swError = $this->valMontoRegistros($archivo, $arrayLinea[9]); 
    if($swError == 1){
        $descripcion = '292;' . Simf_Error::mensajeError('292', $arrayLinea[9]);
        return $descripcion;    
    }

    //validar tipo de orden si es nomina o pago proveedor
    $swError = $this->valTipoOrden($arrayLinea[10]); 
    if($swError == 1){
        $swMonto = 1;
        $descripcion = '216;' . Simf_Error::mensajeError('216', $arrayLinea[10]);
        return $descripcion;
    }    

    //Validar que el monto Total del pago no exceda el limite acumulado del dia del cliente.
    //validar si el monto total es inferior al acumulado del limite diario


    $swError = $this->valMtoLimite($idCliente, $arrayLinea[9], $arrayLinea[10], $arrayLinea[6]);
    if($swError == 1){
        $descripcion = '217;' . Simf_Error::mensajeError('217', $arrayLinea[9]);
        return $descripcion;
    }

    //validar Cuenta Bancaria
    $respCuenta = $this->valCuentaCliente($arrayLinea[1], $arrayLinea[2], $arrayLinea[4]); 
     


    $arrgCuenta = explode(";", $respCuenta);
    if($arrgCuenta[0] != '200'){
        return $respCuenta;
    }

    //validar Concepto Pago
    if(empty($arrayLinea[5])){ 
        $descripcion = '218;' . Simf_Error::mensajeError('218', $arrayLinea[5]);        
        return $descripcion;
    }

    //validar que el lote Contra la base de datos



    $respLote = $this->valLote($arrayLinea[1], $arrayLinea[2], $arrayLinea[7],$arrayLinea[4], $arrayLinea[8], $arrayLinea[9], $arrayLinea[10], $arrayLinea[6]);
   
    $arrgLote = explode(";", $respLote);
    if($arrgLote[0] != '00'){
        return $respLote;
    }


    //Si no ahi error ok
    $descripcion = "200;200 - ENCABEZADO OK";
    return $descripcion;
    
 }


public function validarLoteBD($tDocumento, $nDocumento, $lote){
        $tDocumento = trim($tDocumento);
        $nDocumento = trim($nDocumento);

        $archivoEncabezado = Archivo_Encabezado::where('tipo_documento',$tDocumento)
                                 ->where('nro_documento',$nDocumento)
                                 ->where('lote',$lote)
                                 ->first();
        if($archivoEncabezado){
            return 1;
        }else{
            return 0;
        }

}


public function guardarDatosEncabezado($idArchivo, $arrayLinea, $lineaEncabezado, $errorEncabezado, $errorDescripcionEncabezado, $nroLinea, $docPagador){
    
    //Generar referencia de banco bansta
    $refBansta = Contador::where('contador','REF_BANSTA')->first();
    $refBancoBansta = $refBansta->valor +1;
    $refBancoBanstaF = str_pad($refBancoBansta, 8, "0", STR_PAD_LEFT);
    $refBansta->valor = $refBancoBansta;
    $refBansta->save();

    //Guardar registro encabezado

    $archivoEncabezado = new Archivo_Encabezado();
    $archivoEncabezado->id_archivo = $idArchivo;
    $archivoEncabezado->tipo_documento = trim($arrayLinea[1]);
    $archivoEncabezado->nro_documento = substr($arrayLinea[2],0,20);
    $archivoEncabezado->nob_empresa = $arrayLinea[3];
    $archivoEncabezado->cta_pagador = substr($arrayLinea[4],0,20);                        
    $archivoEncabezado->concepto_pago = $arrayLinea[5];  
    $fechaPago = $arrayLinea[6];

    $archivoEncabezado->nro_ref_orden_bansta = $docPagador;  
    $archivoEncabezado->nro_ref_banco_bansta = $refBancoBanstaF;   

    $archivoEncabezado->fecha_pago = $fechaPago;  

    $lote = trim($arrayLinea[7]);
    $lote = str_pad($lote,15,0, STR_PAD_LEFT);
    $lote = substr($lote, 6);

    $archivoEncabezado->lote = $lote;
    $archivoEncabezado->total_registro = $arrayLinea[8];
    $archivoEncabezado->total_pago = $arrayLinea[9];
    $archivoEncabezado->linea = $lineaEncabezado;
    $archivoEncabezado->nro_linea = $nroLinea;

    if($errorEncabezado != '200'){
        $archivoEncabezado->error_encabezado = 1;
    }else{
        $archivoEncabezado->error_encabezado = 0;
    }    

    $archivoEncabezado->cod_error_encabezado = $errorEncabezado;
    $archivoEncabezado->desc_error_encabezado = $errorDescripcionEncabezado;
    $archivoEncabezado->tipo_pago = $arrayLinea[10];

    if($errorEncabezado == '200'){
        $fechaValor = substr($fechaPago,4,4) . "-" . substr($fechaPago,2,2) . "-" . substr($fechaPago,0,2);
        $archivoEncabezado->fecha_valor = $fechaValor;
    }  

    $archivoEncabezado->save();
    return $archivoEncabezado->id;
}


//Permite guardar el registro del archivo
public function guardarDatosArchivo($idArchivoDescarga, $archivo, $rifPagador, $nobCliente, $docPagador, $nroRegistro, $mtoArchivo, $errorArchivo, $descErrorArchivo,$errorEncabezado, $descErrorEncabezado, $idArchivo){

    //validar existencia del cliente
    $tipoDocumento = trim(substr($rifPagador,0,1));
    $nroDocumento = trim(substr($rifPagador,1));
    $idCliente = '0';

    $consultaCliente = Cliente::where('tipo_documento', $tipoDocumento)
                           ->where('nro_documento',$nroDocumento)
                           ->first();
    if($consultaCliente){
        $idCliente = $consultaCliente->id;
    }     

    Session::put('idCliente',$idCliente);  

    //ubicar archivo en archivo_recibidos
    
    $archivoRecibido = Archivo_Recibido::where('id', $idArchivo)
                           ->first();
    if($archivoRecibido){                       
        $archivoRecibido->id_cliente = $idCliente;
        $archivoRecibido->nro_documento = $docPagador;  
        $archivoRecibido->nivel_proceso = 2;

        if($errorEncabezado != '200'){
            $errorArchivo = 1; 
            $archivoRecibido->error_archivo = 3;
            $archivoRecibido->desc_error = $descErrorEncabezado;
            $archivoRecibido->status_proceso = 1;
        }else{
            $errorArchivo = 0;
            $archivoRecibido->tot_monto_pago = $mtoArchivo; 
            $archivoRecibido->nro_registro = $nroRegistro; 
        }    
        $archivoRecibido->save();
        return 0;
    }  
}




    //validar el monto total de pago versus al acumulado maximo del dia
    public function valMtoLimite($idCliente, $mtoTotal, $tipoPago, $fecha){
    $fechaValor = substr($fecha,4,4) . "-" . substr($fecha,2,2) . "-" . substr($fecha,0,2);
    $acumulado = 0.00;
    $limite = 0.00;                                      

    //traer los montos acumulados de los pagos del dia tanto nomina, proveedor
    $arrayAcumulado = Proveedor_Acumulado::where('id_cliente', $idCliente)
                                         ->where('fec_acumulado', $fechaValor)
                                         ->first();

    if($arrayAcumulado){
        if($tipoPago == 'SAL'){
            $acumulado = $arrayAcumulado->mto_acum_nomina;
            $limite = $arrayAcumulado->mto_max_nomina;                                      
        }else{
            $acumulado = $arrayAcumulado->mto_acum_proveedor;
            $limite = $arrayAcumulado->mto_max_proveedor;                                      
        }
        $acumulado = $limite - $acumulado;
        if($mtoTotal > $acumulado){
            return 1;
        }  
    }else{
        $arrayLimite = Proveedor_Limite::where('id_cliente', $idCliente)
                             ->first();
        if($arrayLimite){
            if($tipoPago == 'SAL'){
                $limite = $arrayLimite->lim_max_nomina;
            }else{
                $limite = $arrayLimite->lim_max_proveedor;
            } 
            if($mtoTotal > $limite){
                return 1;
            }    
        }else{
            return 0;
        }    
    }                                       

} 


    public function valRifCliente($tDocumento, $nDocumento, $nEmpresa, $rifPagador){
        //validar Tipo documento  
        $descripcion = '';
        $swError = $this->valTipoDocumento($tDocumento); 
        if($swError == 1){
            $descripcion = '202;' . Simf_Error::mensajeError('202', $tDocumento);
            return $descripcion;
        }

        //validar nro documento
        $swError = $this->valDigitos($nDocumento); 
        if($swError == 1){
            $descripcion = '203;' . Simf_Error::mensajeError('203', $nDocumento);
            return $descripcion;
        }   

        //Validar que el rif del nombre del archivo con el rif cliente pagador del encabezado
        $rif = trim($tDocumento) . trim($nDocumento);
        $rif = strtoupper($rif);
        $rifPagador = strtoupper(trim($rifPagador));

        if($rif != $rifPagador){
            $descripcion = '204;' . Simf_Error::mensajeError('204', $rif);
            return $descripcion;
        }

        //Si la validacion del rif es valida, se procede a revisar si ese cliente existe en la Base de datos, 
        //en la tabla cliente, de no existir crear el mismo

        //validar Concepto Pago
        if(empty($nEmpresa)){ 
            $descripcion = '205;' . Simf_Error::mensajeError('205', $nEmpresa);
            return $descripcion; 
        }    

        //Verificar si el cliente esta ingresado en la tabla cliente, si no esta lo agrega
        $descripcion = $this->validarCliente($tDocumento, $nDocumento, $nEmpresa);
        return $descripcion;
    }


    public function validarCliente($tdocumento, $nDocumento, $nobCliente){
        $resCliente = Cliente::where('tipo_documento',$tdocumento)
                             ->where('nro_documento', $nDocumento)
                             ->first();

        $rif = $tdocumento . $nDocumento;                     
        if(!$resCliente){
            $descripcion = '206;' . Simf_Error::mensajeError('206', $rif);
        }else{
            $descripcion = '200;' . $resCliente->id;
        }                     
        return $descripcion;
    }


    function valCuentaCliente($tDocumento, $nDocumento, $nroCuenta){
        $swError = $this->valCuentaBancoActivo($nroCuenta); 
        if($swError > 0){
            if($swError == 1){
                $descripcion = '207;' . Simf_Error::mensajeError('207', $nroCuenta);
             }else{
                $descripcion = '208;' . Simf_Error::mensajeError('208', $nroCuenta);
             }  
            return $descripcion;
        }else{
            //validar si la cuenta esta en el core bancario
            $tDocumento = trim($tDocumento);
            $nDocumento = trim($nDocumento);
            $identificacion = $tDocumento . str_pad($nDocumento, 9, "0", STR_PAD_LEFT);
            //Consulta las cuentas AS400Cuentas
            $dataCuenta = $this->obtenerCtasCliente($identificacion);

            if($dataCuenta['error'] == '98' || $dataCuenta['error'] == '99'){
                $codOperacion = 'AS400';                
                $motivoError = $dataCuenta['respuesta'] . ' / ' . $dataCuenta['mensaje'];
                $respError = Log_Error_Proceso::guardarErrorProceso($codOperacion, $motivoError); 
                $this->enviarEmail("AS400","", $motivoError);                
                exit();
            }else{
                if($dataCuenta['error'] ==0){
                    $descripcion = '208;' . Simf_Error::mensajeError('208', $nroCuenta);
                    return $descripcion;
                }else{
                    $ctaActiva = 0;
                    foreach($dataCuenta['respuesta'] as $cuentas){
                        if($cuentas['SUMACN'] == $nroCuenta && $cuentas['SUMAST'] == 'A' && $cuentas['SUMCCY'] == 'VEB'){
                            $ctaActiva = 1;
                        }
                    }
                    if($ctaActiva == 0){
                        $descripcion = '208;' . Simf_Error::mensajeError('208', $nroCuenta);
                        return $descripcion;
                    } 
                }    
            }
        }  //fin if($swRif == 0){      
        $descripcion = "200;Valido";
        return $descripcion;    
    }


    public function valLote($tDocumento, $nDocumento, $lote, $cuenta, $nRegistro, $mTotal, $tPago, $fecPago){
        //validar lote
        $lote = trim($lote);
        $lote = substr($lote, 6);

    
        $swError = $this->valDigitos($lote); 
        if($swError == 1){
        $descripcion = '210;' . Simf_Error::mensajeError('210', $lote);    
            return $descripcion;
        }

        //Validar que lote no exista en la base de datos
        $swError = $this->validarLoteBD($tDocumento, $nDocumento, $lote);
        if($swError == 1){
            $descripcion = '211;' . Simf_Error::mensajeError('211', $lote);
            return $descripcion;
        }

        //validar que el lote a procesar no se haya procesado en el as400

        //formato fecha para validar lote as400
        $fechaAS400 = substr($fecPago,4,4) . '-' . substr($fecPago,2,2) . "-" . substr($fecPago,0,2);

        $arrgLote = $this->validarPagoAS400($tDocumento, $nDocumento, $lote, $cuenta, $nRegistro, $mTotal, $tPago, $fechaAS400);
        
        if($arrgLote['error'] != '00' ){
            if($arrgLote['error'] != '98' && $arrgLote['error'] != '99' && $arrgLote['error'] != '00'){
                if($arrgLote['error'] == '0215'){
                    $descripcion = '212;' . Simf_Error::mensajeError('212', $lote);
                }else if($arrgLote['error'] == '1616'){
                    $descripcion = '213;' . Simf_Error::mensajeError('213', $lote);                    
                }
                return $descripcion;
            }else{

                $codOperacion = 'AS400';                
                $motivoError = $arrgLote['respuesta'] . ' / ' . $arrgLote['mensajeTecnico'];
                $respError = Log_Error_Proceso::guardarErrorProceso($codOperacion, $motivoError); 
                $this->enviarEmail("AS400","", $motivoError);
                $descripcion = '211;' . Simf_Error::mensajeError('211', $lote);
                return $descripcion;                
            }    
        }else{
            $descripcion= '200;200 - Validado';     
            return $descripcion;
        } 


    }  //Fin lote    


//fin del trait
}    

