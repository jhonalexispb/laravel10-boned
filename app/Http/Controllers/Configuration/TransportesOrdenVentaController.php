<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\OrdenVentaAtributtes\TransportesOrdenVenta;
use Illuminate\Http\Request;

class TransportesOrdenVentaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get("search");

        $transporte = TransportesOrdenVenta::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $transporte->total(),
            "transportes" => $transporte->map(function($t){
                return [
                    "id" => $t->id,
                    "ruc" => $t->ruc,
                    "razonSocial" => $t->razonSocial,
                    "name" => $t->name,
                    "direccion" => $t->direccion,
                    "celular" => $t->celular,
                    "solicita_guia" => $t->solicita_guia,
                    "state" => $t->state,
                    "latitud" => $t->latitud,
                    "longitud" => $t->longitud,
                    "created_at" => $t->created_at->format("Y-m-d h:i A")
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $is_exist_transporte = TransportesOrdenVenta::where("name",$request->name)->first();
        if($is_exist_transporte){
            return response()->json([
                "message" => 403,
                "message_text" => "El nombre del transporte ya existe"
            ],422);
        }

        $transportes = TransportesOrdenVenta::create($request->all());
        return response()->json([
            "message" => 200,
            "transportes" => [
                "id" => $transportes->id,
                "ruc" => $transportes->ruc,
                "razonSocial" => $transportes->razonSocial,
                "name" => $transportes->name,
                "direccion" => $transportes->direccion,
                "celular" => $transportes->celular,
                "solicita_guia" => $transportes->solicita_guia,
                "state" => $transportes->state ?? 1,
                "latitud" => $transportes->latitud,
                "longitud" => $transportes->longitud,
                "created_at" => $transportes->created_at->format("Y-m-d h:i A")
            ]
        ]);
    }

    public function update(Request $request, string $id)
    {
        $is_exist = TransportesOrdenVenta::where("name",$request->name)->where("id","<>",$id)->first();
        if($is_exist){
            return response()->json([
                "message" => 403,
                "message_text" => "El nombre de la sucursal ya existe"
            ],422);
        }

        $is_ruc_exist = TransportesOrdenVenta::where("ruc", $request->ruc)
            ->where("id", "<>", $id)
            ->first();

        if ($is_ruc_exist) {
            return response()->json([
                "message" => 403,
                "message_text" => "El RUC ya está registrado"
            ], 422);
        }


        $transporte = TransportesOrdenVenta::findOrFail($id);
        $transporte->update($request->all());
        return response()->json([
            "message" => 200,
            "transporte" => [
                "id" => $transporte->id,
                "ruc" => $transporte->ruc,
                "razonSocial" => $transporte->razonSocial,
                "name" => $transporte->name,
                "direccion" => $transporte->direccion,
                "celular" => $transporte->celular,
                "solicita_guia" => $transporte->solicita_guia,
                "state" => $transporte->state ?? 1,
                "latitud" => $transporte->latitud,
                "longitud" => $transporte->longitud,
                "created_at" => $transporte->created_at->format("Y-m-d h:i A")
            ]
        ]);
    }

    public function destroy(string $id)
    {
        $transporte = TransportesOrdenVenta::findOrFail($id);

        // Verificar si tiene órdenes de venta asociadas
        if ($transporte->ordenesVenta()->exists()) {
            return response()->json([
                "message" => 403,
                "message_text" => "no se puede eliminar el transporte porque está asociado a una orden de venta."
            ], 422);
        }

        // Si no tiene órdenes, eliminar
        $transporte->delete();

        return response()->json([
            "message" => "transporte eliminado correctamente."
        ]);
    }
}
