<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::prefix('/v1')->group(function () {
    // Autenticação
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/{username}', [UserController::class, 'getUserByUsername']);
        Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
        Route::put('/user', [UserController::class, 'update']);
        Route::delete('/user', [UserController::class, 'destroy']);
        Route::get('/user/me', [UserController::class, 'me']);
        Route::put('/user/password', [UserController::class, 'updatePassword']);
    });
});
