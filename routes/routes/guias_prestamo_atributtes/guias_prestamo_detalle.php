<?php

use App\Http\Controllers\GuiasPrestamoAtributtes\GuiasPrestamoMovimientosController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],

], function () {
    Route::apiResource('atributtes/guias_prestamo', GuiasPrestamoMovimientosController::class)
    ->except('show');
});