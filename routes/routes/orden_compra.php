<?php

use App\Http\Controllers\OrdenCompraController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    //banco
    Route::get('/orden_compra/recursos_crear', [OrdenCompraController::class, 'getRecursosParaCrear']);
    Route::get('/orden_compra/recursos_editar', [OrdenCompraController::class, 'getRecursosParaEditar']);
    Route::get('/orden_compra/recursos_crear/productos/{id}', [OrdenCompraController::class, 'getProductDetail']);
    Route::get('/orden_compra/cuotas_pendientes', [OrdenCompraController::class,'getCuotasPendientes']);
    Route::get('/orden_compra/recursos_editar/cuotas/{id}', [OrdenCompraController::class, 'getCuotasPendientesEditarOrdenCompra']);
    Route::get('/orden_compra/recursos/productos/{id}', [OrdenCompraController::class, 'getProductosOrdenCompra']);
    Route::post('/orden_compra/recursos_crear/productos', [OrdenCompraController::class, 'getProductosByLaboratorio']);
    Route::put('/orden_compra/estado/{id}', [OrdenCompraController::class, 'change_state']);
    Route::resource("orden_compra",OrdenCompraController::class)->except(['create','edit']);
});