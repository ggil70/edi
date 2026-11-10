<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Proveedor_Limite;
use App\Models\Proveedor_Acumulado;

use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Session;

class LimiteProveedorController extends Controller
{
    public function index(){
        //validar session

        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $swOperacion = 0;
        if(Session::get('swOperacionLimite')){
            $swOperacion = Session::get('swOperacionLimite'); 
        } 
        Session::put('swOperacionLimite',0); 
        $data = $this->llenarDT();
        $data['swOperacion'] = $swOperacion;        
        return view('limiteProveedor/index_limite',$data);    
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

       $data = Proveedor_Limite::find($id);

        $data = Proveedor_Limite::join('clientes', 'proveedor_limites.id_cliente', '=', 'clientes.id')
                ->select('proveedor_limites.*', 'clientes.nob_cliente')
                ->where('proveedor_limites.id',$id)
                ->orderBy('clientes.nob_cliente', 'desc') // Ordena de más reciente a más antiguo
                ->first();
       Session::put('idLimite',$data['id']); 
       Session::put('idCliente',$data['id_cliente']); 

       if($data){
         $data['swModificar'] = 0;
         return view('limiteProveedor/update_limite',$data);                     
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
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session  

        $validated = $request->validate([
            'mto_nomina' => 'required',
            'mto_proveedor' => 'required',
        ],[
            'mto_nomina.required' => '¡El monto Limite maximo para nomina es obligatorio',  
            'mto_proveedor.required' => '¡El monto Limite maximo para proveedores es obligatorio',  
        ]);

        $idLimite = Session::get('idLimite');
        $mtoNomina = str_replace(",", ".", $request->mto_nomina);
        $mtoProveedor = str_replace(",", ".", $request->mto_proveedor);

        $limite = Proveedor_Limite::find($idLimite);
        $limite->lim_max_nomina = $mtoNomina;
        $limite->lim_max_proveedor = $mtoProveedor;
        $limite->save();


        //Modificar campos de porvedor acumulado
        $idCliente = Session::get('idCliente'); 
        $fecha = date('Y-m-d');
        $acumulado = Proveedor_Acumulado::where('id_cliente', $idCliente)
                                ->where('fec_acumulado', $fecha)->first();
        if($acumulado){
            $acumulado->mto_max_nomina = $mtoNomina;
            $acumulado->mto_max_proveedor = $mtoProveedor;
            $acumulado->save();
        }                        
        
        //log transaccion banco
        $id_ejecutor = Session::get('usuario.id');
        //$this->log_transaccion($id_ejecutor, 1, 4, $idRol);

        Session::put('swOperacionLimite',2); 
        return Redirect::route('index_limite');
    }   


    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarDT(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 


        $swRegistro = 0;
        $data['records'] = Proveedor_Limite::join('clientes', 'proveedor_limites.id_cliente', '=', 'clientes.id')
                ->select('proveedor_limites.*', 'clientes.nob_cliente')
                ->orderBy('clientes.nob_cliente', 'desc') // Ordena de más reciente a más antiguo
                ->get();

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
