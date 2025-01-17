<?php

use App\Http\Controllers\configuration\bankController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    //banco
    Route::post('/banco/{id}', [bankController::class, 'update']);
    Route::resource("banco",bankController::class)->except(['update','create','edit']);
});