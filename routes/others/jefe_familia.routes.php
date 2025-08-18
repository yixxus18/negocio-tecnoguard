<?php

use App\Http\Controllers\JefeFamiliaController;
use App\Http\Controllers\SolicitudCambioController;
use App\Http\Controllers\TokensController;
use App\Models\SolicitudCambioCerrada;
use Illuminate\Support\Facades\Route;


// Rutas del Rol de Jefe de Familia
Route::middleware(['role:4'])->group(function () {

    Route::post('family-members', [JefeFamiliaController::class, 'agregarMiembroFamiliausuarioyaexistente']);
     Route::post('add-members', [JefeFamiliaController::class, 'agregarMiembroFamilia']);
    Route::get('family-members', [JefeFamiliaController::class, 'obtenerMiembrosFamilia']);
    Route::delete('family-members/{member_id}', [JefeFamiliaController::class, 'eliminarMiembroFamilia']);
    Route::get('account-status', [JefeFamiliaController::class, 'consultarSaldoEstado']);
    Route::get('my-family/membership', [JefeFamiliaController::class, 'obtenerHistorialMembresia']);
    Route::post('pagos', [JefeFamiliaController::class, 'procesarPagoFamilia']);
        Route::get('/misSolicitudes', [SolicitudCambioController::class, 'misSolicitudes']);
    Route::post('/crearSolicitud', [SolicitudCambioController::class, 'crearSolicitud']);
    Route::get('tokensmifamilia',[JefeFamiliaController::class,'TokensFamiliares']);
    Route::get('/miscerradascambio',[JefeFamiliaController::class,'cerradasExcluyendoMiCerrada']);
    Route::get('/dashboardjefefamilia',[JefeFamiliaController::class,'dashboardjefefamilia']);
    
     Route::get('/cerradasExceptoMiFamilia', [SolicitudCambioController::class, 'cerradasExceptoMiFamilia']);
         Route::post('/desactivarMiembroFamilia/{member_id}', [JefeFamiliaController::class, 'desactivarMiembroFamilia']);
             Route::post('/activarMiembroFamilia/{member_id}', [JefeFamiliaController::class, 'activarMiembroFamilia']);
});

Route::middleware('role:4,5')->group(function () {
    Route::post('token', [TokensController::class, 'generarTokenAcceso']);
    Route::get('tokens', [TokensController::class, 'obtenerTokens']);
});