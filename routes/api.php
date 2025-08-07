<?php

use App\Http\Controllers\CameraController;
use App\Http\Controllers\PuertasController;
use App\Services\FileService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Http;

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


Route::prefix('v1')->group(function () {

    // Rutas públicas autenticadas
    Route::middleware(['auth.api'])->group(function () {
        Route::get('/me', [UserController::class, 'me']);
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('puerta', [PuertasController::class, 'openDoor']);
    });

    Route::post('imagenes', [CameraController::class, 'imagenes']);
});


