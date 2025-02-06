<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductoResource;
use App\Models\Configuration\CategoriaProducto;
use App\Models\Configuration\FabricanteProducto;
use App\Models\Configuration\Laboratorio;
use App\Models\Configuration\LineaFarmaceutica;
use App\Models\Configuration\PrincipioActivo;
use App\Models\Producto;
use App\Models\ProductoAtributtes\CondicionAlmacenamiento;
use App\Models\ProductoAtributtes\Unidad;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $bank = Producto::where("nombre","like","%".$search."%")
                                ->orderBy("id","desc")
                                ->paginate(25);
                                
        return response()->json([
            'total' => $bank->total(),
            'products' => ProductoResource::collection($bank),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        /* $request->validate([
            'sku_manual' => 'digits:8',
            'tproducto' => 'required|in:1,2',
            'unidad_id' => 'required|numeric|exists:unidades,id',
            'laboratorio_id' => 'required|numeric|exists:laboratorio,id',
            'nombre' => 'required',
            'categoria_id' => 'nullable|exists:categoria,id',
            'stock_seguridad' => 'required|numeric',
            'linea_farmaceutica_id' => 'required|exists:lineas_farmaceuticas,id',
            'fabricante_id ' => 'required|exists:fabricantes_producto,id',
            'sale_boleta' => 'required|boolean',
            'maneja_lotes' => 'required|boolean',
            'maneja_escalas' => 'required|boolean',
            'promocionable' => 'required|boolean'
        ]); */

        if(!empty($request->sku_manual)){
            $request->merge(['sku' => $request->sku_manual]);
        }else{
            $codigo = Producto::generarCodigo($request->laboratorio_id);
            if (is_null($codigo)) {
                return response()->json([
                    'message' => 'Error al generar el código de producto.',
                    'message_text' => 'No se pudo generar un código debido a un problema con el laboratorio.',
                ], 422);
            }
            $request->merge(['sku' => $codigo]);
        }

        $exist_sku = Producto::withTrashed()
                    ->where('sku',$request->sku)
                    ->first();
        
        if($exist_sku){
            if ($exist_sku->deleted_at) {
                // Si el departamento está eliminado lógicamente, puedes restaurarlo o actualizarlo
                return response() -> json([
                    "message" => 409,
                    "message_text" => "el sku ".$exist_sku->sku." ya existe pero se encuentra eliminado, esta asignado al producto ".$exist_sku->nombre.' '.$exist_sku->caracteristicas." del laboratorio de ".$exist_sku->get_laboratorio->name." ¿Deseas restaurarlo?",
                    "producto" => $exist_sku->id
                ]);
            }
            return response()->json([
                "message" => 403,
                "message_text" => "el sku ".$exist_sku->sku." ya existe, esta asignado al producto ".$exist_sku->nombre.' '.$exist_sku->caracteristicas." del laboratorio de ".$exist_sku->get_laboratorio->name
            ], 422);
        }

        $normalized_nombre = preg_replace('/\s+/', '', $request->nombre);
        $normalized_caracteristicas = preg_replace('/\s+/', '', $request->caracteristicas);

        if (empty($normalized_caracteristicas)) {
            $is_exist_with_caracteristicas_null = Producto::withTrashed()
            ->whereRaw('REPLACE(name, " ", "") = ?', [$normalized_nombre])
            ->whereNull('concentracion')
            ->first();

            if($is_exist_with_caracteristicas_null){
                if ($is_exist_with_caracteristicas_null->deleted_at) {
                    
                    return response() -> json([
                        "message" => 409,
                        "message_text" => "el producto ".$is_exist_with_caracteristicas_null->nombre." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                        "producto" => $is_exist_with_caracteristicas_null->id
                    ]);
                }
                return response()->json([
                    "message" => 403,
                    "message_text" => "el producto ".$is_exist_with_caracteristicas_null->nombre." ya existe"
                ], 422);
            }
        } else {
            $is_exist_producto = Producto::withTrashed()
            ->whereRaw('REPLACE(name, " ", "") = ?', [$normalized_nombre])
            ->whereRaw('REPLACE(caracteristicas, " ", "") = ?', [$normalized_caracteristicas])
            ->first();

            if($is_exist_producto){
                if ($is_exist_producto->deleted_at) {
                    return response() -> json([
                        "message" => 409,
                        "message_text" => "el producto ".$is_exist_producto->nombre.' '.$is_exist_producto->caracteristicas." ya existe pero se encuentra eliminado, ¿Deseas restaurarlo?",
                        "producto" => $is_exist_producto->id
                    ]);
                }
                return response() -> json([
                    "message" => 403,
                    "message_text" => "el producto ".$is_exist_producto->nombre.' '.$is_exist_producto->caracteristicas." ya existe"
                ],422);
            }
        }

        $producto = Producto::create($request->all());
        ProductoResource::collection($producto);
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
                    "name" => $p->nombre,
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
}
