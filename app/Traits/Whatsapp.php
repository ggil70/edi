<?php

namespace App\Traits;

use App\Models\Whatsapp_Usuario;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Importante importar esto para trabajar con archivo fisico

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

trait WhatsApp
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

   

    //Permite verificar los datos de un cliente
    public function enviarWhatsapp($servcio,$archivo=null, $motivo=null){
        if($servcio == 'FTP'){
            die("Servicio FTP");
        }else if($servcio == 'AS400'){
            die("Servicio AS400");
        }else if($servcio == 'FUNCIONAL'){
            //die("Servicio Funcional: Archivo = " . $archivo . "  Motivo = " . $motivo);
        } 
        return 0;  
    }


///////////////////////////////////////////////////////////////////
       /**
     * Retorna el mensaje y el número de teléfono basados en el rol solicitado.
     * * @param string $requestRol El rol (servidores, as400, etc.) enviado por el cliente.
     * @return array {'message': string, 'number': string|null}
     */
     
     
    public function getRolConfiguration(string $requestRol): array
    {
        // 1. Array de mapeo (clave => mensaje y clave de ENV)
        $rolMap = [
            'servidores' => [
                'message' => "Mensaje de prueba desde el sistema Backend - Rol Servidores",
                'env_key' => 'WHATSAPP_NUMBER_SERVIDORES',
            ],
            'as400' => [
                'message' => "Mensaje de prueba desde el sistema Backend - Rol AS400",
                'env_key' => 'WHATSAPP_NUMBER_AS400',
            ],
            'funcional' => [
                'message' => "Mensaje de prueba desde el sistema Backend - Rol Funcional",
                'env_key' => 'WHATSAPP_NUMBER_FUNCIONAL',
            ],
            'distribuido' => [
                'message' => "Mensaje de prueba desde el sistema Backend - Rol Distribuido",
                'env_key' => 'WHATSAPP_NUMBER_DISTRIBUIDO',
            ],
        ];


        // Busca la configuración del rol solicitado
        $config = $rolMap[$requestRol] ?? null;

        if ($config) {
            // Obtiene el número de teléfono directamente de la variable de entorno mapeada
            $phoneNumber = env($config['env_key']);

            return [
                'message' => $config['message'],
                'number'  => $phoneNumber,
            ];
        }

        // Si el rol no es reconocido o es inválido
        return [
            'message' => "Error no reconocido, por favor contactar a soporte. Rol enviado: " . $requestRol,
            'number'  => null,
        ];
    }




    /**
     * Procesa la solicitud, determina el rol, obtiene la configuración y envía el mensaje de WhatsApp.
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function sendWhatsapp(Request $request)
    {
        // ... (código anterior para obtener rol y configuración, que está bien)

        $request_rol = $request->input('request_rol');

        if (empty($request_rol)) {
            return response()->json(['error' => 1, 'mensaje' => 'El campo request_rol es obligatorio.'], 400);
        }

        // 1. OBTENER LA CONFIGURACIÓN (MENSAJE Y NÚMERO)
        $rolConfig = $this->getRolConfiguration($request_rol);

          

        $whatsappMessage = $rolConfig['message'];
        $whatsappNumber = $rolConfig['number'];

        // 2. VALIDACIÓN: Verificar si el número de WhatsApp está configurado o si el rol fue válido
        if (empty($whatsappNumber)) {
            Log::error('Configuración de WhatsApp faltante o Rol inválido.', [
                'rol' => $request_rol,
                'mensaje' => $whatsappMessage
            ]);

            return response()->json([
                'error' => 1,
                'mensaje' => 'Número de WhatsApp no configurado o Rol inválido.',
                'rol_enviado' => $request_rol,
                'detalles' => $whatsappMessage
            ], 500);
        }

        // Define la URL del endpoint
        $url = env('WHATSAPP_ENDPOINT');

        if (empty($url)) {
            Log::error('WHATSAPP_ENDPOINT no está configurado en el archivo .env.');
            return response()->json([
                'error' => 1,
                'mensaje' => 'Error de configuración: WHATSAPP_ENDPOINT no configurado.'
            ], 500);
        }


        // Define los datos a enviar en el cuerpo de la solicitud
        $data = [
            'tlf' => [$whatsappNumber], // Número específico del rol
            'message' => $whatsappMessage // Mensaje específico del rol
        ];

        try {
            // Realiza una solicitud POST con los datos
            // **LÍNEA CORREGIDA:** Pasa el array completo $data. Laravel lo serializará a JSON.
            $response = Http::post($url, $data);

            // Si la solicitud fue exitosa (código 2xx), obtén la respuesta JSON
            if ($response->successful()) {
                $responseData = $response->json();

                // Opcional: Loguear éxito
                Log::info('WhatsApp enviado exitosamente.', ['rol' => $request_rol, 'tlf' => $whatsappNumber]);

                return response()->json($responseData);
            }

            // Si la solicitud falló, maneja el error
            if ($response->failed()) {
                Log::error('Fallo en la API de WhatsApp.', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                // Devolver la respuesta de error de la API externa
                return response()->json([
                    'error' => 'La solicitud falló en la API de WhatsApp',
                    'status' => $response->status(),
                    'message' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Captura cualquier excepción de la red o de la conexión
            Log::critical('Excepción de conexión con API de WhatsApp.', ['exception' => $e->getMessage()]);

            return response()->json([
                'error' => 'Ocurrió un error al intentar conectar con la API.',
                'message' => $e->getMessage()
            ], 500);
        }
    } 




    //fin del trait
}
