<?php

namespace App\Http\Controllers;

use App\Models\Servicio_Edi;
use App\Models\Log_Servicio_Edi;

 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class ServicioEdiController extends Controller{
    //
    public function actServicioEdi(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = Servicio_Edi::join('adm_usuarios', 'servicio_edis.id_usuario_ultimo', '=', 'adm_usuarios.id')
            ->select('servicio_edis.*', 'adm_usuarios.nob_usuario', 'adm_usuarios.ape_usuario')
            ->first();


        Session::put('servicioEdi.id', $data->id);


        if($data){
            return view('servicioEdi/actServicioEdi',$data);  
        }else{
            return Redirect::route('inicio'); 
        }    
    }


    public function reactivarEdi(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = Servicio_Edi::join('adm_usuarios', 'servicio_edis.id_usuario_ultimo', '=', 'adm_usuarios.id')
            ->select('servicio_edis.*', 'adm_usuarios.nob_usuario', 'adm_usuarios.ape_usuario')
            ->first();
        

        if($data){
            return view('servicioEdi/reactivarServicioEdi',$data);  
        }else{
            return Redirect::route('inicio'); 
        }    
    }



    public function activarEdi(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }      

        $idServicio = Session::get('servicioEdi.id');
        $motivo = "";
        $status = 1;
        $idUsuario = Session::get('usuario.id');
        $fecha = date('Y-m-d h:i:s');

        $servicioEDI = Servicio_Edi::find($idServicio);
        $servicioEDI->estatus_servicio = $status;
        $servicioEDI->fec_hora_servicio = $fecha;
        $servicioEDI->id_usuario_ultimo = $idUsuario;
        $servicioEDI->motivo = $motivo;        
        $servicioEDI->save();

        ////Registrar en el log Servicio EDI
        $logServicio = new Log_Servicio_Edi;
        $logServicio->estatus_servicio = $status;
        $logServicio->fec_hora_servicio = $fecha;
        $logServicio->id_usuario = $idUsuario;
        $logServicio->motivo_servicio = "";
        $logServicio->save();


        $data = Servicio_Edi::join('adm_usuarios', 'servicio_edis.id_usuario_ultimo', '=', 'adm_usuarios.id')
            ->select('servicio_edis.*', 'adm_usuarios.nob_usuario', 'adm_usuarios.ape_usuario')
            ->first();

        return view('servicioEdi/actServicioEdi',$data);  
    }


    public function detenerEdi(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = Servicio_Edi::join('adm_usuarios', 'servicio_edis.id_usuario_ultimo', '=', 'adm_usuarios.id')
            ->select('servicio_edis.*', 'adm_usuarios.nob_usuario', 'adm_usuarios.ape_usuario')
            ->first();
        

        if($data){
            return view('servicioEdi/detenerMotivo',$data);  
        }else{
            return Redirect::route('inicio'); 
        }    
    }

    public function save_detenerEDI(Request $request){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 
        $validated = $request->validate([
            'motivo' => 'required'

        ],[
            'motivo.required' => '¡El motivo es obligatorio',  
        ]);

        $idServicio = Session::get('servicioEdi.id');

        $motivo = $request->motivo;
        $status = 0;
        $idUsuario = Session::get('usuario.id');
        $fecha = date('Y-m-d h:i:s');

        $servicioEDI = Servicio_Edi::find($idServicio);
        $servicioEDI->estatus_servicio = $status;
        $servicioEDI->fec_hora_servicio = $fecha;
        $servicioEDI->id_usuario_ultimo = $idUsuario;
        $servicioEDI->motivo = $motivo;        
        $servicioEDI->save();

        ////Registrar en el log Servicio EDI
        $logServicio = new Log_Servicio_Edi;
        $logServicio->estatus_servicio = $status;
        $logServicio->fec_hora_servicio = $fecha;
        $logServicio->id_usuario = $idUsuario;
        $logServicio->motivo_servicio = $motivo;
        $logServicio->save();

        $data = Servicio_Edi::join('adm_usuarios', 'servicio_edis.id_usuario_ultimo', '=', 'adm_usuarios.id')
            ->select('servicio_edis.*', 'adm_usuarios.nob_usuario', 'adm_usuarios.ape_usuario')
            ->first();

        return view('servicioEdi/actServicioEdi',$data);         


    }    

}    