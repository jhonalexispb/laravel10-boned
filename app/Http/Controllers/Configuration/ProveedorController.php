<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\Distrito;
use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\Proveedor;
use App\Models\Configuration\ProveedorLaboratorio;
use App\Models\Configuration\RepresentanteProveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");

        $proveedor = Proveedor::where("name","like","%".$search."%")
                            ->orderBy("id","desc")
                            ->with('ubicacion.provincia.departamento') 
                            ->paginate(25);

        return response()->json([
            "total" => $proveedor->total(),
            "proveedor" => $proveedor->map(function($proveedor){

                $ubicacionCompleta = $proveedor->ubicacion 
                ? $proveedor->ubicacion->name . " / " .
                  $proveedor->ubicacion->provincia->name . " / " .
                  $proveedor->ubicacion->provincia->departamento->name
                : null; // Si no tiene ubicación asociada

                return [
                    "id" => $proveedor->id,
                    "razonSocial" => $proveedor->razonSocial,
                    "name" => $proveedor->name,
                    "address" => $proveedor->address,
                    "state" => $proveedor->state,
                    "iddistrito" => $proveedor->iddistrito,
                    "email" => $proveedor->email,
                    "created_at" => $proveedor->created_at->format("Y-m-d h:i A"),
                    "ubicacion" => $ubicacionCompleta,
                    "idrepresentante" => $proveedor->idrepresentante,
                    "representante" => $proveedor->idrepresentante ? $proveedor->representante->name : 'Sin representante',
                    "representante_celular" => $proveedor->idrepresentante ? $proveedor->representante->celular : '',
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $PROVEEDOR_EXIST = Proveedor::withTrashed()
                            ->where('name',$request->name)
                            ->first();
        if($PROVEEDOR_EXIST){
            if ($PROVEEDOR_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el proveedor ".$PROVEEDOR_EXIST->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarlo?",
                    "proveedor" => $PROVEEDOR_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el proveedor ".$PROVEEDOR_EXIST->name." ya existe"
            ],422);
        }

        $proveedor = Proveedor::create(  $request->all());

        $distrito = $proveedor->ubicacion;

        $ubicacionCompleta = $distrito 
        ? $distrito->name . " / " . 
          $distrito->provincia->name . " / " . 
          $distrito->provincia->departamento->name
        : null;

        return response()->json([
            "message" => 200,
            "proveedor" => [
                "id" => $proveedor->id,
                "razonSocial" => $proveedor->razonSocial,
                "name" => $proveedor->name,
                "address" => $proveedor->address,
                "state" => $proveedor->state ?? 1,
                "iddistrito" => $proveedor->iddistrito,
                "email" => $proveedor->email,
                "created_at" => $proveedor->created_at->format("Y-m-d h:i A"),
                "ubicacion" => $ubicacionCompleta,
                "idrepresentante" => $proveedor->idrepresentante,
                "representante" => $proveedor->idrepresentante ? $proveedor->representante->name : 'Sin representante',
                "representante_celular" => $proveedor->representante->celular ?? '',
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
        $PROVEEDOR_EXIST = Proveedor::withTrashed()
                            ->where('name',$request->name)
                            ->where('id','<>', $id)
                            ->first();
        if($PROVEEDOR_EXIST){
            if ($PROVEEDOR_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el proveedor ".$PROVEEDOR_EXIST->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarlo?",
                    "proveedor" => $PROVEEDOR_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el proveedor ".$PROVEEDOR_EXIST->name." ya existe"
            ],422);
        }

        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update($request->all());

        $distrito = $proveedor->ubicacion;

        $ubicacionCompleta = $distrito 
        ? $distrito->name . " / " . 
          $distrito->provincia->name . " / " . 
          $distrito->provincia->departamento->name
        : null;

        return response()->json([
            "message" => 200,
            "proveedor" => [
                "id" => $proveedor->id,
                "razonSocial" => $proveedor->razonSocial,
                "name" => $proveedor->name,
                "address" => $proveedor->address,
                "state" => $proveedor->state,
                "iddistrito" => $proveedor->iddistrito,
                "email" => $proveedor->email,
                "created_at" => $proveedor->created_at->format("Y-m-d h:i A"),
                "ubicacion" => $ubicacionCompleta,
                "idrepresentante" => $proveedor->idrepresentante,
                "representante" => $proveedor->idrepresentante ? $proveedor->representante->name : 'Sin representante',
                "representante_celular" => $proveedor->representante->celular,
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $d = Proveedor::findOrFail($id);
        $d->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $id)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $proveedor = Proveedor::withTrashed()->findOrFail($id);

        // Restaurar el departamento si está eliminado
        if ($proveedor->trashed()) {

            $distrito = $proveedor->ubicacion;

            $ubicacionCompleta = $distrito 
            ? $distrito->name . " / " . 
            $distrito->provincia->name . " / " . 
            $distrito->provincia->departamento->name
            : 'Sin ubicación';

            $proveedor->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "el proveedor ".$proveedor->name." fue restaurada de manera satisfactoria",
                "proveedor_restaurado" => [
                    "id" => $proveedor->id,
                    "razonSocial" => $proveedor->razonSocial,
                    "name" => $proveedor->name,
                    "address" => $proveedor->address,
                    "state" => $proveedor->state ?? 1,
                    "iddistrito" => $proveedor->iddistrito,
                    "email" => $proveedor->email,
                    "created_at" => $proveedor->created_at->format("Y-m-d h:i A"),
                    "ubicacion" => $ubicacionCompleta,
                    "idrepresentante" => $proveedor->idrepresentante,
                    "representante" => $proveedor->idrepresentante ? $proveedor->representante->name : 'Sin representante',
                    "representante_celular" => $proveedor->representante->celular ?? '',
                ]
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'el proveedor no estaba eliminado'
        ],422);
    }

    public function getRecursos()
    {

        $distritos = Distrito::with('provincia.departamento')->get();

        $representante = RepresentanteProveedor::where('state',1)->get();

        return response()->json([
            
            "distritos" => $distritos->map(function($d) {
                return [
                    "id" => $d->id,
                    "distrito_provincia_department_name" => $d->name ." / ". $d->provincia->name ." / ".$d->provincia->departamento->name,
                ];
            }),

            "representantes" => $representante->map(function($r) {
                return [
                    "id" => $r->id,
                    "name" => $r->name,
                    "celular" => $r->celular ?? '',
                ];
            })
        ]);
    }     

    public function getLaboratorios(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedor,id',
        ]);
        $proveedor = Proveedor::where('id', $request->proveedor_id)->first();

        return response()->json([
            "proveedor" => $proveedor->name,
            "laboratorios_proveedor" => ProveedorLaboratorio::where('proveedor_id',$request->proveedor_id)
            ->with([
                'laboratorios',
            ])
            ->orderBy('id','desc')
            ->get()
            ->map(function ($p) {
                return [
                    "id" => $p->id,
                    "laboratorio_id" => $p->laboratorio_id,
                    "name" => $p->laboratorios->name,
                    "name_margen" => $p->laboratorios->name." (".$p->margen_minimo."%)",
                    "margen_minimo" => $p->margen_minimo,
                ];
            }),
            "laboratorios" => Laboratorio::where('state','1')->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                    "margen_minimo" => $p->margen_minimo,
                ];
            }),
        ]);
    } 
    
    public function registerLaboratorioProveedor(Request $request){
        $request->validate([
            'proveedor_id' => 'required|exists:proveedor,id',
            'laboratorio_id' => 'required|exists:laboratorio,id',
            'margen_minimo' => 'required|numeric|min:0.01',
        ]);

        $existingRelacion = ProveedorLaboratorio::where('proveedor_id', $request->proveedor_id)
                                            ->where('laboratorio_id', $request->laboratorio_id)
                                            ->first();

        if ($existingRelacion) {
            return response() -> json([
                "message" => 403,
                "message_text" => "la relacion ya existe"
            ],422);
        }

        $relacion = ProveedorLaboratorio::create(  $request->all());

        return response()->json([
            "relacion" => [
                "id" => $relacion->id,
                "laboratorio_id" => $relacion->laboratorio_id,
                "name" => $relacion->laboratorios->name,
                "name_margen" => $relacion->laboratorios->name." (".$relacion->margen_minimo."%)",
                "margen_minimo" => $relacion->margen_minimo,
            ],
        ]);
    }

    public function updateLaboratorioProveedor(Request $request, String $id){
        $request->validate([
            'proveedor_id' => 'required|exists:proveedor,id',
            'laboratorio_id' => 'required|exists:laboratorio,id',
            'margen_minimo' => 'required|numeric|min:0.01',
        ]);

        $existingRelacion = ProveedorLaboratorio::where('proveedor_id', $request->proveedor_id)
                                            ->where('laboratorio_id', $request->laboratorio_id)
                                            ->where('id','<>',$id)
                                            ->first();

        if ($existingRelacion) {
            return response() -> json([
                "message" => 403,
                "message_text" => "la relacion ya existe"
            ],422);
        }

        $relacion = ProveedorLaboratorio::findOrFail($id);

        $relacion->update($request->all());

        return response()->json([
            "relacion" => [
                "id" => $relacion->id,
                "laboratorio_id" => $relacion->laboratorio_id,
                "name" => $relacion->laboratorios->name,
                "name_margen" => $relacion->laboratorios->name." (".$relacion->margen_minimo."%)",
                "margen_minimo" => $relacion->margen_minimo,
            ],
        ]);
    }

    public function deleteLaboratorioProveedor(String $id){

        $d = ProveedorLaboratorio::findOrFail($id);
        $d->delete();

        return response()->json([
            "message" => 200
        ]);
    }
}
