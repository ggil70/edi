<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log_Gestion_Pago extends Model
{
    protected $table = "log_gestion_pagos";

    public static function guardarGestionPago($codOperacion, $motivo, $idCliente, $nobArchivo){
        $logGestion = new Log_Gestion_Pago();
        $logGestion->id_cliente = $idCliente;
        $logGestion->fecha_gestion = date("Y-m-d H:i:s");
        $logGestion->status_gestion = $codOperacion;
        $logGestion->mot_status = $motivo;
        $logGestion->nob_archivo = $nobArchivo;
        $logGestion->save();
        return true;
    }
}
