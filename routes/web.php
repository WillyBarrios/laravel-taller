<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Ruta directa a /login para la SPA (central domains)
Route::view('/login', 'app');

// Fallback SPA para cualquier ruta no-API (central)
Route::view('/{any}', 'app')->where('any', '^(?!api).*$');

