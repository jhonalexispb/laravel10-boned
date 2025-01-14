<?php

use App\Http\Controllers\Configuration\ProveedorController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {

    Route::put('/proveedor/restaurar/{id}', [ProveedorController::class, 'restaurar']);
    Route::get('/proveedor/recursos',[ProveedorController::class, 'getRecursos']);
    Route::resource("proveedor",ProveedorController::class)->except(['create','edit']);

});