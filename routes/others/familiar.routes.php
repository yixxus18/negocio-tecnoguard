<?php

use App\Http\Controllers\FamiliarController;
use Illuminate\Support\Facades\Route;

// Rutas del Rol de Familiar
Route::middleware(['role:5'])->group(function () {
    Route::get('me', [FamiliarController::class, 'obtenerInformacionPersonal']);
    Route::put('me', [FamiliarController::class, 'actualizarInformacionPersonal']);
});