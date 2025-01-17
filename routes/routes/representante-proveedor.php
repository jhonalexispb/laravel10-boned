<?php

use App\Http\Controllers\Configuration\RepresentanteProveedorController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    //representante proveedor
    Route::put('/representante_proveedor/restaurar/{id}', [RepresentanteProveedorController::class, 'restaurar']);
    Route::resource("representante_proveedor",RepresentanteProveedorController::class)->except(['create','edit']);
});