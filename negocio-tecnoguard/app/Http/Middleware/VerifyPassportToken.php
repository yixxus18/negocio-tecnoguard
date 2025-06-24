<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyPassportToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Token de acceso requerido',
                'error' => 'UNAUTHORIZED'
            ], 401);
        }

        try {
            // Verificar el token con la API de autenticación
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get('http://localhost:8000/api/user');
            Log::info('Respuesta de verificación de Passport token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $userData = $response->json();
                $request->merge(['auth_user' => $userData]);
                $request->setUserResolver(function () use ($userData) {
                    return (object) $userData;
                });
                return $next($request);
            } else {
                return response()->json([
                    'message' => 'Token inválido o expirado',
                    'error' => 'INVALID_TOKEN'
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error('Error verificando token: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
}
