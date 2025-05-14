<?php

use App\Http\Controllers\GuiasPrestamoAtributtes\GuiasPrestamoMovimientosController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],

], function () {
    Route::delete('atributtes/guias_prestamo/vaciar_guia_prestamo/{id}', [GuiasPrestamoMovimientosController::class, 'vaciarGuiaPrestamo']);
    Route::apiResource('atributtes/guias_prestamo/detalle', GuiasPrestamoMovimientosController::class)
    ->except('show');
});