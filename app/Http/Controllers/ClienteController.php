<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $cliente = Cliente::where("ruc","like","%".$search."%")
                            ->orWhere("razonSocial", "like", "%" . $search . "%")
                            ->orderBy("id","desc")
                            ->paginate(25);
        return response()->json([
            "total" => $cliente->total(),
            "clientes" => $cliente->map(function($d){
                return [
                    "id" => $d->id,
                    "ruc" => $d->ruc,
                    "razonSocial" => $d->razonSocial,
                    "state" => $d->state,
                    "created_at" => $d->created_at->format("Y-m-d h:i A"),
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $CLIENTE_EXIST = Cliente::withTrashed()
                            ->where('ruc',$request->ruc)
                            ->first();
        if($CLIENTE_EXIST){
            if ($CLIENTE_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el cliente ".$CLIENTE_EXIST->ruc.' '.$CLIENTE_EXIST->razonSocial." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                    "cliente" => $CLIENTE_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el cliente ".$CLIENTE_EXIST->ruc.' '.$CLIENTE_EXIST->razonSocial." ya existe"
            ],422);
        }

        $cliente = Cliente::create($request->all());

        return response()->json([
            "message" => 200,
            "cliente" => [
                "id" => $cliente->id,
                "ruc" => $cliente->ruc,
                "razonSocial" => $cliente->razonSocial,
                "state" => $cliente->state ?? 1,
                "created_at" => $cliente->created_at->format("Y-m-d h:i A"),
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
        $CLIENTE_EXIST = Cliente::withTrashed()
                            ->where('ruc',$request->ruc)
                            ->where('id','<>',$id)
                            ->first();
        if($CLIENTE_EXIST){
            if ($CLIENTE_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el cliente ".$CLIENTE_EXIST->ruc.' '.$CLIENTE_EXIST->razonSocial." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                    "cliente" => $CLIENTE_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el cliente ".$CLIENTE_EXIST->ruc.' '.$CLIENTE_EXIST->razonSocial." ya existe"
            ],422);
        }

        $cliente = Cliente::findOrFail($id);
        $cliente->update($request->all());

        return response()->json([
            "message" => 200,
            "cliente" => [
                "id" => $cliente->id,
                "ruc" => $cliente->ruc,
                "razonSocial" => $cliente->razonSocial,
                "state" => $cliente->state,
                "created_at" => $cliente->created_at->format("Y-m-d h:i A"),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $d = Cliente::findOrFail($id);
        $d->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $id)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $cliente = Cliente::withTrashed()->findOrFail($id);

        // Restaurar el departamento si está eliminado
        if ($cliente->trashed()) {
            $cliente->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "el cliente ".$cliente->ruc.' '.$cliente->razonSocial." fue restaurado de manera satisfactoria",
                "cliente_restaurado" => [
                    "id" => $cliente->id,
                    "ruc" => $cliente->ruc,
                    "razonSocial" => $cliente->razonSocial,
                    "state" => $cliente->state,
                    "created_at" => $cliente->created_at->format("Y-m-d h:i A"),
                ],
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'el cliente no estaba eliminado'
        ],422);
    }

}
