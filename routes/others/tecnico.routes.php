<?php

use App\Http\Controllers\TecnicoController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth.api','role:1,2,6' ])->group(function () {
    Route::post('/obtenerdispositivos', [TecnicoController::class, 'index']);

    Route::post('/adddispositivos', [TecnicoController::class, 'store']);


     Route::get('/Realizarinstalacion/{id}',[TecnicoController::class,'realizarinstalacion']);
     Route::get('/ObtenerInstalacionesPendientes',[TecnicoController::class,'ObtenerInstalacionesPendientes']);
    Route::put('editarconfigdispositivos/{id}', [TecnicoController::class, 'update']);

    Route::delete('deletedispositivos/{id}', [TecnicoController::class, 'destroy']);
    Route::post('/download-config', action: [TecnicoController::class, 'downloadConfig']);
     Route::get('/catalogosDelTecnico',[TecnicoController::class,'catalogosDelTecnico']);
     Route::post('/actualizarCatalogo/{catalogo_id}',[TecnicoController::class,'actualizarCatalogo']);
     Route::get('/DashboardTecnico',[TecnicoController::class,'DashboardTecnico']);
     Route::post('/bitacoras/{bitacora_id}/concluir', [TecnicoController::class, 'ConcluirActividad']);
    Route::post('/bitacoras/{bitacora_id}/no-concluir', [TecnicoController::class, 'NoConcluidoActividad']);

});

Route::post('/configinitial/{identificador?}', action: [TecnicoController::class, 'crearconfiguracioninicialiot']);