<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio_Edi extends Model
{
        protected $table = "servicio_edis";


     public static function estatus_servicio(){
        $data =  Servicio_Edi::first();
        if($data){
            return $data['estatus_servicio'];
        }else{
            die("No encontrado");    
            return 2;    
        }
 

     }
     

}
