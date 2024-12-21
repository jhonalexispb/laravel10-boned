<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DepartamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $departamento = Departamento::where("name","like","%".$search."%")->orderBy("id","asc")->paginate(25);
        return response()->json([
            "total" => $departamento->total(),
            "departamento" => $departamento->map(function($d){
                return [
                    "id" => $d->id,
                    "name" => $d->name,
                    "state" => $d->state,
                    "image" => $d->image ? env("APP_URL")."storage/".$d->image : '',
                    "created_at" => $d->created_at->format("Y-m-d h:i A")
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $DEPARTMENT_EXIST = Departamento::withTrashed()->where('name',$request->name)->first();
        if($DEPARTMENT_EXIST){
            if ($DEPARTMENT_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el departamento ".$DEPARTMENT_EXIST->name." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                    "departamento" => $DEPARTMENT_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "el departamento ".$DEPARTMENT_EXIST->name." ya existe"
            ]);
        }

        if($request->hasFile("image_department")){
            $path = Storage::putFile("departments",$request->file("image_department"));
            $request->request->add(["image" => $path]);
        }

        $d = Departamento::create(  $request->all());
        return response()->json([
            "message" => 200,
            "departamento" => [
                "id" => $d->id,
                "name" => $d->name,
                "state" => $d->state ?? 1,
                "image" => $d->image ? env("APP_URL")."storage/".$d->image : '',
                "created_at" => $d->created_at->format("Y-m-d h:i A")
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $departamento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $departamento)
    {
        $DEPARTMENT_EXIST = Departamento::withTrashed()->where('name',$request->name)
                                        ->where('id','<>', $departamento)
                                        ->first();
        if($DEPARTMENT_EXIST){
            if ($DEPARTMENT_EXIST->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el departamento ".$DEPARTMENT_EXIST->name." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                    "departamento" => $DEPARTMENT_EXIST->id
                ]);
            }
            return response() -> json([
                "message" => 403,
                "message_text" => "El departamento ".$DEPARTMENT_EXIST->name." ya existe"
            ]);
        }

        if($request->hasFile("image_department")){
            if($request->image){
                Storage::delete($request->image);
            }
            $path = Storage::putFile("departments",$request->file("image_department"));
            $request->request->add(["image" => $path]);
        }

        $d = Departamento::findOrFail($departamento);
        $d->update($request->all());
        return response()->json([
            "message" => 200,
            "departamento" => [
                "id" => $d->id,
                "name" => $d->name,
                "state" => $d->state,
                "image" => $d->image ? env("APP_URL")."storage/".$d->image : '',
                "created_at" => $d->created_at->format("Y-m-d h:i A")
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $departamento)
    {
        $d = Departamento::findOrFail($departamento);
        if($d->image){
            Storage::delete($d->image);
        }
        $d->delete();

        return response()->json([
            "message" => 200
        ]);
    }

    public function restaurar(int $departamento)
    {
        // Buscar el departamento eliminado (soft deleted) por su ID
        $departamento = Departamento::withTrashed()->findOrFail($departamento);

        // Restaurar el departamento si está eliminado
        if ($departamento->trashed()) {
            $departamento->restore();
            return response()->json([
                'message' => 200,
                "message_text" => "el departamento ".$departamento->name." fue restaurado de manera satisfactoria",
                "departamento_restaurado" => [
                    "id" => $departamento->id,
                    "name" => $departamento->name,
                    "state" => $departamento->state,
                    "image" => $departamento->image ? env("APP_URL")."storage/".$departamento->image : '',
                    "created_at" => $departamento->created_at->format("Y-m-d h:i A")
                ]
            ]);
        }

        // Si el departamento no estaba eliminado
        return response()->json([
            'message' => 403,
            'message_text' => 'el departamento no estaba eliminado'
        ]);
    }
}
