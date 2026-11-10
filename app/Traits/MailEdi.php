<?php

namespace App\Traits;

use App\Models\Adm_Usuario;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlertNotification;

trait MailEdi
{

    //Permite verificar los datos de un cliente
    public function enviarEmail($servicio,$archivo=null, $motivo=null){

        $data['motivoServidores'] = "";            
        $data['motivoAdministrador'] =  "";            
        $data['motivoFuncional'] = "";
        $data['motivoFuncional'] = "";

        if($servicio == 'FTP'){
            $data['records'] = Adm_Usuario::where('estatus', 1)
                        ->where('id_rol', 2)
                        ->orWhere('id_rol', 4)
                        ->orWhere('id_rol', 1)
                        ->get();
            $data['motivoServidores'] = $motivo;            
            $data['motivoAdministrador'] =  $motivo;            
            $data['motivoFuncional'] = 'Fallo en el Servicio FTP de Archivo';

        }else if($servicio == 'AS400'){
            $data['records'] = Adm_Usuario::where('estatus', 1)
                        ->where('id_rol', 3)
                        ->orWhere('id_rol', 2)
                        ->orWhere('id_rol', 4)
                        ->orWhere('id_rol', 1)
                        ->get();
            $data['motivoAs400'] = $motivo;
            $data['motivoAdministrador'] =  $motivo;            
            $data['motivoServidores'] = $motivo;            
            $data['motivoFuncional'] = 'Fallo en el Servicio AS400, Comuniquese con el area correspondiente';
        }else if($servicio == 'FUNCIONAL'){
            $data['records'] = Adm_Usuario::where('estatus', 1)
                        ->where('id_rol', 4)
                        ->orWhere('id_rol', 1)   
                        ->get();

            if(!is_null($archivo)){  
                $motivo = $motivo .  "       Archivo = " . $archivo;           
            }

            $data['motivoFuncional'] = $motivo;
            $data['motivoAdministrador'] =  $motivo;   
        } 
        $this->sendMail($data);
    }

 
    public function sendMail($data){

        foreach($data['records'] as $record){
         
            $email = $record->email;

            if(!empty($email)){
                if($record->id_rol == 1){
                    $message = $data['motivoAdministrador'];
                    $cabecera = 'Notificacion Administrador EDI';
                }else if($record->id_rol == 2){   //servidores
                    $message = $data['motivoServidores'];
                    $cabecera = 'Notificacion  Servidores EDI';

                }else if($record->id_rol == 3){   //AS400 
                    $message = $data['motivoAs400'];
                    $cabecera = 'Notificacion  AS400 EDI';

                }else{   //funcional
                    $message = $data['motivoFuncional'];
                    $cabecera = 'Notificacion  Funcional EDI';

                }  

                try {
                    // ENVIAR EL CORREO
                    $resp = Mail::to($email)->send(new AlertNotification(
                        $message, 
                        $cabecera
                    ));

                } catch (\Exception $e) {
                    Log::critical('Error enviando correo: ' . $e->getMessage());
                }

            }  //fin if(!empty($email))   


        } //fin de foreach
    }    

          



    //fin del trait
}
