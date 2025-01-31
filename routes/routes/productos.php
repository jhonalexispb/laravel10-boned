<?php

use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],

], function () {
    Route::get('/productos/recursos', [ProductoController::class, 'getRecursos']);
    Route::put('/productos/restaurar/{id}', [ProductoController::class, 'restaurar']);
    Route::resource("productos",ProductoController::class)->except(['create','edit']);
});