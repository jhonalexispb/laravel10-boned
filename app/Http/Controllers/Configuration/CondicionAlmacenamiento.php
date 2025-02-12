<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\ProductoAtributtes\CondicionAlmacenamiento as ProductoAtributtesCondicionAlmacenamiento;
use Illuminate\Http\Request;

class CondicionAlmacenamiento extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $condicion_almacenamiento = ProductoAtributtesCondicionAlmacenamiento::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $condicion_almacenamiento->total(),
            "condicion_almacenamiento" => $condicion_almacenamiento->map(function($c){
                return [
                    "id" => $c->id,
                    "name" => $c->name,
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
        $CONDICION_EXIST = ProductoAtributtesCondicionAlmacenamiento::withTrashed()
                            ->where('name',$request->name)
                            ->first();
        if($CONDICION_EXIST){
            if ($CONDICION_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "la condicion de almacenamiento ".$CONDICION_EXIST->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "condicion_almacenamiento" => $CONDICION_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "la condicion de almacenamiento ".$CONDICION_EXIST->name." ya existe"
            ],422);
        }

        $d = ProductoAtributtesCondicionAlmacenamiento::create(  $request->all());
        return response()->json([
            "message" => 200,
            "condicion_almacenamiento" => [
                "id" => $d->id,
                "name" => $d->name,
                "state" => $d->state ?? 1,
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
        $CONDICION_EXIST = ProductoAtributtesCondicionAlmacenamiento::withTrashed()
                            ->where('name',$request->name)
                            ->where('id','<>', $id)
                            ->first();
        if($CONDICION_EXIST){
            if ($CONDICION_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "la condicion de almacenamiento ".$CONDICION_EXIST->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "condicion_almacenamiento" => $CONDICION_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "la condicion de almacenamiento ".$CONDICION_EXIST->name." ya existe"
            ],422);
        }

        $c = ProductoAtributtesCondicionAlmacenamiento::findOrFail($id);
        $c->update($request->all());
        return response()->json([
            "message" => 200,
            "condicion_almacenamiento" => [
                "id" => $c->id,
                "name" => $c->name,
                "state" => $c->state,
                "created_at" => $c->created_at->format("Y-m-d h:i A"),
            ],
            "gaaa" => 'gaaa'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $c = ProductoAtributtesCondicionAlmacenamiento::findOrFail($id);
        $c->delete();
        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $id)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $condicion_almacenamiento = ProductoAtributtesCondicionAlmacenamiento::withTrashed()->findOrFail($id);

        // Restaurar el departamento si está eliminado
        if ($condicion_almacenamiento->trashed()) {
            $condicion_almacenamiento->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "la condicion de almacenamiento ".$condicion_almacenamiento->name." fue restaurada de manera satisfactoria",
                "condicion_almacenamiento_restaurada" => [
                    "id" => $condicion_almacenamiento->id,
                    "name" => $condicion_almacenamiento->name,
                    "state" => $condicion_almacenamiento->state,
                    "created_at" => $condicion_almacenamiento->created_at->format("Y-m-d h:i A"),
                ]
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'la condicion de almacenamiento no estaba eliminada'
        ],422);
    }
}
