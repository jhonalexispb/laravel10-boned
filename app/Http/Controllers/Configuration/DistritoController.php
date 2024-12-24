<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\Distrito;
use App\Models\Configuration\Provincia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DistritoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $provincia = Provincia::all();

        $distrito = Distrito::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $distrito->total(),
            "distrito" => $distrito->map(function($d){
                return [
                    "id" => $d->id,
                    "name" => $d->name,
                    "state" => $d->state,
                    "image" => $d->image ? env("APP_URL")."storage/".$d->image : '',
                    "created_at" => $d->created_at->format("Y-m-d h:i A"),
                    "idprovincia" => $d->idprovincia,
                    "provincia_name" => $d->provincia ? $d->provincia->name : null,
                    "departamento_name" => $d->provincia && $d->provincia->departamento ? $d->provincia->departamento->name : null,
                ];
            }),
            "provincias" => $provincia->map(function($p) {
                return [
                    "id" => $p->id,
                    "provincia_department_name" => $p->name ." / ". $p->departamento->name,
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $DISTRITO_EXIST = Distrito::withTrashed()
                            ->where('name',$request->name)
                            ->where('idprovincia',$request->idprovincia)
                            ->first();
        if($DISTRITO_EXIST){
            if ($DISTRITO_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el distrito ".$DISTRITO_EXIST->name." de la provincia de ".$DISTRITO_EXIST->provincia->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "distrito" => $DISTRITO_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el distrito ".$DISTRITO_EXIST->name." ya existe"
            ]);
        }

        if($request->hasFile("image_distrito")){
            $path = Storage::putFile("district",$request->file("image_distrito"));
            $request->request->add(["image" => $path]);
        }

        $d = Distrito::create(  $request->all());
        return response()->json([
            "message" => 200,
            "distrito" => [
                "id" => $d->id,
                "name" => $d->name,
                "state" => $d->state ?? 1,
                "image" => $d->image ? env("APP_URL")."storage/".$d->image : '',
                "created_at" => $d->created_at->format("Y-m-d h:i A"),
                "idprovincia" => $d->idprovincia,
                "provincia_name" => $d->provincia ? $d->provincia->name : null,
                "departamento_name" => $d->provincia && $d->provincia->departamento ? $d->provincia->departamento->name : null,
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Distrito $distrito)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $distrito)
    {
        $DISTRITO_EXIST = Distrito::withTrashed()
                            ->where('name',$request->name)
                            ->where('idprovincia',$request->idprovincia)
                            ->where('id','<>', $distrito)
                            ->first();
        if($DISTRITO_EXIST){
            if ($DISTRITO_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el distrito ".$DISTRITO_EXIST->name." de la provincia de ".$DISTRITO_EXIST->provincia->name." ya existe pero se encuentra eliminada, ¿Deseas restaurarla?",
                    "distrito" => $DISTRITO_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el distrito ".$DISTRITO_EXIST->name." ya existe"
            ]);
        }

        if($request->hasFile("image_distrito")){
            $path = Storage::putFile("district",$request->file("image_distrito"));
            $request->request->add(["image" => $path]);
        }

        $d = Distrito::findOrFail($distrito);
        $d->update($request->all());
        return response()->json([
            "message" => 200,
            "distrito" => [
                "id" => $d->id,
                "name" => $d->name,
                "state" => $d->state,
                "image" => $d->image ? env("APP_URL")."storage/".$d->image : '',
                "created_at" => $d->created_at->format("Y-m-d h:i A"),
                "idprovincia" => $d->idprovincia,
                "provincia_name" => $d->provincia ? $d->provincia->name : null,
                "departamento_name" => $d->provincia && $d->provincia->departamento ? $d->provincia->departamento->name : null,
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $distrito)
    {
        $d = Distrito::findOrFail($distrito);
        if($d->image){
            Storage::delete($d->image);
        }
        $d->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $distrito)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $distrito = Distrito::withTrashed()->findOrFail($distrito);

        // Restaurar el departamento si está eliminado
        if ($distrito->trashed()) {
            $distrito->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "el distrito ".$distrito->name." fue restaurada de manera satisfactoria",
                "distrito_restaurado" => [
                    "id" => $distrito->id,
                    "name" => $distrito->name,
                    "state" => $distrito->state,
                    "image" => $distrito->image ? env("APP_URL")."storage/".$distrito->image : '',
                    "created_at" => $distrito->created_at->format("Y-m-d h:i A"),
                    "idprovincia" => $distrito->idprovincia,
                    "provincia_name" => $distrito->provincia ? $distrito->provincia->name : null,
                    "departamento_name" => $distrito->provincia && $distrito->provincia->departamento ? $distrito->provincia->departamento->name : null,
                ]
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'el distrito no estaba eliminado'
        ]);
    }
}
