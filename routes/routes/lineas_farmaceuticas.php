<?php

use App\Http\Controllers\Configuration\LineaFarmaceuticaController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    //banco
    Route::post('/lineas_farmaceuticas/{id}', [LineaFarmaceuticaController::class, 'update']);
    Route::put('/lineas_farmaceuticas/restaurar/{id}', [LineaFarmaceuticaController::class, 'restaurar']);
    Route::resource("lineas_farmaceuticas",LineaFarmaceuticaController::class)->except(['update','create','edit']);
});