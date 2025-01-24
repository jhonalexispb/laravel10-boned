<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDigemid;
use App\Models\Cliente;
use App\Models\ClientesSucursales;
use App\Models\ClienteSucursalAtributtes\Celular;
use App\Models\ClienteSucursalAtributtes\CelularSucursal;
use App\Models\ClienteSucursalAtributtes\Correo;
use App\Models\ClienteSucursalAtributtes\CorreoSucursal;
use App\Models\ClienteSucursalAtributtes\Dni;
use App\Models\ClienteSucursalAtributtes\DniSucursal;
use App\Models\ClienteSucursalAtributtes\EstadoDigemid;
use App\Models\ClienteSucursalAtributtes\RegistroDigemid;
use App\Models\ClienteSucursalAtributtes\SucursalesActivas;
use App\Models\ClienteSucursalAtributtes\SucursalesCierreDefinitivo;
use App\Models\ClienteSucursalAtributtes\SucursalesCierreTemporal;
use App\Models\ClienteSucursalAtributtes\SucursalesPersonaNatural;
use App\Models\ClienteSucursalAtributtes\SucursalesSinRegistroDigemid;
use App\Models\Configuration\Distrito;
use App\Models\configuration\lugarEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientesSucursalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $cliente_sucursal = ClientesSucursales::when($search, function($query, $search) {
            return $query->where('id', '=', $search);
        })
        ->orderBy('id', 'desc')
        ->paginate(25);

        return response()->json([
            "total" => $cliente_sucursal->total(),
            "cliente_sucursales" => $cliente_sucursal->map(function($d){
                return [
                    "id" => $d->id,
                    "ruc" => $d->ruc ? $d->ruc->ruc : null,
                    "razon_social" => $d->ruc ? $d->ruc->razonSocial : null,
                    "state" => $d->state ?? 1,
                    "created_at" => $d->created_at->format("Y-m-d h:i A"),
                    "nombre_comercial" => $d->nombre_comercial,
                    "direccion" => $d->direccion,
                    "deuda" => $d->deuda,
                    "linea_credito" => $d->linea_credito,
                    "modo_trabajo" => $d->modo_trabajo,
                    "estado_digemid"=> $d->estado_digemid,
                    "nombre_estado_digemid"=> $d->getEstadoDigemid->nombre,
                    "distrito" => $d->distrito ? $d->getNameDistrito->name : null, // Accedemos al nombre del distrito
                    "provincia" => $d->distrito && $d->getNameDistrito->provincia ? $d->getNameDistrito->provincia->name : null, // Accedemos al nombre de la provincia
                    "departamento" => $d->distrito && $d->getNameDistrito->provincia && $d->getNameDistrito->provincia->departamento ? $d->getNameDistrito->provincia->departamento->name : null, // Accedemos al nombre del departamento
                    "categoria_digemid" => $d->categoriaDigemid ? $d->categoriaDigemid->nombre : null,
                    "categoria_digemid_id" => $d->categoriaDigemid ? $d->categoriaDigemid->id : null,
                    "nregistro" => $d->nregistro ? $d->getRegistro->nregistro : null,

                    "coordenadas_" => $d->getDirecciones->map(function($lugarEntrega) {
                        return [
                            "latitud" => $lugarEntrega->latitud, // latitud de la dirección
                            "longitud" => $lugarEntrega->longitud, // longitud de la dirección
                            "address" => $lugarEntrega->address, // dirección del lugar
                        ];
                    }),

                    "celulares" => $d->getCelular->map(function($celularSucursal) {
                        return $celularSucursal->getNumberCelular ? $celularSucursal->getNumberCelular->celular : null;
                    }),

                    "correos" => $d->getCorreo->map(function($correo) {
                        return $correo->correo ? $correo->correo->correo : null;
                    }),

                    "dni" => $d->getDni->map(function($dniSucursal) {
                        return [
                            "numero" => $dniSucursal->dni->numero, // Obtén el número de DNI desde la relación
                            "nombre_dni" => $dniSucursal->dni->nombre,
                        ];
                    }),
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'estado_digemid' => 'required|in:1,2,3,4,5',
            'direccion' => 'required',
            'distrito' => 'required|numeric|exists:distritos,id',
            'ruc' => 'required|digits:11|numeric',
            'razon_social' => 'required|string',
            'categoria_digemid' => 'nullable|numeric|exists:categorias_digemid,id|required_unless:estado_digemid,5',
            'nombre_comercial' => 'nullable|string|required_unless:estado_digemid,5',
            'correo' => 'nullable|email|required_if:estado_digemid,1',
            'celular' => 'required|numeric',
            'dni' => 'nullable|numeric|required_unless:estado_digemid,1',
            'nombre_dni' => 'nullable|string|required_unless:estado_digemid,1',
            'nregistro' => 'nullable|string|required_if:estado_digemid,1|required_if:estado_digemid,2|required_if:estado_digemid,3|unique:registros_digemid,nregistro',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ]);

        DB::beginTransaction();

        try {
            $ruc_exist = Cliente::withTrashed()
                                ->where('ruc',$request->ruc)
                                ->first();
            if($ruc_exist){
                if ($ruc_exist->deleted_at) {
                    // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                    return response() -> json([
                        "message" => 409,
                        "message_text" => "el ruc ".$ruc_exist->ruc.' '.$ruc_exist->razonSocial." ya existe pero se encuentra eliminado, contactate con el administrador",
                    ]);
                }
            } else {
                $ruc_exist = Cliente::create([
                    'ruc' => $request->ruc,
                    'razonSocial' => $request->razon_social,
                ]);
            }

            $celular_exist = Celular::where("celular","=",$request->celular)->first();
            if($celular_exist){
                if($celular_exist->getRucAsoc()->id != $ruc_exist->id){
                    return response() -> json([
                        "message" => 403,
                        "message_text" => "el celular ".$request->celular." ya esta siendo usado. Solicita otro numero de celular a tu sucursal",
                    ],422);
                }
            }else{
                $celular_exist = Celular::create([
                    'celular' => $request->celular,
                ]);    
            }

            $correo_exist = Correo::where("correo","=",$request->correo)->first();
            if($correo_exist){
                if($correo_exist->getRucAsoc()->id != $ruc_exist->id){
                    return response() -> json([
                        "message" => 403,
                        "message_text" => "el correo ".$request->correo." ya esta siendo usado. Solicita otro correo a tu sucursal",
                    ],422);
                }
            }else{
                $correo_exist = Correo::create([
                    'correo' => $request->correo,
                ]);  
            }

            if ($request->estado_digemid != 1) {
            $dni_exist = Dni::where("numero","=",$request->dni)->first();
                if($dni_exist){
                    if($dni_exist->getRucAsoc()->id != $ruc_exist->id){
                        return response() -> json([
                            "message" => 403,
                            "message_text" => "el DNI ".$request->dni." ya esta siendo usado. Solicita otro DNI a tu sucursal",
                        ],422);
                    }
                }else{
                    $dni_exist = Dni::create([
                        'numero' => $request->dni,
                        'nombre' => $request->nombre_dni,
                    ]);
                }
            }

            if($request->estado_digemid == 5){
                $request->nombre_comercial = $request->nombre_dni;
            }

            if ($request->estado_digemid == 1 || $request->estado_digemid == 2 || $request->estado_digemid == 3) {
                $registro_digemid = RegistroDigemid::create([
                    'nregistro' => $request->nregistro,
                ]);
            }

            $sucursal = ClientesSucursales::create([
                'ruc_id' => $ruc_exist->id,
                'nombre_comercial' => $request->nombre_comercial,
                'direccion' => $request->direccion,
                'distrito' => $request->distrito,
                'categoria_digemid_id' => $request->categoria_digemid,
                'estado_digemid' => $request->estado_digemid,
                'nregistro_id' => $registro_digemid->id ?? null,
            ]);

            lugarEntrega::create([
                'sucursal_id' => $sucursal->id,
                'address' => $sucursal->direccion,
                'distrito_id' => $sucursal->distrito,
                'latitud' => $request->latitud,
                'longitud'=> $request->longitud,
            ]);

            CorreoSucursal::create([
                'ruc_id' => $ruc_exist->id,
                'cliente_sucursal_id' => $sucursal->id,
                'correo_id' => $correo_exist->id,
            ]);

            CelularSucursal::create([
                'ruc_id' => $ruc_exist->id,
                'cliente_sucursal_id' => $sucursal->id,
                'celular_id' => $celular_exist->id,
            ]);

            if ($request->estado_digemid != 1) {
                DniSucursal::create([
                    'ruc_id' => $ruc_exist->id,
                    'cliente_sucursal_id' => $sucursal->id,
                    'dni_id' => $dni_exist->id,
                ]);
            }

            DB::commit();

            return response()->json([
                "cliente_sucursal" => [
                    "id" => $sucursal->id,
                    "ruc" => $sucursal->ruc ? $sucursal->ruc->ruc : null,
                    "razon_social" => $sucursal->ruc ? $sucursal->ruc->razonSocial : null,
                    "state" => $sucursal->state ?? 1,
                    "created_at" => $sucursal->created_at->format("Y-m-d h:i A"),
                    "nombre_comercial" => $sucursal->nombre_comercial,
                    "estado_digemid"=> $sucursal->estado_digemid,
                    "nombre_estado_digemid"=> $sucursal->getEstadoDigemid->nombre,
                    "direccion" => $sucursal->direccion,
                    "deuda" => $sucursal->deuda ?? 0.0,
                    "linea_credito" => $sucursal->linea_credito ?? 0.0,
                    "modo_trabajo" => $sucursal->modo_trabajo,
                    "distrito" => $sucursal->distrito ? $sucursal->getNameDistrito->name : null, // Accedemos al nombre del distrito
                    "provincia" => $sucursal->distrito && $sucursal->getNameDistrito->provincia ? $sucursal->getNameDistrito->provincia->name : null, // Accedemos al nombre de la provincia
                    "departamento" => $sucursal->distrito && $sucursal->getNameDistrito->provincia && $sucursal->getNameDistrito->provincia->departamento ? $sucursal->getNameDistrito->provincia->departamento->name : null, // Accedemos al nombre del departamento
                    "categoria_digemid" => $sucursal->categoriaDigemid ? $sucursal->categoriaDigemid->nombre : null,
                    "categoria_digemid_id" => $sucursal->categoriaDigemid ? $sucursal->categoriaDigemid->id : null,
                    "nregistro" => $sucursal->nregistro ? $sucursal->getRegistro->nregistro : null,

                    "celulares" => $sucursal->getCelular->map(function($celularSucursal) {
                        return $celularSucursal->getNumberCelular ? $celularSucursal->getNumberCelular->celular : null;
                    }),

                    "correos" => $sucursal->getCorreo->map(function($correo) {
                        return $correo->correo ? $correo->correo->correo : null;
                    }),

                    "dni" => $sucursal->getDni->map(function($dniSucursal) {
                        return [
                            "numero" => $dniSucursal->dni->numero, // Obtén el número de DNI desde la relación
                            "nombre_dni" => $dniSucursal->dni->nombre,
                        ];
                    }),

                    "coordenadas_" => $sucursal->getDirecciones->map(function($lugarEntrega) {
                        return [
                            "latitud" => $lugarEntrega->latitud, // latitud de la dirección
                            "longitud" => $lugarEntrega->longitud, // longitud de la dirección
                            "address" => $lugarEntrega->address, // dirección del lugar
                        ];
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            // Si ocurre algún error, hacemos rollback de la transacción
            DB::rollBack();
    
            // Devolvemos el error al usuario
            return response()->json([
                'error' => 'Ocurrió un error, por favor intente de nuevo.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getRecursos()
    {   
        $distritos = Distrito::where("state",1)->get();
        $categorias_digemid = CategoriaDigemid::all();
        $estados_digemid = EstadoDigemid::all();

        return response()->json([
            "distritos" => $distritos->map(function($d) {
                return [
                    "id" => $d->id,
                    "distrito_provincia_department_name" => $d->name ." / ". $d->provincia->name ." / ". $d->provincia->departamento->name,
                ];
            }),
            "categorias_digemid" => $categorias_digemid->map(function($d) {
                return [
                    "id" => $d->id,
                    "nombre" => $d->nombre ." (". $d->abreviatura.")",
                ];
            }),

            "estados_digemid" => $estados_digemid->map(function($d) {
                return [
                    "id" => $d->id,
                    "nombre" => $d->nombre,
                ];
            }),
        ]);
    }  
}
