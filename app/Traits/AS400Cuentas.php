<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Importante importar esto para trabajar con archivo fisico

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

//use Illuminate\Support\Facades\Log;

use SoapClient;
use SimpleXMLElement;

use Exception;

trait AS400Cuentas
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */



    /////////////////Cuentas
    public function obtenerCtasCliente($identificacion)
    {

        // 1. Definición de la URL del WSDL
        $wsdl = env('URL_GATEWAY_AEL');
        // 2. Parámetros de la solicitud SOAP (datos que funcionan)
        $params = [
            'req' => [
                'SUMACN' => $identificacion,
                'SUMFIR' => 'X',
            ]
        ];
        // 3. Llamada al método que consume el servicio


        try {
            $result = $this->fetch_soap_api_01($wsdl, $params, 'SUMDSJV');
            $respuesta = $result['SUMDSJVReturn']['SUMDSJV'];
            $swRespuesta = 0;

            foreach ($respuesta as $valor) {
                $swRespuesta = 1;
            }

            if ($swRespuesta == 0) {
                $data = [
                    'error'=>'0',
                    'respuesta'=>'SIN CUENTAS',
                    'mensaje'=>''
                ];                 
            } else {
                $data = [
                    'error'=>'1',
                    'respuesta'=>$respuesta,
                    'mensaje'=>''
                ];                 

            }
            return $data;
        } catch (\Exception $e) {   
            //Maneja errores Inesperado
                $data = [
                    'error'=>'98',
                    'respuesta'=>'ERROR COMUNICACION AS400',
                    'mensaje'=>$e->getMessage()
                ];                 
                return $data;
        } catch (\Exception $e) {
                $data = [
                    'error'=>'99',
                    'respuesta'=>'Error Inespserado, consulta al administrador 500',
                    'mensaje'=>$e->getMessage()
                ];                 
                return $data;
        }        
        // Muestra el resultado (debe ser un array con los registros)
    }

    /**
     * Convierte recursivamente un objeto SimpleXMLElement a un array asociativo.
     * Esta función es crucial para manejar elementos XML repetidos (arrays) y limpiar los valores.
     */
    protected function xml_to_array_01(\SimpleXMLElement $xml)
    {
        $arr = [];
        // Itera sobre todos los hijos del nodo XML actual
        foreach ($xml->children() as $key => $node) {
            // Si el nodo tiene hijos, llama recursivamente (para anidamiento).
            // Si no tiene hijos, obtiene el valor de texto y limpia espacios en blanco (trim).
            $value = count($node->children()) > 0 ? $this->xml_to_array_01($node) : trim((string)$node);

            // Lógica de mapeo: maneja elementos XML repetidos (ej: la lista de registros)
            if (isset($arr[$key])) {
                // Si la clave ya existe, nos aseguramos de que sea un array
                if (!is_array($arr[$key]) || !isset($arr[$key][0])) {
                    $arr[$key] = [$arr[$key]];
                }
                // Añadimos el nuevo valor al array para formar la lista de resultados
                $arr[$key][] = $value;
            } else {
                $arr[$key] = $value;
            }
        }
        return $arr;
    }


    protected function fetch_soap_api_01($url, $params, $response_key)
    {
        $client = null;
        try {
            // Configuración del cliente: 'trace' debe estar activo para obtener el XML de la respuesta
            $options = [
                'exceptions' => true,
                'trace' => 1,
            ];

            $client = new \SoapClient($url, $options);
            // Ejecutamos la llamada SOAP. El objeto $result es ignorado por el problema de mapeo.
            $client->$response_key($params);

            // 1. Obtener la respuesta XML cruda (string) del cliente SOAP
            $xml_response = $client->__getLastResponse();

            // 2. Cargar el XML con SimpleXMLElement
            $xml_obj = new \SimpleXMLElement($xml_response, LIBXML_NOCDATA);

            // 3. Registrar namespaces para XPath
            $xml_obj->registerXPathNamespace('s', 'http://schemas.xmlsoap.org/soap/envelope/');
            // Registramos el namespace de nuestro servicio como 'ns'
            $xml_obj->registerXPathNamespace('ns', 'http://servicios.gateway.activo.com');

            // 4. Búsqueda de la lista de registros REAL utilizando XPath y local-name()
            // Esto busca en la ruta: SUMDSJVReturn -> (SUMDSJV contenedor) -> (SUMDSJV registro)
            // local-name() ignora los prefijos para robustez contra problemas de namespace.
            $xpath_query = '//ns:SUMDSJVReturn/*[local-name()="SUMDSJV"]/*[local-name()="SUMDSJV"]';
            $list_nodes = $xml_obj->xpath($xpath_query);

            $registros = [];

            // 5. Procesar los nodos de la lista encontrados por XPath
            if (!empty($list_nodes)) {
                foreach ($list_nodes as $node) {
                    // Convertimos cada nodo de registro (SimpleXMLElement) a un array asociativo
                    $registros[] = $this->xml_to_array_01($node);
                }
            }

            // 6. Revisar el nodo de error (usamos XPath también)
            $error_node = $xml_obj->xpath('//ns:SUMDSJVReturn/error');
            $error_data = null;
            if (isset($error_node[0]) && (string)$error_node[0] !== '') {
                // Si el nodo existe y tiene contenido (no es xsi:nil="true"), lo mapeamos
                $error_data = $this->xml_to_array_01($error_node[0]);
            }

            // 7. Devolver la respuesta final estructurada y limpia
            return [
                "SUMDSJVReturn" => [
                    "error" => $error_data,
                    "SUMDSJV" => $registros, // ¡Lista de arrays limpia!
                ]
            ];
        } catch (\Exception $e) {
            // Manejo de errores de conexión, WSDL o SOAP Fault
            $error_response = ['SOAP_ERROR' => $e->getMessage()];
            if ($client && property_exists($client, '__getLastResponse')) {
                $error_response['SOAP_RESPONSE_XML'] = $client->__getLastResponse();
            }
            return $error_response;
        }
    }
    /////////////////fin obtener cuentas cliente   




    //fin del trait
}
