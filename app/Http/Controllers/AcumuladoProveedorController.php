<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Proveedor_Acumulado;

use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Session;

class AcumuladoProveedorController extends Controller
{
    public function index(){
        //validar session

        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = $this->llenarDT();
        return view('limiteProveedor/index_limite_acumulado',$data);    
    }





    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarDT(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 


        $swRegistro = 0;
        $data['records'] = Proveedor_Acumulado::join('clientes', 'proveedor_acumulados.id_cliente', '=', 'clientes.id')
                ->select('proveedor_acumulados.*', 'clientes.nob_cliente')
                ->orderBy('proveedor_acumulados.id', 'desc') // Ordena de más reciente a más antiguo
                ->get();


        if($data['records']->count() > 0){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    }     






}
