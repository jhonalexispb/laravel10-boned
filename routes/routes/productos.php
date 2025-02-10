<?php

use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],

], function () {
    Route::get('/productos/recursos', [ProductoController::class, 'getRecursos']);
    Route::get('/productos/recursos_para_crear', [ProductoController::class, 'getRecursosParaCrear']);
    Route::put('/productos/restaurar/{id}', [ProductoController::class, 'restaurar']);
    Route::post('/productos/index', [ProductoController::class, 'index']);
    Route::post('/productos/import', [ProductoController::class, 'import_product']);
    Route::resource("productos",ProductoController::class)->except(['create','edit','index']);
});