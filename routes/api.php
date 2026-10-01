<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EstanqueController;
use App\Http\Controllers\ProducerController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RoleUserController;
use App\Http\Controllers\VariableController;

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

Route::middleware('auth:sanctum')->group( function(){
    // Sesión del usuario
    Route::get('/me',[AuthController::class,'me']);
    Route::put('/me/password',[AuthController::class,'cambiarPassword']);
    Route::post('/logout',[AuthController::class,'logout']);

    // Lectura disponible para cualquier usuario autenticado
    // (un productor solo ve su propia información)
    Route::apiResource('/producer', ProducerController::class)->only(['index','show']);
    Route::apiResource('/variable',VariableController::class)->only(['index','show']);
    Route::apiResource('/estanque',EstanqueController::class)->only(['index','show']);
    Route::get('/getProducerUserId/{id}',[ProducerController::class,'getProducerUserId']);

    // Registros de mediciones: el usuario se toma del token y solo el autor
    // (o un administrador) puede editar o eliminar un registro
    Route::get('/estadisticas',[RegisterController::class,'estadisticas']);
    Route::post('/register/lote',[RegisterController::class,'lote']);
    Route::apiResource('/register',RegisterController::class);

    // Administración: solo el rol Administrador
    Route::middleware('role:Administrador')->group(function(){
        Route::apiResource('/role', RoleController::class);
        Route::apiResource('/user',UserController::class);
        Route::apiResource('/roleuser', RoleUserController::class);
        Route::apiResource('/producer', ProducerController::class)->except(['index','show']);
        Route::apiResource('/variable',VariableController::class)->except(['index','show']);
        Route::apiResource('/estanque',EstanqueController::class)->except(['index','show']);
        Route::get('/getProducer',[UserController::class,'getProducer']);
    });
});

Route::post ('/login',[AuthController::class,'login'])->middleware('throttle:10,1');
