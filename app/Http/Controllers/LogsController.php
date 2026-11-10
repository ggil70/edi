<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Archivo_Descarga;
use App\Models\Archivo_Recibido;

use App\Models\Log_Error_Archivo;
use App\Models\Log_Error_Proceso;
use App\Models\Log_Servicio_Edi;




use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Session;

class LogsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function log_error_archivo(){ 
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = $this->llenarErrorArchivo();

        return view('logs/log_error_archivo',$data);  
    }    


    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarErrorArchivo(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data['records'] = Log_Error_Archivo::orderBy('id','desc')->get();
        $swRegistro = 0;
        if($data['records']->count() > 0){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    } 


    public function log_error_proceso(){ 
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = $this->llenarErrorProceso();

        return view('logs/log_error_proceso',$data);  
    }    



    public function llenarErrorProceso(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data['records'] = Log_Error_Proceso::orderBy('id','desc')->get();
        $swRegistro = 0;
        if($data['records']->count() > 0){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    }





    public function log_servicio_edi(){ 
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = $this->llenarLogServicioEdi();

        return view('logs/log_servicio_edi',$data);  
    }    


    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarLogServicioEdi(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        //$data['records'] = Log_Servicio_Edi::with('adm_usuarios:id,nob_usuario,ape_usuario') 
        //    ->latest('id') // Es un shortcut de orderBy('id', 'desc')
        //    ->get();

        $data['records'] = Log_Servicio_Edi::select(
            'log_servicio_edis.*', 
            'adm_usuarios.nob_usuario', 
            'adm_usuarios.ape_usuario'
            )
            ->join('adm_usuarios', 'log_servicio_edis.id_usuario', '=', 'adm_usuarios.id')
            ->orderBy('log_servicio_edis.fec_hora_servicio', 'desc')
            ->get();



        $swRegistro = 0;
        if($data['records']->count() > 0){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    } 




}
