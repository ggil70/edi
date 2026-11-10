<?php

namespace App\Http\Controllers;

//Definicion de modelos
use App\Models\Adm_Proceso;

use App\Models\Archivo_Descarga;
use App\Models\Archivo_Encabezado;
use App\Models\Archivo_Recibido;
use App\Models\Archivo_Detalle;
use App\Models\Cliente;
use App\Models\Log_Error_Archivo;
use App\Models\Log_Error_Proceso;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;




//Definicion traits
use App\Traits\FuncArchivos;
use App\Traits\FuncValidarArchivos;
use App\Traits\FuncValidarEncabezados;
use App\Traits\FuncValidarDetalles;
use App\Traits\FuncValidarCampos;
use App\Traits\FuncProcesarPagos01;

use App\Traits\FuncGestionPagos;

use App\Traits\AS400_3010;
use App\Traits\AS400ValidarPago;
use App\Traits\AS400Cuentas;
use App\Traits\AS400Banco;
use App\Traits\AS400CabGestion;
use App\Traits\AS400GestionPago;
use App\Traits\AS400_validar;
use App\Traits\Whatsapp;
use App\Traits\ValidarEdi;

use App\Traits\MailEdi;


use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class ProcesarPagoController extends Controller
{
    //Libreria para el manejo de archivos txt.
    use FuncArchivos;
    //librerio para validar nombre archivo
    use FuncValidarArchivos;
    //libreria para validar campos genericos
    use FuncValidarCampos;
    //libreria para validar encabezado archivo
    use FuncValidarEncabezados;
    //libreria para validar detalles archivo
    use FuncValidarDetalles;
    //Pago de nomina/proveedor
    use FuncProcesarPagos01;
    //libreria Gestionar pagos
    use FuncGestionPagos;   

    //AS400_validar
    use AS400_validar;

    //validar proceso EDI
    use ValidarEdi;    

    //EnviarCorreoEdi EDI
    use MailEdi;    

    //AS400_validar
    use Whatsapp;



    public function callProcesarArchivos(){
        Session::put('swForm', 1);
        return Redirect::route('procesarPago');
    }


    public function procesarArchivos(){
        //validar estatus servicio edi.
        //trials ValidarEdi

        $respServicio = $this->validarServicioEdi(); 

        //Validar funcionamiento del AS400 antes de procesar la validacion del archivo
        //de no haber conexion se detiene el proceso.
        //trait AS400_validar
        $this->validarAS400();

        //////////////////////////////////////////////////////////////////
        ////////////////////        
         //Activar despues de las pruebas para que lea el archivo
                            //Desde el FTP 

        //$idDescarga = $this->cargarArchivoFTP();
        $arregloArchivo = $this->leerArchivosFtp();

        if($arregloArchivo['respuesta'] != 0){
            $codOperacion = "FTP";
            if($arregloArchivo['respuesta'] == 1){ 
                $motivoError = "SIN ARCHIVO EN LA RUTA FTP";
                $cantidad = 0;
                $idArchivoDescarga = Archivo_Descarga::crearLoteDescarga($cantidad);
                //$respError = Log_Error_Proceso::guardarErrorProceso($codOperacion, $motivoError);   
                if (session()->has('swForm')) {
                    Session::forget('swForm');
                    $data['respuesta'] = "SIN ARCHIVO EN LA RUTA FTP";
                    return view('procesoPago/procesarPago',$data);
                }   

                exit();
            }else{
                //$motivoError = "ERROR EN CONEXION CON EL SERVIDOR FTP";
                $motivoError = $arregloArchivo['detError'];
                $respError = Log_Error_Proceso::guardarErrorProceso($codOperacion, $motivoError);
                $this->enviarEmail($codOperacion,"",$motivoError);
                if (session()->has('swForm')) {
                    Session::forget('swForm');
                    $data['respues
                    ta'] = $motivoError;
                    return view('procesoPago/procesarPago',$data);
                }
                exit();
            }    
            //Guardar incedencia en el log_error_proceso

        }else{
            $arrgArchivos = $arregloArchivo['archivos'];
            //registrar lote de descarga
            $cantidad = count($arrgArchivos);
            $idDescarga = Archivo_Descarga::crearLoteDescarga($cantidad);
            Session::put('idArchivoDescarga',$idDescarga);
            Session::put('statusDescarga','p');

            //Validar y guardar archivos 
            $swArchivo = $this->validacionArchivo($arrgArchivos, $idDescarga);  //funcValidarArchivos
            if($swArchivo == 1){
                if (session()->has('swForm')) {
                    Session::forget('swForm');
                    $data['respuesta'] = "FINAL DEL PROCESO [ TODOS LOS ARCHIVOS INVALIDOS ]";
                    return view('procesoPago/procesarPago',$data);
                }                
                exit;
            }
        }



        
    
           // 2 paso Validar encabezado del archivo
           // trait funcValidarEncabezado

       
           $reproceso = 0;
           $idArchivo = 0;

           $swEncabezado = 0;
           $swEncabezado = $this->validarEncabezadosArchivos($idDescarga, $idArchivo, $reproceso);
           

           //Validar detalle
           $swDetalle = $this->validarDetalleArchivos($idDescarga, $idArchivo, $reproceso);

           $swPago = 0;

           $swPago = $this->procesarPagos($idDescarga, $idArchivo, $reproceso);
           


            if($swEncabezado == 0 || $swPago == 0){
                if (session()->has('swForm')) {
                   Session::forget('swForm');
                    $data['respuesta'] = "EL PROCESO PAGO FUE REALIZADO SATISFACTORIAMENTE";
                    return view('procesoPago/procesarPago',$data);
                }
            }else{
                if (session()->has('swForm')) {
                    Session::forget('swForm');
                    $data['respuesta'] = "TODOS LOS ARCHIVOS DESCARGADOS SON INVALIDOS";
                    return view('procesoPago/procesarPago',$data);
                }

            }    



        //} //fin de consulta de archivos en ruta descarga
    } 





////////////////////////////////////////////////////////////////////////////
///  REPROCESAR PAGO
////////////////////////////////////////////////////////////////////////////  
    public function callReprocesar(){


        Session::put('swFormRP', 1);
        return Redirect::route('reprocesarPago');
    }


    public function reprocesarPago(){
        //validar estatus servicio edi.
        //trials ValidarEdi
        $respServicio = $this->validarServicioEdi(); 



        //Validar funcionamiento del AS400 antes de procesar la validacion del archivo
        //de no haber conexion se detiene el proceso.
        //trait AS400_validar
        $this->validarAS400();

        //obtener descargas pendientes
        $arrgDescarga = Archivo_Descarga::obtenerDescargaPendiente();

        if($arrgDescarga['respuesta'] == '0'){
            foreach ($arrgDescarga['arrgData'] as $arrgD) {        
                $idDescarga = $arrgD->id;
                //obtener los archivos procesador de la decarga consultada con estatus
                // de pagos procesados (nivel_proceso = 1)
                $resArchivo = Archivo_Recibido::obtenerTodosArchivorReproceso($idDescarga);


                if($resArchivo['respuesta'] == '0'){ 
                    $arrgArchivo = $resArchivo['arrgData']; 
                    foreach ($arrgArchivo as $arrgA) {
                        $idArchivo = $arrgA->id;
                        $nivelProceso = $arrgA->nivel_proceso;

                        // Verificar donde quedo el procesamiento del archivo
                        switch ($nivelProceso) {
                            case 1:
                                $resp = $this->reprocesarEncabezado($idDescarga, $idArchivo);
                                break;
                            case 2:
                                $resp = $this->reprocesarDetalle($idDescarga, $idArchivo);
                                break;  
                            case 3:
                                if($arrgA->error_archivo == 0){
                                    $resp = $this->reprocesarPagoArchivo($idDescarga, $idArchivo);
                                }    
                                    break;                                                                
                        }
                    }

 
                }
            }  //fin del ciclo de archivo recibido  

            if (session()->has('swFormRP')) {
                Session::forget('swFormRP');
                $data['respuesta'] = 0;
                return view('procesoPago/reProcesarPago',$data);
            }else{
                die("Reproceso pago terminado");
            } 

        }else{
            if (session()->has('swFormRP')) {
                Session::forget('swFormRP');
                $data['respuesta'] = 1;
                return view('procesoPago/reProcesarPago',$data);
            }else{
                die("Reproceso pago terminado");
            } 
        }



    }  //fin de reprocesar    

    public function reprocesarEncabezado($idDescarga, $idArchivo){
        //Validar encabezado
        $reproceso = 1;
        $swEncabezado = $this->validarEncabezadosArchivos($idDescarga, $idArchivo, $reproceso);
        //Validar detalle
        $swDetalle = $this->validarDetalleArchivos($idDescarga, $idArchivo, $reproceso);  
        //Procesar Pago 
        $swPago = $this->procesarPagos($idDescarga, $idArchivo, $reproceso);
        if($swEncabezado == 0 || $swPago == 0){
            return 0;
        }else{
            return 1;
        }    
    }  



    public function reprocesarDetalle($idDescarga, $idArchivo){
        $reproceso = 1;
        //Validar detalle
        $swDetalle = $this->validarDetalleArchivos($idDescarga, $idArchivo, $reproceso);
        //Procesar Pago  
        $swPago = $this->procesarPagos($idDescarga, $idArchivo, $reproceso);
        if($swPago == 0){
            return 0;
        }else{
            return 1;
        }         
    }     


    public function reprocesarPagoArchivo($idDescarga, $idArchivo){
        $reproceso = 1;
        //Procesar Pago  
        $swPago = $this->procesarPagos($idDescarga, $idArchivo, $reproceso);
        if($swPago == 0){
            return 0;
        }else{
            return 1;
        }        
    } 


  /////////////////////////////////////////////////////////////////////////
  //subir archivo    
  public function subirArchivo(){
      return view('procesoPago/subirArchivo');
  }  

  public function guardarArchivo(Request $request){
        $validated = $request->validate([
            'archivo' => 'max:4096|mimes:txt',
        ],[
            'archivo.max' => '¡El archivo excede del tamaño permitido(4096 Mbtes)',  
            'archivo.mimes' => '¡La extensión del archivo debe ser .txt',  
        ]);

        $archivos = $request->file('archivo');
        $nombreArchivo = $archivos->getClientOriginalName();
        $ruta = $archivos->storeAs('', $nombreArchivo, 'ftpEntrada'); 

        
        //echo back()->with('success', "Archivo subido con éxito a: $ruta");



        //$ruta = $archivo->storeAs('archivo', $nombreArchivo, 'EDI_SALIDA');
        //echo back()->with('success', "Archivo subido con éxito a: $ruta");







         



        return view('procesoPago/finSubirArchivo');
  }  
    

}
