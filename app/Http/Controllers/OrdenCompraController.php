<?php

namespace App\Http\Controllers;

use App\Models\Configuration\Proveedor;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraAtributtes\FormaPagoOrdenesCompra;
use App\Models\OrdenCompraAtributtes\OrdenCompraCuotas;
use App\Models\OrdenCompraAtributtes\OrdenCompraDetails;
use App\Models\OrdenCompraAtributtes\TipoComprobantePagoCompra;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    
    public function getCuotasPendientes()
    {
        $cuotas = OrdenCompraCuotas::where('state',0)->get();

        return response()->json([
            "cuotas_pendientes" => $cuotas->map(function($d){
                return [
                    "title" => $d->title,
                    "start" => Carbon::parse($d->start)->format("Y-m-d"),
                    "startStr" => Carbon::parse($d->start)->format("Y-m-d"),
                    "className" => 'border-warning bg-warning text-white',
                    "extendedProps" => [
                        "amount" => $d->amount,
                        "notes" => $d->notes,
                        "reminder" => $d->reminder,
                    ],
                    "state" => $d->state,
                    "numero_unico" => $d->numero_unico,
                    "fecha_cancelado" => $d->fecha_cancelado,
                ];
            })
        ]);
    }

    public function index(Request $request)  {
        $search = $request->get('search');
        $order_compra_list = OrdenCompra::with(['getProveedor:id,name', 'getTypeComprobante:id,name', 'getFormaPago:id,name','getCuotas'])
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
                    })
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
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

            'eventos_compra_cuotas' => 'required|array',
            'eventos_compra_cuotas.*.title' => 'required',
            'eventos_compra_cuotas.*.start' => 'required|date',
            'eventos_compra_cuotas.*.amount' => 'required|numeric|min:0',
            'eventos_compra_cuotas.*.notes' => 'nullable|string',
            'eventos_compra_cuotas.*.reminder' => 'required|date',
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
                    'p_compra' => $detalle['pcompra'],
                    'total' => $detalle['total'],
                    'margen_ganancia' => $detalle['margen_ganancia'],
                    'p_venta' => $detalle['pventa'],
                    'condicion_vencimiento' => $detalle['condicion_vencimiento'],
                    'fecha_vencimiento' => $detalle['fecha_vencimiento'],
                ]);
            }
            
            foreach ($validatedData['eventos_compra_cuotas'] as $cuota) {
                OrdenCompraCuotas::create([
                    'orden_compra_id' => $ordenCompra->id,
                    'title' => $cuota['title'],
                    'amount' => $cuota['amount'],
                    'start' => $cuota['start'],
                    'reminder' => $cuota['reminder'],
                    'notes' => $cuota['notes'] ?? null,
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
}
