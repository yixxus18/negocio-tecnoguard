<?php

use App\Http\Controllers\GuardiaController;
use Illuminate\Support\Facades\Route;


// Rutas del Rol de Guardia
Route::middleware(['role:3'])->group(function () {
    Route::get('access-logs', [GuardiaController::class, 'obtenerLogsAcceso']);
    Route::get('tokens', [GuardiaController::class, 'obtenerTokensActivos']);
    Route::post('tokens/service', [GuardiaController::class, 'crearTokenServicio']);
});