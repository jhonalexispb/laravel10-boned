<?php

use App\Http\Controllers\OrdenVentaController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    Route::get('/orden_venta/recursos_crear/productos/{id}', [OrdenVentaController::class, 'getProductDetail']);
    Route::post('/orden_venta/recursos_crear/obtener_productos', [OrdenVentaController::class, 'getProductosByLaboratorio']);
    Route::get('/orden_venta/consultar_guia_prestamo_pendiente', [OrdenVentaController::class, 'verificarGuiaPrestamoPendiente']);
    Route::apiResource("orden_venta",OrdenVentaController::class)->except(['create','edit']);
});