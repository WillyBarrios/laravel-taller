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

// Login público (emite token)
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas con token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas para el controlador de usuarios
    Route::prefix('usuarios')->group(function () {
        Route::get('/listUsers', [UsuarioController::class, 'index']);
        Route::post('/addUser', [UsuarioController::class, 'store']);
        Route::get('/getUser/{id}', [UsuarioController::class, 'show']);
        Route::put('/updateUser/{id}', [UsuarioController::class, 'update']);
        Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']);
    });

    // Rutas para el controlador de tareas
    Route::prefix('tareas')->group(function () {
        Route::get('/listTasks', [TareasController::class, 'index']);
        Route::post('/addTask', [TareasController::class, 'store']);
        Route::get('/getTask/{id}', [TareasController::class, 'show']);
        Route::put('/updateTask/{id}', [TareasController::class, 'update']);
        Route::delete('/deleteTask/{id}', [TareasController::class, 'destroy']);
    });
});
