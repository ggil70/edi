<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor_Acumulado extends Model
{
    protected $table = "proveedor_acumulados";
    

    public static function ActAcumulado($mtoTotal, $fechaValor, $idCliente, $tipoPago, $mtoLimNomina, $mtoLimProveedor){

        $acumulado = Proveedor_Acumulado::where('id_cliente', $idCliente)
                ->where('fec_acumulado', $fechaValor)
                ->first();
        if($acumulado){
            if($tipoPago == 'SAL'){
                $mtoAcumulado = $acumulado->mto_acum_nomina + $mtoTotal;
                $acumulado->mto_acum_nomina = $mtoAcumulado;
            }else{
                $mtoAcumulado = $acumulado->mto_acum_proveedor + $mtoTotal;
                $acumulado->mto_acum_proveedor = $mtoAcumulado;
            }
            $acumulado->save();
        }else{
            $acumulado = new Proveedor_Acumulado();
            $acumulado->id_cliente = $idCliente;
            $acumulado->fec_acumulado = $fechaValor;
            $acumulado->mto_max_nomina = $mtoLimNomina;
            $acumulado->mto_max_proveedor = $mtoLimProveedor;
            if($tipoPago == 'SAL'){
                $acumulado->mto_acum_nomina = $mtoTotal;
            }else{
                $acumulado->mto_acum_proveedor = $mtoTotal;
            } 
            $acumulado->save();   
        } 

    }



    public static function modAcumulado($mtoRechazo, $fechaValor, $idCliente, $tipoPago){
        if($mtoRechazo > 0.00){
            $acumulado = Proveedor_Acumulado::where('id_cliente', $idCliente)
                ->where('fec_acumulado', $fechaValor)
                ->first();


            if($acumulado){
                if($tipoPago == 'SAL'){
                    if($mtoRechazo > 0.00){
                        $mtoAcumulado = $acumulado->mto_acum_nomina - $mtoRechazo;
                        $acumulado->mto_acum_nomina = $mtoAcumulado;
                    }    
                }else{
                    if($mtoRechazo > 0.00){
                        $mtoAcumulado = $acumulado->mto_acum_proveedor - $mtoRechazo;
                        $acumulado->mto_acum_proveedor = $mtoAcumulado;
                    }    
                }
                $acumulado->save();
            }  
        }       
    }




    public static function reversarAcumulado($mtoTotal, $fechaValor, $idCliente, $tipoPago){

        $acumulado = Proveedor_Acumulado::where('id_cliente', $idCliente)
                ->where('fec_acumulado', $fechaValor)
                ->first();
        if($acumulado){
            if($tipoPago == 'SAL'){
                $mtoAcumulado = $acumulado->mto_acum_nomina - $mtoTotal;
                $acumulado->mto_acum_nomina = $mtoAcumulado;
            }else{
                $mtoAcumulado = $acumulado->mto_acum_proveedor - $mtoTotal;
                $acumulado->mto_acum_proveedor = $mtoAcumulado;
            }
            $acumulado->save();
        }
        return true;

    }    

}
