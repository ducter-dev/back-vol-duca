<?php

use App\Http\Controllers\AlmacenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\ArchivoMensualController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompuestoController;
use App\Http\Controllers\DensidadController;
use App\Http\Controllers\DictamenController;
use \App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\RolController;
use \App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('json/v3', [EmpresaController::class, 'crearJsonV1'])->middleware('auth:sanctum');
Route::post('json/v3/mensual', [EmpresaController::class, 'crearJsonMensualV1'])->middleware('auth:sanctum');
Route::post('restore/{role}', [RolController::class, 'restoreRol'])->middleware(['auth:sanctum']);
/* Route::resource('empresas', EmpresaController::class)->middleware(['auth:api', 'admin']); */
Route::resource('empresas', EmpresaController::class)->middleware(['auth:sanctum']);
Route::resource('balances', BalanceController::class)->middleware(['auth:sanctum']);
Route::resource('archivos', ArchivoController::class)->middleware(['auth:sanctum']);
Route::resource('archivosMensuales', ArchivoMensualController::class)->middleware('auth:sanctum');
Route::resource('bitacora', BitacoraController::class)->middleware(['auth:sanctum']);
Route::resource('clientes', ClienteController::class)->middleware(['auth:sanctum']);
Route::resource('compuestos', CompuestoController::class)->middleware(['auth:sanctum']);
Route::resource('densidades', DensidadController::class)->middleware(['auth:sanctum']);
Route::resource('dictamenes', DictamenController::class)->middleware(['auth:sanctum']);
Route::resource('eventos', EventoController::class)->middleware(['auth:sanctum']);
Route::resource('productos', ProductoController::class)->middleware(['auth:sanctum']);
Route::resource('roles', RolController::class)->middleware(['auth:sanctum']);
Route::resource('permisos', PermisoController::class)->middleware(['auth:sanctum']);
Route::resource('revisiones', RevisionController::class)->middleware(['auth:sanctum']);
Route::get('bitacora/fecha/{fecha}', [BitacoraController::class, 'filtrarFecha'])->middleware('auth:sanctum');
Route::get('dictamenes/fecha/{fecha}', [DictamenController::class, 'filtrarFecha'])->middleware('auth:sanctum');

Route::get('errores', [BitacoraController::class, 'filtrarErrores'])->middleware('auth:sanctum');
Route::get('permisos-all', [PermisoController::class, 'all'])->middleware('auth:sanctum');
Route::get('productos-all', [ProductoController::class, 'all'])->middleware('auth:sanctum');
Route::get('compuestos-all', [CompuestoController::class, 'all'])->middleware('auth:sanctum');
Route::get('perfiles-all', [RolController::class, 'all'])->middleware('auth:sanctum');
Route::post('productos-compuestos', [ProductoController::class, 'attachCompuestos'])->middleware(['auth:sanctum']);
Route::get('json/fecha/{fecha}', [EmpresaController::class, 'checkDataExperion'])->middleware('auth:sanctum');

// Errores
Route::get('errores', [BitacoraController::class, 'filtrarErrores'])->middleware('auth:sanctum');

// Prestamos
Route::get('prestamos', [PrestamoController::class, 'index'])->middleware('auth:sanctum');
Route::post('prestamos', [PrestamoController::class, 'store'])->middleware('auth:sanctum');
Route::get('prestamos/{id_prestamo}', [PrestamoController::class, 'show'])->middleware('auth:sanctum');
Route::put('prestamos/{id_prestamo}', [PrestamoController::class, 'update'])->middleware('auth:sanctum');
Route::delete('prestamos/{id_prestamo}', [PrestamoController::class, 'destroy'])->middleware('auth:sanctum');

// Almacén
Route::get('almacenes', [AlmacenController::class, 'index'])->middleware('auth:sanctum');
Route::post('almacenes', [AlmacenController::class, 'store'])->middleware('auth:sanctum');
Route::get('almacenes/{id_almacen}', [AlmacenController::class, 'show'])->middleware('auth:sanctum');
Route::put('almacenes/{id_almacen}', [AlmacenController::class, 'update'])->middleware('auth:sanctum');
Route::delete('almacenes/{id_almacen}', [AlmacenController::class, 'destroy'])->middleware('auth:sanctum');

Route::group([
    'prefix' => 'users'
], function () {
    //Public access routes
    Route::post('signup', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    
    Route::post('recuperarPassword', [UserController::class, 'recoveryPassword']);
    Route::get('activar-cuenta/{token}', [UserController::class, 'activarCuenta']);
    Route::post('bloquear-usuario', [UserController::class, 'bloquearUsuario']);
    Route::post('updatePassword', [UserController::class, 'updatePassword']);

    // Access only for logged in users
    Route::group([
        'middleware' => 'auth:sanctum'
    ], function() {
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('delete/{id}', [UserController::class, 'destroy']);
        Route::get('/', [UserController::class, 'index']);
        Route::post('logout', [UserController::class, 'logout']);
        Route::get('/{idUser}', [UserController::class, 'show']);
        Route::delete('/{id}', [UserController::class, 'destroy']);

    });

});