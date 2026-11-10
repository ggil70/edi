<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log_Servicio_Edi extends Model
{
    protected $table = "log_servicio_edis";

    public static function guardarServicioEdi($estatus, $motivo){
        $des_fecha = date("d-m-Y H:i:s");
        $logServicio = new Log_Servicio_Edi();
        $logServicio->estatus_servicio = $estatus;
        $logServicio->fec_hora_servicio = date('Y-m-d h:i:s');
        $logServicio->id_usuario = Session::get('usuario.id');
        $logServicio->motivo_servicio = $motivo;
        $logServicio->save();
        return true;
    }


}
