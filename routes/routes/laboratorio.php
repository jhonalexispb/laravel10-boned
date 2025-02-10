<?php

use App\Http\Controllers\Configuration\LaboratorioController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {

    Route::get('/laboratorio/recursos', [LaboratorioController::class, 'getRecursos']);
    Route::get('/laboratorio/recurso_para_crear', [LaboratorioController::class, 'getRecursosParaCrear']);
    Route::post('/laboratorio/{id}', [LaboratorioController::class, 'update']);
    Route::put('/laboratorio/restaurar/{id}', [LaboratorioController::class, 'restaurar']);
    Route::post('/laboratorio/tool/import', [LaboratorioController::class, 'import_laboratorio']);
    Route::resource("laboratorio",LaboratorioController::class)->except(['update','create','edit']);

});