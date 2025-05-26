<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDigemid;
use App\Models\Cliente;
use App\Models\ClientesSucursales;
use App\Models\ClienteSucursalAtributtes\Celular;
use App\Models\ClienteSucursalAtributtes\CelularSucursal;
use App\Models\ClienteSucursalAtributtes\ConfigurationClienteSucursal;
use App\Models\ClienteSucursalAtributtes\ConversorEstadoDigemid;
use App\Models\ClienteSucursalAtributtes\Correo;
use App\Models\ClienteSucursalAtributtes\CorreoSucursal;
use App\Models\ClienteSucursalAtributtes\Dni;
use App\Models\ClienteSucursalAtributtes\DniSucursal;
use App\Models\ClienteSucursalAtributtes\EstadoDigemid;
use App\Models\ClienteSucursalAtributtes\ModoFacturacion;
use App\Models\ClienteSucursalAtributtes\RegistroDigemid;
use App\Models\Configuration\Distrito;
use App\Models\configuration\lugarEntrega;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
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
        }, )
        ->orderBy('id', 'desc')
        ->paginate(25);

        return response()->json([
            "total" => $cliente_sucursal->total(),
            "cliente_sucursales" => $cliente_sucursal->map(function($d){
                return [
                    "id" => $d->id,
                    "sucursal_name_complete" => $d->ruc->ruc.' '.$d->ruc->razonSocial.' '.$d->nombre_comercial.' '.$d->direccion.' '.$d->getNameDistrito->name.' '.$d->getNameDistrito->provincia->name.' '.$d->getNameDistrito->provincia->departamento->name,
                    "ruc" => $d->ruc ? $d->ruc->ruc : null,
                    "ruc_id" => $d->ruc ? $d->ruc->id : null,
                    "razon_social" => $d->ruc ? $d->ruc->razonSocial : null,
                    "state" => $d->state ?? 1,
                    "created_at" => $d->created_at->format("Y-m-d h:i A"),
                    "nombre_comercial" => $d->nombre_comercial,
                    "direccion" => $d->direccion,
                    "deuda" => $d->deuda,
                    "formaPago" => $d->formaPago,
                    "linea_credito" => $d->linea_credito,
                    "modo_trabajo" => $d->modo_trabajo,
                    "estado_digemid"=> $d->estado_digemid,
                    "nombre_estado_digemid"=> $d->getEstadoDigemid->nombre,
                    "distrito" => $d->distrito ? $d->getNameDistrito->name : null, // Accedemos al nombre del distrito
                    "distrito_id" => $d->distrito ? $d->getNameDistrito->id : null,
                    "provincia" => $d->distrito && $d->getNameDistrito->provincia ? $d->getNameDistrito->provincia->name : null, // Accedemos al nombre de la provincia
                    "departamento" => $d->distrito && $d->getNameDistrito->provincia && $d->getNameDistrito->provincia->departamento ? $d->getNameDistrito->provincia->departamento->name : null, // Accedemos al nombre del departamento
                    "categoria_digemid" => $d->categoriaDigemid ? $d->categoriaDigemid->nombre : null,
                    "categoria_digemid_id" => $d->categoriaDigemid ? $d->categoriaDigemid->id : null,
                    "nregistro" => $d->nregistro_id ? $d->getRegistro->nregistro : null,
                    "nregistro_id" => $d->nregistro_id ? $d->getRegistro->id : null,
                    "image" => $d->image ? $d->image : null,
                    "documento_en_proceso" => $d->documento_en_proceso ? $d->documento_en_proceso : null,

                    "coordenadas_" => $d->getDirecciones->map(function($lugarEntrega) {
                        return [
                            "latitud" => $lugarEntrega->latitud, // latitud de la dirección
                            "longitud" => $lugarEntrega->longitud, // longitud de la dirección
                            "address" => $lugarEntrega->address, // dirección del lugar
                        ];
                    }),

                    "celulares" => $d->getCelular ? $d->getCelular->map(function($celularSucursal) {
                        return $celularSucursal->getNumberCelular ? $celularSucursal->getNumberCelular->celular : null;
                    }) : [],

                    "correos" => $d->getCorreo ? $d->getCorreo->map(function($correo) {
                        return $correo->correo ? $correo->correo->correo : null;
                    }) : [],

                    "dni" => $d->getDni ? [
                        "dni_id" => $d->getDni->dni->id,
                        "numero" => $d->getDni->dni->numero,
                        "nombre_dni" => $d->getDni->dni->nombre,
                    ] : null,
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
            'estado_digemid' => 'required|numeric|exists:estados_digemid,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg',
            'documento_en_proceso' => 'nullable|image|mimes:jpeg,png,jpg',
            'direccion' => 'required',
            'distrito' => 'required|numeric|exists:distritos,id',
            'ruc' => 'required|digits:11|numeric',
            'razon_social' => 'required|string',
            'categoria_digemid' => 'nullable|numeric|exists:categorias_digemid,id|required_unless:estado_digemid,5',
            'nombre_comercial' => 'nullable|string|required_unless:estado_digemid,5',
            'correo' => 'nullable|email',
            /* 'correo' => 'nullable|email|required_if:estado_digemid,1|required_if:estado_digemid,2', */
            /* 'celular' => 'required|numeric', */
            'celular' => 'nullable|numeric',
            'dni' => 'nullable|numeric|required_if:estado_digemid,2|required_if:estado_digemid,3|required_if:estado_digemid,4|required_if:estado_digemid,5',
            'nombre_dni' => 'nullable|string|required_if:estado_digemid,2|required_if:estado_digemid,3|required_if:estado_digemid,4|required_if:estado_digemid,5',
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

            if ($request->celular) {
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
            }

            if ($request->correo) {
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
            }

            if ($request->estado_digemid == 2 || $request->estado_digemid == 3 || $request->estado_digemid == 4 || $request->estado_digemid == 5) {
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

            if($request->image){
                $uploadedFile = Cloudinary::upload($request->file('image')->getRealPath(),[
                    'folder' => 'ClienteSucursales',  // Nombre de la carpeta en Cloudinary
                ]);
                $imageUrl = $uploadedFile->getSecurePath();
                $imagePublicId = $uploadedFile->getPublicId();
            }

            if($request->documento_en_proceso){
                $uploadedFile = Cloudinary::upload($request->file('documento_en_proceso')->getRealPath(),[
                    'folder' => 'ActasInspeccionDigemid',  // Nombre de la carpeta en Cloudinary
                ]);
                $documentUrl  = $uploadedFile->getSecurePath();
                $documentPublicId = $uploadedFile->getPublicId();
            }

            $sucursal = ClientesSucursales::create([
                'ruc_id' => $ruc_exist->id,
                'nombre_comercial' => $request->nombre_comercial,
                'direccion' => $request->direccion,
                'distrito' => $request->distrito,
                'categoria_digemid_id' => $request->categoria_digemid,
                'estado_digemid' => $request->estado_digemid,
                'nregistro_id' => $registro_digemid->id ?? null,
                'image' => isset($imageUrl) ? $imageUrl : null,
                'image_public_id' => isset($imagePublicId) ? $imagePublicId : null,
                'documento_en_proceso' => isset($documentUrl) ? $documentUrl : null,
                'documento_en_proceso_public_id' => isset($documentPublicId) ? $documentPublicId : null,
                'dias' => ConfigurationClienteSucursal::where('nombre', 'dias_creacion')->value('valor') ?? 30,
                'linea_credito' => ConfigurationClienteSucursal::where('nombre', 'linea_credito')->value('valor') ?? 2000,
            ]);

            lugarEntrega::create([
                'sucursal_id' => $sucursal->id,
                'address' => $sucursal->direccion,
                'distrito_id' => $sucursal->distrito,
                'latitud' => $request->latitud,
                'longitud'=> $request->longitud,
            ]);

            if ($request->correo) {
                CorreoSucursal::create([
                    'ruc_id' => $ruc_exist->id,
                    'cliente_sucursal_id' => $sucursal->id,
                    'correo_id' => $correo_exist->id,
                ]);
            }

            if ($request->celular) {
                CelularSucursal::create([
                    'ruc_id' => $ruc_exist->id,
                    'cliente_sucursal_id' => $sucursal->id,
                    'celular_id' => $celular_exist->id,
                ]);
            }

            if ($request->estado_digemid == 2 || $request->estado_digemid == 3 || $request->estado_digemid == 4 || $request->estado_digemid == 5) {
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
                    "formaPago" => $sucursal->formaPago ?? 2, 
                    "linea_credito" => $sucursal->linea_credito ?? 0.0,
                    "modo_trabajo" => $sucursal->modo_trabajo,
                    "distrito" => $sucursal->distrito ? $sucursal->getNameDistrito->name : null, // Accedemos al nombre del distrito
                    "distrito_id" => $sucursal->distrito ? $sucursal->getNameDistrito->id : null,
                    "provincia" => $sucursal->distrito && $sucursal->getNameDistrito->provincia ? $sucursal->getNameDistrito->provincia->name : null, // Accedemos al nombre de la provincia
                    "departamento" => $sucursal->distrito && $sucursal->getNameDistrito->provincia && $sucursal->getNameDistrito->provincia->departamento ? $sucursal->getNameDistrito->provincia->departamento->name : null, // Accedemos al nombre del departamento
                    "categoria_digemid" => $sucursal->categoriaDigemid ? $sucursal->categoriaDigemid->nombre : null,
                    "categoria_digemid_id" => $sucursal->categoriaDigemid ? $sucursal->categoriaDigemid->id : null,
                    "nregistro" => $sucursal->nregistro_id ? $sucursal->getRegistro->nregistro : null,
                    "image" => $sucursal->image ? $sucursal->image : null,
                    "documento_en_proceso" => $sucursal->documento_en_proceso ? $sucursal->documento_en_proceso : null,

                    "celulares" => $sucursal->getCelular->map(function($celularSucursal) {
                        return $celularSucursal->getNumberCelular ? $celularSucursal->getNumberCelular->celular : null;
                    }),

                    "correos" => $sucursal->getCorreo->map(function($correo) {
                        return $correo->correo ? $correo->correo->correo : null;
                    }),

                    "dni" => $sucursal->getDni ? [
                        "dni_id" => $sucursal->getDni->dni->id,
                        "numero" => $sucursal->getDni->dni->numero,
                        "nombre_dni" => $sucursal->getDni->dni->nombre,
                    ] : null,

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
        $request->validate([
            'estado_digemid' => 'required|numeric|exists:estados_digemid,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg',
            'documento_en_proceso' => 'nullable|image|mimes:jpeg,png,jpg',
            'direccion' => 'required',
            'distrito' => 'required|numeric|exists:distritos,id',
            'ruc' => 'required|digits:11|numeric',
            'razon_social' => 'required|string',
            'categoria_digemid' => 'nullable|numeric|exists:categorias_digemid,id|required_unless:estado_digemid,5',
            'nombre_comercial' => 'nullable|string|required_unless:estado_digemid,5',
            'correo' => 'nullable|email',
            /* 'correo' => 'nullable|email|required_if:estado_digemid,1|required_if:estado_digemid,2', */
            /* 'celular' => 'required|numeric', */
            'celular' => 'nullable|numeric',
            'dni' => 'nullable|numeric|required_if:estado_digemid,2|required_if:estado_digemid,3|required_if:estado_digemid,4|required_if:estado_digemid,5',
            'nombre_dni' => 'nullable|string|required_if:estado_digemid,2|required_if:estado_digemid,3|required_if:estado_digemid,4|required_if:estado_digemid,5',
            'nregistro' => 'nullable|string|required_if:estado_digemid,1|required_if:estado_digemid,2|required_if:estado_digemid,3',
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

            if ($request->celular) {
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
            }

            if ($request->correo) {
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
            }

            if ($request->estado_digemid == 2 || $request->estado_digemid == 3 || $request->estado_digemid == 4 || $request->estado_digemid == 5) {
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
                $registro_exist = RegistroDigemid::where("nregistro","=",$request->nregistro)->first();
                if($registro_exist){
                    if($registro_exist->getSucursalHandleRegistro->id != $id){
                        return response() -> json([
                            "message" => 403,
                            "message_text" => "el numero de registro ".$request->nregistro." ya esta siendo usado.",
                        ],422);
                    }
                }else{
                    $registro_exist = RegistroDigemid::create([
                        'nregistro' => $request->nregistro,
                    ]);
                }
            }

            if($request->hasFile('image')){
                $image_exist = ClientesSucursales::where('image','=',$request->image)->exists();
                if(!$image_exist){
                    try {
                        // Subir la imagen a Cloudinary
                        $uploadedFile = Cloudinary::upload($request->file('image')->getRealPath(), [
                            'folder' => 'ClienteSucursales',  // Nombre de la carpeta en Cloudinary
                        ]);
                        $imageUrl = $uploadedFile->getSecurePath();
                        $imagePublicId = $uploadedFile->getPublicId();
                    } catch (\Exception $e) {
                        // Capturar cualquier error y retornar un mensaje
                        return response()->json([
                            'error' => 'Error al subir la imagen: ' . $e->getMessage()
                        ], 500);
                    }
                }
            }

            if($request->hasFile('documento_en_proceso')){
                $documento_en_proceso_exist = ClientesSucursales::where('documento_en_proceso','=',$request->documento_en_proceso)->exists();;
                if(!$documento_en_proceso_exist){
                    try {
                        // Subir el documento a Cloudinary
                        $uploadedFile = Cloudinary::upload($request->file('documento_en_proceso')->getRealPath(), [
                            'folder' => 'ActasInspeccionDigemid',  // Nombre de la carpeta en Cloudinary
                        ]);
                        $documentUrl = $uploadedFile->getSecurePath();
                        $documentPublicId = $uploadedFile->getPublicId();
                    } catch (\Exception $e) {
                        // Capturar cualquier error y retornar un mensaje
                        return response()->json([
                            'error' => 'Error al subir el documento: ' . $e->getMessage()
                        ], 500);
                    }
                }
            }
            $sucursal = ClientesSucursales::findOrFail($id);
            $sucursal -> update([
                'ruc_id' => $ruc_exist->id,
                'nombre_comercial' => $request->nombre_comercial,
                'direccion' => $request->direccion,
                'distrito' => $request->distrito,
                'categoria_digemid_id' => $request->categoria_digemid,
                'estado_digemid' => $request->estado_digemid,
                'nregistro_id' => $registro_exist->id ?? null,
                'image' => isset($imageUrl) ? $imageUrl : $sucursal->image,
                'image_public_id' => isset($imagePublicId) ? $imagePublicId : $sucursal->image_public_id,
                'documento_en_proceso' => isset($documentUrl) ? $documentUrl : $sucursal->documento_en_proceso,
                'documento_en_proceso_public_id' => isset($documentPublicId) ? $documentPublicId : $sucursal->documento_en_proceso_public_id,
            ]);

            $lugar_exist = lugarEntrega::where('address',"=",$request->direccion)
                                        ->where('distrito_id','=',$request->distrito)
                                        ->first(); 
            if(!$lugar_exist){
                lugarEntrega::create([
                    'sucursal_id' => $sucursal->id,
                    'address' => $sucursal->direccion,
                    'distrito_id' => $sucursal->distrito,
                    'latitud' => $request->latitud,
                    'longitud'=> $request->longitud,
                ]);
            }

            if ($request->correo) {
                CorreoSucursal::firstOrCreate([
                    'ruc_id' => $ruc_exist->id,
                    'cliente_sucursal_id' => $sucursal->id,
                    'correo_id' => $correo_exist->id
                ],
        [
                    'ruc_id' => $ruc_exist->id,
                    'cliente_sucursal_id' => $sucursal->id,
                    'correo_id' => $correo_exist->id
                ]);
            }

            if ($request->celular) {
                CorreoSucursal::firstOrCreate([
                    'ruc_id' => $ruc_exist->id,
                    'cliente_sucursal_id' => $sucursal->id,
                    'celular_id' => $celular_exist->id,
                ],
        [
                    'ruc_id' => $ruc_exist->id,
                    'cliente_sucursal_id' => $sucursal->id,
                    'celular_id' => $celular_exist->id,
                ]);
            }

            if ($request->estado_digemid == 2 || $request->estado_digemid == 3 || $request->estado_digemid == 4 || $request->estado_digemid == 5) {
                // Buscar el registro existente
                $existingDniSucursal = DniSucursal::where('ruc_id', $ruc_exist->id)
                                                   ->where('cliente_sucursal_id',$sucursal->id)
                                                   ->first();
                
                // Si existe, actualiza el dni
                if ($existingDniSucursal) {
                    $existingDniSucursal->update([
                        'ruc_id' => $ruc_exist->id,
                        'cliente_sucursal_id' => $sucursal->id,
                        'dni_id' => $dni_exist->id,
                    ]);
                }else{
                    // Crear el nuevo registro
                    DniSucursal::create([
                        'ruc_id' => $ruc_exist->id,
                        'cliente_sucursal_id' => $sucursal->id,
                        'dni_id' => $dni_exist->id,
                    ]);
                }
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
                    "deuda" => $sucursal->deuda,
                    "formaPago" => $sucursal->formaPago, 
                    "linea_credito" => $sucursal->linea_credito,
                    "modo_trabajo" => $sucursal->modo_trabajo,
                    "distrito" => $sucursal->distrito ? $sucursal->getNameDistrito->name : null, // Accedemos al nombre del distrito
                    "distrito_id" => $sucursal->distrito ? $sucursal->getNameDistrito->id : null,
                    "provincia" => $sucursal->distrito && $sucursal->getNameDistrito->provincia ? $sucursal->getNameDistrito->provincia->name : null, // Accedemos al nombre de la provincia
                    "departamento" => $sucursal->distrito && $sucursal->getNameDistrito->provincia && $sucursal->getNameDistrito->provincia->departamento ? $sucursal->getNameDistrito->provincia->departamento->name : null, // Accedemos al nombre del departamento
                    "categoria_digemid" => $sucursal->categoriaDigemid ? $sucursal->categoriaDigemid->nombre : null,
                    "categoria_digemid_id" => $sucursal->categoriaDigemid ? $sucursal->categoriaDigemid->id : null,
                    "nregistro" => $sucursal->nregistro_id ? $sucursal->getRegistro->nregistro : null,
                    "image" => $sucursal->image ? $sucursal->image : null,
                    "documento_en_proceso" => $sucursal->documento_en_proceso ? $sucursal->documento_en_proceso : null,

                    "celulares" => $sucursal->getCelular->map(function($celularSucursal) {
                        return $celularSucursal->getNumberCelular ? $celularSucursal->getNumberCelular->celular : null;
                    }),

                    "correos" => $sucursal->getCorreo->map(function($correo) {
                        return $correo->correo ? $correo->correo->correo : null;
                    }),

                    "dni" => $sucursal->getDni ? [
                        "dni_id" => $sucursal->getDni->dni->id,
                        "numero" => $sucursal->getDni->dni->numero,
                        "nombre_dni" => $sucursal->getDni->dni->nombre,
                    ] : null,

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function gestionarSucursal(Request $request, string $id)
    {
        $request->validate([
            'formaPago' => 'required|numeric|in:1,2,3',  // Solo números del 1 al 3
            'dias' => 'required|numeric|gte:0',  // Debe ser mayor que 0
            'modo_facturacion_id' => 'required|exists:formas_facturacion_cliente,id',  // Debe existir en la tabla formas_facturacion_cliente
            'linea_credito' => 'required|numeric|gte:0',  // No debe ser menor que 0
        ]);

        try {
            $sucursal = ClientesSucursales::findOrFail($id);
            $sucursal -> update([
                'formaPago' => $request->formaPago,
                'dias' => $request->dias,
                'modo_facturacion_id' => $request->modo_facturacion_id,
                'linea_credito' => $request->linea_credito,
            ]);

            return response()->json([
                "sucursal_gestionada" => [
                    "formaPago" => $sucursal->formaPago,
                    "dias" => $sucursal->dias,
                    "modo_facturacion_id" => $sucursal->modo_facturacion_id,
                    "linea_credito" => $sucursal->linea_credito,
                ]
            ]);
        } catch (\Exception $e) {
            // Devolvemos el error al usuario
            return response()->json([
                'error' => 'Ocurrió un error, por favor intente de nuevo.',
                'message' => $e->getMessage(),
            ], 500);
        }
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

    public function getRecursosParaEditar(string $id)
    {   
        
        $sucursal = ClientesSucursales::find($id);
        $estado_digemid_sucursal = (array) $sucursal->estado_digemid;
        $conversores = ConversorEstadoDigemid::whereIn('estado_digemid_id', $estado_digemid_sucursal)->with('estadoDigemid')->get();

        $distritos = Distrito::where("state",1)->get();
        $categorias_digemid = CategoriaDigemid::all();

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

            "estados_digemid" => $conversores->map(function($d) {
                return [
                    "id" => $d->estadoDigemid->id,
                    "nombre" => $d->estadoDigemid->nombre,
                ];
            }),

            
        ]);
    }  

    public function getRecursosParaGestionar(string $id)
    {   
        $modos_facturacion = ModoFacturacion::where("state",1)->get();
        $datos_sucursal = ClientesSucursales::where("id","=",$id)->first();
        return response()->json([
            "modos_facturacion" => $modos_facturacion->map(function($d) {
                return [
                    "id" => $d->id,
                    "nombre" => $d->nombre,
                    "dias" => $d->dias
                ];
            }),
            "datos_sucursal" => [
                "modo_facturacion" => $datos_sucursal->modo_facturacion_id,
                "dias" => $datos_sucursal->dias,
                "forma_pago" => $datos_sucursal->formaPago
            ]
        ]);
    } 
}
