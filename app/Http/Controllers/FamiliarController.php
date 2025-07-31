<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FamiliarController extends Controller
{
    /**
     * Obtener información personal
     */
    public function obtenerInformacionPersonal(Request $request): JsonResponse
    {
        $familiar = $request->user();
        $jefe_familia = User::where('family_id', $familiar->family_id)
            ->where('role_id', 4)->get();
        $familiar['jefe_familia'] = $jefe_familia;
        return response()->json([
            'message' => 'Información personal obtenida exitosamente',
            'data' => $familiar
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
