<?php

namespace App\Http\Controllers;

use App\Models\Adm_Usuario;

 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class UsuarioController extends Controller
{
    //

    public function index()
    {
        $data['swValidar'] = 0;
        return view("login/login_admin",$data);
    }

    public function validar_acceso(Request $request){
        $login = strtoupper($request->usuario);
        $validated = $request->validate([
            'usuario' => 'required|min:4',
            'pass' => 'required|min:8',
        ],[
            'usuario.required' => '¡El Login es obligatorio',  
            'usuario.unique' => '¡El Login ya se encuentra registrado',  
            'usuario.min' => '¡El Login esta incompleto, debe tener un mínimo de 4 Carácteres',  
            'pass.required' => '¡El password es obligatorio',  
            'pass.min' => '¡El password esta incompleto, debe tener un mínimo de 8 Carácteres',  
        ]);   

        $login = strtoupper($request->usuario);
        $pass = $request->pass;
        $consulta = Adm_Usuario::where('login',$login)
                           ->where('password',$pass)
                           ->first();
        if($consulta){
            if($consulta->estatus == 1){ 
                Session::put('usuario.id', $consulta->id);
                Session::put('usuario.nobUsuario', $consulta->nob_usuario);
                Session::put('usuario.apeUsuario', $consulta->ape_usuario);
                Session::put('usuario.email', $consulta->email_usuario);
                Session::put('usuario.idRol', $consulta->id_rol);
                Session::put('usuario.idUnidad', $consulta->id_unidad);

                //log usuario
                //$this->log_usuario($consulta->id, $consulta->id_aliado, $consulta->id, 6);
                return view('home/presentacion');
            }else{
                die("Final");
                //$data['swValidar'] = 1;
                $data['mensaje'] = '!Error validación. Usuario Inhabilitado, consultar al Administrador.' ;
                return Redirect::route('valError',2);
            }    
        }else{

            //$data['swValidar'] = 1;
            $data['mensaje'] = '!Error validación. Login o Password incorrecto.';
            return Redirect::route('valError',1);
        }
    }   

    public function index_error($sw)
    {
        $data['swValidar'] = 1;
        if($sw == 1){  
            $data['mensaje'] = '!Error validación. Login o Password incorrecto.';
        }else{  
            $data['mensaje'] = '!Error validación. Usuario Inhabilitado, consultar al Administrador.' ;
        }  
        return view("login/login_admin",$data);
    }    

    function cerrar_sesion(){
        //validar session del usuario
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }      

        //Registrar cierre de session del usuario
        //$this->log_usuario($idUser, $idUser, $idUser, 7);

        //Borrar variable de session
        Session::forget('usuario.id');
        Session::forget('usuario.nobUsuario');
        Session::forget('usuario.apeUsuario');
        Session::forget('usuario.email');
        Session::forget('usuario.idRol');
        Session::forget('usuario.idUnidad');
        //Fin validar session   
        return Redirect::route('inicio');
    }

////////////////////////////// Olvido clave  ///////////////////////////////
    public function olvido_clave(){
        $data['swValidarEmail'] = 0;
        return view('usuarios/olvido',$data);
    } 

    public function validar_olvido(Request $request){
        $validated = $request->validate([        
        'email' => 'required|email',
        ],[
            'email.required' => '¡El Email del Cliente es obligatorio', 
            'email.email' => '¡El Email es incorrecto',            
        ]);
    
        $email = strtolower($request->email); 
        $data = Adm_Usuario::where('email', $email)
                         ->where('estatus',1)
                         ->first(); 
        if($data){ 
            $codSeguridad = random_int(100000, 999999);
            $data['codigo'] = $codSeguridad;
            $data['swValidarCodigo'] =0;
            Session::put('idUsuario', $data['id']);  
            Session::put('nobUsuario', $data['nob_usuario']);  
            Session::put('apeUsuario', $data['ape_usuario']);  
            Session::put('idRol', $data['id_rol']);  
            Session::put('codSeguridad', $codSeguridad);
            //enviar correo
            return view('usuarios/ingreso_codigo',$data);
        }else{
            $data['swValidar'] = 1;
            $data['mensaje'] = '!Error validación. El email suministrado no esta asociado a ningún usuario registrado.' ;
            return view("login/login_admin",$data);            
        }
    }    


    public function validar_codigo(Request $request){
        $validated = $request->validate([        
        'codigo' => 'required|min:6',
        ],[
            'codigo.required' => '¡El codigo de seguridad es obligatorio', 
            'codigo.min' => '¡El codigo de seguridad ingresado esta incompleto', 
        ]);

        $codSeguridad = Session::get('codSeguridad');
        if($request->codigo ==  $codSeguridad){
            return view('usuarios/cambio_clave');            
        }else{
            $data['codigo'] = $codSeguridad;
            $data['swValidarCodigo'] =1;            
            return view('usuarios/ingreso_codigo',$data);            
            
        }
    }    


    public function cambiar_clave(Request $request){

        $clave = $request->clave;
        $idUsuario = Session::get('idUsuario');
        
        $usuario = Adm_Usuario::find($idUsuario);
        $idUsuario = $usuario->id;
        $idAliado = $usuario->id_aliado;
        $usuario->password = $request->clave;
        $usuario->save();        
        if($usuario->save()){
            //$this->log_usuario($idUsuario, $idAliado, $idUsuario, 5);
            return Redirect::route('inicio');
        }else{
            dd('Error inesperado');
        }
    }   







}    