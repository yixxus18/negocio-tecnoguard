<?php

use App\Http\Controllers\JefeCerradaController;
use App\Http\Controllers\MembershipController;
use Illuminate\Support\Facades\Route;



// Rutas del Rol de Jefe de Cerrada
Route::middleware(['role:2'])->group(function () {
    Route::get('familias', [JefeCerradaController::class, 'obtenerFamiliasCerrada']);
    Route::post('guardias/{cerradaId}', [JefeCerradaController::class, 'asignarGuardiaCerrada']);

    
     Route::get('/obtenerusuariosmicerrada',[JefeCerradaController::class,'obtenerusuariosmicerrada']);
    Route::get('/obtenerpagosdemicerrada',[JefeCerradaController::class,'obtenerpagosdemicerrada']);

    // Route::get('guardias-libres', [JefeCerradaController::class, 'obtenerGuardiasDisponibles']);
    Route::delete('guardias/{userId}', [JefeCerradaController::class, 'desasignarGuardiaCerrada']);
    Route::get('guardias', [JefeCerradaController::class, 'obtenerGuardiasCerrada']);
    Route::get('obtenerguardiaslibres', [JefeCerradaController::class, 'obtenerguardiaslibres']);
     Route::post('/cambioguardia/{guardiaId}', [JefeCerradaController::class, 'cambioguardia']);
    
    Route::get('obtenerallguardiasdemicerrada', [JefeCerradaController::class, 'obtenerallguardiasdemicerrada']);
    Route::post('/AprobarMembershipDetail/{detailId}',[MembershipController::class,'AprobarMembershipDetail']);
      Route::post('/RechazarMembershipDetail/{detailId}',[MembershipController::class,'RechazarMembershipDetail']);

    Route::get('cerrada/config/{configId}', [JefeCerradaController::class, 'obtenerConfigPago']);
    Route::post('config-pagos', [JefeCerradaController::class,'crearConfigPago']);
    Route::post('config-pagos/{configId}', [JefeCerradaController::class,'updateConfigPago']);
});
