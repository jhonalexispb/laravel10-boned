<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\configuration\ComprobantePagoController;
use App\Http\Controllers\configuration\bankController;
use App\Http\Controllers\Configuration\CategoriaProducto;
use App\Http\Controllers\Configuration\DepartamentoController;
use App\Http\Controllers\Configuration\DistritoController;
use App\Http\Controllers\Configuration\LaboratorioController;
use App\Http\Controllers\configuration\lugarEntregaController;
use App\Http\Controllers\configuration\methodPaymentController;
use App\Http\Controllers\Configuration\ProvinciaController;
use App\Http\Controllers\Configuration\RepresentanteProveedorController;
use App\Http\Controllers\Configuration\SucursaleController;
use App\Http\Controllers\Configuration\WarehouseController;
use App\Http\Controllers\UserAccessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolPermissionController;

require __DIR__.'/routes/proveedor.php';
require __DIR__.'/routes/laboratorio.php';
require __DIR__.'/routes/principioActivo.php';
require __DIR__.'/routes/categoria-producto.php';
require __DIR__.'/routes/cliente.php';
require __DIR__.'/routes/representante-proveedor.php';
require __DIR__.'/routes/geografia.php';
require __DIR__.'/routes/comprobante.php';
require __DIR__.'/routes/bancos.php';

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
});
