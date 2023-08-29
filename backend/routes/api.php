<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/task', [TaskController::class, 'getAll']);

    Route::get('/task/{id}', [TaskController::class, 'getById']);

    Route::put('/task/{id}', [TaskController::class, 'update']);

    Route::post('/task', [TaskController::class, 'create']);

    Route::delete('/task', [TaskController::class, 'delete']);

    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'login']);