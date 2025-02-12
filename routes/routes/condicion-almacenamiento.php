<?php

use App\Http\Controllers\Configuration\CondicionAlmacenamiento;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {

    //CATEGORIAS PRODUCTOS
    Route::put('/condicion_almacenamiento/restaurar/{id}', [CondicionAlmacenamiento::class, 'restaurar']);
    Route::resource("condicion_almacenamiento",CondicionAlmacenamiento::class)->except(['create','edit']);
});