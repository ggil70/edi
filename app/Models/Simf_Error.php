<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simf_Error extends Model
{
    protected $table = "simf_errores";

    public static function mensajeError($codigo, $mensaje){
        $motivo = '';
        $arrgSimf = Simf_Error::where('codigo', $codigo)->first();
        if($arrgSimf){
            $motivo.= $arrgSimf->desc_simf . " [ " . $mensaje . ' ]';
        }  
        return $motivo;                   
    }
}
