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

        $departamento = Departamento::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
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
        $DEPARTMENT_EXIST = Departamento::where('name',$request->email)->first();
        if($DEPARTMENT_EXIST){
            return response() -> json([
                "message" => 403,
                "message_text" => "El departamento ya existe"
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
    public function show(Departamento $departamento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Departamento $departamento)
    {
        $DEPARTMENT_EXIST = Departamento::where('name',$request->email)->first();
        if($DEPARTMENT_EXIST){
            return response() -> json([
                "message" => 403,
                "message_text" => "El departamento ya existe"
            ]);
        }

        if($request->hasFile("image_department")){
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
    public function destroy(Departamento $departamento)
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
}
