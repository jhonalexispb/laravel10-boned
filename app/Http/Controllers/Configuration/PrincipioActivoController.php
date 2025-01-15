<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\PrincipioActivo;
use Illuminate\Http\Request;

class PrincipioActivoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");

        $principioActivo = PrincipioActivo::where("name","like","%".$search."%")
                            ->orderBy("id","desc")
                            ->paginate(25);

        return response()->json([
            "total" => $principioActivo->total(),
            "principio_activo" => $principioActivo->map(function($principioActivo){

                return [
                    "id" => $principioActivo->id,
                    "name" => $principioActivo->name,
                    "concentracion" => $principioActivo->concentracion,
                    "name_complete" => $principioActivo->name.' '.$principioActivo->concentracion,
                    "state" => $principioActivo->state,
                    "created_at" => $principioActivo->created_at->format("Y-m-d h:i A"),
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        // Normalizamos los valores de 'name' y 'concentracion'
        $normalized_name = preg_replace('/\s+/', '', $request->name);
        $normalized_concentracion = preg_replace('/\s+/', '', $request->concentracion);

        // Si ya existe un principio activo con el mismo nombre y concentración, no permitimos registrar sin concentración
        if (empty($normalized_concentracion)) {
            // Verificamos si el nombre ya existe con una concentración
            $is_exist_with_concentracion_null = PrincipioActivo::withTrashed()
            ->whereRaw('REPLACE(name, " ", "") = ?', [$normalized_name])
            ->whereNull('concentracion') // Nos aseguramos que haya una concentración asociada
            ->first();

            if($is_exist_with_concentracion_null){
                if ($is_exist_with_concentracion_null->deleted_at) {
                    // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                    return response() -> json([
                        "message" => 409,
                        "message_text" => "el principio activo ".$is_exist_with_concentracion_null->name." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                        "principio_activo" => $is_exist_with_concentracion_null->id
                    ]);
                }
                return response()->json([
                    "message" => 403,
                    "message_text" => "el principio activo ".$is_exist_with_concentracion_null->name." ya existe"
                ], 422);
            }
        } else {
            // Si el nombre con la concentración o sin concentración ya existe, respondemos según el estado
            $is_exist_principio_activo = PrincipioActivo::withTrashed()
            ->whereRaw('REPLACE(name, " ", "") = ?', [$normalized_name])
            ->whereRaw('REPLACE(concentracion, " ", "") = ?', [$normalized_concentracion])
            ->first();

            if($is_exist_principio_activo){
                if ($is_exist_principio_activo->deleted_at) {
                    // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                    return response() -> json([
                        "message" => 409,
                        "message_text" => "el principio activo ".$is_exist_principio_activo->name.' '.$is_exist_principio_activo->concentracion." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                        "principio_activo" => $is_exist_principio_activo->id
                    ]);
                }
                return response() -> json([
                    "message" => 403,
                    "message_text" => "el principio activo ".$is_exist_principio_activo->name.' '.$is_exist_principio_activo->concentracion." ya existe"
                ],422);
            }
        }

        $principio_activo = PrincipioActivo::create($request->all());
        return response()->json([
            "message" => 200,
            "principio_activo" => [
                "id" => $principio_activo->id,
                "name" => $principio_activo->name,
                "concentracion" => $principio_activo->concentracion,
                "name_complete" => $principio_activo->name.' '.$principio_activo->concentracion,
                "state" => $principio_activo->state ?? 1,
                "created_at" => $principio_activo->created_at->format("Y-m-d h:i A"),
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
        // Normalizamos los valores de 'name' y 'concentracion'
        $normalized_name = preg_replace('/\s+/', '', $request->name);
        $normalized_concentracion = preg_replace('/\s+/', '', $request->concentracion);

        // Si ya existe un principio activo con el mismo nombre y concentración, no permitimos registrar sin concentración
        if (empty($normalized_concentracion)) {
            // Verificamos si el nombre ya existe con una concentración
            $is_exist_with_concentracion_null = PrincipioActivo::withTrashed()
            ->whereRaw('REPLACE(name, " ", "") = ?', [$normalized_name])
            ->whereNull('concentracion') // Nos aseguramos que haya una concentración asociada
            ->where('id','<>',$id)
            ->first();

            if($is_exist_with_concentracion_null){
                if ($is_exist_with_concentracion_null->deleted_at) {
                    // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                    return response() -> json([
                        "message" => 409,
                        "message_text" => "el principio activo ".$is_exist_with_concentracion_null->name." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                        "principio_activo" => $is_exist_with_concentracion_null->id
                    ]);
                }
                return response()->json([
                    "message" => 403,
                    "message_text" => "el principio activo ".$is_exist_with_concentracion_null->name." ya existe"
                ], 422);
            }
        } else {
            // Si el nombre con la concentración o sin concentración ya existe, respondemos según el estado
            $is_exist_principio_activo = PrincipioActivo::withTrashed()
            ->whereRaw('REPLACE(name, " ", "") = ?', [$normalized_name])
            ->whereRaw('REPLACE(concentracion, " ", "") = ?', [$normalized_concentracion])
            ->where('id','<>',$id)
            ->first();

            if($is_exist_principio_activo){
                if ($is_exist_principio_activo->deleted_at) {
                    // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                    return response() -> json([
                        "message" => 409,
                        "message_text" => "el principio activo ".$is_exist_principio_activo->name.' '.$is_exist_principio_activo->concentracion." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                        "principio_activo" => $is_exist_principio_activo->id
                    ]);
                }
                return response() -> json([
                    "message" => 403,
                    "message_text" => "el principio activo ".$is_exist_principio_activo->name.' '.$is_exist_principio_activo->concentracion." ya existe"
                ],422);
            }
        }

        $principio_activo = PrincipioActivo::findOrFail($id);
        $principio_activo->update($request->all());
        return response()->json([
            "message" => 200,
            "principio_activo" => [
                "id" => $principio_activo->id,
                "name" => $principio_activo->name,
                "concentracion" => $principio_activo->concentracion,
                "name_complete" => $principio_activo->name.' '.$principio_activo->concentracion,
                "state" => $principio_activo->state ?? 1,
                "created_at" => $principio_activo->created_at->format("Y-m-d h:i A"),
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $d = PrincipioActivo::findOrFail($id);
        $d->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $id)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $principio_activo = PrincipioActivo::withTrashed()->findOrFail($id);

        // Restaurar el departamento si está eliminado
        if ($principio_activo->trashed()) {
            $principio_activo->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "el principio activo ".$principio_activo->name.' '.$principio_activo->concentracion." fue restaurado de manera satisfactoria",
                "principio_activo_restaurado" => [
                    "id" => $principio_activo->id,
                    "name" => $principio_activo->name,
                    "concentracion" => $principio_activo->concentracion,
                    "name_complete" => $principio_activo->name.' '.$principio_activo->concentracion,
                    "state" => $principio_activo->state ?? 1,
                    "created_at" => $principio_activo->created_at->format("Y-m-d h:i A"),
                ],
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'el principio activo no estaba eliminado'
        ],422);
    }

    public function getRecursos()
    {
        return response()->json([
            "nombres_principios_activos" => PrincipioActivo::select('name')  // Solo selecciona la columna 'name'
            ->distinct()  // Obtiene solo valores únicos
            ->get()
            ->map(function ($p) {
                return [
                    "name" => $p->name,
                ];
            })
        ]);
    }  
}
