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
    
    // Realm Settings
    Route::prefix('realms/{realm}/settings')->name('realm-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\RealmSettingsController::class, 'show'])->name('index');
        Route::get('/general', [App\Http\Controllers\AdminUI\RealmSettingsController::class, 'general'])->name('general');
        Route::post('/general', [App\Http\Controllers\AdminUI\RealmSettingsController::class, 'updateGeneral']);
        Route::get('/login', [App\Http\Controllers\AdminUI\RealmSettingsController::class, 'login'])->name('login');
        Route::get('/tokens', [App\Http\Controllers\AdminUI\RealmSettingsController::class, 'tokens'])->name('tokens');
        Route::post('/tokens', [App\Http\Controllers\AdminUI\RealmSettingsController::class, 'updateTokens']);
        Route::get('/security', [App\Http\Controllers\AdminUI\RealmSettingsController::class, 'security'])->name('security');
        Route::post('/security', [App\Http\Controllers\AdminUI\RealmSettingsController::class, 'updateSecurity']);
    });
    
    // Client Details
    Route::prefix('clients/{client}/details')->name('client-details.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\ClientDetailsController::class, 'show'])->name('index');
        Route::get('/settings', [App\Http\Controllers\AdminUI\ClientDetailsController::class, 'settings'])->name('settings');
        Route::post('/settings', [App\Http\Controllers\AdminUI\ClientDetailsController::class, 'updateSettings']);
        Route::get('/credentials', [App\Http\Controllers\AdminUI\ClientDetailsController::class, 'credentials'])->name('credentials');
        Route::post('/credentials/regenerate', [App\Http\Controllers\AdminUI\ClientDetailsController::class, 'regenerateSecret'])->name('regenerate-secret');
        Route::get('/roles', [App\Http\Controllers\AdminUI\ClientDetailsController::class, 'roles'])->name('roles');
        Route::get('/sessions', [App\Http\Controllers\AdminUI\ClientDetailsController::class, 'sessions'])->name('sessions');
    });
    
    // User Details
    Route::prefix('users/{user}/details')->name('user-details.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'show'])->name('index');
        Route::get('/info', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'info'])->name('info');
        Route::post('/info', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'updateInfo']);
        Route::get('/credentials', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'credentials'])->name('credentials');
        Route::post('/credentials/set-password', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'setPassword'])->name('set-password');
        Route::get('/role-mappings', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'roleMappings'])->name('role-mappings');
        Route::post('/role-mappings', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'assignRole'])->name('assign-role');
        Route::delete('/role-mappings/{role}', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'removeRole'])->name('remove-role');
        Route::get('/groups', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'groups'])->name('groups');
        Route::post('/groups', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'joinGroup'])->name('join-group');
        Route::delete('/groups/{group}', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'leaveGroup'])->name('leave-group');
        Route::get('/sessions', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'sessions'])->name('sessions');
        Route::get('/required-actions', [App\Http\Controllers\AdminUI\UserDetailsController::class, 'requiredActions'])->name('required-actions');
    });
    
    // Groups Management
    Route::prefix('realms/{realm}/groups')->name('groups.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\GroupsManagementController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\AdminUI\GroupsManagementController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\AdminUI\GroupsManagementController::class, 'store'])->name('store');
        Route::get('/{group}/edit', [App\Http\Controllers\AdminUI\GroupsManagementController::class, 'edit'])->name('edit');
        Route::put('/{group}', [App\Http\Controllers\AdminUI\GroupsManagementController::class, 'update'])->name('update');
        Route::delete('/{group}', [App\Http\Controllers\AdminUI\GroupsManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{group}/roles', [App\Http\Controllers\AdminUI\GroupsManagementController::class, 'assignRole'])->name('assign-role');
        Route::delete('/{group}/roles/{role}', [App\Http\Controllers\AdminUI\GroupsManagementController::class, 'removeRole'])->name('remove-role');
    });
    
    // Roles Management
    Route::prefix('realms/{realm}/roles')->name('roles.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\RolesManagementController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\AdminUI\RolesManagementController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\AdminUI\RolesManagementController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [App\Http\Controllers\AdminUI\RolesManagementController::class, 'edit'])->name('edit');
        Route::put('/{role}', [App\Http\Controllers\AdminUI\RolesManagementController::class, 'update'])->name('update');
        Route::delete('/{role}', [App\Http\Controllers\AdminUI\RolesManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{role}/composites', [App\Http\Controllers\AdminUI\RolesManagementController::class, 'addComposite'])->name('add-composite');
        Route::delete('/{role}/composites/{compositeRole}', [App\Http\Controllers\AdminUI\RolesManagementController::class, 'removeComposite'])->name('remove-composite');
    });
    Route::get('realms/{realm}/clients/{client}/roles', [App\Http\Controllers\AdminUI\RolesManagementController::class, 'clientRoles'])->name('client-roles');
    
    // Sessions Management
    Route::prefix('realms/{realm}/sessions')->name('sessions.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\SessionsManagementController::class, 'index'])->name('index');
        Route::get('/{sessionId}', [App\Http\Controllers\AdminUI\SessionsManagementController::class, 'show'])->name('show');
        Route::delete('/{sessionId}', [App\Http\Controllers\AdminUI\SessionsManagementController::class, 'destroy'])->name('destroy');
        Route::post('/destroy-all', [App\Http\Controllers\AdminUI\SessionsManagementController::class, 'destroyAll'])->name('destroy-all');
    });
    
    // Events Management
    Route::prefix('realms/{realm}/events')->name('events.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\EventsManagementController::class, 'index'])->name('index');
        Route::get('/{event}', [App\Http\Controllers\AdminUI\EventsManagementController::class, 'show'])->name('show');
        Route::post('/clear', [App\Http\Controllers\AdminUI\EventsManagementController::class, 'clear'])->name('clear');
    });
    
    // Import/Export
    Route::prefix('realms/{realm}/import-export')->name('import-export.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\ImportExportController::class, 'index'])->name('index');
        Route::post('/export', [App\Http\Controllers\AdminUI\ImportExportController::class, 'export'])->name('export');
    });
    Route::get('import', [App\Http\Controllers\AdminUI\ImportExportController::class, 'importForm'])->name('import.form');
    Route::post('import', [App\Http\Controllers\AdminUI\ImportExportController::class, 'import'])->name('import.process');
});
