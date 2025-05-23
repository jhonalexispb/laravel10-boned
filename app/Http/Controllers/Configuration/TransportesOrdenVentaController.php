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
                "created_at" => $transportes->created_at->format("Y-m-d h:i A")
            ]
        ]);
    }
}
