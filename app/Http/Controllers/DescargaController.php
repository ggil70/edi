<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Importante importar esto para trabajar con archivo fisico
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Models\Archivo_Recibido;


class DescargaController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function verArchivo(){
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 
        $data = $this->llenarArchivo();
        return view('descargar/ver_archivo',$data);  
    } 

    //arreglo resultante de los registro de la tabla para llenar datatable
    public function llenarArchivo(){
        //validar session
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        //Fin validar session 

        $data['records'] = Archivo_Recibido::join('clientes', 'archivo_recibidos.id_cliente', '=', 'clientes.id')
                ->select('archivo_recibidos.*', 'clientes.nob_cliente')
                ->orderBy('archivo_recibidos.id', 'desc') // Ordena de más reciente a más antiguo
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




    public function descargaArchivo($nombreArchivo){

        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }  

        //$ruta  = storage_path('app\archivos\procesados\\' . $nombreArchivo);
        $ruta = storage_path('app/archivos/procesados/' . $nombreArchivo);

        // Verificamos si existe antes de intentar leerlo

            if (File::exists($ruta)) {
                $lineas = File::lines($ruta); 
                $archivo = ''; 
                $nroLinea = 0;
                foreach ($lineas as $linea) {
                    if($nroLinea == 0){
                        $nroLinea+=1;
                        $archivo.=$linea . "\n";
                    }else{
                        if(!empty($linea)){
                            $nroLinea+=1;
                            $archivo.=$nroLinea . " - " . $linea . "\n";
                        }    
                    }    
                }    
                $data = [
                   'archivo'=>$archivo,
                   'nobArchivo'=>$nombreArchivo
                ];
                return view('descargar/descargar_archivo',$data);    


            } else {
                $contenido = "Error: El archivo no se encuentra en la ruta especificada.";
            }


    }


    public function descargaBansta($nombreArchivo){

        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }  

        //$ruta  = storage_path('app\archivos\archivoBansta\\' . $nombreArchivo);
        $ruta = storage_path('app/archivos/archivoBansta/' . $nombreArchivo);


        // Verificamos si existe antes de intentar leerlo

            if (File::exists($ruta)) {
                $lineas = File::lines($ruta); 
                $archivo = ''; 
                $nroLinea = 0;
                foreach ($lineas as $linea) {
                    if($nroLinea == 0){
                        $nroLinea+=1;
                        $archivo.=$linea . "\n";
                    }else{
                        if(!empty($linea)){
                            $nroLinea+=1;
                            $archivo.=$linea . "\n";
                        }    
                    }    
                }    
                $data = [
                   'archivo'=>$archivo,
                   'nobArchivo'=>$nombreArchivo
                ];
                return view('descargar/descargar_bansta',$data);    
            } else {
                $contenido = "Error: El archivo no se encuentra en la ruta especificada.";
            }

            die($contenido);

    }




    public function downloadArchivo($nombreArchivo){
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        $rutaAbsoluta = storage_path('app/archivos/procesados/' . $nombreArchivo);
        return response()->download($rutaAbsoluta);
    }
      

    public function downloadBansta($nombreArchivo){
        if(Session::get('usuario.id')==null){
            return Redirect::route('inicio');
        }        
        $rutaAbsoluta = storage_path('app/archivos/archivoBansta/' . $nombreArchivo);
        return response()->download($rutaAbsoluta);
    }




}
