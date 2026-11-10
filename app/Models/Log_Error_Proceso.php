<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log_Error_Proceso extends Model
{
    protected $table = "log_error_procesos";

    public static function guardarErrorProceso($codOperacion, $motivoError){
        $des_fecha = date("d-m-Y H:i:s");
        $logError = new Log_Error_Proceso();
        $logError->cod_operacion = $codOperacion;
        $logError->desc_fecha = $des_fecha;
        $logError->fec_proceso = date("Y-m-d H:i:s");
        $logError->motivo_error = $motivoError;
        $logError->save();
        return true;
    }


}
