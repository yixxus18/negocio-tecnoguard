<?php

use App\Http\Controllers\TecnicoController;
use Illuminate\Support\Facades\Route;


// Rutas del Rol de Guardia
Route::middleware(['role:[1,2,5]'])->group(function () {
    Route::get('obtenerdispositivos/', [TecnicoController::class, 'index']);

    Route::post('adddispositivos/', [TecnicoController::class, 'store']);

    Route::put('editarconfigdispositivos/{id}', [TecnicoController::class, 'update']);

    Route::delete('deletedispositivos/{id}', [TecnicoController::class, 'destroy']);
});