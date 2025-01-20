<?php

use App\Http\Controllers\ClientesSucursalesController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    Route::get('/cliente_sucursal/recursos', [ClientesSucursalesController::class, 'getRecursos']);
    Route::put('/cliente_sucursal/restaurar/{id}', [ClientesSucursalesController::class, 'restaurar']);
    Route::resource("cliente_sucursal",ClientesSucursalesController::class)->except(['create','edit']);
});