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
    Route::apiResource('/role', RoleController::class);
    Route::apiResource('/user',UserController::class);
    Route::apiResource('/producer', ProducerController::class);
    Route::apiResource('/variable',VariableController::class);
    Route::apiResource('/estanque',EstanqueController::class);
    Route::apiResource('/register',RegisterController::class);
    Route::apiResource('/roleuser', RoleUserController::class);
    Route::get('/getProducer',[UserController::class,'getProducer']);
    Route::get('/getProducerUserId/{id}',[ProducerController::class,'getProducerUserId']);
});

Route::post ('/login',[AuthController::class,'login']);



