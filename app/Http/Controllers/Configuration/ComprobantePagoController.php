<?php

namespace App\Http\Controllers\configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\ComprobantePago;
use Illuminate\Http\Request;

class ComprobantePagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $comprobante_pago = ComprobantePago::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $comprobante_pago->total(),
            "comprobante_pago" => $comprobante_pago->map(function($c){
                return [
                    "id" => $c->id,
                    "name" => $c->name,
                    "state" => $c->state,
                    "created_at" => $c->created_at->format("Y-m-d h:i A")
                ];
            })
        ]);
    }

    
    public function store(Request $request)
    {
        $is_exist_comprobante = ComprobantePago::where("name", $request->name)->first();
        if($is_exist_comprobante){
            return response()->json([
                "message" => 403,
                "message_text" => "el nombre del comprobante ya existe"
            ],422);
        }

        $comprobante_pago = ComprobantePago::create($request->all());
        return response()->json([
            "message" => 200,
            "comprobante_pago" => [
                "id" => $comprobante_pago->id,
                "name" => $comprobante_pago->name,
                "state" => $comprobante_pago->state ?? 1,
                "created_at" => $comprobante_pago->created_at->format('Y-m-d h:i A'),
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

    public function update(Request $request, string $id)
    {
        $is_exist_comprobante = ComprobantePago::where("name", $request->name)
                                                ->where("id","<>",$id)
                                                ->first();
        if($is_exist_comprobante){
            return response()->json([
                "message" => 403,
                "message_text" => "el nombre del comprobante ya existe"
            ],422);
        }

        $comprobante_pago = ComprobantePago::findOrFail($id);

        $comprobante_pago->update($request->all());
        return response()->json([
            "message" => 200,
            "comprobante_pago" => [
                "id" => $comprobante_pago->id,
                "name" => $comprobante_pago->name,
                "state" => $comprobante_pago->state,
                "created_at" => $comprobante_pago->created_at->format('Y-m-d h:i A'),
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comprobante_pago = ComprobantePago::findOrFail($id);
        $comprobante_pago->delete();

        return response()->json([
            "message" => 200
        ]);
    }
}
