<?php

namespace App\Http\Controllers\ProductoAtributtes;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\ProductoAtributtes\ProductoEscala;
use Illuminate\Http\Request;

class ProductoEscalaController extends Controller
{
    public function index(string $id){
        $producto = Producto::findOrFail($id);

        $escalas = $producto->get_escalas()->orderBy('cantidad','asc')->get();

        return response()->json([
            "escalas" => $escalas->map(function($b){
                return [
                    "id" => $b->id,
                    "cantidad" => $b->cantidad,
                    "precio" => $b->precio,
                    "state" => $b->state,
                ];
            }),

            "escalas_activas" => $producto->get_escalas()->where('state', 1)->count(),
            "escalas_inactivas" => $producto->get_escalas()->where('state', 0)->count(),
        ]);
    }

    public function store(Request $request, string $id)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
        ]);

        $producto = Producto::findOrFail($id);

        $existeEscala = $producto->get_escalas()->where('cantidad',$request->cantidad)->first();

        if($existeEscala){
            return response()->json([
                "message" => 403,
                "message_text" => "ya existe una escala con esa cantidad"
            ], 422);
        }

        $existePrecio = $producto->get_escalas()->where('precio',$request->precio)->first();

        if($existePrecio){
            return response()->json([
                "message" => 403,
                "message_text" => "ya existe una escala con ese precio"
            ], 422);
        }

        // Crear una nueva escala asociada al producto
        $escala = ProductoEscala::create([
            'producto_id' => $producto->id,
            'cantidad' => $request->cantidad,
            'precio' => $request->precio,
        ]);

        return response()->json([
            "escala" => [
                "id" => $escala->id,
                "cantidad" => $escala->cantidad,
                "precio" => $escala->precio,
                "state" => $escala->state ?? 1,
            ],

            "escalas_activas" => $producto->get_escalas()->where('state', 1)->count(),
            "escalas_inactivas" => $producto->get_escalas()->where('state', 0)->count(),
        ]);
    }

    public function update(Request $request, string $id, string $escalaId)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
        ]);

        $producto = Producto::findOrFail($id);

        $existeEscala = $producto->get_escalas()->where('cantidad',$request->cantidad)
                                            ->where('id','<>', $escalaId)
                                            ->first();

        if($existeEscala){
            return response()->json([
                "message" => 403,
                "message_text" => "ya existe una escala con esa cantidad"
            ], 422);
        }

        $existePrecio = $producto->get_escalas()->where('precio',$request->precio)
                                            ->where('id','<>', $escalaId)
                                            ->first();

        if($existePrecio){
            return response()->json([
                "message" => 403,
                "message_text" => "ya existe una escala con ese precio"
            ], 422);
        }

        $escala = $producto->get_escalas()->findOrFail($escalaId);  // Buscar la escala
        $escala->delete();

        // Actualizar los datos de la escala
        $escala = ProductoEscala::create([
            'producto_id' => $producto->id,
            'cantidad' => $request->cantidad,
            'precio' => $request->precio,
        ]);

        return response()->json([
            "escala" => [
                "id" => $escala->id,
                "cantidad" => $escala->cantidad,
                "precio" => $escala->precio,
                "state" => $escala->state ?? 1,
            ],

            "escalas_activas" => $producto->get_escalas()->where('state', 1)->count(),
            "escalas_inactivas" => $producto->get_escalas()->where('state', 0)->count(),
        ]);
    }

    public function delete(string $id, $escalaId)
    {
        $producto = Producto::findOrFail($id);
        $escala = $producto->get_escalas()->findOrFail($escalaId);  // Buscar la escala

        // Eliminar la escala
        $escala->delete();

        return response()->json([
            'message' => 'Escala eliminada con éxito',
            "escalas_activas" => $producto->get_escalas()->where('state', 1)->count(),
            "escalas_inactivas" => $producto->get_escalas()->where('state', 0)->count(),
        ]);
    }
}
