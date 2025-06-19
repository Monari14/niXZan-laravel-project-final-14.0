<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserSettingsController;

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

        Route::post('/user/{username}/follow', [UserController::class, 'follow']);
        Route::post('/user/{username}/unfollow', [UserController::class, 'unfollow']);
        Route::get('/user/{username}/followers', [UserController::class, 'followers']);
        Route::get('/user/{username}/following', [UserController::class, 'following']);

        Route::get('/notifications', [UserController::class, 'notifications']);
        Route::post('/notifications/{id}/read', [UserController::class, 'markNotificationAsRead']);

        Route::put('/user/privacy', [UserController::class, 'updatePrivacy']);

        Route::get('/posts', [PostController::class, 'index']);
        Route::post('/posts', [PostController::class, 'store']);
        Route::get('/posts/{id}', [PostController::class, 'show']);
        Route::delete('/posts/{id}', [PostController::class, 'destroy']);

        Route::post('/posts/{postId}/like', [PostController::class, 'like']);
        Route::post('/posts/{postId}/unlike', [PostController::class, 'unlike']);

        Route::get('/posts/feed', [PostController::class, 'feed']);

        Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
        Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
        Route::delete('/comments/{commentId}', [CommentController::class, 'destroy']);

        Route::post('/messages/{username}', [MessageController::class, 'send']);
        Route::get('/messages/{username}', [MessageController::class, 'conversation']);
        Route::post('/messages/{id}/read', [MessageController::class, 'markAsRead']);

        Route::get('/user/settings', [UserSettingsController::class, 'show']);
        Route::put('/user/settings', [UserSettingsController::class, 'update']);
    });
});
