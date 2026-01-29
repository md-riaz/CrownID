<?php

use App\Http\Controllers\Oidc\OidcController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web', \App\Http\Middleware\RealmExists::class])
    ->prefix('realms/{realm}')
    ->group(function () {
        Route::get('/.well-known/openid-configuration', [OidcController::class, 'discovery']);
        
        Route::prefix('protocol/openid-connect')->group(function () {
            Route::get('/certs', [OidcController::class, 'certs']);
            Route::get('/auth', [OidcController::class, 'authorize']);
            Route::post('/auth/login', [OidcController::class, 'login']);
            Route::post('/token', [OidcController::class, 'token']);
            Route::get('/userinfo', [OidcController::class, 'userinfo']);
            Route::post('/userinfo', [OidcController::class, 'userinfo']);
            Route::get('/logout', [OidcController::class, 'logout']);
            Route::post('/logout', [OidcController::class, 'logout']);
        });
    });
