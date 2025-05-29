<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrdenVenta\OrdenVentaCollection;
use App\Models\ClientesSucursales;
use App\Models\Configuration\Proveedor;
use App\Models\OrdenCompraAtributtes\FormaPagoOrdenesCompra;
use App\Models\OrdenCompraAtributtes\TipoComprobantePagoCompra;
use App\Models\OrdenVenta;
use App\Models\OrdenVentaAtributtes\TransportesOrdenVenta;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdenVentaController extends Controller
{
    public function getRecursosParaCrear()
    {   
        $userId = auth()->id();
        $codigo = OrdenVenta::generarCodigo($userId);
        if (!$codigo) {
            return response()->json(['error' => 'Código no generado'], 422);
        }

        return response()->json([
            "codigo" => $codigo,
            "proveedores" => Proveedor::where('state', 1)
            ->with([
                'proveedorLaboratorios.laboratorios:id,name,color',
            ])
            ->get()
            ->map(function ($p) {
                return [
                    "id" => $p->id,
                    "razonSocial" => $p->razonSocial,
                    "name" => $p->name,
                    "email" => $p->email,
                    "representante" => $p->idrepresentante ? $p->representante->name : 'Sin representante',
                    "representante_celular" => $p->idrepresentante ? $p->representante->celular : '',
                    "laboratorios" => $p->proveedorLaboratorios->map(function ($pl) {
                        return [
                            "id" => $pl->id,
                            "laboratorio_id" => $pl->laboratorios->id,
                            "color" => $pl->laboratorios->color,
                            "name" => $pl->laboratorios->name,
                            "name_margen" => $pl->laboratorios->name." (".$pl->margen_minimo."%)",
                            "margen_minimo" => $pl->margen_minimo,
                        ];
                    }),
                ];
            }),

            "forma_pago" => FormaPagoOrdenesCompra::where('state', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            }),

            "tipo_comprobante" => TipoComprobantePagoCompra::where('state', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            }),
        ]);
    }
    //necasitamoas envai8r la mercaderia que no este vencida, y que no este para devolucion, si no tiene devolucion se tiene que avisar, habra una seccion en donde se filtre los productos que esten a 6 y 3 meses de vencery tiene que ser configurable 
    public function getProductosByLaboratorio(Request $request){
        $request->validate([
            'laboratorio_id' => 'nullable|array',
            'laboratorio_id.*' => 'exists:laboratorio,id',
            'orden_productos_ids' => 'nullable|array',
            'orden_productos_ids.*' => 'integer',
        ]);

        $laboratorioIds = $request->laboratorio_id ?? [];
        $ordenProductosIds = $request->orden_productos_ids ?? [];

        $queryBase = Producto::query();

        if (!empty($laboratorioIds)) {
            $queryBase->whereIn('laboratorio_id', $laboratorioIds);
        }

        // Productos con stock > 0
        $productosConStock = (clone $queryBase)
            ->with([
                'get_laboratorio', 
                'get_escalas' => function ($q) {
                    $q->where('state', 1);
                }
            ])
            ->where('state', 1)
            ->where('stock_vendedor', '>', 0)
            ->get();

        // Productos en orden con stock = 0 (que no estén en la lista anterior)
        $productosEnOrdenSinStock = collect();

        if (!empty($ordenProductosIds)) {
            $productosEnOrdenSinStock = (clone $queryBase)
                ->with([
                    'get_laboratorio', 
                    'get_escalas' => function ($q) {
                        $q->where('state', 1);
                    }
                ])
                ->where('state', 1)
                ->where('stock_vendedor', '<=', 0)
                ->whereIn('id', $ordenProductosIds)
                ->get();
        }

        // Unir ambos resultados, evitando duplicados (por id)
        $productos = $productosConStock->keyBy('id')->union($productosEnOrdenSinStock->keyBy('id'))->values();

        return response()->json([
            "productos" => $productos->map(function ($p) {
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
                    "maneja_escalas" => $p->maneja_escalas && $p->get_escalas->where('state', 1)->count() > 0,
                    "escalas" => $p->maneja_escalas ? $p->get_escalas->map(function ($e) {
                        return [
                            "precio" => $e->precio,
                            "cantidad" => $e->cantidad,
                        ];
                    }) : [],
                ];
            }) ?? collect(),
        ]);
    } 
    
    public function getProductDetail(String $id)
    {
        $producto = Producto::with([
            'get_lotes' => function ($query) {
                $query->where('cantidad', '>', 0)
                    ->orderBy('fecha_vencimiento','asc')
                    ->select('lote', 'cantidad', 'fecha_vencimiento');
            },
            'get_escalas' => function ($query) {
                $query->where('state', 1)
                    ->orderBy('cantidad','asc')
                    ->select('cantidad', 'precio');
            }
        ])->where('id', $id)->first();

        // Si no existe, retornar error
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $hoy = now();

        $escalas = $producto->get_escalas()->where('state', 1)->orderBy('cantidad','asc')->get();

        $lotes = $producto->get_lotes()->where('cantidad', '>', 0)->orderBy('fecha_vencimiento','asc')->get();

        return response()->json([
            "stock" => $producto->stock_vendedor,
            "pventa" => $producto->pventa,
            "escalas" => $escalas->map(function($b){
                return [
                    "id" => $b->id,
                    "cantidad" => $b->cantidad,
                    "precio" => $b->precio,
                ];
            }),
            "lotes" => $lotes->map(function ($b) use ($hoy) {
                $fechaVencimiento = Carbon::parse($b->fecha_vencimiento);
                $dias_faltantes = $hoy->diffInDays($fechaVencimiento, false);
                return [
                    "id" => $b->id,
                    "dias_faltantes" => $dias_faltantes,
                    "lote" => $b->lote ?? 'SIN LOTE',
                    "fecha_vencimiento" => ' FV: ' . $b->fecha_vencimiento ? Carbon::parse($b->fecha_vencimiento)->format("d-m-Y") : 'SIN FECHA DE VENCIMIENTO',
                    "cantidad" => $b->cantidad_vendedor,
                    "fecha_vencimiento_null" => $b->fecha_vencimiento ? false : true
                ];
            }),
        ]);
    } 



    public function index(Request $request)
    {
        $search = $request->get('search');

        $ordenes_venta = OrdenVenta::where("codigo","like","%".$search."%")
                                ->with([
                                    'detalles',
                                    'cliente.ruc',
                                    'cliente.getNameDistrito.provincia.departamento',
                                    'cliente.getEstadoDigemid',
                                    'comprobante',
                                    'transporte'
                                ])
                                ->orderBy("id","desc")
                                ->paginate(25);
        return response()->json([
            'total' => $ordenes_venta->total(),
            'ordenes_venta' => new OrdenVentaCollection($ordenes_venta),
        ]);
    }

    public function store(Request $request)
    {
        $crearOrdenVenta = $request->input('crear_orden_venta', true); // por defecto true
        $userId = auth()->id();
        $orden_venta_id = null;
        $codigo = null;
        $movimientos = null;
        $cliente = null;

        if ($crearOrdenVenta) {
            $codigo = OrdenVenta::generarCodigo($userId);

            if (!$codigo) {
                return response()->json(['error' => 'Código no generado'], 422);
            }

            $orden = OrdenVenta::create([
                'codigo' => $codigo,
            ]);

            $orden_venta_id = $orden->id;
        } else {
            $orden_venta_id = $request->input('orden_venta_id');
            $orden_venta = OrdenVenta::with([
                'detalles.producto.get_laboratorio',
                'detalles.lote'
            ])->findOrFail($orden_venta_id);
            $codigo = $orden_venta->codigo;
            $movimientos = $orden_venta->detalles;
            $cliente = $orden_venta->cliente_id;
        }

        $productosEnOrden = collect();
        if (!$crearOrdenVenta && isset($orden_venta)) {
            $productosEnOrden = $orden_venta->detalles->pluck('producto_id');
        }

        $productos = Producto::with([
            'get_laboratorio', 
            'get_escalas' => function ($q) {
                $q->where('state', 1);
            }
        ])
        ->where('state', 1)
        ->where(function ($q) use ($productosEnOrden) {
            $q->where('stock_vendedor', '>', 0);
            if ($productosEnOrden->isNotEmpty()) {
                $q->orWhereIn('id', $productosEnOrden);
            }
        })
        ->get()->map(function ($p) {
            return [
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
                "maneja_escalas" => $p->maneja_escalas && $p->get_escalas->where('state', 1)->count() > 0,
                "escalas" => $p->maneja_escalas ? $p->get_escalas->map(function ($e) {
                    return [
                        "precio" => $e->precio,
                        "cantidad" => $e->cantidad,
                    ];
                }) : [],
            ];
        });

        return response()->json([
            'orden_venta_id' => $orden_venta_id,
            'codigo' => $codigo,
            'cliente_id' => $cliente,
            'transportes' =>  TransportesOrdenVenta::where('state',1)
                                ->get()->map(fn($p) => [
                "id" => $p->id,
                "name" => $p->name,
            ]),

            'clientes' => ClientesSucursales::with([
                'ruc',
                'getNameDistrito.provincia.departamento',
                'getEstadoDigemid',
                'getDirecciones.distrito.provincia.departamento'
            ])
            ->where('state', 1)
            ->get()
            ->map(function ($p) {
                $departamento = strtoupper(trim($p->getNameDistrito->provincia->departamento->name));
                $provincia = strtoupper(trim($p->getNameDistrito->provincia->name));
                $distrito = strtoupper(trim($p->getNameDistrito->name));

                $zona = ($departamento === 'AREQUIPA' && $provincia === 'AREQUIPA') ? 0 : 1;

                return [
                    "id" => $p->id,
                    "ruc" => $p->ruc->ruc,
                    "razon_social" => $p->ruc->razonSocial,
                    "nombre_comercial" => $p->nombre_comercial,
                    "direccion" => $p->direccion,
                    "distrito" => $departamento . '/' . $provincia . '/' . $distrito,
                    "zona_reparto" => $zona,
                    "deuda" => $p->deuda,
                    "estado_digemid" => $p->getEstadoDigemid->nombre,
                    "type_documentos" => $p->getEstadoDigemid->comprobantesPermitidos->map(fn($doc) => [
                        'id' => $doc->id,
                        'codigo' => $doc->codigo,
                        'name' => $doc->name,
                    ]),
                    "forma_pago" => $p->formaPago,
                    "lugares_entrega" => $p->getDirecciones?->map(fn($l) => [
                        'id' => $l->id,
                        "address" => $l->distrito
                        ? strtoupper(trim($l->address)) . ' - ' . strtoupper(trim($l->distrito->provincia->departamento->name)) . '/' .
                        strtoupper(trim($l->distrito->provincia->name)) . '/' .
                        strtoupper(trim($l->distrito->name)) 
                        : strtoupper(trim($l->address)),
                        'latitud' => $l->latitud,
                        'longitud' => $l->longitud,
                        'imagen' => $l->imagen,
                    ]),
                ];
            }),

            'productos' => $productos,

            'movimiento' => $movimientos?->map(function ($p) {
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
                        "total" => $p->total,
                     ];
                 }) ?? collect(),
        ]);
    }
}
