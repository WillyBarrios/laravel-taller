<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\TareasController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// All tenant routes are constrained to subdomains like tenant2.localhost so they don't shadow central routes
Route::domain('{tenant}.localhost')->group(function () {
    // Simple landing to confirm tenancy context
    Route::middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
    ])->group(function () {
        Route::get('/', function () {
            return 'Tenant context OK. Current tenant id: ' . tenant('id');
        });
    });

    // Tenant-scoped API using tenant database
    Route::middleware([
        'api',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
    ])->prefix('api')->group(function () {
    // Public login for this tenant (issues Sanctum token stored in tenant DB)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (\Illuminate\Http\Request $request) {
            return $request->user();
        });
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::prefix('usuarios')->group(function () {
            Route::get('/listUsers', [UsuarioController::class, 'index']);
            Route::post('/addUser', [UsuarioController::class, 'store']);
            Route::get('/getUser/{id}', [UsuarioController::class, 'show']);
            Route::put('/updateUser/{id}', [UsuarioController::class, 'update']);
            Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']);
        });

        Route::prefix('tareas')->group(function () {
            Route::get('/listTasks', [TareasController::class, 'index']);
            Route::post('/addTask', [TareasController::class, 'store']);
            Route::get('/getTask/{id}', [TareasController::class, 'show']);
            Route::put('/updateTask/{id}', [TareasController::class, 'update']);
            Route::delete('/deleteTask/{id}', [TareasController::class, 'destroy']);
        });
        });
    });
});
