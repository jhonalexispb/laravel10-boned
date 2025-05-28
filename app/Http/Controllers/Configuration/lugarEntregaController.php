<?php

namespace App\Http\Controllers\configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\Distrito;
use App\Models\configuration\lugarEntrega;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class lugarEntregaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get("search");

        $lugarEntrega = lugarEntrega::where("address","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $lugarEntrega->total(),
            "lugarEntrega" => $lugarEntrega->map(function($lugarEntrega){
                return [
                    "id" => $lugarEntrega->id,
                    "address" => $lugarEntrega->address,
                    "state" => $lugarEntrega->state,
                    "coordenadas" => $lugarEntrega->destination_coordinates,
                    "created_at" => $lugarEntrega->created_at->format("Y-m-d h:i A")
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
            'imagen_lugar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
        $is_exist_lugar_entrega = lugarEntrega::where("address",$request->address)->first();
        if($is_exist_lugar_entrega){
            return response()->json([
                "message" => 403,
                "message_text" => "la direccion del lugar de entrega ya existe"
            ],422);
        }

        if (!empty($request->distrito)) {
            $nombreDistritoRequest = $this->normalizeString($request->distrito);

            $distrito = Distrito::all()->first(function ($item) use ($nombreDistritoRequest) {
                return $this->normalizeString($item->name) === $nombreDistritoRequest;
            });

            if ($distrito) {
                $request->merge(['distrito_id' => $distrito->id]);
            }
        }

        if ($request->hasFile('imagen_lugar')) {
            $mainImage = $request->file('imagen_lugar');

            $uploadedFile = Cloudinary::upload($mainImage->getRealPath(), [
                'folder' => 'LugarEntrega',
            ]);
            $imageUrl = $uploadedFile->getSecurePath();
            $publicId = $uploadedFile->getPublicId();

            // Agregar datos de imagen al request para crear el modelo
            $request->merge([
                'imagen' => $imageUrl,
                'imagen_public_id' => $publicId
            ]);
        }

        $lugar_entrega = lugarEntrega::create($request->all());
        if ($lugar_entrega->distrito) {
            $departamento = strtoupper($lugar_entrega->distrito->provincia->departamento->name);
            $provincia = strtoupper($lugar_entrega->distrito->provincia->name);
            $distrito = strtoupper($lugar_entrega->distrito->name);
            $ubicacion = $departamento . '/' . $provincia . '/' . $distrito;
        } else {
            $ubicacion = 'SIN DISTRITO';
        }
        return response()->json([
            "message" => 200,
            "lugarEntrega" => [
                "id" => $lugar_entrega->id,
                "address" => $lugar_entrega->address.' - '.$ubicacion,
                "latitud" => $lugar_entrega->latitud,
                "longitud" => $lugar_entrega->longitud,
                "imagen" => $lugar_entrega->imagen ?? null,
                "created_at" => $lugar_entrega->created_at->format("Y-m-d h:i A")
            ]
        ]);
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
        $is_exist_lugar_entrega = lugarEntrega::where("address",$request->address)->where("id","<>",$id)->first();
        if($is_exist_lugar_entrega){
            return response()->json([
                "message" => 403,
                "message_text" => "la direccion del lugar de entrega ya existe"
            ],422);
        }

        if (!empty($request->distrito)) {
            $nombreDistritoRequest = $this->normalizeString($request->distrito);

            $distrito = Distrito::all()->first(function ($item) use ($nombreDistritoRequest) {
                return $this->normalizeString($item->name) === $nombreDistritoRequest;
            });

            if ($distrito) {
                $request->merge(['distrito_id' => $distrito->id]);
            }
        }

        $lugar_entrega = lugarEntrega::findOrFail($id); 

        if ($request->hasFile('imagen_lugar')) {
            $mainImage = $request->file('imagen_lugar');

            // Eliminar imagen anterior si existe
            if ($lugar_entrega->imagen_public_id) {
                Cloudinary::destroy($lugar_entrega->imagen_public_id);
            }

            $uploadedFile = Cloudinary::upload($mainImage->getRealPath(), [
                'folder' => 'LugarEntrega',
            ]);
            $imageUrl = $uploadedFile->getSecurePath();
            $publicId = $uploadedFile->getPublicId();

            // Agregar datos de imagen al request para crear el modelo
            $request->merge([
                'imagen' => $imageUrl,
                'imagen_public_id' => $publicId
            ]);
        }

        $lugar_entrega->update($request->all());
        if ($lugar_entrega->distrito) {
            $departamento = strtoupper($lugar_entrega->distrito->provincia->departamento->name);
            $provincia = strtoupper($lugar_entrega->distrito->provincia->name);
            $distrito = strtoupper($lugar_entrega->distrito->name);
            $ubicacion = $departamento . '/' . $provincia . '/' . $distrito;
        } else {
            $ubicacion = 'SIN DISTRITO';
        }
        return response()->json([
            "message" => 200,
            "lugarEntrega" => [
                "id" => $lugar_entrega->id,
                "address" => $lugar_entrega->address.' - '.$ubicacion,
                "latitud" => $lugar_entrega->latitud,
                "longitud" => $lugar_entrega->longitud,
                "imagen" => $lugar_entrega->imagen,
                "created_at" => $lugar_entrega->created_at->format("Y-m-d h:i A")
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lugar_entrega = lugarEntrega::findOrFail($id);
        //Validacion por venta
        $lugar_entrega->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    private function normalizeString($string)
    {
        $string = trim($string); // elimina espacios al inicio y fin
        $string = strtolower($string); // convierte a minúsculas
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string); // elimina tildes
        $string = preg_replace('/[^a-z0-9]/', '', $string); // elimina caracteres especiales (opcional)
        return $string;
    }
}
