<?php

namespace App\Http\Controllers;



use App\Traits\CrearBanstas;
use App\Traits\FuncArchivos;
use App\Traits\ValidarEdi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;



class CrearArchivoBanstaController extends Controller
{
    //Libreria Crear Archivo Bansta.
    use CrearBanstas;
    use FuncArchivos;

    //validar proceso EDI
    use ValidarEdi; 


    public function callCrearBansta(){

        Session::put('swFormBansta', 1);
        return Redirect::route('crearArchivoBansta');
    }

    public function crearArchivoBanstas(){
        
        //validar estatus servicio edi.
        //trials ValidarEdi
        $respServicio = $this->validarServicioEdi();

        
        $resp = $this->crearArchivoBansta();
        if($resp == 0){
            $data['respuesta'] = 0; 
        }else{
            $data['respuesta'] = 1;         
        }
        return view('procesoPago/crearBansta', $data);    
    }    

}
