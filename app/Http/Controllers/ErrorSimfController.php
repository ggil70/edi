<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Simf_Error;

use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Session;







class ErrorSimfController extends Controller
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
        if(Session::get('swOperacionSimfError')){
            $swOperacion = Session::get('swOperacionSimfError'); 
        } 
        Session::put('swOperacionSimfError',0); 
        $data = $this->llenarDT();
        $data['swOperacion'] = $swOperacion;        
        return view('simf_error/index_simfError',$data);    
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
        return view('simf_error/create_simfError',$data);
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

       $data = Simf_Error::find($id);

       Session::put('codSimfError',$data['codigo']); 
       Session::put('idSimfError',$data['cod_simf']); 

       if($data){
         $data['swModificar'] = 0;
         return view('simf_error/update_simfError',$data);                     
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
            'codigo' => ['required']
        ]);

        $codSimfError = Session::get('codSimfError'); 
        $idSimfError = Session::get('idSimfError');

        if($codSimfError != $request->codigo){
            $consulta = Simf_Error::where('codigo',$request['codigo'])->count();
            if($consulta > 0){ 
                $data = Simf_Error::find($idSimfError);
                $data['swModificar'] = 1;
                return view('simf_error/update_simfError',$data);     
            }
        }  

        $simfError = Simf_Error::find($idSimfError);
        $simfError->codigo = strtoupper($request->codigo);
        $simfError->cod_simf = strtoupper($request->codSimf);
        $simfError->desc_simf = strtoupper($request->DescSimf);
        $simfError->save();
        
        //log transaccion banco
        $id_ejecutor = Session::get('usuario.id');
        //$this->log_transaccion($id_ejecutor, 1, 4, $idRol);

        Session::put('swOperacionSimfError',2); 
        return Redirect::route('index_simfError');
    }   


    public function save_create(Request $request){

        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $request['codigo'] = strtoupper($request['codigo']);
        $request['codSimfError'] = strtoupper($request['codSimfError']);        
        $request['descSimfError'] = strtoupper($request['descSimfError']);        


        $validated = $request->validate([
            'codigo' => 'required|max:10|unique:simf_errores,codigo',
            'codSimfError' => 'required|max:10',
            'descSimfError' => 'required',
        ],[
            'codigo.required' => '¡El codigo es obligatorio',  
            'codigo.unique' => 'El codigo editado ya existe',
            'codigo.max' => 'El Maximo es 10 digitos',
            'codSimfError.required' => '¡El codigo Simf es obligatorio',  
            'codigoSimfError.max' => 'El Maximo es 10 digitos',
            'descSimfError.required' => '¡El codigo es obligatorio',
        ]);

        $simfError = new Simf_Error;

        if($request->estatus==true){
           $simfError->estatus = 1;      
        }else{
            $simfError->estatus = 0;
        }

        $simfError->codigo = strtoupper($request->codigo);
        $simfError->cod_simf = strtoupper($request->codSimfError);
        $simfError->desc_simf = strtoupper($request->descSimfError);
        $simfError->estatus = 1;

        $simfError->save();

        $idSimf = $simfError->id;

        $id_ejecutor = Session::get('usuario.id');
        //$this->log_transaccion($id_ejecutor, 1, 3, $idRol);


        Session::put('swOperacionSimf',1);    

        return Redirect::route('simfError_index');
    }




    public function simfError_delete(Request $request, string $id){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = Simf_Error::find($id);
        if($data){
           return view('simf_error/delete_simfError',$data);                     
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
        $simfError = Simf_Error::find($id);
        $simfError->estatus = 0;
        $simfError->save();
        $id_ejecutor = Session::get('usuario.id');
       // $this->log_transaccion($id_ejecutor, 1, 5, $id);
        Session::put('swOperacionSimfError',3);
        return Redirect::route('index_simfError');
    }



    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarDT(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 


        $swRegistro = 0;
        $data['records'] = Simf_Error::orderBy('codigo','asc')->get();

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
