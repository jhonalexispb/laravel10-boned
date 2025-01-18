<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDigemid;
use App\Models\ClientesSucursales;
use App\Models\Configuration\Distrito;
use Illuminate\Http\Request;

class ClientesSucursalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $cliente_sucursal = ClientesSucursales::where("id","=",$search)
                            ->orderBy("id","desc")
                            ->paginate(25);
        return response()->json([
            "total" => $cliente_sucursal->total(),
            "cliente_sucursales" => $cliente_sucursal->map(function($d){
                return [
                    "id" => $d->id,
                    "ruc" => $d->ruc ? $d->ruc->ruc : null,
                    "razon_social" => $d->ruc ? $d->ruc->razonSocial : null,
                    "state" => $d->state,
                    "created_at" => $d->created_at->format("Y-m-d h:i A"),
                    "nombre_comercial" => $d->nombre_comercial,
                    "direccion" => $d->direccion,
                    "celular" => $d->celular,
                    "correo" => $d->correo,
                    "ubicacion" => $d->ubicacion,
                    "deuda" => $d->deuda,
                    "linea_credito" => $d->linea_credito,
                    "modo_trabajo" => $d->modo_trabajo,
                    "distrito" => $d->distrito ? $d->distrito->nombre : null, // Accedemos al nombre del distrito
                    "provincia" => $d->distrito && $d->distrito->provincia ? $d->distrito->provincia->nombre : null, // Accedemos al nombre de la provincia
                    "departamento" => $d->distrito && $d->distrito->provincia && $d->distrito->provincia->departamento ? $d->distrito->provincia->departamento->nombre : null, // Accedemos al nombre del departamento

                    "categoria_digemid_id" => $d->categoriaDigemid ? $d->categoriaDigemid->nombre : null,
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

    public function getRecursos()
    {   
        $distritos = Distrito::where("state",1)->get();
        $categorias_digemid = CategoriaDigemid::all();

        return response()->json([
            "distritos" => $distritos->map(function($d) {
                return [
                    "id" => $d->id,
                    "distrito_provincia_department_name" => $d->name ." / ". $d->provincia->name ." / ". $d->provincia->departamento->name,
                ];
            }),
            "categorias_digemid" => $categorias_digemid->map(function($d) {
                return [
                    "id" => $d->id,
                    "nombre" => $d->nombre ." (". $d->abreviatura.")",
                ];
            }),
        ]);
    }  
}
