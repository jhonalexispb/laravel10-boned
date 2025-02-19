<?php

use App\Http\Controllers\Configuration\ProveedorController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {

    Route::put('/proveedor/restaurar/{id}', [ProveedorController::class, 'restaurar']);
    Route::get('/proveedor/recursos',[ProveedorController::class, 'getRecursos']);
    Route::get('/proveedor/recursos/laboratorios',[ProveedorController::class, 'getLaboratorios']);
    Route::post('/proveedor/recursos/create_relacion_laboratorio_proveedor',[ProveedorController::class, 'registerLaboratorioProveedor']);
    Route::put('/proveedor/recursos/update_relacion_laboratorio_proveedor/{id}',[ProveedorController::class, 'updateLaboratorioProveedor']);
    Route::delete('/proveedor/recursos/relacion_laboratorio_proveedor/{id}',[ProveedorController::class, 'deleteLaboratorioProveedor']);
    Route::resource("proveedor",ProveedorController::class)->except(['create','edit']);

});