<?php

use App\Http\Controllers\Configuration\DepartamentoController;
use App\Http\Controllers\Configuration\DistritoController;
use App\Http\Controllers\Configuration\ProvinciaController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    Route::post('/departamentos/{id}', [DepartamentoController::class, 'update']);
    Route::put('/departamentos/restaurar/{id}', [DepartamentoController::class, 'restaurar']);
    Route::resource("departamentos",DepartamentoController::class)->except(['update','create','edit']);

    //provincias
    Route::post('/provincias/{id}', [ProvinciaController::class, 'update']);
    Route::put('/provincias/restaurar/{id}', [ProvinciaController::class, 'restaurar']);
    Route::resource("provincias",ProvinciaController::class)->except(['update','create','edit']);

    //distritos
    Route::post('/distritos/{id}', [DistritoController::class, 'update']);
    Route::put('/distritos/restaurar/{id}', [DistritoController::class, 'restaurar']);
    Route::resource("distritos",DistritoController::class)->except(['update','create','edit']);
});