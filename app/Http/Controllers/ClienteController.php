<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Proveedor_Limite;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class ClienteController extends Controller
{
    //

        public function index(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session  


        $swOperacion = 0;
        if(Session::get('swOperacionCliente')){
            $swOperacion = Session::get('swOperacionCliente'); 
        } 
        Session::put('swOperacionCliente',0); 
        $data = $this->llenarDT();
        $data['swOperacion'] = $swOperacion;
        return view('cliente/index_cliente',$data);    
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
        $cliente = new Cliente;        
        $data['tipoDocumento'] = $cliente->listaTipoDocumento();
        $data['swIncluir'] = 0;
        return view('cliente/create_cliente',$data);
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
       
       $cliente = new Cliente; 
       $data = Cliente::find($id);
       $data['tipoDocumento'] = $cliente->listaTipoDocumento();

       Session::put('idCliente',$data['id']);       
       Session::put('nobCliente',$data['nob_cliente']);       

       if($data){
           $data['swModificar'] = 0;
           return view('cliente/update_cliente',$data);                     
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
            'nob_cliente' => 'required',
            'doc_cliente' => 'required',
            'codEdi' => 'required',

        ],[
            'nob_cliente.required' => '¡El cliente es obligatorio',  
            'doc_cliente.required' =>'¡El RIF del Cliente es obligatorio',
            'codEdi.required' =>'¡El Codigo EDI es obligatorio',

        ]);

        $nobCliente = Session::get('nobCliente');
        $idCliente =  Session::get('idCliente');
        $cliente = Cliente::find($idCliente);

        if($nobCliente != $request->nob_cliente){
            $consulta = Cliente::where('nob_cliente',$request['nob_cliente'])->count();

            if($consulta > 0){ 
                $data = Cliente::find($idCliente);
                $data['tipoDocumento'] = $cliente->listaTipoDocumento();
                $data['swModificar'] = 1;
                return view('cliente/update_cliente',$data);     
            }
        }  

        $cliente = Cliente::find($idCliente);
        $cliente->nob_cliente = strtoupper($request->nob_cliente);  
        $cliente->tipo_documento = $request->tipo_doc;
        $cliente->nro_documento = $request->doc_cliente;
        $cliente->codigo_edi = $request->codEdi;

        if($request->estatus==true){
           $cliente->estatus_cliente = 1;      
        }else{
            $cliente->estatus_cliente = 0;
        }
        
        $cliente->save();

        //log transaccion banco
        //$id_ejecutor = Session::get('usuario.id');
        //$this->log_transaccion($id_ejecutor, 5, 4, $idAliado); 

        Session::put('swOperacionCliente',2);

        return Redirect::route('index_cliente');
    }   


    public function save_create(Request $request){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session  

        $request['nob_cliente'] = strtoupper($request['nob_cliente']);
        $request['codEdi'] = strtoupper($request['codEdi']);

        $validated = $request->validate([
            'nob_cliente' => 'required|unique:clientes,nob_cliente',
            'doc_cliente' => 'required|unique:clientes,nro_documento',
            'codEdi' => 'required',
        ],[
            'nob_cliente.required' => '¡El nombre es obligatorio',
            'nob_cliente.unique' => 'El nombre ingresado ya existe',
            'doc_cliente.required' =>'¡El RIF del Cliente es obligatorio',
            'doc_cliente.unique' => 'El Nro documento ingresado ya existe',
            'codEdi.required' => '¡El Codigo EDI es obligatorio',
        ]);

        $request['nob_cliente'] = strtoupper($request['nob_cliente']);

        $cliente = new Cliente;
        $cliente->nob_cliente = strtoupper($request->nob_cliente);
        $cliente->tipo_documento = $request->tipo_doc;
        $cliente->nro_documento = $request->doc_cliente;
        $cliente->codigo_edi = $request->codEdi;

        $cliente->save();

        //copiar
        $clienteId = $cliente->id;        

        $cliente = Cliente::find($clienteId);
        $cliente->save();

        $idCliente = $cliente->id;


        //crear limite del cliente
        $limite = new Proveedor_Limite;
        $limite->id_cliente = $idCliente;
        $limite->lim_max_nomina = 0.00;
        $limite->lim_max_proveedor = 0.00;
        $limite->save();



        //log transaccion banco
//        $id_ejecutor = Session::get('usuario.id');
//        $this->log_transaccion($id_ejecutor, 5, 3, $aliadoId); 

        Session::put('swOperacionCliente', 1); 

        return Redirect::route('index_cliente');
    }




    public function cliente_delete(Request $request, string $id)
    {
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session  

        $data = Cliente::find($id);
        if($data){
           return view('cliente/delete_cliente',$data);                     
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
      $cliente = Cliente::find($id);

        $cliente->estatus_cliente = 0;
        $cliente->save();

      Session::put('swModificarCliente', 3);
      return Redirect::route('index_cliente');

    }



    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarDT(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session  


        $swRegistro = 0;
        $data['records'] = Cliente::select('clientes.*')->orderBy('nob_cliente','asc')->get();

        if($data['records']->count() > 0){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    }    




/*    function log_transaccion($id, $id_transa, $id_accion, $id_actualizado){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session  

        
        //log transaccion
        $logTransaccion = new Log_Transaccion_Banco;
        $logTransaccion->id_usuario = $id;
        $logTransaccion->id_transaccion = $id_transa;
        $logTransaccion->id_accion = $id_accion;
        $logTransaccion->fec_transaccion = date("Y-m-d H:m:s");
        $logTransaccion->id_actualizado = $id_actualizado;
        $logTransaccion->ip = $_SERVER['REMOTE_ADDR'];            
        $logTransaccion->save();
    }
*/    








}
