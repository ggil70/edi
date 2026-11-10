<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = "clientes";



    public function listaTipoDocumento(){
    $tipoD = array(
        'J'=>'J',
        'V'=>'V',
        'E'=>'E',
        'G'=>'G',
        'P'=>'P'
    );  
    return $tipoD; 
    }



}
