<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompuestoController;
use App\Http\Controllers\DensidadController;
use App\Http\Controllers\DictamenController;
use \App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EventoController;
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

Route::post('json/v3/empresas/{idEmpresa}/fecha/{fecha}/json/{tipo}/unidad/{unidad}', [EmpresaController::class, 'crearJsonV1'])->middleware('auth:sanctum');
/* Route::resource('empresas', EmpresaController::class)->middleware(['auth:api', 'admin']); */
Route::resource('empresas', EmpresaController::class)->middleware(['auth:sanctum']);
Route::resource('archivos', ArchivoController::class)->middleware(['auth:sanctum']);
Route::resource('bitacora', BitacoraController::class)->middleware(['auth:sanctum']);
Route::resource('clientes', ClienteController::class)->middleware(['auth:sanctum']);
Route::resource('compuestos', CompuestoController::class)->middleware(['auth:sanctum']);
Route::resource('densidades', DensidadController::class)->middleware(['auth:sanctum']);
Route::resource('dictamenes', DictamenController::class)->middleware(['auth:sanctum']);
Route::resource('eventos', EventoController::class)->middleware(['auth:sanctum']);

Route::group([
    'prefix' => 'users'
], function () {
    //Public access routes
    Route::post('signup', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('updatePassword/{idUser}', [UserController::class, 'updatePassword']);
    Route::post('recuperarPassword/', [UserController::class, 'recoveryPassword']);
    /* Route::post('activarCuenta', [UserController::class, 'activateAcount']);
    Route::post('loginFallido', [UserController::class, 'registerFailAuth']); */
    //

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