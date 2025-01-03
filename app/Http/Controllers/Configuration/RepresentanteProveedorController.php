<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\RepresentanteProveedor;
use Illuminate\Http\Request;

class RepresentanteProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $representante = RepresentanteProveedor::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $representante->total(),
            "representate_proveedor" => $representante->map(function($r){
                return [
                    "id" => $r->id,
                    "name" => $r->name,
                    "celular" => $r->celular,
                    "telefono" => $r->telefono,
                    "email" => $r->email,
                    "state" => $r->state,
                    "created_at" => $r->created_at->format("Y-m-d h:i A")
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $REPRESENTANTE_EXIST = RepresentanteProveedor::withTrashed()
                            ->where('email',$request->email)
                            ->first();
        if($REPRESENTANTE_EXIST){
            if ($REPRESENTANTE_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el correo ".$REPRESENTANTE_EXIST->email." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "representante_proveedor" => $REPRESENTANTE_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el correo ".$REPRESENTANTE_EXIST->email." ya existe"
            ]);
        }

        $r = RepresentanteProveedor::create(  $request->all());
        return response()->json([
            "message" => 200,
            "representante_proveedor" => [
                "id" => $r->id,
                "name" => $r->name,
                "celular" => $r->celular,
                "telefono" => $r->telefono,
                "email" => $r->email,
                "state" => $r->state ?? 1,
                "created_at" => $r->created_at->format("Y-m-d h:i A")
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
        $request->validate([
            'celular' => 'nullable|numeric|digits:9',  // Celular opcional, debe ser numérico y no mayor de 9 dígitos
        ], [
            'celular.numeric' => 'el número de celular debe ser numérico.',
            'celular.digits' => 'el número de celular debe tener 9 dígitos.',
        ]);
    

        $REPRESENTANTE_EXIST = RepresentanteProveedor::withTrashed()
                            ->where('email',$request->email)
                            ->where('id','<>', $id)
                            ->first();
        if($REPRESENTANTE_EXIST){
            if ($REPRESENTANTE_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el correo ".$REPRESENTANTE_EXIST->email." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "representante_proveedor" => $REPRESENTANTE_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el correo ".$REPRESENTANTE_EXIST->email." ya existe"
            ]);
        }

        $r = RepresentanteProveedor::findOrFail($id);
        $r->update($request->all());
        return response()->json([
            "message" => 200,
            "representante_proveedor" => [
                "id" => $r->id,
                "name" => $r->name,
                "celular" => $r->celular,
                "telefono" => $r->telefono,
                "email" => $r->email,
                "state" => $r->state,
                "created_at" => $r->created_at->format("Y-m-d h:i A")
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $c = RepresentanteProveedor::findOrFail($id);
        $c->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $rep)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $rep = RepresentanteProveedor::withTrashed()->findOrFail($rep);

        // Restaurar el departamento si está eliminado
        if ($rep->trashed()) {
            $rep->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "el correo ".$rep->email." fue restaurado de manera satisfactoria",
                "representante_proveedor_restaurado" => [
                    "id" => $rep->id,
                    "name" => $rep->name,
                    "celular" => $rep->celular,
                    "telefono" => $rep->telefono,
                    "email" => $rep->email,
                    "state" => $rep->state,
                    "created_at" => $rep->created_at->format("Y-m-d h:i A")
                ]
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'el representante no estaba eliminado'
        ]);
    }
}
