<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\LineaFarmaceutica;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class LineaFarmaceuticaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $linea = LineaFarmaceutica::where("nombre","like","%".$search."%")
                                    ->orderBy("id","desc")
                                    ->paginate(25);
        return response()->json([
            "total" => $linea->total(),
            "lineas_farmaceuticas" => $linea->map(function($d){
                return [
                    "id" => $d->id,
                    "nombre" => $d->nombre,
                    "status" => $d->status,
                    "imagen" => $d->imagen,
                    "imagen_public" => $d->imagen_public,
                    "created_at" => $d->created_at->format("Y-m-d h:i A")
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
            'nombre' => 'required',
            'imagen_linea_farmaceutica' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $LINEA_EXIST = LineaFarmaceutica::withTrashed()
                            ->where('nombre',$request->nombre)
                            ->first();
        if($LINEA_EXIST){
            if ($LINEA_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "la linea farmaceutica ".$LINEA_EXIST->nombre." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "linea_farmaceutica" => $LINEA_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "la linea farmaceutica ".$LINEA_EXIST->nombre." ya existe"
            ],422);
        }

        if($request->hasFile('imagen_linea_farmaceutica')){
            $image_exist = LineaFarmaceutica::where('imagen','=',$request->imagen_linea_farmaceutica)->exists();
            if(!$image_exist){
                try {
                    // Subir la imagen a Cloudinary
                    $uploadedFile = Cloudinary::upload($request->file('imagen_linea_farmaceutica')->getRealPath(), [
                        'folder' => 'Lineas_Farmaceuticas',  // Nombre de la carpeta en Cloudinary
                    ]);
                    $imageUrl = $uploadedFile->getSecurePath();
                    $request->request->add(["imagen" => $imageUrl]);
                    $imagePublicId = $uploadedFile->getPublicId();
                    $request->request->add(["imagen_public_id" => $imagePublicId]);
                } catch (\Exception $e) {
                    // Capturar cualquier error y retornar un mensaje
                    return response()->json([
                        'error' => 'Error al subir la imagen: ' . $e->getMessage()
                    ], 500);
                }
            }
        }

        $linea = LineaFarmaceutica::create(  $request->all());

        return response()->json([
            "message" => 200,
            "linea_farmaceutica" => [
                "id" => $linea->id,
                "nombre" => $linea->nombre,
                "status" => $linea->status ?? 1,
                "imagen" => $linea->imagen,
                "imagen_public" => $linea->imagen_public,
                "created_at" => $linea->created_at->format("Y-m-d h:i A")
            ],
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
        $request->validate([
            'nombre' => 'required',
        ]);

        $LINEA_EXIST = LineaFarmaceutica::withTrashed()
                            ->where('nombre',$request->nombre)
                            ->where('id','<>',$id)
                            ->first();
        if($LINEA_EXIST){
            if ($LINEA_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "la linea farmaceutica ".$LINEA_EXIST->nombre." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "linea_farmaceutica" => $LINEA_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "la linea farmaceutica ".$LINEA_EXIST->nombre." ya existe"
            ],422);
        }

        if($request->hasFile('imagen_linea_farmaceutica')){
            $image_exist = LineaFarmaceutica::where('imagen','=',$request->imagen_linea_farmaceutica)->exists();
            if(!$image_exist){
                try {
                    // Subir la imagen a Cloudinary
                    $uploadedFile = Cloudinary::upload($request->file('imagen_linea_farmaceutica')->getRealPath(), [
                        'folder' => 'Lineas_Farmaceuticas',  // Nombre de la carpeta en Cloudinary
                    ]);
                    $imageUrl = $uploadedFile->getSecurePath();
                    $request->request->add(["imagen" => $imageUrl]);
                    $imagePublicId = $uploadedFile->getPublicId();
                    $request->request->add(["imagen_public_id" => $imagePublicId]);
                } catch (\Exception $e) {
                    // Capturar cualquier error y retornar un mensaje
                    return response()->json([
                        'error' => 'Error al subir la imagen: ' . $e->getMessage()
                    ], 500);
                }
            }
        }

        $linea = LineaFarmaceutica::findOrFail($id);
        $linea->update($request->all());

        return response()->json([
            "message" => 200,
            "linea_farmaceutica" => [
                "id" => $linea->id,
                "nombre" => $linea->nombre,
                "status" => $linea->status ?? 1,
                "imagen" => $linea->imagen,
                "imagen_public" => $linea->imagen_public,
                "created_at" => $linea->created_at->format("Y-m-d h:i A")
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $d = LineaFarmaceutica::findOrFail($id);
        $d->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $id)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $linea = LineaFarmaceutica::withTrashed()->findOrFail($id);

        // Restaurar el departamento si está eliminado
        if ($linea->trashed()) {
            $linea->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "la linea farmaceutica ".$linea->nombre." fue restaurada de manera satisfactoria",
                "linea_farmaceutica_restaurada" => [
                    "id" => $linea->id,
                    "nombre" => $linea->nombre,
                    "status" => $linea->status ?? 1,
                    "imagen" => $linea->imagen,
                    "imagen_public" => $linea->imagen_public,
                    "created_at" => $linea->created_at->format("Y-m-d h:i A")
                ],
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'la linea farmaceutica no estaba eliminado'
        ],422);
    }
}
