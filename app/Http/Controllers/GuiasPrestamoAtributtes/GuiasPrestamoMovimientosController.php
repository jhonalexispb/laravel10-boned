<?php

namespace App\Http\Controllers\GuiasPrestamoAtributtes;

use App\Http\Controllers\Controller;
use App\Models\GuiasPrestamoAtributtes\GuiaPrestamoDetalle;
use App\Models\Producto;
use App\Models\ProductoAtributtes\ProductoLotes;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class GuiasPrestamoMovimientosController extends Controller
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
         $request->validate([
             'guia_prestamo_id' => 'required|exists:guias_prestamo,id',
             'producto_id' => 'required|exists:productos,id',
             'cantidad' => 'required|integer|min:1',
             'lote_id' => 'nullable|exists:producto_lotes,id',
         ]);
     
         DB::beginTransaction();
     
         try {
             $producto = Producto::findOrFail($request->producto_id);
     
             if ($producto->stock_vendedor < $request->cantidad) {
                return response() -> json([
                    "message" => 403,
                    "message_text" => "stock insuficiente del producto"
                ],422);
             }
     
             $cantidadRestante = $request->cantidad;
             $movimientos = [];
     
             if ($request->filled('lote_id')) {
                 // Solo validar ese lote
                 $lote = ProductoLotes::where('id', $request->lote_id)
                     ->where('producto_id', $request->producto_id)
                     ->where('state', 1)
                     ->where('cantidad_vendedor', '>=', $cantidadRestante)
                     ->first();
     
                 if (!$lote) {
                    return response() -> json([
                        "message" => 403,
                        "message_text" => "lote sin stock"
                    ],422);
                 }
     
                 $movimientos[] = GuiaPrestamoDetalle::create([
                     'guia_prestamo_id' => $request->guia_prestamo_id,
                     'producto_id' => $request->producto_id,
                     'unit_id' => 1,
                     'lote_id' => $lote->id,
                     'cantidad' => $cantidadRestante,
                     'stock' => $cantidadRestante,
                 ]);
     
                 $lote->cantidad_vendedor -= $cantidadRestante;
                 $lote->save();
             } else {
                 // Buscar lotes ordenados por: fecha_vencimiento asc, created_at asc, y los NULL al final
                 $lotes = ProductoLotes::where('producto_id', $request->producto_id)
                     ->where('cantidad_vendedor', '>', 0)
                     ->where('state',1)
                     ->orderByRaw("
                         CASE WHEN fecha_vencimiento IS NULL THEN 1 ELSE 0 END,
                         fecha_vencimiento ASC,
                         created_at ASC
                     ")
                     ->get();
     
                 foreach ($lotes as $lote) {
                     if ($cantidadRestante <= 0) break;
     
                     $extraer = min($cantidadRestante, $lote->cantidad_vendedor);
     
                     $movimientos[] = GuiaPrestamoDetalle::create([
                         'guia_prestamo_id' => $request->guia_prestamo_id,
                         'producto_id' => $request->producto_id,
                         'unit_id' => 1,
                         'lote_id' => $lote->id,
                         'cantidad' => $extraer,
                         'stock' => $extraer,
                     ]);
     
                     $lote->cantidad_vendedor -= $extraer;
                     $lote->save();
     
                     $cantidadRestante -= $extraer;
                 }
     
                 if ($cantidadRestante > 0) {
                     DB::rollBack();
                     return response() -> json([
                        "message" => 403,
                        "message_text" => "no hay suficiente stock distribuido en los lotes"
                    ],422);
                 }
             }
     
             $producto->stock_vendedor -= $request->cantidad;
             $producto->save();
     
             DB::commit();
     
             // Opcional: cargar relaciones para devolver detalle
             $detalles = GuiaPrestamoDetalle::with(['producto.get_laboratorio', 'lote'])
                        ->where('guia_prestamo_id', $request->guia_prestamo_id)
                        ->get();
     
             return response()->json([
                 'success' => true,
                 'mensaje' => 'Movimiento registrado correctamente.',
                 'movimientos' => $detalles->map(function ($p) {
                     return [
                         "id" => $p->id,
                         "sku" => $p->producto->sku,
                         "laboratorio" => $p->producto->get_laboratorio->name,
                         "laboratorio_id" => $p->producto->laboratorio_id,
                         "color_laboratorio" => $p->producto->get_laboratorio->color,
                         "nombre" => $p->producto->nombre,
                         "caracteristicas" => $p->producto->caracteristicas,
                         "nombre_completo" => $p->producto->nombre . ' ' . $p->producto->caracteristicas,
                         "pventa" => $p->producto->pventa ?? '0.0',
                         "imagen" => $p->producto->imagen ?? env("IMAGE_DEFAULT"),

                         "lote" => ($p->lote->lote ?? 'SIN LOTE') . ' - ' . ($p->lote->fecha_vencimiento ? Carbon::parse($p->lote->fecha_vencimiento)->format('d-m-Y') : 'SIN FECHA DE VENCIMIENTO'),
                         "cantidad" => $p->cantidad,
                     ];
                 })
             ]);
     
         } catch (\Exception $e) {
             DB::rollBack();
             return response()->json([
                 'error' => 'Ocurrió un error inesperado.',
                 'mensaje' => $e->getMessage()
             ], 500);
         }
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
