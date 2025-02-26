<?php

use App\Http\Controllers\ProductoCatalogoDigemid;
use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],

], function () {
    Route::get('/productos/recursos', [ProductoController::class, 'getRecursos']);
    Route::get('/productos/recursos_para_crear', [ProductoController::class, 'getRecursosParaCrear']);
    Route::get('/productos/obtener_codigo_digemid', [ProductoCatalogoDigemid::class, 'getCodigoDigemid']);
    Route::put('/productos/restaurar/{id}', [ProductoController::class, 'restaurar']);
    Route::post('/productos/index', [ProductoController::class, 'index']);
    Route::post('/productos/atributtes/images/update/{id}', [ProductoController::class, 'update_images']);
    
    Route::post('/productos/import/externos/catalogo_digemid', [ProductoCatalogoDigemid::class, 'import_catalogo_digemid']);
    Route::resource("productos",ProductoController::class)->except(['create','edit','index']);
});