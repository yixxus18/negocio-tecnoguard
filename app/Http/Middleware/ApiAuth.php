<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Verificar si hay un token Bearer
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'error' => 'unauthorized',
                    'message' => 'Token de acceso requerido',
                    'data' => null,
                    'status' => false
                ], 401);
            }

            // Intentar autenticar con el token usando Passport
            if (!Auth::guard('api')->check()) {
                return response()->json([
                    'error' => 'invalid_token',
                    'message' => 'Token inválido o expirado',
                    'data' => null,
                    'status' => false
                ], 401);
            }

            // Obtener el usuario autenticado
            $user = Auth::guard('api')->user();

            // Agregar el usuario a la request para que esté disponible en los controladores
            $request->merge(['auth_user' => $user]);

            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'internal_error',
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
                'data' => null,
                'status' => false
            ], 500);
        }
    }
}
