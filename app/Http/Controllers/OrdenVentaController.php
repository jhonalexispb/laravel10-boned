<?php

namespace App\Http\Controllers;

use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\Proveedor;
use App\Models\OrdenCompraAtributtes\FormaPagoOrdenesCompra;
use App\Models\OrdenCompraAtributtes\TipoComprobantePagoCompra;
use App\Models\OrdenVenta;
use App\Models\Producto;
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

    public function getRecursosIniciales(){
        $userId = auth()->id();

        $search_codigo = OrdenVenta::where('usuario_id',$userId)
                        ->where('state_orden',0)
                        ->first();
        if($search_codigo){
            $carrito_venta_id = $search_codigo->id;
            $codigo = $search_codigo->codigo;
        }else{
            $codigo = OrdenVenta::generarCodigo($userId);
            if (!$codigo) {
                return response() -> json([
                    "message" => 403,
                    "message_text" => 'Codigo no generado'
                ],422);
            }

            $carritoVenta  = OrdenVenta::create([
                'usuario_id' => $userId,
                'codigo' => $codigo,
            ]);

            if (!$carritoVenta) {
                return response() -> json([
                    "message" => 403,
                    "message_text" => 'Carrito de venta no creado'
                ],422);
            }

            $carrito_venta_id = $carritoVenta->id;
        }

        return response()->json([
            'codigo' => $codigo,
            'carrito_venta_id' => $carrito_venta_id
        ]);
    }
    //necasitamoas envai8r la mercaderia que no este vencida, y que no este para devolucion, si no tiene devolucion se tiene que avisar, habra una seccion en donde se filtre los productos que esten a 6 y 3 meses de vencery tiene que ser configurable 
    public function getProductos(){

        return response()->json([
            "productos" => Producto::where('state', 1)
                                    /* ->where('stock', '>', 0) */
                                    ->with([
                                        'get_escalas' => function ($query) {
                                            $query->where('state', 1); // Solo escalas activas
                                        },
                                        'get_lotes' => function ($query) {
                                            $query->where('state', 1); // Solo lotes activos
                                        },
                                        'get_laboratorio',
                                        'get_lineaFarmaceutica',
                                        'get_fabricante',
                                        'get_presentacion',
                                        'get_principios_activos'
                                    ])
                                    ->orderBy('sku','asc')
                                    ->get()
                                    ->map(function ($p) {
                return [
                    "id" => $p->id,
                    "sku" => $p->sku,
                    "tproducto" => $p->tproducto,
                    "laboratorio" => $p->get_laboratorio->name,
                    "laboratorio_id" => $p->laboratorio_id,
                    "color_laboratorio" => $p->get_laboratorio->color,
                    "nombre" => $p->nombre,
                    "caracteristicas" => $p->caracteristicas,
                    "nombre_completo" => $p->nombre . ' ' . $p->caracteristicas,
                    "pventa" => $p->pventa ?? '0.0',
                    "stock" => $p->stock ?? '0',
                    "imagen" => $p->imagen ?? env("IMAGE_DEFAULT"),
                    "linea_farmaceutica" => $p->get_lineaFarmaceutica->nombre,
                    "fabricante" => $p->get_fabricante->nombre,
                    "presentacion" => $p->presentacion_id ? $p->get_presentacion->name : 'Sin presentación',
                    "principios_activos" => $p->get_principios_activos->pluck('id')->toArray(),
                    "maneja_escalas" => $p->maneja_escalas,
                    "escalas" => $p->get_escalas, // Solo escalas activas
                    "lotes" => $p->get_lotes, // Solo lotes activos
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
}
