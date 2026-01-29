<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\RealmController;
use App\Http\Controllers\Admin\RealmExportController;
use App\Http\Controllers\Admin\RealmImportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserGroupController;
use App\Http\Controllers\Admin\UserRoleMappingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::prefix('realms')->group(function () {
        Route::get('/', [RealmController::class, 'index']);
        Route::post('/', [RealmController::class, 'store']);
        Route::post('/import', [RealmImportController::class, 'import']);
        Route::post('/import-directory', [RealmImportController::class, 'importDirectory']);
        
        Route::get('/{realm}', [RealmController::class, 'show']);
        Route::put('/{realm}', [RealmController::class, 'update']);
        Route::delete('/{realm}', [RealmController::class, 'destroy']);
        Route::get('/{realm}/export', [RealmExportController::class, 'export']);
        
        Route::prefix('/{realm}/users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
            
            Route::prefix('/{id}/role-mappings')->group(function () {
                Route::get('/', [UserRoleMappingController::class, 'index']);
                Route::post('/realm', [UserRoleMappingController::class, 'addRealmRoles']);
                Route::delete('/realm', [UserRoleMappingController::class, 'deleteRealmRoles']);
                Route::post('/clients/{clientId}', [UserRoleMappingController::class, 'addClientRoles']);
                Route::delete('/clients/{clientId}', [UserRoleMappingController::class, 'deleteClientRoles']);
            });
            
            Route::prefix('/{id}/groups')->group(function () {
                Route::get('/', [UserGroupController::class, 'index']);
                Route::post('/', [UserGroupController::class, 'store']);
                Route::delete('/{groupId}', [UserGroupController::class, 'destroy']);
            });
        });
        
        Route::prefix('/{realm}/clients')->group(function () {
            Route::get('/', [ClientController::class, 'index']);
            Route::post('/', [ClientController::class, 'store']);
            Route::get('/{id}', [ClientController::class, 'show']);
            Route::put('/{id}', [ClientController::class, 'update']);
            Route::delete('/{id}', [ClientController::class, 'destroy']);
            
            Route::prefix('/{id}/roles')->group(function () {
                Route::get('/', [RoleController::class, 'indexClient']);
                Route::post('/', [RoleController::class, 'storeClient']);
                Route::get('/{roleName}', [RoleController::class, 'showClient']);
                Route::delete('/{roleName}', [RoleController::class, 'destroyClient']);
            });
        });
        
        Route::prefix('/{realm}/roles')->group(function () {
            Route::get('/', [RoleController::class, 'indexRealm']);
            Route::post('/', [RoleController::class, 'storeRealm']);
            Route::get('/{roleName}', [RoleController::class, 'showRealm']);
            Route::delete('/{roleName}', [RoleController::class, 'destroyRealm']);
        });
        
        Route::prefix('/{realm}/groups')->group(function () {
            Route::get('/', [GroupController::class, 'index']);
            Route::post('/', [GroupController::class, 'store']);
            Route::get('/{id}', [GroupController::class, 'show']);
            Route::delete('/{id}', [GroupController::class, 'destroy']);
        });
    });
});
