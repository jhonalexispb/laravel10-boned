<?php

use App\Http\Controllers\serviciosConsultaSunatReniecController;
use Illuminate\Support\Facades\Route;

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function () {
    Route::get('/servicio_consulta/get_razon_social', [serviciosConsultaSunatReniecController::class, 'getRazonSocial']);
    Route::get('/servicio_consulta/get_nombre_por_dni', [serviciosConsultaSunatReniecController::class, 'getNameByDNI']);
});
