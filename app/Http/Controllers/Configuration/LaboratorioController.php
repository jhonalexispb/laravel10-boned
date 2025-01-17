<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LaboratorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $laboratorio = Laboratorio::where("name","like","%".$search."%")
                                    ->orderBy("id","desc")
                                    ->with('proveedores')
                                    ->paginate(25);
        return response()->json([
            "total" => $laboratorio->total(),
            "laboratorio" => $laboratorio->map(function($d){
                return [
                    "id" => $d->id,
                    "name" => $d->name,
                    "state" => $d->state,
                    "image" => $d->image ? env("APP_URL")."storage/".$d->image : '',
                    "margen_minimo" => $d->margen_minimo,
                    "color" => $d->color,
                    "created_at" => $d->created_at->format("Y-m-d h:i A"),
                    "idproveedor" => $d->proveedores->map(function ($p) {
                        return $p->id;
                    }),
                    "proveedor_laboratorio" => $d->proveedores->map(function ($p) {
                        return [
                            "id" => $p->id,
                            "name" => $p->name,
                        ];
                    }),
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        $proveedores = json_decode($request->input('proveedores'), true);
        $request->merge(['proveedores' => $proveedores]);
        $request->validate([
            'image_laboratorio' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'margen_minimo' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'color' => 'required|string|max:7',
            'proveedores' => 'required|array|min:1',  // Validamos que proveedores sea un array y contenga al menos un proveedor
            'proveedores.*' => 'exists:proveedor,id', // Verifica que cada ID de proveedor sea válido y exista en la tabla `proveedor`
        ]);

        $LABORATORIO_EXIST = Laboratorio::withTrashed()
                            ->where('name',$request->name)
                            ->first();
        if($LABORATORIO_EXIST){
            if ($LABORATORIO_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el laboratorio ".$LABORATORIO_EXIST->name." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                    "laboratorio" => $LABORATORIO_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el laboratorio ".$LABORATORIO_EXIST->name." ya existe"
            ],422);
        }

        if($request->hasFile("image_laboratorio")){
            $path = Storage::putFile("laboratorios",$request->file("image_laboratorio"));
            $request->request->add(["image" => $path]);
        }

        $laboratorio = Laboratorio::create(  $request->all());

        $laboratorio->proveedores()->attach($proveedores);

        $laboratorio->load('proveedores');

        return response()->json([
            "message" => 200,
            "laboratorio" => [
                "id" => $laboratorio->id,
                "name" => $laboratorio->name,
                "state" => $laboratorio->state ?? 1,
                "image" => $laboratorio->image ? env("APP_URL")."storage/".$laboratorio->image : '',
                "margen_minimo" => $laboratorio->margen_minimo,
                "color" => $laboratorio->color,
                "created_at" => $laboratorio->created_at->format("Y-m-d h:i A"),
                "idproveedor" => $laboratorio->proveedores->map(function ($p) {
                        return $p->id;
                    }),
                "proveedor_laboratorio" => $laboratorio->proveedores->map(function ($p) {
                    return [
                        "id" => $p->id,
                        "name" => $p->name,
                    ];
                }),
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
        $proveedores = json_decode($request->input('proveedores'), true);
        $request->merge(['proveedores' => $proveedores]);
        $request->validate([
            'image_laboratorio' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'margen_minimo' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'color' => 'required|string|max:7',
            'proveedores' => 'required|array|min:1',  // Validamos que proveedores sea un array y contenga al menos un proveedor
            'proveedores.*' => 'exists:proveedor,id', // Verifica que cada ID de proveedor sea válido y exista en la tabla `proveedor`
        ]);

        $LABORATORIO_EXIST = Laboratorio::withTrashed()
                            ->where('name',$request->name)
                            ->where('id','<>',$id)
                            ->first();
        if($LABORATORIO_EXIST){
            if ($LABORATORIO_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el laboratorio ".$LABORATORIO_EXIST->name." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                    "laboratorio" => $LABORATORIO_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el laboratorio ".$LABORATORIO_EXIST->name." ya existe"
            ],422);
        }

        if($request->hasFile("image_laboratorio")){
            $path = Storage::putFile("laboratorios",$request->file("image_laboratorio"));
            $request->request->add(["image" => $path]);
        }

        $laboratorio = Laboratorio::findOrFail($id);
        $laboratorio->update($request->all());

        $laboratorio->proveedores()->sync($proveedores);

        $laboratorio->load('proveedores');

        return response()->json([
            "message" => 200,
            "laboratorio" => [
                "id" => $laboratorio->id,
                "name" => $laboratorio->name,
                "state" => $laboratorio->state ?? 1,
                "image" => $laboratorio->image ? env("APP_URL")."storage/".$laboratorio->image : '',
                "margen_minimo" => $laboratorio->margen_minimo,
                "color" => $laboratorio->color,
                "created_at" => $laboratorio->created_at->format("Y-m-d h:i A"),
                "idproveedor" => $laboratorio->proveedores->map(function ($p) {
                        return $p->id;
                    }),
                "proveedor_laboratorio" => $laboratorio->proveedores->map(function ($p) {
                    return [
                        "id" => $p->id,
                        "name" => $p->name,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $d = Laboratorio::findOrFail($id);
        if($d->image){
            Storage::delete($d->image);
        }
        $d->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $id)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $laboratorio = Laboratorio::withTrashed()->findOrFail($id);

        // Restaurar el departamento si está eliminado
        if ($laboratorio->trashed()) {
            $laboratorio->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "el laboratorio ".$laboratorio->name." fue restaurado de manera satisfactoria",
                "laboratorio_restaurado" => [
                    "id" => $laboratorio->id,
                    "name" => $laboratorio->name,
                    "state" => $laboratorio->state ?? 1,
                    "image" => $laboratorio->image ? env("APP_URL")."storage/".$laboratorio->image : '',
                    "margen_minimo" => $laboratorio->margen_minimo,
                    "color" => $laboratorio->color,
                    "created_at" => $laboratorio->created_at->format("Y-m-d h:i A"),
                    "idproveedor" => $laboratorio->proveedores->map(function ($p) {
                            return $p->id;
                        }),
                    "proveedor_laboratorio" => $laboratorio->proveedores->map(function ($p) {
                        return [
                            "id" => $p->id,
                            "name" => $p->name,
                        ];
                    }),
                ],
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'el laboratorio no estaba eliminado'
        ],422);
    }

    public function getRecursos()
    {
        return response()->json([
            
            "proveedores" => Proveedor::all()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            })
        ]);
    }   
}
