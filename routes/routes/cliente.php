<?php

use App\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {

    Route::put('/cliente/restaurar/{id}', [ClienteController::class, 'restaurar']);
    Route::resource("cliente",ClienteController::class)->except(['create','edit']);

});