<?php

namespace App\Http\Controllers;

use App\Models\Configuration\CategoriaProducto;
use App\Models\Configuration\FabricanteProducto;
use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\LineaFarmaceutica;
use App\Models\Configuration\PrincipioActivo;
use App\Models\ProductoAtributtes\CondicionAlmacenamiento;
use App\Models\ProductoAtributtes\Unidad;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        return response()->json([

            "unidades" => Unidad::all()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->abreviatura.' ('.$p->name.')',
                ];
            }),
        
            "laboratorios" => Laboratorio::where('state', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            }),
        
            "principios_activos" => PrincipioActivo::where('state', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name.' '.$p->concentracion,
                ];
            }),
        
            "lineas_farmaceuticas" => LineaFarmaceutica::where('status', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->nombre,
                ];
            }),
        
            "fabricantes" => FabricanteProducto::where('status', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->nombre.' ('.$p->pais.')',
                ];
            }),
        
            "categorias" => CategoriaProducto::where('state', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            }),
        
            "condiciones_almacenamiento" => CondicionAlmacenamiento::all()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            }),
        
        ]);
    }
}
