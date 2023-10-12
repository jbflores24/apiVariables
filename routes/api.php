<?php

use App\Http\Controllers\EstanqueController;
use App\Http\Controllers\ProducerController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VariableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::apiResource('/role', RoleController::class);
Route::apiResource('/user',UserController::class);
Route::apiResource('/producer', ProducerController::class);
Route::apiResource('/variable',VariableController::class);
Route::apiResource('/estanque',EstanqueController::class);

