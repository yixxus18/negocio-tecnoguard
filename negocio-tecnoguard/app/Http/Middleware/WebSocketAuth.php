<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebSocketAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TODO: Implementar lógica de autenticación para WebSocket
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'unauthenticated',
                'message' => 'No autenticado',
                'data' => null,
                'status' => false
            ], 401);
        }
        // Por ahora, simplemente permite el acceso
        return $next($request);
    }
}
