<?php

use App\Http\Controllers\JefeCerradaController;
use Illuminate\Support\Facades\Route;



// Rutas del Rol de Jefe de Cerrada
Route::middleware(['role:2'])->group(function () {
    Route::get('familias', [JefeCerradaController::class, 'obtenerFamiliasCerrada']);
    Route::post('guardias/{cerradaId}', [JefeCerradaController::class, 'asignarGuardiaCerrada']);

    Route::get('guardias-libres', [JefeCerradaController::class, 'obtenerGuardiasDisponibles']);
    Route::delete('guardias/{userId}', [JefeCerradaController::class, 'desasignarGuardiaCerrada']);
    Route::get('guardias', [JefeCerradaController::class, 'obtenerGuardiasCerrada']);
    Route::post('pagos', [JefeCerradaController::class, 'procesarPagoFamilia']);

    Route::get('cerrada/config/{configId}', [JefeCerradaController::class, 'obtenerConfigPago']);
    Route::post('config-pagos', [JefeCerradaController::class,'crearConfigPago']);
    Route::post('config-pagos/{configId}', [JefeCerradaController::class,'updateConfigPago']);
});
