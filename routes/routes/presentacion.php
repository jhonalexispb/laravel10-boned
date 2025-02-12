<?php

use App\Http\Controllers\Configuration\PresentacionController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {

    //CATEGORIAS PRODUCTOS
    Route::put('/producto_presentacion/restaurar/{id}', [PresentacionController::class, 'restaurar']);
    Route::resource("producto_presentacion",PresentacionController::class)->except(['create','edit']);

});