<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Adm_Usuario;
use App\Models\Rol_Usuario;
use App\Models\Log_Usuario;


use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;


class UsuarioAdmController extends Controller
{
    
    public function usuarioAdm_index (){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $swOperacion = 0;
        if(Session::get('swOperacionUsuarioAdm')){
            $swOperacion = Session::get('swOperacionUsuarioAdm'); 
        } 
        Session::put('swOperacionUsuarioAdm',0);         
        $data = $this->llenarDT();
        $data['swOperacion'] = $swOperacion;
        return view('usuarioAdm/index_usuarioAdm',$data);    
    }


    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarDT(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $swRegistro = 0;
      
        $data['records'] = Adm_Usuario::select('adm_usuarios.*')
            ->orderBy('ape_usuario')
            ->orderBy('nob_usuario')
            ->get();

        $data['records'] = Adm_Usuario::join('adm_roles', 'adm_usuarios.id_rol', '=', 'adm_roles.id')
            ->select('adm_usuarios.*', 'adm_roles.nob_rol')
            ->get();



        if($data['records']->count() > 0){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    }    


    public function create(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 
        $roles = Rol_Usuario::where('estatus', 1)
                   ->orderBy('nob_rol')
                   ->get();

        $data['roles'] = $roles;           

        return view('usuarioAdm/create_usuarioAdm',$data);
    }    

    public function save_create(Request $request){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session       
        $request['login'] = strtoupper($request['login']);
        $request->email = trim(strtolower($request->email));     

        $validated = $request->validate([
            'nob_usuario' => 'required',
            'ape_usuario' => 'required',
            'login' => 'required|unique:adm_usuarios,login|min:4',
            'email' => 'required|email|unique:adm_usuarios,email',
        ],[
            'nob_usuario.required' => '¡El nombre del usuario es obligatorio',  
            'ape_usuario.required' => '¡El apellido del usuario es obligatorio',  
            'login.required' => '¡El login del usuario es obligatorio',  
            'login.unique' => '¡El login del usuario ya existe',  
            'login.min' => '¡El login debe tener 4 Carácteres como nínimo',  
            'email.required' => '¡El Email del usuario es obligatorio',  
            'email.email' => '¡El email ingreado es incorrecto', 
            'email.unique' => '¡El Email del usuario ya existe',   
        ]);

        
        $usuario = new Adm_Usuario;
        $usuario->nob_usuario = strtoupper($request->nob_usuario);
        $usuario->ape_usuario = strtoupper($request->ape_usuario);
        $usuario->email = strtolower($request->email);
        $usuario->login = strtoupper($request->login);
        $usuario->id_rol = $request->idRol;
        $usuario->password = '12345678';

        $usuario->save();

        $idUsuario = $usuario->id;   

        $id_ejecutor = Session::get('usuario.id');  //id ejecutor     
       

        //log usuarios
        //$this->log_usuario($id_ejecutor, 1, $idUsuario, 1);

        Session::put('swOperacionUsuarioAdm',1);         

        $data = $this->llenarDT();
        return Redirect::route('usuarioAdm_index');

    }


    public function edit($id){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session       
        Session::put('idUsuario',$id);  
        $roles = Rol_Usuario::where('estatus', 1)
                   ->orderBy('nob_rol')
                   ->get();
        $data = Adm_Usuario::find($id);
        $data['roles'] = $roles;
        return view('usuarioAdm/update_usuarioAdm',$data);
    }


    public function save_update(Request $request){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session       
        $validated = $request->validate([
            'nob_usuario' => 'required',
            'ape_usuario' => 'required',
            'email' => 'required|email',
        ],[
            'nob_usuario.required' => '¡El nombre del usuario es obligatorio',  
            'ape_usuario.required' => '¡El apellido del usuario es obligatorio',  
            'login.required' => '¡El login del usuario es obligatorio',  
            'login.min' => '¡El login debe tener 8 Carácteres como nínimo',  
            'email.required' => '¡El Email del usuario es obligatorio',  
            'email.email' => '¡El email ingreado es incorrecto',  
        ]);

        $idUsuario = Session::get('idUsuario');
        $usuario = Adm_Usuario::find($idUsuario);
        $usuario->ape_usuario = strtoupper($request->ape_usuario);
        $usuario->nob_usuario = strtoupper($request->nob_usuario);
        $usuario->email = strtolower($request->email);
        $usuario->id_rol = $request->idRol;
        $usuario->login = strtoupper($request->login);
        $usuario->save();
        //log usuarios
        $id_ejecutor = Session::get('usuario.id');  //id ejecutor
        //$this->log_usuario($id_ejecutor, 1, $idUsuario, 2);
        Session::put('swOperacionUsuarioAdm',2);         
        $data = $this->llenarDT();
        return Redirect::route('usuarioAdm_index');
    }   


    public function usuarioAdm_delete($id){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session     
        $data = Adm_Usuario::find($id);
        if($data){
           //$data['nob_estatus'] = "INACTIVO"; 
           if($data['estatus'] == 0){
                $data['nob_estatus'] = "Activar";
           }else{
                $data['nob_estatus'] = "Inhabilitar"; 
           }  
           return view('usuarioAdm/delete_usuarioAdm',$data);                     
        }else{
           die("Error inesperado");
        }
    }   

    public function delete(Request $request)
    {
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session      
       $id = $request->id;
       $idEstatus = $request->idEstatus;
       $usuario = Adm_Usuario::find($id);
       if($idEstatus == 0){
           $usuario->estatus = 1;
           $accion = 4;
           Session::put('swOperacionUsuarioAdm',4);

       }else{
           Session::put('swOperacionUsuarioAdm',3);  
           $usuario->estatus = 0;
           $accion = 3;
       }
      $usuario->save();


        //log usuarios
        $id_ejecutor = Session::get('usuario.id');  //id ejecutor
//        $this->log_usuario($id_ejecutor, 1, $id, $accion);

     

      return Redirect::route('usuarioAdm_index');
      
      
    }



    function log_usuario($idEjecutor, $idAliado, $id_actualizado, $tipoOperacion){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 
                
        $logUsuarios = new Log_Usuario;
        $logUsuarios->id_ejecutor = $idEjecutor;
        $logUsuarios->ip = $_SERVER['REMOTE_ADDR'];
        $logUsuarios->id_aliado = $idAliado;
        $logUsuarios->id_usuario = $id_actualizado;
        $logUsuarios->tipo_operacion = $tipoOperacion;  //tipo operacio incluir
        $logUsuarios->fec_operacion = date("Y-m-d H:m:s");
        $logUsuarios->save();
    }   

}
