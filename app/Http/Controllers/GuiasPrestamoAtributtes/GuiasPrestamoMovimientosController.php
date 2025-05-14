<?php

namespace App\Http\Controllers\GuiasPrestamoAtributtes;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuiaPrestamo\GuiaPrestamoResource;
use App\Models\GuiaPrestamo;
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
            'lote_id' => 'nullable|exists:producto_lote_relation,id',
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
    
            $cantidadRestante = (int) $request->cantidad;
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
                $lote->cantidad -= $cantidadRestante;
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
                    $lote->cantidad -= $extraer;
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
            $producto->stock -= $request->cantidad;
            $producto->actualizarEstadosStock();
            $producto->save();

            //actualizamos el estado de la guia, de "en proceso de creacion" a "pendiente"
            GuiaPrestamo::where('id', $request->guia_prestamo_id)->update(['state' => 1]);
    
            DB::commit();
    
            // Opcional: cargar relaciones para devolver detalle
            $detalles = GuiaPrestamoDetalle::with(['producto.get_laboratorio', 'lote'])
        ->whereIn('id', collect($movimientos)->pluck('id'))
        ->get();
    
            return response()->json([
            'movimiento' => $detalles->map(function ($p) {
                return [
                    "id" => $p->id,
                    "producto_id" => $p->producto_id,
                    "sku" => $p->producto->sku,
                    "laboratorio" => $p->producto->get_laboratorio->name,
                    "color_laboratorio" => $p->producto->get_laboratorio->color,
                    "nombre" => $p->producto->nombre,
                    "caracteristicas" => $p->producto->caracteristicas,
                    "pventa" => $p->producto->pventa ?? '0.0',
                    "imagen" => $p->producto->imagen ?? env("IMAGE_DEFAULT"),
                    "lote" => ($p->lote->lote ?? 'SIN LOTE') . ' - ' . ($p->lote->fecha_vencimiento ? Carbon::parse($p->lote->fecha_vencimiento)->format('d-m-Y') : 'SIN FV'),
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $movimiento = GuiaPrestamoDetalle::findOrFail($id);

        if($movimiento->cantidad == $request->cantidad){
            return response()->json([
                'message' => 'No se realizaron cambios porque la cantidad es la misma.'
            ], 200);
        }

        DB::beginTransaction();
        try {

            if($movimiento->cantidad > $request->cantidad){ //habia 8 pero ahora quiere 5, entonces devolver a los stocks
                $diferencia = $movimiento->cantidad - $request->cantidad;
                $producto = Producto::findOrFail($movimiento->producto_id);
                $producto->stock += $diferencia;
                $producto->stock_vendedor += $diferencia;
                $producto->actualizarEstadosStock();
                $producto->save();

                $lote = ProductoLotes::findOrFail($movimiento->lote_id);
                $lote->cantidad += $diferencia;
                $lote->cantidad_vendedor += $diferencia;
                $lote->save();
            } else { // había 8 pero ahora quiere 10, entonces pedir más
                $diferencia = $request->cantidad - $movimiento->cantidad;
            
                $producto = Producto::findOrFail($movimiento->producto_id);
                $lote = ProductoLotes::findOrFail($movimiento->lote_id);
            
                // Verificar si hay suficiente stock
                if (
                    $producto->stock < $diferencia || $producto->stock_vendedor < $diferencia ||
                    $lote->cantidad < $diferencia || $lote->cantidad_vendedor < $diferencia
                ) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => $lote->cantidad_vendedor <= 0
                            ? "No hay stock suficiente en el lote"
                            : "No hay stock suficiente en el lote, solo puedes aumentar hasta " . $lote->cantidad_vendedor,
                    ], 422);
                }
            
                // Si hay suficiente stock, se descuenta
                $producto->stock -= $diferencia;
                $producto->stock_vendedor -= $diferencia;
                $producto->actualizarEstadosStock();
                $producto->save();
            
                $lote->cantidad -= $diferencia;
                $lote->cantidad_vendedor -= $diferencia;
                $lote->save();
            }

            $movimiento->cantidad = $request->cantidad;
            $movimiento->stock = $request->cantidad;
            $movimiento->save();

            DB::commit();

            return response()->json([
                'message' => 'Movimiento actualizado correctamente'
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $movimiento = GuiaPrestamoDetalle::findOrFail($id);

            $producto = Producto::findOrFail($movimiento->producto_id);
            $producto->stock += $movimiento->cantidad;
            $producto->stock_vendedor += $movimiento->cantidad;

            $producto->actualizarEstadosStock();
            $producto->save();

            $lote = ProductoLotes::findOrFail($movimiento->lote_id);
            $lote->cantidad += $movimiento->cantidad;
            $lote->cantidad_vendedor += $movimiento->cantidad;
            $lote->save();

            // Guardamos la guía antes de eliminar el detalle
            $guia = $movimiento->guia_prestamo;

            // Eliminamos (soft delete)
            $movimiento->delete();

            // Ahora actualizamos el estado
            $guia->actualizarEstadoPorDetalles();

            DB::commit();

            return response()->json([
                'message' => 'Movimiento eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Ocurrió un error inesperado.',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function vaciarGuiaPrestamo($id)
    {
        DB::beginTransaction();

        try {
            $guia_prestamo = GuiaPrestamo::with('detalles')->findOrFail($id);

            foreach ($guia_prestamo->detalles as $detalle) {
                $producto = Producto::findOrFail($detalle->producto_id);
                $producto->stock += $detalle->cantidad;
                $producto->stock_vendedor += $detalle->cantidad;
                $producto->actualizarEstadosStock();
                $producto->save();

                if ($detalle->lote_id) {
                    $lote = ProductoLotes::findOrFail($detalle->lote_id);
                    $lote->cantidad += $detalle->cantidad;
                    $lote->cantidad_vendedor += $detalle->cantidad;
                    $lote->save();
                }

                // Eliminar el detalle (soft delete o hard delete según tu diseño)
                $detalle->delete();
            }

            $guia_prestamo->actualizarEstadoPorDetalles();

            DB::commit();

            return response()->json([
                "guia_prestamo_actualizada" => new GuiaPrestamoResource($guia_prestamo)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Ocurrió un error al eliminar los productos de la guia de prestamo.',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }
}
