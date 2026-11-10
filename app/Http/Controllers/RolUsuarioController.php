<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Adm_Rol;

use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Session;







class RolUsuarioController extends Controller
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
        if(Session::get('swOperacionRol')){
            $swOperacion = Session::get('swOperacionRol'); 
        } 
        Session::put('swOperacionRol',0); 
        $data = $this->llenarDT();
        $data['swOperacion'] = $swOperacion;        
        return view('rols/index_rol',$data);    
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data['swIncluir'] = 0;
        return view('rols/create_rol',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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

       $data = Adm_Rol::find($id);

       Session::put('nobRol',$data['nob_rol']); 
       Session::put('idRol',$data['id']); 

       if($data){
         $data['swModificar'] = 0;
         return view('rols/update_rol',$data);                     
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
            'nob_rol' => ['required']
        ]);

        $nobRol = Session::get('nobRol'); 
        $idRol = Session::get('idRol');

        if($nobRol != $request->nob_rol){
            $consulta = Adm_Rol::where('nob_rol',$request['nob_rol'])->count();
            if($consulta > 0){ 
                $data = Adm_Rol::find($idRol);
                $data['swModificar'] = 1;
                return view('rols/update_rol',$data);     
            }
        }  

        //$id = session()->get('idRol');
        $rol = Adm_Rol::find($idRol);
        $rol->nob_rol = strtoupper($request->nob_rol);

        if($request->estatus==true){
           $rol->estatus = 1;      
        }else{
            $rol->estatus = 0;
        }
        $rol->save();
        


        //log transaccion banco
        $id_ejecutor = Session::get('usuario.id');
        //$this->log_transaccion($id_ejecutor, 1, 4, $idRol);

        Session::put('swOperacionRol',2); 
        return Redirect::route('index_rol');
    }   


    public function save_create(Request $request){

        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $request['nob_rol'] = strtoupper($request['nob_rol']);


        $validated = $request->validate([
            'nob_rol' => 'required|unique:adm_roles,nob_rol',
        ],[
            'nob_rol.required' => '¡El nombre es obligatorio',  
            'nob_rol.unique' => 'El nombre editado ya existe',
        ]);

        $rol = new Adm_Rol;

        if($request->estatus==true){
           $rol->estatus = 1;      
        }else{
            $rol->estatus = 0;
        }

        $rol->nob_rol = strtoupper($request->nob_rol);
        $rol->save();

        $idRol = $rol->id;

        $id_ejecutor = Session::get('usuario.id');
        //$this->log_transaccion($id_ejecutor, 1, 3, $idRol);


        Session::put('swOperacionRol',1);    

        return Redirect::route('index_rol');
    }




    public function rol_delete(Request $request, string $id){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = Adm_Rol::find($id);
        if($data){
           return view('rols/delete_rol',$data);                     
        }else{
           die("Error inesperado");
        }
    }


/**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request)
    {
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session         
        $id = $request->id;
        $rol = Adm_Rol::find($id);
        $rol->estatus = 0;
        $rol->save();
        $id_ejecutor = Session::get('usuario.id');
       // $this->log_transaccion($id_ejecutor, 1, 5, $id);
        Session::put('swOperacionRol',3);
        return Redirect::route('index_rol');
    }



    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarDT(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 


        $swRegistro = 0;
        $data['records'] = Adm_Rol::orderBy('nob_rol','asc')->get();

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
