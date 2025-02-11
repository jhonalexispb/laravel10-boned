<?php

namespace App\Http\Controllers;

use App\Exports\Product\DownloadProduct;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Imports\ProductImport;
use App\Models\Configuration\CategoriaProducto;
use App\Models\Configuration\FabricanteProducto;
use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\LineaFarmaceutica;
use App\Models\Configuration\PrincipioActivo;
use App\Models\Configuration\Warehouse;
use App\Models\Producto;
use App\Models\ProductoAtributtes\CondicionAlmacenamiento;
use App\Models\ProductoAtributtes\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {   
        $producto_id = $request->producto_id;
        $laboratorio_id = $request->laboratorio_id;
        $state_stock = $request->state_stock;
        $warehouse_id = $request->warehouse_id;

        $products = Producto::filterAdvance($producto_id, $laboratorio_id,$state_stock, $warehouse_id)
                        ->orderBy('id', 'desc')
                        ->paginate(25);

        $num_products_disponible = Producto::where("state_stock",1)->count();
        $num_products_por_agotar = Producto::where("state_stock",2)->count();
        $num_products_agotado = Producto::where("state_stock",3)->count();
                                
        return response()->json([
            'total' => $products->total(),
            'products' => ProductCollection::make($products),
            'laboratorios' => Laboratorio::all()->map(function ($l) {
                return [
                    "id" => $l->id,
                    "name" => $l->name,
                ];
            }),
            'num_products_disponible' => $num_products_disponible,
            'num_products_por_agotar' => $num_products_por_agotar, 
            'num_products_agotado' => $num_products_agotado,
            'warehouses'  => Warehouse::where('state',1)->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        $request->validate([
            'sku_manual' => 'nullable|digits:8',
            'tproducto' => 'required|in:1,2',
            'unidad_id' => 'required|numeric|exists:unidades,id',
            'laboratorio_id' => 'required|numeric|exists:laboratorio,id',
            'nombre' => 'required',
            'categoria_id' => 'nullable|exists:categoria,id',
            'stock_seguridad' => 'required|numeric',
            'linea_farmaceutica_id' => 'required|exists:lineas_farmaceuticas,id',
            'fabricante_id' => 'required|exists:fabricantes_producto,id',
            'sale_boleta' => 'required|boolean',
            'maneja_lotes' => 'required|boolean',
            'maneja_escalas' => 'required|boolean',
            'promocionable' => 'required|boolean',
            'principio_activo_id' => 'nullable|array',
            'principio_activo_id.*' => 'exists:principio_activo,id',
        ]);

        DB::beginTransaction();  // Inicia la transacción

        try {

            // Generación del SKU si no se pasa manualmente
            if (!empty($request->sku_manual)) {
                $request->merge(['sku' => $request->sku_manual]);
            } else {
                $codigo = Producto::generarCodigo($request->laboratorio_id);
                if (is_null($codigo)) {
                    return response()->json([
                        'message' => 'Error al generar el código de producto.',
                        'message_text' => 'No se pudo generar un código debido a un problema con el laboratorio.',
                    ], 422);
                }
                $request->merge(['sku' => $codigo]);
            }

            // Verificar si el SKU ya existe
            $exist_sku = Producto::withTrashed()->where('sku', $request->sku)->first();

            if ($exist_sku) {
                if ($exist_sku->deleted_at) {
                    return response()->json([
                        "message" => 409,
                        "message_text" => "El SKU {$exist_sku->sku} ya existe pero se encuentra eliminado. ¿Deseas restaurarlo?",
                        "producto" => $exist_sku->id
                    ]);
                }
                return response()->json([
                    "message" => 403,
                    "message_text" => "El SKU {$exist_sku->sku} ya existe en el producto {$exist_sku->nombre}."
                ], 422);
            }

            // Validación de nombre y características (sin blancos)
            $normalized_nombre = preg_replace('/\s+/', '', $request->nombre);
            $normalized_caracteristicas = preg_replace('/\s+/', '', $request->caracteristicas);

            if (empty($normalized_caracteristicas)) {
                $is_exist_with_caracteristicas_null = Producto::withTrashed()
                    ->whereRaw('REPLACE(nombre, " ", "") = ?', [$normalized_nombre])
                    ->whereNull('concentracion')
                    ->first();

                if ($is_exist_with_caracteristicas_null) {
                    return response()->json([
                        "message" => 409,
                        "message_text" => "El producto {$is_exist_with_caracteristicas_null->nombre} ya existe pero se encuentra eliminado."
                    ]);
                }
            } else {
                $is_exist_producto = Producto::withTrashed()
                    ->whereRaw('REPLACE(nombre, " ", "") = ?', [$normalized_nombre])
                    ->whereRaw('REPLACE(caracteristicas, " ", "") = ?', [$normalized_caracteristicas])
                    ->first();

                if ($is_exist_producto) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "El producto {$is_exist_producto->nombre} ya existe."
                    ], 422);
                }
            }

            // Crear el producto
            $producto = Producto::create($request->all());

            // Asociar los principios activos usando sync()
            if (!empty($request->principio_activo_id)) {
                $producto->get_principios_activos()->sync($request->principio_activo_id);
            }

            // Si todo va bien, confirmamos la transacción
            DB::commit();

            // Devolver el recurso del producto creado
            return new ProductResource($producto);

        } catch (\Exception $e) {
            // Si algo sale mal, hacemos rollback
            DB::rollBack();
            
            // Devolver el error
            return response()->json([
                'message' => 'Error en la creación del producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /* $producto->get_principios_activos()->attach($proveedores);

        $producto->load('proveedores'); */

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
            'sku_manual' => 'required|digits:8',
            'tproducto' => 'required|in:1,2',
            'unidad_id' => 'required|numeric|exists:unidades,id',
            'laboratorio_id' => 'required|numeric|exists:laboratorio,id',
            'nombre' => 'required',
            'categoria_id' => 'nullable|exists:categoria,id',
            'stock_seguridad' => 'required|numeric',
            'linea_farmaceutica_id' => 'required|exists:lineas_farmaceuticas,id',
            'fabricante_id' => 'required|exists:fabricantes_producto,id',
            'sale_boleta' => 'required|boolean',
            'maneja_lotes' => 'required|boolean',
            'maneja_escalas' => 'required|boolean',
            'promocionable' => 'required|boolean',
            'principio_activo_id' => 'nullable|array',
            'principio_activo_id.*' => 'exists:principio_activo,id',
        ]);

        DB::beginTransaction();  // Inicia la transacción

        try {
            // Generación del SKU si no se pasa manualmente
            if (!empty($request->sku_manual)) {
                $request->merge(['sku' => $request->sku_manual]);
            } else {
                $codigo = Producto::generarCodigo($request->laboratorio_id);
                if (is_null($codigo)) {
                    return response()->json([
                        'message' => 'Error al generar el código de producto.',
                        'message_text' => 'No se pudo generar un código debido a un problema con el laboratorio.',
                    ], 422);
                }
                $request->merge(['sku' => $codigo]);
            }

            // Verificar si el SKU ya existe
            $exist_sku = Producto::withTrashed()
                        ->where('sku', $request->sku)
                        ->where('id', '<>',$id)
                        ->first();

            if ($exist_sku) {
                if ($exist_sku->deleted_at) {
                    return response()->json([
                        "message" => 409,
                        "message_text" => "El SKU {$exist_sku->sku} ya existe pero se encuentra eliminado. ¿Deseas restaurarlo?",
                        "producto" => $exist_sku->id
                    ]);
                }
                return response()->json([
                    "message" => 403,
                    "message_text" => "El SKU {$exist_sku->sku} ya existe para el producto {$exist_sku->get_laboratorio->name} {$exist_sku->nombre} {$exist_sku->caracteristicas}."
                ], 422);
            }

            // Validación de nombre y características (sin blancos)
            $normalized_nombre = preg_replace('/\s+/', '', $request->nombre);
            $normalized_caracteristicas = preg_replace('/\s+/', '', $request->caracteristicas);

            if (empty($normalized_caracteristicas)) {
                $is_exist_with_caracteristicas_null = Producto::withTrashed()
                    ->whereRaw('REPLACE(nombre, " ", "") = ?', [$normalized_nombre])
                    ->whereNull('concentracion')
                    ->where('id','<>',$id)
                    ->first();

                if ($is_exist_with_caracteristicas_null) {
                    return response()->json([
                        "message" => 409,
                        "message_text" => "El producto:  {$is_exist_with_caracteristicas_null->get_laboratorio->name} {$is_exist_with_caracteristicas_null->nombre} {$is_exist_with_caracteristicas_null->caracteristicas} ya existe pero se encuentra eliminado."
                    ]);
                }
            } else {
                $is_exist_producto = Producto::withTrashed()
                    ->whereRaw('REPLACE(nombre, " ", "") = ?', [$normalized_nombre])
                    ->whereRaw('REPLACE(caracteristicas, " ", "") = ?', [$normalized_caracteristicas])
                    ->where('id','<>',$id)
                    ->first();

                if ($is_exist_producto) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "El producto: {$is_exist_producto->get_laboratorio->name} {$is_exist_producto->nombre} {$is_exist_producto->caracteristicas} ya existe."
                    ], 422);
                }
            }

            // Crear el producto
            $producto = Producto::findOrFail($id);
            $producto->update($request->all());

            // Asociar los principios activos usando sync()
            if (!empty($request->principio_activo_id)) {
                $producto->get_principios_activos()->sync($request->principio_activo_id);
            }

            // Si todo va bien, confirmamos la transacción
            DB::commit();

            // Devolver el recurso del producto creado
            return new ProductResource($producto);

        } catch (\Exception $e) {
            // Si algo sale mal, hacemos rollback
            DB::rollBack();
            
            // Devolver el error
            return response()->json([
                'message' => 'Error en la creación del producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getRecursos()
    {
        return response()->json([

            "unidades" => Unidad::all()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->abreviatura.' ('.$p->name.')',
                ];
            }),
        
            "laboratorios" => Laboratorio::where('state', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            }),
        
            "principios_activos" => PrincipioActivo::where('state', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name_complete" => $p->name.' '.$p->concentracion,
                ];
            }),
        
            "lineas_farmaceuticas" => LineaFarmaceutica::where('status', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "nombre" => $p->nombre,
                ];
            }),
        
            "fabricantes" => FabricanteProducto::where('status', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name_complete" => $p->nombre.' ('.$p->pais.')',
                ];
            }),
        
            "categorias" => CategoriaProducto::where('state', 1)->get()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            }),
        
            "condiciones_almacenamiento" => CondicionAlmacenamiento::all()->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                ];
            }),
        
        ]);
    }

    public function getRecursosParaCrear(Request $request)
    {   
        $request -> validate([
            'laboratorio_id' => 'required|exists:laboratorio,id'
        ]);
        $codigo = Producto::generarCodigo($request->laboratorio_id);
        if (!$codigo) {
            return response()->json(['error' => 'Código no generado'], 422);  // Si algo va mal en la generación, enviar error
        }
        return response()->json([
            "codigo" => $codigo,
        ]);
    }

    public function import_product(Request $request){
        $request->validate([
            "import_file" => 'required|file|mimes:xls,xlsx,csv'
        ]);

        $path = $request->file("import_file");
        $data = Excel::import(new ProductImport,$path);

        return response()->json([
            "message" => 200
        ]);
    }

    public function export_products(Request $request){
        $producto_id = $request->get("producto_id");
        $laboratorio_id = $request->get("laboratorio_id");
        $state_stock = $request->get("state_stock");
        $warehouse_id = $request->get("warehouse_id");

        $products = Producto::filterAdvance($producto_id, $laboratorio_id, $state_stock,$warehouse_id)
        ->orderBy('laboratorio_id', 'asc')
        ->paginate(25);

        return Excel::download(new DownloadProduct($products),"productos_descargados.xlsx");
    }
}
