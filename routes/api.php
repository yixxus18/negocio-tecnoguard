<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\JefeCerradaController;
use App\Http\Controllers\GuardiaController;
use App\Http\Controllers\JefeFamiliaController;
use App\Http\Controllers\FamiliarController;
use App\Http\Controllers\UserController;

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

// Rutas públicas (sin autenticación)
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'message' => 'API funcionando correctamente']);
});



Route::prefix('v1')->group(function () {
    // Rutas de usuario autenticado (sin verificación de rol)
    Route::middleware(['auth.passport'])->group(function () {
        Route::get('/me', [UserController::class, 'me']);
        Route::get('/profile', [UserController::class, 'profile']);
    });

    // Rutas del Rol de Administrador
    Route::middleware(['auth.passport', 'role:1'])->group(function () {

        // Rutas de Usuarios, Cerradas y Configuración de pagos (CRUD completo)
        Route::resource('users', AdminUserController::class)->except(['edit', 'create']);
        Route::resource('config-pagos', AdminController::class)->except(['edit', 'create']);
        Route::resource('cerradas', AdminController::class)->except(['edit', 'create']);
    });

    // Rutas del Rol de Jefe de Cerrada
    Route::middleware(['auth.passport', 'role:2'])->prefix('jefe-cerrada')->group(function () {
        Route::get('familias', [JefeCerradaController::class, 'obtenerFamiliasCerrada']);
        Route::post('guardia', [JefeCerradaController::class, 'asignarGuardiaCerrada']);
        Route::get('guardia', [JefeCerradaController::class, 'obtenerGuardiasCerrada']);
        Route::post('pagos', [JefeCerradaController::class, 'procesarPagoFamilia']);
    });

    // Rutas del Rol de Guardia
    Route::middleware(['auth.passport', 'role:3'])->prefix('guardia')->group(function () {
        Route::get('access-logs', [GuardiaController::class, 'obtenerLogsAcceso']);
        Route::get('tokens', [GuardiaController::class, 'obtenerTokensActivos']);
        Route::post('tokens/service', [GuardiaController::class, 'crearTokenServicio']);
    });

    // Rutas del Rol de Jefe de Familia
    Route::middleware(['auth.passport', 'role:4'])->prefix('propietario')->group(function () {
        Route::post('tokens', [JefeFamiliaController::class, 'generarTokenAcceso']);
        Route::post('family-members', [JefeFamiliaController::class, 'agregarMiembroFamilia']);
        Route::get('family-members', [JefeFamiliaController::class, 'obtenerMiembrosFamilia']);
        Route::delete('family-members/{member_id}', [JefeFamiliaController::class, 'eliminarMiembroFamilia']);
        Route::get('account-status', [JefeFamiliaController::class, 'consultarSaldoEstado']);
        Route::get('my-family/membership', [JefeFamiliaController::class, 'obtenerHistorialMembresia']);
    });

    // Rutas del Rol de Familiar
    Route::middleware(['auth.passport', 'role:5'])->prefix('familiar')->group(function () {
        Route::get('me', [FamiliarController::class, 'obtenerInformacionPersonal']);
        Route::put('me', [FamiliarController::class, 'actualizarInformacionPersonal']);
    });

});