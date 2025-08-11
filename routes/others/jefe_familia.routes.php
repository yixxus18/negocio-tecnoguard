<?php

use App\Http\Controllers\JefeFamiliaController;
use App\Http\Controllers\TokensController;
use Illuminate\Support\Facades\Route;


// Rutas del Rol de Jefe de Familia
Route::middleware(['role:4'])->group(function () {

    Route::post('family-members', [JefeFamiliaController::class, 'agregarMiembroFamilia']);
    Route::get('family-members', [JefeFamiliaController::class, 'obtenerMiembrosFamilia']);
    Route::delete('family-members/{member_id}', [JefeFamiliaController::class, 'eliminarMiembroFamilia']);
    Route::get('account-status', [JefeFamiliaController::class, 'consultarSaldoEstado']);
    Route::get('my-family/membership', [JefeFamiliaController::class, 'obtenerHistorialMembresia']);
    Route::post('pagos', [JefeFamiliaController::class, 'procesarPagoFamilia']);

});

Route::middleware('role:4,5')->group(function () {
    Route::post('token', [TokensController::class, 'generarTokenAcceso']);
    Route::get('tokens', [TokensController::class, 'obtenerTokens']);
});