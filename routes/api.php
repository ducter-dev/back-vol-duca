<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use \App\Http\Controllers\EmpresaController;
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
/* Route::resource('empresas', EmpresaController::class)->middleware(['auth:api', 'admin']); */
Route::resource('empresas', EmpresaController::class)->middleware(['auth:api']);

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
        'middleware' => 'auth:api'
    ], function() {
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('delete/{id}', [UserController::class, 'destroy']);
        Route::get('/', [UserController::class, 'index']);
        Route::post('logout', [UserController::class, 'logout']);
        Route::get('/{idUser}', [UserController::class, 'show']);
        Route::delete('/{id}', [UserController::class, 'destroy']);

    });

});