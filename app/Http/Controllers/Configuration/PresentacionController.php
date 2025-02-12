<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\ProductoAtributtes\Presentacion;
use Illuminate\Http\Request;

class PresentacionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $presentacion = Presentacion::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $presentacion->total(),
            "presentacion" => $presentacion->map(function($c){
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
        $PRESENTACION_EXIST = Presentacion::withTrashed()
                            ->where('name',$request->name)
                            ->first();
        if($PRESENTACION_EXIST){
            if ($PRESENTACION_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "la presentacion ".$PRESENTACION_EXIST->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "presentacion" => $PRESENTACION_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "la presentacion ".$PRESENTACION_EXIST->name." ya existe"
            ],422);
        }

        $d = Presentacion::create(  $request->all());
        return response()->json([
            "message" => 200,
            "presentacion" => [
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
        $PRESENTACION_EXIST = Presentacion::withTrashed()
                            ->where('name',$request->name)
                            ->where('id','<>', $id)
                            ->first();
        if($PRESENTACION_EXIST){
            if ($PRESENTACION_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "la presentacion ".$PRESENTACION_EXIST->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "presentacion" => $PRESENTACION_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "la presentacion ".$PRESENTACION_EXIST->name." ya existe"
            ],422);
        }

        $c = Presentacion::findOrFail($id);
        $c->update($request->all());
        return response()->json([
            "message" => 200,
            "presentacion" => [
                "id" => $c->id,
                "name" => $c->name,
                "state" => $c->state ?? 1,
                "created_at" => $c->created_at->format("Y-m-d h:i A"),
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $c = Presentacion::findOrFail($id);
        $c->delete();
        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $id)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $presentacion = Presentacion::withTrashed()->findOrFail($id);

        // Restaurar el departamento si está eliminado
        if ($presentacion->trashed()) {
            $presentacion->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "la presentacion ".$presentacion->name." fue restaurada de manera satisfactoria",
                "presentacion_restaurada" => [
                    "id" => $presentacion->id,
                    "name" => $presentacion->name,
                    "state" => $presentacion->state,
                    "created_at" => $presentacion->created_at->format("Y-m-d h:i A"),
                ]
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'la presentacion no estaba eliminada'
        ],422);
    }
}
