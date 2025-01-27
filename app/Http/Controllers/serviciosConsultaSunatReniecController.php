<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Cliente;
use App\Models\ClienteSucursalAtributtes\Dni;

class serviciosConsultaSunatReniecController extends Controller
{
    public function getRazonSocial(Request $request)
    {   
        $request->validate([
            'ruc' => ['required', 'digits:11'],
        ]);
        $ruc = $request->get('ruc');
        $exist_ruc = Cliente::where("ruc","=", $ruc)->first();
        
        if($exist_ruc){
            return response() -> json([
                "message_text" => "el ruc ".$exist_ruc->ruc." ya se encuentra registrado, crearás una sucursal",
                "razonSocial" =>  $exist_ruc->razonSocial,
            ]);
        }else{
            try {
                // Token y configuración de la API
                $token = 'apis-token-12739.t9BIaJX3bol5mIcot6Q3nDyhOOXlhxAk';
                $client = new Client([
                    'base_uri' => 'https://api.apis.net.pe',
                    'verify' => false,
                ]);
    
                $parameters = [
                    'http_errors' => false,
                    'connect_timeout' => 5,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Referer' => 'https://apis.net.pe/api-consulta-ruc',
                        'User-Agent' => 'laravel/guzzle',
                        'Accept' => 'application/json',
                    ],
                    'query' => ['numero' => $ruc],
                ];
    
                // Hacer la solicitud a la API
                $res = $client->request('GET', '/v2/sunat/ruc', $parameters);
                $response = json_decode($res->getBody()->getContents(), true);
    
                if ($res->getStatusCode() === 200) {
                    return response()->json([
                        "message_text" => "felicidades, crearas un nuevo cliente",
                        "razonSocial" => $response['razonSocial'] ?? 'No disponible',
                        "response" => $response,
                        "message" => "new"
                    ]);
                } else {
                    // Manejar errores de la API
                    return response()->json([
                        "message_text" => "no se pudo obtener la información del RUC, consultalo manualmente en la SUNAT, recuerda que el ruc ya esta copiado en tu portapapeles.",
                        "message" => 403,
                        "go_sunat" => 1,
                        "ruc_search" => $ruc
                    ], 422);
                }
            } catch (\Exception $e) {
                // Manejar errores inesperados
                return response()->json([
                    "message_text" => "ocurrió un error al procesar la solicitud.",
                    "error" => $e->getMessage(),
                ], 500);
            }
        }
    } 
    
    public function getNameByDNI(Request $request)
    {
        $request->validate([
            'dni' => ['required', 'digits:8'],
        ]);
        $dni = $request->get('dni');
        $exist_dni = Dni::where("numero","=", $dni)->first();
        
        if($exist_dni){
            return response()->json([
                "message_text" => "dale un saludo a ".$exist_dni->nombre,
                "nombre_dni" => $exist_dni->nombre ?? 'No disponible',
            ]);
        }else{
            try {
                // Token y configuración de la API
                $token = 'apis-token-12739.t9BIaJX3bol5mIcot6Q3nDyhOOXlhxAk';
                $client = new Client([
                    'base_uri' => 'https://api.apis.net.pe',
                    'verify' => false,
                ]);
    
                $parameters = [
                    'http_errors' => false,
                    'connect_timeout' => 5,
                    'headers' => [
                        'Authorization' => 'Bearer '.$token,
                        'Referer' => 'https://apis.net.pe/api-consulta-dni',
                        'User-Agent' => 'laravel/guzzle',
                        'Accept' => 'application/json',
                    ],
                    'query' => ['numero' => $dni]
                ];
                
                // Hacer la solicitud a la API
                $res = $client->request('GET', '/v2/reniec/dni', $parameters);
                $response = json_decode($res->getBody()->getContents(), true);

                if ($res->getStatusCode() === 200) {
                    return response()->json([
                        "message_text" => "felicidades, dale un saludo a ".$response['nombreCompleto'],
                        "nombre_dni" => $response['nombreCompleto'] ?? 'No disponible',
                    ]);
                } else {
                    return response()->json([
                        "message_text" => "no se pudo obtener la información del DNI, preguntale el nombre de la persona a tu cliente",
                        "message" => 403,
                        "respuesta" => $parameters
                    ], 422);
                }
            } catch (\Exception $e) {
                // Manejar errores inesperados
                return response()->json([
                    "message_text" => "ocurrió un error al procesar la solicitud.",
                    "error" => $e->getMessage(),
                ], 500);
            }
        }
    }
}
