<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\JefeCerradaController;
use App\Http\Controllers\GuardiaController;
use App\Http\Controllers\JefeFamiliaController;
use App\Http\Controllers\FamiliarController;
use App\Http\Controllers\UserController;

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


Route::middleware(['auth.api'])->prefix('v1')->group(function () {

    // Rutas públicas autenticadas
    Route::get('/me', [UserController::class, 'me']);
    Route::get('/profile', [UserController::class, 'profile']);
});
