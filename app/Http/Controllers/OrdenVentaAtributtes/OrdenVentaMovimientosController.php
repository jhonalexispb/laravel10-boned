<?php

namespace App\Http\Controllers\OrdenVentaAtributtes;

use App\Http\Controllers\Controller;
use App\Models\OrdenVenta;
use App\Models\OrdenVentaAtributtes\OrdenVentaDetalle;
use App\Models\Producto;
use App\Models\ProductoAtributtes\ProductoLotes;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class OrdenVentaMovimientosController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'orden_venta_id' => 'required|exists:orden_venta,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $producto = Producto::where('id', $request->producto_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($producto->stock_vendedor < $request->cantidad) {
                return response()->json([
                    "message" => 403,
                    "message_text" => "No hay suficiente stock disponible del producto."
                ], 422);
            }

            $movimientos = $this->registrarMovimientosOrdenVenta($request->orden_venta_id, $producto, $request->cantidad);

            $producto->stock_vendedor -= $request->cantidad;
            $producto->actualizarEstadosStock();
            $producto->save();

            $totalOrdenVenta = collect($movimientos)->sum(fn($m) => $m->total);
            $orden = OrdenVenta::findOrFail($request->orden_venta_id);
            $orden->total += $totalOrdenVenta;
            $orden->save();

            DB::commit();

            $detalles = OrdenVentaDetalle::with(['producto.get_laboratorio', 'lote'])
                ->whereIn('id', collect($movimientos)->pluck('id'))
                ->get();

            return response()->json([
                'movimiento' => $detalles->map(fn($p) => $this->formatearDetalle($p))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Ocurrió un error inesperado.',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function editarCantidadOrdenVenta(Request $request)
    {
        DB::beginTransaction();
        try {
            $producto_id = $request->producto_id;
            $order_venta_id = $request->orden_venta_id;
            $nuevaCantidad = $request->cantidad;

            $producto = Producto::findOrFail($producto_id);
            $stockActualEnVenta = OrdenVentaDetalle::where('producto_id', $producto_id)
                ->where('order_venta_id', $order_venta_id)
                ->sum('cantidad');

            $stockDisponible = $producto->stock_vendedor + $stockActualEnVenta;

            if ($stockDisponible < $nuevaCantidad) {
                $cantidadMaxima = $producto->stock_vendedor;
                return response()->json([
                    "message" => 403,
                    "message_text" => $cantidadMaxima == 0
                        ? "No hay stock disponible del producto."
                        : "Solo puedes aumentar hasta $cantidadMaxima unidades."
                ], 422);
            }

            // Revertir movimientos actuales
            $movimientos = OrdenVentaDetalle::where('producto_id', $producto_id)
                ->where('order_venta_id', $order_venta_id)
                ->get();

            foreach ($movimientos as $mov) {
                $producto->stock_vendedor += $mov->cantidad;
                $lote = ProductoLotes::findOrFail($mov->lote_id);
                $lote->cantidad_vendedor += $mov->cantidad;
                $lote->save();
                $mov->delete();
            }

            // Reasignar lotes con la nueva cantidad
            $movimientos_creados = $this->registrarMovimientosOrdenVenta($order_venta_id, $producto, $nuevaCantidad);

            $producto->stock_vendedor -= $nuevaCantidad;
            $producto->actualizarEstadosStock();
            $producto->save();

            $orden = OrdenVenta::findOrFail($order_venta_id);
            $orden->total = OrdenVentaDetalle::where('order_venta_id', $order_venta_id)->sum('total');
            $orden->save();

            DB::commit();

            $detalles = OrdenVentaDetalle::with(['producto.get_laboratorio', 'lote'])
                ->whereIn('id', collect($movimientos_creados)->pluck('id'))
                ->get();

            return response()->json([
                'movimiento' => $detalles->map(fn($p) => $this->formatearDetalle($p))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al editar cantidad.',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarPorProducto($producto_id, $orden_venta_id)
    {
        DB::beginTransaction();
        try {
            $movimientos = OrdenVentaDetalle::where('producto_id', $producto_id)
                ->where('order_venta_id', $orden_venta_id)
                ->get();
            $totalMovimientos = 0;
            foreach ($movimientos as $movimiento) {
                $producto = Producto::findOrFail($movimiento->producto_id);
                $producto->stock_vendedor += $movimiento->cantidad;
                $producto->actualizarEstadosStock();
                $producto->save();

                $lote = ProductoLotes::findOrFail($movimiento->lote_id);
                $lote->cantidad_vendedor += $movimiento->cantidad;
                $lote->save();
                $totalMovimientos += $movimiento->total;
                $movimiento->delete(); // Soft delete
            }

            $orden = OrdenVenta::findOrFail($orden_venta_id);
            $orden->total -= $totalMovimientos;

            // Evitar que el total sea negativo
            if ($orden->total < 0) {
                $orden->total = 0;
            }

            $orden->save();

            DB::commit();

            return response()->json([
                'message' => 'Todos los movimientos del producto fueron eliminados correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Ocurrió un error al eliminar los movimientos.',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    private function calcularPrecioYPromocion(Producto $producto, int $cantidad): array
    {
        $pventa = $producto->pventa;
        $tipo_promocion = null;

        if ($producto->maneja_escalas) {
            $escala = $producto->get_escalas()
                ->where('state', 1)
                ->orderBy('cantidad', 'asc')
                ->get()
                ->filter(fn($e) => $cantidad >= $e->cantidad)
                ->last();

            if ($escala) {
                $pventa = $escala->precio;
                $tipo_promocion = 1;
            }
        }

        return [
            'pventa' => $pventa,
            'tipo_promocion' => $tipo_promocion
        ];
    }

    private function registrarMovimientosOrdenVenta(int $orden_venta_id, Producto $producto, int $cantidad): array
    {
        $cantidadRestante = $cantidad;
        $movimientos = [];

        $precios = $this->calcularPrecioYPromocion($producto, $cantidad);
        $pventa = $precios['pventa'];
        $tipo_promocion = $precios['tipo_promocion'];

        $lotes = ProductoLotes::where('producto_id', $producto->id)
            ->where('cantidad_vendedor', '>', 0)
            ->where('state', 1)
            ->orderByRaw("
                CASE WHEN fecha_vencimiento IS NULL THEN 1 ELSE 0 END,
                fecha_vencimiento ASC,
                created_at ASC
            ")
            ->lockForUpdate()
            ->get();

        foreach ($lotes as $lote) {
            if ($cantidadRestante <= 0) break;

            $extraer = min($cantidadRestante, $lote->cantidad_vendedor);

            $movimientos[] = OrdenVentaDetalle::create([
                'order_venta_id' => $orden_venta_id,
                'producto_id' => $producto->id,
                'unit_id' => 1,
                'lote_id' => $lote->id,
                'cantidad' => $extraer,
                'tipo_promocion' => $tipo_promocion,
                'pventa' => $pventa,
                'total' => $pventa * $extraer,
            ]);

            $lote->cantidad_vendedor -= $extraer;
            $lote->save();

            $cantidadRestante -= $extraer;
        }

        if ($cantidadRestante > 0) {
            throw new \Exception("No hay suficiente stock distribuido en los lotes");
        }

        return $movimientos;
    }

    private function formatearDetalle($p): array
    {
        return [
            "id" => $p->id,
            "producto_id" => $p->producto_id,
            "sku" => $p->producto->sku,
            "laboratorio" => $p->producto->get_laboratorio->name,
            "color_laboratorio" => $p->producto->get_laboratorio->color,
            "nombre" => $p->producto->nombre,
            "caracteristicas" => $p->producto->caracteristicas,
            "pventa" => $p->pventa ?? '0.0',
            "imagen" => $p->producto->imagen ?? env("IMAGE_DEFAULT"),
            "lote" => $p->lote->lote ?? 'SIN LOTE',
            "fecha_vencimiento" => $p->lote->fecha_vencimiento ? Carbon::parse($p->lote->fecha_vencimiento)->format('d-m-Y') : 'SIN FV',
            "cantidad" => $p->cantidad,
            "tipo_promocion" => $p->tipo_promocion,
            "total" => $p->total,
        ];
    }
}
