<?php

use App\Http\Controllers\ProductoAtributtes\ProductoGestionController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],

], function () {
    Route::put('atributtes/productos/gestionar/{id}', [ProductoGestionController::class, 'gestionar']);
});