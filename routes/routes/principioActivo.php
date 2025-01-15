<?php

use App\Http\Controllers\Configuration\PrincipioActivoController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],

], function () {
    Route::get('/principio_activo/recursos', [PrincipioActivoController::class, 'getRecursos']);
    Route::put('/principio_activo/restaurar/{id}', [PrincipioActivoController::class, 'restaurar']);
    Route::resource("principio_activo",PrincipioActivoController::class)->except(['create','edit']);
});