<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\Configuration\TransportesOrdenVentaController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    Route::apiResource("transporte_orden_venta",TransportesOrdenVentaController::class);
});