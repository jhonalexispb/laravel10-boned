<?php

use App\Http\Controllers\configuration\ComprobantePagoController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    Route::resource("comprobante",ComprobantePagoController::class)->except(['create','edit']);
});