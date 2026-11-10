<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log_Error_Archivo extends Model
{
    protected $table = "log_error_archivos";

    public static function guardarErrorArchivo($idDescarga, $nobArchivo, $motivoError){
        $logError = new Log_Error_Archivo();
        $logError->id_descarga = $idDescarga;
        $logError->nob_archivo = $nobArchivo;
        $logError->fec_error = date("Y-m-d H:i:s");
        $logError->motivo_error = $motivoError;
        $logError->save();
        return true;
    }
}
