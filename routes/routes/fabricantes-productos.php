<?php

use App\Http\Controllers\Configuration\FabricanteProductoController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    Route::get('/fabricante_productos/recursos', [FabricanteProductoController::class, 'getRecursos']);
    Route::post('/fabricante_productos/{id}', [FabricanteProductoController::class, 'update']);
    Route::put('/fabricante_productos/restaurar/{id}', [FabricanteProductoController::class, 'restaurar']);
    Route::resource("fabricante_productos",FabricanteProductoController::class)->except(['create','edit','update']);
});