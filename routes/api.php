<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TareasController;
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

// Restringir explícitamente las rutas centrales a los dominios centrales para que
// no capturen requests de dominios tenant (tenant2.localhost, etc.).
foreach (['localhost', '127.0.0.1'] as $centralDomain) {
    Route::domain($centralDomain)->group(function () {
        // Rutas públicas (login & register) que emiten token en la BD central
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);

        // Rutas protegidas con token Sanctum
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/user', function (Request $request) {
                return $request->user();
            });
            Route::post('/logout', [AuthController::class, 'logout']);

            Route::prefix('usuarios')->group(function () {
                Route::get('/listUsers', [UsuarioController::class, 'index']);
                Route::post('/addUser', [UsuarioController::class, 'store']);
                Route::get('/getUser/{user}', [UsuarioController::class, 'show'])->whereNumber('user');
                Route::put('/updateUser/{user}', [UsuarioController::class, 'update'])->whereNumber('user');
                Route::delete('/deleteUser/{user}', [UsuarioController::class, 'destroy'])->whereNumber('user');
            });

            Route::prefix('tareas')->group(function () {
                Route::get('/listTasks', [TareasController::class, 'index']);
                Route::post('/addTask', [TareasController::class, 'store']);
                Route::get('/getTask/{task}', [TareasController::class, 'showCentral'])->whereNumber('task');
                Route::put('/updateTask/{task}', [TareasController::class, 'updateCentral'])->whereNumber('task');
                Route::delete('/deleteTask/{task}', [TareasController::class, 'destroyCentral'])->whereNumber('task');
            });
        });
    });
}
