<?php

use App\Http\Controllers\configuration\bankController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    //banco
    Route::post('/lineas_farmaceuticas/{id}', [bankController::class, 'update']);
    Route::put('/lineas_farmaceuticas/restaurar/{id}', [bankController::class, 'restaurar']);
    Route::resource("lineas_farmaceuticas",bankController::class)->except(['update','create','edit']);
});