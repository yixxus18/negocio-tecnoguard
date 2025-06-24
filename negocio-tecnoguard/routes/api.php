<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
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

// Rutas de usuario autenticado (sin verificación de rol)
Route::middleware(['auth.passport'])->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::get('/profile', [UserController::class, 'profile']);
});

// Rutas del Rol de Administrador
Route::middleware(['auth.passport', 'role:1'])->group(function () {
    Route::post('/v1/admin/cerradas', [AdminController::class, 'crearCerrada']);
    Route::get('/v1/admin/cerradas', [AdminController::class, 'obtenerTodasCerradas']);
    Route::put('/v1/admin/cerradas/{id_cerrada}', [AdminController::class, 'actualizarCerrada']);
    Route::post('/v1/admin/cerradas/{id_cerrada}/asignar-jefe', [AdminController::class, 'asignarJefeCerrada']);
    Route::post('/v1/admin/users', [AdminController::class, 'crearUsuarioAdministrativo']);
    Route::get('/v1/admin/users/{id}', [AdminController::class, 'obtenerDetallesUsuario']);
    Route::get('/v1/admin/config-pagos', [AdminController::class, 'listarConfiguracionesPagos']);
    Route::post('/v1/admin/config-pagos', [AdminController::class, 'crearConfiguracionPagos']);
    Route::get('/v1/admin/config-pagos/{id}', [AdminController::class, 'obtenerDetalleConfiguracion']);
    Route::put('/v1/admin/config-pagos/{id}', [AdminController::class, 'actualizarConfiguracionPagos']);
});

// Rutas del Rol de Jefe de Cerrada
Route::middleware(['auth.passport', 'role:2'])->group(function () {
    Route::get('/v1/jefe-cerrada/familias', [JefeCerradaController::class, 'obtenerFamiliasCerrada']);
    Route::post('/v1/jefe-cerrada/guardia', [JefeCerradaController::class, 'asignarGuardiaCerrada']);
    Route::get('/v1/jefe-cerrada/guardias', [JefeCerradaController::class, 'obtenerGuardiasCerrada']);
    Route::post('/v1/jefe-cerrada/pagos', [JefeCerradaController::class, 'procesarPagoFamilia']);
});

// Rutas del Rol de Guardia
Route::middleware(['auth.passport', 'role:3'])->group(function () {
    Route::get('/v1/guardia/access-logs', [GuardiaController::class, 'obtenerLogsAcceso']);
    Route::get('/v1/guardia/tokens', [GuardiaController::class, 'obtenerTokensActivos']);
    Route::post('/v1/guardia/tokens/service', [GuardiaController::class, 'crearTokenServicio']);
});

// Rutas del Rol de Jefe de Familia
Route::middleware(['auth.passport', 'role:4'])->group(function () {
    Route::post('/v1/propietario/tokens', [JefeFamiliaController::class, 'generarTokenAcceso']);
    Route::post('/v1/jefe-familia/family-members', [JefeFamiliaController::class, 'agregarMiembroFamilia']);
    Route::get('/v1/propietario/family-members', [JefeFamiliaController::class, 'obtenerMiembrosFamilia']);
    Route::delete('/v1/propietario/family-members/{member_id}', [JefeFamiliaController::class, 'eliminarMiembroFamilia']);
    Route::get('/v1/propietario/account-status', [JefeFamiliaController::class, 'consultarSaldoEstado']);
    Route::get('/v1/propietario/my-family/membership', [JefeFamiliaController::class, 'obtenerHistorialMembresia']);
});

// Rutas del Rol de Familiar
Route::middleware(['auth.passport', 'role:5'])->group(function () {
    Route::get('/v1/familiar/me', [FamiliarController::class, 'obtenerInformacionPersonal']);
    Route::put('/v1/familiar/me', [FamiliarController::class, 'actualizarInformacionPersonal']);
});

// Rutas de Tiempo Real (WebSockets)
Route::middleware(['auth.passport'])->group(function () {
    // Estas rutas son para WebSockets, pero en Laravel se manejarían con BeyondCode/LaravelWebSockets o similar
    Route::get('/ws/camera-stream', function () {
        // Manejar conexión WebSocket para stream de cámara
    })->middleware('websocket.auth');

    Route::get('/ws/entrada', function () {
        // Manejar conexión WebSocket para abrir puerta de entrada
    })->middleware('websocket.auth');

    Route::get('/ws/salida', function () {
        // Manejar conexión WebSocket para abrir puerta de salida
    })->middleware('websocket.auth');

    Route::get('/ws/peatonal', function () {
        // Manejar conexión WebSocket para abrir puerta peatonal
    })->middleware('websocket.auth');
});


