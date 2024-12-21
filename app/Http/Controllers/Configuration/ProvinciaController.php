<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\Departamento;
use App\Models\Configuration\Provincia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProvinciaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $departamentos = Departamento::all();

        $provincia = Provincia::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $provincia->total(),
            "provincia" => $provincia->map(function($p){
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                    "state" => $p->state,
                    "image" => $p->image ? env("APP_URL")."storage/".$p->image : '',
                    "created_at" => $p->created_at->format("Y-m-d h:i A"),
                    "iddepartamento" => $p->iddepartamento,
                    "departamento_name" => $p->departamento ? $p->departamento->name : null,
                ];
            }),
            "departamentos" => $departamentos->map(function($d) {
                return [
                    "id" => $d->id,
                    "name" => $d->name,
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $PROVINCIA_EXIST = Provincia::withTrashed()
                            ->where('name',$request->name)
                            ->where('iddepartamento',$request->iddepartamento)
                            ->first();
        if($PROVINCIA_EXIST){
            if ($PROVINCIA_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "la provincia ".$PROVINCIA_EXIST->name." del departamento de ".$PROVINCIA_EXIST->departamento->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "provincia" => $PROVINCIA_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "la provincia ".$PROVINCIA_EXIST->name." ya existe"
            ]);
        }

        if($request->hasFile("image_provincia")){
            $path = Storage::putFile("provincias",$request->file("image_provincia"));
            $request->request->add(["image" => $path]);
        }

        $d = Provincia::create(  $request->all());
        return response()->json([
            "message" => 200,
            "provincia" => [
                "id" => $d->id,
                "name" => $d->name,
                "state" => $d->state ?? 1,
                "image" => $d->image ? env("APP_URL")."storage/".$d->image : '',
                "created_at" => $d->created_at->format("Y-m-d h:i A"),
                "iddepartamento" => $d->iddepartamento,
                "departamento_name" => $d->departamento ? $d->departamento->name : null,
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $provincia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $provincia)
    {
        $PROVINCIA_EXIST = Provincia::withTrashed()->where('name',$request->name)
                                        ->where('iddepartamento',$request->iddepartamento)
                                        ->where('id','<>', $provincia)
                                        ->first();

        if($PROVINCIA_EXIST){
            if ($PROVINCIA_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "la provincia ".$PROVINCIA_EXIST->name." del departamento de ".$PROVINCIA_EXIST->departamento->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "provincia" => $PROVINCIA_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "la provincia ".$PROVINCIA_EXIST->name." ya existe"
            ]);
        }

        if($request->hasFile("image_provincia")){
            if($request->image){
                Storage::delete($request->image);
            }
            $path = Storage::putFile("provincias",$request->file("image_provincia"));
            $request->request->add(["image" => $path]);
        }

        $p = Provincia::findOrFail($provincia);
        $p->update($request->all());
        return response()->json([
            "message" => 200,
            "provincia" => [
                "id" => $p->id,
                "name" => $p->name,
                "state" => $p->state,
                "image" => $p->image ? env("APP_URL")."storage/".$p->image : '',
                "created_at" => $p->created_at->format("Y-m-d h:i A"),
                "iddepartamento" => $p->iddepartamento,
                "departamento_name" => $p->departamento ? $p->departamento->name : null,
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $provincia)
    {
        $p = Provincia::findOrFail($provincia);
        if($p->image){
            Storage::delete($p->image);
        }
        $p->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $provincia)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $provincia = Provincia::withTrashed()->findOrFail($provincia);

        // Restaurar el departamento si está eliminado
        if ($provincia->trashed()) {
            $provincia->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "la provincia ".$provincia->name." fue restaurada de manera satisfactoria",
                "provincia_restaurada" => [
                    "id" => $provincia->id,
                    "name" => $provincia->name,
                    "state" => $provincia->state,
                    "image" => $provincia->image ? env("APP_URL")."storage/".$provincia->image : '',
                    "created_at" => $provincia->created_at->format("Y-m-d h:i A"),
                    "iddepartamento" => $provincia->iddepartamento,
                    "departamento_name" => $provincia->departamento ? $provincia->departamento->name : null,
                ]
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'la provincia no estaba eliminada'
        ]);
    }
}
