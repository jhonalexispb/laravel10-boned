<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\FabricanteProducto;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class FabricanteProductoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $fabricante = FabricanteProducto::where("nombre","like","%".$search."%")
                                    ->orderBy("id","desc")
                                    ->paginate(25);
        return response()->json([
            "total" => $fabricante->total(),
            "fabricantes" => $fabricante->map(function($d){
                return [
                    "id" => $d->id,
                    "nombre" => $d->nombre,
                    "status" => $d->status,
                    "pais" => $d->pais ?? 'Sin pais',
                    "imagen" => $d->imagen,
                    "imagen_public" => $d->imagen_public
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
            'imagen_fabricante_producto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $FABRICANTE_EXIST = FabricanteProducto::withTrashed()
                            ->where('nombre',$request->nombre)
                            ->first();
        if($FABRICANTE_EXIST){
            if ($FABRICANTE_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el fabricante ".$FABRICANTE_EXIST->nombre." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                    "fabricante" => $FABRICANTE_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el fabricante ".$FABRICANTE_EXIST->nombre." ya existe"
            ],422);
        }

        if($request->hasFile('imagen_fabricante_producto')){
            $image_exist = FabricanteProducto::where('imagen','=',$request->imagen_fabricante_producto)->exists();
            if(!$image_exist){
                try {
                    // Subir la imagen a Cloudinary
                    $uploadedFile = Cloudinary::upload($request->file('imagen_fabricante_producto')->getRealPath(), [
                        'folder' => 'Fabricantes_productos',  // Nombre de la carpeta en Cloudinary
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

        $fabricante = FabricanteProducto::create(  $request->all());

        return response()->json([
            "message" => 200,
            "fabricante" => [
                "id" => $fabricante->id,
                "nombre" => $fabricante->nombre,
                "state" => $fabricante->status ?? 1,
                "pais" => $fabricante->pais ?? 'Sin pais',
                "imagen" => $fabricante->imagen,
                "imagen_public" => $fabricante->imagen_public
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
            'imagen_fabricante_producto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $FABRICANTE_EXIST = FabricanteProducto::withTrashed()
                            ->where('nombre',$request->nombre)
                            ->where('id','<>',$id)
                            ->first();
        if($FABRICANTE_EXIST){
            if ($FABRICANTE_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el fabricante ".$FABRICANTE_EXIST->nombre." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                    "fabricante" => $FABRICANTE_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el fabricante ".$FABRICANTE_EXIST->nombre." ya existe"
            ],422);
        }

        if($request->hasFile('imagen_fabricante_producto')){
            $image_exist = FabricanteProducto::where('imagen','=',$request->imagen_fabricante_producto)->exists();
            if(!$image_exist){
                try {
                    // Subir la imagen a Cloudinary
                    $uploadedFile = Cloudinary::upload($request->file('imagen_fabricante_producto')->getRealPath(), [
                        'folder' => 'Fabricantes_productos',  // Nombre de la carpeta en Cloudinary
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

        $fabricante = FabricanteProducto::findOrFail($id);
        $fabricante->update($request->all());

        return response()->json([
            "message" => 200,
            "fabricante" => [
                "id" => $fabricante->id,
                "nombre" => $fabricante->nombre,
                "state" => $fabricante->status ?? 1,
                "pais" => $fabricante->pais ?? 'Sin pais',
                "imagen" => $fabricante->imagen,
                "imagen_public" => $fabricante->imagen_public
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $d = FabricanteProducto::findOrFail($id);
        $d->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $id)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $fabricante = FabricanteProducto::withTrashed()->findOrFail($id);

        // Restaurar el departamento si está eliminado
        if ($fabricante->trashed()) {
            $fabricante->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "el fabricante ".$fabricante->nombre." fue restaurado de manera satisfactoria",
                "fabricante_restaurado" => [
                    "id" => $fabricante->id,
                    "nombre" => $fabricante->nombre,
                    "state" => $fabricante->status ?? 1,
                    "pais" => $fabricante->pais ?? 'Sin pais',
                    "imagen" => $fabricante->imagen,
                    "imagen_public" => $fabricante->imagen_public
                ],
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'el fabricante no estaba eliminado'
        ],422);
    }

    public function getRecursos()
    {
        return response()->json([
            "nombres_paises" => FabricanteProducto::select('nombre') 
            ->distinct()  // Obtiene solo valores únicos
            ->get()
            ->map(function ($p) {
                return [
                    "nombre" => $p->nombre,
                ];
            })
        ]);
    }   
}
