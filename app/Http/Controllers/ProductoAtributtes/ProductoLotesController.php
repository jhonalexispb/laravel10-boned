<?php

namespace App\Http\Controllers\ProductoAtributtes;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\ProductoAtributtes\ProductoLotes;
use Illuminate\Http\Request;

class ProductoLotesController extends Controller
{
    public function index(string $id){
        $producto = Producto::findOrFail($id);
        $hoy = now();

        $lotes = $producto->get_lotes()->orderBy('fecha_vencimiento','asc')->get();

        return response()->json([
            "lotes" => $lotes->map(function($b) use ($hoy){
                $fechaVencimiento = \Carbon\Carbon::parse($b->fecha_vencimiento);
                $dias_faltantes = $hoy->diffInDays($fechaVencimiento, false);
                return [
                    "id" => $b->id,
                    "fecha_vencimiento" => $fechaVencimiento,
                    "dias_faltantes" => $dias_faltantes,
                    "lote" => $b->lote,
                    "cantidad" => $b->cantidad,
                    "state" => $b->state,
                ];
            }),

            "lotes_activos" => $producto->get_lotes()->where('state', 1)->count(),
            "lotes_inactivos" => $producto->get_lotes()->where('state', 0)->count(),
        ]);
    }

    public function store(Request $request, string $id)
    {
        $request->validate([
            'fecha_vencimiento' => 'required|date|after:today',
            'lote' => 'required',
        ]);

        $producto = Producto::findOrFail($id);

        $lotes = $producto->get_lotes()->get();
        
        $existeLote = $lotes->where('lote', $request->lote)->first();
        if ($existeLote) {
            return response()->json([
                "message" => 403,
                "message_text" => "el numero de lote ya existe"
            ], 422);
        }

        $lote = ProductoLotes::create([
            'producto_id' => $producto->id,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'lote' => $request->lote,
        ]);

        $hoy = now();
        $fechaVencimiento = \Carbon\Carbon::parse($lote->fecha_vencimiento);
        $dias_faltantes = $hoy->diffInDays($fechaVencimiento, false);

        return response()->json([
            "lotes" => [
                "id" => $lote->id,
                "fecha_vencimiento" => $fechaVencimiento,
                "dias_faltantes" => $dias_faltantes,
                "lote" => $lote->lote,
                "cantidad" => $lote->cantidad ?? 0,
                "state" => $lote->state ?? 1,
            ],
            "lotes_activos" => $producto->get_lotes()->where('state', 1)->count(),
            "lotes_inactivos" => $producto->get_lotes()->where('state', 0)->count(),
        ]);
    }

    public function update(Request $request, string $id, string $loteId)
    {
        $request->validate([
            'fecha_vencimiento' => 'required|date|after:today',
            'lote' => 'required',
        ]);

        $producto = Producto::findOrFail($id);
        
        $existeLote = ProductoLotes::where('producto_id', $id)
                               ->where('lote', $request->lote)
                               ->where('id', '<>', $loteId)
                               ->exists();
        if ($existeLote) {
            return response()->json([
                "message" => 403,
                "message_text" => "el numero de lote ya existe"
            ], 422);
        }

        $lote = ProductoLotes::findOrFail($loteId);

        $lote->update([
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'lote' => $request->lote,
        ]);

        $hoy = now();
        $fechaVencimiento = \Carbon\Carbon::parse($lote->fecha_vencimiento);
        $dias_faltantes = $hoy->diffInDays($fechaVencimiento, false);

        return response()->json([
            "lotes" => [
                "id" => $lote->id,
                "fecha_vencimiento" => $fechaVencimiento,
                "dias_faltantes" => $dias_faltantes,
                "lote" => $lote->lote,
                "cantidad" => $lote->cantidad,
                "state" => $lote->state,
            ],
            "lotes_activos" => $producto->get_lotes()->where('state', 1)->count(),
            "lotes_inactivos" => $producto->get_lotes()->where('state', 0)->count(),
        ]);
    }

    public function delete(string $id, $loteId)
    {
        $producto = Producto::findOrFail($id);
        $lote = $producto->get_lotes()->findOrFail($loteId); 

        $lote->delete();

        return response()->json([
            'message' => 'Lote eliminado con éxito',
            "lotes_activos" => $producto->get_lotes()->where('state', 1)->count(),
            "lotes_inactivos" => $producto->get_lotes()->where('state', 0)->count(),
        ]);
    }

    public function updateState(Request $request, string $id, $loteId)
    {   
        $request->validate([
            'state' => 'required|boolean',
        ]);
        $producto = Producto::findOrFail($id);
        $lote = $producto->get_lotes()->findOrFail($loteId);  // Buscar la lote

        // Eliminar la lote
        $lote->update([
            'state' => $request->state
        ]);

        $hoy = now();
        $fechaVencimiento = \Carbon\Carbon::parse($lote->fecha_vencimiento);
        $dias_faltantes = $hoy->diffInDays($fechaVencimiento, false);

        return response()->json([
            "lotes" => [
                "id" => $lote->id,
                "fecha_vencimiento" => $fechaVencimiento,
                "dias_faltantes" => $dias_faltantes,
                "lote" => $lote->lote,
                "cantidad" => $lote->cantidad,
                "state" => $lote->state,
            ],
            "lotes_activos" => $producto->get_lotes()->where('state', 1)->count(),
            "lotes_inactivos" => $producto->get_lotes()->where('state', 0)->count(),
        ]);
    }
}
