<?php

namespace App\Http\Controllers\configuration;

use App\Http\Controllers\Controller;
use App\Models\configuration\lugarEntrega;
use Illuminate\Http\Request;

class lugarEntregaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get("search");

        $lugarEntrega = lugarEntrega::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $lugarEntrega->total(),
            "lugarEntrega" => $lugarEntrega->map(function($lugarEntrega){
                return [
                    "id" => $lugarEntrega->id,
                    "name" => $lugarEntrega->name,
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
        $is_exist_lugar_entrega = lugarEntrega::where("name",$request->name)->first();
        if($is_exist_lugar_entrega){
            return response()->json([
                "message" => 403,
                "message_text" => "El nombre del lugar de entrega ya existe"
            ],422);
        }

        $lugar_entrega = lugarEntrega::create($request->all());
        return response()->json([
            "message" => 200,
            "lugarEntrega" => [
                "id" => $lugar_entrega->id,
                "name" => $lugar_entrega->name,
                "address" => $lugar_entrega->address,
                "state" => $lugar_entrega->state ?? 1,
                "coordenadas" => $lugar_entrega->destination_coordinates,
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
        $is_exist_lugar_entrega = lugarEntrega::where("name",$request->name)->where("id","<>",$id)->first();
        if($is_exist_lugar_entrega){
            return response()->json([
                "message" => 403,
                "message_text" => "El nombre del lugar de entrega ya existe"
            ],422);
        }

        $lugar_entrega = lugarEntrega::findOrFail($id);
        $lugar_entrega->update($request->all());
        return response()->json([
            "message" => 200,
            "lugarEntrega" => [
                "id" => $lugar_entrega->id,
                "name" => $lugar_entrega->name,
                "address" => $lugar_entrega->address,
                "state" => $lugar_entrega->state,
                "coordenadas" => $lugar_entrega->destination_coordinates,
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
}
