<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComprobantePagoController;
use App\Http\Controllers\configuration\bankController;
use App\Http\Controllers\configuration\lugarEntregaController;
use App\Http\Controllers\configuration\methodPaymentController;
use App\Http\Controllers\Configuration\SucursaleController;
use App\Http\Controllers\Configuration\WarehouseController;
use App\Http\Controllers\UserAccessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolPermissionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
 
    //'middleware' => 'auth:api',
    'prefix' => 'auth',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
});

Route::group([
 
    'middleware' => 'auth:api',
    //'middleware' => ['auth:api','permission:publish articles|edit articles'],
 
], function ($router) {
    Route::resource("roles",RolPermissionController::class)->except(['create','edit']);
    Route::post('/users/{id}', [UserAccessController::class, 'update']);
    Route::get("users/config", [UserAccessController::class, 'config']);
    Route::resource("users",UserAccessController::class)->except(['update','create','edit']);

    Route::resource("sucursales",SucursaleController::class)->except(['create','edit']);
    Route::resource("warehouses",WarehouseController::class)->except(['create','edit']);
    Route::resource("lugar_entrega",lugarEntregaController::class)->except(['create','edit']);
    Route::resource("metodo_pago",methodPaymentController::class)->only(['index', 'show', 'update']);
    Route::post('/banco/{id}', [bankController::class, 'update']);
    Route::resource("banco",bankController::class)->except(['update','create','edit']);
    Route::resource("comprobante",ComprobantePagoController::class)->except(['create','edit']);
});
