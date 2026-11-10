<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Archivo_Descarga;
use App\Models\Archivo_Recibido;
use App\Models\Archivo_Detalle;
use App\Models\Archivo_Encabezado;


use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Session;

class GestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function gestionDescarga(){

        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = $this->llenarDescarga();
        return view('gestion/gestion_descarga',$data);    
    }

    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarDescarga(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data['records'] = Archivo_Descarga::where('nro_archivo','<>',0)
                           ->orderBy('id','desc')->get();
        $swRegistro = 0;
        if($data['records']->count() > 0){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    }     


    public function gestionArchivo(){

        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = $this->llenarArchivo();
        return view('gestion/gestion_archivo',$data);    
    }

    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarArchivo(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

       /* $data['records'] = Archivo_Recibido::join('clientes', 'archivo_recibidos.id_cliente', '=', 'clientes.id')
                ->select('archivo_recibidos.*', 'clientes.nob_cliente')
                ->orderBy('archivo_recibidos.id', 'desc') // Ordena de más reciente a más antiguo
                ->get();
        */
        
        $data['records'] = Archivo_Recibido::orderBy('id', 'desc')->get();
        



                //->orderBy('clientes.nob_cliente', 'desc') // Ordena de más reciente a más antiguo

        $swRegistro = 0;
        if($data['records']->count() > 0){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    }   


    public function gestion_archivo_detalle($idArchivo){
        //validar session

        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

       /* $data = Archivo_Recibido::leftJoin('clientes', 'archivo_recibidos.id_cliente', '=', 'clientes.id')
                ->join('archivo_encabezados', 'archivo_recibidos.id', '=', 'archivo_encabezados.id_archivo')   
                ->select('archivo_recibidos.*', 'clientes.nob_cliente','archivo_encabezados.lote')
                ->where('archivo_recibidos.id',$idArchivo)
                ->first();
        */
        $data = Archivo_Recibido::leftJoin('clientes', 'archivo_recibidos.id_cliente', '=', 'clientes.id')
            ->leftJoin('archivo_encabezados', 'archivo_recibidos.id', '=', 'archivo_encabezados.id_archivo') // Cambiado a leftJoin
            ->select(
                'archivo_recibidos.*', 
                'clientes.nob_cliente',
                'archivo_encabezados.lote'
            )
            ->where('archivo_recibidos.id', $idArchivo)
            ->first();                


        if($data){
            return view('gestion/detalle_archivo',$data); 
        }else{
            return Redirect::route('inicio');
        }    
    }  


    public function gestion_detalle_pago($idArchivo, $nobArchivo){

        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = $this->llenarDetalle($idArchivo);
        $data['nobArchivo'] = $nobArchivo;
        $data['idArchivo'] = $idArchivo;
        return view('gestion/gestion_mostrar_detalle',$data);    
    }

    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarDetalle($idAchivo){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data['records'] = Archivo_Detalle::where('id_archivo', $idAchivo)
                            ->orderBy('nro_linea','asc')->get();
        $swRegistro = 0;
        if($data['records']->count() > 0){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    }   

    public function gestion_encabezado_pago($idArchivo, $nobArchivo){

        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data = $this->llenarEncabezado($idArchivo);
        $data['nobArchivo'] = $nobArchivo;
        $data['idArchivo'] = $idArchivo;

        $data['desc_error_encabezado'] = $data['record']['desc_error_encabezado'];
        $data['linea'] = $data['record']['linea'];

        return view('gestion/gestion_mostrar_encabezado',$data);    
    }

    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarEncabezado($idAchivo){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data['record'] = Archivo_Encabezado::where('id_archivo', $idAchivo)
                            ->first();
        $swRegistro = 0;
        if($data['record']){
            $swRegistro = 1;
        }else{
            $swRegistro = 0;
        }
        $data['swRegistro'] = $swRegistro;
        return $data;
    }   



}
