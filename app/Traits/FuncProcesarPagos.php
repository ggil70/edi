<?php

namespace App\Traits; // Solo debe quedar este namespace

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Archivo_Recibido;   
use App\Models\Archivo_Encabezado; 
use App\Models\Archivo_Detalle;    
use App\Models\Log_Error_Proceso;  
use App\Models\Archivo_Descarga; 
use App\Models\Proveedor_Acumulado; 
use App\Models\Proveedor_Limite; 
use App\Models\Simf_Error;



use Exception;

trait FuncProcesarPagos
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    //Permite validar los campos según reglas del negocio
    use AS400_validar;
    use Whatsapp;
    use AS400Banco;

    public function procesarPagos($idDescarga, $idArchivo, $reproceso ){
        //Validar funcionamiento del AS400 antes de procesar la validacion del archivo
        //de no haber conexion se detiene el proceso.

        //Activar al culminar las pruebas
        //trait AS400_validar
        $this->validarAS400();

        if($reproceso == 0){
            $nivelProceso = 3;
            $errorArchivo = 0;
            $detalle = 0;
            $arrgArchivoPago = Archivo_Recibido::obtenerArchivoRecibido($idDescarga, $nivelProceso, $errorArchivo,$detalle);
        }else{
            $arrgArchivoPago = Archivo_Recibido::obtenerArchivorReproceso($idArchivo, 0);
        }


        //Si no existen archivos para realizar el pago se detiene el proceso
        if($arrgArchivoPago['respuesta'] == 1){
           return 1;
        }

        $arrayArchivoPago = $arrgArchivoPago['arrgData'];
        //procesar los pagos
        foreach ($arrayArchivoPago as $archivoPago){
            $arrayPago = Archivo_Recibido::query()
                    ->select([
                        'archivo_encabezados.*', 'archivo_recibidos.id_cliente',
                    ])
                    ->join('archivo_encabezados', 'archivo_recibidos.id', '=', 'archivo_encabezados.id_archivo')
                    ->where('archivo_recibidos.id', $archivoPago->id)
                    ->first();

            $tDocumento = $arrayPago->tipo_documento;
            $nDocumento = $arrayPago->nro_documento;
            $lote = $arrayPago->lote;
            $ctaDebitar = $arrayPago->cta_pagador;
            $nroTotal = $arrayPago->total_registro;
            $mtoTotal = $arrayPago->total_pago;
            $tipoPago = $arrayPago->tipo_pago;
            $fechaValor = $arrayPago->fecha_valor;
            $idCliente = $arrayPago->id_cliente;

            //Valida que pago no se halla efectuado o tenga saldo para el pago 


            $respValidacion = $this->validarPagoAS400($tDocumento, $nDocumento, $lote, $ctaDebitar, $nroTotal, $mtoTotal, $tipoPago, $fechaValor);

            if($respValidacion['error']=='98' || $respValidacion['error']=='99'){
                //si hay un error en el as400 se reporta y se para el proceso
                //trait AS400_validar 
                $this->reportarErrorAS400($respValidacion);
            }

            //En funcion a la respuesta del AS400, Validar pago se reporta un error o continua 
            //el pago de los detalles 
            $respV = $this->valResp($respValidacion,$archivoPago->id, $idDescarga);
            if($respV == 0){
                ///////////////////////////////////////////////////////////////////////////
                //GUARDAR LOS PAGOS EN EL AS400 
                //*obtener los detalles del archivo a procesar y almacenarlo en el as400
                ///////////////////////////////////////////////////////////////////////////
                $arrgDetalle = Archivo_Detalle::where('id_archivo', $archivoPago->id)
                              ->where('sw_pago','0')
                              ->where('error_pago',0)
                              ->get();
                if(count($arrgDetalle) > 0){
                    $i = 0;  //contador detalle 
                    //Registrar pagos en as400. 
                    $swErrorPago =0;
                    $sumaControl = 0;
                    //obtener informacion de los bancos
                    $respBanco = $this->consultaBanco();
                    $arrgBanco = $respBanco['BNKSRDSJVReturn']['BNKSRDSJV'];

                    foreach ($arrgDetalle as $detallaPago) {
                        $i++;
                        $idDetalle = $detallaPago->id;
                        //registrar detalle As400
                        $respDetalle = $this->detallePagoAS400($arrayPago, $detallaPago,$i, $arrgBanco);
                        if($respDetalle['error'] == '00'){
                            $detallaPago->error_pago = 0;
                            $detallaPago->cod_error_pago = '400';
                            $detallaPago->desc_error_pago = $respDetalle['mensaje'];
                            $swErrorPago = 0;
                            $detallaPago->sw_pago = 0;
                            $detallaPago->save();
                        }else if($respDetalle['error'] != '98' && $respDetalle['error'] != '99'){
                            $swErrorPago = 1;
                            $detallaPago->cod_error_pago = $respDetalle['error'];
                            $detallaPago->desc_error_pago = $respDetalle['mensaje'];
                            $detallaPago->sw_pago = 0;
                            $detallaPago->sw_error = 1;
                            $detallaPago->save();
                            break;
                        }else if($respDetalle['error'] == '98' || $respDetalle['error'] == '99'){
                            $this->reportarErrorAS400($respDetalle);
                        }        
                    }  //fin foreach detalle


                    //Si existe un error en uno de los detalles hay que cambiar la descripcion del codigo
                    //error pago, error_detalle 
                    if($swErrorPago == 1){
                        $this->errorPagoIndividual($arrayPago->id, $idDescarga);
                    }else{
                        ///Ejecucion del paggo
                        $this->ejecutarPago($arrayPago, $idDescarga, $mtoTotal, $fechaValor, $idCliente, $tipoPago);
                    }    
                }else{
                    $respSinDetalle = $this->errorSinDetalle($arrayPago->id, $idDescarga);
                }    

                    
            }  //fin de if (resval)
        }  //foreach de archivo  
        return 0;                              
    }
    

    public function detallePagoAS400($arregloArchivo, $detallaPago, $i, $arrgBanco){
        //Datso del beneficiario   
        $pyrusr =  trim($arregloArchivo['tipo_documento']) . trim($arregloArchivo['nro_documento']); //rif
        $lote = $arregloArchivo['lote'];  //lote del pago

        $lote = trim($lote);
        


        $pyrpid = $lote;
        $ctaBeneficiario = trim($detallaPago['cta_receptora']);
        $codCtaBeneficiario = substr($ctaBeneficiario,0,4);
        $refPaymul = trim($detallaPago['nro_referencia']);
        if($codCtaBeneficiario == '0171'){
            $pyrtyp = 'C';   //tipo operacion
        }else{
            $pyrtyp = 'A';
        }
        $pyrnum = $i;
        $pyrbacc = $ctaBeneficiario;

        $pyribkid = $codCtaBeneficiario;
        $pyrbbnk = str_pad($codCtaBeneficiario, 9, 0, STR_PAD_LEFT);
        foreach ($arrgBanco as $banco) {
            if($pyrbbnk == $banco['BNKID']){
                $pyrbbkn = substr($banco['BNKNME'], 0, 30);  //BNKNME Nombre banco
                $pyribknm = substr($banco['BNKNME'], 0, 30);  //BNKNME Nombre banco
                $pyribkad =  substr($banco['BNKAD1'], 0, 30); //Direccion banco
                $pyribka2 = substr($banco['BNKCTY'], 0, 30);
                break;
            }             
         } 


        $pyrbnam =  trim($detallaPago['nob_proveedor']);  //nob beneficiario
        $pyramt = trim($detallaPago['monto_pagar']);

        
        $pyrmem1 = ''; 
        if($codCtaBeneficiario != '0171'){ 
            $pyrmem1 = '0222-Terceros Otros Bancos';
        }else{
            $pyrmem1 = '0221';
        } 

        $tipoPago = trim($arregloArchivo['tipo_pago']);
        if($tipoPago == 'SAL'){
            $pyrmem2 = 'NOM' . $refPaymul;
         }else{
           $pyrmem2 = 'PRV' . $refPaymul;
        }

        $nroDocumento = trim($detallaPago['nro_documento']);
        $pyrbad1 = trim($detallaPago['tipo_documento']) . str_pad($nroDocumento,9,0, STR_PAD_LEFT);

        $dataPago = [
           'PYRUSR'   => $pyrusr,
           'PYRPID'   => $pyrpid,
           'PYRTYP'   => $pyrtyp,
           'PYRNUM'   => $i,
           'PYRBACC'  => $pyrbacc,
           'PYRBBNK'  => $pyrbbnk,
           'PYRBBKN'  => $pyrbbkn,
           'PYRIBKID' => $pyribkid,
           'PYRIBKNM' => $pyribknm,
           'PYRIBKAD' => $pyribkad,
           'PYRIBKA2' => $pyribka2,
           'PYRBNAM'  => $pyrbnam,
           'PYRBBKA2' => '',
           'PYRAMT'   => $pyramt,
           'PYRCCY'   => 'VEB',
           'PYRMEM1'  => $pyrmem1,
           'PYRMEM2'  => $pyrmem2,
           'PYRBAD1'  => $pyrbad1,
        ]; 

        $respRegistroPago = $this->registrarDetallePago($dataPago);
        return $respRegistroPago;

    }


    public function ejecucionPagoAS400($arregloPago){
        $pyrusr = trim($arregloPago['tipo_documento']) . trim($arregloPago['nro_documento']);
        $pyrpid = $arregloPago['lote'];
        
        //tipo pago nomina o orden de pago
        //SAL = pago nomina
        //SCV = pago Proveedor
        $tipoPago = $arregloPago['tipo_pago'];
        if(trim($arregloPago['tipo_pago']) == 'SAL'){  //PAgo nomina
            $pyrtyp = 'N'; 
            $pyrmem1 =  "PAGO NOMINA " . ltrim($pyrpid, "0");
        }else{
            $pyrtyp = 'P';
            $pyrmem1 = "ORDEN DE PAGO " . ltrim($pyrpid, "0");
        }
        $ctaDebitar = trim($arregloPago['cta_pagador']);
        $ctaDebitar = substr($ctaDebitar,-10);
        $pyracc = $ctaDebitar;
        $pyramt = trim($arregloPago['total_pago']);
        $pyrnum = trim($arregloPago['total_registro']);
 
        //Fecha valor
        $fechaValor = trim($arregloPago['fecha_pago']);
        //$fechaValor = trim($arregloPago['fecha_valor']);
          
        $pyrvdd  = substr($fechaValor,8,2);
        $pyrvdm  = substr($fechaValor,5,2);
        $pyrvym = substr($fechaValor,2,2);

        $data = [
            'PYRUSR' => $pyrusr,
            'PYRPID' => ltrim($pyrpid, "0"),
            'PYROPC' => '02',
            'PYRTYP' =>  strtoupper($pyrtyp),
            'PYRMEM1' => $pyrmem1,
            'PYRMEM2' => ltrim($pyrpid, "0"),
            'PYRACC' => $pyracc,
            'PYRAMT' => $pyramt,
            'PYRNUM' => $pyrnum,
            'PYRVDD' => $pyrvdd,
            'PYRVDM' => $pyrvdm,
            'PYRVDY' => $pyrvym 
        ];

        $respEjecucion = $this->ejecutarPago400($data); 
        return $respEjecucion;
    }

    
    public function valResp($respValidacion, $id_archivo, $idDescarga){
        //si existe un error en el as400´para el proceso, para ser ejecutado
        //al resolverse la incidencia
        if($respValidacion['error']!='00'){
            //error de lote o saldo
            // colocar todos detalle con dicho error con update.
            //Parar ejecucion            
            $resp = $this->errorPago($respValidacion, $id_archivo, $idDescarga, '491');
            return 1;
        }else{
            return 0;
        }    
    }

    public function errorPago($respValidacion, $idArchivo, $idDescarga, $codError){
        if($codError == '491'){
            if($respValidacion['error']=='0215'){
                 //error lote
                $codError = '451';
                $descripcion = '451;' . Simf_Error::mensajeError('451', count($arrayLinea));

            }else{
                //error saldo insuficiente
                $codError = '452';
                $descripcion = '452;' . Simf_Error::mensajeError('452', count($arrayLinea));                
            }
            $descError = $respValidacion['mensaje']; 

        }else if($codError == '497'){
            $codError = '470';
            $descError = '470 - ERROR EJECUCION PAGO [ ' . $respValidacion['mensaje'] . ' ]';
        }else{ 
            $codError = '480';   
            $descError = '480 - ERROR INESPERADO PAGO [ ' .  $respValidacion['mensaje'] . ' ]';
        }    

        Archivo_Detalle::where('id_archivo', $idArchivo)
                    ->update(['cod_error_pago' =>  $codError,
                              'desc_error_pago'=> $descError,
                              'sw_pago'=>'0',
                              'error_pago'=>'1']);


        Archivo_Recibido::where('id',  $idArchivo)
                    ->update(['status_proceso' => '2',
                              'error_archivo'=>'8',
                              'desc_error'=>'PAGO [ERROR VALIDACION PAGO]',
                              'nivel_proceso'=>'4']);

        Archivo_Descarga::errorDescarga($idDescarga);  
        return true;
    }              


    public function errorPagoIndividual($idArchivo, $idDescarga){
        Archivo_Detalle::where('id_archivo', $idArchivo)
                       ->where('cod_error_pago', '400')
                       ->update(['cod_error_pago' => '430',
                          'desc_error_pago'=> '430 - ERROR VALIDACION PAGO',
                          'sw_pago'=>'0',
                          'error_pago'=>'1']);
        Archivo_Recibido::where('id',  $id_archivo)
                    ->update(['status_proceso' => '2',
                              'error_archivo'=>'7',
                              'desc_error'=>'430 - ERROR VALIDACION PAGO',
                              'nivel_proceso'=>'4']);

        Archivo_Descarga::errorDescarga($idDescarga);  
          
    }     



    function ejecutarPago($arrayPago, $idDescarga, $mtoTotal, $fechaValor, $idCliente, $tipoPago){

        $arrgEjecucion = $this->ejecucionPagoAS400($arrayPago);

        //$arrgEjecucion = explode(";" , $respEjecucion);
        
        if($arrgEjecucion['error'] == '00'){
            $arregArchivo = Archivo_Recibido::find($arrayPago->id_archivo);
            $arregArchivo->nivel_proceso = '4';
            $arregArchivo->save();
            
            //Actualizar acumulado del cliente por pago

            $this->ActualizarAcumuladoCliente($mtoTotal, $fechaValor, $idCliente, $tipoPago); 
            return 0;     
        }else if($arrgEjecucion['error'] == '98' || $arrgEjecucion['error'] == '99') {
            $this->reportarErrorAS400($arrgEjecucion);
        }else{
            $resp = $this->errorPago($arrgEjecucion, $arrayPago->id_archivo, $idDescarga,'497');
        }
        return 0;


    }


    public function errorSinDetalle($idArchivo, $idDescarga){
        //Actualiza en archiovo recibido con error
        Archivo_Recibido::where('id',  $idArchivo)
                    ->update(['status_proceso' => '2',
                              'error_archivo'=>'7',
                              'desc_error'=>'PAGO [ERROR SIN DETALLE PAGO]',
                              'nivel_proceso'=>'4']);
        
        //[BNKSRDSJVReturn] => Array ( [error] => [BNKSRDSJV] valida el estado de las descarga con respecto a los archivvos con error
        $resp = Archivo_Descarga::errorDescarga($idDescarga);            
        return 0;  

    }


    public function ActualizarAcumuladoCliente($mtoTotal, $fechaValor, $idCliente, $tipoPago){
        $acumLimite = Proveedor_Limite::where('id_cliente', $idCliente)->first();
        if($acumLimite){
            $mtoLimNomina = $acumLimite->lim_max_nomina;
            $mtoLimProveedor = $acumLimite->lim_max_proveedor;

            Proveedor_Acumulado::ActAcumulado($mtoTotal, $fechaValor, $idCliente, $tipoPago, $mtoLimNomina, $mtoLimProveedor);
        }




    }



//---------------------------------------------------------------------------------------
//fin del trait
}    

