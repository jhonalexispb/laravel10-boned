<?php

namespace App\Http\Controllers\ProductoAtributtes;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoGestionController extends Controller
{
    public function gestionar(Request $request, string $id)
    {
        $request->validate([
            'sale_boleta' => 'required|boolean',
            /* 'maneja_lotes' => 'required|boolean', */
            'maneja_escalas' => 'required|boolean',
            'promocionable' => 'required|boolean',
            'state' => 'required|boolean',
        ]);

        // Crear el producto
        $producto = Producto::findOrFail($id);
        $producto->update([
            'sale_boleta' => $request->sale_boleta,
            'maneja_escalas' => $request->maneja_escalas,
            'promocionable' => $request->promocionable,
            'state' => $request->state,
        ]);
        // Devolver el recurso del producto creado
        return new ProductResource($producto);
    }
}
