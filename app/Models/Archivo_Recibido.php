<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archivo_Recibido extends Model
{
    protected $table = "archivo_recibidos";


    public static function obtenerArchivoRecibido($idDescarga, $nivelProceso, $errorArchivo, $detalle){
       

        if($detalle == 0){
            $arrgArchivo = Archivo_recibido::where('status_proceso',0)
                                  ->where('id_archivo_descarga',$idDescarga)
                                  ->where('nivel_proceso', $nivelProceso)
                                  ->where('error_archivo', $errorArchivo)
                                  ->get(); 
                 
        }else{  

            //error_archivo = 4 es un error critico deonde no se procesa el detalle
            $arrgArchivo = Archivo_recibido::where('status_proceso', '!=', 2)
                              ->where('id_archivo_descarga',$idDescarga)
                              ->where('nivel_proceso', $nivelProceso)
                              ->where('error_archivo','<>',4)
                              ->get();         
        } 


        if(count($arrgArchivo) > 0){ 
            $data = [
                'respuesta'=>'0',
                'arrgData'=>$arrgArchivo
            ];       
        }else{
            $data = [
                'respuesta'=>'1'
            ];             
        }
        return $data;
    }  




    public static function obtenerTodosArchivorReproceso($idDescarga){
        $arrgArchivo = Archivo_recibido::where('status_proceso', '!=', 2)
                              ->where('id_archivo_descarga',$idDescarga)
                              ->where('nivel_proceso','<' ,4)
                              ->get(); 


        if(count($arrgArchivo) > 0){ 
            $data = [
                'respuesta'=>'0',
                'arrgData'=>$arrgArchivo
            ];       
        }else{
            $data = [
                'respuesta'=>'1'
            ];             
        }
        return $data;                              
    }



    public static function  obtenerArchivorReproceso($idArchivo, $detalle){
        if($detalle == 0){
            $arrgArchivo = Archivo_recibido::where('id', $idArchivo)
                            ->get();
        }else{
            $arrgArchivo = Archivo_recibido::where('id', $idArchivo)
                            ->where('error_archivo','<>',4)
                            ->get();            
        }  

        if(count($arrgArchivo) > 0){ 
            $data = [
                'respuesta'=>'0',
                'arrgData'=>$arrgArchivo
            ];       
        }else{
            $data = [
                'respuesta'=>'1'
            ];             
        }

        return $data;                        
    }                   



}
