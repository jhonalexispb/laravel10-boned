<?php

namespace App\Http\Controllers;

use App\Models\Configuration\Proveedor;
use App\Models\Configuration\TypeComprobante;
use App\Models\Configuration\TypeComprobanteSerie;
use App\Models\GuiaDevolucion;
use App\Models\GuiaDevolucionAtributtes\GuiaDevolucionDetail;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraAtributtes\FormaPagoOrdenesCompra;
use App\Models\OrdenCompraAtributtes\NDocumentoOrdenCompra;
use App\Models\OrdenCompraAtributtes\OrdenCompraCuotas;
use App\Models\OrdenCompraAtributtes\OrdenCompraDetails;
use App\Models\OrdenCompraAtributtes\TipoComprobantePagoCompra;
use App\Models\OrdenCompraAtributtes\OrderCompraDetailsGestionado;
use App\Models\Producto;
use App\Models\ProductoAtributtes\Afectacion_igv;
use App\Models\ProductoAtributtes\HistorialPrecioCompra;
use App\Models\ProductoAtributtes\HistorialPrecioVenta;
use App\Models\ProductoAtributtes\ProductoLotes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class OrdenCompraController extends Controller
{
    public function getRecursosParaCrear()
    {   
        $codigo = OrdenCompra::generarCodigo();
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

    public function getRecursosParaEditar()
    {   
        return response()->json([
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

    public function getProductosByLaboratorio(Request $request){
        $request->validate([
            'laboratorio_id' => 'required|array',
            'laboratorio_id.*' => 'exists:laboratorio,id',
        ]);

        return response()->json([
            "productos" => Producto::whereIn('laboratorio_id', $request->laboratorio_id)
                                    ->with([
                                        'get_escalas:id,producto_id,cantidad,precio,state',
                                        'get_lotes',
                                        'get_laboratorio', 
                                        'get_lineaFarmaceutica',
                                        'get_fabricante',
                                        'get_presentacion',
                                        'get_principios_activos'
                                    ])
                                    ->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "sku" => $p->sku,
                    "tproducto" => $p->tproducto,
                    "laboratorio" => $p->get_laboratorio->name,
                    "laboratorio_id" => $p->laboratorio_id,
                    "color_laboratorio" => $p->get_laboratorio->color,
                    "nombre" => $p->nombre,
                    "caracteristicas" => $p->caracteristicas,
                    "nombre_completo" => $p->nombre.' '.$p->caracteristicas,
                    "pventa" => $p->pventa ?? '0.0',
                    "stock" => $p->stock ?? '0',
                    "imagen" => $p->imagen ?? env("IMAGE_DEFAULT"),
                    "linea_farmaceutica" => $p->get_lineaFarmaceutica->nombre,
                    "fabricante" => $p->get_fabricante->nombre,
                    "presentacion" => $p->presentacion_id ? $p->get_presentacion->name : 'Sin presentación',
                    "principios_activos" => $p->get_principios_activos->pluck('id')->toArray(),
                    "maneja_escalas" => $p->maneja_escalas,
                    "escalas" => $p->get_escalas,
                    "lotes" => $p->get_lotes,
                    "state_stock" => $p->state_stock ?? 3,
                ];
            })
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
            "stock" => $producto->stock,
            "pventa" => $producto->pventa,
            "escalas" => $escalas->map(function($b){
                return [
                    "id" => $b->id,
                    "cantidad" => $b->cantidad,
                    "precio" => $b->precio,
                    "state" => $b->state,
                ];
            }),
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
        ]);
    }
    
    public function getCuotasPendientes(){
        $cuotas = OrdenCompraCuotas::where('state',0)->get();

        return response()->json([
            "cuotas_pendientes" => $cuotas->map(function($d){
                return [
                    "title" => $d->title,
                    "start" => Carbon::parse($d->start)->format("Y-m-d"),
                    "startStr" => Carbon::parse($d->start)->format("Y-m-d"),
                    "className" => 'border-danger bg-danger text-black',
                    "extendedProps" => [
                        "amount" => $d->amount,
                        "saldo" => $d->saldo,
                        "notes" => $d->notes,
                        "reminder" => $d->reminder,
                        "dias_reminder" => $d->dias_reminder,
                    ],
                    "state" => $d->state,
                    "numero_unico" => $d->numero_unico,
                    "fecha_cancelado" => $d->fecha_cancelado,
                ];
            })
        ]);
    }

    public function getCuotasPendientesEditarOrdenCompra($id){
        $cuotas = OrdenCompraCuotas::where('state',0)
                                    ->where('orden_compra_id','<>',$id)
                                    ->get();
        $ordenCompraCuotas = OrdenCompraCuotas::where('orden_compra_id',$id)->get();

        return response()->json([
            "cuotas_pendientes" => $cuotas->map(function($d){
                return [
                    "title" => $d->title,
                    "start" => Carbon::parse($d->start)->format("Y-m-d"),
                    "startStr" => Carbon::parse($d->start)->format("Y-m-d"),
                    "className" => 'border-danger bg-danger text-black',
                    "extendedProps" => [
                        "amount" => $d->amount,
                        "saldo" => $d->saldo,
                        "notes" => $d->notes,
                        "reminder" => $d->reminder,
                        "dias_reminder" => $d->dias_reminder,
                    ],
                    "state" => $d->state,
                    "numero_unico" => $d->numero_unico,
                    "fecha_cancelado" => $d->fecha_cancelado,
                ];
            }),
            "cuotas_orden_compra" => $ordenCompraCuotas->map(function($d){
                return [
                    "id" => $d->id,
                    "title" => $d->title,
                    "start" => Carbon::parse($d->start)->format("Y-m-d"),
                    "startStr" => Carbon::parse($d->start)->format("Y-m-d"),
                    "className" => 'border-primary bg-primary text-black',
                    "extendedProps" => [
                        "amount" => $d->amount,
                        "saldo" => $d->saldo,
                        "notes" => $d->notes,
                        "reminder" => $d->reminder,
                        "dias_reminder" => $d->dias_reminder,
                    ],
                    "state" => $d->state,
                    "numero_unico" => $d->numero_unico,
                    "fecha_cancelado" => $d->fecha_cancelado,
                ];
            })
        ]);
    }

    public function getProductosOrdenCompra($id){
        $orderCompra = OrdenCompra::with(['comprobante'])->findOrFail($id);
        $ordenCompra_detail = OrdenCompraDetails::with([
            'getProducto'
        ])->where('orden_compra_id',$id)
            ->where('state',0)
            ->get();
        $afectacion_igv = Afectacion_igv::all();
        return response()->json([
            'order_compra' => [
                'id' => $orderCompra->id,
                'codigo' => $orderCompra->codigo,
                'proveedor' => $orderCompra->getProveedor->name,
                'comprobante' => $orderCompra->getTypeComprobante->name,
                'comprobante_id' => $orderCompra->type_comprobante_compra_id
            ],
            'order_compra_detail' => $ordenCompra_detail->map(function($d){
                return [
                    "cantidad" => $d->cantidad_pendiente,
                    "caracteristicas" => $d->getProducto->caracteristicas ?? '',
                    "color_laboratorio" => $d->getProducto->get_laboratorio->color,
                    "condicion_vencimiento" => $d->condicion_vencimiento,
                    "fecha_vencimiento" => $d->fecha_vencimiento,
                    "ganancia" => number_format($d->cantidad * ($d->p_venta - $d->p_compra), 2, '.', ''),
                    "laboratorio" => $d->getProducto->get_laboratorio->name,
                    "margen_minimo" => $d->margen_ganancia,
                    "nombre" => $d->getProducto->nombre ?? 'Sin nombre',
                    "pcompra" => $d->p_compra,
                    "producto_id" => $d->producto_id,
                    "afectacion_producto_id" => $d->getProducto->afectacion_igv_id,
                    "pventa" => $d->p_venta,
                    "sku" => $d->getProducto->sku,
                    "imagen" => $d->getProducto->imagen ?? env("IMAGE_DEFAULT"),
                    "total" => $d->total,
                    "bonificacion" => (bool) $d->bonificacion,
                ];
            }),

            'afectacion_igv' => $afectacion_igv->map(function($d){
                return [
                    "id" => $d->id,
                    "descripcion" => $d->descripcion,
                    "detalle" => $d->detalle,
                    "codigo" => $d->codigo,
                ];
            }),

            'comprobantes' => $orderCompra->comprobante?->map(function($c){
                return [
                    "id" => $c->id,
                    "serie" => $c->serie,
                    "n_documento" => $c->n_documento,
                    "igv_state" => $c->igv_state,
                    "fecha_emision" => Carbon::parse($c->fecha_emision)->format("Y-m-d"),
                    "modo_pago" => $c->modo_pago,
                ];
            }) ?? collect(),
        ]);
    }

    public function getProductosOrdenCompraToWatch($id){
        $orderCompra = OrdenCompra::with(['comprobante'])->findOrFail($id);
        $ordenCompra_detail = OrdenCompraDetails::with([
            'getProducto'
        ])->where('orden_compra_id',$id)
            ->get();
        $afectacion_igv = Afectacion_igv::all();
        return response()->json([
            'order_compra' => [
                'id' => $orderCompra->id,
                'codigo' => $orderCompra->codigo,
                'proveedor' => $orderCompra->getProveedor->name,
                'comprobante' => $orderCompra->getTypeComprobante->name,
                'comprobante_id' => $orderCompra->type_comprobante_compra_id
            ],
            'order_compra_detail' => $ordenCompra_detail->map(function($d){
                return [
                    "cantidad" => $d->cantidad,
                    "caracteristicas" => $d->getProducto->caracteristicas ?? '',
                    "color_laboratorio" => $d->getProducto->get_laboratorio->color,
                    "condicion_vencimiento" => $d->condicion_vencimiento,
                    "fecha_vencimiento" => $d->fecha_vencimiento,
                    "ganancia" => number_format($d->cantidad * ($d->p_venta - $d->p_compra), 2, '.', ''),
                    "laboratorio" => $d->getProducto->get_laboratorio->name,
                    "margen_minimo" => $d->margen_ganancia,
                    "nombre" => $d->getProducto->nombre ?? 'Sin nombre',
                    "pcompra" => $d->p_compra,
                    "producto_id" => $d->producto_id,
                    "afectacion_producto_id" => $d->getProducto->afectacion_igv_id,
                    "pventa" => $d->p_venta,
                    "sku" => $d->getProducto->sku,
                    "imagen" => $d->getProducto->imagen ?? env("IMAGE_DEFAULT"),
                    "total" => $d->total,
                    "bonificacion" => (bool) $d->bonificacion,
                ];
            }),

            'afectacion_igv' => $afectacion_igv->map(function($d){
                return [
                    "id" => $d->id,
                    "descripcion" => $d->descripcion,
                    "detalle" => $d->detalle,
                    "codigo" => $d->codigo,
                ];
            }),

            'comprobantes' => $orderCompra->comprobante?->map(function($c){
                return [
                    "id" => $c->id,
                    "serie" => $c->serie,
                    "n_documento" => $c->n_documento,
                    "igv_state" => $c->igv_state,
                    "fecha_emision" => Carbon::parse($c->fecha_emision)->format("Y-m-d"),
                    "modo_pago" => $c->modo_pago == 1 ? 'CONTADO' : 'CRÉDITO',
                ];
            }) ?? collect(),
        ]);
    }

    public function index(Request $request)  {
        $search = $request->get('search');
        $order_compra_list = OrdenCompra::with(['getProveedor:id,name', 'getTypeComprobante:id,name', 'getFormaPago:id,name','getCuotas','comprobante','guia_devolucion'])
                    ->withCount(['getCuotas as cuotas_pendientes' => function($query) {
                        $query->where('state', 0);
                    }])
                    ->where("codigo", "like", "%{$search}%")
                    ->orderBy("id","desc")
                    ->paginate(25);
                                
        return response()->json([
            'total' => $order_compra_list->total(),
            'order_compra_list' => $order_compra_list->map(function($d){
                return [
                    "id" => $d->id,
                    "codigo" => $d->codigo,
                    "proveedor" => $d->getProveedor->name,
                    "type_comprobante" => $d->getTypeComprobante->name,
                    "forma_pago" => $d->getFormaPago->name,
                    "descripcion" => $d->descripcion,
                    "total" => $d->total,
                    "igv" => $d->igv,
                    "state" => $d->state,
                    "created_at" => $d->created_at->format("Y-m-d h:i A"),
                    "cuotas_pendientes" => $d->cuotas_pendientes,
                    "cuotas" => $d->getCuotas->map(function($c){
                        return [
                            "monto" => $c->amount,
                            "fecha_pago" => $c->start,
                            "comentario" => $c->notes,
                            "state" => $c->state,
                            "numero_unico" => $c->numero_unico,
                            "fecha_cancelado" => $c->fecha_cancelado,
                        ];
                    }),
                    'comprobantes' => $d->comprobante?->map(function($c){
                        return [
                            "id" => $c->id,
                            "serie" => $c->serie,
                            "n_documento" => $c->n_documento,
                            "igv_state" => $c->igv_state,
                            "fecha_emision" => Carbon::parse($c->fecha_emision)->format("Y-m-d"),
                            "modo_pago" => $c->modo_pago == 1 ? 'CONTADO' : 'CRÉDITO',
                        ];
                    }) ?? collect(),
                    'guias_devolucion' => $d->guia_devolucion?->map(function($c){
                        return [
                            "id" => $c->id,
                            "serie" => $c->typeComprobanteSerie->serie,
                            "correlativo" => $c->correlativo,
                            "created_at" => Carbon::parse($c->created_at)->format("Y-m-d"),
                            "state" => $c->state = 1 ? 'SOLVENTADO' : 'SOLICITADO',
                            "descripcion" => $c->descripcion,
                            "date_justificado" => $c->date_justificado ? Carbon::parse($c->date_justificado)->format("Y-m-d") : '',
                        ];
                    }) ?? collect(),
                ];
            })
        ]);
    }

    public function show($id){
        $ordenCompra = OrdenCompra::findOrFail($id);
        $ordenCompra_detail = OrdenCompraDetails::where('orden_compra_id',$id)->get();
        return response()->json([
            'order_compra' => [
                "id" => $ordenCompra->id,
                "codigo" => $ordenCompra->codigo,
                "descripcion" => $ordenCompra->descripcion,
                "fecha_ingreso" => $ordenCompra->fecha_ingreso,
                "forma_pago_id" => $ordenCompra->forma_pago_id,
                "igv" => $ordenCompra->igv_state,
                "impuesto" => $ordenCompra->igv,
                "mensaje_notificacion" => $ordenCompra->mensaje_notificacion,
                "notificacion" => $ordenCompra->notificacion,
                "proveedor" => $ordenCompra->proveedor_id,
                "proveedor_name" => $ordenCompra->getProveedor->name,
                "sub_total" => $ordenCompra->importe,
                "total" => $ordenCompra->total,
                "type_comprobante_compra_id" => $ordenCompra->type_comprobante_compra_id,
            ],
            'order_compra_detail' => $ordenCompra_detail->map(function($d){
                return [
                    "id" => $d->id,
                    "cantidad" => $d->cantidad,
                    "caracteristicas" => $d->getProducto->caracteristicas ?? '',
                    "color_laboratorio" => $d->getProducto->get_laboratorio->color,
                    "condicion_vencimiento" => $d->condicion_vencimiento,
                    "fecha_vencimiento" => $d->fecha_vencimiento,
                    "ganancia" => number_format($d->cantidad * ($d->p_venta - $d->p_compra), 2, '.', ''),
                    "laboratorio" => $d->getProducto->get_laboratorio->name,
                    "margen_minimo" => $d->margen_ganancia,
                    "nombre" => $d->getProducto->nombre ?? 'Sin nombre',
                    "pcompra" => $d->p_compra,
                    "producto_id" => $d->producto_id,
                    "pventa" => $d->p_venta,
                    "sku" => $d->getProducto->sku,
                    "total" => $d->total,
                    "bonificacion" => (bool) $d->bonificacion,
                ];
            })
        ]);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'compra_form.proveedor_id' => 'required|integer|exists:proveedor,id',
            'compra_form.type_comprobante_compra_id' => 'required|integer|exists:type_comprobante_pago_compra,id',
            'compra_form.forma_pago_id' => 'required|integer|exists:forma_pago_ordenes_compra,id',
            'compra_form.igv' => 'required|boolean',
            'compra_form.total' => 'required|numeric',
            'compra_form.impuesto' => 'required|numeric',
            'compra_form.sub_total' => 'required|numeric',
            'compra_form.notificacion' => 'required|boolean',
            'compra_form.mensaje_notificacion' => 'nullable',
            'compra_form.fecha_ingreso' => 'nullable|date',
            'compra_form.descripcion' => 'nullable',

            'compra_details' => 'required|array',
            'compra_details.*.producto_id' => 'required|integer|exists:productos,id',
            'compra_details.*.cantidad' => 'required|integer|min:1',
            'compra_details.*.condicion_vencimiento' => 'required|boolean',
            'compra_details.*.fecha_vencimiento' => 'required|date',
            'compra_details.*.margen_ganancia' => 'required|numeric|min:0',
            'compra_details.*.pcompra' => 'required|numeric|min:0',
            'compra_details.*.pventa' => 'required|numeric|min:0',
            'compra_details.*.total' => 'required|numeric|min:0',
            'compra_details.*.bonificacion' => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();
            
            $codigo = OrdenCompra::generarCodigo();
            // Crear la orden de compra
            $ordenCompra = OrdenCompra::create([
                "codigo" => $codigo,
                "proveedor_id" => $validatedData['compra_form']['proveedor_id'],
                "type_comprobante_compra_id" => $validatedData['compra_form']['type_comprobante_compra_id'],
                "forma_pago_id" => $validatedData['compra_form']['forma_pago_id'],
                "igv_state" => $validatedData['compra_form']['igv'],
                "descripcion" => $validatedData['compra_form']['descripcion'],
                "notificacion" => $validatedData['compra_form']['notificacion'],
                "mensaje_notificacion" => $validatedData['compra_form']['mensaje_notificacion'],
                "importe" => $validatedData['compra_form']['sub_total'],
                "igv" => $validatedData['compra_form']['impuesto'],
                "total" => $validatedData['compra_form']['total'],
                "fecha_ingreso" => $validatedData['compra_form']['fecha_ingreso'],
            ]);
    
            // Guardar los detalles de la compra
            foreach ($validatedData['compra_details'] as $detalle) {
                OrdenCompraDetails::create([
                    'orden_compra_id' => $ordenCompra->id,
                    'producto_id' => $detalle['producto_id'],
                    //La unidad por el momento sera 1 hasta trabajar con otras unidades
                    'unit_id' => 1,
                    'cantidad' => $detalle['cantidad'],
                    'cantidad_pendiente' => $detalle['cantidad'],
                    'p_compra' => $detalle['pcompra'],
                    'total' => $detalle['total'],
                    'margen_ganancia' => $detalle['margen_ganancia'],
                    'p_venta' => $detalle['pventa'],
                    'condicion_vencimiento' => $detalle['condicion_vencimiento'],
                    'fecha_vencimiento' => $detalle['fecha_vencimiento'],
                    'bonificacion' => $detalle['bonificacion'],
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 200,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 409,
                'message_text' => 'Hubo un error al crear la orden de compra'. $e->getMessage(),
            ], 500);
        }
    } 

    public function update(Request $request){
        $validatedData = $request->validate([
            'compra_form.compra_id' => 'required|integer|exists:ordenes_compra,id',
            'compra_form.proveedor_id' => 'required|integer|exists:proveedor,id',
            'compra_form.type_comprobante_compra_id' => 'required|integer|exists:type_comprobante_pago_compra,id',
            'compra_form.forma_pago_id' => 'required|integer|exists:forma_pago_ordenes_compra,id',
            'compra_form.igv' => 'required|boolean',
            'compra_form.total' => 'required|numeric',
            'compra_form.impuesto' => 'required|numeric',
            'compra_form.sub_total' => 'required|numeric',
            'compra_form.notificacion' => 'required|boolean',
            'compra_form.mensaje_notificacion' => 'nullable',
            'compra_form.fecha_ingreso' => 'nullable|date',
            'compra_form.descripcion' => 'nullable',

            'compra_details' => 'required|array',
            'compra_details.*.id' => 'nullable|integer|exists:ordenes_compra_detail,id',
            'compra_details.*.producto_id' => 'required|integer|exists:productos,id',
            'compra_details.*.cantidad' => 'required|integer|min:1',
            'compra_details.*.condicion_vencimiento' => 'required|boolean',
            'compra_details.*.fecha_vencimiento' => 'required|date',
            'compra_details.*.margen_ganancia' => 'required|numeric|min:0',
            'compra_details.*.pcompra' => 'required|numeric|min:0',
            'compra_details.*.pventa' => 'required|numeric|min:0',
            'compra_details.*.total' => 'required|numeric|min:0',
            'compra_details.*.bonificacion' => 'required|boolean',
        ]);

        $orden_compra = OrdenCompra::findOrFail($validatedData['compra_form']['compra_id']);

        if($orden_compra->state == 4){
            return response()->json([
                "message" => 403,
                'message_text' => 'la orden de compra ya se encuentra ingresada al stock, no es posible editarla'
            ], 422);
        }

        try {
            DB::beginTransaction();
            // Crear la orden de compra
            $orden_compra->update([
                "proveedor_id" => $validatedData['compra_form']['proveedor_id'],
                "type_comprobante_compra_id" => $validatedData['compra_form']['type_comprobante_compra_id'],
                "forma_pago_id" => $validatedData['compra_form']['forma_pago_id'],
                "igv_state" => $validatedData['compra_form']['igv'],
                "descripcion" => $validatedData['compra_form']['descripcion'],
                "notificacion" => $validatedData['compra_form']['notificacion'],
                "mensaje_notificacion" => $validatedData['compra_form']['mensaje_notificacion'],
                "importe" => $validatedData['compra_form']['sub_total'],
                "igv" => $validatedData['compra_form']['impuesto'],
                "total" => $validatedData['compra_form']['total'],
                "fecha_ingreso" => $validatedData['compra_form']['fecha_ingreso'],
            ]);

            // Obtener los IDs actuales de la orden de compra
            $ids_actuales = OrdenCompraDetails::where('orden_compra_id', $orden_compra->id)
            ->pluck('id')
            ->toArray();

            // Obtener los IDs enviados (excluyendo valores null o vacíos)
            $ids_enviados = array_filter(array_column($validatedData['compra_details'], 'id'), function ($id) {
                return !empty($id) && is_numeric($id);
            });

            // Eliminar los productos que ya no están en la lista enviada
            $ids_a_eliminar = array_diff($ids_actuales, $ids_enviados);
            if (!empty($ids_a_eliminar)) {
                OrdenCompraDetails::whereIn('id', $ids_a_eliminar)->delete();
            }

            foreach ($validatedData['compra_details'] as $detalle) {
                if (!empty($detalle['id']) && is_numeric($detalle['id'])) {
                    // Si el ID es válido, actualizamos el producto existente
                    OrdenCompraDetails::where('id', $detalle['id'])
                        ->update([
                            'producto_id' => $detalle['producto_id'],
                            'cantidad' => $detalle['cantidad'],
                            'cantidad_pendiente' => $detalle['cantidad'],
                            'p_compra' => $detalle['pcompra'],
                            'total' => $detalle['total'],
                            'margen_ganancia' => $detalle['margen_ganancia'],
                            'p_venta' => $detalle['pventa'],
                            'condicion_vencimiento' => $detalle['condicion_vencimiento'],
                            'fecha_vencimiento' => $detalle['fecha_vencimiento'],
                            'bonificacion' => $detalle['bonificacion'],
                            'updated_by' => auth()->id(),
                        ]);
                } else {
                    // Si el ID es null o vacío, creamos un nuevo registro
                    OrdenCompraDetails::create([
                        'orden_compra_id' => $orden_compra->id,
                        'producto_id' => $detalle['producto_id'],
                        'cantidad' => $detalle['cantidad'],
                        'cantidad_pendiente' => $detalle['cantidad'],
                        'p_compra' => $detalle['pcompra'],
                        'unit_id' => 1,
                        'total' => $detalle['total'],
                        'margen_ganancia' => $detalle['margen_ganancia'],
                        'p_venta' => $detalle['pventa'],
                        'condicion_vencimiento' => $detalle['condicion_vencimiento'],
                        'fecha_vencimiento' => $detalle['fecha_vencimiento'],
                        'bonificacion' => $detalle['bonificacion'],
                    ]);
                }
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 200,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 409,
                'message_text' => 'Hubo un error al editar la orden de compra'. $e->getMessage(),
            ], 500);
        }
    } 

    public function destroy($id)
    {
        $orden_compra = OrdenCompra::findOrFail($id);

        if ($orden_compra->state != 0) {
            return response()->json([
                "message" => 403,
                'message_text' => "no puedes eliminar la orden porque la mercadería ya fue recepcionada"
            ], 422);
        }

        DB::beginTransaction();

        try {
            OrdenCompraDetails::where('orden_compra_id', $id)->delete();
            $details = OrdenCompraDetails::where('orden_compra_id', $id)->get();

            foreach ($details as $detail) {
                $detail->deleted_by = Auth::id();
                $detail->saveQuietly(); // Guardar sin activar eventos innecesarios
                $detail->delete(); // Eliminar individualmente para activar eventos
            }
            
            OrdenCompraCuotas::where('orden_compra_id', $id)->delete();
            $orden_compra->delete();
            DB::commit();
            return response()->json(["message" => "la orden de compra se eliminó correctamente."], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar orden de compra: " . $e->getMessage());
            return response()->json([
                "error" => "Ocurrió un problema al eliminar la orden. Por favor, intenta más tarde."
            ], 500);
        }
    }

    public function change_state(Request $request, $id){
        $request->validate([
            'state' => 'required|in:0,1',
        ]);

        $ordenCompra = OrdenCompra::findOrFail($id);

        if($request->state == 1){
            $date = Carbon::now();
        }else{
            $date = null;
        }

        $ordenCompra->update([
            'state' => $request->state,
            'date_recepcion' => $date
        ]);

        $message = $request->state == 1 
        ? 'Orden de compra recepcionada correctamente' 
        : 'Orden de compra revertida, ya no está recepcionada';

        return response()->json([
            'message' => $message,
            'order_compra' => [
                "id" => $ordenCompra->id,
                "codigo" => $ordenCompra->codigo,
                "proveedor" => $ordenCompra->getProveedor->name,
                "type_comprobante" => $ordenCompra->getTypeComprobante->name,
                "forma_pago" => $ordenCompra->getFormaPago->name,
                "descripcion" => $ordenCompra->descripcion,
                "total" => $ordenCompra->total,
                "igv" => $ordenCompra->igv,
                "state" => $ordenCompra->state,
                "created_at" => $ordenCompra->created_at->format("Y-m-d h:i A"),
                "cuotas_pendientes" => $ordenCompra->cuotas_pendientes,
                "cuotas" => $ordenCompra->getCuotas->map(function ($c) {
                    return [
                        "monto" => $c->amount,
                        "fecha_pago" => $c->start,
                        "comentario" => $c->notes,
                        "state" => $c->state,
                        "numero_unico" => $c->numero_unico,
                        "fecha_cancelado" => $c->fecha_cancelado,
                    ];
                })
            ]
        ], 200);
    }

    public function getLoteProductoOrdenCompra($id){
        $producto = Producto::with(['get_lotes' => function($query) {
            $query->where('state', 1)
                    ->whereNotNull('lote')
                    ->whereNotNull('fecha_vencimiento');
        }])->findOrFail($id);
    
        // Extraer los campos 'lote' y 'fecha_vencimiento'
        $lotesFiltrados = $producto->get_lotes->map(function ($lote) {
            return [
                'id' => $lote->id,
                'lote' => $lote->lote,
                'fecha_vencimiento' => $lote->fecha_vencimiento,
            ];
        });
    
        return response()->json($lotesFiltrados);
    }

    public function register_comprobantes_order_compra(Request $request, $id){
        $data = $request->all();
        $rules = [
            '*.orden_compra_id' => 'required|integer|exists:ordenes_compra,id',
            '*.type_comprobante_compra_id' => 'required|integer|exists:type_comprobante_pago_compra,id',
            '*.serie' => 'required|string',
            '*.n_documento' => 'required|string',
            '*.igv_state' => 'required|boolean',
            '*.importe' => 'required|numeric|min:0',
            '*.igv' => 'required|numeric|min:0',
            '*.total' => 'required|numeric|min:0',
            '*.fecha_emision' => 'required|date',
            '*.modo_pago' => 'required|boolean',
            '*.comentario' => 'nullable|string',
            
            // Productos dentro del comprobante
            '*.productos' => 'required|array|min:1',
            '*.productos.*.afectacion_id' => 'required|integer|exists:afectaciones_igv,id',
            '*.productos.*.producto_id' => 'required|integer|exists:productos,id',
            '*.productos.*.cantidad' => 'required|numeric|min:0',
            '*.productos.*.total' => 'required|numeric|min:0',
            '*.productos.*.bonificacion' => 'required|boolean',
            '*.productos.*.comentario' => 'nullable|string',
            '*.productos.*.pcompra' => 'required|numeric|min:0',
            '*.productos.*.pventa' => 'required|numeric|min:0',
            '*.productos.*.guia_devolucion' => 'required|boolean',
            '*.productos.*.cantidad_pendiente' => 'required|numeric|min:0',
            '*.productos.*.cantidad_reemplazo' => 'required|numeric|min:0',

            // Lotes dentro de productos
            '*.productos.*.lotes' => 'required|array|min:1',
            '*.productos.*.lotes.*.cantidad' => 'required|numeric|min:0',
            '*.productos.*.lotes.*.fecha_vencimiento' => 'nullable|date',
            '*.productos.*.lotes.*.lote' => 'nullable|string',
            '*.productos.*.lotes.*.producto_id' => 'required|integer|exists:productos,id',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        try {
            DB::beginTransaction();

            $productosConsolidados = [];
            $productos_guia_devolucion = [];
            $nuevo_estado = 4;

            foreach ($validatedData as $comprobanteData) {
                // Obtener la orden de compra para extraer el proveedor
                $orden = OrdenCompra::findOrFail($comprobanteData['orden_compra_id']);
                $proveedor_id = $orden->proveedor_id;

                // Verificar si ya existe el comprobante en esta orden de compra
                $comprobanteActual = NDocumentoOrdenCompra::where('serie', $comprobanteData['serie'])
                    ->where('n_documento', $comprobanteData['n_documento'])
                    ->where('orden_compra_id', $comprobanteData['orden_compra_id'])
                    ->first();

                // Verificar si ese mismo comprobante ya fue usado por el proveedor en otra orden
                $comprobanteDuplicado = NDocumentoOrdenCompra::where('serie', $comprobanteData['serie'])
                    ->where('n_documento', $comprobanteData['n_documento'])
                    ->whereHas('order_compra', function($query) use ($proveedor_id, $comprobanteData) {
                        $query->where('proveedor_id', $proveedor_id)
                            ->where('id', '!=', $comprobanteData['orden_compra_id']);
                    })
                    ->first();

                if ($comprobanteDuplicado) {
                    // Ya fue usado por el proveedor en otra orden, no se permite
                    throw new \Exception("El comprobante {$comprobanteData['serie']}-{$comprobanteData['n_documento']} ya fue usado por este proveedor en otra orden de compra.");
                }
                if($comprobanteActual){
                    $comprobante = $comprobanteActual;
                }else{
                    $comprobante = NDocumentoOrdenCompra::create([
                        "orden_compra_id" => $comprobanteData['orden_compra_id'],
                        "type_comprobante_compra_id" => $comprobanteData['type_comprobante_compra_id'],
                        "serie" => $comprobanteData['serie'],
                        "n_documento" => $comprobanteData['n_documento'],
                        "igv_state" => $comprobanteData['igv_state'],
                        "modo_pago" => $comprobanteData['modo_pago'],
                        "importe" => $comprobanteData['importe'],
                        "igv" => $comprobanteData['igv'],
                        "total" => $comprobanteData['total'],
                        "fecha_emision" => $comprobanteData['fecha_emision'],
                        "comentario" => $comprobanteData['comentario'],
                    ]);
                }
    
                foreach ($comprobanteData['productos'] as $producto) {
                    $key = $producto['producto_id'] . '-' . ($producto['bonificacion'] ? '1' : '0');

                    if (!isset($productosConsolidados[$key])) {
                        $productosConsolidados[$key] = [
                            'producto_id' => $producto['producto_id'],
                            'bonificacion' => $producto['bonificacion'],
                            'cantidad_total' => 0,
                            'cantidad_reemplazo' => 0,
                            'cantidad_pendiente' => 0,
                            'guia_devolucion' => false,
                        ];
                    }

                    $productosConsolidados[$key]['cantidad_total'] += $producto['cantidad'];
                    $productosConsolidados[$key]['cantidad_reemplazo'] += ($producto['cantidad'] + $producto['cantidad_pendiente']);
                    $productosConsolidados[$key]['cantidad_pendiente'] += $producto['cantidad_pendiente'];
                    if (!empty($producto['guia_devolucion'])) {
                        $productosConsolidados[$key]['guia_devolucion'] = true;
                        $productos_guia_devolucion[] = [
                            'producto_id' => $producto['producto_id'],
                            'cantidad_pendiente' => $producto['cantidad_pendiente']
                        ];
                    }

                    OrderCompraDetailsGestionado::create([
                        "orden_compra_id" => $comprobanteData['orden_compra_id'],
                        "oc_n_comprob_id" => $comprobante->id,
                        "afectacion_id" => $producto['afectacion_id'],
                        "unit_id" => 1, //por mientras hasta implementar venta de cajas, paquetes, etc
                        "producto_id" => $producto['producto_id'],
                        "cantidad" => $producto['cantidad'],
                        "total" => $producto['total'],
                        "bonificacion" => $producto['bonificacion'],
                        "comentario" => $producto['comentario'],
                        "pcompra" => $producto['pcompra'],
                    ]);

                    $productoModel = Producto::find($producto['producto_id']);
                    if(!$producto['bonificacion']){
                        HistorialPrecioCompra::create([
                            'producto_id' => $producto['producto_id'],
                            'precio' => $producto['pcompra'],
                        ]);
                        HistorialPrecioVenta::create([
                            'producto_id' => $producto['producto_id'],
                            'precio' => $producto['pventa'],
                            'comentario' => 'Por compra'
                        ]);
                        $productoModel->pventa = $producto['pventa'];
                        $productoModel->pcompra = $producto['pcompra'];
                        $productoModel->save();
                    }

                    foreach ($producto['lotes'] as $lote) {
                        $loteDB = ProductoLotes::where('producto_id', $lote['producto_id'])
                            ->where(function ($query) use ($lote) {
                                if (isset($lote['lote'])) {
                                    $query->where('lote', $lote['lote']);
                                } else {
                                    $query->whereNull('lote');
                                }

                                if (isset($lote['fecha_vencimiento'])) {
                                    $query->whereDate('fecha_vencimiento', $lote['fecha_vencimiento']);
                                } else {
                                    $query->whereNull('fecha_vencimiento');
                                }
                            })
                            ->first();

                        if ($loteDB) {
                            // Ya existe, actualiza cantidad
                            $loteDB->cantidad += $lote['cantidad'];
                            $loteDB->save();
                        } else {
                            // No existe, crea nuevo
                            ProductoLotes::create([
                                "producto_id" => $lote['producto_id'],
                                "fecha_vencimiento" => $lote['fecha_vencimiento'] ?? null,
                                "lote" => $lote['lote'] ?? null,
                                "cantidad" => $lote['cantidad'],
                            ]);
                        }

                        // Siempre actualizar el stock general del producto
                        $productoModel->stock += $lote['cantidad'];
                        $productoModel->save();
                    }
                }
            }

            foreach ($productosConsolidados as $item) {
                // Buscar el registro que coincida con producto_id y bonificacion
                $detalle = OrdenCompraDetails::where('orden_compra_id', $id)
                    ->where('producto_id', $item['producto_id'])
                    ->where('bonificacion', $item['bonificacion'])
                    ->first();

                if ($detalle) {
                    if ($item['cantidad_total'] != $item['cantidad_reemplazo']) {
                        if($detalle->cantidad == $detalle->cantidad_pendiente){
                            $detalle->cantidad_reemplazo = $item['cantidad_reemplazo'];
                        }
                    }
                    $detalle->cantidad_pendiente = $item['cantidad_pendiente'];
                    $detalle->devolver = $item['guia_devolucion'] ? 1 : 0;
                    if($item['cantidad_pendiente'] > 0 && !$item['guia_devolucion']){
                        $nuevo_estado = 3;
                    }
                    //Si el producto no tiene cantidades pendiente se cambia el estado del movimiento a ingresado
                    if($item['cantidad_pendiente'] == 0 || $item['guia_devolucion']){
                        $detalle->state = 1;
                    }
                    $detalle->save();
                }
            }

            $orden_compra = OrdenCompra::findOrFail($id);
            $orden_compra->update([
                'state' => $nuevo_estado,
                'fecha_ingreso' => Carbon::now(),
                'date_revision' => Carbon::now(),
            ]);

            if(count($productos_guia_devolucion) > 0){
                $comprobante = TypeComprobante::where('codigo', 'GD')->with('serie')->first();

                if ($comprobante && $comprobante->serie->count() > 0) {
                    $serie = $comprobante->serie->where('state', 1)->first(); //Por el momento coge el primero debido a que solo hay un almacen

                    if ($serie) {
                        $guia_devolucion = GuiaDevolucion::create([
                            'type_comp_serie_id' => $serie->id,
                            'correlativo' => $serie->correlativo + 1,
                            'proveedor_id' => $orden_compra->proveedor_id,
                            'order_compra_id' => $orden_compra->id,
                        ]);

                        foreach ($productos_guia_devolucion as $p) {
                            GuiaDevolucionDetail::create([
                                'guia_devolucion_id' => $guia_devolucion->id,
                                'producto_id' => $p['producto_id'],
                                'unit_id' => 1,
                                'cantidad' => $p['cantidad_pendiente'],
                            ]);
                        }

                        // Actualizar el correlativo
                        $serie->correlativo += 1;
                        $serie->save();
                    }
                }
            }
            DB::commit();
    
            return response()->json([
                'message' => 200,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 409,
                'message_text' => 'Hubo un error al ingresar la mercaderia de la orden de compra'. $e->getMessage(),
            ], 500);
        }
    }
}
