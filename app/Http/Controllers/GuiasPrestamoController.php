<?php

namespace App\Http\Controllers;

use App\Http\Resources\GuiaPrestamo\GuiaPrestamoCollection;
use App\Http\Resources\GuiaPrestamo\GuiaPrestamoResource;
use App\Models\Configuration\Laboratorio;
use App\Models\GuiaPrestamo;
use App\Models\Producto;
use App\Models\ProductoAtributtes\ProductoLotes;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class GuiasPrestamoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $guias_prestamo = GuiaPrestamo::where("codigo","like","%".$search."%")
                                ->with('detalles','user_encargado')
                                ->orderBy("id","desc")
                                ->paginate(25);
        return response()->json([
            'total' => $guias_prestamo->total(),
            'guias_prestamo' => new GuiaPrestamoCollection($guias_prestamo),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $crearGuia = $request->input('crear_guia_prestamo', true); // por defecto true

        $guia_prestamo_id = null;
        $codigo = null;
        $movimientos = null;
        $encargado = null;

        if ($crearGuia) {
            $codigo = GuiaPrestamo::generar_codigo();

            if (!$codigo) {
                return response()->json(['error' => 'Código no generado'], 422);
            }

            $guia_prestamo = GuiaPrestamo::create([
                'codigo' => $codigo,
            ]);

            $guia_prestamo_id = $guia_prestamo->id;
        } else {
            $guia_prestamo_id = $request->input('guia_prestamo_id');
            $guia = GuiaPrestamo::findOrFail($guia_prestamo_id);
            $codigo = $guia->codigo;
            $movimientos = $guia->detalles;
            $encargado = $guia->user_encargado_id;
        }

        return response()->json([
            'guia_prestamo_id' => $guia_prestamo_id,
            'codigo' => $codigo,
            'encargado_id' => $encargado,
            'usuarios' => User::where('state', 1)
                ->get()
                ->map(fn($p) => [
                    "id" => $p->id,
                    "name" => $p->name,
                    "name_complete" => $p->name . ' ' . $p->surname,
                    "email" => $p->email,
                ]),
            'laboratorios' => Laboratorio::where('state', 1)
                ->get()
                ->map(fn($p) => [
                    "id" => $p->id,
                    "name" => $p->name,
                ]),
            'productos' => Producto::where('state', 1)
                ->where('stock_vendedor', '>', 0)
                ->with('get_laboratorio')
                ->get()
                ->map(fn($p) => [
                    "id" => $p->id,
                    "sku" => $p->sku,
                    "laboratorio" => $p->get_laboratorio->name,
                    "laboratorio_id" => $p->laboratorio_id,
                    "color_laboratorio" => $p->get_laboratorio->color,
                    "nombre" => $p->nombre,
                    "caracteristicas" => $p->caracteristicas,
                    "nombre_completo" => $p->nombre . ' ' . $p->caracteristicas,
                    "pventa" => $p->pventa ?? '0.0',
                    "stock" => $p->stock_vendedor ?? '0',
                    "imagen" => $p->imagen ?? env("IMAGE_DEFAULT"),
                ]),
            'movimiento' => $movimientos?->map(function ($p) {
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
                 }) ?? collect(),
        ]);
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
        $request->validate([
            'user_encargado_id' => 'required|exists:users,id',
            'comentario' => 'nullable|string',
        ]);

        try {
            $guia_exist = GuiaPrestamo::where('user_encargado_id',$request->user_encargado_id)
                            ->where('state','<>',5)
                            ->exists();

            if($guia_exist){
                return response() -> json([
                    "message" => 403,
                    "message_text" => "el usuario encargado ya tiene una guia de prestamo asignada"
                ],422);
            }

            $guia_prestamo = GuiaPrestamo::findOrFail($id);
            $guia_prestamo->user_encargado_id = $request->user_encargado_id;
            $guia_prestamo->comentario = $request->comentario;
            $guia_prestamo->save();
    
            return response()->json([
                'message' => '200'
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
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

            // Finalmente eliminamos la guía
            $guia_prestamo->delete();

            DB::commit();

            return response()->json([
                'message' => 'Guía de préstamo eliminada correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Ocurrió un error al eliminar la guía.',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductosByLaboratorio(Request $request){
        $request->validate([
            'laboratorio_id' => 'nullable|array',
            'laboratorio_id.*' => 'exists:laboratorio,id',
        ]);

        $query = Producto::query();

        if (!empty($request->laboratorio_id)) {
            $query->whereIn('laboratorio_id', $request->laboratorio_id);
        }

        return response()->json([
            "productos" => $query->with([
                                        'get_laboratorio', 
                                    ])
                                    ->where('state',1)
                                    ->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "sku" => $p->sku,
                    "laboratorio" => $p->get_laboratorio->name,
                    "laboratorio_id" => $p->laboratorio_id,
                    "color_laboratorio" => $p->get_laboratorio->color,
                    "nombre" => $p->nombre,
                    "caracteristicas" => $p->caracteristicas,
                    "nombre_completo" => $p->nombre.' '.$p->caracteristicas,
                    "pventa" => $p->pventa ?? '0.0',
                    "stock" => $p->stock_vendedor ?? '0',
                    "imagen" => $p->imagen ?? env("IMAGE_DEFAULT"),
                ];
            })
        ]);
    } 

    public function getProductDetail(String $id)
    {
        $producto = Producto::with([
            'get_lotes' => function ($query) {
                $query->where('state', 1)
                    ->where('cantidad_vendedor', '>', 0)
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->select('id', 'producto_id', 'lote', 'cantidad_vendedor', 'fecha_vencimiento');
            },
        ])->where('id', $id)->first();

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $hoy = now();

        $lotes = $producto->get_lotes; // Ya están filtrados por el with()

        return response()->json([
            "stock" => $producto->stock_vendedor,
            "pventa" => $producto->pventa,
            "lotes" => $lotes->map(function ($b) use ($hoy) {
                $fechaVencimiento = Carbon::parse($b->fecha_vencimiento);
                $dias_faltantes = $hoy->diffInDays($fechaVencimiento, false);
                return [
                    "id" => $b->id,
                    "dias_faltantes" => $dias_faltantes,
                    "lote" => ($b->lote ?? 'SIN LOTE') . ' - ' . ($b->fecha_vencimiento ? Carbon::parse($b->fecha_vencimiento)->format("d-m-Y") : 'SIN FECHA DE VENCIMIENTO'),
                    "cantidad" => $b->cantidad_vendedor,
                    "fecha_vencimiento_null" => $b->fecha_vencimiento ? false : true
                ];
            }),
        ]);
    }

    public function updateState( Request $request, $id){
        $request->validate([
            'state' => 'required|in:0,1,2,3,4,5' 
        ]);
        $guia_prestamo = GuiaPrestamo::with('detalles')->findOrFail($id);

        if ($msg = $guia_prestamo->puedeCambiarAEstado($request->state)) {
            return response()->json([
                "message" => 403,
                "message_text" => $msg
            ], 422);
        }

        $guia_prestamo->update([
            'state' => $request->state
        ]);

        return response()->json([
            "guia_prestamo_actualizada" => new GuiaPrestamoResource($guia_prestamo)
        ]);
    }
}
