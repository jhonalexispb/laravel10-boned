<?php

namespace App\Http\Controllers;

use App\Models\Configuration\Laboratorio;
use App\Models\GuiaPrestamo;
use App\Models\Producto;
use App\Models\User;
use Carbon\Carbon;
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
            "total" => $guias_prestamo->total(),
            "guias_prestamo" => $guias_prestamo->map(function($b){
                return [
                    "id" => $b->id,
                    "codigo" => $b->codigo,
                    "state" => $b->state,
                    "comentario" => $b->comentario,
                    "created_by" => $b->user_encargado->name,
                    "fecha_entrega" => $b->fecha_entrega?->format("Y-m-d h:i A"),
                    "fecha_gestionado" => $b->fecha_gestionado?->format("Y-m-d h:i A"),
                    "fecha_revisado" => $b->fecha_revisado?->format("Y-m-d h:i A"),
                    "created_at" => $b->created_at->format("Y-m-d h:i A"),
                    'mercaderia' => $b->detalles?->map(function($p){
                        return [
                            "id" => $p->id,
                            "lote" => $p->lote->lote,
                            "fecha_vencimiento" => $p->lote->fecha_vencimiento,
                            "sku" => $p->producto->sku,
                            "nombre" => $p->producto->nombre,
                            "imagen" => $p->producto->imagen,
                            "caracteristicas" => $p->producto->caracteristicas,
                            "cantidad" => $p->cantidad,
                            "stock" => $p->stock,
                            "created_at" => $p->created_at,
                            "created_by" => $p->creador->name
                        ];
                    }) ?? collect(),
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

    public function getRecursosParaCrear()
    {   
        $codigo = GuiaPrestamo::generar_codigo();
        if (!$codigo) {
            return response()->json(['error' => 'Código no generado'], 422);
        }

        return response()->json([
            "codigo" => $codigo,
            "usuarios" => User::where('state', 1)
                ->get()
                ->map(function ($p) {
                    return [
                        "id" => $p->id,
                        "name" => $p->name,
                        "name_complete" => $p->name.' '.$p->surname,
                        "email" => $p->email,
                    ];
                }),

            "laboratorios" => Laboratorio::where('state', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            }),

            "productos" => Producto::where('state', 1)
                                    ->where('stock','>',0)
                                    ->with([
                                        'get_lotes',
                                        'get_laboratorio', 
                                    ])
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
                    "stock" => $p->stock ?? '0',
                    "imagen" => $p->imagen ?? env("IMAGE_DEFAULT"),
                    "state_stock" => $p->state_stock ?? 3,
                    "lotes" => $p->get_lotes,
                ];
            }),
        ]);
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
                                        'get_lotes',
                                        'get_laboratorio', 
                                    ])
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
                    "stock" => $p->stock ?? '0',
                    "imagen" => $p->imagen ?? env("IMAGE_DEFAULT"),
                    "state_stock" => $p->state_stock ?? 3,
                    "lotes" => $p->get_lotes,
                ];
            })
        ]);
    } 

    public function getProductDetail(String $id)
    {
        $producto = Producto::with([
            'get_lotes' => function ($query) {
                $query->where('state', 1)
                    ->where('cantidad', '>', 0)
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->select('id', 'producto_id', 'lote', 'cantidad', 'fecha_vencimiento');
            },
        ])->where('id', $id)->first();

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $hoy = now();

        $lotes = $producto->get_lotes; // Ya están filtrados por el with()

        return response()->json([
            "stock" => $producto->stock,
            "pventa" => $producto->pventa,
            "lotes" => $lotes->map(function ($b) use ($hoy) {
                $fechaVencimiento = Carbon::parse($b->fecha_vencimiento);
                $dias_faltantes = $hoy->diffInDays($fechaVencimiento, false);
                return [
                    "id" => $b->id,
                    "dias_faltantes" => $dias_faltantes,
                    "lote" => ($b->lote ?? 'SIN LOTE') . ' - ' . ($b->fecha_vencimiento ? Carbon::parse($b->fecha_vencimiento)->format("d-m-Y") : 'SIN FECHA DE VENCIMIENTO'),
                    "cantidad" => $b->cantidad
                ];
            }),
        ]);
    }
}
