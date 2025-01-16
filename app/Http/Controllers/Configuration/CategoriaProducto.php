<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\CategoriaProducto as ConfigurationCategoriaProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriaProducto extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $categoria = ConfigurationCategoriaProducto::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $categoria->total(),
            "categoria" => $categoria->map(function($c){
                return [
                    "id" => $c->id,
                    "name" => $c->name,
                    "image" => $c->image ? env("APP_URL")."storage/".$c->image : null,
                    "state" => $c->state,
                    "created_at" => $c->created_at->format("Y-m-d h:i A")
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $CATEGORIA_EXIST = ConfigurationCategoriaProducto::withTrashed()
                            ->where('name',$request->name)
                            ->first();
        if($CATEGORIA_EXIST){
            if ($CATEGORIA_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "la categoria ".$CATEGORIA_EXIST->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "categoria" => $CATEGORIA_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "la categoria ".$CATEGORIA_EXIST->name." ya existe"
            ],422);
        }

        if($request->hasFile("image_categoria")){
            $path = Storage::putFile("categoria",$request->file("image_categoria"));
            $request->request->add(["image" => $path]);
        }

        $d = ConfigurationCategoriaProducto::create(  $request->all());
        return response()->json([
            "message" => 200,
            "categoria" => [
                "id" => $d->id,
                "name" => $d->name,
                "state" => $d->state ?? 1,
                "image" => $d->image ? env("APP_URL")."storage/".$d->image : '',
                "created_at" => $d->created_at->format("Y-m-d h:i A"),
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
        $CATEGORIA_EXIST = ConfigurationCategoriaProducto::withTrashed()
                            ->where('name',$request->name)
                            ->where('id','<>', $id)
                            ->first();
        if($CATEGORIA_EXIST){
            if ($CATEGORIA_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "la categoria ".$CATEGORIA_EXIST->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "categoria" => $CATEGORIA_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "la categoria ".$CATEGORIA_EXIST->name." ya existe"
            ],422);
        }

        if($request->hasFile("image_categoria")){
            $path = Storage::putFile("categoria",$request->file("image_categoria"));
            $request->request->add(["image" => $path]);
        }

        $c = ConfigurationCategoriaProducto::findOrFail($id);
        $c->update($request->all());
        return response()->json([
            "message" => 200,
            "categoria" => [
                "id" => $c->id,
                "name" => $c->name,
                "state" => $c->state ?? 1,
                "image" => $c->image ? env("APP_URL")."storage/".$c->image : '',
                "created_at" => $c->created_at->format("Y-m-d h:i A"),
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $c = ConfigurationCategoriaProducto::findOrFail($id);
        if($c->image){
            Storage::delete($c->image);
        }
        $c->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $categoria)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $categoria = ConfigurationCategoriaProducto::withTrashed()->findOrFail($categoria);

        // Restaurar el departamento si está eliminado
        if ($categoria->trashed()) {
            $categoria->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "la categoria ".$categoria->name." fue restaurada de manera satisfactoria",
                "categoria_restaurada" => [
                    "id" => $categoria->id,
                    "name" => $categoria->name,
                    "state" => $categoria->state,
                    "image" => $categoria->image ? env("APP_URL")."storage/".$categoria->image : '',
                    "created_at" => $categoria->created_at->format("Y-m-d h:i A"),
                ]
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'la categoria no estaba eliminado'
        ],422);
    }
}
