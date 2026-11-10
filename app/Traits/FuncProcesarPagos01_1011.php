<?php

namespace App\Traits; // Solo debe quedar este namespace

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Archivo_Recibido;   
use App\Models\Archivo_Encabezado; 
use App\Models\Archivo_Detalle;    
use App\Models\Log_Error_Proceso;  
use App\Models\Archivo_Descarga; 
use App\Models\Proveedor_Acumulado; 
use App\Models\Proveedor_Limite; 
use App\Models\Simf_Error;
use App\Traits\ValidarEdi;

use App\Traits\MailEdi;



use Exception;

trait FuncProcesarPagos01
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
    //validar proceso EDI
    use ValidarEdi;    

    //EnviarCorreoEdi EDI
    use MailEdi;    

    public function procesarPagos($idDescarga, $idArchivo, $reproceso ){
        //validar estatus servicio edi.
        //trials ValidarEdi
        $respServicio = $this->validarServicioEdi(); 

        //Validar funcionamiento del AS400 antes de procesar la validacion del archivo
        //de no haber conexion se detiene el proceso.

        //Activar al culminar las pruebas
        //trait AS400_validar
        $this->validarAS400();
        $respuesta = 0;

        if($reproceso == 0){
            $nivelProceso = 3;
            $errorArchivo = 0;
            $detalle = 0;
            $arrayArchivoPago =  Archivo_recibido::where('status_proceso', 0)
                              ->where('id_archivo_descarga',$idDescarga)
                              ->where('nivel_proceso', 3)
                              ->where('error_archivo',0)
                              ->get();
            if(count($arrayArchivoPago) == 0){ 
                $respuesta = 1;
            } 



        }else{
            //$arrgArchivoPago = Archivo_Recibido::obtenerArchivorReproceso($idArchivo, 0);
            $arrayArchivoPago = Archivo_recibido::where('status_proceso', 0)
                                  ->where('nivel_proceso', 3)
                                  ->where('error_archivo',0)                                  
                                  ->where('id_archivo_descarga',$idDescarga)
                                  ->get();             
                if(count($arrayArchivoPago) == 0){ 
                    $respuesta = 1;
                }    
        }

        //Si no existen archivos para realizar el pago se detiene el proceso
        if($respuesta == 1){
           return 1;
        }

        //procesar los pagos
        foreach ($arrayArchivoPago as $archivoPago){
            $arrayPago = Archivo_Recibido::query()
                    ->select([
                        'archivo_encabezados.*', 'archivo_recibidos.id_cliente', 'archivo_recibidos.nro_detalle_ok','archivo_recibidos.mto_detalle_ok'
                    ])
                    ->join('archivo_encabezados', 'archivo_recibidos.id', '=', 'archivo_encabezados.id_archivo')
                    ->where('archivo_recibidos.id', $archivoPago->id)
                    ->first();

            $tDocumento = $arrayPago->tipo_documento;
            $nDocumento = $arrayPago->nro_documento;
            $lote = $arrayPago->lote;
            $lote = $lote * 1;
            $lote = trim($lote);

            //$lote = str_pad($lote, 9, "0", STR_PAD_LEFT);


            $ctaDebitar = $arrayPago->cta_pagador;
            //$nroTotal = $arrayPago->total_registro;
            //$mtoTotal = $arrayPago->total_pago;
            $nroTotal = $arrayPago->nro_detalle_ok;
            $mtoTotal = $arrayPago->mto_detalle_ok;
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

            $respV = $this->valResp($respValidacion,$archivoPago->id, $idDescarga, $nroTotal,$mtoTotal);

            if($respV == 0){
                ///////////////////////////////////////////////////////////////////////////
                //GUARDAR LOS PAGOS EN EL AS400 
                //*obtener los detalles del archivo a procesar y almacenarlo en el as400
                ///////////////////////////////////////////////////////////////////////////
                $arrgDetalle = Archivo_Detalle::where('id_archivo', $archivoPago->id)
                              ->where('sw_pago','0')
                              ->where('error_detalle',0)
                              ->get();

                if(count($arrgDetalle) > 0){
                    $i = 0;  //contador detalle 
                    //Registrar pagos en as400. 
                    $swErrorPago =0;
                    $sumaControl = 0;
                    //obtener informacion de los bancos
                    $respBanco = $this->consultaBanco();

                    $arrgBanco = $respBanco['BNKSRDSJVReturn']['BNKSRDSJV'];            

                    $mtoTotalPago = 0.00;  
                    $nroPagoOk = 0; 

                    foreach ($arrgDetalle as $detallaPago) {
                        $i++;
                        $idDetalle = $detallaPago->id;
                        //registrar detalle As400
                        $respDetalle = $this->detallePagoAS400($arrayPago, $detallaPago,$i, $arrgBanco);

                        if($respDetalle['error'] == '00'){
                            $nroPagoOk = $nroPagoOk + 1;
                            $mtoTotalPago = $mtoTotalPago + $detallaPago->monto_pagar;
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
                        if($nroPagoOk == 0){
                            $this->errorPagoIndividual($arrayPago->id, $idDescarga);
                        }else{
                            $this->ejecutarPago($arrayPago, $idDescarga, $mtoTotal, $fechaValor, $idCliente, $tipoPago, $nroTotal);                            
                        }
                    }else{
                        ///Ejecucion del paggo
                        $this->ejecutarPago($arrayPago, $idDescarga, $mtoTotal, $fechaValor, $idCliente, $tipoPago, $nroTotal);
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

        $lote = $lote * 1;
        $lote = trim($lote);

        //$lote = str_pad($lote, 9, "0", STR_PAD_LEFT);

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

        /*if($codCtaBeneficiario != '0171'){ 
            $pyrmem1 = '0222-Terceros Otros Bancos';
        }else{
            $pyrmem1 = '0221';
        } */

        $tipoPago = trim($arregloArchivo['tipo_pago']);
        if($tipoPago == 'SAL'){
            $pyrmem1 = '0221';
            $pyrmem2 = 'NOM' . $refPaymul;
         }else{
           $pyrmem2 = 'PRV' . $refPaymul;
           $pyrmem1 = '0222';
        }

        $nroDocumento = trim($detallaPago['nro_documento']);
        $pyrbad1 = trim($detallaPago['tipo_documento']) . str_pad($nroDocumento,9,0, STR_PAD_LEFT);
        $ip = $request->ip();
        $puertoIp = '04'.$ip;

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
           'PYRBBKA2' => $puertoIp,
           'PYRAMT'   => $pyramt,
           'PYRCCY'   => 'VEB',
           'PYRMEM1'  => $pyrmem1,
           'PYRMEM2'  => $pyrmem2,
           'PYRBAD1'  => $pyrbad1,
        ]; 

        $respRegistroPago = $this->registrarDetallePago($dataPago);
        return $respRegistroPago;

    }


    public function ejecucionPagoAS400($arregloPago, $mtoTotal, $nroRegistro){
        $pyrusr = trim($arregloPago['tipo_documento']) . trim($arregloPago['nro_documento']);

        $lote = $arregloPago['lote'] * 1;
        $lote = trim($lote);
        //$lote = str_pad($lote, 9, "0", STR_PAD_LEFT);

        $pyrpid = $lote;

        //tipo pago nomina o orden de pago
        //SAL = pago nomina
        //SCV = pago Proveedor
        $tipoPago = $arregloPago['tipo_pago'];
        if(trim($arregloPago['tipo_pago']) == 'SAL'){  //PAgo nomina
            $pyrtyp = 'N'; 
            //$pyrmem1 =  "PAGO NOMINA " . ltrim($pyrpid, "0");
            $pyrmem1 = '0221'; 
        }else{
            $pyrtyp = 'P';
            //$pyrmem1 = "ORDEN DE PAGO " . ltrim($pyrpid, "0");
            $pyrmem1 = '0222';
        }
        $ctaDebitar = trim($arregloPago['cta_pagador']);
        $ctaDebitar = substr($ctaDebitar,-10);
        $pyracc = $ctaDebitar;
        $pyramt = trim($arregloPago['total_pago']);
        $pyrnum = trim($arregloPago['total_registro']);

        $ip = $request->ip();
        $puertoIp = '04'.$ip;
        $pyrbbka2 = $puertoIp;
 
        //Fecha valor
        //$fechaValor = trim($arregloPago['fecha_pago']);
        $fechaValor = trim($arregloPago['fecha_valor']);
          
        $pyrvdd  = substr($fechaValor,8,2);
        $pyrvdm  = substr($fechaValor,5,2);
        $pyrvym = substr($fechaValor,2,2);

        $data = [
            'PYRUSR' => $pyrusr,
            'PYRPID' => $pyrpid,
            'PYROPC' => '02',
            'PYRTYP' =>  strtoupper($pyrtyp),
            'PYRMEM1' => $pyrmem1,
            'PYRMEM2' => $pyrpid,
            'PYRACC' => $pyracc,
            'PYRAMT' => $pyramt,
            'PYRNUM' => $pyrnum,
            'PYRVDD' => $pyrvdd,
            'PYRVDM' => $pyrvdm,
            'PYRVDY' => $pyrvym,
        ];


        $respEjecucion = $this->ejecutarPago400($data, $mtoTotal, $nroRegistro); 

       
        return $respEjecucion;
    }

    
    public function valResp($respValidacion, $id_archivo, $idDescarga, $nroTotal,$mtoTotal){
        //si existe un error en el as400´para el proceso, para ser ejecutado
        //al resolverse la incidencia
        if($respValidacion['error']!='00'){
            //error de lote o saldo
            // colocar todos detalle con dicho error con update.
            //Parar ejecucion 
            $resp = $this->errorPago($respValidacion, $id_archivo, $idDescarga, '491', $nroTotal, $mtoTotal);
            return 1;
        }else{
            return 0;
        }    
    }

    public function errorPago($respValidacion, $idArchivo, $idDescarga, $codError, $nroRegistro, $mtoSaldo){

        if($codError == '491'){
            $descError = '';
            if($respValidacion['error']=='0215'){
                 //error lote
                $codError = '451';
                $descError = '451;' . Simf_Error::mensajeError('451', $nroRegistro);

            }else{
                //error saldo insuficiente
                $codError = '452';
                $descError = '452;' . Simf_Error::mensajeError('452', $mtoSaldo); 
            }
            //$descError = $descError .  $respValidacion['mensajeTecnico']; 

        }else if($codError == '497'){
            $codError = '470';
            $descError = '470 - ERROR EJECUCION PAGO [ ' . $respValidacion['mensajeTecnico'] . ' ]';
        }else{ 
            $codError = '480';   
            $descError = '480 - ERROR INESPERADO PAGO [ ' .  $respValidacion['mensajeTecnico'] . ' ]';
        }    


        Archivo_Detalle::where('id_archivo', $idArchivo)
                    ->update(['cod_error_pago' =>  $codError,
                              'desc_error_pago'=> $descError,
                              'sw_pago'=>'0',
                              'error_pago'=>'1']);


        Archivo_Recibido::where('id',  $idArchivo)
                    ->update(['status_proceso' => '2',
                              'error_archivo'=>'8',
                              'desc_error'=>'PAGO [ERROR VALIDACION PAGO ( ERROR ' . $codError . ' ) ]',
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



    function ejecutarPago($arrayPago, $idDescarga, $mtoTotal, $fechaValor, $idCliente, $tipoPago, $nroRegistro){
        $arrgEjecucion = $this->ejecucionPagoAS400($arrayPago, $mtoTotal, $nroRegistro);

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
            $resp = $this->errorPago($arrgEjecucion, $arrayPago->id_archivo, $idDescarga,'497',0,0);
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

