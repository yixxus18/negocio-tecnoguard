<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CerradasController;
use App\Http\Controllers\TecnicoController;
use Illuminate\Support\Facades\Route;

// Rutas del Rol de Administrador

Route::middleware(['role:1'])->group(function () {
    Route::resource('users', AdminUserController::class)->except(['edit', 'create', 'store']);
    Route::resource('config-pagos', AdminController::class)->except(['edit', 'create']);
    Route::resource('cerradas', CerradasController::class)->except(['edit', 'create', 'destroy']);
        Route::get('/configurationspaydate', [CerradasController::class, 'getConfigurations']);
        Route::post('/crearActividadcontecnico',[TecnicoController::class,'crearActividadcontecnico']);


    Route::post('/generartecnico',[AdminController::class,'creartecnico']);
    Route::get('/ObtenerTecnico',[AdminController::class,'ObtenerTecnico']);
    Route::get('/ObtenerBitacora',[AdminController::class,'ObtenerBitacora']);
     Route::post('/cambiarresponsableactividad',[AdminController::class,'cambiarresponsableactividad']);
    Route::get('/newactivityfromtecnichianregisters',[TecnicoController::class,'newactivityfromtecnichian']);

    Route::get('getguardias', [AdminController::class, 'obtenerGuardiasDisponibles']);
    Route::get('/getEarningsByCerrada',[AdminController::class,'getEarningsByCerrada']);
    Route::post('/crearUsuarioAdministrativo',[AdminController::class, 'crearUsuarioAdministrativo']);
    Route::get('/dashboardadmin',[AdminUserController::class,'dashboardadmin']);
     Route::get('/obtenercolaboradores',[AdminUserController::class,'getcolaborators']);
    Route::post('cerradas/{id}/asignar-jefe', [CerradasController::class,'setJefeDeCerrada']);
    Route::post('cerradas/set-localidad/{id}', [CerradasController::class,'asociarLocalidad']);
    Route::post('cerradas/unset-localidad/{cerradaId}/{localidadId}', [CerradasController::class,'desasociarLocalidad']);
});

Route::middleware(['role:1,2'])->group(function () {
    Route::get('obtenermiscerradasadministradas',[AdminUserController::class, 'obtenermiscerradasadministradas']);
    Route::post('users',[AdminUserController::class, 'store']);
});