<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log_Crear_Bansta extends Model
{
    protected $table = "log_crear_banstas";

    public static function guardarBansta($data){
        $des_fecha = date("d-m-Y H:i:s");
        $log = new Log_Crear_Bansta();
        $log->cod_operacion = $data['cod'];
        $log->fecha_operacion = date("Y-m-d H:i:s");
        $log->desc_operacion = $data['desc'];
        $log->rif = $data['rif'];
        $log->nro_orden = $data['nroOrden'];
        $log->archivo_bansta = $data['archivoBansta'];
        $log->save();
        return true;
    }

}
