<?php

use App\Http\Controllers\ProductoAtributtes\ProductoLotesController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],

], function () {
    Route::get('atributtes/productos/config_lotes/{id}', [ProductoLotesController::class, 'index']);
    Route::put('atributtes/productos/config_lotes/{productoId}/{loteId}', [ProductoLotesController::class, 'update']);
    Route::put('atributtes/productos/config_lotes/state_escala/{productoId}/{escalaId}', [ProductoLotesController::class, 'updateState']);
    Route::delete('atributtes/productos/config_lotes/{productoId}/{loteId}', [ProductoLotesController::class, 'delete']);
    Route::post('atributtes/productos/config_lotes/{id}', [ProductoLotesController::class, 'store']);
});