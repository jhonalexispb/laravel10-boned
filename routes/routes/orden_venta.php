<?php

use App\Http\Controllers\OrdenVentaController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    //banco
    Route::get('/orden_venta/recursos_iniciales', [OrdenVentaController::class, 'getRecursosIniciales']);
    Route::get('/orden_venta/obtener_productos', [OrdenVentaController::class, 'getProductos']);
    Route::get('/orden_venta/recursos_crear/productos/{id}', [OrdenVentaController::class, 'getProductDetail']);
    Route::post('/orden_venta/recursos_crear/productos', [OrdenVentaController::class, 'getProductosByLaboratorio']);
});