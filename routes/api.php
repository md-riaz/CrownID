<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\RealmController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::prefix('realms')->group(function () {
        Route::get('/', [RealmController::class, 'index']);
        Route::post('/', [RealmController::class, 'store']);
        
        Route::get('/{realm}', [RealmController::class, 'show']);
        Route::put('/{realm}', [RealmController::class, 'update']);
        Route::delete('/{realm}', [RealmController::class, 'destroy']);
        
        Route::prefix('/{realm}/users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });
        
        Route::prefix('/{realm}/clients')->group(function () {
            Route::get('/', [ClientController::class, 'index']);
            Route::post('/', [ClientController::class, 'store']);
            Route::get('/{id}', [ClientController::class, 'show']);
            Route::put('/{id}', [ClientController::class, 'update']);
            Route::delete('/{id}', [ClientController::class, 'destroy']);
        });
    });
});
