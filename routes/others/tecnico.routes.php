<?php

use App\Http\Controllers\TecnicoController;
use Illuminate\Support\Facades\Route;


// Rutas del Rol de Guardia
Route::middleware(['role:1,2,5'])->group(function () {
    Route::post('/obtenerdispositivos', [TecnicoController::class, 'index']);

    Route::post('/adddispositivos', [TecnicoController::class, 'store']);


     Route::get('/Realizarinstalacion/{id}',[TecnicoController::class,'realizarinstalacion']);
     Route::get('/ObtenerInstalacionesPendientes',[TecnicoController::class,'ObtenerInstalacionesPendientes']);
    Route::put('editarconfigdispositivos/{id}', [TecnicoController::class, 'update']);

    Route::delete('deletedispositivos/{id}', [TecnicoController::class, 'destroy']);
    Route::post('/download-config', action: [TecnicoController::class, 'downloadConfig']);

});