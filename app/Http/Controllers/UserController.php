<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->get('auth_user');

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no autenticado',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        return response()->json([
            'message' => 'Información del usuario obtenida exitosamente',
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Obtener información detallada del usuario desde la base de datos local
     */
    public function profile(Request $request): JsonResponse
    {
        $authUser = $request->get('auth_user');

        if (!$authUser) {
            return response()->json([
                'message' => 'Usuario no autenticado',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Buscar el usuario en la base de datos local usando el ID del usuario autenticado
        $user = \App\Models\User::with(['role', 'familyGroup'])
            ->where('email', $authUser['email'])
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado en la base de datos local',
                'error' => 'USER_NOT_FOUND'
            ], 404);
        }

        return response()->json([
            'message' => 'Perfil del usuario obtenido exitosamente',
            'data' => [
                'user' => $user,
                'role' => $user->role,
                'family_group' => $user->familyGroup
            ]
        ]);
    }
}
