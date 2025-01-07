<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\Proveedor;
use Illuminate\Http\Request;

class LaboratorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $proveedor = Proveedor::all();

        $laboratorio = Laboratorio::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
        return response()->json([
            "total" => $laboratorio->total(),
            "laboratorio" => $laboratorio->map(function($d){
                return [
                    "id" => $d->id,
                    "name" => $d->name,
                    "state" => $d->state,
                    "image" => $d->image ? env("APP_URL")."storage/".$d->image : '',
                    "color" => $d->color,
                    "created_at" => $d->created_at->format("Y-m-d h:i A"),
                ];
            }),
            "proveedores" => $proveedor->map(function($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
