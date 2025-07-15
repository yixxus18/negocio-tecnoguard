<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;

// Rutas del Rol de Administrador
Route::middleware(['role:1'])->group(function () {
    Route::resource('users', AdminUserController::class)->except(['edit', 'create']);
    Route::resource('config-pagos', AdminController::class)->except(['edit', 'create']);
    Route::resource('cerradas', AdminController::class)->except(['edit', 'create']);
});