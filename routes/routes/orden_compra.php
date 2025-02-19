<?php

use App\Http\Controllers\OrdenCompraController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    //banco
    Route::get('/orden_compra/recursos_crear', [OrdenCompraController::class, 'getRecursosParaCrear']);
    Route::post('/orden_compra/recursos_crear/productos', [OrdenCompraController::class, 'getProductosByLaboratorio']);
});