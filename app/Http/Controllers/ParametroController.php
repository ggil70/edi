<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Parametro;

use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Session;

class ParametroController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(){
        //validar session

        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $swOperacion = 0;
        if(Session::get('swOperacionParametro')){
            $swOperacion = Session::get('swOperacionParametro'); 
        } 
        Session::put('swOperacionParametro',0); 
        $data = $this->llenarDT();
        $data['swOperacion'] = $swOperacion;        
        return view('configuracion/index_parametro',$data);    
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

       $data = Parametro::find($id);

       Session::put('codParametro',$data['cod_parametro']); 
       Session::put('idParametro',$data['id']); 

       if($data){
         $data['swModificar'] = 0;
         return view('configuracion/update_parametro',$data);                     
       }else{
           die("Error inesperado");
       }
    }

    /**
     * Update the specified resource in storage.
     */

    public function save_update(Request $request){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session         

        $validated = $request->validate([
            'mto_vuelto' => 'required'

        ],[
            'mto_vuelto.required' => '¡El monto maximo del vuelto es obligatorio',  
        ]);

        $idParametro = Session::get('idParametro');
        $mtoVuelto = str_replace(",", ".", $request->mto_vuelto);

        $parametro = Parametro::find($idParametro);
        $parametro->valor = $mtoVuelto;

        $parametro->save();
        


        //log transaccion banco
        $id_ejecutor = Session::get('usuario.id');
        //$this->log_transaccion($id_ejecutor, 1, 4, $idRol);

        Session::put('swOperacionParametro',2); 
        return Redirect::route('index_parametro');
    }   


    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarDT(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 


        $swRegistro = 0;
        $data['records'] = Parametro::orderBy('cod_parametro','asc')->get();

        if($data['records']->count() > 0){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    }     


    function log_transaccion($id, $id_transa, $id_accion, $id_actualizado){
        //log transaccion banco

        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 
        
        $logTransaccion = new Log_Transaccion_Banco;
        $logTransaccion->id_usuario = $id;
        $logTransaccion->id_transaccion = $id_transa;
        $logTransaccion->id_accion = $id_accion;
        $logTransaccion->fec_transaccion = date("Y-m-d H:m:s");
        $logTransaccion->id_actualizado = $id_actualizado;
        $logTransaccion->ip = $_SERVER['REMOTE_ADDR'];            
        $logTransaccion->save();
    }    


}
