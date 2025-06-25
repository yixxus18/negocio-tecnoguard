<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FamiliarController extends Controller
{
    /**
     * Obtener información personal
     */
    public function obtenerInformacionPersonal(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para obtener información personal
        return response()->json([
            'message' => 'Información personal obtenida exitosamente',
            'data' => []
        ]);
    }

    /**
     * Actualizar información personal
     */
    public function actualizarInformacionPersonal(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para actualizar información personal
        return response()->json([
            'message' => 'Información personal actualizada exitosamente',
            'data' => []
        ]);
    }
}
