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
    Route::put('/orden_venta/estado/{id}', [OrdenVentaController::class, 'change_state']);
    Route::apiResource("orden_venta",OrdenVentaController::class)->except(['create','edit']);
});

Route::get('/orden_venta/pdf/{id}',[OrdenVentaController::class, 'orden_venta_pdf']);