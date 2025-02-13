<?php

use App\Http\Controllers\ProductoAtributtes\ProductoEscalaController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],

], function () {
    Route::get('atributtes/productos/config_escalas/{id}', [ProductoEscalaController::class, 'index']);
    Route::put('atributtes/productos/config_escalas/{productoId}/{escalaId}', [ProductoEscalaController::class, 'update']);
    Route::delete('atributtes/productos/config_escalas/{productoId}/{escalaId}', [ProductoEscalaController::class, 'delete']);
    Route::post('atributtes/productos/config_escalas/{id}', [ProductoEscalaController::class, 'store']);
});