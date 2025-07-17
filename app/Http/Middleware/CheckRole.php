<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Maneja una solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->get('auth_user');
        if (!$user) {
            return response()->json([
                'error' => 'unauthenticated',
                'message' => 'No autenticado2',
                'data' => null,
                'status' => false
            ], 401);
        }

        // Verificar si el usuario tiene alguno de los roles permitidos
        if (!in_array($user->role_id, $roles)) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'No autorizado',
                'data' => null,
                'status' => false
            ], 403);
        }

        return $next($request);
    }
}
