<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archivo_Descarga extends Model
{
    protected $table = "archivo_descargas";

    //Registra el nbuevo lote a procesarse
    public static function crearLoteDescarga($nroArchivo){
        $descripcionDescarga = "Descarga " . date("d-m-Y H:i:s"); 
        $archivoDescarga = new Archivo_Descarga();
        $archivoDescarga->desc_descarga = $descripcionDescarga;
        $archivoDescarga->fec_descarga =  date('Y-m-d H:i:s');

        if($nroArchivo == 0){
            $archivoDescarga->status_descarga = 'C';
            $archivoDescarga->mot_status = 'DESCARGA SIN ARCHIVO PARA PROCESAR';
        }else{
            $archivoDescarga->nro_archivo = $nroArchivo;
        }

        $archivoDescarga->save();
        $idArchivoDescarga = $archivoDescarga->id;
        return $idArchivoDescarga;
    }  

    //Registra la incedencia de errores de archivo para validar el estatus de la descarga
    public static function errorDescarga($idDescarga){
        $arrgDescarga = Archivo_Descarga::find($idDescarga);
        $nroError = $arrgDescarga->nro_archivo_error + 1;
        $nroOk = $arrgDescarga->nro_archivo_ok - 1;
        //if($arrgDescarga->nro_archivo == $nroError){
        if($nroOk == 0){
            $arrgDescarga->status_descarga = 'X';
            $arrgDescarga->mot_status = 'TODOS LOS ARCHIVOS INVALIDOS';
        }
        $arrgDescarga->nro_archivo_error = $nroError;
        $arrgDescarga->nro_archivo_ok = $nroOk;
        $arrgDescarga->save();         
        return 0;
    }

    //// obtener descargas pendientes 
    public static function obtenerDescargaPendiente(){
        $arrgDescarga = Archivo_Descarga::where('status_descarga','P')
                             ->get();
        if(count($arrgDescarga) > 0){ 
            $data = [
                'respuesta'=>'0',
                'arrgData'=>$arrgDescarga
            ];       
        }else{
            $data = [
                'respuesta'=>'1'
            ];             
        }
        return $data;
    }    


}
