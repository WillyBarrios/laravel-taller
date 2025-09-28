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
        // SPA direct paths (tenant)
        Route::view('/login', 'app');
        // Fallback para cualquier ruta no api (en subdominio tenant)
        Route::view('/{any}', 'app')->where('any', '^(?!api).*$');
    });

    // Tenant-scoped API using tenant database
    Route::middleware([
        'api',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
    ])->prefix('api')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/user', function (\Illuminate\Http\Request $request) {
                return $request->user();
            });
            Route::post('/logout', [AuthController::class, 'logout']);

            Route::prefix('usuarios')->group(function () {
                Route::get('/listUsers', [UsuarioController::class, 'index']);
                Route::post('/addUser', [UsuarioController::class, 'store']);
                Route::get('/getUser/{user}', [UsuarioController::class, 'showTenant'])->whereNumber('user');
                Route::put('/updateUser/{user}', [UsuarioController::class, 'updateTenant'])->whereNumber('user');
                Route::delete('/deleteUser/{user}', [UsuarioController::class, 'destroyTenant'])->whereNumber('user');
            });

            Route::prefix('tareas')->group(function () {
                Route::get('/listTasks', [TareasController::class, 'index']);
                Route::post('/addTask', [TareasController::class, 'store']);
                Route::get('/getTask/{task}', [TareasController::class, 'show'])->whereNumber('task');
                Route::put('/updateTask/{task}', [TareasController::class, 'update'])->whereNumber('task');
                Route::delete('/deleteTask/{task}', [TareasController::class, 'destroy'])->whereNumber('task');
            });
        });
    });
});
