<?php

use App\Http\Controllers\Configuration\CategoriaProducto;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {

    //CATEGORIAS PRODUCTOS
    Route::post('/producto_categoria/{id}', [CategoriaProducto::class, 'update']);
    Route::put('/producto_categoria/restaurar/{id}', [CategoriaProducto::class, 'restaurar']);
    Route::resource("producto_categoria",CategoriaProducto::class)->except(['update','create','edit']);

});
