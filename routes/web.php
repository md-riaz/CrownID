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
            Route::get('/mfa', [OidcController::class, 'showMfaChallenge'])->name('oidc.mfa-challenge');
            Route::post('/mfa', [OidcController::class, 'verifyMfa']);
            Route::get('/required-action', [OidcController::class, 'showRequiredAction'])->name('oidc.required-action');
            Route::post('/required-action', [OidcController::class, 'completeRequiredAction']);
        });
    });

// Admin UI Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminUI\DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('realms', App\Http\Controllers\AdminUI\RealmManagementController::class);
    Route::resource('users', App\Http\Controllers\AdminUI\UserManagementController::class);
    Route::resource('clients', App\Http\Controllers\AdminUI\ClientManagementController::class);
});
