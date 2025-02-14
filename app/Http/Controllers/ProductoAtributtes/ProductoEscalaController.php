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

        if($request->precio >= $producto->pventa){
            return response()->json([
                "message" => 403,
                "message_text" => "el precio de la escala no puede ser mayor al precio normal del producto (S/ ".$producto->pventa.")"
            ], 422);
        }

        $escalas = $producto->get_escalas()->orderBy('cantidad','desc')->get();
        if($escalas->isNotEmpty()){
            $existeEscala = $escalas->where('cantidad', $request->cantidad)->first();
            if ($existeEscala) {
                return response()->json([
                    "message" => 403,
                    "message_text" => "ya existe una escala con esa cantidad"
                ], 422);
            }

            $existePrecio = $escalas->where('precio', $request->precio)->first();
            if ($existePrecio) {
                return response()->json([
                    "message" => 403,
                    "message_text" => "ya existe una escala con ese precio"
                ], 422);
            }

            $escalaAnterior = null;
            $escalaSiguiente = null;

            foreach ($escalas as $index => $escala) {
                if ($escala->cantidad < $request->cantidad) {
                    $escalaSiguiente = $escala;
                    $escalaAnterior = $escalas[$index - 1] ?? null;
                    break;
                }
            }

            if (!isset($escalaSiguiente)) {
                $escalaAnterior = $escalas[count($escalas) - 1]; // Asignamos la última escala como escalaAnterior
                $escalaSiguiente = null;
            }

            if ($escalaAnterior && $escalaSiguiente) {
                // La nueva escala está entre la escala anterior y la siguiente
                if ($request->precio > $escalaSiguiente->precio || $request->precio < $escalaAnterior->precio) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "se encontro una incoherencia, el <span class='text-success'>precio</span> de la escala (".$request->cantidad." a S/".$request->precio.") debe ser menor que la escala de ".$escalaSiguiente->cantidad." a S/ ".$escalaSiguiente->precio." y mayor que la escala de ".$escalaAnterior->cantidad." a S/ ".$escalaAnterior->precio
                    ], 422);
                }
            } elseif ($escalaAnterior) {
                // La nueva escala es menor que todas las escalas existentes
                if ($request->precio < $escalaAnterior->precio) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "se encontro una incoherencia, el <span class='text-success'>precio</span> de la escala (".$request->cantidad." a S/".$request->precio.") debe ser mayor que la escala de ".$escalaAnterior->cantidad." a S/ ".$escalaAnterior->precio." y menor que el precio normal del producto S/".$producto->pventa
                    ], 422);
                }
            } elseif ($escalaSiguiente) {
                // La nueva escala es mayor que todas las escalas existentes
                if ($request->precio > $escalaSiguiente->precio) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "se encontro una incoherencia, el <span class='text-success'>precio</span> de la escala (".$request->cantidad." a S/".$request->precio.") debe ser menor que la escala de ".$escalaSiguiente->cantidad." a S/ ".$escalaSiguiente->precio 
                    ], 422);
                }
            }
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

        if($request->precio >= $producto->pventa){
            return response()->json([
                "message" => 403,
                "message_text" => "el precio de la escala no puede ser mayor al precio normal del producto (S/ ".$producto->pventa.")"
            ], 422);
        }

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

        $escalas = $producto->get_escalas()->orderBy('cantidad','desc')->get();
        
        $escala = $producto->get_escalas()->findOrFail($escalaId);

        if($escala->cantidad == $request->cantidad && $escala->precio == $request->precio){
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

        $escalas = $escalas->reject(function($item) use ($escalaId) {
            return $item->id == $escalaId;
        });

        $escalaAnterior = null;
        $escalaSiguiente = null;

        foreach ($escalas as $index => $esc) {
            if ($esc->cantidad < $request->cantidad) {
                $escalaSiguiente = $esc;
                $escalaAnterior = $esc[$index - 1] ?? null;
                break;
            }
        }

        if (!isset($escalaSiguiente)) {
            $escalaAnterior = $escalas[count($escalas) - 1]; // Asignamos la última escala como escalaAnterior
            $escalaSiguiente = null;
        }

        if ($escalaAnterior && $escalaSiguiente) {
            // La nueva escala está entre la escala anterior y la siguiente
            if ($request->precio > $escalaSiguiente->precio || $request->precio < $escalaAnterior->precio) {
                return response()->json([
                    "message" => 403,
                    "message_text" => "se encontro una incoherencia, el <span class='text-success'>precio</span> de la escala (".$request->cantidad." a S/".$request->precio.") debe ser menor que la escala de ".$escalaSiguiente->cantidad." a S/ ".$escalaSiguiente->precio." y mayor que la escala de ".$escalaAnterior->cantidad." a S/ ".$escalaAnterior->precio
                ], 422);
            }
        } elseif ($escalaAnterior) {
            // La nueva escala es menor que todas las escalas existentes
            if ($request->precio < $escalaAnterior->precio) {
                return response()->json([
                    "message" => 403,
                    "message_text" => "se encontro una incoherencia, el <span class='text-success'>precio</span> de la escala (".$request->cantidad." a S/".$request->precio.") debe ser mayor que la escala de ".$escalaAnterior->cantidad." a S/ ".$escalaAnterior->precio." y menor que el precio normal del producto S/".$producto->pventa
                ], 422);
            }
        } elseif ($escalaSiguiente) {
            // La nueva escala es mayor que todas las escalas existentes
            if ($request->precio > $escalaSiguiente->precio) {
                return response()->json([
                    "message" => 403,
                    "message_text" => "se encontro una incoherencia, el <span class='text-success'>precio</span> de la escala (".$request->cantidad." a S/".$request->precio.") debe ser menor que la escala de ".$escalaSiguiente->cantidad." a S/ ".$escalaSiguiente->precio 
                ], 422);
            }
        }

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

    public function updateState(Request $request, string $id, $escalaId)
    {   
        $request->validate([
            'state' => 'required|boolean',
        ]);
        $producto = Producto::findOrFail($id);
        $escala = $producto->get_escalas()->findOrFail($escalaId);  // Buscar la escala

        // Eliminar la escala
        $escala->update([
            'state' => $request->state
        ]);

        return response()->json([
            "escala" => [
                "id" => $escala->id,
                "cantidad" => $escala->cantidad,
                "precio" => $escala->precio,
                "state" => $escala->state,
            ],

            "escalas_activas" => $producto->get_escalas()->where('state', 1)->count(),
            "escalas_inactivas" => $producto->get_escalas()->where('state', 0)->count(),
        ]);
    }

    public function updateAllEscalasState(Request $request, string $id)
    {
        // Validación del estado
        $request->validate([
            'state' => 'required|boolean',
        ]);

        // Obtener el producto
        $producto = Producto::findOrFail($id);

        // Actualizar el estado de todas las escalas del producto
        $producto->get_escalas()->each(function ($escala) use ($request) {
            $escala->update([
                'state' => $request->state,
            ]);
        });

        // Contar las escalas activas e inactivas después de la actualización
        $escalas_activas = $producto->get_escalas()->where('state', 1)->count();
        $escalas_inactivas = $producto->get_escalas()->where('state', 0)->count();

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
            "escalas_activas" => $escalas_activas,
            "escalas_inactivas" => $escalas_inactivas,
        ]);
    }
}
