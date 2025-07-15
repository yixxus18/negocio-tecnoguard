<?php

use App\Http\Controllers\JefeCerradaController;
use Illuminate\Support\Facades\Route;



// Rutas del Rol de Jefe de Cerrada
    Route::middleware(['role:2'])->group(function () {
        Route::get('familias', [JefeCerradaController::class, 'obtenerFamiliasCerrada']);
        Route::post('guardia', [JefeCerradaController::class, 'asignarGuardiaCerrada']);
        Route::get('guardia', [JefeCerradaController::class, 'obtenerGuardiasCerrada']);
        Route::post('pagos', [JefeCerradaController::class, 'procesarPagoFamilia']);
    });
