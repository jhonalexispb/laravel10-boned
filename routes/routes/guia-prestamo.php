<?php

use App\Http\Controllers\GuiasPrestamoController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    Route::get('/guia_prestamo/recursos_crear/productos/{id}', [GuiasPrestamoController::class, 'getProductDetail']);
    Route::post('/guia_prestamo/recursos_crear/productos', [GuiasPrestamoController::class, 'getProductosByLaboratorio']);
    Route::put('/guia_prestamo/update_state/{id}', [GuiasPrestamoController::class, 'updateState']);
    Route::resource("guia_prestamo",GuiasPrestamoController::class)->except(['create','edit']);
});