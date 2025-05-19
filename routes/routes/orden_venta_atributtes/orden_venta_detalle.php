<?php

use App\Http\Controllers\OrdenVentaAtributtes\OrdenVentaMovimientosController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],

], function () {
    /* Route::delete('atributtes/orden_venta/vaciar_orden_venta/{id}', [GuiasPrestamoMovimientosController::class, 'vaciarGuiaPrestamo']); */
    Route::post('atributtes/orden_venta/detalle/editar_cantidad', [OrdenVentaMovimientosController::class, 'editarCantidadOrdenVenta']);
    Route::delete('atributtes/orden-venta/eliminar-producto/{producto_id}/{orden_venta_id}', [OrdenVentaMovimientosController::class, 'eliminarPorProducto']);
    Route::apiResource('atributtes/orden_venta/detalle', OrdenVentaMovimientosController::class)
    ->except('show','delete','update');
});