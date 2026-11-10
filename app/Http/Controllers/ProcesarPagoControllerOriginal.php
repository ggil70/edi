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

//Definicion traits
use App\Traits\funcArchivos;
use App\Traits\funcValidarArchivos;
use App\Traits\funcValidarEncabezados;
use App\Traits\funcValidarDetalles;
use App\Traits\funcValidarCampos;
use App\Traits\funcProcesarPagos;

use App\Traits\funcGestionPagos;

use App\Traits\AS400_3010;
use App\Traits\AS400ValidarPago;
use App\Traits\AS400Cuentas;
use App\Traits\AS400Banco;
use App\Traits\AS400CabGestion;
use App\Traits\AS400GestionPago;
use App\Traits\AS400_validar;
use App\Traits\Whatsapp;



use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class ProcesarPagoController extends Controller
{
    //Libreria para el manejo de archivos txt.
    use funcArchivos;
    //librerio para validar nombre archivo
    use funcValidarArchivos;
    //libreria para validar campos genericos
    use funcValidarCampos;
    //libreria para validar encabezado archivo
    use funcValidarEncabezados;
    //libreria para validar detalles archivo
    use funcValidarDetalles;
    //Pago de nomina/proveedor
    use funcProcesarPagos;
    //libreria Gestionar pagos
    use funcGestionPagos;   

    //AS400_validar
    use AS400_validar;

    //AS400_validar
    use Whatsapp;


    public function procesarArchivos(){
        //Validar funcionamiento del AS400 antes de procesar la validacion del archivo
        //de no haber conexion se detiene el proceso.
        //trait AS400_validar
        //$this->validarAS400();


        //////////////////////////////////////////////////////////////////
        ////////////////////        
         //Activar despues de las pruebas para que lea el archivo
                            //Desde el FTP 

        //$idDescarga = $this->cargarArchivoFTP();

/*        $arregloArchivo = $this->leerArchivosFtp();
        if($arregloArchivo['respuesta'] != 0){
            $codOperacion = "FTP";
            if($arregloArchivo['respuesta'] == 1){ 
                $motivoError = "SIN ARCHIVO EN LA RUTA FTP";
                $cantidad = 0;
                $idArchivoDescarga = Archivo_Descarga::crearLoteDescarga($cantidad);
                $respError = Log_Error_Proceso::guardarErrorProceso($codOperacion, $motivoError);                
                exit();
            }else{
                $motivoError = "ERROR EN CONEXION CON EL SERVIDOR FTP";
                $respError = Log_Error_Proceso::guardarErrorProceso($codOperacion, $motivoError);
                $this->enviarWhatsapp('FTP');                
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
                echo "FINAL DEL PROCESO [ TODOS LOS ARCHIVOS INVALIDOS ] ";
                exit;
            }
        }
*/        
//quitar despues de las pruebas  
///////////////////////////////////////////////////////////////// 


        $arrgArchivos = $this->leerArchivosCarpetaTemp();
        if($arrgArchivos ==  0){
            //Fin del proceso, no hay archivo en la ruta
            $cantidad = 0;
            $idArchivoDescarga = Archivo_Descarga::crearLoteDescarga($cantidad);
            echo "FINAL DEL PROCESO [ SIN ARCHIVO PARA PROCESAR ] ";

            exit();
        }else{
            //registrar lote de descarga
            $cantidad = count($arrgArchivos);
            $idDescarga = Archivo_Descarga::crearLoteDescarga($cantidad);


            Session::put('idArchivoDescarga',$idDescarga);
            Session::put('statusDescarga','p');

            //Validar y guardar archivos 
            // trait funcValidarArchivos
            $swArchivo = $this->validacionArchivo($arrgArchivos, $idDescarga);  //funcValidarArchivos
            if($swArchivo == 1){
                echo "FINAL DEL PROCESO [ TODOS LOS ARCHIVOS INVALIDOS ] ";
                exit;
            }
           
//quitar despues de las pruebas  
/////////////////////////////////////////////////////////////////  
        
           // 2 paso Validar encabezado del archivo
           // trait funcValidarEncabezado
           
           $reproceso = 0;
           $idArchivo = 0;
           $swEncabezado = $this->validarEncabezadosArchivos($idDescarga, $idArchivo, $reproceso);

           //Validar detalle
           $swDetalle = $this->validarDetalleArchivos($idDescarga, $idArchivo, $reproceso);
            
            $swPago = $this->procesarPagos($idDescarga, $idArchivo, $reproceso);
       
            die("Proceso de carga terminado");


        } //fin de consulta de archivos en ruta descarga 


    } 


/*    public function errorArchivo($arrgArchivos){
        if($arrgArchivos['respuesta'] == 1){
            //crear registro de descarga de archivo
            $descripcionDescarga = "Descarga " . date("d-m-Y H:i:s"); 
            $archivoDescarga = new Archivo_Descarga();
            $archivoDescarga->desc_descarga = $descripcionDescarga;
            $archivoDescarga->fec_descarga =  date('Y-m-d H:i:s');
            $archivoDescarga->status_descarga = 'C';
            $archivoDescarga->nro_archivo = 0;
            $archivoDescarga->save();
            //registrar en el log Descarga sin archivo
        }else{

            //Registrar en el log la incidencia del sevicio ftp
        }
    } 
*/     


////////////////////////////////////////////////////////////////////////////
///  REPROCESAR PAGO
////////////////////////////////////////////////////////////////////////////  
    public function reprocesarPago(){
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
                                $resp = $this->reprocesarPagoArchivo($idDescarga, $idArchivo);
                                break;                                                                
                        }
                    }    
                }else{  //
                    //Archivo descarga sin archivo
                    //reportar el error
                    exit();
                }
            }  //fin del ciclo de archivo recibido              
        }else{
            //reportar en log Archivo descarga
            exit();
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
        return 0;
    }  



    public function reprocesarDetalle($idDescarga, $idArchivo){
        $reproceso = 1;
        //Validar detalle
        $swDetalle = $this->validarDetalleArchivos($idDescarga, $idArchivo, $reproceso);
        //Procesar Pago  
        $swPago = $this->procesarPagos($idDescarga, $idArchivo, $reproceso);
        return 0;
    }     


    public function reprocesarPagoArchivo($idDescarga, $idArchivo){
        $reproceso = 1;
        //Procesar Pago  
        $swPago = $this->procesarPagos($idDescarga, $idArchivo, $reproceso);
        return 0;
    } 

    

}
